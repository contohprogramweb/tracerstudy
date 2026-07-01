<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Profile Controller - Modul Prodi
 * 
 * Menangani halaman profil untuk user admin_prodi, admin_fakultas, dan dosen
 */
class Profile extends MY_Prodi_Controller {
    
    public function __construct()
    {
        parent::__construct();
        // Load model dari modul auth dengan path yang benar untuk HMVC
        $this->load->model('auth/User_model', 'User_model');
    }
    
    /**
     * Halaman Profil User
     */
    public function index()
    {
        $user_id = $this->session->userdata('user_id');
        
        if (empty($user_id)) {
            show_error('Session tidak valid. Silakan login ulang.', 401);
            return;
        }
        
        $user = $this->User_model->getUserById($user_id);
        
        if (!$user || empty($user->id)) {
            log_message('error', 'User tidak ditemukan untuk user_id: ' . $user_id);
            show_error('User tidak ditemukan di database.', 404);
            return;
        }
        
        $data = array(
            'page_title'    => 'Profil Saya - Tracer Study',
            'page_subtitle' => 'Informasi akun Anda',
            'user'          => $user,
            'user_data'     => $this->user_data,
        );
        
        $this->load->view('prodi/profile', $data);
    }
    
    /**
     * Ganti Password
     */
    public function changePassword()
    {
        if ($this->input->method() === 'post') {
            $user_id      = $this->session->userdata('user_id');
            $old_password = $this->input->post('old_password');
            $new_password = $this->input->post('new_password');
            $confirm      = $this->input->post('confirm_password');
            
            if ($new_password !== $confirm) {
                $this->session->set_flashdata('error', 'Konfirmasi password tidak cocok.');
                redirect('prodi/profile/change-password');
            }
            
            $user = $this->User_model->getUserById($user_id);
            
            if (!$user || !password_verify($old_password, $user->password_hash)) {
                $this->session->set_flashdata('error', 'Password lama tidak benar.');
                redirect('prodi/profile/change-password');
            }
            
            $this->User_model->updateUser($user_id, array('password' => $new_password));
            $this->session->set_flashdata('success', 'Password berhasil diubah.');
            redirect('prodi/profile');
        }
        
        $data = array(
            'page_title'    => 'Ganti Password - Tracer Study',
            'page_subtitle' => 'Ubah password akun Anda',
            'user_data'     => $this->user_data,
        );
        
        $this->load->view('prodi/change_password', $data);
    }
}
