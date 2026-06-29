<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * MY_Controller - Base Controller for HMVC
 *
 * This is the base controller that all other controllers will extend.
 * It provides common functionality across the application.
 *
 * @package     Tracer Study
 * @subpackage  Core
 * @category    Core
 * @author      Tracer Study Team
 */

class MY_Controller extends CI_Controller
{
    /**
     * Data yang akan dikirim ke view
     * @var array
     */
    protected $data = array();

    /**
     * Status autentikasi user
     * @var bool
     */
    protected $is_logged_in = FALSE;

    /**
     * Data user yang login
     * @var object|null
     */
    protected $user_data = NULL;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        // Load common libraries
        $this->load->library('session');
        $this->load->helper(array('url', 'form', 'security'));

        // Load Auth library - available in all controllers
        // PERBAIKAN: Dipindahkan dari autoload ke sini agar HMVC sudah siap
        // saat Auth_lib mencoba load User_model dari modul auth/
        $this->load->library('auth_lib');

        // Check if user is logged in
        $this->is_logged_in = $this->session->has_userdata('user_id')
                              && $this->session->userdata('logged_in') === TRUE;

        if ($this->is_logged_in) {
            // PERBAIKAN: Auth controller tidak menyimpan 'user_data' key terpisah,
            // melainkan menyimpan langsung (user_id, username, role, dll).
            // Bangun object user_data dari session keys yang ada.
            $this->user_data = (object) array(
                'id'                => $this->session->userdata('user_id'),
                'username'          => $this->session->userdata('username'),
                'email'             => $this->session->userdata('email'),
                'role'              => $this->session->userdata('role'),
                'profile_id'        => $this->session->userdata('profile_id'),
                'is_email_verified' => $this->session->userdata('is_email_verified'),
            );
        }

        // Set default data for views
        $this->data['base_url'] = base_url();
        $this->data['site_title'] = 'Sistem Tracer Study v3.1';
        $this->data['current_year'] = date('Y');
        $this->data['is_logged_in'] = $this->is_logged_in;
        $this->data['user_data'] = $this->user_data;

        // CATATAN: CSRF verification ditangani otomatis oleh CI
        // ketika csrf_protection = TRUE di config.php.
        // Jangan panggil $this->security->csrf_verify() manual
        // karena akan menyebabkan double-check dan false positive error.
    }

    /**
     * Render view dengan layout
     *
     * @param string $view Nama file view
     * @param array  $data Data untuk view
     * @param bool   $return Jika TRUE, return sebagai string
     * @return string|void
     */
    protected function render($view, $data = array(), $return = FALSE)
    {
        $this->data = array_merge($this->data, $data);

        if ($return) {
            return $this->load->view($view, $this->data, TRUE);
        }

        $this->load->view($view, $this->data);
    }

    /**
     * Redirect dengan pesan flashdata
     *
     * @param string $url URL tujuan
     * @param string $message Pesan flashdata
     * @param string $type Tipe pesan (success, error, warning, info)
     * @return void
     */
    protected function redirect_with_message($url, $message, $type = 'success')
    {
        $this->session->set_flashdata('message', $message);
        $this->session->set_flashdata('message_type', $type);
        redirect($url);
    }

    /**
     * Format response JSON
     *
     * @param mixed  $data Data response
     * @param string $status Status response
     * @param string $message Pesan response
     * @return void
     */
    protected function json_response($data = NULL, $status = 'success', $message = '')
    {
        $response = array(
            'status' => $status,
            'message' => $message,
            'data' => $data
        );

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    /**
     * Upload file helper
     *
     * @param string $field Nama field input
     * @param string $path Path upload
     * @param array  $config Konfigurasi upload
     * @return array|bool
     */
    protected function upload_file($field, $path, $config = array())
    {
        $default_config = array(
            'upload_path'   => FCPATH . 'public/uploads/' . $path,
            'allowed_types' => 'gif|jpg|png|jpeg|pdf|doc|docx|xls|xlsx',
            'max_size'      => 2048,
            'encrypt_name'  => TRUE
        );

        $config = array_merge($default_config, $config);

        // Create directory if not exists
        if (!file_exists($config['upload_path'])) {
            mkdir($config['upload_path'], 0755, TRUE);
        }

        $this->load->library('upload', $config);

        if ($this->upload->do_upload($field)) {
            return $this->upload->data();
        }

        return array('error' => $this->upload->display_errors());
    }
}

/**
 * Admin_Controller - Base Controller untuk Admin Area
 */
class Admin_Controller extends MY_Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        // Redirect ke login jika belum login
        if (!$this->is_logged_in) {
            redirect('auth/login');
        }

        // PERBAIKAN: Role yang diizinkan akses admin area
        // (sebelumnya hanya cek 'admin' tapi role sebenarnya adalah
        //  'super_admin' dan 'admin_pusat_karir')
        $admin_roles = array('super_admin', 'admin_pusat_karir', 'admin');
        $user_role   = $this->session->userdata('role');

        if (!in_array($user_role, $admin_roles)) {
            show_error('Akses ditolak. Anda tidak memiliki hak akses ke halaman ini.', 403);
        }

        $this->data['page_title'] = 'Admin Panel';
    }
}

/**
 * Public_Controller - Base Controller untuk Public Area
 */
class Public_Controller extends MY_Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        // Public area - no authentication required
        $this->data['page_title'] = 'Public Area';
    }
}

/**
 * API_Controller - Base Controller untuk API
 */
class API_Controller extends MY_Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        // API specific initialization
        $this->output->set_content_type('application/json');
    }

    /**
     * Override json_response untuk API
     */
    protected function json_response($data = NULL, $status = 'success', $message = '', $code = 200)
    {
        $response = array(
            'status' => $status,
            'message' => $message,
            'data' => $data
        );

        $this->output
            ->set_status_header($code)
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }
}
