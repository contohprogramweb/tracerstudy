<?php
defined('BASEPATH') OR exit('No direct script access allowed');


/**
 * Survey Controller - Alumni Survey Form
 * 
 * Handles survey form display, submission, auto-save, and resume functionality
 * with offline support and mobile-first design
 * 
 * Business Rules:
 * - BR-ALM-001: Hanya kohort aktif yang bisa isi
 * - BR-ALM-007: Tidak boleh isi 2x untuk kohort yang sama
 * - BR-SUR-005: Auto-save ke localStorage, bisa dilanjutkan 7 hari
 * - BR-SUR-008: Prioritas bekerja > wirausaha > studi > belum untuk IKU
 * 
 * @package Tracer Study
 * @subpackage Survey
 */
class Survey extends MY_Controller {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        
        // Load models
        $this->load->model('survey_builder/survey_model');
        $this->load->model('alumni/alumni_model');
        
        // Load library
        $this->load->library('survey_form');
        
        // Load helpers
        $this->load->helper(['form', 'url', 'security']);
        
        // Load session
        $this->load->library('session');
        
        // Check if user is logged in (alumni)
        if (!$this->session->userdata('alumni_id')) {
            redirect('auth/login');
        }
    }

    /**
     * Display survey form
     * 
     * @param int $survey_id Survey ID
     * @return void
     */
    public function form($survey_id) {
        // Validate survey exists
        $survey = $this->survey_model->get_by_id($survey_id);
        
        if (!$survey) {
            show_404();
        }
        
        // Get alumni data
        $alumni_id = $this->session->userdata('alumni_id');
        $alumni = $this->alumni_model->get($alumni_id);
        
        // BR-ALM-001: Check if alumni's cohort is active
        $this->db->select('status');
        $kohort = $this->db->get_where('kohort', ['id' => $alumni->kohort_id])->row();
        
        if ($kohort && $kohort->status !== 'active') {
            $this->session->set_flashdata('error', 'Maaf, survey hanya tersedia untuk kohort aktif.');
            redirect('alumni/dashboard');
        }
        
        // BR-ALM-007: Check if already submitted for this cohort
        $existing_response = $this->survey_form->checkExistingResponse($survey_id, $alumni_id);
        
        if ($existing_response) {
            $this->session->set_flashdata('error', 'Anda sudah mengisi survey ini sebelumnya.');
            redirect('alumni/dashboard');
        }
        
        // Get questions with logic applied
        $questions = $this->survey_form->getQuestionsWithLogic($survey_id, $alumni_id);
        
        // Check for saved progress
        $saved_progress = $this->survey_form->getSavedProgress($survey_id, $alumni_id);
        
        // Prepare view data
        $data = [
            'survey' => $survey,
            'questions' => $questions,
            'alumni' => $alumni,
            'saved_progress' => $saved_progress,
            'page_title' => 'Survey Tracer Study',
            'total_questions' => count($questions),
            'csrf_token' => $this->security->get_csrf_hash()
        ];
        
        $this->load->view('survey/form', $data);
    }

    /**
     * Submit final survey answers
     * 
     * @param int $survey_id Survey ID
     * @return void
     */
    public function submit($survey_id) {
        // Only accept POST
        if ($this->input->method() !== 'post') {
            show_error('Method not allowed', 405);
        }
        
        $alumni_id = $this->session->userdata('alumni_id');
        
        // Get answers from POST
        $answers = $this->input->post('answers');
        
        // Validate answers
        $validation = $this->survey_form->validateAnswers($survey_id, $answers);
        
        if (!$validation['valid']) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => $validation['message'],
                    'errors' => $validation['errors'] ?? []
                ]));
            return;
        }
        
        // Submit final
        $result = $this->survey_form->submitFinal($survey_id, $alumni_id, $answers);
        
        if ($result['success']) {
            // Clear saved progress
            $this->survey_form->clearSavedProgress($survey_id, $alumni_id);
            
            // Generate certificate if applicable
            $certificate_url = null;
            if ($survey->generate_certificate) {
                $cert_result = $this->survey_form->generateSertifikat($result['response_id']);
                $certificate_url = $cert_result['url'] ?? null;
            }
            
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => 'success',
                    'message' => 'Survey berhasil dikirim!',
                    'response_id' => $result['response_id'],
                    'certificate_url' => $certificate_url
                ]));
        } else {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => $result['message']
                ]));
        }
    }

    /**
     * AJAX Auto-save endpoint
     * Saves progress to database and localStorage
     * 
     * @param int $survey_id Survey ID
     * @return void
     */
    public function autosave($survey_id) {
        // Only accept POST
        if ($this->input->method() !== 'post') {
            show_error('Method not allowed', 405);
        }
        
        $alumni_id = $this->session->userdata('alumni_id');
        
        // Get answers from POST
        $answers = $this->input->post('answers');
        $current_question = $this->input->post('current_question');
        $progress_percent = $this->input->post('progress_percent');
        
        // Save progress
        $result = $this->survey_form->saveProgress(
            $survey_id, 
            $alumni_id, 
            $answers,
            $current_question,
            $progress_percent
        );
        
        if ($result['success']) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => 'success',
                    'message' => 'Progress tersimpan',
                    'timestamp' => date('Y-m-d H:i:s'),
                    'saved_at' => $result['saved_at']
                ]));
        } else {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'Gagal menyimpan progress'
                ]));
        }
    }

    /**
     * Resume incomplete survey
     * Loads saved progress from database
     * 
     * @param int $survey_id Survey ID
     * @return void
     */
    public function resume($survey_id) {
        $alumni_id = $this->session->userdata('alumni_id');
        
        // Get saved progress
        $saved_progress = $this->survey_form->getSavedProgress($survey_id, $alumni_id);
        
        if (!$saved_progress) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => 'not_found',
                    'message' => 'Tidak ada progress yang tersimpan'
                ]));
            return;
        }
        
        // Check if still within 7 days (BR-SUR-005)
        $saved_date = strtotime($saved_progress->updated_at);
        $current_date = time();
        $days_diff = ($current_date - $saved_date) / (60 * 60 * 24);
        
        if ($days_diff > 7) {
            // Expired, clear progress
            $this->survey_form->clearSavedProgress($survey_id, $alumni_id);
            
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => 'expired',
                    'message' => 'Progress sudah kadaluarsa (7 hari)'
                ]));
            return;
        }
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'progress' => [
                    'answers' => json_decode($saved_progress->answers, true),
                    'current_question' => $saved_progress->current_question_id,
                    'progress_percent' => $saved_progress->progress_percent,
                    'updated_at' => $saved_progress->updated_at
                ]
            ]));
    }

    /**
     * Check survey status for alumni
     * Returns whether they can fill, have filled, or have in-progress survey
     * 
     * @param int $survey_id Survey ID
     * @return void
     */
    public function status($survey_id) {
        $alumni_id = $this->session->userdata('alumni_id');
        
        $alumni = $this->alumni_model->get($alumni_id);
        
        // Check cohort status
        $this->db->select('status');
        $kohort = $this->db->get_where('kohort', ['id' => $alumni->kohort_id])->row();
        
        $can_fill = false;
        $already_filled = false;
        $has_progress = false;
        $message = '';
        
        // BR-ALM-001: Check active cohort
        if (!$kohort || $kohort->status !== 'active') {
            $message = 'Survey hanya tersedia untuk kohort aktif';
        } else {
            // BR-ALM-007: Check if already submitted
            $existing_response = $this->survey_form->checkExistingResponse($survey_id, $alumni_id);
            
            if ($existing_response) {
                $already_filled = true;
                $message = 'Anda sudah mengisi survey ini';
            } else {
                // Check for saved progress
                $saved_progress = $this->survey_form->getSavedProgress($survey_id, $alumni_id);
                
                if ($saved_progress) {
                    $has_progress = true;
                    $message = 'Anda memiliki progress yang tersimpan';
                }
                
                $can_fill = true;
            }
        }
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'can_fill' => $can_fill,
                'already_filled' => $already_filled,
                'has_progress' => $has_progress,
                'message' => $message,
                'kohort_status' => $kohort ? $kohort->status : 'unknown'
            ]));
    }
}
