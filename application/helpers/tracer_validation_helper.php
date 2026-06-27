<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Validation Helper untuk Tracer Study v3.1
 * 
 * Fungsi validasi khusus:
 * - validateNIM($nim) - Validasi format NIM
 * - validateEmailUnik($email, $exclude_id=null) - Validasi email unik
 * - validateTanggalYudisium($tanggal) - Validasi tanggal yudisium
 * - validateMasaTunggu($tanggal_mulai, $tanggal_yudisium) - Validasi masa tunggu kerja
 * 
 * @package     Tracer Study
 * @subpackage  Helpers
 * @category    Helpers
 * @author      Tracer Study Team
 * @version     3.1.0
 */

// ============================================================================
// NIM VALIDATION
// ============================================================================

if (!function_exists('validateNIM')) {
    /**
     * Validasi format NIM (Nomor Induk Mahasiswa)
     * 
     * Format yang diterima:
     * - 10-14 digit angka
     * - Dapat dimulai dengan tahun angkatan (2 digit atau 4 digit)
     * - Tidak boleh mengandung karakter khusus kecuali '-' atau '.'
     * 
     * @param string $nim NIM to validate
     * @return array ['valid' => bool, 'message' => string, 'normalized' => string|null]
     */
    function validateNIM($nim)
    {
        $CI =& get_instance();
        $CI->load->helper('string');
        
        if (empty($nim)) {
            return array(
                'valid' => FALSE,
                'message' => 'NIM tidak boleh kosong',
                'normalized' => NULL
            );
        }
        
        // Convert to string and trim
        $nim = trim((string) $nim);
        
        // Remove common separators (- and .)
        $nim_clean = str_replace(array('-', '.'), '', $nim);
        
        // Check if only digits
        if (!ctype_digit($nim_clean)) {
            return array(
                'valid' => FALSE,
                'message' => 'NIM hanya boleh berisi angka, tanda minus (-), atau titik (.)',
                'normalized' => NULL
            );
        }
        
        // Check length (10-14 digits)
        $length = strlen($nim_clean);
        if ($length < 10 || $length > 14) {
            return array(
                'valid' => FALSE,
                'message' => 'Panjang NIM harus antara 10-14 digit (saat ini: ' . $length . ' digit)',
                'normalized' => NULL
            );
        }
        
        // Extract year if present (first 2 or 4 digits)
        $year = NULL;
        if ($length >= 4) {
            $potential_year_4 = substr($nim_clean, 0, 4);
            $current_year = date('Y');
            
            if ($potential_year_4 >= 1990 && $potential_year_4 <= $current_year + 1) {
                $year = $potential_year_4;
            } else {
                $potential_year_2 = substr($nim_clean, 0, 2);
                if ($potential_year_2 >= 90 && $potential_year_2 <= 99) {
                    $year = '19' . $potential_year_2;
                } elseif ($potential_year_2 >= 0 && $potential_year_2 <= 99) {
                    $year = '20' . $potential_year_2;
                }
            }
        }
        
        // Validate year range if extracted
        if ($year !== NULL) {
            $min_year = 1990;
            $max_year = date('Y') + 1;
            
            if ($year < $min_year || $year > $max_year) {
                return array(
                    'valid' => FALSE,
                    'message' => 'Tahun angkatan dalam NIM tidak valid (' . $year . ')',
                    'normalized' => NULL
                );
            }
        }
        
        return array(
            'valid' => TRUE,
            'message' => 'NIM valid',
            'normalized' => $nim_clean,
            'year' => $year,
            'length' => $length
        );
    }
}

// ============================================================================
// EMAIL UNIQUENESS VALIDATION
// ============================================================================

