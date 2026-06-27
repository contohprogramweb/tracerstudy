<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * IKU Controller
 * 
 * Mengelola perhitungan dan pelaporan IKU (Indikator Kinerja Utama)
 * Support CLI dan Web interface
 * 
 * Business Rules:
 * - BR-IKU-001: IKU hanya valid jika response rate ≥ minimum
 * - BR-IKU-002: Sumber kebenaran gaji_aktual > gaji_range
 * - BR-IKU-003: V&V rate < 80% = pengurangan skor 20%
 * - BR-IKU-004: Auto-calc daily 02:00 WIB + real-time saat update
 * - BR-IKU-005: Export Belmawa hanya admin_pusat_karir/super_admin
 * - BR-IKU-006: Data sudah dikirim immutable
 * - BR-IKU-007: UMP provinsi domisili alumni
 * - BR-SUR-008: Prioritas bekerja > wirausaha > studi > belum
 * 
 * @package Tracer Study
 * @subpackage IKU
 */
class Iku extends CI_Controller {

    protected $user_data;
    protected $is_cli;

    public function __construct()
    {
        parent::__construct();
        
        // Check if running from CLI
        $this->is_cli = php_sapi_name() === 'cli';
        
        // Load required libraries and models
        $this->load->library(['session', 'auth_lib']);
        $this->load->model(['alumni/Alumni_model']);
        $this->load->library('IkuCalculator');
        
        // Authentication check for web requests
        if (!$this->is_cli) {
            if (!$this->auth_lib->isLoggedIn()) {
                redirect('auth/login');
            }
            
            $this->user_data = $this->auth_lib->getUserData();
            
            // Check role access
            $allowed_roles = ['super_admin', 'admin_pusat_karir', 'admin_prodi', 'admin_fakultas'];
            if (!in_array($this->user_data['role'], $allowed_roles)) {
                show_error('Anda tidak memiliki akses ke modul IKU', 403);
            }
        }
    }

    /**
     * Trigger perhitungan IKU-1
     * CLI: php index.php iku calculate [kohort_id] [prodi_id]
     * Web: POST /iku/calculate
     * 
     * @param int|null $kohort_id Kohort ID
     * @param int|null $prodi_id Prodi ID
     */
    public function calculate($kohort_id = null, $prodi_id = null)
    {
        // Handle CLI parameters
        if ($this->is_cli) {
            $kohort_id = $this->input->server('argv')[2] ?? null;
            $prodi_id = $this->input->server('argv')[3] ?? null;
        } else {
            // Handle web POST
            if ($this->input->method() === 'post') {
                $kohort_id = $this->input->post('kohort_id');
                $prodi_id = $this->input->post('prodi_id');
            }
        }

        if (empty($kohort_id)) {
            $this->_output([
                'success' => false,
                'message' => 'Kohort ID harus diisi'
            ]);
            return;
        }

        // Execute calculation
        $result = $this->ikucalculator->calculate($kohort_id, $prodi_id);

        if ($this->is_cli) {
            echo "\n=== IKU-1 Calculation Result ===\n";
            echo "Status: " . ($result['success'] ? 'SUCCESS' : 'FAILED') . "\n";
            echo "Message: " . $result['message'] . "\n";
            
            if ($result['success']) {
                echo "IKU Score: " . $result['data']['final_score'] . "%\n";
                echo "Total Responden: " . $result['data']['total_responden'] . "\n";
                echo "Response Rate: " . $result['data']['response_rate'] . "%\n";
                echo "V&V Rate: " . $result['data']['vv_rate'] . "%\n";
                echo "Penalty Applied: " . ($result['data']['penalty_applied'] ? 'Yes' : 'No') . "\n";
            }
            echo "\n";
        } else {
            $this->_output($result);
        }
    }

