<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Dashboard Controller - Halaman Utama Admin Prodi
 * 
 * Menampilkan overview data prodi dengan statistik dan informasi penting
 */
class Dashboard extends MY_Prodi_Controller {
    
    public function __construct() {
        parent::__construct();
        
        $this->load->model('auth/user_model');
        $this->load->helper('tracer_audit');
    }
    
    /**
     * Index: Dashboard utama admin prodi
     */
    public function index() {
        $data['page_title'] = 'Dashboard Admin Prodi';
        $data['page_subtitle'] = 'Overview Data Program Studi';
        
        // Get prodi info
        $prodi_id = $this->prodi_id;
        $data['prodi_id'] = $prodi_id;
        
        if ($prodi_id) {
            $this->db->where('id', $prodi_id);
            $data['prodi_info'] = $this->db->get('prodi')->row_array();
        } else {
            $data['prodi_info'] = null;
        }
        
        // Statistik alumni per prodi
        if ($prodi_id) {
            $this->db->where('prodi_id', $prodi_id);
            $data['total_alumni'] = $this->db->count_all_results('alumni_profiles');
            
            // Status kerja
            $this->db->where('prodi_id', $prodi_id);
            $this->db->where('status_kerja', 'bekerja');
            $data['alumni_bekerja'] = $this->db->count_all_results('alumni_profiles');
            
            $this->db->where('prodi_id', $prodi_id);
            $this->db->where('status_kerja', 'belum_bekerja');
            $data['alumni_belum_bekerja'] = $this->db->count_all_results('alumni_profiles');
            
            $this->db->where('prodi_id', $prodi_id);
            $this->db->where('status_kerja', 'wirausaha');
            $data['alumni_wirausaha'] = $this->db->count_all_results('alumni_profiles');
            
            $this->db->where('prodi_id', $prodi_id);
            $this->db->where('status_kerja', 'melanjutkan_studi');
            $data['alumni_melanjutkan_studi'] = $this->db->count_all_results('alumni_profiles');
        } else {
            $data['total_alumni'] = 0;
            $data['alumni_bekerja'] = 0;
            $data['alumni_belum_bekerja'] = 0;
            $data['alumni_wirausaha'] = 0;
            $data['alumni_melanjutkan_studi'] = 0;
        }
        
        // Statistik survei untuk prodi ini
        if ($prodi_id) {
            // Survei yang targetnya termasuk prodi ini
            $this->db->select('s.*');
            $this->db->from('surveys s');
            $this->db->join('survey_targets st', 's.id = st.survey_id');
            $this->db->where('st.prodi_id', $prodi_id);
            $data['total_surveys'] = $this->db->count_all_results();
            
            // Survei aktif
            $this->db->where('st.prodi_id', $prodi_id);
            $this->db->where('s.status', 'active');
            $data['active_surveys'] = $this->db->count_all_results();
            
            // Survei draft
            $this->db->where('st.prodi_id', $prodi_id);
            $this->db->where('s.status', 'draft');
            $data['draft_surveys'] = $this->db->count_all_results();
        } else {
            $data['total_surveys'] = 0;
            $data['active_surveys'] = 0;
            $data['draft_surveys'] = 0;
        }
        
        // Total responses dari alumni prodi ini
        if ($prodi_id) {
            $this->db->select('COUNT(DISTINCT sr.id) as total');
            $this->db->from('survey_responses sr');
            $this->db->join('alumni_profiles ap', 'sr.alumni_id = ap.id');
            $this->db->where('ap.prodi_id', $prodi_id);
            $query = $this->db->get()->row();
            $data['total_responses'] = $query ? $query->total : 0;
        } else {
            $data['total_responses'] = 0;
        }
        
        // Response rate
        if ($data['total_surveys'] > 0) {
            $data['response_rate'] = round(($data['total_responses'] / ($data['total_surveys'] * 100)) * 100, 2);
        } else {
            $data['response_rate'] = 0;
        }
        
        // Activity logs terbaru untuk prodi ini (5 terakhir)
        $this->db->select('al.*, u.username');
        $this->db->from('activity_logs al');
        $this->db->join('users u', 'al.user_id = u.id', 'left');
        if ($prodi_id) {
            $this->db->where('al.prodi_id', $prodi_id);
        }
        $this->db->order_by('al.created_at', 'DESC');
        $this->db->limit(5);
        $data['recent_activities'] = $this->db->get()->result_array();
        
        // Load view
        $this->load->view('prodi/templates/header', $data);
        $this->load->view('prodi/dashboard/index', $data);
        $this->load->view('prodi/templates/footer');
    }
}
