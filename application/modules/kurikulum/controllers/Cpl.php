<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cpl extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Cpl_model');
        $this->load->library('form_validation');
        
        // Check authentication
        if (!$this->session->userdata('logged_in')) {
            redirect('auth/login');
        }
    }

    /**
     * Display list of CPL per study program
     */
    public function index($prodi_id = null) {
        if (!$prodi_id) {
            $prodi_id = $this->session->userdata('prodi_id');
        }
        
        $data['cpls'] = $this->Cpl_model->get_by_prodi($prodi_id);
        $data['prodi_id'] = $prodi_id;
        $data['title'] = 'Daftar CPL';
        
        $this->load->view('cpl/index', $data);
    }

    /**
     * Show form to create new CPL
     */
    public function create($prodi_id) {
        $data['prodi_id'] = $prodi_id;
        $data['title'] = 'Tambah CPL';
        $this->load->view('cpl/create', $data);
    }

    /**
     * Store new CPL
     */
    public function store($prodi_id) {
        $this->form_validation->set_rules('kode_cpl', 'Kode CPL', 'required|trim');
        $this->form_validation->set_rules('deskripsi', 'Deskripsi', 'required|trim');
        $this->form_validation->set_rules('aspect', 'Aspek', 'required|in_list[sikap,pengetahuan,keterampilan_umum,keterampilan_khusus]');
        $this->form_validation->set_rules('target_industri', 'Target Industri', 'required|numeric|greater_than[0]|less_than_equal_to[5]');

        if ($this->form_validation->run() == FALSE) {
            $this->create($prodi_id);
        } else {
            $data = [
                'prodi_id' => $prodi_id,
                'kode_cpl' => strtoupper($this->input->post('kode_cpl')),
                'deskripsi' => $this->input->post('deskripsi'),
                'aspect' => $this->input->post('aspect'),
                'target_industri' => $this->input->post('target_industri'),
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => $this->session->userdata('user_id')
            ];
            
            $this->Cpl_model->save($data);
            $this->session->set_flashdata('success', 'CPL berhasil ditambahkan');
            redirect('cpl/index/'.$prodi_id);
        }
    }

    /**
     * Show edit form for CPL
     */
    public function edit($prodi_id, $id) {
        $data['cpl'] = $this->Cpl_model->get_detail($id);
        if (!$data['cpl']) {
            show_404();
        }
        $data['prodi_id'] = $prodi_id;
        $data['title'] = 'Edit CPL';
        $this->load->view('cpl/edit', $data);
    }

    /**
     * Update CPL
     */
    public function update($prodi_id, $id) {
        $this->form_validation->set_rules('deskripsi', 'Deskripsi', 'required|trim');
        $this->form_validation->set_rules('target_industri', 'Target Industri', 'required|numeric|greater_than[0]|less_than_equal_to[5]');

        if ($this->form_validation->run() == FALSE) {
            $this->edit($prodi_id, $id);
        } else {
            $data = [
                'deskripsi' => $this->input->post('deskripsi'),
                'aspect' => $this->input->post('aspect'),
                'target_industri' => $this->input->post('target_industri'),
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => $this->session->userdata('user_id')
            ];
            
            $this->Cpl_model->update($id, $data);
            $this->session->set_flashdata('success', 'CPL berhasil diupdate');
            redirect('cpl/index/'.$prodi_id);
        }
    }

    /**
     * Delete CPL
     */
    public function delete($prodi_id, $id) {
        $cpl = $this->Cpl_model->get_detail($id);
        if (!$cpl) {
            show_404();
        }
        
        $this->Cpl_model->delete($id);
        $this->session->set_flashdata('success', 'CPL berhasil dihapus');
        redirect('cpl/index/'.$prodi_id);
    }

    /**
     * Map CPL to SN-Dikti standards
     */
    public function mapSnDikti($id) {
        $data['cpl'] = $this->Cpl_model->get_detail($id);
        if (!$data['cpl']) {
            show_404();
        }
        $data['mappings'] = $this->Cpl_model->get_mapping($id);
        $data['title'] = 'Pemetaan SN-Dikti';
        $this->load->view('cpl/mapping', $data);
    }

    /**
     * Save SN-Dikti mapping
     */
    public function saveSnDikti($id) {
        $data = [
            'cpl_id' => $id,
            'type' => 'SN_DIPTI',
            'code' => $this->input->post('sn_code'),
            'description' => $this->input->post('sn_desc'),
            'created_at' => date('Y-m-d H:i:s')
        ];
        $this->Cpl_model->save_mapping($data);
        redirect('cpl/mapping/'.$id);
    }

    /**
     * Map CPL to KKNI standards
     */
    public function mapKkni($id) {
        $data['cpl'] = $this->Cpl_model->get_detail($id);
        if (!$data['cpl']) {
            show_404();
        }
        $data['mappings'] = $this->Cpl_model->get_mapping($id);
        $data['title'] = 'Pemetaan KKNI';
        $this->load->view('cpl/mapping', $data);
    }

    /**
     * Save KKNI mapping
     */
    public function saveKkni($id) {
        $data = [
            'cpl_id' => $id,
            'type' => 'KKNI',
            'level' => $this->input->post('kkni_level'),
            'descriptor' => $this->input->post('kkni_desc'),
            'created_at' => date('Y-m-d H:i:s')
        ];
        $this->Cpl_model->save_mapping($data);
        redirect('cpl/mapping/'.$id);
    }
}