    /**
     * Calculate all IKU untuk semua kohort aktif
     * CLI: php index.php iku calculateAll
     * Scheduled daily at 02:00 WIB (BR-IKU-004)
     */
    public function calculateAll()
    {
        if (!$this->is_cli) {
            show_error('Fungsi ini hanya dapat dijalankan dari CLI', 403);
            return;
        }

        echo "\n=== Starting Daily IKU Calculation ===\n";
        echo "Time: " . date('Y-m-d H:i:s') . " WIB\n\n";

        // Get all active kohorts
        $this->db->select('id, nama, tahun');
        $this->db->where('status', 'active');
        $kohorts = $this->db->get('kohorts')->result_array();

        if (empty($kohorts)) {
            echo "No active kohorts found.\n";
            return;
        }

        $success_count = 0;
        $fail_count = 0;

        foreach ($kohorts as $kohort) {
            echo "Processing Kohort: {$kohort['nama']} (ID: {$kohort['id']})...\n";
            
            $result = $this->ikucalculator->calculate($kohort['id']);
            
            if ($result['success']) {
                echo "  ✓ Success - IKU Score: {$result['data']['final_score']}%\n";
                $success_count++;
            } else {
                echo "  ✗ Failed - {$result['message']}\n";
                $fail_count++;
            }
        }

        echo "\n=== Calculation Summary ===\n";
        echo "Total Kohorts: " . count($kohorts) . "\n";
        echo "Success: {$success_count}\n";
        echo "Failed: {$fail_count}\n";
        echo "Completed: " . date('Y-m-d H:i:s') . "\n\n";

        // Log to activity_logs
        $this->_logActivity('iku_calculate_all', [
            'total_kohorts' => count($kohorts),
            'success' => $success_count,
            'failed' => $fail_count
        ]);
    }

    /**
     * Dashboard IKU
     * View: gauge chart, progress kohort, tabel detail
     */
    public function dashboard()
    {
        $data['page_title'] = 'Dashboard IKU';
        $data['user'] = $this->user_data;

        // Get filter parameters
        $tahun = $this->input->get('tahun') ?: date('Y');
        $prodi_id = $this->input->get('prodi_id');
        $fakultas_id = $this->input->get('fakultas_id');

        // Get all kohorts for filter
        $this->db->select('id, nama, tahun');
        $this->db->order_by('tahun', 'DESC');
        $data['kohorts'] = $this->db->get('kohorts')->result_array();

        // Get prodi list
        $this->db->select('p.id, p.nama, f.nama as fakultas_nama');
        $this->db->from('program_studi p');
        $this->db->join('fakultas f', 'p.fakultas_id = f.id', 'left');
        if ($fakultas_id) {
            $this->db->where('p.fakultas_id', $fakultas_id);
        }
        $this->db->order_by('p.nama');
        $data['prodis'] = $this->db->get()->result_array();

        // Get IKU calculation results
        $this->db->select('ic.*, k.nama as kohort_nama, p.nama as prodi_nama');
        $this->db->from('iku_calculations ic');
        $this->db->join('kohorts k', 'ic.kohort_id = k.id');
        $this->db->join('program_studi p', 'ic.prodi_id = p.id', 'left');
        $this->db->where('ic.tahun_iku', $tahun);
        $this->db->where('ic.iku_number', 1);
        
        if ($prodi_id) {
            $this->db->where('ic.prodi_id', $prodi_id);
        }
        
        $this->db->order_by('ic.percentage', 'DESC');
        $data['iku_results'] = $this->db->get()->result_array();

        // Calculate summary statistics
        $data['summary'] = $this->_calculateSummary($tahun, $prodi_id);

        $this->load->view('templates/header', $data);
        $this->load->view('iku/dashboard', $data);
        $this->load->view('templates/footer');
    }

