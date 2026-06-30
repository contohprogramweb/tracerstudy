<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Alumni Controller - Manajemen Alumni untuk Admin Prodi
 * 
 * Mengelola data alumni tingkat prodi (CRUD, export)
 */
class Alumni extends MY_Prodi_Controller {
    
    public function __construct() {
        parent::__construct();
        
        $this->load->model('alumni/alumni_model');
        $this->load->model('auth/user_model');
        $this->load->helper('tracer_audit');
    }
    
    /**
     * Index: List alumni untuk prodi ini
     */
    public function index() {
        $data['page_title'] = 'Data Alumni';
        $data['page_subtitle'] = 'Kelola data alumni program studi';
        
        // Load dropdown data for filters
        $this->db->select('id, nama');
        $this->db->where('status', 'active');
        $data['kohorts'] = $this->db->get('kohort')->result_array();
        
        $data['prodi_id'] = $this->prodi_id;
        
        if ($this->prodi_id) {
            $this->db->where('id', $this->prodi_id);
            $data['prodi_info'] = $this->db->get('prodi')->row_array();
        } else {
            $data['prodi_info'] = null;
        }
        
        $data['status_kerja_options'] = [
            '' => 'Semua Status',
            'bekerja' => 'Bekerja',
            'belum_bekerda' => 'Belum Bekerja',
            'wirausaha' => 'Wirausaha',
            'melanjutkan_studi' => 'Melanjutkan Studi'
        ];
        
        // Load view
        $this->load->view('prodi/templates/header', $data);
        $this->load->view('prodi/alumni/index', $data);
        $this->load->view('prodi/templates/footer');
    }
    
    /**
     * API Endpoint untuk DataTables Server-Side Processing
     */
    public function get_data() {
        $draw = $this->input->get('draw');
        $start = $this->input->get('start');
        $length = $this->input->get('length');
        $search = $this->input->get('search')['value'] ?? '';
        
        // Build filters
        $filters = [
            'kohort_id' => $this->input->get('kohort_id'),
            'status_kerja' => $this->input->get('status_kerja'),
            'search' => $search
        ];
        
        // Remove empty filters
        $filters = array_filter($filters, function($value) {
            return $value !== '' && $value !== NULL;
        });
        
        // Always filter by prodi_id
        if ($this->prodi_id) {
            $filters['prodi_id'] = $this->prodi_id;
        }
        
        // Get data from model
        $page = ($start / $length) + 1;
        $result = $this->alumni_model->getAlumniWithFilters($filters, $page, $length);
        
        $data = [];
        foreach ($result['data'] as $row) {
            $nestedData = [];
            $nestedData[] = htmlspecialchars($row['nim']);
            $nestedData[] = htmlspecialchars($row['nama']);
            $nestedData[] = htmlspecialchars($row['prodi_nama'] ?? '-');
            $nestedData[] = htmlspecialchars($row['tahun_lulus']);
            $nestedData[] = '<span class="badge bg-' . ($row['status_kerja'] === 'bekerja' ? 'success' : 'secondary') . '">' . str_replace('_', ' ', strtoupper($row['status_kerja'])) . '</span>';
            $nestedData[] = '
                <a href="' . site_url('prodi/alumni/edit/' . $row['id']) . '" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteAlumni(' . $row['id'] . ')"><i class="bi bi-trash"></i></button>
            ';
            $data[] = $nestedData;
        }
        
        $output = [
            "draw" => intval($draw),
            "recordsTotal" => $result['total'],
            "recordsFiltered" => $result['filtered'],
            "data" => $data
        ];
        
        echo json_encode($output);
    }
    
