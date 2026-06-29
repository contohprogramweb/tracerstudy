<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Dashboard Controller - Halaman Utama Admin/Superadmin
 * 
 * Menampilkan overview sistem tracer study dengan statistik dan informasi penting
 */
class Dashboard extends Admin_Controller {

    public function __construct() {
        parent::__construct();
        
        // Hanya super_admin dan admin_pusat_karir yang bisa akses
        $this->auth_lib->requireRole(['super_admin', 'admin_pusat_karir']);
        
        $this->load->model('auth/user_model');
        $this->load->helper('tracer_audit');
    }

    /**
     * Index: Dashboard utama admin
     */
    public function index() {
        $data['page_title'] = 'Dashboard Admin';
        $data['page_subtitle'] = 'Overview Sistem Tracer Study';
        
        // Statistik umum
        $data['total_users'] = $this->db->count_all('users');
        $data['total_alumni'] = $this->db->count_all('alumni_profiles');
        $data['total_surveys'] = $this->db->count_all('surveys');
        $data['total_responses'] = $this->db->count_all('survey_responses');
        
        // Statistik berdasarkan role
        $this->db->where('role', 'super_admin');
        $data['total_super_admin'] = $this->db->count_all_results('users');
        
        $this->db->where('role', 'admin_pusat_karir');
        $data['total_admin_pusat'] = $this->db->count_all_results('users');
        
        $this->db->where('role', 'admin_prodi');
        $data['total_admin_prodi'] = $this->db->count_all_results('users');
        
        $this->db->where('role', 'admin_fakultas');
        $data['total_admin_fakultas'] = $this->db->count_all_results('users');
        
        $this->db->where('role', 'dosen');
        $data['total_dosen'] = $this->db->count_all_results('users');
        
        $this->db->where('role', 'reviewer');
        $data['total_reviewer'] = $this->db->count_all_results('users');
        
        $this->db->where('role', 'alumni');
        $data['total_alumni_users'] = $this->db->count_all_results('users');
        
        // Statistik stakeholder
        $this->db->where('role', 'stakeholder');
        $data['total_stakeholder'] = $this->db->count_all_results('users');
        
        // Survey aktif
        $this->db->where('status', 'active');
        $data['active_surveys'] = $this->db->count_all_results('surveys');
        
        // Survey draft
        $this->db->where('status', 'draft');
        $data['draft_surveys'] = $this->db->count_all_results('surveys');
        
        // Response rate (sederhana)
        if ($data['total_surveys'] > 0) {
            $data['response_rate'] = round(($data['total_responses'] / ($data['total_surveys'] * 100)) * 100, 2);
        } else {
            $data['response_rate'] = 0;
        }
        
        // Activity logs terbaru (5 terakhir)
        $this->db->select('al.*, u.username');
        $this->db->from('activity_logs al');
        $this->db->join('users u', 'al.user_id = u.id', 'left');
        $this->db->order_by('al.created_at', 'DESC');
        $this->db->limit(5);
        $data['recent_activities'] = $this->db->get()->result_array();
        
        // Load view
        $this->load->view('admin/templates/header', $data);
        $this->load->view('admin/dashboard/index', $data);
        $this->load->view('admin/templates/footer');
    }
}