if (!function_exists('validateEmailUnik')) {
    /**
     * Validasi email unik di database
     * 
     * @param string $email Email to validate
     * @param int|null $exclude_id Exclude this user ID from check (for updates)
     * @param string $table Table to check (default: 'users')
     * @return array ['valid' => bool, 'message' => string, 'exists' => bool]
     */
    function validateEmailUnik($email, $exclude_id = NULL, $table = 'users')
    {
        $CI =& get_instance();
        $CI->load->database();
        $CI->load->helper('email');
        
        // First, validate email format
        if (empty($email)) {
            return array(
                'valid' => FALSE,
                'message' => 'Email tidak boleh kosong',
                'exists' => FALSE
            );
        }
        
        if (!valid_email($email)) {
            return array(
                'valid' => FALSE,
                'message' => 'Format email tidak valid',
                'exists' => FALSE
            );
        }
        
        // Normalize email to lowercase
        $email = strtolower(trim($email));
        
        // Check uniqueness in database
        $CI->db->where('email', $email);
        
        if ($exclude_id !== NULL) {
            $CI->db->where('id !=', $exclude_id);
        }
        
        $query = $CI->db->get($table);
        
        if ($query->num_rows() > 0) {
            return array(
                'valid' => FALSE,
                'message' => 'Email sudah terdaftar di sistem',
                'exists' => TRUE
            );
        }
        
        return array(
            'valid' => TRUE,
            'message' => 'Email tersedia',
            'exists' => FALSE
        );
    }
}

// ============================================================================
// GRADUATION DATE VALIDATION
// ============================================================================

if (!function_exists('validateTanggalYudisium')) {
    /**
     * Validasi tanggal yudisium
     * 
     * Kriteria:
     * - Harus format date yang valid (YYYY-MM-DD)
     * - Tidak boleh di masa depan (maksimal hari ini)
     * - Tidak boleh sebelum tahun 1990
     * - Harus lebih besar dari tanggal lahir (jika disediakan)
     * 
     * @param string $tanggal Tanggal yudisium (YYYY-MM-DD)
     * @param string|null $tanggal_lahir Tanggal lahir untuk validasi tambahan
     * @return array ['valid' => bool, 'message' => string, 'date_object' => DateTime|null]
     */
    function validateTanggalYudisium($tanggal, $tanggal_lahir = NULL)
    {
        $CI =& get_instance();
        $CI->load->helper('date');
        
        if (empty($tanggal)) {
            return array(
                'valid' => FALSE,
                'message' => 'Tanggal yudisium tidak boleh kosong',
                'date_object' => NULL
            );
        }
        
        // Try to parse date
        $date_obj = DateTime::createFromFormat('Y-m-d', $tanggal);
        
        if (!$date_obj || $date_obj->format('Y-m-d') !== $tanggal) {
            return array(
                'valid' => FALSE,
                'message' => 'Format tanggal tidak valid. Gunakan format YYYY-MM-DD',
                'date_object' => NULL
            );
        }
        
        $current_date = new DateTime();
        $min_date = new DateTime('1990-01-01');
        
        // Check if date is in the future
        if ($date_obj > $current_date) {
            return array(
                'valid' => FALSE,
                'message' => 'Tanggal yudisium tidak boleh di masa depan',
                'date_object' => NULL
            );
        }
        
        // Check if date is before minimum year
        if ($date_obj < $min_date) {
            return array(
                'valid' => FALSE,
                'message' => 'Tanggal yudisium tidak boleh sebelum tahun 1990',
                'date_object' => NULL
            );
        }
        
        // Check against birth date if provided
        if ($tanggal_lahir !== NULL && !empty($tanggal_lahir)) {
            $birth_date = DateTime::createFromFormat('Y-m-d', $tanggal_lahir);
            
            if ($birth_date && $birth_date->format('Y-m-d') === $tanggal_lahir) {
                // Graduate must be at least 15 years old (reasonable minimum)
                $min_graduate_age = clone $birth_date;
                $min_graduate_age->modify('+15 years');
                
                if ($date_obj < $min_graduate_age) {
                    return array(
                        'valid' => FALSE,
                        'message' => 'Tanggal yudisium tidak valid berdasarkan tanggal lahir (usia minimal 15 tahun)',
                        'date_object' => NULL
                    );
                }
            }
        }
        
        return array(
            'valid' => TRUE,
            'message' => 'Tanggal yudisium valid',
            'date_object' => $date_obj,
            'formatted' => $date_obj->format('d F Y')
        );
    }
}

// ============================================================================
// WAITING PERIOD VALIDATION
// ============================================================================

