<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kurikulum extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Kurikulum_model');
        $this->load->library('form_validation');
        
        // Check authentication
        if (!$this->session->userdata('logged_in')) {
            redirect('auth/login');
        }
    }

    /**
     * Display list of curriculum per study program
     */
    public function index() {
        $prodi_id = $this->input->get('prodi_id') ?? $this->session->userdata('prodi_id');
        $tahun = $this->input->get('tahun') ?? date('Y');
        
        $data['curricula'] = $this->Kurikulum_model->get_by_prodi($prodi_id, $tahun);
        $data['total_sks'] = $this->Kurikulum_model->get_sks_total($prodi_id, $tahun);
        $data['prodi_id'] = $prodi_id;
        $data['tahun'] = $tahun;
        $data['title'] = 'Manajemen Kurikulum';
        
        $this->load->view('kurikulum/index', $data);
    }

    /**
     * Show form to add new course
     */
    public function create() {
        $data['prodi_id'] = $this->input->get('prodi_id');
        $data['title'] = 'Tambah Mata Kuliah';
        $this->load->view('kurikulum/create', $data);
    }

    /**
     * Save new course
     */
    public function store() {
        $this->form_validation->set_rules('kode_mk', 'Kode MK', 'required|trim');
        $this->form_validation->set_rules('nama_mk', 'Nama MK', 'required|trim');
        $this->form_validation->set_rules('sks', 'SKS', 'required|numeric|greater_than[0]');
        $this->form_validation->set_rules('semester', 'Semester', 'required|numeric|greater_than[0]|less_than_equal_to[14]');
        $this->form_validation->set_rules('tahun_kurikulum', 'Tahun Kurikulum', 'required|numeric|exact_length[4]');

        if ($this->form_validation->run() == FALSE) {
            $this->create();
        } else {
            $cpl_related = $this->input->post('cpl_related') ?? [];
            
            $data = [
                'prodi_id' => $this->input->post('prodi_id'),
                'tahun_kurikulum' => $this->input->post('tahun_kurikulum'),
                'semester' => $this->input->post('semester'),
                'kode_mk' => strtoupper($this->input->post('kode_mk')),
                'nama_mk' => $this->input->post('nama_mk'),
                'sks' => $this->input->post('sks'),
                'jenis' => $this->input->post('jenis'),
                'cpl_related' => json_encode($cpl_related),
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => $this->session->userdata('user_id')
            ];
            
            $this->Kurikulum_model->save($data);
            $this->session->set_flashdata('success', 'Mata kuliah berhasil ditambahkan');
            redirect('kurikulum?prodi_id='.$data['prodi_id'].'&tahun='.$data['tahun_kurikulum']);
        }
    }

    /**
     * Show edit form for course
     */
    public function edit($id) {
        $data['mk'] = $this->Kurikulum_model->get_detail($id);
        if (!$data['mk']) {
            show_404();
        }
        $data['title'] = 'Edit Mata Kuliah';
        $this->load->view('kurikulum/edit', $data);
    }

    /**
     * Update course
     */
    public function update($id) {
        $this->form_validation->set_rules('nama_mk', 'Nama MK', 'required|trim');
        $this->form_validation->set_rules('sks', 'SKS', 'required|numeric|greater_than[0]');
        $this->form_validation->set_rules('semester', 'Semester', 'required|numeric|greater_than[0]|less_than_equal_to[14]');

        if ($this->form_validation->run() == FALSE) {
            $this->edit($id);
        } else {
            $cpl_related = $this->input->post('cpl_related') ?? [];
            
            $data = [
                'nama_mk' => $this->input->post('nama_mk'),
                'sks' => $this->input->post('sks'),
                'semester' => $this->input->post('semester'),
                'jenis' => $this->input->post('jenis'),
                'cpl_related' => json_encode($cpl_related),
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => $this->session->userdata('user_id')
            ];
            
            $this->Kurikulum_model->update($id, $data);
            $this->session->set_flashdata('success', 'Mata kuliah berhasil diupdate');
            redirect('kurikulum?prodi_id='.$this->input->post('prodi_id'));
        }
    }

    /**
     * Delete course
     */
    public function delete($id) {
        $mk = $this->Kurikulum_model->get_detail($id);
        if (!$mk) {
            show_404();
        }
        
        $this->Kurikulum_model->delete($id);
        $this->session->set_flashdata('success', 'Mata kuliah berhasil dihapus');
        redirect('kurikulum?prodi_id='.$mk->prodi_id);
    }

    /**
     * Compare curriculum between two periods
     */
    public function compare($tahun1 = null, $tahun2 = null) {
        $prodi_id = $this->input->get('prodi_id') ?? $this->session->userdata('prodi_id');
        
        if (!$tahun1 || !$tahun2) {
            $tahun1 = date('Y') - 1;
            $tahun2 = date('Y');
        }
        
        $data['curricula'] = $this->Kurikulum_model->get_for_compare($prodi_id, $tahun1, $tahun2);
        $data['tahun1'] = $tahun1;
        $data['tahun2'] = $tahun2;
        $data['sks_1'] = $this->Kurikulum_model->get_sks_total($prodi_id, $tahun1);
        $data['sks_2'] = $this->Kurikulum_model->get_sks_total($prodi_id, $tahun2);
        $data['prodi_id'] = $prodi_id;
        $data['title'] = 'Perbandingan Kurikulum';
        
        $this->load->view('kurikulum/compare', $data);
    }
}
