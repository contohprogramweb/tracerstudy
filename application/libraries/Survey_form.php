<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Survey Form Library
 * 
 * Handles survey form logic, validation, progress saving, and certificate generation
 * with offline support
 * 
 * Business Rules:
 * - BR-ALM-001: Hanya kohort aktif yang bisa isi
 * - BR-ALM-007: Tidak boleh isi 2x untuk kohort yang sama
 * - BR-SUR-005: Auto-save ke localStorage, bisa dilanjutkan 7 hari
 * - BR-SUR-008: Prioritas bekerja > wirausaha > studi > belum untuk IKU
 * 
 * @package Tracer Study
 * @subpackage Libraries
 */
class Survey_form {

    protected $CI;

    /**
     * Constructor
     */
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->database();
        $this->CI->load->helper(['url', 'date']);
    }

    /**
     * Get questions with logic applied for specific alumni
     * 
     * @param int $survey_id Survey ID
     * @param int $alumni_id Alumni ID
     * @return array Questions array with logic metadata
     */
    public function getQuestionsWithLogic($survey_id, $alumni_id) {
        // Get all questions for this survey
        $this->CI->db->where('survey_id', $survey_id);
        $this->CI->db->where('deleted_at', NULL);
        $this->CI->db->order_by('order', 'ASC');
        $questions = $this->CI->db->get('survey_questions')->result_array();
        
        // Get logic jumps
        $this->CI->db->select('l.*, q.type as question_type');
        $this->CI->db->from('survey_logics l');
        $this->CI->db->join('survey_questions q', 'l.question_id = q.id');
        $this->CI->db->where('l.survey_id', $survey_id);
        $logics = $this->CI->db->get()->result_array();
        
        // Attach logics to questions
        foreach ($questions as &$question) {
            $question['logics'] = [];
            $question['has_logic'] = false;
            
            foreach ($logics as $logic) {
                if ($logic['question_id'] == $question['id']) {
                    $question['logics'][] = [
                        'condition_value' => $logic['condition_value'],
                        'target_question_id' => $logic['target_question_id']
                    ];
                    $question['has_logic'] = true;
                }
            }
            
            // Parse options for choice questions
            if (!empty($question['options'])) {
                $question['parsed_options'] = explode('|', $question['options']);
            } else {
                $question['parsed_options'] = [];
            }
        }
        
        return $questions;
    }

    /**
     * Process logic jump based on answers
     * Determines next question based on conditional logic
     * 
     * @param int $survey_id Survey ID
     * @param array $answers Current answers
     * @return array ['next_question_id' => int, 'jumped' => bool]
     */
    public function processLogicJump($survey_id, $answers) {
        // Get all logics for this survey
        $this->CI->db->where('survey_id', $survey_id);
        $logics = $this->CI->db->get('survey_logics')->result_array();
        
        $next_question_id = null;
        $jumped = false;
        
        // Find the last answered question with logic
        foreach ($logics as $logic) {
            $question_id = $logic['question_id'];
            
            if (isset($answers[$question_id])) {
                $answer = $answers[$question_id];
                $condition_value = $logic['condition_value'];
                
                // Check if answer matches condition
                if ($this->_checkCondition($answer, $condition_value)) {
                    $next_question_id = $logic['target_question_id'];
                    $jumped = true;
                    break;
                }
            }
        }
        
        return [
            'next_question_id' => $next_question_id,
            'jumped' => $jumped
        ];
    }

    /**
     * Check if answer matches condition
     * 
     * @param mixed $answer User's answer
     * @param string $condition Condition value
     * @return bool
     */
    private function _checkCondition($answer, $condition) {
        // Handle multiple choice (checkbox) - answer is array
        if (is_array($answer)) {
            return in_array($condition, $answer);
        }
        
        // Handle single value
        return (string)$answer === (string)$condition;
    }

    /**
     * Validate answers before submission
     * 
     * @param int $survey_id Survey ID
     * @param array $answers Answers array
     * @return array ['valid' => bool, 'message' => string, 'errors' => array]
     */
    public function validateAnswers($survey_id, $answers) {
        // Get required questions
        $this->CI->db->where('survey_id', $survey_id);
        $this->CI->db->where('is_required', 1);
        $this->CI->db->where('deleted_at', NULL);
        $required_questions = $this->CI->db->get('survey_questions')->result_array();
        
        $errors = [];
        
        foreach ($required_questions as $question) {
            $q_id = $question['id'];
            
            // Check if answer exists
            if (!isset($answers[$q_id]) || $answers[$q_id] === '') {
                $errors[$q_id] = "Pertanyaan '{$question['question_text']}' wajib diisi";
                continue;
            }
            
            $answer = $answers[$q_id];
            
            // Type-specific validation
            switch ($question['type']) {
                case 'email':
                    if (!filter_var($answer, FILTER_VALIDATE_EMAIL)) {
                        $errors[$q_id] = "Format email tidak valid";
                    }
                    break;
                    
                case 'number':
                case 'rating':
                    if (!is_numeric($answer)) {
                        $errors[$q_id] = "Harus berupa angka";
                    }
                    break;
                    
                case 'date':
                    if (!strtotime($answer)) {
                        $errors[$q_id] = "Format tanggal tidak valid";
                    }
                    break;
                    
                case 'checkbox':
                    if (!is_array($answer) || empty($answer)) {
                        $errors[$q_id] = "Pilih minimal satu opsi";
                    }
                    break;
            }
        }
        
        if (!empty($errors)) {
            return [
                'valid' => false,
                'message' => 'Mohon lengkapi jawaban yang wajib diisi',
                'errors' => $errors
            ];
        }
        
        return [
            'valid' => true,
            'message' => 'Validasi berhasil'
        ];
    }

    /**
     * Save survey progress (auto-save)
     * 
     * @param int $survey_id Survey ID
     * @param int $alumni_id Alumni ID
     * @param array $answers Current answers
     * @param int $current_question_id Current question being answered
     * @param int $progress_percent Progress percentage
     * @return array ['success' => bool, 'saved_at' => string]
     */
    public function saveProgress($survey_id, $alumni_id, $answers, $current_question_id = null, $progress_percent = 0) {
        // Check if progress already exists
        $this->CI->db->where('survey_id', $survey_id);
        $this->CI->db->where('alumni_id', $alumni_id);
        $existing = $this->CI->db->get('survey_progress')->row();
        
        $data = [
            'survey_id' => $survey_id,
            'alumni_id' => $alumni_id,
            'answers' => json_encode($answers),
            'current_question_id' => $current_question_id,
            'progress_percent' => $progress_percent,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($existing) {
            // Update existing progress
            $this->CI->db->where('id', $existing->id);
            $success = $this->CI->db->update('survey_progress', $data);
        } else {
            // Insert new progress
            $data['created_at'] = date('Y-m-d H:i:s');
            $success = $this->CI->db->insert('survey_progress', $data);
        }
        
        return [
            'success' => $success,
            'saved_at' => $data['updated_at']
        ];
    }

    /**
     * Get saved progress
     * 
     * @param int $survey_id Survey ID
     * @param int $alumni_id Alumni ID
     * @return object|null Progress data or null
     */
    public function getSavedProgress($survey_id, $alumni_id) {
        $this->CI->db->where('survey_id', $survey_id);
        $this->CI->db->where('alumni_id', $alumni_id);
        return $this->CI->db->get('survey_progress')->row();
    }

    /**
     * Clear saved progress
     * 
     * @param int $survey_id Survey ID
     * @param int $alumni_id Alumni ID
     * @return bool
     */
    public function clearSavedProgress($survey_id, $alumni_id) {
        $this->CI->db->where('survey_id', $survey_id);
        $this->CI->db->where('alumni_id', $alumni_id);
        return $this->CI->db->delete('survey_progress');
    }

    /**
     * Submit final survey answers
     * 
     * @param int $survey_id Survey ID
     * @param int $alumni_id Alumni ID
     * @param array $answers Final answers
     * @return array ['success' => bool, 'response_id' => int, 'message' => string]
     */
    public function submitFinal($survey_id, $alumni_id, $answers) {
        // Start transaction
        $this->CI->db->trans_start();
        
        // Get alumni data for response metadata
        $alumni = $this->CI->db->get_where('alumni', ['id' => $alumni_id])->row();
        
        // Insert response
        $response_data = [
            'survey_id' => $survey_id,
            'respondent_id' => $alumni_id,
            'respondent_email' => $alumni->email ?? null,
            'submitted_at' => date('Y-m-d H:i:s'),
            'ip_address' => $this->CI->input->ip_address(),
            'user_agent' => $this->CI->input->user_agent()
        ];
        
        $this->CI->db->insert('survey_responses', $response_data);
        $response_id = $this->CI->db->insert_id();
        
        // Insert answers
        foreach ($answers as $question_id => $answer) {
            // Handle array answers (checkbox)
            if (is_array($answer)) {
                $answer_text = implode('|', $answer);
                $answer_value = null;
            } else {
                $answer_text = is_string($answer) ? $answer : null;
                $answer_value = is_numeric($answer) ? $answer : null;
            }
            
            $answer_data = [
                'response_id' => $response_id,
                'question_id' => $question_id,
                'answer_text' => $answer_text,
                'answer_value' => $answer_value,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $this->CI->db->insert('survey_answers', $answer_data);
        }
        
        // Update alumni status kerja based on survey answers (BR-SUR-008)
        $this->_updateAlumniStatus($alumni_id, $answers);
        
        $this->CI->db->trans_complete();
        
        if ($this->CI->db->trans_status() === FALSE) {
            return [
                'success' => false,
                'message' => 'Gagal menyimpan jawaban'
            ];
        }
        
        return [
            'success' => true,
            'response_id' => $response_id,
            'message' => 'Survey berhasil dikirim'
        ];
    }

    /**
     * Update alumni status kerja based on survey answers
     * Implements BR-SUR-008: Prioritas bekerja > wirausaha > studi > belum
     * 
     * @param int $alumni_id Alumni ID
     * @param array $answers Survey answers
     * @return void
     */
    private function _updateAlumniStatus($alumni_id, $answers) {
        // Find status kerja question (assuming it's a core question)
        $status_kerja = null;
        
        // Priority order: bekerja > wirausaha > studi > belum
        foreach ($answers as $q_id => $answer) {
            // Check if this looks like a status kerja answer
            if (is_string($answer)) {
                if (stripos($answer, 'bekerja') !== false) {
                    $status_kerja = 'bekerja';
                    break; // Highest priority
                } elseif (stripos($answer, 'wirausaha') !== false && !$status_kerja) {
                    $status_kerja = 'wirausaha';
                } elseif (stripos($answer, 'studi') !== false && !$status_kerja) {
                    $status_kerja = 'melanjutkan_studi';
                } elseif (stripos($answer, 'belum') !== false && !$status_kerja) {
                    $status_kerja = 'belum_bekerja';
                }
            }
        }
        
        if ($status_kerja) {
            $this->CI->db->where('id', $alumni_id);
            $this->CI->db->update('alumni', [
                'status_kerja' => $status_kerja,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Check if alumni already has a response for this survey
     * 
     * @param int $survey_id Survey ID
     * @param int $alumni_id Alumni ID
     * @return bool
     */
    public function checkExistingResponse($survey_id, $alumni_id) {
        $this->CI->db->where('survey_id', $survey_id);
        $this->CI->db->where('respondent_id', $alumni_id);
        return $this->CI->db->count_all_results('survey_responses') > 0;
    }

    /**
     * Generate PDF Certificate
     * 
     * @param int $response_id Response ID
     * @return array ['success' => bool, 'url' => string|null]
     */
    public function generateSertifikat($response_id) {
        // Get response data
        $this->CI->db->select('r.*, s.title as survey_title, a.nama as alumni_nama, a.nim');
        $this->CI->db->from('survey_responses r');
        $this->CI->db->join('surveys s', 'r.survey_id = s.id');
        $this->CI->db->join('alumni a', 'r.respondent_id = a.id');
        $this->CI->db->where('r.id', $response_id);
        $response = $this->CI->db->get()->row();
        
        if (!$response) {
            return [
                'success' => false,
                'url' => null
            ];
        }
        
        // Generate unique filename
        $filename = 'sertifikat_' . $response->nim . '_' . date('YmdHis') . '.pdf';
        $filepath = FCPATH . 'public/uploads/certificates/' . $filename;
        
        // Create directory if not exists
        if (!file_exists(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, TRUE);
        }
        
        // Simple PDF generation using TCPDF or similar
        // For now, create a placeholder
        $pdf_content = $this->_generateCertificateContent($response);
        
        // In production, use TCPDF, DomPDF, or similar library
        // file_put_contents($filepath, $pdf_content);
        
        // For demo purposes, create a simple text file
        file_put_contents($filepath, "SERTIFIKAT\n\n" . 
            "Telah menyelesaikan: {$response->survey_title}\n" . 
            "Nama: {$response->alumni_nama}\n" . 
            "NIM: {$response->nim}\n" . 
            "Tanggal: {$response->submitted_at}");
        
        $url = base_url('public/uploads/certificates/' . $filename);
        
        return [
            'success' => true,
            'url' => $url
        ];
    }

    /**
     * Generate certificate content (placeholder for PDF library)
     * 
     * @param object $response Response data
     * @return string PDF content
     */
    private function _generateCertificateContent($response) {
        // This should use TCPDF, DomPDF, or mPDF in production
        return "PDF_CONTENT_PLACEHOLDER";
    }
}