if (!function_exists('validateMasaTunggu')) {
    /**
     * Validasi masa tunggu kerja (waktu antara yudisium sampai mulai kerja)
     * 
     * Kriteria:
     * - Tanggal mulai kerja harus setelah tanggal yudisium
     * - Masa tunggu tidak boleh negatif
     * - Masa tunggu lebih dari 5 tahun dianggap tidak wajar (warning)
     * 
     * @param string $tanggal_mulai Tanggal mulai kerja (YYYY-MM-DD)
     * @param string $tanggal_yudisium Tanggal yudisium (YYYY-MM-DD)
     * @return array [
     *   'valid' => bool,
     *   'message' => string,
     *   'days' => int|null,
     *   'months' => float|null,
     *   'years' => float|null,
     *   'warning' => bool
     * ]
     */
    function validateMasaTunggu($tanggal_mulai, $tanggal_yudisium)
    {
        $result = array(
            'valid' => FALSE,
            'message' => '',
            'days' => NULL,
            'months' => NULL,
            'years' => NULL,
            'warning' => FALSE
        );
        
        if (empty($tanggal_mulai) || empty($tanggal_yudisium)) {
            $result['message'] = 'Tanggal mulai kerja dan tanggal yudisium harus diisi';
            return $result;
        }
        
        // Parse dates
        $yudisium_date = DateTime::createFromFormat('Y-m-d', $tanggal_yudisium);
        $start_date = DateTime::createFromFormat('Y-m-d', $tanggal_mulai);
        
        if (!$yudisium_date || $yudisium_date->format('Y-m-d') !== $tanggal_yudisium) {
            $result['message'] = 'Format tanggal yudisium tidak valid';
            return $result;
        }
        
        if (!$start_date || $start_date->format('Y-m-d') !== $tanggal_mulai) {
            $result['message'] = 'Format tanggal mulai kerja tidak valid';
            return $result;
        }
        
        // Calculate difference
        $diff = $yudisium_date->diff($start_date);
        $days = $diff->days;
        
        // Check if start date is before graduation
        if ($start_date < $yudisium_date) {
            $result['message'] = 'Tanggal mulai kerja tidak boleh sebelum tanggal yudisium';
            return $result;
        }
        
        // Calculate months and years
        $months = $days / 30.44; // Average days per month
        $years = $days / 365.25; // Average days per year
        
        $result['valid'] = TRUE;
        $result['message'] = 'Masa tunggu valid';
        $result['days'] = $days;
        $result['months'] = round($months, 2);
        $result['years'] = round($years, 2);
        
        // Warning if waiting period is too long (> 5 years)
        if ($years > 5) {
            $result['warning'] = TRUE;
            $result['message'] .= ' (Peringatan: Masa tunggu lebih dari 5 tahun)';
        }
        
        // Special case: started working before graduation (internship, etc.)
        if ($days === 0) {
            $result['message'] = 'Mulai kerja pada hari yang sama dengan yudisium';
        }
        
        return $result;
    }
}

// ============================================================================
// ADDITIONAL VALIDATION HELPERS
// ============================================================================