    /**
     * Detail perhitungan IKU per alumni
     * 
     * @param int $id IKU calculation ID
     */
    public function detail($id)
    {
        $data['page_title'] = 'Detail Perhitungan IKU';
        $data['user'] = $this->user_data;

        // Get calculation data
        $this->db->select('ic.*, k.nama as kohort_nama, p.nama as prodi_nama');
        $this->db->from('iku_calculations ic');
        $this->db->join('kohorts k', 'ic.kohort_id = k.id');
        $this->db->join('program_studi p', 'ic.prodi_id = p.id', 'left');
        $this->db->where('ic.id', $id);
        $data['calculation'] = $this->db->get()->row();

        if (!$data['calculation']) {
            show_error('Data perhitungan tidak ditemukan', 404);
            return;
        }

        // Get detail alumni from mapping_data
        $mapping_ids = json_decode($data['calculation']->mapping_data, true) ?: [];
        
        if (!empty($mapping_ids)) {
            $this->db->select('a.id, a.nim, a.nama_lengkap, a.status_tracing, a.provinsi_domisili, a.gaji, a.gaji_aktual');
            $this->db->where_in('a.id', $mapping_ids);
            $this->db->order_by('a.nim');
            $data['alumni_detail'] = $this->db->get('alumni a')->result_array();
            
            // Calculate bobot for each alumni
            foreach ($data['alumni_detail'] as &$alumni) {
                $bobot_result = $this->ikucalculator->calculateBobot($alumni);
                $alumni['bobot'] = $bobot_result['bobot'];
                $alumni['kategori'] = $bobot_result['kategori'];
                $alumni['keterangan'] = $bobot_result['keterangan'];
            }
        } else {
            $data['alumni_detail'] = [];
        }

        $this->load->view('templates/header', $data);
        $this->load->view('iku/detail', $data);
        $this->load->view('templates/footer');
    }

    /**
     * Verifikasi data alumni pending
     * List alumni yang perlu diverifikasi
     */
    public function verifikasi()
    {
        $data['page_title'] = 'Verifikasi Data Alumni';
        $data['user'] = $this->user_data;

        // Only admin_pusat_karir and super_admin can verify (BR-IKU-005)
        if (!in_array($this->user_data['role'], ['super_admin', 'admin_pusat_karir'])) {
            show_error('Anda tidak memiliki akses untuk verifikasi data', 403);
            return;
        }

        // Get filter parameters
        $status = $this->input->get('status') ?: 'pending';
        $kohort_id = $this->input->get('kohort_id');

        // Get pending verification list
        $this->db->select('vd.*, a.nim, a.nama_lengkap, u.nama as verifikator_nama');
        $this->db->from('verifikasi_data vd');
        $this->db->join('alumni a', 'vd.alumni_id = a.id');
        $this->db->join('users u', 'vd.verifikator_id = u.id', 'left');
        $this->db->where('vd.status', $status);
        
        if ($kohort_id) {
            $this->db->where('a.kohort_id', $kohort_id);
        }
        
        $this->db->order_by('vd.created_at', 'DESC');
        $data['verifications'] = $this->db->get()->result_array();

        // Get kohorts for filter
        $this->db->select('id, nama');
        $this->db->order_by('tahun', 'DESC');
        $data['kohorts'] = $this->db->get('kohorts')->result_array();

        $this->load->view('templates/header', $data);
        $this->load->view('iku/verifikasi', $data);
        $this->load->view('templates/footer');
    }

