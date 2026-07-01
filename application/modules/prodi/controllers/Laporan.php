<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Laporan Controller - Generate dan Export Laporan untuk Admin Prodi
 */
class Laporan extends MY_Prodi_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('alumni/alumni_model');
        $this->load->helper('tracer_audit');
    }
    
    /**
     * Index: Halaman utama laporan
     */
    public function index() {
        $data['page_title'] = 'Laporan & Analisis';
        $data['page_subtitle'] = 'Generate dan export laporan program studi';
        
        if ($this->prodi_id) {
            $this->db->where('id', $this->prodi_id);
            $data['prodi_info'] = $this->db->get('prodi')->row_array();
        } else {
            $data['prodi_info'] = null;
        }
        
        $this->load->view('prodi/templates/header', $data);
        $this->load->view('prodi/laporan/index', $data);
        $this->load->view('prodi/templates/footer');
    }
    
    /**
     * Generate laporan PDF
     */
    public function generate($type) {
        $allowed_types = ['tracer', 'status', 'survey', 'employment'];
        
        if (!in_array($type, $allowed_types)) {
            show_error('Jenis laporan tidak valid', 400);
            return;
        }
        
        $tahun = $this->input->get('tahun') ?? date('Y');
        
        // Get data based on type
        $data = [];
        
        if ($this->prodi_id) {
            // Get alumni count
            $this->db->where('prodi_id', $this->prodi_id);
            if ($type === 'tracer' || $type === 'status') {
                $this->db->where('tahun_lulus', $tahun);
            }
            $data['total_alumni'] = $this->db->count_all_results('alumni');
            
            // Get status breakdown
            $this->db->where('prodi_id', $this->prodi_id);
            if ($type === 'tracer' || $type === 'status') {
                $this->db->where('tahun_lulus', $tahun);
            }
            $status_data = $this->db->group_by('status_kerja')->get('alumni')->result_array();
            $data['status_breakdown'] = [];
            foreach ($status_data as $row) {
                $data['status_breakdown'][$row['status_kerja']] = $this->db->affected_rows();
            }
            
            // Get prodi info
            $this->db->where('id', $this->prodi_id);
            $data['prodi_info'] = $this->db->get('prodi')->row_array();
        } else {
            $data['total_alumni'] = 0;
            $data['status_breakdown'] = [];
            $data['prodi_info'] = null;
        }
        
        $data['type'] = $type;
        $data['tahun'] = $tahun;
        $data['generated_at'] = date('d/m/Y H:i:s');
        
        // Load PDF view
        $this->load->view('prodi/laporan/pdf_' . $type, $data);
    }
    
    /**
     * Export data alumni ke Excel
     */
    public function export($format = 'excel') {
        if (!$this->prodi_id) {
            show_error('Prodi tidak ditemukan', 404);
            return;
        }
        
        $tahun = $this->input->get('tahun') ?? null;
        
        $this->db->select('ap.*, p.nama_prodi, p.kode_prodi');
        $this->db->from('alumni ap');
        $this->db->join('prodi p', 'ap.prodi_id = p.id', 'left');
        $this->db->where('ap.prodi_id', $this->prodi_id);
        
        if ($tahun) {
            $this->db->where('ap.tahun_lulus', $tahun);
        }
        
        $this->db->order_by('ap.tahun_lulus', 'DESC');
        $this->db->order_by('ap.nim', 'ASC');
        
        $alumni = $this->db->get()->result_array();
        
        if ($format === 'excel') {
            // Set headers for Excel download
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename="Data_Alumni_' . date('Y-m-d') . '.xls"');
            
            echo '<table border="1">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>NIM</th>';
            echo '<th>Nama</th>';
            echo '<th>Program Studi</th>';
            echo '<th>Kode Prodi</th>';
            echo '<th>Tahun Lulus</th>';
            echo '<th>Status Kerja</th>';
            echo '<th>Email</th>';
            echo '<th>Telepon</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            
            foreach ($alumni as $row) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($row['nim']) . '</td>';
                echo '<td>' . htmlspecialchars($row['nama']) . '</td>';
                echo '<td>' . htmlspecialchars($row['nama_prodi'] ?? '-') . '</td>';
                echo '<td>' . htmlspecialchars($row['kode_prodi'] ?? '-') . '</td>';
                echo '<td>' . htmlspecialchars($row['tahun_lulus']) . '</td>';
                echo '<td>' . htmlspecialchars(str_replace('_', ' ', $row['status_kerja'])) . '</td>';
                echo '<td>' . htmlspecialchars($row['email'] ?? '-') . '</td>';
                echo '<td>' . htmlspecialchars($row['telepon'] ?? '-') . '</td>';
                echo '</tr>';
            }
            
            echo '</tbody>';
            echo '</table>';
            
            log_activity($this->session->userdata('user_id'), 'export', 'alumni_profiles', null, 'Export data alumni ke Excel');
            exit;
        }
    }
    
    /**
     * Custom report page
     */
    public function custom() {
        $data['page_title'] = 'Laporan Kustom';
        $data['page_subtitle'] = 'Buat laporan sesuai kebutuhan';
        
        if ($this->prodi_id) {
            $this->db->where('id', $this->prodi_id);
            $data['prodi_info'] = $this->db->get('prodi')->row_array();
        } else {
            $data['prodi_info'] = null;
        }
        
        $data['kohorts'] = $this->db->where('status', 'active')->get('kohort')->result_array();
        
        $this->load->view('prodi/templates/header', $data);
        $this->load->view('prodi/laporan/custom', $data);
        $this->load->view('prodi/templates/footer');
    }
}
