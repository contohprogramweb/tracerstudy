<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Hooks Configuration
|--------------------------------------------------------------------------
|
| This file lets you define hooks to extend the core functionality.
| See: https://codeigniter.com/userguide3/general/hooks.html
|
*/

// AuthHook - Check authentication before accessing protected pages
$hook['pre_controller'] = array(
    'class'    => 'AuthHook',
    'function' => 'check_auth',
    'filename' => 'AuthHook.php',
    'filepath' => 'hooks'
);

// AuditHook - Log user activities for audit trail
$hook['post_controller'] = array(
    'class'    => 'AuditHook',
    'function' => 'log_activity',
    'filename' => 'AuditHook.php',
    'filepath' => 'hooks'
);

// MaintenanceHook - Check if system is in maintenance mode
$hook['pre_controller'][] = array(
    'class'    => 'MaintenanceHook',
    'function' => 'check_maintenance',
    'filename' => 'MaintenanceHook.php',
    'filepath' => 'hooks'
);

// Additional hook for session handling
$hook['post_controller_constructor'][] = array(
    'class'    => 'AuthHook',
    'function' => 'set_user_data',
    'filename' => 'AuthHook.php',
    'filepath' => 'hooks'
);
