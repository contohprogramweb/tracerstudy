<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Reports Controller - Halaman Laporan untuk Admin/Superadmin
 */
class Reports extends Admin_Controller {

    public function __construct() {
        parent::__construct();
        
        // Hanya super_admin dan admin_pusat_karir yang bisa akses
        $this->auth_lib->requireRole(['super_admin', 'admin_pusat_karir']);
        
        $this->load->helper('tracer_audit');
    }

    /**
     * Index: Halaman utama laporan
     */
    public function index() {
        $data['page_title'] = 'Laporan';
        $data['page_subtitle'] = 'Laporan dan statistik tracer study';
        
        // Statistik ringkas untuk laporan
        $data['total_alumni'] = $this->db->count_all('alumni_profiles');
        $data['total_surveys'] = $this->db->count_all('surveys');
        $data['total_responses'] = $this->db->count_all('survey_responses');
        
        // Alumni berdasarkan tahun lulus
        $this->db->select('graduation_year, COUNT(*) as total');
        $this->db->group_by('graduation_year');
        $this->db->order_by('graduation_year', 'DESC');
        $data['alumni_by_year'] = $this->db->get('alumni_profiles')->result_array();
        
        // Response rate per survei
        $this->db->select('s.title, s.status, COUNT(sr.id) as response_count');
        $this->db->from('surveys s');
        $this->db->join('survey_responses sr', 's.id = sr.survey_id', 'left');
        $this->db->group_by('s.id');
        $this->db->order_by('s.created_at', 'DESC');
        $data['survey_responses'] = $this->db->get()->result_array();
        
        // Load view
        $this->load->view('admin/templates/header', $data);
        $this->load->view('admin/reports/index', $data);
        $this->load->view('admin/templates/footer');
    }
    
    /**
     * Detail laporan per survei
     */
    public function detail($survey_id) {
        $data['page_title'] = 'Detail Laporan Survei';
        $data['page_subtitle'] = 'Statistik lengkap survei';
        
        // Get survey info
        $this->db->where('id', $survey_id);
        $data['survey'] = $this->db->get('surveys')->row_array();
        
        if (empty($data['survey'])) {
            show_404();
        }
        
        // Count responses
        $this->db->where('survey_id', $survey_id);
        $data['total_responses'] = $this->db->count_all_results('survey_responses');
        
        // Get questions
        $this->db->where('survey_id', $survey_id);
        $this->db->order_by('question_order', 'ASC');
        $data['questions'] = $this->db->get('survey_questions')->result_array();
        
        // Load view
        $this->load->view('admin/templates/header', $data);
        $this->load->view('admin/reports/detail', $data);
        $this->load->view('admin/templates/footer');
    }
    
    /**
     * Export laporan ke Excel/PDF
     */
    public function export($type = 'excel', $survey_id = null) {
        // Implementasi export akan ditambahkan nanti
        $this->session->set_flashdata('info', 'Fitur export akan segera tersedia');
        redirect('admin/reports');
    }
}
