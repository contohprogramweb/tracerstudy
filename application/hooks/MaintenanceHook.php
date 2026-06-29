<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * MaintenanceHook - Maintenance Mode Hook
 *
 * PERBAIKAN: Hook ini harus menggunakan pre_controller, BUKAN pre_system.
 * Pada tahap pre_system, CodeIgniter belum diinisialisasi sehingga
 * get_instance() akan gagal / tidak tersedia.
 */

class MaintenanceHook
{
    private $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
    }

    /**
     * Check if system is in maintenance mode
     * Dipanggil via hook pre_controller (bukan pre_system)
     */
    public function check_maintenance()
    {
        $maintenance_enabled = FALSE;

        // Option 1: Check via config item
        if (config_item('maintenance_mode') === TRUE) {
            $maintenance_enabled = TRUE;
        }

        // Option 2: Check via maintenance config file
        if (!$maintenance_enabled && file_exists(APPPATH . 'config/maintenance.php')) {
            include(APPPATH . 'config/maintenance.php');
            if (isset($maintenance_mode) && $maintenance_mode === TRUE) {
                $maintenance_enabled = TRUE;
            }
        }

        // Option 3: Check via file flag
        if (!$maintenance_enabled && file_exists(FCPATH . '.maintenance')) {
            $maintenance_enabled = TRUE;
        }

        if (!$maintenance_enabled) {
            return;
        }

        // Get current URI
        $uri = $this->CI->uri->uri_string();

        // Allow login page during maintenance
        $allowed_uris = array('auth/login', 'auth/auth/login', 'maintenance', 'maintenance/index');
        foreach ($allowed_uris as $allowed) {
            if (strpos($uri, $allowed) !== FALSE) {
                return;
            }
        }

        // Admin bypass maintenance
        if ($this->CI->session->has_userdata('user_id')) {
            $role = $this->CI->session->userdata('role');
            if (in_array($role, array('admin', 'super_admin', 'superadmin'))) {
                $this->CI->session->set_flashdata('warning', 'Sistem sedang dalam mode maintenance.');
                return;
            }
        }

        // API / AJAX request
        if ($this->CI->input->is_ajax_request() || strpos($uri, 'api/') !== FALSE) {
            $this->CI->output
                ->set_status_header(503)
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'status'  => 'error',
                    'message' => 'Sistem sedang dalam maintenance. Silakan coba lagi nanti.',
                    'code'    => 503
                )));
            exit;
        }

        // Show maintenance page
        $site_title   = config_item('site_title') ?: 'Sistem Tracer Study';
        $current_year = date('Y');

        $this->CI->output
            ->set_status_header(503)
            ->set_content_type('text/html')
            ->set_output($this->_get_maintenance_page($site_title, $current_year));
        exit;
    }

    private function _get_maintenance_page($site_title, $current_year)
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance - {$site_title}</title>
    <style>
        body { font-family: sans-serif; background: #667eea; min-height:100vh;
               display:flex; align-items:center; justify-content:center; }
        .box { background:#fff; padding:2rem; border-radius:10px; text-align:center; max-width:480px; }
        h1   { color:#667eea; }
        p    { color:#666; }
    </style>
</head>
<body>
    <div class="box">
        <div style="font-size:3rem">🔧</div>
        <h1>Sedang Dalam Perbaikan</h1>
        <p>Sistem sedang dalam maintenance. Silakan kembali lagi dalam beberapa saat.</p>
        <p style="font-size:.8rem;color:#999">&copy; {$current_year} {$site_title}</p>
    </div>
</body>
</html>
HTML;
    }
}
