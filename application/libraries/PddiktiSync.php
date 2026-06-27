<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * PDDikti NeoFeeder Synchronization Library
 * 
 * Handles connection, data fetching, mapping, and merging with PDDikti API
 * Implements BR-ALM-009 (PDDikti precedence), BR-SEC-003 (API Key rotation)
 * Handles ERR-ALM-002 (timeout retry), ERR-ALM-003 (invalid format rejection)
 */
class PddiktiSync {
    
    protected $CI;
    protected $api_url;
    protected $api_key;
    protected $api_secret;
    protected $timeout = 30; // seconds
    protected $max_retries = 3;
    protected $retry_delay = 5; // seconds
    protected $last_error;
    protected $connection;
    
    // Field mapping from PDDikti to local Alumni format
    protected $field_mapping = [
        'nm' => 'nama',
        'nim' => 'nim',
        'email' => 'email',
        'tgl_lulus' => 'tanggal_yudisium',
        'thn_lulus' => 'tahun_lulus',
        'no_ijazah' => 'no_ijazah',
        'no_sk_yudisium' => 'no_sk_yudisium',
        'tgl_sk_yudisium' => 'tanggal_sk_yudisium',
        'ipk' => 'ipk',
        'masa_studi' => 'masa_studi',
        'status_mahasiswa' => 'status_aktif',
        'prodi_id' => 'prodi_id',
        'pt_id' => 'universitas_id',
        'jenis_kelamin' => 'jenis_kelamin',
        'tempat_lahir' => 'tempat_lahir',
        'tanggal_lahir' => 'tanggal_lahir',
        'nik' => 'nik',
        'no_hp' => 'no_telepon',
        'alamat' => 'alamat',
        'pekerjaan' => 'pekerjaan',
        'gaji_pertama' => 'gaji_pertama',
        'tanggal_mulai_kerja' => 'tanggal_mulai_kerja'
    ];
    
    public function __construct($config = []) {
        $this->CI =& get_instance();
        $this->CI->load->model('alumni/alumni_model');
        $this->CI->load->model('sync/sync_job_model');
        $this->CI->load->helper('date');
        
        if (!empty($config)) {
            $this->connect($config);
        } else {
            // Load default config
            $this->CI->config->load('pddikti');
            $default_config = $this->CI->config->item('pddikti');
            $this->connect($default_config);
        }
    }
    
    /**
     * Connect to PDDikti API
     * 
     * @param array $config Configuration array with api_url, api_key, api_secret
     * @return bool Connection status
     */
    public function connect($config) {
        if (empty($config['api_url']) || empty($config['api_key'])) {
            $this->last_error = "Configuration missing: api_url or api_key";
            return false;
        }
        
        $this->api_url = rtrim($config['api_url'], '/');
        $this->api_key = $config['api_key'];
        $this->api_secret = isset($config['api_secret']) ? $config['api_secret'] : '';
        
        // Check API key rotation (BR-SEC-003)
        if ($this->shouldRotateApiKey()) {
            $this->rotateApiKey();
        }
        
        // Test connection
        try {
            $response = $this->makeRequest('GET', '/auth/test', [], true);
            if ($response && isset($response['status']) && $response['status'] === 'ok') {
                $this->connection = true;
                return true;
            }
        } catch (Exception $e) {
            $this->last_error = "Connection test failed: " . $e->getMessage();
        }
        
        $this->connection = false;
        return false;
    }
    