    /**
     * Tambah alumni baru
     */
    public function add() {
        $data['page_title'] = 'Tambah Alumni';
        $data['page_subtitle'] = 'Tambah alumni baru';
        
        if ($this->input->post()) {
            $this->form_validation->set_rules('nim', 'NIM', 'required|is_unique[alumni_profiles.nim]');
            $this->form_validation->set_rules('nama', 'Nama', 'required');
            $this->form_validation->set_rules('prodi_id', 'Prodi', 'required');
            $this->form_validation->set_rules('tahun_lulus', 'Tahun Lulus', 'required|integer|min_length[4]|max_length[4]');
            
            if ($this->form_validation->run() == TRUE) {
                $alumni_data = [
                    'nim' => $this->input->post('nim', TRUE),
                    'nama' => $this->input->post('nama', TRUE),
                    'prodi_id' => $this->input->post('prodi_id', TRUE),
                    'tahun_lulus' => $this->input->post('tahun_lulus', TRUE),
                    'status_kerja' => $this->input->post('status_kerja', TRUE) ?? 'belum_bekerja',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                $insert_id = $this->alumni_model->insert($alumni_data);
                
                if ($insert_id) {
                    log_activity($this->session->userdata('user_id'), 'create', 'alumni_profiles', $insert_id, 'Menambah alumni baru');
                    $this->session->set_flashdata('success', 'Alumni berhasil ditambahkan');
                    redirect('prodi/alumni');
                } else {
                    $this->session->set_flashdata('error', 'Gagal menambahkan alumni');
                }
            }
        }
        
        $this->db->select('id, nama, kode');
        $this->db->order_by('nama', 'ASC');
        $data['prodis'] = $this->db->get('prodi')->result_array();
        
        $this->load->view('prodi/templates/header', $data);
        $this->load->view('prodi/alumni/form', $data);
        $this->load->view('prodi/templates/footer');
    }
    
    /**
     * Edit alumni
     */
    public function edit($id) {
        $data['page_title'] = 'Edit Alumni';
        $data['page_subtitle'] = 'Ubah data alumni';
        
        $alumni = $this->alumni_model->get_by_id($id);
        
        if (!$alumni) {
            show_error('Alumni tidak ditemukan', 404);
        }
        
        // Cek akses prodi
        if ($this->prodi_id && $alumni->prodi_id != $this->prodi_id) {
            show_error('Akses ditolak. Anda hanya bisa mengelola alumni prodi Anda.', 403);
        }
        
        if ($this->input->post()) {
            $this->form_validation->set_rules('nim', 'NIM', 'required');
            $this->form_validation->set_rules('nama', 'Nama', 'required');
            $this->form_validation->set_rules('prodi_id', 'Prodi', 'required');
            $this->form_validation->set_rules('tahun_lulus', 'Tahun Lulus', 'required|integer|min_length[4]|max_length[4]');
            
            if ($this->form_validation->run() == TRUE) {
                $alumni_data = [
                    'nim' => $this->input->post('nim', TRUE),
                    'nama' => $this->input->post('nama', TRUE),
                    'prodi_id' => $this->input->post('prodi_id', TRUE),
                    'tahun_lulus' => $this->input->post('tahun_lulus', TRUE),
                    'status_kerja' => $this->input->post('status_kerja', TRUE),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                $update = $this->alumni_model->update($id, $alumni_data);
                
                if ($update) {
                    log_activity($this->session->userdata('user_id'), 'update', 'alumni_profiles', $id, 'Mengupdate data alumni');
                    $this->session->set_flashdata('success', 'Alumni berhasil diupdate');
                    redirect('prodi/alumni');
                } else {
                    $this->session->set_flashdata('error', 'Gagal mengupdate alumni');
                }
            }
        }
        
        $data['alumni'] = $alumni;
        $this->db->select('id, nama, kode');
        $this->db->order_by('nama', 'ASC');
        $data['prodis'] = $this->db->get('prodi')->result_array();
        
        $this->load->view('prodi/templates/header', $data);
        $this->load->view('prodi/alumni/form', $data);
        $this->load->view('prodi/templates/footer');
    }
    
    /**
     * Delete alumni
     */
    public function delete($id) {
        $alumni = $this->alumni_model->get_by_id($id);
        
        if (!$alumni) {
            echo json_encode(['status' => 'error', 'message' => 'Alumni tidak ditemukan']);
            return;
        }
        
        // Cek akses prodi
        if ($this->prodi_id && $alumni->prodi_id != $this->prodi_id) {
            echo json_encode(['status' => 'error', 'message' => 'Akses ditolak']);
            return;
        }
        
        $delete = $this->alumni_model->delete($id);
        
        if ($delete) {
            log_activity($this->session->userdata('user_id'), 'delete', 'alumni_profiles', $id, 'Menghapus alumni');
            echo json_encode(['status' => 'success', 'message' => 'Alumni berhasil dihapus']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus alumni']);
        }
    }
}
