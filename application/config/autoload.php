<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Autoload Configuration Files
|--------------------------------------------------------------------------
|
| This file determines which configuration files are automatically loaded
| when the application starts.
|
*/
$autoload['config'] = array('encryption');

/*
|--------------------------------------------------------------------------
| Autoload Libraries
|--------------------------------------------------------------------------
|
| These files are the core libraries that CodeIgniter uses.
| Libraries are loaded automatically by default.
|
*/
$autoload['libraries'] = array('database', 'session', 'form_validation', 'encryption', 'tracer_encryption', 'auth_lib');

/*
|--------------------------------------------------------------------------
| Autoload Helper Files
|--------------------------------------------------------------------------
|
| Helper files contain functions that assist in common tasks.
|
*/
$autoload['helper'] = array('url', 'file', 'security', 'form', 'text', 'date', 'tracer_validation');

/*
|--------------------------------------------------------------------------
| Autoload Models
|--------------------------------------------------------------------------
|
| Automatically load models when the application starts.
| For HMVC, models are typically loaded per module.
|
*/
$autoload['model'] = array();
