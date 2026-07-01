<?php
/**
 * CodeIgniter 3 HMVC - Front Controller
 * 
 * This file serves as the main entry point for all requests.
 * It initializes the CodeIgniter framework and routes requests
 * to appropriate controllers.
 */

// Set error reporting based on environment
define('ENVIRONMENT', isset($_SERVER['CI_ENV']) ? $_SERVER['CI_ENV'] : 'development');

switch (ENVIRONMENT) {
    case 'development':
        error_reporting(-1);
        ini_set('display_errors', 1);
        break;

    case 'testing':
    case 'production':
        ini_set('display_errors', 0);
        if (version_compare(PHP_VERSION, '5.3', '>=')) {
            error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
        } else {
            error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
        }
        break;

    default:
        header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
        echo 'The application environment is not set correctly.';
        exit(1);
}

// Set time zone
date_default_timezone_set('Asia/Jakarta');

// Application path constants
define('APPPATH', dirname(__FILE__) . '/application/');
define('BASEPATH', dirname(__FILE__) . '/system/');
define('FCPATH', dirname(__FILE__) . '/');
define('SYSDIR', basename(BASEPATH));
define('VIEWPATH', APPPATH . 'views/');
define('MODULEPATH', APPPATH . 'modules/');

// Include CodeIgniter core
if (file_exists(BASEPATH . 'core/CodeIgniter.php')) {
    require_once BASEPATH . 'core/CodeIgniter.php';
} else {
    header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
    echo 'The system folder is not properly configured.';
    exit(1);
}
