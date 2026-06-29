<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Settings Controller - Pengaturan Sistem untuk Admin/Superadmin
 */
class Settings extends Admin_Controller {

    public function __construct() {
        parent::__construct();
        
        // Hanya super_admin yang bisa akses settings
        $this->auth_lib->requireRole(['super_admin']);
        
        $this->load->helper('tracer_audit');
        $this->load->library('form_validation');
    }

    /**
     * Index: Halaman utama pengaturan
     */
    public function index() {
        $data['page_title'] = 'Pengaturan';
        $data['page_subtitle'] = 'Konfigurasi sistem tracer study';
        
        // Get current settings from database
        $this->db->select('*');
        $settings = $this->db->get('system_settings')->result_array();
        
        // Convert to key-value array
        $data['settings'] = [];
        foreach ($settings as $setting) {
            $data['settings'][$setting['setting_key']] = $setting['setting_value'];
        }
        
        // Default values if settings table is empty
        $default_settings = [
            'site_name' => 'Tracer Study System',
            'site_description' => 'Sistem Tracer Study Alumni',
            'logo_text' => 'Tracer Study',
            'primary_color' => '#667eea',
            'secondary_color' => '#764ba2',
            'enable_registration' => '1',
            'require_email_verification' => '1',
            'admin_email' => 'admin@tracerstudy.com',
            'contact_phone' => '',
            'contact_address' => '',
        ];
        
        foreach ($default_settings as $key => $value) {
            if (!isset($data['settings'][$key])) {
                $data['settings'][$key] = $value;
            }
        }
        
        // Load view
        $this->load->view('admin/templates/header', $data);
        $this->load->view('admin/settings/index', $data);
        $this->load->view('admin/templates/footer');
    }
    
    /**
     * Save settings
     */
    public function save() {
        // Validate input
        $this->form_validation->set_rules('site_name', 'Nama Situs', 'required|trim|max_length[255]');
        $this->form_validation->set_rules('admin_email', 'Email Admin', 'required|trim|valid_email|max_length[255]');
        
        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', 'Validasi gagal. Silakan periksa form.');
            redirect('admin/settings');
        }
        
        // Get POST data
        $settings_data = [
            'site_name' => $this->input->post('site_name'),
            'site_description' => $this->input->post('site_description'),
            'logo_text' => $this->input->post('logo_text'),
            'primary_color' => $this->input->post('primary_color'),
            'secondary_color' => $this->input->post('secondary_color'),
            'enable_registration' => $this->input->post('enable_registration') ? '1' : '0',
            'require_email_verification' => $this->input->post('require_email_verification') ? '1' : '0',
            'admin_email' => $this->input->post('admin_email'),
            'contact_phone' => $this->input->post('contact_phone'),
            'contact_address' => $this->input->post('contact_address'),
        ];
        
        // Save each setting
        $updated = 0;
        foreach ($settings_data as $key => $value) {
            // Check if setting exists
            $this->db->where('setting_key', $key);
            $query = $this->db->get('system_settings');
            
            if ($query->num_rows() > 0) {
                // Update existing setting
                $this->db->where('setting_key', $key);
                $this->db->update('system_settings', ['setting_value' => $value]);
            } else {
                // Insert new setting
                $this->db->insert('system_settings', [
                    'setting_key' => $key,
                    'setting_value' => $value,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
            $updated++;
        }
        
        // Log activity
        log_activity($this->auth_lib->getId(), 'update', 'system_settings', 'Pengaturan sistem diperbarui', $this->input->ip_address());
        
        $this->session->set_flashdata('success', "Berhasil memperbarui {$updated} pengaturan.");
        redirect('admin/settings');
    }
    
    /**
     * Reset settings to default
     */
    public function reset() {
        // Only super admin can reset
        $user_role = $this->auth_lib->getRole();
        if ($user_role !== 'super_admin') {
            show_error('Akses ditolak', 403);
        }
        
        $default_settings = [
            'site_name' => 'Tracer Study System',
            'site_description' => 'Sistem Tracer Study Alumni',
            'logo_text' => 'Tracer Study',
            'primary_color' => '#667eea',
            'secondary_color' => '#764ba2',
            'enable_registration' => '1',
            'require_email_verification' => '1',
            'admin_email' => 'admin@tracerstudy.com',
            'contact_phone' => '',
            'contact_address' => '',
        ];
        
        // Save default settings
        foreach ($default_settings as $key => $value) {
            $this->db->replace('system_settings', [
                'setting_key' => $key,
                'setting_value' => $value,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
        
        // Log activity
        log_activity($this->auth_lib->getId(), 'update', 'system_settings', 'Pengaturan sistem direset ke default', $this->input->ip_address());
        
        $this->session->set_flashdata('info', 'Pengaturan telah direset ke nilai default.');
        redirect('admin/settings');
    }
}
