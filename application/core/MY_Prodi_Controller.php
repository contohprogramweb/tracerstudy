<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Prodi Controller - Base Controller untuk Admin Prodi/Fakultas/Dosen
 * 
 * Base controller untuk modul prodi dengan akses terbatas berdasarkan role
 */
class MY_Prodi_Controller extends MY_Controller {
    
    protected $prodi_id = null;
    
    public function __construct() {
        parent::__construct();
        
        // Redirect ke login jika belum login
        if (!$this->is_logged_in) {
            redirect('auth/login');
            exit;
        }
        
        // Cek role yang diizinkan akses modul prodi
        $allowed_roles = array('admin_prodi', 'admin_fakultas', 'dosen');
        $user_role = $this->session->userdata('role');
        
        if (!in_array($user_role, $allowed_roles)) {
            show_error('Akses ditolak. Anda tidak memiliki hak akses ke halaman ini.', 403);
            exit;
        }
        
        // Get prodi_id dari session atau profile_id
        $this->prodi_id = $this->session->userdata('profile_id');
        
        if (!$this->prodi_id && $user_role === 'admin_prodi') {
            // Admin prodi wajib punya profile_id
            // Cek apakah user ini adalah user testing/development
            $username = $this->session->userdata('username');
            
            // Untuk development, izinkan akses dengan warning jika username mengandung 'test' atau 'demo'
            if (strpos(strtolower($username), 'test') !== false || strpos(strtolower($username), 'demo') !== false) {
                // Set prodi_id default untuk testing (ambil prodi pertama dari database)
                $this->db->select('id')->from('prodi')->limit(1);
                $default_prodi = $this->db->get()->row();
                if ($default_prodi) {
                    $this->prodi_id = $default_prodi->id;
                    $this->session->set_userdata('profile_id', $this->prodi_id);
                } else {
                    show_error('Tidak ada data program studi di database. Silakan tambahkan data prodi terlebih dahulu.', 403);
                    exit;
                }
            } else {
                show_error('Konfigurasi akun tidak lengkap. User admin_prodi harus memiliki profile_id. Hubungi administrator untuk mengaitkan akun Anda dengan program studi.', 403);
                exit;
            }
        }
        
        $this->data['page_title'] = 'Dashboard Prodi';
        $this->data['prodi_id'] = $this->prodi_id;
    }
}