    /**
     * Fetch graduated students from PDDikti
     * 
     * @param int $tahun_lulus Year of graduation
     * @param string|null $prodi_id Study program ID (optional filter)
     * @return array|false Array of student data or false on failure
     */
    public function fetchMahasiswaLulus($tahun_lulus, $prodi_id = null) {
        if (!$this->connection) {
            $this->last_error = "Not connected to PDDikti API";
            return false;
        }
        
        $params = [
            'tahun_lulus' => $tahun_lulus,
            'limit' => 1000,
            'offset' => 0
        ];
        
        if ($prodi_id) {
            $params['prodi_id'] = $prodi_id;
        }
        
        $all_data = [];
        $has_more = true;
        $retry_count = 0;
        
        while ($has_more) {
            try {
                $response = $this->makeRequestWithRetry(
                    'GET', 
                    '/mahasiswa/lulus', 
                    $params
                );
                
                if ($response === false) {
                    // ERR-ALM-002: Timeout after retries
                    log_message('error', "PDDikti Sync: Timeout fetching data for tahun {$tahun_lulus}, offset {$params['offset']}");
                    return false;
                }
                
                if (isset($response['data']) && is_array($response['data'])) {
                    $all_data = array_merge($all_data, $response['data']);
                    
                    // Check if there's more data
                    $has_more = isset($response['total']) && 
                               count($all_data) < $response['total'];
                    
                    if ($has_more) {
                        $params['offset'] += $params['limit'];
                    }
                } else {
                    $has_more = false;
                }
                
            } catch (Exception $e) {
                log_message('error', "PDDikti Sync Error: " . $e->getMessage());
                $retry_count++;
                
                if ($retry_count >= $this->max_retries) {
                    $this->last_error = "Max retries exceeded: " . $e->getMessage();
                    return false;
                }
                
                sleep($this->retry_delay);
            }
        }
        
        log_message('info', "PDDikti Sync: Fetched " . count($all_data) . " records for tahun {$tahun_lulus}");
        return $all_data;
    }
    
    /**
     * Map PDDikti data fields to local Alumni format
     * 
     * @param array $pddikti_data Raw data from PDDikti
     * @return array Mapped data in Alumni format
     */
    public function mapToAlumniFormat($pddikti_data) {
        $mapped = [];
        
        foreach ($this->field_mapping as $pddikti_field => $local_field) {
            if (isset($pddikti_data[$pddikti_field])) {
                $value = $pddikti_data[$pddikti_field];
                
                // Apply transformations based on field type
                switch ($local_field) {
                    case 'tanggal_yudisium':
                    case 'tanggal_sk_yudisium':
                    case 'tanggal_lahir':
                    case 'tanggal_mulai_kerja':
                        $mapped[$local_field] = $this->formatDate($value);
                        break;
                    
                    case 'status_aktif':
                        // Map PDDikti status to active/inactive
                        $mapped[$local_field] = ($value === 'Aktif' || $value === 'Lulus') ? 1 : 0;
                        break;
                    
                    case 'jenis_kelamin':
                        $mapped[$local_field] = ($value === 'L' || $value === 'Laki-laki') ? 'L' : 'P';
                        break;
                    
                    case 'ipk':
                        $mapped[$local_field] = floatval($value);
                        break;
                    
                    case 'gaji_pertama':
                        $mapped[$local_field] = intval(str_replace(['.', ','], ['', ''], $value));
                        break;
                    
                    default:
                        $mapped[$local_field] = $value;
                        break;
                }
            }
        }
        
        // Add metadata
        $mapped['sumber_data'] = 'pddikti';
        $mapped['last_sync'] = date('Y-m-d H:i:s');
        $mapped['verified_pddikti'] = 1;
        
        return $mapped;
    }
    
