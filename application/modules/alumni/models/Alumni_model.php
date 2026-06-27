<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Alumni Model
 * 
 * Mengelola data alumni dengan fitur filtering, import/export, dan sync PDDikti
 * 
 * Business Rules:
 * - BR-ALM-001: Hanya bisa isi survey kohort aktif
 * - BR-ALM-002: NIM immutable setelah create
 * - BR-ALM-003: Alumni belum_bekerja tidak boleh isi gaji/perusahaan/jabatan
 * - BR-ALM-004: Masa tunggu auto-calculate, reject jika negatif
 * - BR-ALM-005: Alumni belum verifikasi email boleh isi survey tapi tidak masuk IKU
 * - BR-ALM-006: Import Excel wajib kolom NIM, Nama, Prodi, Tahun Lulus
 * - BR-ALM-007: Alumni tidak boleh isi survey kohort yang sama 2x
 * - BR-ALM-008: Update profil memicu recalculation IKU
 * - BR-ALM-009: Data PDDikti precedence lebih tinggi dari manual
 * - BR-ALM-010: Alumni nonaktif dikecualikan dari target populasi
 * 
 * @package Tracer Study
 * @subpackage Alumni
 */
class Alumni_model extends MY_Model {

    protected $table_name = 'alumni';
    protected $primary_key = 'id';
    protected $soft_delete = TRUE;
    protected $deleted_field = 'deleted_at';
    
    // Fillable fields untuk create/update
    protected $fillable = [
        'nim',
        'nama',
        'email',
        'no_hp',
        'prodi_id',
        'kohort_id',
        'tahun_lulus',
        'tanggal_yudisium',
        'status_kerja',
        'perusahaan',
        'jabatan',
        'tanggal_mulai_kerja',
        'gaji',
        'lokasi_kerja',
        'jenis_pekerjaan',
        'kesesuaian_bidang',
        'email_verified',
        'status_aktif',
        'foto_url',
        'linkedin_url',
        'instagram_url',
        'alamat_domisili',
        'kota_domisili',
        'provinsi_domisili',
        'negara_domisili'
    ];

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Get alumni dengan filter kompleks
     * 
     * @param array $filters Filter parameters (kohort, prodi, status_kerja, gaji, search)
     * @param int $page Page number
     * @param int $per_page Items per page
     * @return array ['data' => [], 'total' => int, 'page' => int, 'per_page' => int]
     */
    public function getAlumniWithFilter($filters = [], $page = 1, $per_page = 25)
    {
        $offset = ($page - 1) * $per_page;
        
        // Build query utama
        $this->db->select('a.*, p.nama as prodi_nama, k.nama as kohort_nama');
        $this->db->from('alumni a');
        $this->db->join('prodi p', 'a.prodi_id = p.id', 'left');
        $this->db->join('kohort k', 'a.kohort_id = k.id', 'left');
        
        // Apply soft delete filter
        if ($this->soft_delete) {
            $this->db->where('a.deleted_at', NULL);
        }
        
        // Filter: Kohort
        if (!empty($filters['kohort_id'])) {
            $this->db->where('a.kohort_id', $filters['kohort_id']);
        }
        
        // Filter: Prodi
        if (!empty($filters['prodi_id'])) {
            $this->db->where('a.prodi_id', $filters['prodi_id']);
        }
        
        // Filter: Status Kerja
        if (!empty($filters['status_kerja'])) {
            $this->db->where('a.status_kerja', $filters['status_kerja']);
        }
        
        // Filter: Gaji (range)
        if (!empty($filters['gaji_min'])) {
            $this->db->where('a.gaji >=', $filters['gaji_min']);
        }
        if (!empty($filters['gaji_max'])) {
            $this->db->where('a.gaji <=', $filters['gaji_max']);
        }
        
        // Filter: Search (NIM, Nama, Email)
        if (!empty($filters['search'])) {
            $search_term = $this->db->escape_like_str($filters['search']);
            $this->db->group_start();
            $this->db->like('a.nim', $filters['search']);
            $this->db->or_like('a.nama', $filters['search']);
            $this->db->or_like('a.email', $filters['search']);
            $this->db->group_end();
        }
        
        // Filter: Status Aktif (BR-ALM-010)
        if (isset($filters['status_aktif']) && $filters['status_aktif'] !== '') {
            $this->db->where('a.status_aktif', $filters['status_aktif']);
        } else {
            // Default hanya tampilkan alumni aktif
            $this->db->where('a.status_aktif', 1);
        }
        
        // Hitung total records
        $total = $this->db->count_all_results('', FALSE);
        
        // Reset dan rebuild query untuk data
        $this->db->reset_query();
        $this->db->select('a.*, p.nama as prodi_nama, k.nama as kohort_nama');
        $this->db->from('alumni a');
        $this->db->join('prodi p', 'a.prodi_id = p.id', 'left');
        $this->db->join('kohort k', 'a.kohort_id = k.id', 'left');
        
        if ($this->soft_delete) {
            $this->db->where('a.deleted_at', NULL);
        }
        
        // Re-apply all filters
        if (!empty($filters['kohort_id'])) {
            $this->db->where('a.kohort_id', $filters['kohort_id']);
        }
        if (!empty($filters['prodi_id'])) {
            $this->db->where('a.prodi_id', $filters['prodi_id']);
        }
        if (!empty($filters['status_kerja'])) {
            $this->db->where('a.status_kerja', $filters['status_kerja']);
        }
        if (!empty($filters['gaji_min'])) {
            $this->db->where('a.gaji >=', $filters['gaji_min']);
        }
        if (!empty($filters['gaji_max'])) {
            $this->db->where('a.gaji <=', $filters['gaji_max']);
        }
        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('a.nim', $filters['search']);
            $this->db->or_like('a.nama', $filters['search']);
            $this->db->or_like('a.email', $filters['search']);
            $this->db->group_end();
        }
        if (isset($filters['status_aktif']) && $filters['status_aktif'] !== '') {
            $this->db->where('a.status_aktif', $filters['status_aktif']);
        } else {
            $this->db->where('a.status_aktif', 1);
        }
        
