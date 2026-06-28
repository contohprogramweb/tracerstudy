<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * MaintenanceHook - Maintenance Mode Hook
 *
 * Checks if the system is in maintenance mode.
 *
 * @package     Tracer Study
 * @subpackage  Hooks
 * @category    Hooks
 * @author      Tracer Study Team
 */

class MaintenanceHook
{
    /**
     * CodeIgniter instance
     * @var CI_Controller
     */
    private $CI;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->CI =& get_instance();
    }

    /**
     * Check if system is in maintenance mode
     *
     * This hook runs before controller execution.
     * If maintenance mode is enabled, it blocks access to all pages
     * except for admin users and specific maintenance pages.
     *
     * @return void
     */
    public function check_maintenance()
    {
        // Get CodeIgniter instance (available at pre_controller stage)
        $CI =& get_instance();
        
        // Check if maintenance mode is enabled via config or file
        $maintenance_enabled = FALSE;

        // Option 1: Check via config
        if (config_item('maintenance_mode') === TRUE) {
            $maintenance_enabled = TRUE;
        }

        // Option 2: Check via maintenance file
        if (file_exists(APPPATH . 'config/maintenance.php')) {
            include(APPPATH . 'config/maintenance.php');
            if (isset($maintenance_mode) && $maintenance_mode === TRUE) {
                $maintenance_enabled = TRUE;
            }
        }

        // Option 3: Check via file flag
        if (file_exists(FCPATH . '.maintenance')) {
            $maintenance_enabled = TRUE;
        }

        if (!$maintenance_enabled) {
            return;
        }

        // Get current class and method
        $class = $CI->router->class;
        $method = $CI->router->method;

        // Define pages that are accessible during maintenance
        $allowed_pages = array(
            'auth' => array('login'),
            'maintenance' => array('index', 'status')
        );

        // Check if current page is allowed during maintenance
        if (isset($allowed_pages[$class]) && in_array($method, $allowed_pages[$class])) {
            return;
        }

        // Check if user is admin (can bypass maintenance)
        if ($CI->session->has_userdata('user_id')) {
            $user_role = $CI->session->userdata('role');

            if ($user_role === 'admin' || $user_role === 'superadmin') {
                // Admins can bypass maintenance mode
                // But show a notification
                $CI->session->set_flashdata('warning', 'Sistem sedang dalam mode maintenance.');
                return;
            }
        }

        // For API requests, return JSON response
        if ($CI->input->is_ajax_request() || strpos($CI->uri->uri_string(), 'api/') !== FALSE) {
            $CI->output
                ->set_status_header(503)
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'status' => 'error',
                    'message' => 'Sistem sedang dalam maintenance. Silakan coba lagi nanti.',
                    'code' => 503
                )));
            exit;
        }

        // Show maintenance page
        $CI->output
            ->set_status_header(503)
            ->set_content_type('text/html')
            ->set_output($this->_get_maintenance_page());
        exit;
    }

    /**
     * Get maintenance page HTML
     *
     * @return string
     */
    private function _get_maintenance_page()
    {
        $site_title = config_item('site_title') ?: 'Sistem Tracer Study';
        $current_year = date('Y');

        return <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance - {$site_title}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #333;
        }
        .container {
            text-align: center;
            background: white;
            padding: 3rem;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 500px;
            margin: 1rem;
        }
        .icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        h1 {
            color: #667eea;
            margin-bottom: 1rem;
            font-size: 2rem;
        }
        p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }
        .contact {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 5px;
            margin-top: 1.5rem;
        }
        .contact p {
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 1.5rem auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🔧</div>
        <h1>Sedang Dalam Perbaikan</h1>
        <p>
            Maaf, sistem saat ini sedang dalam maintenance untuk peningkatan performa 
            dan penambahan fitur baru.
        </p>
        <div class="spinner"></div>
        <p style="margin-top: 1rem; font-size: 0.9rem;">
            Silakan kembali lagi dalam beberapa saat.
        </p>
        <div class="contact">
            <p><strong>Informasi:</strong></p>
            <p>Untuk bantuan, hubungi tim IT Support</p>
            <p>Email: support@university.ac.id</p>
        </div>
        <p style="margin-top: 2rem; font-size: 0.8rem; color: #999;">
            &copy; {$current_year} {$site_title}
        </p>
    </div>
</body>
</html>
HTML;
    }
}
