<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'core/MY_Controller.php';

/**
 * Alumni Controller
 * 
 * Mengelola CRUD alumni, import/export Excel, dan sync PDDikti
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
class Alumni extends Admin_Controller {

    public function __construct() {
        parent::__construct();
        
        // Load model
        $this->load->model('alumni/alumni_model');
        $this->load->model('auth/user_model');
        
        // Load helpers
        $this->load->helper(array('form', 'url', 'file'));
        
        // Load libraries
        $this->load->library(array('form_validation', 'session'));
    }

    /**
     * Index: List alumni dengan filter dan pagination
     * Filters: kohort, prodi, status_kerja, gaji, search
     */
    public function index() {
        $data['page_title'] = 'Manajemen Alumni';
        $data['page_subtitle'] = 'Kelola data alumni tracer study';
        
        // Load dropdown data for filters
        $this->db->select('id, nama');
        $this->db->where('status', 'active');
        $data['kohorts'] = $this->db->get('kohort')->result_array();
        
        $this->db->select('id, nama, kode');
        $this->db->order_by('nama', 'ASC');
        $data['prodis'] = $this->db->get('prodi')->result_array();
        
        $data['status_kerja_options'] = [
            '' => 'Semua Status',
            'bekerja' => 'Bekerja',
            'belum_bekerja' => 'Belum Bekerja',
            'wirausaha' => 'Wirausaha',
            'melanjutkan_studi' => 'Melanjutkan Studi'
        ];
        
        // Load view
        $this->load->view('admin/templates/header', $data);
        $this->load->view('alumni/index', $data);
        $this->load->view('admin/templates/footer');
    }

    /**
     * API Endpoint untuk DataTables Server-Side Processing
     * 
     * @return JSON
     */
    public function get_data() {
        // Get parameters
        $draw = $this->input->get('draw');
        $start = $this->input->get('start');
        $length = $this->input->get('length');
        $search = $this->input->get('search')['value'] ?? '';
        
        // Build filters
        $filters = [
            'kohort_id' => $this->input->get('kohort_id'),
            'prodi_id' => $this->input->get('prodi_id'),
            'status_kerja' => $this->input->get('status_kerja'),
            'gaji_min' => $this->input->get('gaji_min'),
            'gaji_max' => $this->input->get('gaji_max'),
            'search' => $search
        ];
        
        // Remove empty filters
        $filters = array_filter($filters, function($value) {
            return $value !== '' && $value !== NULL;
        });
        
        // Get data from model
        $page = ($start / $length) + 1;
        $result = $this->alumni_model->getAlumniWithFilter($filters, $page, $length);
        
        // Format data for DataTables
        $data = [];
        foreach ($result['data'] as $row) {
            $nestedData = [];
            
            // Checkbox
            $nestedData[] = '<input type="checkbox" class="alumni-checkbox" value="' . $row['id'] . '">';
            
            // NIM
            $nestedData[] = '<strong>' . htmlspecialchars($row['nim']) . '</strong>';
            
            // Nama
            $nestedData[] = htmlspecialchars($row['nama']) . 
                ($row['email_verified'] ? ' <span class="badge bg-success"><i class="fas fa-check-circle"></i></span>' : '');
            
            // Prodi
            $nestedData[] = htmlspecialchars($row['prodi_nama'] ?? '-');
            
            // Tahun Lulus
            $nestedData[] = $row['tahun_lulus'];
            
            // Status Kerja
            $status_badge = $this->_getStatusBadge($row['status_kerja']);
            $nestedData[] = $status_badge;
            
            // Gaji (formatted)
            if (!empty($row['gaji'])) {
                $nestedData[] = 'Rp ' . number_format($row['gaji'], 0, ',', '.');
            } else {
                $nestedData[] = '-';
            }
            
            // Masa Tunggu
            if ($row['masa_tunggu'] !== NULL) {
                $nestedData[] = $row['masa_tunggu'] . ' bulan';
            } else {
                $nestedData[] = '-';
            }
            
            // Actions
            $actions = '
                <div class="btn-group btn-group-sm" role="group">
                    <a href="' . site_url('alumni/detail/' . $row['id']) . '" class="btn btn-outline-info" title="Detail">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="' . site_url('alumni/edit/' . $row['id']) . '" class="btn btn-outline-primary" title="Edit">
                        <i class="fas fa-edit"></i>
                    </a>
                    <button type="button" class="btn btn-outline-danger" onclick="deleteAlumni(' . $row['id'] . ')" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            ';
            $nestedData[] = $actions;
            
            $data[] = $nestedData;
        }
        
        $output = [
            "draw" => intval($draw),
            "recordsTotal" => $result['total'],
            "recordsFiltered" => $result['total'],
            "data" => $data
        ];
        
        echo json_encode($output);
    }

    /**
     * Create: Form registrasi alumni baru
     */
    public function create() {
        $data['page_title'] = 'Registrasi Alumni Baru';
        $data['page_subtitle'] = 'Tambah alumni ke sistem';
        
        // Load dropdown data
        $this->db->select('id, nama');
        $this->db->where('status', 'active');
        $data['kohorts'] = $this->db->get('kohort')->result_array();
        
        $this->db->select('id, nama, kode');
        $this->db->order_by('nama', 'ASC');
        $data['prodis'] = $this->db->get('prodi')->result_array();
        
        $data['status_kerja_options'] = [
            'belum_bekerja' => 'Belum Bekerja',
            'bekerja' => 'Bekerja',
            'wirausaha' => 'Wirausaha',
            'melanjutkan_studi' => 'Melanjutkan Studi'
        ];
        
        // Load view
        $this->load->view('admin/templates/header', $data);
        $this->load->view('alumni/create', $data);
        $this->load->view('admin/templates/footer');
    }

    /**
     * Store: Simpan alumni baru dengan validasi
     */
    public function store() {
        // Validation rules
        $this->form_validation->set_rules('nim', 'NIM', 'required|is_unique[alumni.nim]', [
            'required' => 'NIM wajib diisi',
            'is_unique' => 'NIM sudah terdaftar di sistem'
        ]);
        
        $this->form_validation->set_rules('nama', 'Nama', 'required|trim|min_length[3]', [
            'required' => 'Nama wajib diisi',
            'min_length' => 'Nama minimal 3 karakter'
        ]);
        
        $this->form_validation->set_rules('email', 'Email', 'valid_email|is_unique[alumni.email]', [
            'valid_email' => 'Format email tidak valid',
            'is_unique' => 'Email sudah terdaftar'
        ]);
        
        $this->form_validation->set_rules('prodi_id', 'Program Studi', 'required|integer', [
            'required' => 'Program studi wajib dipilih'
        ]);
        
        $this->form_validation->set_rules('tahun_lulus', 'Tahun Lulus', 'required|integer|exact_length[4]', [
            'required' => 'Tahun lulus wajib diisi',
            'integer' => 'Tahun lulus harus angka',
            'exact_length' => 'Tahun lulus harus 4 digit'
        ]);
        
        if ($this->form_validation->run() === FALSE) {
            // Validation failed
            $this->session->set_flashdata('message', validation_errors());
            $this->session->set_flashdata('message_type', 'danger');
            redirect('alumni/create');
        }
        
        // Prepare data
        $data = [
            'nim' => $this->input->post('nim'),
            'nama' => $this->input->post('nama'),
            'email' => $this->input->post('email'),
            'no_hp' => $this->input->post('no_hp'),
            'prodi_id' => $this->input->post('prodi_id'),
            'kohort_id' => $this->input->post('kohort_id'),
            'tahun_lulus' => $this->input->post('tahun_lulus'),
            'tanggal_yudisium' => $this->input->post('tanggal_yudisium') ?: NULL,
            'status_kerja' => $this->input->post('status_kerja'),
            'email_verified' => 0,
            'status_aktif' => 1
        ];
        
        // BR-ALM-003: Validate status_kerja related fields
        if ($data['status_kerja'] === 'belum_bekerja') {
            // Clear employment-related fields
            $data['perusahaan'] = NULL;
            $data['jabatan'] = NULL;
            $data['gaji'] = NULL;
            $data['tanggal_mulai_kerja'] = NULL;
        } else {
            // Add employment fields if provided
            $data['perusahaan'] = $this->input->post('perusahaan');
            $data['jabatan'] = $this->input->post('jabatan');
            $data['gaji'] = !empty($this->input->post('gaji')) ? str_replace(',', '', $this->input->post('gaji')) : NULL;
            $data['tanggal_mulai_kerja'] = $this->input->post('tanggal_mulai_kerja');
            $data['lokasi_kerja'] = $this->input->post('lokasi_kerja');
        }
        
        // BR-ALM-004: Validate masa tunggu (negative check)
        if (!empty($data['tanggal_mulai_kerja']) && !empty($data['tanggal_yudisium'])) {
            $masa_tunggu = $this->alumni_model->calculateMasaTunggu(
                $data['tanggal_mulai_kerja'], 
                $data['tanggal_yudisium']
            );
            
            if ($masa_tunggu === FALSE) {
                $this->session->set_flashdata('message', 'Tanggal mulai kerja tidak boleh sebelum tanggal yudisium');
                $this->session->set_flashdata('message_type', 'danger');
                redirect('alumni/create');
            }
        }
        
        // Insert
        $alumni_id = $this->alumni_model->insert($data);
        
        if ($alumni_id) {
            // Log activity
            $this->load->helper('tracer_audit');
            audit_log('create', 'alumni', 'Create alumni: ' . $data['nim'] . ' - ' . $data['nama'], $this->user_data->id);
            
            $this->session->set_flashdata('message', 'Alumni berhasil ditambahkan');
            $this->session->set_flashdata('message_type', 'success');
            redirect('alumni/detail/' . $alumni_id);
        } else {
            $this->session->set_flashdata('message', 'Gagal menambahkan alumni');
            $this->session->set_flashdata('message_type', 'danger');
            redirect('alumni/create');
        }
    }

    /**
     * Edit: Form edit alumni
     * 
     * @param int $id Alumni ID
     */
    public function edit($id) {
        $alumni = $this->alumni_model->find($id);
        
        if (!$alumni) {
            show_error('Alumni tidak ditemukan', 404);
        }
        
        $data['page_title'] = 'Edit Alumni';
        $data['page_subtitle'] = 'Update data alumni';
        $data['alumni'] = $alumni;
        
        // Load dropdown data
        $this->db->select('id, nama');
        $this->db->where('status', 'active');
        $data['kohorts'] = $this->db->get('kohort')->result_array();
        
        $this->db->select('id, nama, kode');
        $this->db->order_by('nama', 'ASC');
        $data['prodis'] = $this->db->get('prodi')->result_array();
        
        $data['status_kerja_options'] = [
            'belum_bekerja' => 'Belum Bekerja',
            'bekerja' => 'Bekerja',
            'wirausaha' => 'Wirausaha',
            'melanjutkan_studi' => 'Melanjutkan Studi'
        ];
        
        // Load view
        $this->load->view('admin/templates/header', $data);
        $this->load->view('alumni/edit', $data);
        $this->load->view('admin/templates/footer');
    }

    /**
     * Update: Update alumni dengan trigger recalculation IKU
     * 
     * @param int $id Alumni ID
     */
    public function update($id) {
        $alumni = $this->alumni_model->find($id);
        
        if (!$alumni) {
            show_error('Alumni tidak ditemukan', 404);
        }
        
        // Validation rules
        $this->form_validation->set_rules('nama', 'Nama', 'required|trim|min_length[3]', [
            'required' => 'Nama wajib diisi',
            'min_length' => 'Nama minimal 3 karakter'
        ]);
        
        $this->form_validation->set_rules('email', 'Email', 'valid_email', [
            'valid_email' => 'Format email tidak valid'
        ]);
        
        $this->form_validation->set_rules('prodi_id', 'Program Studi', 'required|integer', [
            'required' => 'Program studi wajib dipilih'
        ]);
        
        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('message', validation_errors());
            $this->session->set_flashdata('message_type', 'danger');
            redirect('alumni/edit/' . $id);
        }
        
        // Prepare update data
        $data = [
            'nama' => $this->input->post('nama'),
            'email' => $this->input->post('email'),
            'no_hp' => $this->input->post('no_hp'),
            'prodi_id' => $this->input->post('prodi_id'),
            'kohort_id' => $this->input->post('kohort_id'),
            'status_kerja' => $this->input->post('status_kerja')
        ];
        
        // BR-ALM-002: NIM is immutable - don't allow update
        
        // BR-ALM-003: Validate status_kerja related fields
        if ($data['status_kerja'] === 'belum_bekerja') {
            $data['perusahaan'] = NULL;
            $data['jabatan'] = NULL;
            $data['gaji'] = NULL;
            $data['tanggal_mulai_kerja'] = NULL;
            $data['lokasi_kerja'] = NULL;
            $data['jenis_pekerjaan'] = NULL;
            $data['kesesuaian_bidang'] = NULL;
        } else {
            $data['perusahaan'] = $this->input->post('perusahaan');
            $data['jabatan'] = $this->input->post('jabatan');
            $data['gaji'] = !empty($this->input->post('gaji')) ? str_replace(',', '', $this->input->post('gaji')) : NULL;
            $data['tanggal_mulai_kerja'] = $this->input->post('tanggal_mulai_kerja');
            $data['lokasi_kerja'] = $this->input->post('lokasi_kerja');
            $data['jenis_pekerjaan'] = $this->input->post('jenis_pekerjaan');
            $data['kesesuaian_bidang'] = $this->input->post('kesesuaian_bidang');
        }
        
        // Add optional fields
        if ($this->input->post('tanggal_yudisium')) {
            $data['tanggal_yudisium'] = $this->input->post('tanggal_yudisium');
        }
        
        // BR-ALM-004: Validate masa tunggu
        if (!empty($data['tanggal_mulai_kerja']) && !empty($data['tanggal_yudisium'])) {
            $masa_tunggu = $this->alumni_model->calculateMasaTunggu(
                $data['tanggal_mulai_kerja'], 
                $data['tanggal_yudisium']
            );
            
            if ($masa_tunggu === FALSE) {
                $this->session->set_flashdata('message', 'Tanggal mulai kerja tidak boleh sebelum tanggal yudisium');
                $this->session->set_flashdata('message_type', 'danger');
                redirect('alumni/edit/' . $id);
            }
        }
        
        // Validate using model
        $validation = $this->alumni_model->validateUpdate($id, $data);
        if (!$validation['valid']) {
            $this->session->set_flashdata('message', implode('<br>', $validation['errors']));
            $this->session->set_flashdata('message_type', 'danger');
            redirect('alumni/edit/' . $id);
        }
        
        // Update
        $success = $this->alumni_model->update($id, $data);
        
        if ($success) {
            // BR-ALM-008: Trigger IKU recalculation
            $this->alumni_model->triggerIKURecalculation($id);
            
            // Log activity
            $this->load->helper('tracer_audit');
            audit_log('update', 'alumni', 'Update alumni: ' . $alumni->nim, $this->user_data->id);
            
            $this->session->set_flashdata('message', 'Data alumni berhasil diupdate');
            $this->session->set_flashdata('message_type', 'success');
            redirect('alumni/detail/' . $id);
        } else {
            $this->session->set_flashdata('message', 'Gagal mengupdate data alumni');
            $this->session->set_flashdata('message_type', 'danger');
            redirect('alumni/edit/' . $id);
        }
    }

    /**
     * Delete: Soft delete alumni
     * 
     * @param int $id Alumni ID
     */
    public function delete($id) {
        $alumni = $this->alumni_model->find($id);
        
        if (!$alumni) {
            echo json_encode(['success' => FALSE, 'message' => 'Alumni tidak ditemukan']);
            return;
        }
        
        // Soft delete
        $success = $this->alumni_model->delete($id);
        
        if ($success) {
            // Log activity
            $this->load->helper('tracer_audit');
            audit_log('delete', 'alumni', 'Soft delete alumni: ' . $alumni->nim . ' - ' . $alumni->nama, $this->user_data->id);
            
            echo json_encode(['success' => TRUE, 'message' => 'Alumni berhasil dihapus']);
        } else {
            echo json_encode(['success' => FALSE, 'message' => 'Gagal menghapus alumni']);
        }
    }

    /**
     * Import: Upload Excel/CSV dengan PHPSpreadsheet
     */
    public function import() {
        $data['page_title'] = 'Import Alumni';
        $data['page_subtitle'] = 'Upload data alumni dari Excel/CSV';
        
        // Load view
        $this->load->view('admin/templates/header', $data);
        $this->load->view('alumni/import', $data);
        $this->load->view('admin/templates/footer');
    }

    /**
     * Process import file
     */
    public function process_import() {
        // Check if file uploaded
        if (empty($_FILES['import_file']['name'])) {
            $this->session->set_flashdata('message', 'File belum dipilih');
            $this->session->set_flashdata('message_type', 'danger');
            redirect('alumni/import');
        }
        
        // Check for PHPSpreadsheet
        if (!class_exists('PhpOffice\PhpSpreadsheet\IOFactory')) {
            $this->session->set_flashdata('message', 'PHPSpreadsheet library not installed. Run: composer require phpoffice/phpspreadsheet');
            $this->session->set_flashdata('message_type', 'danger');
            redirect('alumni/import');
        }
        
        $config['upload_path'] = FCPATH . 'public/uploads/temp/';
        $config['allowed_types'] = 'xlsx|xls|csv';
        $config['max_size'] = 10240; // 10MB
        $config['encrypt_name'] = TRUE;
        
        // Create directory if not exists
        if (!file_exists($config['upload_path'])) {
            mkdir($config['upload_path'], 0755, TRUE);
        }
        
        $this->load->library('upload', $config);
        
        if (!$this->upload->do_upload('import_file')) {
            $this->session->set_flashdata('message', $this->upload->display_errors());
            $this->session->set_flashdata('message_type', 'danger');
            redirect('alumni/import');
        }
        
        $upload_data = $this->upload->data();
        $file_path = $config['upload_path'] . $upload_data['file_name'];
        
        try {
            // Load spreadsheet
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file_path);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            // Remove header row
            array_shift($rows);
            
            // Parse and validate data
            $data_array = [];
            foreach ($rows as $index => $row) {
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }
                
                // Map columns (BR-ALM-006: Required columns)
                $data_array[] = [
                    'nim' => trim($row[0] ?? ''),
                    'nama' => trim($row[1] ?? ''),
                    'email' => trim($row[2] ?? ''),
                    'no_hp' => trim($row[3] ?? ''),
                    'prodi_id' => $this->_getProdiIdByName(trim($row[4] ?? '')),
                    'kohort_id' => $this->_getKohortIdByYear(trim($row[5] ?? '')),
                    'tahun_lulus' => trim($row[6] ?? ''),
                    'tanggal_yudisium' => !empty($row[7]) ? $this->_parseExcelDate($row[7]) : NULL
                ];
            }
            
            // Import batch
            $result = $this->alumni_model->importBatch($data_array);
            
            // Delete temp file
            unlink($file_path);
            
            if ($result['success']) {
                // Log activity
                $this->load->helper('tracer_audit');
                audit_log('import', 'alumni', 'Import ' . $result['inserted'] . ' alumni from Excel', $this->user_data->id);
                
                $message = sprintf(
                    'Import berhasil: %d alumni ditambahkan, %d dilewati (duplicate)',
                    $result['inserted'],
                    $result['skipped']
                );
                
                if (!empty($result['errors'])) {
                    $message .= '<br><small>' . count($result['errors']) . ' error detail tersedia di log</small>';
                }
                
                $this->session->set_flashdata('message', $message);
                $this->session->set_flashdata('message_type', 'success');
            } else {
                $this->session->set_flashdata('message', 'Import gagal: ' . ($result['errors'][0]['message'] ?? 'Unknown error'));
                $this->session->set_flashdata('message_type', 'danger');
            }
            
        } catch (Exception $e) {
            // Delete temp file
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            
            $this->session->set_flashdata('message', 'Error processing file: ' . $e->getMessage());
            $this->session->set_flashdata('message_type', 'danger');
        }
        
        redirect('alumni/import');
    }

    /**
     * Export: Export alumni ke Excel
     */
    public function export() {
        // Get filters
        $filters = [
            'kohort_id' => $this->input->get('kohort_id'),
            'prodi_id' => $this->input->get('prodi_id'),
            'status_kerja' => $this->input->get('status_kerja'),
            'search' => $this->input->get('search')
        ];
        
        // Get all data (no pagination)
        $result = $this->alumni_model->getAlumniWithFilter($filters, 1, 10000);
        
        // Set headers
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="alumni_export_' . date('Y-m-d_His') . '.xls"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Output Excel XML format
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<?mso-application progid="Excel.Sheet"?>';
        echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheetml"';
        echo ' xmlns:o="urn:schemas-microsoft-com:office:office"';
        echo ' xmlns:x="urn:schemas-microsoft-com:office:excel"';
        echo ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheetml"';
        echo ' xmlns:html="http://www.w3.org/TR/REC-html40">';
        echo '<Worksheet ss:Name="Alumni">';
        echo '<Table>';
        
        // Header row
        echo '<Row>';
        echo '<Cell><Data ss:Type="String">NIM</Data></Cell>';
        echo '<Cell><Data ss:Type="String">Nama</Data></Cell>';
        echo '<Cell><Data ss:Type="String">Email</Data></Cell>';
        echo '<Cell><Data ss:Type="String">No HP</Data></Cell>';
        echo '<Cell><Data ss:Type="String">Prodi</Data></Cell>';
        echo '<Cell><Data ss:Type="String">Tahun Lulus</Data></Cell>';
        echo '<Cell><Data ss:Type="String">Status Kerja</Data></Cell>';
        echo '<Cell><Data ss:Type="String">Perusahaan</Data></Cell>';
        echo '<Cell><Data ss:Type="String">Jabatan</Data></Cell>';
        echo '<Cell><Data ss:Type="String">Gaji</Data></Cell>';
        echo '<Cell><Data ss:Type="String">Masa Tunggu (Bulan)</Data></Cell>';
        echo '<Cell><Data ss:Type="String">Email Verified</Data></Cell>';
        echo '</Row>';
        
        // Data rows
        foreach ($result['data'] as $row) {
            echo '<Row>';
            echo '<Cell><Data ss:Type="String">' . htmlspecialchars($row['nim']) . '</Data></Cell>';
            echo '<Cell><Data ss:Type="String">' . htmlspecialchars($row['nama']) . '</Data></Cell>';
            echo '<Cell><Data ss:Type="String">' . htmlspecialchars($row['email'] ?? '-') . '</Data></Cell>';
            echo '<Cell><Data ss:Type="String">' . htmlspecialchars($row['no_hp'] ?? '-') . '</Data></Cell>';
            echo '<Cell><Data ss:Type="String">' . htmlspecialchars($row['prodi_nama'] ?? '-') . '</Data></Cell>';
            echo '<Cell><Data ss:Type="Number">' . htmlspecialchars($row['tahun_lulus']) . '</Data></Cell>';
            echo '<Cell><Data ss:Type="String">' . htmlspecialchars($row['status_kerja']) . '</Data></Cell>';
            echo '<Cell><Data ss:Type="String">' . htmlspecialchars($row['perusahaan'] ?? '-') . '</Data></Cell>';
            echo '<Cell><Data ss:Type="String">' . htmlspecialchars($row['jabatan'] ?? '-') . '</Data></Cell>';
            echo '<Cell><Data ss:Type="Number">' . htmlspecialchars($row['gaji'] ?? '') . '</Data></Cell>';
            echo '<Cell><Data ss:Type="Number">' . htmlspecialchars($row['masa_tunggu'] ?? '') . '</Data></Cell>';
            echo '<Cell><Data ss:Type="String">' . ($row['email_verified'] ? 'Yes' : 'No') . '</Data></Cell>';
            echo '</Row>';
        }
        
        echo '</Table></Worksheet></Workbook>';
        
        // Log activity
        $this->load->helper('tracer_audit');
        audit_log('export', 'alumni', 'Export ' . count($result['data']) . ' alumni to Excel', $this->user_data->id);
    }

    /**
     * SyncPddikti: Trigger sync dari PDDikti NeoFeeder
     */
    public function syncPddikti() {
        // This would integrate with PDDikti NeoFeeder API
        // For now, simulate the process
        
        $mode = $this->input->post('mode') ?? 'all'; // 'all' or 'by_nim'
        $nim = $this->input->post('nim');
        
        try {
            // Simulate PDDikti API call
            // In production, this would call actual PDDikti NeoFeeder REST API
            
            if ($mode === 'by_nim' && !empty($nim)) {
                // Sync single alumni by NIM
                $pddikti_data = $this->_fetchFromPDDikti($nim);
                
                if ($pddikti_data) {
                    $success = $this->alumni_model->syncFromPDDikti($nim, $pddikti_data);
                    
                    if ($success) {
                        // Log activity
                        $this->load->helper('tracer_audit');
                        audit_log('sync', 'alumni', 'Sync alumni ' . $nim . ' from PDDikti', $this->user_data->id);
                        
                        echo json_encode(['success' => TRUE, 'message' => 'Sync berhasil untuk NIM: ' . $nim]);
                    } else {
                        echo json_encode(['success' => FALSE, 'message' => 'Gagal sync alumni']);
                    }
                } else {
                    echo json_encode(['success' => FALSE, 'message' => 'Data tidak ditemukan di PDDikti']);
                }
            } else {
                // Sync all alumni (batch process)
                // This should be done via queue/cron job in production
                
                $this->db->select('nim');
                $this->db->where('deleted_at', NULL);
                $alumni_list = $this->db->get('alumni')->result_array();
                
                $synced = 0;
                $failed = 0;
                
                foreach ($alumni_list as $alumni) {
                    $pddikti_data = $this->_fetchFromPDDikti($alumni['nim']);
                    
                    if ($pddikti_data) {
                        if ($this->alumni_model->syncFromPDDikti($alumni['nim'], $pddikti_data)) {
                            $synced++;
                        } else {
                            $failed++;
                        }
                    } else {
                        $failed++;
                    }
                }
                
                // Log activity
                $this->load->helper('tracer_audit');
                audit_log('sync', 'alumni', 'Batch sync ' . count($alumni_list) . ' alumni from PDDikti', $this->user_data->id);
                
                echo json_encode([
                    'success' => TRUE, 
                    'message' => sprintf('Sync selesai: %d berhasil, %d gagal', $synced, $failed)
                ]);
            }
            
        } catch (Exception $e) {
            echo json_encode(['success' => FALSE, 'message' => 'Sync error: ' . $e->getMessage()]);
        }
    }

    /**
     * Detail: View detail alumni lengkap
     * 
     * @param int $id Alumni ID
     */
    public function detail($id) {
        $alumni = $this->alumni_model->find($id);
        
        if (!$alumni) {
            show_error('Alumni tidak ditemukan', 404);
        }
        
        $data['page_title'] = 'Detail Alumni';
        $data['page_subtitle'] = 'Informasi lengkap alumni';
        $data['alumni'] = $alumni;
        
        // Get additional info
        $this->db->select('nama');
        $prodi = $this->db->get_where('prodi', ['id' => $alumni->prodi_id])->row();
        $data['prodi_nama'] = $prodi->nama ?? '-';
        
        $this->db->select('nama');
        $kohort = $this->db->get_where('kohort', ['id' => $alumni->kohort_id])->row();
        $data['kohort_nama'] = $kohort->nama ?? '-';
        
        // Calculate masa tunggu
        if (!empty($alumni->tanggal_yudisium) && !empty($alumni->tanggal_mulai_kerja)) {
            $data['masa_tunggu'] = $this->alumni_model->calculateMasaTunggu(
                $alumni->tanggal_mulai_kerja,
                $alumni->tanggal_yudisium
            );
        } else {
            $data['masa_tunggu'] = NULL;
        }
        
        // Load view
        $this->load->view('admin/templates/header', $data);
        $this->load->view('alumni/detail', $data);
        $this->load->view('admin/templates/footer');
    }

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    /**
     * Get status badge HTML
     */
    private function _getStatusBadge($status) {
        $badges = [
            'bekerja' => '<span class="badge bg-success">Bekerja</span>',
            'belum_bekerja' => '<span class="badge bg-secondary">Belum Bekerja</span>',
            'wirausaha' => '<span class="badge bg-info">Wirausaha</span>',
            'melanjutkan_studi' => '<span class="badge bg-warning">Melanjutkan Studi</span>'
        ];
        
        return $badges[$status] ?? '<span class="badge bg-light text-dark">' . $status . '</span>';
    }

    /**
     * Get Prodi ID by name
     */
    private function _getProdiIdByName($name) {
        if (empty($name)) {
            return NULL;
        }
        
        $this->db->select('id');
        $this->db->like('nama', $name, 'both');
        $prodi = $this->db->get('prodi')->row();
        
        return $prodi ? $prodi->id : NULL;
    }

    /**
     * Get Kohort ID by year
     */
    private function _getKohortIdByYear($year) {
        if (empty($year)) {
            return NULL;
        }
        
        $this->db->select('id');
        $this->db->where('tahun_angkatan', $year);
        $kohort = $this->db->get('kohort')->row();
        
        return $kohort ? $kohort->id : NULL;
    }

    /**
     * Parse Excel date
     */
    private function _parseExcelDate($value) {
        // Handle Excel serial date
        if (is_numeric($value)) {
            $unix_date = ($value - 25569) * 86400;
            return date('Y-m-d', $unix_date);
        }
        
        // Handle string date
        if (is_string($value)) {
            $timestamp = strtotime($value);
            if ($timestamp) {
                return date('Y-m-d', $timestamp);
            }
        }
        
        return NULL;
    }

    /**
     * Fetch data from PDDikti NeoFeeder API
     * This is a placeholder - implement actual API integration
     */
    private function _fetchFromPDDikti($nim) {
        // In production, this would call:
        // https://feed.feeder.university/v2/mahasiswa/{nim}
        // or similar PDDikti NeoFeeder endpoint
        
        // Simulated response
        return [
            'nama' => 'Simulated Name',
            'email' => 'simulated@email.com',
            'prodi_id' => 1,
            'tahun_lulus' => date('Y'),
            'tanggal_yudisium' => date('Y-m-d')
        ];
    }
}
