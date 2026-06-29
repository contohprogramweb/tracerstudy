<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Configuration untuk Encryption Library
 * 
 * Konfigurasi ini digunakan oleh CI3 Encryption library dan
 * Tracer_encryption library wrapper.
 * 
 * @package     Tracer Study
 * @subpackage  Config
 * @category    Encryption
 * @author      Tracer Study Team
 * @version     3.1.0
 */

// ============================================================================
// ENCRYPTION CONFIGURATION
// ============================================================================

/**
 * Encryption Driver
 * 
 * Pilihan: 'OpenSSL' atau 'Mcrypt' (deprecated)
 * OpenSSL direkomendasikan untuk PHP 7.2+
 */
$config['encryption_driver'] = 'OpenSSL';

/**
 * Encryption Cipher
 * 
 * Cipher yang digunakan untuk enkripsi AES-256.
 * Pilihan yang aman:
 * - 'aes-256-cbc' (recommended)
 * - 'aes-128-cbc'
 * - 'aes-256-gcm' (jika tersedia, lebih aman dengan authentication)
 */
$config['encryption_cipher'] = 'aes-256-cbc';

/**
 * Encryption Mode
 * 
 * Mode operasi cipher. CBC adalah mode yang paling umum digunakan.
 */
$config['encryption_mode'] = 'cbc';

/**
 * Encryption Key
 * 
 * Kunci enkripsi harus:
 * - 32 bytes untuk AES-256
 * - 16 bytes untuk AES-128
 * - Disimpan dengan aman, jangan di-commit ke version control
 * 
 * Jika NULL, akan menggunakan nilai dari config['encryption_key'] utama
 */
// PERBAIKAN: Jangan set encryption_key di sini - gunakan nilai dari config.php utama
// $config['encryption_key'] sudah di-set di config.php dengan nilai statis

/**
 * Encryption HMAC Key
 * 
 * Kunci untuk HMAC signature (untuk memverifikasi integritas data).
 * Jika NULL, akan digenerate otomatis dari encryption key.
 * 
 * Untuk keamanan maksimal, gunakan key terpisah sepanjang 32 bytes.
 */
$config['encryption_hmac_key'] = NULL;

/**
 * Encryption HMAC Digest
 * 
 * Algoritma hash untuk HMAC.
 * Pilihan: 'SHA512' (recommended), 'SHA256', 'SHA1'
 */
$config['encryption_hmac_digest'] = 'SHA512';

/**
 * Base64 Encoding
 * 
 * Apakah hasil enkripsi di-encode dengan base64?
 * TRUE = output lebih pendek, aman untuk storage di database
 * FALSE = output binary, lebih efisien untuk transfer
 */
$config['encryption_base64'] = TRUE;

/**
 * OpenSSL Options
 * 
 * Opsi tambahan untuk OpenSSL (jika menggunakan driver OpenSSL)
 */
$config['openssl_options'] = array(
    'encrypt_method' => 'AES-256-CBC',
    'raw_data' => FALSE, // FALSE = base64 encode otomatis
);

// ============================================================================
// PII (Personally Identifiable Information) FIELDS
// ============================================================================

/**
 * Daftar field yang dianggap sebagai PII dan harus dienkripsi
 * 
 * Field-field ini akan otomatis dienkripsi saat insert/update
 * melalui MY_Model jika ada di tabel yang bersangkutan.
 */
$config['pii_fields'] = array(
    'nik',
    'nik_encrypted',
    'no_hp',
    'nomor_telepon',
    'alamat',
    'alamat_domisili',
    'gaji',
    'gaji_aktual',
    'email_pribadi',
    'tanggal_lahir',
    'tempat_lahir',
    'nama_ibu_kandung',
    'nama_ayah',
    'npwp',
    'rekening_bank',
    'no_paspor',
    'no_sim',
    'no_kk'
);

// ============================================================================
// MASKING CONFIGURATION
// ============================================================================

/**
 * Default masking character
 * 
 * Karakter yang digunakan untuk masking PII di log/display
 */
