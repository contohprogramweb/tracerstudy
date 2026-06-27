<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Gap_analysis extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Cpl_model');
        $this->load->model('Kurikulum_model');
        
        // Check authentication
        if (!$this->session->userdata('logged_in')) {
            redirect('auth/login');
        }
    }

    /**
     * Display gap analysis dashboard for CPL vs industry
     */
    public function index($prodi_id = null) {
        if (!$prodi_id) {
            $prodi_id = $this->session->userdata('prodi_id');
        }
        
        $tahun = $this->input->get('tahun') ?? date('Y');
        
        // KUR-007: Evaluasi CPL berbasis tracer study
        $data['gap_data'] = $this->Cpl_model->calculate_gap($prodi_id, $tahun);
        $data['prodi_id'] = $prodi_id;
        $data['tahun'] = $tahun;
        $data['title'] = 'Gap Analysis CPL vs Industri';
        
        $this->load->view('gap_analysis/index', $data);
    }

    /**
     * Calculate gap via AJAX
     */
    public function calculate($prodi_id) {
        $tahun = $this->input->get('tahun') ?? date('Y');
        $result = $this->Cpl_model->calculate_gap($prodi_id, $tahun);
        
        echo json_encode([
            'status' => true, 
            'data' => $result,
            'message' => 'Gap calculation completed'
        ]);
    }

    /**
     * Generate recommendations for curriculum improvement
     */
    public function rekomendasi($prodi_id = null) {
        if (!$prodi_id) {
            $prodi_id = $this->session->userdata('prodi_id');
        }
        
        $tahun = $this->input->get('tahun') ?? date('Y');
        $gap_data = $this->Cpl_model->calculate_gap($prodi_id, $tahun);
        
        $data['recommendations'] = $this->Cpl_model->generate_recommendations($gap_data);
        $data['gap_summary'] = $gap_data;
        $data['prodi_id'] = $prodi_id;
        $data['tahun'] = $tahun;
        $data['title'] = 'Rekomendasi Perbaikan Kurikulum';
        
        $this->load->view('gap_analysis/rekomendasi', $data);
    }

    /**
     * Export gap analysis to PDF
     */
    public function exportPdf($prodi_id) {
        $tahun = $this->input->get('tahun') ?? date('Y');
        $data['gap_data'] = $this->Cpl_model->calculate_gap($prodi_id, $tahun);
        $data['recommendations'] = $this->Cpl_model->generate_recommendations($data['gap_data']);
        
        $html = $this->load->view('gap_analysis/print', $data, true);
        
        $this->load->library('pdf');
        $this->pdf->setPaper('A4', 'landscape');
        $this->pdf->load_html($html);
        $this->pdf->render();
        $this->pdf->stream("Gap_Analysis_Prodi_{$prodi_id}_{$tahun}.pdf", array("Attachment" => false));
    }
}