    /**
     * Approve/Reject verifikasi
     * 
     * @param int $id Verification ID
     */
    public function approveVerification($id)
    {
        if (!in_array($this->user_data['role'], ['super_admin', 'admin_pusat_karir'])) {
            $this->_output(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        $action = $this->input->post('action'); // approve or reject
        $catatan = $this->input->post('catatan');

        $this->db->trans_begin();

        try {
            $update_data = [
                'status' => $action === 'approve' ? 'approved' : 'rejected',
                'verified_at' => date('Y-m-d H:i:s'),
                'catatan' => $catatan
            ];

            $this->db->where('id', $id);
            $this->db->update('verifikasi_data', $update_data);

            // Update alumni status if approved
            if ($action === 'approve') {
                $verif = $this->db->get_where('verifikasi_data', ['id' => $id])->row();
                $this->db->where('id', $verif->alumni_id);
                $this->db->update('alumni', ['is_verified' => 1]);
                
                // Trigger recalculation (BR-IKU-004)
                $alumni = $this->db->get_where('alumni', ['id' => $verif->alumni_id])->row();
                if ($alumni) {
                    $this->ikucalculator->calculate($alumni->kohort_id, $alumni->prodi_id);
                }
            }

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Database error');
            }

            $this->db->trans_commit();
            $this->_output(['success' => true, 'message' => 'Verifikasi berhasil']);

        } catch (Exception $e) {
            $this->db->trans_rollback();
            $this->_output(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Export template Belmawa
     * Hanya untuk admin_pusat_karir/super_admin (BR-IKU-005)
     * Data immutable setelah dikirim (BR-IKU-006)
     * 
     * @param int $kohort_id Kohort ID
     * @param int|null $prodi_id Prodi ID
     */
    public function exportBelmawa($kohort_id = null, $prodi_id = null)
    {
        // Authorization check (BR-IKU-005)
        if (!in_array($this->user_data['role'], ['super_admin', 'admin_pusat_karir'])) {
            show_error('Hanya admin_pusat_karir dan super_admin yang dapat export data Belmawa', 403);
            return;
        }

        // Get parameters from POST if not in URL
        if (!$kohort_id) {
            $kohort_id = $this->input->post('kohort_id');
            $prodi_id = $this->input->post('prodi_id');
        }

        if (empty($kohort_id)) {
            show_error('Kohort ID harus diisi', 400);
            return;
        }

        // Get calculation result
        $this->db->select('*');
        $this->db->where('kohort_id', $kohort_id);
        $this->db->where('prodi_id', $prodi_id ?: NULL);
        $this->db->where('iku_number', 1);
        $this->db->order_by('created_at', 'DESC');
        $calculation = $this->db->get('iku_calculations')->row();

        if (!$calculation) {
            show_error('Data perhitungan IKU belum tersedia. Silakan lakukan perhitungan terlebih dahulu.', 404);
            return;
        }

        // Prepare Belmawa format data
        $data['export_data'] = $this->_prepareBelmawaExport($kohort_id, $prodi_id);
        $data['calculation'] = $calculation;
        $data['generated_at'] = date('Y-m-d H:i:s');
        $data['immutable'] = true; // BR-IKU-006

        // Log export activity
        $this->_logActivity('export_belmawa', [
            'kohort_id' => $kohort_id,
            'prodi_id' => $prodi_id,
            'calculation_id' => $calculation->id
        ]);

        $this->load->view('iku/export', $data);
    }

    /**
     * Laporan Triwulan
     * 
     * @param int|null $tahun Tahun
     * @param int|null $quarter Quarter (1-4)
     */
    public function triwulan($tahun = null, $quarter = null)
    {
        $data['page_title'] = 'Laporan Triwulan IKU';
        $data['user'] = $this->user_data;

        // Get parameters from URL or POST
        if (!$tahun) {
            $tahun = $this->input->get('tahun') ?: date('Y');
        }
        if (!$quarter) {
            $quarter = $this->input->get('quarter') ?: 1;
        }

        // Validate quarter
        $quarter = max(1, min(4, (int) $quarter));

        // Calculate date range for quarter
        $date_ranges = $this->_getQuarterDateRange($tahun, $quarter);
        $data['quarter_info'] = [
            'tahun' => $tahun,
            'quarter' => $quarter,
            'start_date' => $date_ranges['start'],
            'end_date' => $date_ranges['end'],
            'label' => "Triwulan {$quarter} {$tahun}"
        ];

        // Get IKU calculations within quarter
        $this->db->select('ic.*, k.nama as kohort_nama, p.nama as prodi_nama');
        $this->db->from('iku_calculations ic');
        $this->db->join('kohorts k', 'ic.kohort_id = k.id');
        $this->db->join('program_studi p', 'ic.prodi_id = p.id', 'left');
        $this->db->where('ic.tahun_iku', $tahun);
        $this->db->where('ic.iku_number', 1);
        $this->db->where('ic.created_at >=', $date_ranges['start']);
        $this->db->where('ic.created_at <=', $date_ranges['end']);
        $this->db->order_by('ic.percentage', 'DESC');
        $data['iku_results'] = $this->db->get()->result_array();

        // Get trend data (previous quarters)
        $data['trend'] = $this->_getTrendData($tahun, $quarter);

        $this->load->view('templates/header', $data);
        $this->load->view('iku/triwulan', $data);
        $this->load->view('templates/footer');
    }

    /**
     * Helper: Calculate summary statistics
     */
    protected function _calculateSummary($tahun, $prodi_id = null)
    {
        $summary = [
            'total_calculations' => 0,
            'avg_iku_score' => 0,
            'highest_score' => 0,
            'lowest_score' => 100,
            'tercapai_count' => 0,
            'melampaui_count' => 0
        ];

        $this->db->select('percentage, status_capaian');
        $this->db->where('tahun_iku', $tahun);
        $this->db->where('iku_number', 1);
        
        if ($prodi_id) {
            $this->db->where('prodi_id', $prodi_id);
        }
        
        $results = $this->db->get('iku_calculations')->result_array();

        if (!empty($results)) {
            $summary['total_calculations'] = count($results);
            
            $scores = array_column($results, 'percentage');
            $summary['avg_iku_score'] = round(array_sum($scores) / count($scores), 2);
            $summary['highest_score'] = max($scores);
            $summary['lowest_score'] = min($scores);
            
            foreach ($results as $row) {
                if ($row['status_capaian'] === 'Tercapai') {
                    $summary['tercapai_count']++;
                } elseif ($row['status_capaian'] === 'Melampaui') {
                    $summary['melampaui_count']++;
                }
            }
        }

        return $summary;
    }

    /**
     * Helper: Get quarter date range
     */
    protected function _getQuarterDateRange($tahun, $quarter)
    {
        $ranges = [
            1 => ['01-01', '03-31'],
            2 => ['04-01', '06-30'],
            3 => ['07-01', '09-30'],
            4 => ['10-01', '12-31']
        ];

        $range = $ranges[$quarter];
        
        return [
            'start' => "{$tahun}-{$range[0]} 00:00:00",
            'end' => "{$tahun}-{$range[1]} 23:59:59"
        ];
    }

    /**
     * Helper: Get trend data for previous quarters
     */
    protected function _getTrendData($tahun, $current_quarter)
    {
        $trend = [];
        
        // Get previous 3 quarters
        for ($i = 1; $i <= 3; $i++) {
            $q = $current_quarter - $i;
            $y = $tahun;
            
            if ($q < 1) {
                $q += 4;
                $y -= 1;
            }
            
            $date_range = $this->_getQuarterDateRange($y, $q);
            
            $this->db->select_avg('percentage', 'avg_score');
            $this->db->where('tahun_iku', $y);
            $this->db->where('iku_number', 1);
            $this->db->where('created_at >=', $date_range['start']);
            $this->db->where('created_at <=', $date_range['end']);
            $result = $this->db->get('iku_calculations')->row();
            
            $trend[] = [
                'quarter' => "Q{$q}/{$y}",
                'avg_score' => round($result->avg_score ?? 0, 2)
            ];
        }
        
        return array_reverse($trend);
    }

    /**
     * Helper: Prepare Belmawa export data
     */
    protected function _prepareBelmawaExport($kohort_id, $prodi_id = null)
    {
        $export_data = [];
        
        // Get calculation
        $this->db->select('*');
        $this->db->where('kohort_id', $kohort_id);
        $this->db->where('prodi_id', $prodi_id ?: NULL);
        $this->db->where('iku_number', 1);
        $this->db->order_by('created_at', 'DESC');
        $calc = $this->db->get('iku_calculations')->row();
        
        if ($calc) {
            $export_data = [
                'institution_code' => '', // Fill from settings
                'study_program_code' => '', // Fill from prodi
                'academic_year' => $calc->tahun_iku,
                'iku_number' => 1,
                'numerator' => $calc->numerator,
                'denominator' => $calc->denominator,
                'percentage' => $calc->percentage,
                'target' => $calc->target_percentage,
                'achievement_status' => $calc->status_capaian,
                'calculation_date' => $calc->created_at
            ];
        }
        
        return $export_data;
    }

    /**
     * Helper: Log activity
     */
    protected function _logActivity($activity_type, $data)
    {
        $log_data = [
            'user_id' => $this->user_data['id'] ?? null,
            'activity_type' => $activity_type,
            'description' => json_encode($data),
            'ip_address' => $this->input->ip_address(),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('activity_logs', $log_data);
    }

    /**
     * Helper: Output JSON or view based on context
     */
    protected function _output($data)
    {
        if ($this->is_cli) {
            echo json_encode($data, JSON_PRETTY_PRINT) . "\n";
        } else {
            // For AJAX requests
            if ($this->input->is_ajax_request()) {
                header('Content-Type: application/json');
                echo json_encode($data);
            } else {
                // Redirect with flashdata
                $this->session->set_flashdata('result', $data);
                redirect('iku/dashboard');
            }
        }
    }
}
