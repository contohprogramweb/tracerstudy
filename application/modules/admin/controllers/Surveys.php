<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Surveys Admin Controller - Manajemen Survei dari Admin Panel
 */
class Surveys extends Admin_Controller {

    public function __construct() {
        parent::__construct();
        $this->auth_lib->requireRole(['super_admin', 'admin_pusat_karir']);
        $this->load->helper('tracer_audit');
    }

    public function index() {
        $data['page_title'] = 'Manajemen Survei';
        $data['page_subtitle'] = 'Kelola survei dan kuesioner';
        
        $this->db->select('s.*, u.username as creator');
        $this->db->from('surveys s');
        $this->db->join('users u', 's.created_by = u.id', 'left');
        $this->db->order_by('s.created_at', 'DESC');
        $data['surveys'] = $this->db->get()->result_array();
        
        $this->load->view('admin/templates/header', $data);
        $this->load->view('admin/surveys/index', $data);
        $this->load->view('admin/templates/footer');
    }
}
