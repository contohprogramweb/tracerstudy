<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Encryption Library Wrapper untuk Tracer Study v3.1
 * 
 * Fitur:
 * - encryptPII($data) - AES-256 untuk data sensitif (no_hp, alamat, gaji_aktual, NIK)
 * - decryptPII($data) - Decrypt data yang dienkripsi
 * - maskPII($data, $type) - Masking untuk display di log (***)
 * - hashForLog($data) - SHA-256 untuk audit trail
 * 
 * @package     Tracer Study
 * @subpackage  Libraries
 * @category    Libraries
 * @author      Tracer Study Team
 * @version     3.1.0
 */
class Tracer_encryption {

    /**
     * CI Instance
     * @var object
     */
    protected $CI;

    /**
     * Encryption driver
     * @var object
     */
    protected $encryption;

    /**
     * Fields yang dianggap PII (Personally Identifiable Information)
     * @var array
     */
    protected $pii_fields = array(
        'nik',
        'nik_encrypted',
        'no_hp',
        'nomor_telepon',
        'alamat',
        'alamat_domisili',
        'gaji',
        'gaji_aktual',
        'email_pribadi',
        'tanggal_lahir'
    );

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->CI =& get_instance();
        
        // Load CI encryption library
        $this->CI->load->library('encryption');
        $this->encryption = $this->CI->encryption;
        
