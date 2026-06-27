<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'third_party/phpspreadsheet/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * Library untuk generate dan validasi template Excel Belmawa
 * Format sesuai tracerstudy.kemdikbud.go.id
 */
class BelmawaTemplate {
    protected $CI;
    private $spreadsheet;
    private $sheet;
    
    // Mapping Kode Jawaban Inti (Kolom H-X)
    private $question_codes = [
        'Q1' => 'H', 'Q2' => 'I', 'Q3' => 'J', 'Q4' => 'K', 'Q5' => 'L',
        'Q6' => 'M', 'Q7' => 'N', 'Q8' => 'O', 'Q9' => 'P', 'Q10' => 'Q',
        'Q11' => 'R', 'Q12' => 'S', 'Q13' => 'T', 'Q14' => 'U', 'Q15' => 'V',
        'Q16' => 'W', 'Q17' => 'X'
    ];

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->model('alumni_model');
        $this->CI->load->model('tracer_model');
        $this->CI->load->model('iku/iku_model');
        $this->CI->load->library('email');
    }

    /**
     * Generate file Excel untuk Kohort dan Prodi tertentu
     * @param int $kohort_id
     * @param int $prodi_id
     * @return array ['status' => bool, 'file' => path|null, 'message' => string]
     */
    public function generate($kohort_id, $prodi_id) {
        // 1. Cek Business Rules
        $check_rules = $this->_checkBusinessRules($kohort_id, $prodi_id);
        if (!$check_rules['valid']) {
            return ['status' => false, 'message' => $check_rules['message']];
        }

        // 2. Ambil Data Alumni
        $alumni_data = $this->CI->alumni_model->get_by_kohort_prodi($kohort_id, $prodi_id);
        
        if (empty($alumni_data)) {
            return ['status' => false, 'message' => 'Tidak ada data alumni untuk kohort/prodi ini.'];
        }

        // 3. Format Data
        $formatted_data = $this->formatData($alumni_data);

        // 4. Generate Excel
        $this->spreadsheet = new Spreadsheet();
        $this->sheet = $this->spreadsheet->getActiveSheet();
        $this->sheet->setTitle('Data Tracer');

        $this->_createHeader();
        $this->_fillData($formatted_data);
        $this->_styleSheet();

        // 5. Simpan File
        $filename = 'Export_Belmawa_Prodi_'.$prodi_id.'_Kohort_'.$kohort_id.'_'.date('YmdHis').'.xlsx';
        $save_path = FCPATH . 'uploads/exports/' . $filename;

        // Pastikan direktori ada
        if (!file_exists(FCPATH . 'uploads/exports')) {
            mkdir(FCPATH . 'uploads/exports', 0755, true);
        }

        $writer = new Xlsx($this->spreadsheet);
        $writer->save($save_path);

        // Catat Job
        $this->_logJob($kohort_id, $prodi_id, $filename);

        return ['status' => true, 'file' => $filename, 'message' => 'Export berhasil'];
    }

    /**
     * Format data alumni sesuai kolom Belmawa
     */
    public function formatData($alumni_data) {
        $result = [];
        
        foreach ($alumni_data as $alumni) {
            // Ambil detail tracer jika ada
            $tracer = $this->CI->tracer_model->get_by_alumni($alumni->id);
            
            // Tentukan Status (E)
            $status_code = $this->_determineStatusCode($alumni, $tracer);
            
            // Hitung Masa Tunggu (F)
            $wait_time = $this->_calculateWaitTime($alumni->graduation_date, $tracer->work_start_date ?? null);
            
            // Kode Gaji (G)
            $salary_code = $this->_getSalaryCode($tracer->salary ?? 0);

            $row = [
                'A' => $alumni->nim,
                'B' => $alumni->name,
                'C' => $alumni->study_program_name,
                'D' => date('Y', strtotime($alumni->graduation_date)),
                'E' => $status_code,
                'F' => $wait_time,
                'G' => $salary_code,
            ];

            // H-X: Jawaban Pertanyaan Inti
            if ($tracer) {
                foreach ($this->question_codes as $q_key => $col_letter) {
                    $answer = $tracer->$q_key ?? ''; 
                    $row[$col_letter] = $this->_mapAnswerToCode($q_key, $answer);
                }
            } else {
                foreach ($this->question_codes as $col_letter) {
                    $row[$col_letter] = '';
                }
            }

            $result[] = $row;
        }
        return $result;
    }

    /**
     * Validasi format file upload
     */
    public function validateFormat($file_path) {
        try {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
            $spreadsheet = $reader->load($file_path);
            $sheet = $spreadsheet->getActiveSheet();
            
            $header_nim = $sheet->getCell('A1')->getValue();
            if ($header_nim !== 'NIM' && strtolower(trim($header_nim)) !== 'nim') {
                return ['valid' => false, 'message' => 'Kolom A harus NIM'];
            }
            
            return ['valid' => true, 'message' => 'Format valid'];
        } catch (Exception $e) {
            return ['valid' => false, 'message' => 'File Excel rusak: ' . $e->getMessage()];
        }
    }

    /**
     * Import feedback dari file Excel Belmawa
     */
    public function importFeedback($file_path) {
        $validation = $this->validateFormat($file_path);
        if (!$validation['valid']) {
            return $validation;
        }

        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
        $spreadsheet = $reader->load($file_path);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();

        $imported = 0;
        $errors = [];

        for ($row = 2; $row <= $highestRow; $row++) {
            $nim = $sheet->getCell('A' . $row)->getValue();
            $feedback = $sheet->getCell('Z' . $row)->getValue();
            
            if ($nim) {
                $alumni = $this->CI->alumni_model->get_by_nim($nim);
                if ($alumni) {
                    $data = [
                        'alumni_id' => $alumni->id,
                        'belmawa_feedback' => $feedback,
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                    $this->CI->db->update('tracer', $data, ['alumni_id' => $alumni->id]);
                    $imported++;
                } else {
                    $errors[] = "NIM $nim tidak ditemukan";
                }
            }
        }

        return ['status' => true, 'imported' => $imported, 'errors' => $errors];
    }

    // --- Private Helper Methods ---

    private function _checkBusinessRules($kohort_id, $prodi_id) {
        // BR-IKU-005: Cek Role
        $user_role = $this->CI->session->userdata('role');
        if (!in_array($user_role, ['admin_pusat_karir', 'super_admin'])) {
            return ['valid' => false, 'message' => 'Akses ditolak: Hanya Admin Pusat Karir/Super Admin yang boleh export.'];
        }

        // ERR-IKU-002: Cek Response Rate
        $stats = $this->CI->iku_model->get_response_stats($kohort_id, $prodi_id);
        $min_threshold = 70;
        
        if ($stats->response_rate < $min_threshold) {
            return ['valid' => false, 'message' => 'ERR-IKU-002: Response rate ('.$stats->response_rate.'%) belum memenuhi minimum ('.$min_threshold.'%). Export dicegah.'];
        }

        // ERR-IKU-003: Cek Immutable
        $already_sent = $this->CI->db->where('kohort_id', $kohort_id)
                                     ->where('prodi_id', $prodi_id)
                                     ->where('status', 'sent_to_belmawa')
                                     ->get('export_logs')
                                     ->row();
        
        if ($already_sent) {
            return ['valid' => false, 'message' => 'ERR-IKU-003: Data untuk periode ini sudah dikirim dan bersifat immutable.'];
        }

        return ['valid' => true, 'message' => 'OK'];
    }

    private function _determineStatusCode($alumni, $tracer) {
        if (!$tracer) return '4';

        if (!empty($tracer->is_working)) return '1';
        if (!empty($tracer->is_entrepreneur)) return '2';
        if (!empty($tracer->is_study)) return '3';
        
        return '4';
    }

    private function _calculateWaitTime($grad_date, $work_date) {
        if (!$work_date) return 0;
        $grad = new DateTime($grad_date);
        $work = new DateTime($work_date);
        $diff = $grad->diff($work);
        return ($diff->y * 12) + $diff->m;
    }

    private function _getSalaryCode($salary) {
        if ($salary == 0) return '0';
        if ($salary < 2000000) return '1';
        if ($salary < 4000000) return '2';
        if ($salary < 6000000) return '3';
        if ($salary < 10000000) return '4';
        return '5';
    }

    private function _mapAnswerToCode($question, $answer) {
        $maps = [
            'Q1' => ['sangat_puas' => '1', 'puas' => '2', 'kurang' => '3', 'tidak_puas' => '4'],
        ];
        
        if (isset($maps[$question][$answer])) {
            return $maps[$question][$answer];
        }
        return $answer;
    }

    private function _createHeader() {
        $headers = [
            'A1' => 'NIM', 'B1' => 'Nama', 'C1' => 'Program Studi', 'D1' => 'Tahun Lulus',
            'E1' => 'Status', 'F1' => 'Masa Tunggu (Bulan)', 'G1' => 'Gaji (Kode)',
            'H1' => 'Q1', 'I1' => 'Q2', 'J1' => 'Q3', 'K1' => 'Q4', 'L1' => 'Q5',
            'M1' => 'Q6', 'N1' => 'Q7', 'O1' => 'Q8', 'P1' => 'Q9', 'Q1' => 'Q10',
            'R1' => 'Q11', 'S1' => 'Q12', 'T1' => 'Q13', 'U1' => 'Q14', 'V1' => 'Q15',
            'W1' => 'Q16', 'X1' => 'Q17'
        ];

        foreach ($headers as $cell => $value) {
            $this->sheet->setCellValue($cell, $value);
        }
    }

    private function _fillData($data) {
        $row_num = 2;
        foreach ($data as $row_data) {
            foreach ($row_data as $col => $value) {
                $this->sheet->setCellValue($col . $row_num, $value);
            }
            $row_num++;
        }
    }

    private function _styleSheet() {
        $highestRow = $this->sheet->getHighestRow();
        $range = 'A1:X' . $highestRow;
        
        $this->sheet->getStyle($range)->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => '000000']]]
        ]);

        $this->sheet->getStyle('A1:X1')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFCC00']]
        ]);

        foreach (range('A', 'X') as $col) {
            $this->sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    private function _logJob($kohort_id, $prodi_id, $filename) {
        $data = [
            'job_type' => 'export_belmawa',
            'kohort_id' => $kohort_id,
            'prodi_id' => $prodi_id,
            'filename' => $filename,
            'status' => 'completed',
            'created_at' => date('Y-m-d H:i:s'),
            'user_id' => $this->CI->session->userdata('user_id')
        ];
        $this->CI->db->insert('jobs', $data);
    }
}
