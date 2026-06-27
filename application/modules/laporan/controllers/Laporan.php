<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Laporan extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('alumni_model');
        $this->load->model('tracer_model');
        $this->load->model('iku/iku_model');
        $this->load->library('session');
        // Load DOMPDF library jika tersedia
        if (file_exists(APPPATH . 'libraries/Pdf.php')) {
            $this->load->library('pdf');
        }
    }

    /**
     * Dashboard utama dengan grafik analitik
     */
    public function dashboard() {
        $data['title'] = 'Dashboard Analitik Tracer Study';
        
        // Ambil filter dari GET parameter
        $tahun = $this->input->get('tahun') ? $this->input->get('tahun') : date('Y');
        $prodi_id = $this->input->get('prodi_id') ? $this->input->get('prodi_id') : null;
        $kohort_id = $this->input->get('kohort_id') ? $this->input->get('kohort_id') : null;

        // Data untuk grafik
        $data['tahun_filter'] = $tahun;
        $data['prodi_filter'] = $prodi_id;
        
        // 1. Distribusi Status Kerja (Pie Chart)
        $data['status_kerja'] = $this->tracer_model->get_status_distribution($tahun, $prodi_id);
        
        // 2. Rata-rata Gaji per Prodi (Bar Chart)
        $data['gaji_prodi'] = $this->tracer_model->get_avg_salary_by_prodi($tahun, $prodi_id);
        
        // 3. Tren Responden (Line Chart)
        $data['tren_responden'] = $this->alumni_model->get_respondent_trend(5, $prodi_id);
        
        // 4. IKU-1 Score (Gauge Chart)
        $data['iku_score'] = $this->iku_model->calculate_current_iku($tahun, $prodi_id);
        $data['iku_target'] = 80; // Target default 80%
        
        // 5. Kesesuaian Kompetensi (Doughnut Chart)
        $data['kompetensi_match'] = $this->tracer_model->get_competency_match_summary($tahun, $prodi_id);
        
        // 6. Radar Chart Kompetensi
        $data['radar_data'] = $this->tracer_model->get_competency_radar_data($tahun, $prodi_id);
        
        // Summary stats
        $data['total_responden'] = array_sum(array_column($data['status_kerja'], 'count'));
        $data['avg_salary'] = $this->tracer_model->get_overall_avg_salary($tahun, $prodi_id);
        $data['avg_wait_time'] = $this->tracer_model->get_avg_wait_time($tahun, $prodi_id);

        $this->load->view('laporan/dashboard', $data);
    }

    /**
     * Laporan Detail Status Kerja
     */
    public function statusKerja() {
        $filter = $this->_get_filters();
        $data['results'] = $this->tracer_model->get_detailed_status($filter);
        $data['summary'] = $this->tracer_model->get_status_distribution($filter['tahun'], $filter['prodi_id']);
        $data['title'] = 'Laporan Status Kerja Lulusan';
        $data['filter'] = $filter;
        $this->load->view('laporan/status_kerja', $data);
    }

    /**
     * Laporan Distribusi Gaji
     */
    public function gaji() {
        $filter = $this->_get_filters();
        $data['ranges'] = $this->tracer_model->get_salary_ranges($filter);
        $data['stats'] = $this->tracer_model->get_salary_statistics($filter);
        $data['by_prodi'] = $this->tracer_model->get_avg_salary_by_prodi($filter['tahun'], $filter['prodi_id']);
        $data['title'] = 'Laporan Distribusi Gaji Lulusan';
        $data['filter'] = $filter;
        $this->load->view('laporan/gaji', $data);
    }

    /**
     * Laporan Masa Tunggu Pekerjaan
     */
    public function masaTunggu() {
        $filter = $this->_get_filters();
        $data['distribution'] = $this->tracer_model->get_wait_time_distribution($filter);
        $data['avg_wait'] = $this->tracer_model->get_avg_wait_time($filter['tahun'], $filter['prodi_id']);
        $data['by_category'] = $this->tracer_model->get_wait_time_by_category($filter);
        $data['title'] = 'Laporan Masa Tunggu Pekerjaan';
        $data['filter'] = $filter;
        $this->load->view('laporan/masa_tunggu', $data);
    }

    /**
     * Laporan Evaluasi Kompetensi
     */
    public function kompetensi() {
        $filter = $this->_get_filters();
        $data['radar_data'] = $this->tracer_model->get_competency_radar_data($filter['tahun'], $filter['prodi_id']);
        $data['detail_per_cpl'] = $this->tracer_model->get_competency_detail_per_cpl($filter);
        $data['stakeholder_rating'] = $this->tracer_model->get_stakeholder_competency_rating($filter);
        $data['title'] = 'Evaluasi Kompetensi Lulusan';
        $data['filter'] = $filter;
        $this->load->view('laporan/kompetensi', $data);
    }

    /**
     * Laporan Evaluasi Kurikulum & Gap Analysis
     */
    public function kurikulum() {
        $filter = $this->_get_filters();
        $data['gap_matrix'] = $this->tracer_model->get_curriculum_gap_matrix($filter);
        $data['recommendations'] = $this->tracer_model->get_curriculum_recommendations($filter);
        $data['gap_by_aspect'] = $this->tracer_model->get_gap_by_aspect($filter);
        $data['title'] = 'Evaluasi Kurikulum & Gap Analysis';
        $data['filter'] = $filter;
        $this->load->view('laporan/kurikulum', $data);
    }

    /**
     * Laporan IKU-1 Detail
     */
    public function iku() {
        $filter = $this->_get_filters();
        if (!$filter['kohort_id']) {
            // Default ke kohort terbaru
            $filter['kohort_id'] = $this->alumni_model->get_latest_kohort_id();
        }
        
        $data['iku_detail'] = $this->iku_model->get_detailed_calculation($filter['kohort_id'], $filter['prodi_id']);
        $data['iku_history'] = $this->iku_model->get_iku_history($filter['prodi_id'], 5);
        $data['response_rate'] = $this->iku_model->get_response_rate($filter['kohort_id'], $filter['prodi_id']);
        $data['vv_rate'] = $this->iku_model->get_vv_rate($filter['kohort_id'], $filter['prodi_id']);
        $data['title'] = 'Laporan Kinerja Utama (IKU-1)';
        $data['filter'] = $filter;
        $this->load->view('laporan/iku', $data);
    }

    /**
     * Laporan BAN-PT Kriteria 9
     */
    public function banpt() {
        $filter = $this->_get_filters();
        $data['kriteria_9'] = $this->tracer_model->get_banpt_kriteria_9_data($filter);
        $data['mahasiswa_prestasi'] = $this->tracer_model->get_student_achievements($filter);
        $data['dosen_prestasi'] = $this->tracer_model->get_lecturer_achievements($filter);
        $data['kesesuaian_bidang'] = $this->tracer_model->get_job_relevance($filter);
        $data['title'] = 'Laporan Evaluasi Diri BAN-PT (Kriteria 9)';
        $data['filter'] = $filter;
        $this->load->view('laporan/banpt', $data);
    }

    /**
     * Laporan PPEPP (Penjaminan Mutu)
     */
    public function ppepp() {
        $filter = $this->_get_filters();
        $data['ppepp_cycle'] = $this->tracer_model->get_ppepp_data($filter);
        $data['indikator_mutu'] = $this->tracer_model->get_quality_indicators($filter);
        $data['tindak_lanjut'] = $this->tracer_model->get_follow_up_actions($filter);
        $data['title'] = 'Laporan Siklus PPEPP';
        $data['filter'] = $filter;
        $this->load->view('laporan/ppepp', $data);
    }

    /**
     * Export ke PDF
     */
    public function exportPdf($type) {
        // Check authorization
        if (!$this->session->userdata('logged_in')) {
            show_error('Anda harus login untuk mengakses fitur ini', 403);
        }

        $filter = $this->_get_filters();
        $data = $filter;
        
        // Prepare data based on type
        switch($type) {
            case 'dashboard':
                $data['status_kerja'] = $this->tracer_model->get_status_distribution($filter['tahun'], $filter['prodi_id']);
                $data['gaji_prodi'] = $this->tracer_model->get_avg_salary_by_prodi($filter['tahun'], $filter['prodi_id']);
                $data['iku_score'] = $this->iku_model->calculate_current_iku($filter['tahun'], $filter['prodi_id']);
                break;
            case 'status_kerja':
                $data['results'] = $this->tracer_model->get_detailed_status($filter);
                break;
            case 'gaji':
                $data['ranges'] = $this->tracer_model->get_salary_ranges($filter);
                $data['stats'] = $this->tracer_model->get_salary_statistics($filter);
                break;
            case 'kompetensi':
                $data['radar_data'] = $this->tracer_model->get_competency_radar_data($filter['tahun'], $filter['prodi_id']);
                break;
            case 'iku':
                $data['iku_detail'] = $this->iku_model->get_detailed_calculation($filter['kohort_id'], $filter['prodi_id']);
                break;
            default:
                show_error('Tipe laporan tidak valid');
        }

        $html = $this->load->view('laporan/print/' . $type, $data, true);
        
        // Generate PDF using DOMPDF
        if (isset($this->pdf)) {
            $this->pdf->setPaper('A4', 'landscape');
            $this->pdf->load_html($html);
            $this->pdf->render();
            $this->pdf->stream("Laporan_" . ucfirst($type) . "_" . date('Ymd') . ".pdf", array("Attachment" => false));
        } else {
            // Fallback: return HTML with print styling
            echo $html;
        }
    }

    /**
     * Export ke Excel
     */
    public function exportExcel($type) {
        // Check authorization
        if (!$this->session->userdata('logged_in')) {
            show_error('Anda harus login untuk mengakses fitur ini', 403);
        }

        $this->load->library('PhpSpreadsheet');
        $filter = $this->_get_filters();
        
        $filename = "Laporan_" . ucfirst($type) . "_" . date('YmdHis') . ".xlsx";
        $filepath = FCPATH . 'uploads/exports/' . $filename;
        
        // Create spreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set header based on type
        $sheet->setCellValue('A1', 'Laporan: ' . ucfirst($type));
        $sheet->setCellValue('A2', 'Tanggal Export: ' . date('d/m/Y H:i:s'));
        $sheet->setCellValue('A3', 'Filter: Tahun=' . $filter['tahun'] . ', Prodi=' . ($filter['prodi_id'] ?? 'Semua'));
        
        // Fill data based on type (simplified example)
        $row = 5;
        switch($type) {
            case 'status_kerja':
                $sheet->setCellValue('A' . $row, 'Status');
                $sheet->setCellValue('B' . $row, 'Jumlah');
                $sheet->setCellValue('C' . $row, 'Persentase');
                $row++;
                
                $data = $this->tracer_model->get_status_distribution($filter['tahun'], $filter['prodi_id']);
                foreach ($data as $item) {
                    $sheet->setCellValue('A' . $row, $item['status']);
                    $sheet->setCellValue('B' . $row, $item['count']);
                    $sheet->setCellValue('C' . $row, $item['percentage'] . '%');
                    $row++;
                }
                break;
                
            case 'gaji':
                $sheet->setCellValue('A' . $row, 'Range Gaji');
                $sheet->setCellValue('B' . $row, 'Jumlah Alumni');
                $sheet->setCellValue('C' . $row, 'Persentase');
                $row++;
                
                $data = $this->tracer_model->get_salary_ranges($filter);
                foreach ($data as $item) {
                    $sheet->setCellValue('A' . $row, $item['range']);
                    $sheet->setCellValue('B' . $row, $item['count']);
                    $sheet->setCellValue('C' . $row, $item['percentage'] . '%');
                    $row++;
                }
                break;
                
            default:
                $sheet->setCellValue('A' . $row, 'Data sedang diproses...');
        }
        
        // Auto-size columns
        foreach (range('A', 'C') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Save file
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($filepath);
        
        // Force download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        readfile($filepath);
        exit;
    }

    /**
     * Helper: Get filters from GET/POST
     */
    private function _get_filters() {
        return [
            'tahun' => $this->input->get('tahun') ?? $this->input->post('tahun') ?? date('Y'),
            'prodi_id' => $this->input->get('prodi_id') ?? $this->input->post('prodi_id') ?? null,
            'kohort_id' => $this->input->get('kohort_id') ?? $this->input->post('kohort_id') ?? null,
            'status' => $this->input->get('status') ?? $this->input->post('status') ?? null,
            'range_gaji' => $this->input->get('range_gaji') ?? $this->input->post('range_gaji') ?? null,
            'angkatan' => $this->input->get('angkatan') ?? $this->input->post('angkatan') ?? null
        ];
    }
}