        // Ordering
        $this->db->order_by('a.tahun_lulus', 'DESC');
        $this->db->order_by('a.nim', 'ASC');
        
        // Pagination
        $this->db->limit($per_page, $offset);
        $query = $this->db->get();
        
        $data = $query->result_array();
        
        // Calculate masa_tunggu untuk setiap alumni
        foreach ($data as &$row) {
            if (!empty($row['tanggal_yudisium']) && !empty($row['tanggal_mulai_kerja'])) {
                $row['masa_tunggu'] = $this->calculateMasaTunggu(
                    $row['tanggal_mulai_kerja'], 
                    $row['tanggal_yudisium']
                );
            } else {
                $row['masa_tunggu'] = NULL;
            }
        }
        
        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'per_page' => $per_page,
            'total_pages' => ceil($total / $per_page)
        ];
    }

    /**
     * Import batch alumni dari Excel/CSV
     * Skip duplicate NIM (BR-ALM-002, BR-ALM-006)
     * 
     * @param array $data_array Array of alumni data
     * @return array ['inserted' => count, 'skipped' => count, 'errors' => []]
     */
    public function importBatch($data_array)
    {
        $inserted = 0;
        $skipped = 0;
        $errors = [];
        
        $this->db->trans_begin();
        
        foreach ($data_array as $index => $row) {
            try {
                // Validasi kolom wajib (BR-ALM-006)
                if (empty($row['nim']) || empty($row['nama']) || empty($row['prodi_id']) || empty($row['tahun_lulus'])) {
                    $errors[] = [
                        'row' => $index + 2, // +2 karena header dan 0-index
                        'message' => 'Kolom wajib tidak lengkap (NIM, Nama, Prodi, Tahun Lulus)'
                    ];
                    continue;
                }
                
                // Cek duplicate NIM (BR-ALM-002)
                $existing = $this->getByNIM($row['nim']);
                if ($existing) {
                    $skipped++;
                    $errors[] = [
                        'row' => $index + 2,
                        'message' => 'NIM sudah ada: ' . $row['nim']
                    ];
                    continue;
                }
                
                // Prepare data untuk insert
                $insert_data = [
                    'nim' => $row['nim'],
                    'nama' => $row['nama'],
                    'email' => $row['email'] ?? NULL,
                    'no_hp' => $row['no_hp'] ?? NULL,
                    'prodi_id' => $row['prodi_id'],
                    'kohort_id' => $row['kohort_id'] ?? NULL,
                    'tahun_lulus' => $row['tahun_lulus'],
                    'tanggal_yudisium' => $row['tanggal_yudisium'] ?? NULL,
                    'status_kerja' => 'belum_bekerja', // Default
                    'email_verified' => 0,
                    'status_aktif' => 1
                ];
                
                // Insert
                $this->db->insert('alumni', $insert_data);
                $inserted++;
                
            } catch (Exception $e) {
                $errors[] = [
                    'row' => $index + 2,
                    'message' => $e->getMessage()
                ];
            }
        }
        
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return [
                'success' => FALSE,
                'inserted' => 0,
                'skipped' => 0,
                'errors' => [['message' => 'Database transaction failed']]
            ];
        }
        
        $this->db->trans_commit();
        
        return [
            'success' => TRUE,
            'inserted' => $inserted,
            'skipped' => $skipped,
            'errors' => $errors
        ];
    }

    /**
     * Get alumni by NIM
     * 
     * @param string $nim NIM alumni
     * @param bool $include_deleted Include soft deleted records
     * @return object|NULL
     */
    public function getByNIM($nim, $include_deleted = FALSE)
    {
        if ($this->soft_delete && !$include_deleted) {
            $this->db->where('deleted_at', NULL);
        }
        
        $query = $this->db->get_where('alumni', ['nim' => $nim]);
        return $query->row();
    }

    /**
     * Get alumni by Kohort ID
     * 
     * @param int $kohort_id Kohort ID
     * @param bool $only_active Only active alumni (BR-ALM-010)
     * @return array
     */
    public function getByKohort($kohort_id, $only_active = TRUE)
    {
        if ($this->soft_delete) {
            $this->db->where('deleted_at', NULL);
        }
        
        $this->db->where('kohort_id', $kohort_id);
        
        if ($only_active) {
            $this->db->where('status_aktif', 1);
        }
        
        $this->db->order_by('nim', 'ASC');
        $query = $this->db->get('alumni');
        
        return $query->result_array();
    }

    /**
     * Update status kerja alumni
     * 
     * @param int $id Alumni ID
     * @param string $status Status kerja (bekerja/belum_bekerja/wirausaha/melanjutkan_studi)
     * @return bool
     */
    public function updateStatusKerja($id, $status)
    {
        $valid_statuses = ['bekerja', 'belum_bekerja', 'wirausaha', 'melanjutkan_studi'];
        
        if (!in_array($status, $valid_statuses)) {
            return FALSE;
        }
        
        $data = [
            'status_kerja' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // BR-ALM-003: Jika belum_bekerja, kosongkan data perusahaan/gaji/jabatan
        if ($status === 'belum_bekerja') {
            $data['perusahaan'] = NULL;
            $data['jabatan'] = NULL;
            $data['gaji'] = NULL;
            $data['tanggal_mulai_kerja'] = NULL;
            $data['lokasi_kerja'] = NULL;
            $data['jenis_pekerjaan'] = NULL;
            $data['kesesuaian_bidang'] = NULL;
        }
        
        $this->db->where('id', $id);
        return $this->db->update('alumni', $data);
    }

    /**
     * Calculate masa tunggu (waktu antara yudisium sampai mulai kerja)
     * 
     * @param string $tanggal_mulai Tanggal mulai kerja
     * @param string $tanggal_yudisium Tanggal yudisium
     * @return int|FALSE Masa tunggu dalam bulan, FALSE jika negatif (BR-ALM-004)
     */
    public function calculateMasaTunggu($tanggal_mulai, $tanggal_yudisium)
    {
        if (empty($tanggal_mulai) || empty($tanggal_yudisium)) {
            return NULL;
        }
        
        $start = new DateTime($tanggal_yudisium);
        $end = new DateTime($tanggal_mulai);
        
        // BR-ALM-004: Reject jika negatif (mulai kerja sebelum yudisium)
        if ($end < $start) {
            return FALSE;
        }
        
        $interval = $start->diff($end);
        
        // Return dalam bulan
        return ($interval->y * 12) + $interval->m;
    }

    /**
     * Get statistics by Kohort
     * 
     * @param int $kohort_id Kohort ID
     * @return array Statistics data
     */
    public function getStatsByKohort($kohort_id)
    {
        $stats = [];
        
        // Total alumni
        $this->db->select('COUNT(*) as total');
        $this->db->where('kohort_id', $kohort_id);
        if ($this->soft_delete) {
            $this->db->where('deleted_at', NULL);
        }
        $this->db->where('status_aktif', 1); // BR-ALM-010
        $total = $this->db->get('alumni')->row()->total;
        
        // Count by status kerja
        $this->db->select('status_kerja, COUNT(*) as count');
        $this->db->where('kohort_id', $kohort_id);
        if ($this->soft_delete) {
            $this->db->where('deleted_at', NULL);
        }
        $this->db->where('status_aktif', 1);
        $this->db->group_by('status_kerja');
        $status_result = $this->db->get('alumni')->result_array();
        
        $status_breakdown = [];
        foreach ($status_result as $row) {
            $status_breakdown[$row['status_kerja']] = $row['count'];
        }
        
        // Average masa tunggu
        $this->db->select('AVTIMESTAMPDIFF(MONTH, tanggal_yudisium, tanggal_mulai_kerja) as avg_masa_tunggu');
        $this->db->where('kohort_id', $kohort_id);
        if ($this->soft_delete) {
            $this->db->where('deleted_at', NULL);
        }
        $this->db->where('status_aktif', 1);
        $this->db->where('status_kerja', 'bekerja');
        $this->db->where('tanggal_mulai_kerja IS NOT NULL', NULL, FALSE);
        $this->db->where('tanggal_yudisium IS NOT NULL', NULL, FALSE);
        $avg_result = $this->db->get('alumni')->row();
        
        // Email verified count
        $this->db->select('COUNT(*) as verified');
        $this->db->where('kohort_id', $kohort_id);
        if ($this->soft_delete) {
            $this->db->where('deleted_at', NULL);
        }
        $this->db->where('email_verified', 1);
        $verified = $this->db->get('alumni')->row()->verified;
        
        return [
            'total' => $total,
            'status_breakdown' => $status_breakdown,
            'bekerja' => $status_breakdown['bekerja'] ?? 0,
            'belum_bekerja' => $status_breakdown['belum_bekerja'] ?? 0,
            'wirausaha' => $status_breakdown['wirausaha'] ?? 0,
            'melanjutkan_studi' => $status_breakdown['melanjutkan_studi'] ?? 0,
            'avg_masa_tunggu' => round($avg_result->avg_masa_tunggu ?? 0, 2),
            'email_verified' => $verified,
            'email_unverified' => $total - $verified
        ];
    }

    /**
     * Trigger recalculation IKU setelah update alumni
     * 
     * @param int $alumni_id Alumni ID
     * @return bool TRUE jika berhasil trigger
     */
    public function triggerIKURecalculation($alumni_id)
    {
        // Get alumni data
        $alumni = $this->find($alumni_id);
        
        if (!$alumni) {
            return FALSE;
        }
        
        // Log activity untuk audit trail
        $this->load->helper('tracer_audit');
        audit_log(
            'recalculate',
            'iku',
            'Trigger IKU recalculation for alumni ID: ' . $alumni_id,
            $this->session->userdata('user_data')['id'] ?? NULL
        );
        
        // Insert ke queue untuk processing async (jika ada)
        // Atau langsung call model IKU untuk recalculate
        $this->db->insert('iku_calculation_queue', [
            'alumni_id' => $alumni_id,
            'kohort_id' => $alumni->kohort_id,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        return TRUE;
    }

    /**
     * Sync data dari PDDikti NeoFeeder
     * Precedence lebih tinggi dari data manual (BR-ALM-009)
     * 
     * @param string $nim NIM alumni
     * @param array $pddikti_data Data dari PDDikti
     * @return bool TRUE jika berhasil sync
     */
    public function syncFromPDDikti($nim, $pddikti_data)
    {
        $existing = $this->getByNIM($nim);
        
        if (!$existing) {
            // Create new alumni from PDDikti data
            $insert_data = [
                'nim' => $nim,
                'nama' => $pddikti_data['nama'] ?? NULL,
                'email' => $pddikti_data['email'] ?? NULL,
                'prodi_id' => $pddikti_data['prodi_id'] ?? NULL,
                'tahun_lulus' => $pddikti_data['tahun_lulus'] ?? NULL,
                'tanggal_yudisium' => $pddikti_data['tanggal_yudisium'] ?? NULL,
                'status_kerja' => 'belum_bekerja',
                'email_verified' => 0,
                'status_aktif' => 1,
                'synchronized_from_pddikti' => 1,
                'last_sync_at' => date('Y-m-d H:i:s')
            ];
            
            return $this->insert($insert_data);
        }
        
        // Update existing - PDDikti has higher precedence (BR-ALM-009)
        $update_data = [
            'nama' => $pddikti_data['nama'] ?? $existing->nama,
            'email' => $pddikti_data['email'] ?? $existing->email,
            'prodi_id' => $pddikti_data['prodi_id'] ?? $existing->prodi_id,
            'tahun_lulus' => $pddikti_data['tahun_lulus'] ?? $existing->tahun_lulus,
            'tanggal_yudisium' => $pddikti_data['tanggal_yudisium'] ?? $existing->tanggal_yudisium,
            'synchronized_from_pddikti' => 1,
            'last_sync_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->update($existing->id, $update_data);
    }

    /**
     * Check if alumni can fill survey for cohort
     * 
     * @param int $alumni_id Alumni ID
     * @param int $kohort_id Kohort ID
     * @param bool $email_verified Email verification status
     * @return array ['can_fill' => bool, 'reason' => string]
     */
    public function canFillSurvey($alumni_id, $kohort_id, $email_verified = FALSE)
    {
        $alumni = $this->find($alumni_id);
        
        if (!$alumni) {
            return ['can_fill' => FALSE, 'reason' => 'Alumni not found'];
        }
        
        // BR-ALM-001: Hanya bisa isi survey kohort aktif
        $this->db->where('id', $kohort_id);
        $this->db->where('status', 'active');
        $kohort = $this->db->get('kohort')->row();
        
        if (!$kohort) {
            return ['can_fill' => FALSE, 'reason' => 'Kohort tidak aktif'];
        }
        
        // BR-ALM-007: Alumni tidak boleh isi survey kohort yang sama 2x
        $this->db->where('alumni_id', $alumni_id);
        $this->db->where('kohort_id', $kohort_id);
        $existing_survey = $this->db->get('survey_responses')->row();
        
        if ($existing_survey) {
            return ['can_fill' => FALSE, 'reason' => 'Anda sudah mengisi survey untuk kohort ini'];
        }
        
        // BR-ALM-005: Alumni belum verifikasi email boleh isi survey tapi tidak masuk IKU
        // Note: Still allowed to fill, just won't be counted in IKU
        
        return ['can_fill' => TRUE, 'reason' => 'OK'];
    }

    /**
     * Get alumni count for IKU calculation
     * Exclude non-active alumni (BR-ALM-010)
     * 
     * @param int $kohort_id Kohort ID
     * @return int Count of eligible alumni
     */
    public function getIKUEligibleCount($kohort_id)
    {
        $this->db->select('COUNT(*) as total');
        $this->db->where('kohort_id', $kohort_id);
        if ($this->soft_delete) {
            $this->db->where('deleted_at', NULL);
        }
        $this->db->where('status_aktif', 1); // BR-ALM-010
        
        $result = $this->db->get('alumni')->row();
        return $result->total ?? 0;
    }

    /**
     * Validate update data
     * 
     * @param int $id Alumni ID
     * @param array $data Data to validate
     * @return array ['valid' => bool, 'errors' => []]
     */
    public function validateUpdate($id, $data)
    {
        $errors = [];
        $alumni = $this->find($id);
        
        if (!$alumni) {
            return ['valid' => FALSE, 'errors' => ['Alumni not found']];
        }
        
        // BR-ALM-002: NIM immutable
        if (isset($data['nim']) && $data['nim'] !== $alumni->nim) {
            $errors[] = 'NIM tidak dapat diubah (immutable)';
        }
        
        // BR-ALM-003: Validate status_kerja related fields
        if (isset($data['status_kerja']) && $data['status_kerja'] === 'belum_bekerja') {
            if (!empty($data['perusahaan'])) {
                $errors[] = 'Perusahaan harus kosong untuk status belum_bekerja';
            }
            if (!empty($data['jabatan'])) {
                $errors[] = 'Jabatan harus kosong untuk status belum_bekerja';
            }
            if (!empty($data['gaji'])) {
                $errors[] = 'Gaji harus kosong untuk status belum_bekerja';
            }
        }
        
        // BR-ALM-004: Validate masa tunggu (negative check)
        if (!empty($data['tanggal_mulai_kerja']) && !empty($data['tanggal_yudisium'])) {
            $masa_tunggu = $this->calculateMasaTunggu($data['tanggal_mulai_kerja'], $data['tanggal_yudisium']);
            if ($masa_tunggu === FALSE) {
                $errors[] = 'Tanggal mulai kerja tidak boleh sebelum tanggal yudisium';
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}
