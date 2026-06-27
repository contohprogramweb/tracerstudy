<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller: Stakeholder_survey
 * CRUD operations untuk survey stakeholder
 */
class Stakeholder_survey extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Stakeholder_model');
        $this->load->model('Stakeholder_survey_model');
        $this->load->library('form_validation');
        
        // Check authentication
        if (!$this->session->userdata('logged_in')) {
            redirect('auth/login');
        }
    }

    /**
     * Create form survey stakeholder untuk alumni tertentu
     * 
     * @param int $alumni_id
     * @return void
     */
    public function create($alumni_id)
    {
        $user_id = $this->session->userdata('user_id');
        $stakeholder = $this->Stakeholder_model->getByUserId($user_id);

        if (!$stakeholder) {
            show_error('Anda harus registrasi sebagai stakeholder terlebih dahulu', 403);
        }

        // Get alumni data
        $this->load->model('Alumni_model');
        $data['alumni'] = $this->Alumni_model->getById($alumni_id);

        if (!$data['alumni']) {
            show_404('Alumni tidak ditemukan');
        }

        // BR-SUR-006: Must be linked to alumni or prodi
        $data['alumni_id'] = $alumni_id;
        $data['prodi_id'] = $data['alumni']['prodi_id'];

        // Get CPL for this prodi
        $this->load->model('CPL_model');
        $data['cpl_list'] = $this->CPL_model->getByProdi($data['prodi_id']);

        $data['page_title'] = 'Buat Penilaian Stakeholder';
        $data['rating_options'] = [
            1 => 'Sangat Kurang (1)',
            2 => 'Kurang (2)',
            3 => 'Cukup (3)',
            4 => 'Baik (4)',
            5 => 'Sangat Baik (5)'
        ];

        $data['kesesuaian_options'] = [
            'sangat_sesuai' => 'Sangat Sesuai dengan Kebutuhan Industri',
            'sesuai' => 'Sesuai dengan Kebutuhan Industri',
            'kurang' => 'Kurang Sesuai dengan Kebutuhan Industri',
            'tidak_sesuai' => 'Tidak Sesuai dengan Kebutuhan Industri'
        ];

        $data['work_periods'] = [
            '< 6 bulan' => '< 6 bulan',
            '6 bulan - 1 tahun' => '6 bulan - 1 tahun',
            '1 - 2 tahun' => '1 - 2 tahun',
            '2 - 5 tahun' => '2 - 5 tahun',
            '> 5 tahun' => '> 5 tahun'
        ];

        $this->load->view('stakeholder/survey', $data);
    }

    /**
     * Store/Create new survey stakeholder
     * 
     * @param int $alumni_id
     * @return void
     */
    public function store($alumni_id)
    {
        $user_id = $this->session->userdata('user_id');
        $stakeholder = $this->Stakeholder_model->getByUserId($user_id);

        if (!$stakeholder) {
            $this->output
                ->set_status_header(403)
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Stakeholder tidak terdaftar']));
            return;
        }

        // Validation
        $this->form_validation->set_rules([
            'work_period' => 'required',
            'work_position' => 'required|min_length[3]',
            'company_feedback' => 'required|min_length[10]'
        ]);

        if ($this->form_validation->run() == FALSE) {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false, 
                    'message' => 'Validasi gagal',
                    'errors' => validation_errors()
                ]));
            return;
        }

        // Get CPL ratings from POST
        $cpl_ratings = $this->input->post('cpl_ratings');
        $cpl_kesesuaian = $this->input->post('cpl_kesesuaian');
        
        if (empty($cpl_ratings)) {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false, 
                    'message' => 'Penilaian CPL wajib diisi'
                ]));
            return;
        }

        // Validate each CPL rating
        foreach ($cpl_ratings as $cpl_id => $rating) {
            if (!is_numeric($rating) || $rating < 1 || $rating > 5) {
                $this->output
                    ->set_status_header(400)
                    ->set_content_type('application/json')
                    ->set_output(json_encode([
                        'success' => false, 
                        'message' => 'Rating CPL harus antara 1-5'
                    ]));
                return;
            }
        }

        // Prepare survey data - BR-SUR-006: linked to alumni and prodi
        $survey_data = [
            'alumni_id' => $alumni_id,
            'stakeholder_id' => $stakeholder['id'],
            'prodi_id' => $this->input->post('prodi_id'),
            'work_period' => $this->input->post('work_period'),
            'work_position' => $this->input->post('work_position'),
            'company_feedback' => $this->input->post('company_feedback'),
            'recommended_competencies' => $this->input->post('recommended_competencies'),
            'curriculum_suggestions' => $this->input->post('curriculum_suggestions'),
            'status' => 'completed',
            'submitted_at' => date('Y-m-d H:i:s'),
            'submitted_by' => $user_id
        ];

        // Save survey header
        $survey_id = $this->Stakeholder_survey_model->create($survey_data);

        if ($survey_id) {
            // Save CPL ratings
            $ratings_data = [];
            foreach ($cpl_ratings as $cpl_id => $rating) {
                $ratings_data[] = [
                    'survey_id' => $survey_id,
                    'cpl_id' => $cpl_id,
                    'rating' => $rating,
                    'kesesuaian' => isset($cpl_kesesuaian[$cpl_id]) ? $cpl_kesesuaian[$cpl_id] : null,
                    'created_at' => date('Y-m-d H:i:s')
                ];
            }

            $this->Stakeholder_survey_model->saveCplRatings($ratings_data);

            // Calculate average rating
            $average_rating = array_sum($cpl_ratings) / count($cpl_ratings);
            $this->Stakeholder_survey_model->update($survey_id, ['average_rating' => $average_rating]);

            // Send notifications
            $this->_sendNotifications($survey_id);

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => true, 
                    'message' => 'Penilaian berhasil disimpan',
                    'survey_id' => $survey_id,
                    'redirect' => site_url('stakeholder/dashboard')
                ]));
        } else {
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false, 
                    'message' => 'Gagal menyimpan penilaian'
                ]));
        }
    }

    /**
     * List survey stakeholder per alumni
     * 
     * @param int $alumni_id
     * @return void
     */
    public function listByAlumni($alumni_id)
    {
        $data['page_title'] = 'Riwayat Penilaian Alumni';
        $data['alumni_id'] = $alumni_id;

        // Get alumni info
        $this->load->model('Alumni_model');
        $data['alumni'] = $this->Alumni_model->getById($alumni_id);

        if (!$data['alumni']) {
            show_404('Alumni tidak ditemukan');
        }

        // Get all stakeholder surveys for this alumni
        $data['surveys'] = $this->Stakeholder_survey_model->getByAlumni($alumni_id);

        // Calculate summary statistics
        $data['summary'] = $this->Stakeholder_survey_model->getSummaryByAlumni($alumni_id);

        $this->load->view('stakeholder/list_by_alumni', $data);
    }

    /**
     * List survey stakeholder per program studi
     * 
     * @param int $prodi_id
     * @return void
     */
    public function listByProdi($prodi_id)
    {
        $data['page_title'] = 'Survey Stakeholder per Program Studi';
        $data['prodi_id'] = $prodi_id;

        // Get prodi info
        $this->load->model('Prodi_model');
        $data['prodi'] = $this->Prodi_model->getById($prodi_id);

        if (!$data['prodi']) {
            show_404('Program Studi tidak ditemukan');
        }

        // Filter options
        $data['filter_year'] = $this->input->get('year') ?: date('Y');
        $data['filter_status'] = $this->input->get('status') ?: 'all';

        // Get surveys with filters
        $data['surveys'] = $this->Stakeholder_survey_model->getByProdi(
            $prodi_id, 
            $data['filter_year'],
            $data['filter_status'] !== 'all' ? $data['filter_status'] : null
        );

        // Get summary statistics
        $data['summary'] = $this->Stakeholder_survey_model->getSummaryByProdi($prodi_id, $data['filter_year']);

        // Get CPL averages for gap analysis
        $this->load->model('CPL_model');
        $data['cpl_list'] = $this->CPL_model->getByProdi($prodi_id);
        $data['cpl_averages'] = $this->Stakeholder_survey_model->getAverageRatingByCpl($prodi_id);

        $this->load->view('stakeholder/list_by_prodi', $data);
    }

    /**
     * Edit existing survey
     * 
     * @param int $survey_id
     * @return void
     */
    public function edit($survey_id)
    {
        $user_id = $this->session->userdata('user_id');
        $stakeholder = $this->Stakeholder_model->getByUserId($user_id);

        if (!$stakeholder) {
            show_error('Akses ditolak', 403);
        }

        $data['survey'] = $this->Stakeholder_survey_model->getById($survey_id);

        if (!$data['survey']) {
            show_404('Survey tidak ditemukan');
        }

        // Check ownership
        if ($data['survey']['stakeholder_id'] != $stakeholder['id']) {
            show_error('Anda tidak berhak mengedit survey ini', 403);
        }

        // Can only edit pending or returned surveys
        if ($data['survey']['status'] === 'completed') {
            show_error('Survey sudah submitted dan tidak dapat diedit', 403);
        }

        // Get alumni data
        $this->load->model('Alumni_model');
        $data['alumni'] = $this->Alumni_model->getById($data['survey']['alumni_id']);

        // Get CPL ratings
        $data['cpl_ratings'] = $this->Stakeholder_survey_model->getCplRatings($survey_id);

        // Get CPL list
        $this->load->model('CPL_model');
        $data['cpl_list'] = $this->CPL_model->getByProdi($data['survey']['prodi_id']);

        $data['page_title'] = 'Edit Penilaian Stakeholder';
        $data['rating_options'] = [
            1 => 'Sangat Kurang (1)',
            2 => 'Kurang (2)',
            3 => 'Cukup (3)',
            4 => 'Baik (4)',
            5 => 'Sangat Baik (5)'
        ];

        $data['kesesuaian_options'] = [
            'sangat_sesuai' => 'Sangat Sesuai',
            'sesuai' => 'Sesuai',
            'kurang' => 'Kurang Sesuai',
            'tidak_sesuai' => 'Tidak Sesuai'
        ];

        $this->load->view('stakeholder/survey_edit', $data);
    }

    /**
     * Update existing survey
     * 
     * @param int $survey_id
     * @return void
     */
    public function update($survey_id)
    {
        $user_id = $this->session->userdata('user_id');
        $stakeholder = $this->Stakeholder_model->getByUserId($user_id);

        if (!$stakeholder) {
            $this->output
                ->set_status_header(403)
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Unauthorized']));
            return;
        }

        $survey = $this->Stakeholder_survey_model->getById($survey_id);

        if (!$survey || $survey['stakeholder_id'] != $stakeholder['id']) {
            $this->output
                ->set_status_header(403)
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Survey tidak ditemukan']));
            return;
        }

        // Get CPL ratings
        $cpl_ratings = $this->input->post('cpl_ratings');
        $cpl_kesesuaian = $this->input->post('cpl_kesesuaian');

        // Update survey header
        $update_data = [
            'work_period' => $this->input->post('work_period'),
            'work_position' => $this->input->post('work_position'),
            'company_feedback' => $this->input->post('company_feedback'),
            'recommended_competencies' => $this->input->post('recommended_competencies'),
            'curriculum_suggestions' => $this->input->post('curriculum_suggestions'),
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $user_id
        ];

        if ($this->input->post('submit_final')) {
            $update_data['status'] = 'completed';
            $update_data['submitted_at'] = date('Y-m-d H:i:s');
        } else {
            $update_data['status'] = 'draft';
        }

        $this->Stakeholder_survey_model->update($survey_id, $update_data);

        // Update CPL ratings
        if (!empty($cpl_ratings)) {
            $this->Stakeholder_survey_model->deleteCplRatings($survey_id);
            
            $ratings_data = [];
            foreach ($cpl_ratings as $cpl_id => $rating) {
                $ratings_data[] = [
                    'survey_id' => $survey_id,
                    'cpl_id' => $cpl_id,
                    'rating' => $rating,
                    'kesesuaian' => isset($cpl_kesesuaian[$cpl_id]) ? $cpl_kesesuaian[$cpl_id] : null
                ];
            }
            $this->Stakeholder_survey_model->saveCplRatings($ratings_data);

            // Recalculate average
            $average_rating = array_sum($cpl_ratings) / count($cpl_ratings);
            $this->Stakeholder_survey_model->update($survey_id, ['average_rating' => $average_rating]);
        }

        if ($update_data['status'] === 'completed') {
            $this->_sendNotifications($survey_id);
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'message' => $update_data['status'] === 'completed' ? 'Penilaian berhasil disubmit' : 'Draft berhasil disimpan'
            ]));
    }

    /**
     * Delete survey
     * 
     * @param int $survey_id
     * @return void
     */
    public function delete($survey_id)
    {
        $user_id = $this->session->userdata('user_id');
        $stakeholder = $this->Stakeholder_model->getByUserId($user_id);

        if (!$stakeholder) {
            $this->output
                ->set_status_header(403)
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Unauthorized']));
            return;
        }

        $survey = $this->Stakeholder_survey_model->getById($survey_id);

        if (!$survey || $survey['stakeholder_id'] != $stakeholder['id']) {
            $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Survey tidak ditemukan']));
            return;
        }

        // Can only delete draft surveys
        if ($survey['status'] === 'completed') {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Survey yang sudah submitted tidak dapat dihapus']));
            return;
        }

        $this->Stakeholder_survey_model->delete($survey_id);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'message' => 'Survey berhasil dihapus'
            ]));
    }

    /**
     * Export survey results to Excel
     * 
     * @param int $prodi_id
     * @return void
     */
    public function export($prodi_id)
    {
        $year = $this->input->get('year') ?: date('Y');
        
        $surveys = $this->Stakeholder_survey_model->getByProdi($prodi_id, $year);
        
        // Load Excel library
        $this->load->library('excel');
        
        $this->excel->setActiveSheetIndex(0);
        $sheet = $this->excel->getActiveSheet();
        
        // Headers
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Nama Alumni');
        $sheet->setCellValue('C1', 'NIM');
        $sheet->setCellValue('D1', 'Stakeholder');
        $sheet->setCellValue('E1', 'Perusahaan');
        $sheet->setCellValue('F1', 'Posisi');
        $sheet->setCellValue('G1', 'Periode Kerja');
        $sheet->setCellValue('H1', 'Rating Rata-rata');
        $sheet->setCellValue('I1', 'Tanggal Submit');
        
        $row = 2;
        foreach ($surveys as $survey) {
            $sheet->setCellValue('A' . $row, $row - 1);
            $sheet->setCellValue('B' . $row, $survey['alumni_name']);
            $sheet->setCellValue('C' . $row, $survey['alumni_nim']);
            $sheet->setCellValue('D' . $row, $survey['stakeholder_name']);
            $sheet->setCellValue('E' . $row, $survey['company_name']);
            $sheet->setCellValue('F' . $row, $survey['work_position']);
            $sheet->setCellValue('G' . $row, $survey['work_period']);
            $sheet->setCellValue('H' . $row, number_format($survey['average_rating'], 2));
            $sheet->setCellValue('I' . $row, date('d/m/Y', strtotime($survey['submitted_at'])));
            $row++;
        }
        
        // Auto-size columns
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Download
        $filename = 'Survey_Stakeholder_Prodi_' . $prodi_id . '_' . $year . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($this->excel, 'Xlsx');
        $objWriter->save('php://output');
        exit;
    }

    /**
     * Send notifications after survey submission
     * 
     * @param int $survey_id
     * @return void
     */
    private function _sendNotifications($survey_id)
    {
        $this->load->library('email');
        $survey = $this->Stakeholder_survey_model->getById($survey_id);
        
        // Notify alumni
        $this->load->model('Alumni_model');
        $alumni = $this->Alumni_model->getById($survey['alumni_id']);
        
        if ($alumni && $alumni['email']) {
            $this->email->from('noreply@university.edu', 'University Alumni System');
            $this->email->to($alumni['email']);
            $this->email->subject('Penilaian Kompetensi dari Stakeholder');
            
            $message = "Halo {$alumni['name']},\n\n";
            $message .= "Seorang stakeholder telah memberikan penilaian terhadap kompetensi Anda.\n\n";
            $message .= "Silakan login ke sistem untuk melihat detail penilaian.";

            $this->email->message($message);
            $this->email->send();
        }

        // Notify prodi
        $this->load->model('Prodi_model');
        $prodi = $this->Prodi_model->getById($survey['prodi_id']);
        
        if ($prodi && $prodi['email']) {
            $this->email->to($prodi['email']);
            $this->email->subject('Penilaian Kompetensi Alumni oleh Stakeholder');
            
            $message = "Halo Kaprodi {$prodi['name']},\n\n";
            $message .= "Telah ada penilaian kompetensi alumni oleh stakeholder.\n\n";
            $message .= "Silakan cek dashboard untuk melihat detail dan gap analysis.";

            $this->email->message($message);
            $this->email->send();
        }
    }
}