    /**
     * Merge PDDikti data with existing data (PDDikti wins - BR-ALM-009)
     * 
     * @param array $pddikti_data Data from PDDikti (already mapped)
     * @param array $existing_data Existing data in database
     * @return array Merged data
     */
    public function mergeData($pddikti_data, $existing_data) {
        // Start with existing data
        $merged = $existing_data ?: [];
        
        // PDDikti data takes precedence (BR-ALM-009)
        // Critical fields that must be updated from PDDikti
        $critical_fields = [
            'nama', 'nim', 'email', 'tanggal_yudisium', 'tahun_lulus',
            'no_ijazah', 'no_sk_yudisium', 'ipk', 'status_aktif',
            'prodi_id', 'nik', 'verified_pddikti'
        ];
        
        // Always update critical fields from PDDikti
        foreach ($critical_fields as $field) {
            if (isset($pddikti_data[$field]) && $pddikti_data[$field] !== null) {
                $merged[$field] = $pddikti_data[$field];
            }
        }
        
        // For other fields, update only if PDDikti has value and local is empty/null
        $optional_fields = array_diff(array_keys($pddikti_data), $critical_fields);
        
        foreach ($optional_fields as $field) {
            if ($field === 'sumber_data' || $field === 'last_sync') {
                $merged[$field] = $pddikti_data[$field];
                continue;
            }
            
            if (isset($pddikti_data[$field]) && 
                $pddikti_data[$field] !== null && 
                $pddikti_data[$field] !== '' &&
                (empty($merged[$field]) || $merged[$field] === null)) {
                $merged[$field] = $pddikti_data[$field];
            }
        }
        
        // Preserve manual-only fields if not in PDDikti
        $manual_fields = ['alamat_lengkap', 'linkedin_url', 'facebook_url', 'instagram_url'];
        foreach ($manual_fields as $field) {
            if (isset($existing_data[$field]) && !isset($pddikti_data[$field])) {
                $merged[$field] = $existing_data[$field];
            }
        }
        
        return $merged;
    }
    
    /**
     * Validate data before import
     * 
     * @param array $data Data to validate
     * @return array ['valid' => bool, 'errors' => array]
     */
    public function validateData($data) {
        $errors = [];
        
        // Validate NIM (required, unique check will be done during import)
        if (empty($data['nim'])) {
            $errors[] = "NIM is required";
        } elseif (!preg_match('/^[A-Za-z0-9]{8,20}$/', $data['nim'])) {
            $errors[] = "Invalid NIM format";
        }
        
        // Validate email format
        if (!empty($data['email'])) {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Invalid email format";
            }
        }
        
        // Validate tahun lulus
        if (empty($data['tahun_lulus'])) {
            $errors[] = "Tahun lulus is required";
        } elseif (!is_numeric($data['tahun_lulus']) || 
                  $data['tahun_lulus'] < 2000 || 
                  $data['tahun_lulus'] > date('Y')) {
            $errors[] = "Invalid tahun lulus";
        }
        
        // Validate tanggal yudisium if present
        if (!empty($data['tanggal_yudisium'])) {
            $timestamp = strtotime($data['tanggal_yudisium']);
            if ($timestamp === false) {
                $errors[] = "Invalid tanggal yudisium format";
            }
        }
        
