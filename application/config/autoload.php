<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Autoload Configuration Files
|--------------------------------------------------------------------------
| PERBAIKAN: Hapus 'encryption' dari config autoload karena file
| encryption.php meng-override $config['encryption_key'] menjadi NULL,
| yang merusak session. Config 'encryption' hanya perlu di-load jika
| benar-benar digunakan oleh library Encryption secara khusus.
*/
$autoload['config'] = array();

/*
|--------------------------------------------------------------------------
| Autoload Libraries
|--------------------------------------------------------------------------
| PERBAIKAN:
| - Hapus 'tracer_encryption' dari autoload - load on demand di controller
|   yang membutuhkan enkripsi PII (alumni, stakeholder, dll)
| - Hapus 'auth_lib' dari autoload - Auth_lib membutuhkan HMVC module loader
|   untuk load User_model, tapi saat autoload HMVC belum siap sepenuhnya.
|   Auth_lib di-load via constructor MY_Controller (setelah routing selesai)
| - 'encryption' library CI tetap di autoload karena dibutuhkan session
*/
$autoload['libraries'] = array(
    'database',
    'session',
    'form_validation',
    'encryption',
);

/*
|--------------------------------------------------------------------------
| Autoload Helper Files
|--------------------------------------------------------------------------
*/
$autoload['helper'] = array(
    'url',
    'file',
    'security',
    'form',
    'text',
    'date',
    'tracer_validation',
    'tracer_audit',
);

/*
|--------------------------------------------------------------------------
| Autoload Models
|--------------------------------------------------------------------------
| Untuk HMVC, model di-load per modul/controller.
*/
$autoload['model'] = array();