        log_message('info', 'Tracer Encryption Library Initialized');
    }

    // ========================================================================
    // ENCRYPTION/DECRYPTION METHODS
    // ========================================================================

    /**
     * Encrypt PII data
     * 
     * Mendukung berbagai input:
     * - String: encrypt langsung
     * - Array: encrypt field-field PII dalam array
     * - Object: encrypt field-field PII dalam object
     * 
     * @param mixed $data Data to encrypt
     * @return mixed Encrypted data
     */
    public function encryptPII($data)
    {
        if ($data === NULL || $data === '') {
            return $data;
        }

        if (is_string($data)) {
            return $this->encryption->encrypt($data);
        }

        if (is_array($data)) {
            return $this->_encrypt_array($data);
        }

        if (is_object($data)) {
            return $this->_encrypt_object($data);
        }

        return $data;
    }

    /**
     * Decrypt PII data
     * 
     * @param mixed $data Data to decrypt
     * @return mixed Decrypted data or FALSE on failure
     */
    public function decryptPII($data)
    {
        if ($data === NULL || $data === '') {
            return $data;
        }

        if (is_string($data)) {
            return $this->encryption->decrypt($data);
        }

        if (is_array($data)) {
            return $this->_decrypt_array($data);
        }

        if (is_object($data)) {
            return $this->_decrypt_object($data);
        }

        return $data;
    }

    /**
     * Mask PII data for display in logs
     * 
     * Type masking:
     * - 'nik': 3208***********1234 (show first 4 and last 4)
     * - 'phone': 0812****5678 (show first 4 and last 4)
     * - 'email': j***@example.com (show first char and domain)
     * - 'address': Jl. *** (show first 10 chars)
     * - 'default': ******** (mask all)
     * 
     * @param mixed $data Data to mask
     * @param string $type Type of masking (nik, phone, email, address, default)
     * @return string Masked data
     */
    public function maskPII($data, $type = 'default')
    {
        if ($data === NULL || $data === '') {
            return $data;
        }

        $data = (string) $data;
        $length = strlen($data);

        switch ($type) {
            case 'nik':
                // Show first 4 and last 4 characters
                if ($length > 8) {
                    return substr($data, 0, 4) . str_repeat('*', $length - 8) . substr($data, -4);
                }
                return str_repeat('*', $length);

            case 'phone':
                // Show first 4 and last 4 characters
                if ($length > 8) {
                    return substr($data, 0, 4) . str_repeat('*', $length - 8) . substr($data, -4);
                }
                return str_repeat('*', $length);

            case 'email':
                // Show first character and domain
                $parts = explode('@', $data);
                if (count($parts) === 2) {
                    $local = $parts[0];
                    $domain = $parts[1];
                    
                    if (strlen($local) > 1) {
                        $masked_local = $local[0] . str_repeat('*', strlen($local) - 1);
                    } else {
                        $masked_local = '*';
                    }
                    
                    return $masked_local . '@' . $domain;
                }
                return str_repeat('*', $length);

            case 'address':
                // Show first 10 characters
                if ($length > 10) {
                    return substr($data, 0, 10) . ' ***';
                }
                return str_repeat('*', $length);

            case 'date':
                // Show only year
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
                    return substr($data, 0, 4) . '-**-**';
                }
                return str_repeat('*', $length);

            default:
                // Mask all
                return str_repeat('*', $length);
        }
    }

    /**
     * Hash data for audit trail using SHA-256
     * 
     * @param mixed $data Data to hash
     * @param string $salt Optional salt for additional security
     * @return string Hashed data (64 characters hex)
     */
    public function hashForLog($data, $salt = '')
    {
        if ($data === NULL) {
            $data = 'NULL';
        }

        if (is_array($data) || is_object($data)) {
            $data = json_encode($data);
        }

        $salt = $salt ?: $this->CI->config->item('encryption_key');
        
        return hash('sha256', (string) $data . $salt);
    }

    /**
     * Hash multiple fields for batch logging
     * 
     * @param array $data Associative array of field => value
     * @param array $fields Specific fields to hash (optional, defaults to PII fields)
     * @return array Array with hashed values
     */
    public function hashFields(array $data, array $fields = NULL)
    {
        $fields = $fields ?: $this->pii_fields;
        $hashed = array();

        foreach ($data as $key => $value) {
            if (in_array($key, $fields)) {
                $hashed[$key] = $this->hashForLog($value);
            } else {
                $hashed[$key] = $value;
            }
        }

        return $hashed;
    }

    /**
     * Mask multiple fields for display
     * 
     * @param array $data Associative array of field => value
     * @param array $field_types Associative array of field => mask_type
     * @return array Array with masked values
     */
    public function maskFields(array $data, array $field_types = NULL)
    {
        $field_types = $field_types ?: array();
        $masked = array();

        foreach ($data as $key => $value) {
            if (isset($field_types[$key])) {
                $masked[$key] = $this->maskPII($value, $field_types[$key]);
            } elseif (in_array($key, $this->pii_fields)) {
                // Auto-detect type based on field name
                $type = $this->_detect_mask_type($key);
                $masked[$key] = $this->maskPII($value, $type);
            } else {
                $masked[$key] = $value;
            }
        }

        return $masked;
    }

    /**
     * Verify that decrypted data matches expected value
     * 
     * @param string $encrypted_data Encrypted data
     * @param string $expected_value Expected plain text value
     * @return bool TRUE if match, FALSE otherwise
     */
    public function verifyDecryption($encrypted_data, $expected_value)
    {
        $decrypted = $this->encryption->decrypt($encrypted_data);
        return $decrypted === $expected_value;
    }

    /**
     * Get list of PII fields
     * 
     * @return array
     */
    public function getPIIFields()
    {
        return $this->pii_fields;
    }

    /**
     * Add custom PII field
     * 
     * @param string $field Field name
     * @return void
     */
    public function addPIIField($field)
    {
        if (!in_array($field, $this->pii_fields)) {
            $this->pii_fields[] = $field;
        }
    }

    // ========================================================================
    // PRIVATE HELPER METHODS
    // ========================================================================

    /**
     * Encrypt array recursively
     * 
     * @param array $data Array to encrypt
     * @return array Encrypted array
     */
    private function _encrypt_array(array $data)
    {
        $encrypted = array();

        foreach ($data as $key => $value) {
            if (in_array($key, $this->pii_fields) && !is_array($value) && !is_object($value)) {
                $encrypted[$key] = $this->encryption->encrypt((string) $value);
            } elseif (is_array($value)) {
                $encrypted[$key] = $this->_encrypt_array($value);
            } elseif (is_object($value)) {
                $encrypted[$key] = $this->_encrypt_object($value);
            } else {
                $encrypted[$key] = $value;
            }
        }

        return $encrypted;
    }

    /**
     * Decrypt array recursively
     * 
     * @param array $data Array to decrypt
     * @return array Decrypted array
     */
    private function _decrypt_array(array $data)
    {
        $decrypted = array();

        foreach ($data as $key => $value) {
            if (in_array($key, $this->pii_fields) && !is_array($value) && !is_object($value)) {
                $decrypted[$key] = $this->encryption->decrypt($value);
            } elseif (is_array($value)) {
                $decrypted[$key] = $this->_decrypt_array($value);
            } elseif (is_object($value)) {
                $decrypted[$key] = $this->_decrypt_object($value);
            } else {
                $decrypted[$key] = $value;
            }
        }

        return $decrypted;
    }

    /**
     * Encrypt object properties
     * 
     * @param object $data Object to encrypt
     * @return object Encrypted object
     */
    private function _encrypt_object($data)
    {
        $array = (array) $data;
        $encrypted_array = $this->_encrypt_array($array);
        
        // Preserve original class if possible
        $reflected = new ReflectionClass(get_class($data));
        if ($reflected->isInstantiable()) {
            $new_obj = $reflected->newInstanceWithoutConstructor();
            foreach ($encrypted_array as $key => $value) {
                $property = $reflected->getProperty($key);
                $property->setAccessible(true);
                $property->setValue($new_obj, $value);
            }
            return $new_obj;
        }
        
        return (object) $encrypted_array;
    }

    /**
     * Decrypt object properties
     * 
     * @param object $data Object to decrypt
     * @return object Decrypted object
     */
    private function _decrypt_object($data)
    {
        $array = (array) $data;
        $decrypted_array = $this->_decrypt_array($array);
        return (object) $decrypted_array;
    }

    /**
     * Detect mask type based on field name
     * 
     * @param string $field_name Field name
     * @return string Mask type
     */
    private function _detect_mask_type($field_name)
    {
        $field_name_lower = strtolower($field_name);

        if (strpos($field_name_lower, 'nik') !== false) {
            return 'nik';
        }

        if (strpos($field_name_lower, 'hp') !== false || 
            strpos($field_name_lower, 'phone') !== false ||
            strpos($field_name_lower, 'telepon') !== false ||
            strpos($field_name_lower, 'telp') !== false) {
            return 'phone';
        }

        if (strpos($field_name_lower, 'email') !== false) {
            return 'email';
        }

        if (strpos($field_name_lower, 'alamat') !== false || 
            strpos($field_name_lower, 'address') !== false) {
            return 'address';
        }

        if (strpos($field_name_lower, 'tanggal') !== false || 
            strpos($field_name_lower, 'date') !== false ||
            strpos($field_name_lower, 'lahir') !== false) {
            return 'date';
        }

        return 'default';
    }
}