        // Validate IPK if present
        if (!empty($data['ipk'])) {
            if ($data['ipk'] < 0 || $data['ipk'] > 4.0) {
                $errors[] = "IPK must be between 0.00 and 4.00";
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Process batch synchronization
     * 
     * @param int $tahun_lulus Year to sync
     * @param string|null $prodi_id Study program ID filter
     * @return array Sync result statistics
     */
    public function syncBatch($tahun_lulus, $prodi_id = null) {
        $result = [
            'tahun_lulus' => $tahun_lulus,
            'prodi_id' => $prodi_id,
            'fetched' => 0,
            'inserted' => 0,
            'updated' => 0,
            'skipped' => 0,
            'failed' => 0,
            'errors' => []
        ];
        
        log_message('info', "Starting PDDikti sync batch: tahun={$tahun_lulus}, prodi={$prodi_id}");
        
        // Fetch data from PDDikti
        $pddikti_data = $this->fetchMahasiswaLulus($tahun_lulus, $prodi_id);
        
        if ($pddikti_data === false) {
            $result['errors'][] = "Failed to fetch data from PDDikti: " . $this->last_error;
            return $result;
        }
        
        $result['fetched'] = count($pddikti_data);
        
        // Process each record
        foreach ($pddikti_data as $record) {
            try {
                // Map to local format
                $mapped_data = $this->mapToAlumniFormat($record);
                
                // Validate
                $validation = $this->validateData($mapped_data);
                
                if (!$validation['valid']) {
                    // ERR-ALM-003: Invalid format - reject and log
                    $result['failed']++;
                    $result['errors'][] = [
                        'nim' => $mapped_data['nim'] ?? 'unknown',
                        'errors' => $validation['errors']
                    ];
                    continue;
                }
                
                // Check if alumni exists
                $existing = $this->CI->alumni_model->getByNIM($mapped_data['nim']);
                
                if ($existing) {
                    // BR-ALM-010: Skip non-active alumni
                    if (isset($existing['status_aktif']) && $existing['status_aktif'] == 0) {
                        $result['skipped']++;
                        continue;
                    }
                    
                    // Merge data (PDDikti wins)
                    $merged_data = $this->mergeData($mapped_data, $existing);
                    
                    // Update existing record
                    $update_result = $this->CI->alumni_model->update($existing['id'], $merged_data);
                    
                    if ($update_result) {
                        $result['updated']++;
                        
                        // Trigger IKU recalculation (BR-ALM-008)
                        $this->CI->alumni_model->triggerIKURecalculation($existing['id']);
                        
                        log_message('info', "Updated alumni: " . $mapped_data['nim']);
                    } else {
                        $result['failed']++;
                        $result['errors'][] = [
                            'nim' => $mapped_data['nim'],
                            'error' => 'Database update failed'
                        ];
                    }
                } else {
                    // Insert new alumni
                    $insert_id = $this->CI->alumni_model->insert($mapped_data);
                    
                    if ($insert_id) {
                        $result['inserted']++;
                        log_message('info', "Inserted new alumni: " . $mapped_data['nim']);
                    } else {
                        $result['failed']++;
                        $result['errors'][] = [
                            'nim' => $mapped_data['nim'],
                            'error' => 'Database insert failed'
                        ];
                    }
                }
                
            } catch (Exception $e) {
                $result['failed']++;
                $result['errors'][] = [
                    'nim' => $record['nim'] ?? 'unknown',
                    'error' => $e->getMessage()
                ];
                log_message('error', "PDDikti sync error: " . $e->getMessage());
            }
        }
        
        // Log sync result
        $this->logSyncResult($result);
        
        log_message('info', "PDDikti sync completed: " . json_encode($result));
        
        return $result;
    }
    
    /**
     * Log synchronization result
     * 
     * @param array $result Sync result array
     * @return int Log ID
     */
    public function logSyncResult($result) {
        $log_data = [
            'sync_type' => 'pddikti',
            'sync_date' => date('Y-m-d H:i:s'),
            'tahun_lulus' => $result['tahun_lulus'],
            'prodi_id' => $result['prodi_id'],
            'fetched_count' => $result['fetched'],
            'inserted_count' => $result['inserted'],
            'updated_count' => $result['updated'],
            'skipped_count' => $result['skipped'],
            'failed_count' => $result['failed'],
            'errors' => !empty($result['errors']) ? json_encode($result['errors']) : null,
            'status' => $result['failed'] > 0 ? 'partial' : 'success'
        ];
        
        // Insert into sync_logs table
        $this->CI->db->insert('sync_logs', $log_data);
        
        return $this->CI->db->insert_id();
    }
    
    /**
     * Make HTTP request to PDDikti API
     * 
     * @param string $method HTTP method
     * @param string $endpoint API endpoint
     * @param array $params Request parameters
     * @param bool $auth_only Only for auth test
     * @return array|false Response data or false on failure
     */
    protected function makeRequest($method, $endpoint, $params = [], $auth_only = false) {
        $url = $this->api_url . $endpoint;
        
        $headers = [
            'Content-Type: application/json',
            'X-API-Key: ' . $this->api_key
        ];
        
        if (!$auth_only && !empty($this->api_secret)) {
            // Generate signature for authenticated requests
            $timestamp = time();
            $signature = hash_hmac('sha256', $timestamp . $endpoint, $this->api_secret);
            $headers[] = 'X-Signature: ' . $signature;
            $headers[] = 'X-Timestamp: ' . $timestamp;
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . '?' . http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        }
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            throw new Exception("CURL error: " . $error);
        }
        
        if ($http_code !== 200) {
            throw new Exception("HTTP error {$http_code}: " . $response);
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Make request with retry logic (ERR-ALM-002)
     * 
     * @param string $method HTTP method
     * @param string $endpoint API endpoint
     * @param array $params Request parameters
     * @return array|false Response or false after max retries
     */
    protected function makeRequestWithRetry($method, $endpoint, $params) {
        $retry_count = 0;
        
        while ($retry_count < $this->max_retries) {
            try {
                return $this->makeRequest($method, $endpoint, $params);
            } catch (Exception $e) {
                $retry_count++;
                
                // Check if it's a timeout error
                if (strpos($e->getMessage(), 'timeout') !== false || 
                    strpos($e->getMessage(), 'timed out') !== false) {
                    
                    log_message('warning', "PDDikti API timeout (attempt {$retry_count}/{$this->max_retries})");
                    
                    if ($retry_count < $this->max_retries) {
                        sleep($this->retry_delay * $retry_count); // Exponential backoff
                    }
                } else {
                    // Non-timeout error, don't retry
                    throw $e;
                }
            }
        }
        
        // ERR-ALM-002: Max retries exceeded
        return false;
    }
    
    /**
     * Format date from various formats to Y-m-d
     * 
     * @param string $date Date string
     * @return string|false Formatted date or false
     */
    protected function formatDate($date) {
        if (empty($date)) {
            return null;
        }
        
        // Try common formats
        $formats = ['Y-m-d', 'd/m/Y', 'd-M-Y', 'Ymd', 'Y-m-d H:i:s'];
        
        foreach ($formats as $format) {
            $parsed = DateTime::createFromFormat($format, $date);
            if ($parsed && $parsed->format($format) === $date) {
                return $parsed->format('Y-m-d');
            }
        }
        
        // If all fails, return as-is or null
        return $date;
    }
    
    /**
     * Check if API key should be rotated (BR-SEC-003)
     * 
     * @return bool True if rotation needed
     */
    protected function shouldRotateApiKey() {
        $this->CI->load->helper('date');
        
        $last_rotation = $this->CI->config->item('pddikti_api_last_rotation');
        
        if (!$last_rotation) {
            return true; // Never rotated
        }
        
        $rotation_time = strtotime($last_rotation);
        $ninety_days = 90 * 24 * 60 * 60;
        
        return (time() - $rotation_time) > $ninety_days;
    }
    
    /**
     * Rotate API key (BR-SEC-003)
     * 
     * @return bool Success status
     */
    protected function rotateApiKey() {
        log_message('info', "Rotating PDDikti API key (90-day rotation policy)");
        
        try {
            // Request new API key from PDDikti
            $response = $this->makeRequest('POST', '/auth/rotate-key', [
                'current_key' => $this->api_key,
                'reason' => 'scheduled_rotation'
            ]);
            
            if (isset($response['new_key'])) {
                // Update config file or database
                $this->updateApiKeyConfig($response['new_key']);
                
                $this->api_key = $response['new_key'];
                
                // Update last rotation timestamp
                $this->CI->config->set_item('pddikti_api_last_rotation', date('Y-m-d H:i:s'));
                
                log_message('info', "API key rotated successfully");
                return true;
            }
        } catch (Exception $e) {
            log_message('error', "API key rotation failed: " . $e->getMessage());
        }
        
        return false;
    }
    
    /**
     * Update API key in configuration
     * 
     * @param string $new_key New API key
     * @return bool Success status
     */
    protected function updateApiKeyConfig($new_key) {
        // This could be implemented to update database config or config file
        // For now, we'll just log it
        log_message('info', "New API key received, update configuration manually or via admin panel");
        
        // Example: Update in database config table
        $this->CI->db->where('config_key', 'pddikti_api_key');
        $this->CI->db->update('config', ['config_value' => $new_key]);
        
        return true;
    }
    
    /**
     * Get last error message
     * 
     * @return string Last error
     */
    public function getLastError() {
        return $this->last_error;
    }
    
    /**
     * Check connection status
     * 
     * @return bool Connection status
     */
    public function isConnected() {
        return $this->connection;
    }
}
