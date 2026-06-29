<?php
/**
 * Diagnostic file - hapus setelah selesai setup!
 * Akses via: http://localhost/tracerstudy/public/check.php
 */

echo "<pre style='font-family:monospace;padding:20px'>";
echo "<h2>Tracer Study - Diagnostic Check</h2>";

echo "<h3>1. PHP & Server Info</h3>";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Server: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'unknown') . "\n";
echo "OS: " . PHP_OS . "\n";
echo "SCRIPT_FILENAME: " . ($_SERVER['SCRIPT_FILENAME'] ?? '-') . "\n";
echo "DOCUMENT_ROOT: " . ($_SERVER['DOCUMENT_ROOT'] ?? '-') . "\n";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? '-') . "\n";
echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? '-') . "\n\n";

echo "<h3>2. Path Constants (jika CI sudah load)</h3>";
$root = dirname(__DIR__);
echo "Project root  : $root\n";
echo "APPPATH would : $root/application/\n";
echo "BASEPATH would: $root/system/\n";
echo "FCPATH would  : $root/public/\n\n";

echo "<h3>3. Critical Files Check</h3>";
$files = [
    'system/core/CodeIgniter.php',
    'system/core/Router.php',
    'application/core/MY_Router.php',
    'application/core/MY_Loader.php',
    'application/core/MY_Controller.php',
    'application/third_party/MX/Router.php',
    'application/third_party/MX/Loader.php',
    'application/config/config.php',
    'application/config/autoload.php',
    'application/config/hooks.php',
    'application/config/routes.php',
    'application/hooks/AuthHook.php',
    'application/hooks/AuditHook.php',
    'application/hooks/MaintenanceHook.php',
    'application/modules/auth/controllers/Auth.php',
    'application/modules/auth/models/User_model.php',
    'application/libraries/Auth_lib.php',
    'vendor/autoload.php',
];
foreach ($files as $f) {
    $path = $root . '/' . $f;
    $exists = file_exists($path);
    echo ($exists ? "✓" : "✗ MISSING") . "  $f\n";
}

echo "\n<h3>4. mod_rewrite Check</h3>";
if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    echo in_array('mod_rewrite', $modules) ? "✓ mod_rewrite AKTIF\n" : "✗ mod_rewrite TIDAK AKTIF\n";
    echo in_array('mod_headers', $modules) ? "✓ mod_headers AKTIF\n" : "⚠ mod_headers tidak aktif (opsional)\n";
} else {
    echo "⚠ Tidak bisa cek modul Apache (php-fpm atau CGI mode)\n";
}

echo "\n<h3>5. Database Connection</h3>";
$host = 'localhost'; $user = 'root'; $pass = ''; $db = 'tracer_study_v31';
$conn = @new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo "✗ Database gagal: " . $conn->connect_error . "\n";
    echo "  Pastikan:\n";
    echo "  - MySQL/MariaDB sudah running di XAMPP\n";
    echo "  - Database 'tracer_study_v31' sudah dibuat\n";
    echo "  - SQL schema sudah di-import\n";
} else {
    echo "✓ Database OK - Connected ke '$db'\n";
    // Check tables
    $tables = ['users','ci_sessions','login_attempts','activity_logs'];
    foreach ($tables as $t) {
        $r = $conn->query("SHOW TABLES LIKE '$t'");
        echo ($r && $r->num_rows > 0 ? "  ✓" : "  ✗ MISSING") . " tabel: $t\n";
    }
    $conn->close();
}

echo "\n<h3>6. RewriteBase Suggestion</h3>";
$script = $_SERVER['SCRIPT_NAME'] ?? '';
$base = str_replace('check.php', '', $script);
echo "Akses URL saat ini: " . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $script . "\n";
echo "Rekomendasi RewriteBase di public/.htaccess: $base\n";

echo "\n<b>⚠ HAPUS file ini setelah selesai diagnosa!</b>";
echo "</pre>";

// Extra: show modules_locations value
echo "\n<h3>7. HMVC Modules Path Test</h3>";
$modules_path = str_replace('\\', '/', realpath(dirname(__DIR__) . '/application/modules')) . '/';
echo "Modules path: $modules_path\n";
echo is_dir($modules_path) ? "✓ Folder modules/ ditemukan\n" : "✗ Folder modules/ TIDAK ditemukan\n";
echo is_dir($modules_path . 'auth/') ? "✓ Module auth/ ada\n" : "✗ Module auth/ TIDAK ada\n";
echo is_dir($modules_path . 'auth/controllers/') ? "✓ auth/controllers/ ada\n" : "✗ auth/controllers/ TIDAK ada\n";
echo is_file($modules_path . 'auth/controllers/Auth.php') ? "✓ Auth.php ada\n" : "✗ Auth.php TIDAK ada\n";

// Test APPPATH normalization (simulating what HMVC Router does)
$apppath = str_replace('\\', '/', realpath(dirname(__DIR__) . '/application'));
echo "\nAPPPATH (normalized): $apppath\n";
echo "modules_locations would be: $modules_path\n";
$relative_test = str_replace($apppath . '/', '../', $modules_path);
echo "Relative path would be: $relative_test\n";
