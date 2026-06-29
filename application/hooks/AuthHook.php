<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * AuthHook - Authentication Hook
 *
 * Handles authentication checks before controller execution.
 *
 * @package     Tracer Study
 * @subpackage  Hooks
 * @category    Hooks
 * @author      Tracer Study Team
 */

class AuthHook
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
     * Check if user is authenticated
     * PERBAIKAN: Menggunakan URI string, bukan router class,
     * karena pada HMVC nama class bisa berupa "auth" (module name)
     * sementara controller sebenarnya ada di dalam modul.
     */
    public function check_auth()
    {
        $uri = $this->CI->uri->uri_string();

        // Definisi URI publik yang tidak perlu autentikasi
        $public_uri_prefixes = array(
            'auth/login',
            'auth/auth/login',
            'auth/register',
            'auth/auth/register',
            'auth/forgot-password',
            'auth/auth/forgot_password',
            'auth/reset-password',
            'auth/auth/reset_password',
            'auth/verify-email',
            'auth/auth/verify_email',
            'auth/auth/verifyEmail',
            'stakeholder/verify',
            'stakeholder/stakeholder/verify',
        );

        // Juga izinkan akses ke root / default controller (redirect ke login)
        if (empty($uri) || $uri === '/') {
            return;
        }

        foreach ($public_uri_prefixes as $prefix) {
            if (strpos($uri, $prefix) === 0) {
                return;
            }
        }

        // Cek apakah sudah login
        $is_logged_in = $this->CI->session->has_userdata('user_id') 
                        && $this->CI->session->userdata('logged_in');

        if (!$is_logged_in) {
            if ($this->CI->input->is_ajax_request() || strpos($uri, 'api/') !== FALSE) {
                $this->CI->output
                    ->set_status_header(401)
                    ->set_content_type('application/json')
                    ->set_output(json_encode(array(
                        'status'  => 'error',
                        'message' => 'Unauthorized. Please login first.'
                    )));
                exit;
            }

            $this->CI->session->set_flashdata('info', 'Silakan login untuk mengakses halaman ini.');
            redirect('auth/login');
        }
    }

    /**
     * Set user data for views
     *
     * This hook runs after controller constructor.
     * It makes user data available to all views.
     *
     * @return void
     */
    public function set_user_data()
    {
        if ($this->CI->session->has_userdata('user_id')) {
            $user_data = $this->CI->session->userdata('user_data');

            // Make user data available to all views
            $this->CI->load->vars(array(
                'current_user' => $user_data,
                'user_id' => $this->CI->session->userdata('user_id'),
                'user_role' => $this->CI->session->userdata('role'),
                'is_logged_in' => TRUE
            ));
        } else {
            $this->CI->load->vars(array(
                'current_user' => NULL,
                'user_id' => NULL,
                'user_role' => NULL,
                'is_logged_in' => FALSE
            ));
        }
    }

    /**
     * Check user role/permission
     *
     * @param array $roles Allowed roles
     * @return bool
     */
    public function check_role($roles = array())
    {
        if (!is_array($roles)) {
            $roles = array($roles);
        }

        $user_role = $this->CI->session->userdata('role');

        if (empty($roles) || in_array($user_role, $roles)) {
            return TRUE;
        }

        return FALSE;
    }
}