$config['masking_char'] = '*';

/**
 * Masking rules per field type
 * 
 * Format: field_type => array('show_first' => N, 'show_last' => M)
 * show_first = jumlah karakter yang ditampilkan di awal
 * show_last = jumlah karakter yang ditampilkan di akhir
 * 
 * Jika kedua nilai NULL, maka semua karakter di-mask
 */
$config['masking_rules'] = array(
    'nik' => array('show_first' => 4, 'show_last' => 4),
    'phone' => array('show_first' => 4, 'show_last' => 4),
    'email' => array('show_first' => 1, 'show_last' => 0), // Show first char + domain
    'address' => array('show_first' => 10, 'show_last' => 0),
    'date' => array('show_first' => 4, 'show_last' => 0), // Show year only
    'default' => array('show_first' => NULL, 'show_last' => NULL), // Mask all
);

// ============================================================================
// AUDIT LOGGING
// ============================================================================

/**
 * Enable audit logging untuk PII access
 * 
 * Jika TRUE, setiap akses decrypt PII akan dicatat di activity_logs
 */
$config['audit_pii_access'] = TRUE;

/**
 * Hash algorithm untuk audit trail
 * 
 * Digunakan untuk hash nilai PII sebelum disimpan di log
 */
$config['audit_hash_algorithm'] = 'sha256';

/**
 * Salt untuk audit hash
 * 
 * Jika NULL, akan menggunakan encryption_key sebagai salt
 */
$config['audit_hash_salt'] = NULL;

// ============================================================================
// KEY ROTATION (Advanced)
// ============================================================================

/**
 * Enable key rotation support
 * 
 * Jika TRUE, sistem akan mendukung multiple encryption keys
 * untuk keperluan rotasi kunci secara berkala.
 */
$config['enable_key_rotation'] = FALSE;

/**
 * Current key version
 * 
 * Version dari encryption key yang sedang aktif.
 * Increment nilai ini saat melakukan rotasi kunci.
 */
$config['current_key_version'] = 1;

/**
 * Available keys
 * 
 * Array of encryption keys dengan version sebagai key.
 * Hanya digunakan jika enable_key_rotation = TRUE.
 * 
 * Format: array(
 *     1 => 'key_for_version_1',
 *     2 => 'key_for_version_2',
 * )
 */
$config['encryption_keys'] = array();

// ============================================================================
// SECURITY SETTINGS
// ============================================================================

/**
 * Minimum PHP version requirement
 * 
 * Enkripsi AES-256 memerlukan PHP 7.2+ untuk keamanan optimal
 */
$config['min_php_version'] = '7.2.0';

/**
 * Check for secure random number generator
 * 
 * Pastikan random_int() atau openssl_random_pseudo_bytes() tersedia
 */
$config['require_secure_random'] = TRUE;

/**
 * Log encryption errors
 * 
 * Apakah error enkripsi/dekripsi dicatat ke log?
 */
$config['log_encryption_errors'] = TRUE;

/**
 * Fail silently on decryption error
 * 
 * Jika TRUE, dekripsi yang gagal akan return FALSE tanpa error
 * Jika FALSE, akan throw exception
 */
$config['fail_silent_on_decrypt_error'] = TRUE;

// ============================================================================
// PERFORMANCE SETTINGS
// ============================================================================

/**
 * Cache decrypted values
 * 
 * Apakah hasil dekripsi di-cache untuk performa?
 * Hati-hati: dapat meningkatkan penggunaan memory
 */
$config['cache_decrypted_values'] = FALSE;

/**
 * Cache TTL (Time To Live)
 * 
 * Berapa lama (dalam detik) cache decrypted values bertahan
 */
$config['decryption_cache_ttl'] = 300; // 5 menit

/**
 * Batch encryption limit
 * 
 * Jumlah maksimum record yang dapat dienkripsi/didekripsi dalam satu batch
 */
$config['batch_encryption_limit'] = 1000;