if (!function_exists('validateNIK')) {
    /**
     * Validasi NIK (Nomor Induk Kependudukan) Indonesia
     * 
     * Format: 16 digit, struktur: PPDDKKTTBBJJRRRR
     * PP = Provinsi, DD = Kota/Kabupaten, KK = Kecamatan
     * TT = Tahun, BB = Bulan, JJ = Jenis Kelamin (1-6)
     * RRRR = Random/Unique
     * 
     * @param string $nik NIK to validate
     * @return array ['valid' => bool, 'message' => string, 'data' => array|null]
     */
    function validateNIK($nik)
    {
        if (empty($nik)) {
            return array(
                'valid' => FALSE,
                'message' => 'NIK tidak boleh kosong',
                'data' => NULL
            );
        }
        
        // Remove any non-digit characters
        $nik_clean = preg_replace('/[^0-9]/', '', $nik);
        
        // Must be exactly 16 digits
        if (strlen($nik_clean) !== 16) {
            return array(
                'valid' => FALSE,
                'message' => 'NIK harus terdiri dari 16 digit',
                'data' => NULL
            );
        }
        
        // Extract components
        $province_code = substr($nik_clean, 0, 2);
        $city_code = substr($nik_clean, 2, 2);
        $district_code = substr($nik_clean, 4, 2);
        $date_part = substr($nik_clean, 6, 6);
        $unique_part = substr($nik_clean, 12, 4);
        
        // Extract birth date from NIK
        $day = substr($date_part, 0, 2);
        $month = substr($date_part, 2, 2);
        $year = substr($date_part, 4, 2);
        
        // Adjust gender encoding (1-6 for day)
        if ($day > 40) {
            $day = $day - 40;
            $gender = 'Perempuan';
        } else {
            $gender = 'Laki-laki';
        }
        
        // Determine century for year
        $current_year = date('Y');
        $century = floor($current_year / 100) * 100;
        $full_year = $century + $year;
        
        // If year is in future, adjust century
        if ($full_year > $current_year) {
            $full_year = ($century - 100) + $year;
        }
        
        // Validate date
        $birth_date = sprintf('%04d-%02d-%02d', $full_year, $month, $day);
        $date_check = validateTanggalYudisium($birth_date);
        
        if (!$date_check['valid']) {
            return array(
                'valid' => FALSE,
                'message' => 'Tanggal lahir dalam NIK tidak valid',
                'data' => NULL
            );
        }
        
        return array(
            'valid' => TRUE,
            'message' => 'NIK valid',
            'data' => array(
                'province_code' => $province_code,
                'city_code' => $city_code,
                'district_code' => $district_code,
                'birth_date' => $birth_date,
                'gender' => $gender,
                'unique_code' => $unique_part,
                'formatted' => $province_code . '.' . $city_code . '.' . $district_code . '.' . $birth_date
            )
        );
    }
}

if (!function_exists('validateIPK')) {
    /**
     * Validasi IPK (Indeks Prestasi Kumulatif)
     * 
     * @param float|string $ipk IPK value
     * @return array ['valid' => bool, 'message' => string, 'normalized' => float|null]
     */
    function validateIPK($ipk)
    {
        if (empty($ipk)) {
            return array(
                'valid' => FALSE,
                'message' => 'IPK tidak boleh kosong',
                'normalized' => NULL
            );
        }
        
        $ipk_float = floatval(str_replace(',', '.', $ipk));
        
        if ($ipk_float < 0 || $ipk_float > 4) {
            return array(
                'valid' => FALSE,
                'message' => 'IPK harus antara 0.00 - 4.00',
                'normalized' => NULL
            );
        }
        
        return array(
            'valid' => TRUE,
            'message' => 'IPK valid',
            'normalized' => round($ipk_float, 2)
        );
    }
}

if (!function_exists('validatePhone')) {
    /**
     * Validasi nomor telepon Indonesia
     * 
     * @param string $phone Phone number
     * @return array ['valid' => bool, 'message' => string, 'normalized' => string|null]
     */
    function validatePhone($phone)
    {
        if (empty($phone)) {
            return array(
                'valid' => FALSE,
                'message' => 'Nomor telepon tidak boleh kosong',
                'normalized' => NULL
            );
        }
        
        // Remove spaces, dashes, parentheses
        $phone_clean = preg_replace('/[\s\-\(\)]/', '', $phone);
        
        // Handle international prefix
        if (substr($phone_clean, 0, 3) === '+62') {
            $phone_clean = '0' . substr($phone_clean, 3);
        } elseif (substr($phone_clean, 0, 2) === '62') {
            $phone_clean = '0' . substr($phone_clean, 2);
        }
        
        // Indonesian phone patterns
        $patterns = array(
            '/^08[1-9][0-9]{7,10}$/', // Mobile (08xx)
            '/^0[2-9][0-9]{7,9}$/'    // Landline
        );
        
        $valid = FALSE;
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $phone_clean)) {
                $valid = TRUE;
                break;
            }
        }
        
        if (!$valid) {
            return array(
                'valid' => FALSE,
                'message' => 'Format nomor telepon tidak valid',
                'normalized' => NULL
            );
        }
        
        return array(
            'valid' => TRUE,
            'message' => 'Nomor telepon valid',
            'normalized' => $phone_clean
        );
    }
}
