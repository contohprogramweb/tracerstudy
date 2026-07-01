<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Alumni Admin Controller - Manajemen Data Alumni dari Admin Panel
 */
class Alumni extends Admin_Controller {

    public function __construct() {
        parent::__construct();
        $this->auth_lib->requireRole(['super_admin', 'admin_pusat_karir']);
        $this->load->helper('tracer_audit');
    }

    public function index() {
        $data['page_title'] = 'Data Alumni';
        $data['page_subtitle'] = 'Kelola data alumni';
        
        $this->db->select('ap.*, u.username, u.email');
        $this->db->from('alumni ap');
        $this->db->join('users u', 'ap.user_id = u.id', 'left');
        $this->db->order_by('ap.created_at', 'DESC');
        $data['alumni'] = $this->db->get()->result_array();
        
        $this->load->view('admin/templates/header', $data);
        $this->load->view('admin/alumni/index', $data);
        $this->load->view('admin/templates/footer');
    }
}
