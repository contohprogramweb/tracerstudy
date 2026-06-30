<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Survey extends MY_Prodi_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('auth/user_model');
    }
    
    public function index() {
        $data['page_title'] = 'Data Survei';
        $data['page_subtitle'] = 'Lihat survei yang tersedia';
        
        $prodi_id = $this->prodi_id;
        
        if ($prodi_id) {
            $this->db->select('s.*');
            $this->db->from('surveys s');
            $this->db->join('survey_targets st', 's.id = st.survey_id');
            $this->db->where('st.prodi_id', $prodi_id);
            $this->db->order_by('s.created_at', 'DESC');
            $data['surveys'] = $this->db->get()->result_array();
        } else {
            $data['surveys'] = [];
        }
        
        $this->load->view('prodi/templates/header', $data);
        $this->load->view('prodi/survey/index', $data);
        $this->load->view('prodi/templates/footer');
    }
}
