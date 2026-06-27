<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * IkuCalculator Library
 * 
 * Engine perhitungan IKU-1 (Indikator Kinerja Utama)
 * Menghitung persentase alumni yang berhasil berdasarkan bobot aktivitas
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
 * @subpackage Libraries
 */
class IkuCalculator {

    protected $CI;
    
    // Threshold response rate minimum per kohort
    protected $min_response_threshold = 30; // persen
    
    // UMP rates cache
    protected $ump_cache = [];
    
    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->database();
        $this->CI->load->model('alumni/Alumni_model');
    }

    /**
     * Perhitungan utama IKU-1 untuk kohort dan prodi tertentu
     * 
     * Formula: IKU-1 = (Σ Bobot Responden / Total Responden Memenuhi Minimum) × 100
     * 
     * @param int $kohort_id ID Kohort
     * @param int|null $prodi_id ID Program Studi (null untuk semua prodi)
     * @return array Result calculation dengan status dan detail
     */
    public function calculate($kohort_id, $prodi_id = null)
    {
        $result = [
            'success' => false,
            'message' => '',
            'data' => []
        ];

        try {
            // Validasi input
            if (empty($kohort_id)) {
                throw new Exception('Kohort ID harus diisi');
            }

            // Cek apakah kohort ada
            $kohort = $this->_getKohort($kohort_id);
            if (!$kohort) {
                throw new Exception('Kohort tidak ditemukan');
            }

            // Check minimum response rate (BR-IKU-001)
            $response_check = $this->checkMinimumResponse($kohort_id, $prodi_id);
            
            if (!$response_check['valid']) {
                $result['message'] = 'Response rate belum memenuhi threshold minimum';
                $result['data'] = [
                    'response_rate' => $response_check['response_rate'],
                    'threshold' => $response_check['threshold'],
                    'total_responden' => $response_check['total_responden'],
                    'total_populasi' => $response_check['total_populasi']
                ];
                return $result;
            }

            // Ambil data alumni untuk perhitungan
            $alumni_list = $this->_getAlumniForCalculation($kohort_id, $prodi_id);
            
            if (empty($alumni_list)) {
                throw new Exception('Tidak ada data alumni untuk dihitung');
            }

            // Hitung bobot per alumni
            $total_bobot = 0;
            $total_responden = 0;
            $detail_perhitungan = [];

            foreach ($alumni_list as $alumni) {
                $bobot = $this->calculateBobot($alumni);
                $total_bobot += $bobot['bobot'];
                $total_responden++;
                
                $detail_perhitungan[] = [
                    'alumni_id' => $alumni['id'],
                    'nama' => $alumni['nama_lengkap'],
                    'nim' => $alumni['nim'],
                    'status' => $alumni['status_tracing'],
                    'bobot' => $bobot['bobot'],
                    'kategori' => $bobot['kategori'],
                    'keterangan' => $bobot['keterangan']
                ];
            }

            // Hitung IKU-1
            $iku_score = 0;
            if ($total_responden > 0) {
                $iku_score = ($total_bobot / $total_responden) * 100;
            }

            // Apply V&V penalty jika perlu (BR-IKU-003)
            $vv_rate = $this->_getVVRate($kohort_id, $prodi_id);
            $final_score = $this->applyVVPenalty($iku_score, $vv_rate);
            $penalty_applied = ($final_score < $iku_score);

            // Simpan hasil perhitungan ke database
            $calculation_id = $this->_saveCalculation(
                $kohort_id,
                $prodi_id,
                $final_score,
                $total_bobot,
                $total_responden,
                $detail_perhitungan,
                $vv_rate,
                $penalty_applied
            );

            $result['success'] = true;
            $result['message'] = 'Perhitungan IKU-1 berhasil';
            $result['data'] = [
                'calculation_id' => $calculation_id,
                'kohort_id' => $kohort_id,
                'prodi_id' => $prodi_id,
                'iku_score' => round($iku_score, 2),
                'final_score' => round($final_score, 2),
                'total_bobot' => $total_bobot,
                'total_responden' => $total_responden,
                'vv_rate' => $vv_rate,
                'penalty_applied' => $penalty_applied,
                'response_rate' => $response_check['response_rate'],
                'calculated_at' => date('Y-m-d H:i:s'),
                'detail' => $detail_perhitungan
            ];

        } catch (Exception $e) {
            $result['message'] = $e->getMessage();
            log_message('error', '[IKU Calculator] Error calculating IKU-1: ' . $e->getMessage());
        }

        return $result;
    }

    /**
     * Hitung bobot per alumni berdasarkan status dan kondisi
     * 
     * Rules:
     * - Bekerja:
     *   - Gaji ≥ 1.2 UMP + Masa tunggu ≤ 6 bulan = 1.0
     *   - Gaji ≥ 1.2 UMP + Masa tunggu 7-12 bulan = 0.8
     *   - Gaji < 1.2 UMP + Masa tunggu ≤ 6 bulan = 0.8
     *   - Gaji < 1.2 UMP + Masa tunggu 7-12 bulan = 0.6
     * - Wirausaha: 1.0 (omzet ≥ UMP) / 0.8 (omzet < UMP)
     * - Lanjut Studi: 0.6
     * - Belum Bekerja: 0
     * 
     * @param array|object $alumni Data alumni
     * @return array ['bobot' => float, 'kategori' => string, 'keterangan' => string]
     */
    public function calculateBobot($alumni)
    {
        $bobot = 0;
        $kategori = '';
        $keterangan = '';

        // Konversi ke array jika object
        if (is_object($alumni)) {
            $alumni = (array) $alumni;
        }

        $status = strtolower($alumni['status_tracing'] ?? $alumni['status_kerja'] ?? '');
        
        // BR-SUR-008: Prioritas bekerja > wirausaha > studi > belum
        // Rekonsiliasi jika ada konflik data
        $reconciled = $this->reconcileData($alumni);
        $status = $reconciled['status'];

        switch ($status) {
            case 'bekerja':
                list($bobot, $keterangan) = $this->_hitungBobotBekerja($alumni);
                $kategori = 'Bekerja';
                break;

            case 'wirausaha':
                list($bobot, $keterangan) = $this->_hitungBobotWirausaha($alumni);
                $kategori = 'Wirausaha';
                break;

            case 'melanjutkan_studi':
                $bobot = 0.6;
                $kategori = 'Lanjut Studi';
                $keterangan = 'Melanjutkan studi (S2/S3)';
                break;

            case 'belum_bekerja':
            default:
                $bobot = 0;
                $kategori = 'Belum Bekerja';
                $keterangan = 'Belum bekerja atau tidak responden';
                break;
        }

        return [
            'bobot' => $bobot,
            'kategori' => $kategori,
            'keterangan' => $keterangan
        ];
    }

    /**
     * Cek response rate vs threshold minimum
     * 
     * @param int $kohort_id ID Kohort
     * @param int|null $prodi_id ID Program Studi
     * @return array ['valid' => bool, 'response_rate' => float, 'threshold' => int]
     */
    public function checkMinimumResponse($kohort_id, $prodi_id = null)
    {
        $result = [
            'valid' => false,
            'response_rate' => 0,
            'threshold' => $this->min_response_threshold,
            'total_responden' => 0,
            'total_populasi' => 0
        ];

        // Get total populasi alumni
        $this->CI->db->select('COUNT(*) as total');
        $this->CI->db->where('kohort_id', $kohort_id);
        $this->CI->db->where('status_aktif', 1);
        $this->CI->db->where('deleted_at', NULL);
        
        if ($prodi_id) {
            $this->CI->db->where('prodi_id', $prodi_id);
        }
        
        $total_populasi = $this->CI->db->get('alumni')->row()->total;
        $result['total_populasi'] = $total_populasi;

        if ($total_populasi == 0) {
            return $result;
        }

        // Get total responden (sudah mengisi survey)
        $this->CI->db->select('COUNT(DISTINCT a.id) as total');
        $this->CI->db->from('alumni a');
        $this->CI->db->join('survey_responses sr', 'a.id = sr.alumni_id', 'inner');
        $this->CI->db->where('a.kohort_id', $kohort_id);
        $this->CI->db->where('a.status_aktif', 1);
        $this->CI->db->where('sr.status', 'submitted');
        $this->CI->db->where('a.deleted_at', NULL);
        
        if ($prodi_id) {
            $this->CI->db->where('a.prodi_id', $prodi_id);
        }
        
        $total_responden = $this->CI->db->get()->row()->total;
        $result['total_responden'] = $total_responden;

        // Hitung response rate
        $response_rate = ($total_responden / $total_populasi) * 100;
        $result['response_rate'] = round($response_rate, 2);

        // Validasi threshold (BR-IKU-001)
        $result['valid'] = ($response_rate >= $this->min_response_threshold);

        return $result;
    }

    /**
     * Apply penalty V&V jika rate < 80%
     * Kurangi skor sebesar 20% (BR-IKU-003)
     * 
     * @param float $score Skor IKU awal
     * @param float $vv_rate Rate verifikasi & validasi
     * @return float Skor setelah penalty
     */
    public function applyVVPenalty($score, $vv_rate)
    {
        if ($vv_rate < 80) {
            // Kurangi 20% dari skor
            return $score * 0.8;
        }
        return $score;
    }

    /**
     * Lookup UMP berdasarkan provinsi dan tahun
     * 
     * @param string $provinsi Nama provinsi
     * @param int $tahun Tahun
     * @return float|null Nominal UMP
     */
    public function getUMPByProvinsi($provinsi, $tahun)
    {
        $cache_key = strtolower($provinsi) . '_' . $tahun;
        
        if (isset($this->ump_cache[$cache_key])) {
            return $this->ump_cache[$cache_key];
        }

        $this->CI->db->select('nominal');
        $this->CI->db->where('provinsi', $provinsi);
        $this->CI->db->where('tahun', $tahun);
        $query = $this->CI->db->get('ump_provinsi');
        
        $ump = null;
        if ($query->num_rows() > 0) {
            $ump = (float) $query->row()->nominal;
            $this->ump_cache[$cache_key] = $ump;
        }

        return $ump;
    }

    /**
     * Rekonsiliasi data alumni jika ada konflik
     * Prioritas: bekerja > wirausaha > studi > belum (BR-SUR-008)
     * 
     * @param array|object $alumni Data alumni
     * @return array ['status' => string, 'reconciled' => bool]
     */
    public function reconcileData($alumni)
    {
        if (is_object($alumni)) {
            $alumni = (array) $alumni;
        }

        $status_tracing = $alumni['status_tracing'] ?? '';
        $status_kerja = $alumni['status_kerja'] ?? '';
        
        // Cek apakah ada data pekerjaan
        $has_pekerjaan = !empty($alumni['perusahaan']) || !empty($alumni['jabatan']);
        $has_gaji = !empty($alumni['gaji']) || !empty($alumni['gaji_aktual']);
        
        // Cek apakah ada data wirausaha
        $has_wirausaha = !empty($alumni['jenis_wirausaha']) || !empty($alumni['omzet']);
        
        // Cek apakah ada data studi lanjut
        $has_studi = !empty($alumni['studi_lanjut']) || !empty($alumni['institusi_studi']);

        // BR-SUR-008: Prioritas bekerja > wirausaha > studi > belum
        if ($has_pekerjaan && $has_gaji) {
            return ['status' => 'bekerja', 'reconciled' => ($status_tracing !== 'sudah_responden')];
        }
        
        if ($has_wirausaha) {
            return ['status' => 'wirausaha', 'reconciled' => ($status_tracing !== 'sudah_responden')];
        }
        
        if ($has_studi) {
            return ['status' => 'melanjutkan_studi', 'reconciled' => ($status_tracing !== 'sudah_responden')];
        }

        return ['status' => 'belum_bekerja', 'reconciled' => false];
    }

    /**
     * Hitung bobot untuk alumni bekerja
     * 
     * @param array $alumni Data alumni
     * @return array [bobot, keterangan]
     */
    protected function _hitungBobotBekerja($alumni)
    {
        $bobot = 0.6; // Default minimum
        $keterangan = [];

        // Dapatkan gaji aktual (BR-IKU-002: prioritas gaji_aktual > gaji_range)
        $gaji = !empty($alumni['gaji_aktual']) ? (float) $alumni['gaji_aktual'] : (float) ($alumni['gaji'] ?? 0);
        
        // Dapatkan UMP provinsi domisili (BR-IKU-007)
        $provinsi = $alumni['provinsi_domisili'] ?? '';
        $tahun = date('Y');
        $ump = $this->getUMPByProvinsi($provinsi, $tahun);
        
        if (!$ump) {
            // Fallback: gunakan UMP nasional atau default
            $ump = 3000000; // Default placeholder
        }

        $threshold_ump = $ump * 1.2; // 1.2 x UMP
        
        // Hitung masa tunggu
        $masa_tunggu = $this->_calculateMasaTunggu($alumni);
        
        // Tentukan bobot berdasarkan kombinasi gaji dan masa tunggu
        $gaji_high = ($gaji >= $threshold_ump);
        $wait_short = ($masa_tunggu !== null && $masa_tunggu <= 6);
        $wait_medium = ($masa_tunggu !== null && $masa_tunggu > 6 && $masa_tunggu <= 12);

        if ($gaji_high && $wait_short) {
            $bobot = 1.0;
            $keterangan[] = "Gaji ≥ 1.2 UMP (" . number_format($gaji, 0, ',', '.') . ")";
            $keterangan[] = "Masa tunggu ≤ 6 bulan (" . $masa_tunggu . " bulan)";
        } elseif ($gaji_high && $wait_medium) {
            $bobot = 0.8;
            $keterangan[] = "Gaji ≥ 1.2 UMP (" . number_format($gaji, 0, ',', '.') . ")";
            $keterangan[] = "Masa tunggu 7-12 bulan (" . $masa_tunggu . " bulan)";
        } elseif (!$gaji_high && $wait_short) {
            $bobot = 0.8;
            $keterangan[] = "Gaji < 1.2 UMP (" . number_format($gaji, 0, ',', '.') . ")";
            $keterangan[] = "Masa tunggu ≤ 6 bulan (" . $masa_tunggu . " bulan)";
        } elseif (!$gaji_high && $wait_medium) {
            $bobot = 0.6;
            $keterangan[] = "Gaji < 1.2 UMP (" . number_format($gaji, 0, ',', '.') . ")";
            $keterangan[] = "Masa tunggu 7-12 bulan (" . $masa_tunggu . " bulan)";
        } else {
            // Masa tunggu > 12 bulan atau tidak diketahui
            $bobot = $gaji_high ? 0.6 : 0.4;
            $keterangan[] = "Gaji " . ($gaji_high ? "≥" : "<") . " 1.2 UMP";
            $keterangan[] = "Masa tunggu > 12 bulan atau tidak diketahui";
        }

        return [$bobot, implode(', ', $keterangan)];
    }

    /**
     * Hitung bobot untuk alumni wirausaha
     * 
     * @param array $alumni Data alumni
     * @return array [bobot, keterangan]
     */
    protected function _hitungBobotWirausaha($alumni)
    {
        $bobot = 0.8; // Default
        $keterangan = [];

        $omzet = !empty($alumni['omzet']) ? (float) $alumni['omzet'] : 0;
        
        // Dapatkan UMP untuk perbandingan
        $provinsi = $alumni['provinsi_domisili'] ?? '';
        $tahun = date('Y');
        $ump = $this->getUMPByProvinsi($provinsi, $tahun);
        
        if (!$ump) {
            $ump = 3000000;
        }

        if ($omzet >= $ump) {
            $bobot = 1.0;
            $keterangan[] = "Omzet ≥ UMP (" . number_format($omzet, 0, ',', '.') . ")";
        } else {
            $bobot = 0.8;
            $keterangan[] = "Omzet < UMP (" . number_format($omzet, 0, ',', '.') . ")";
        }

        return [$bobot, implode(', ', $keterangan)];
    }

    /**
     * Calculate masa tunggu dalam bulan
     * 
     * @param array $alumni Data alumni
     * @return int|null Masa tunggu dalam bulan
     */
    protected function _calculateMasaTunggu($alumni)
    {
        $tanggal_yudisium = $alumni['tanggal_yudisium'] ?? $alumni['tanggal_lulus'] ?? null;
        $tanggal_mulai = $alumni['tanggal_mulai_kerja'] ?? null;

        if (empty($tanggal_yudisium) || empty($tanggal_mulai)) {
            return null;
        }

        try {
            $start = new DateTime($tanggal_yudisium);
            $end = new DateTime($tanggal_mulai);
            
            if ($end < $start) {
                return null;
            }
            
            $interval = $start->diff($end);
            return ($interval->y * 12) + $interval->m;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get data kohort
     * 
     * @param int $kohort_id Kohort ID
     * @return object|null
     */
    protected function _getKohort($kohort_id)
    {
        $query = $this->CI->db->get_where('kohorts', ['id' => $kohort_id]);
        return $query->row();
    }

    /**
     * Get alumni data untuk perhitungan IKU
     * 
     * @param int $kohort_id Kohort ID
     * @param int|null $prodi_id Prodi ID
     * @return array
     */
    protected function _getAlumniForCalculation($kohort_id, $prodi_id = null)
    {
        $this->CI->db->select('a.*, sr.submitted_at');
        $this->CI->db->from('alumni a');
        $this->CI->db->join('survey_responses sr', 'a.id = sr.alumni_id AND sr.status = "submitted"', 'left');
        $this->CI->db->where('a.kohort_id', $kohort_id);
        $this->CI->db->where('a.status_aktif', 1);
        $this->CI->db->where('a.deleted_at', NULL);
        
        if ($prodi_id) {
            $this->CI->db->where('a.prodi_id', $prodi_id);
        }
        
        $this->CI->db->order_by('a.nim', 'ASC');
        $query = $this->CI->db->get();
        
        return $query->result_array();
    }

    /**
     * Get V&V rate untuk kohort/prodi
     * 
     * @param int $kohort_id Kohort ID
     * @param int|null $prodi_id Prodi ID
     * @return float Percentage
     */
    protected function _getVVRate($kohort_id, $prodi_id = null)
    {
        $this->CI->db->select('COUNT(*) as total');
        $this->CI->db->from('alumni a');
        $this->CI->db->where('a.kohort_id', $kohort_id);
        $this->CI->db->where('a.status_aktif', 1);
        $this->CI->db->where('a.deleted_at', NULL);
        
        if ($prodi_id) {
            $this->CI->db->where('a.prodi_id', $prodi_id);
        }
        
        $total = $this->CI->db->get()->row()->total;
        
        if ($total == 0) {
            return 0;
        }

        $this->CI->db->select('COUNT(DISTINCT a.id) as verified');
        $this->CI->db->from('alumni a');
        $this->CI->db->where('a.kohort_id', $kohort_id);
        $this->CI->db->where('a.status_aktif', 1);
        $this->CI->db->where('a.deleted_at', NULL);
        $this->CI->db->where('a.is_verified', 1);
        
        if ($prodi_id) {
            $this->CI->db->where('a.prodi_id', $prodi_id);
        }
        
        $verified = $this->CI->db->get()->row()->verified;
        
        return ($verified / $total) * 100;
    }

    /**
     * Save calculation result to database
     * 
     * @param int $kohort_id Kohort ID
     * @param int|null $prodi_id Prodi ID
     * @param float $score Final score
     * @param float $total_bobot Total bobot
     * @param int $total_responden Total responden
     * @param array $detail Detail perhitungan
     * @param float $vv_rate V&V rate
     * @param bool $penalty_applied Whether penalty was applied
     * @return int|false Calculation ID or false on failure
     */
    protected function _saveCalculation($kohort_id, $prodi_id, $score, $total_bobot, $total_responden, $detail, $vv_rate, $penalty_applied)
    {
        $tahun_iku = date('Y');
        
        // Prepare mapping data (alumni IDs used)
        $mapping_data = array_column($detail, 'alumni_id');
        
        // Determine status capaian
        $target = 70; // Target default 70%
        $status_capaian = 'Belum';
        if ($score >= $target) {
            $status_capaian = 'Tercapai';
        }
        if ($score >= ($target * 1.2)) {
            $status_capaian = 'Melampaui';
        }

        $data = [
            'prodi_id' => $prodi_id,
            'kohort_id' => $kohort_id,
            'tahun_iku' => $tahun_iku,
            'iku_number' => 1,
            'numerator' => (int) $total_bobot,
            'denominator' => $total_responden,
            'percentage' => round($score, 2),
            'target_percentage' => $target,
            'status_capaian' => $status_capaian,
            'mapping_data' => json_encode($mapping_data)
        ];

        // Check if already exists for this period
        $this->CI->db->where('tahun_iku', $tahun_iku);
        $this->CI->db->where('prodi_id', $prodi_id ?: NULL);
        $this->CI->db->where('kohort_id', $kohort_id);
        $this->CI->db->where('iku_number', 1);
        $existing = $this->CI->db->get('iku_calculations')->row();

        if ($existing) {
            // Update existing
            $this->CI->db->where('id', $existing->id);
            $this->CI->db->update('iku_calculations', $data);
            return $existing->id;
        } else {
            // Insert new
            $this->CI->db->insert('iku_calculations', $data);
            return $this->CI->db->insert_id();
        }
    }
}
