<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Survey Logic Controller
 * Handles logic jump/conditional branching management
 */
class Survey_logic extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(['survey_model', 'survey_question_model', 'survey_logic_model']);
        $this->load->helper(['form', 'url']);
        if (!$this->session->userdata('logged_in')) {
            redirect('auth/login');
        }
    }

    public function index($survey_id) {
        $survey = $this->survey_model->get_by_id($survey_id);
        
        if (!$survey) {
            show_404();
        }

        $data['survey'] = $survey;
        $data['questions'] = $this->survey_question_model->get_by_survey($survey_id);
        $data['logics'] = $this->survey_logic_model->get_by_survey($survey_id);
        $this->load->view('survey_builder/logic', $data);
    }

    public function create($survey_id) {
        $survey = $this->survey_model->get_by_id($survey_id);
        
        if (!$survey || $survey->status === 'published') {
            $this->session->set_flashdata('error', 'Survey tidak ditemukan atau sudah dipublikasikan.');
            redirect('survey_builder/logic/' . $survey_id);
        }

        $data['survey'] = $survey;
        $data['questions'] = $this->survey_question_model->get_by_survey($survey_id);
        $this->load->view('survey_builder/logic_form', $data);
    }

    public function store($survey_id) {
        $survey = $this->survey_model->get_by_id($survey_id);
        
        if (!$survey || $survey->status === 'published') {
            $this->output->set_status_header(403);
            echo json_encode(['success' => false, 'message' => 'Survey sudah dipublikasikan.']);
            return;
        }

        $this->form_validation->set_rules('question_id', 'Pertanyaan Sumber', 'required|numeric');
        $this->form_validation->set_rules('condition_value', 'Nilai Kondisi', 'required|trim');
        $this->form_validation->set_rules('target_question_id', 'Pertanyaan Tujuan', 'required|numeric');

        if ($this->form_validation->run() == FALSE) {
            $this->output->set_status_header(400);
            echo json_encode(['success' => false, 'message' => validation_errors()]);
            return;
        }

        $question_id = $this->input->post('question_id');
        $target_question_id = $this->input->post('target_question_id');
        $condition_value = $this->input->post('condition_value');

        // Validate question exists and is appropriate type
        $question = $this->survey_question_model->get_by_id($question_id);
        
        if (!$question) {
            $this->output->set_status_header(404);
            echo json_encode(['success' => false, 'message' => 'Pertanyaan sumber tidak ditemukan.']);
            return;
        }

        // Only allow logic on questions with options or rating
        if (!in_array($question->type, ['multiple_choice', 'dropdown', 'checkbox', 'rating'])) {
            $this->output->set_status_header(400);
            echo json_encode(['success' => false, 'message' => 'Logic jump hanya bisa diterapkan pada pertanyaan pilihan atau rating.']);
            return;
        }

        // BR-SUR-003: Detect cycle before saving
        $has_cycle = $this->survey_logic_model->validate_cycle($survey_id, $question_id, $target_question_id);
        
        if ($has_cycle) {
            $this->output->set_status_header(400);
            echo json_encode(['success' => false, 'message' => 'Logic jump ini akan membuat referensi melingkar (circular reference).']);
            return;
        }

        // Validate target is not the same as source
        if ($question_id == $target_question_id) {
            $this->output->set_status_header(400);
            echo json_encode(['success' => false, 'message' => 'Pertanyaan tujuan tidak boleh sama dengan pertanyaan sumber.']);
            return;
        }

        $data = [
            'survey_id' => $survey_id,
            'question_id' => $question_id,
            'condition_value' => $condition_value,
            'target_question_id' => $target_question_id,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $logic_id = $this->survey_logic_model->insert($data);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Logic jump berhasil ditambahkan!',
            'logic_id' => $logic_id
        ]);
    }

    public function delete($survey_id, $logic_id) {
        $survey = $this->survey_model->get_by_id($survey_id);
        
        if (!$survey || $survey->status === 'published') {
            $this->output->set_status_header(403);
            echo json_encode(['success' => false, 'message' => 'Survey sudah dipublikasikan.']);
            return;
        }

        $logic = $this->survey_logic_model->get_by_id($logic_id);
        
        if (!$logic) {
            $this->output->set_status_header(404);
            echo json_encode(['success' => false, 'message' => 'Logic tidak ditemukan.']);
            return;
        }

        $this->survey_logic_model->delete($logic_id);
        
        echo json_encode(['success' => true, 'message' => 'Logic jump berhasil dihapus!']);
    }

    /**
     * AJAX endpoint to validate cycle detection
     */
    public function validate_cycle_ajax() {
        if (!$this->input->is_ajax_request()) {
            show_error('Unauthorized access', 403);
            return;
        }

        $survey_id = $this->input->post('survey_id');
        $question_id = $this->input->post('question_id');
        $target_id = $this->input->post('target_id');

        $has_cycle = $this->survey_logic_model->validate_cycle($survey_id, $question_id, $target_id);
        
        echo json_encode(['has_cycle' => $has_cycle]);
    }

    /**
     * Get all logics for a survey (for visual builder)
     */
    public function get_logics($survey_id) {
        if (!$this->input->is_ajax_request()) {
            show_error('Unauthorized access', 403);
            return;
        }

        $logics = $this->survey_logic_model->get_by_survey($survey_id);
        
        $result = [];
        foreach ($logics as $logic) {
            $question = $this->survey_question_model->get_by_id($logic->question_id);
            $target = $this->survey_question_model->get_by_id($logic->target_question_id);
            
            $result[] = [
                'id' => $logic->id,
                'question_text' => $question ? $question->question_text : 'Unknown',
                'condition_value' => $logic->condition_value,
                'target_text' => $target ? $target->question_text : 'Unknown',
                'target_order' => $target ? $target->order : 0
            ];
        }

        echo json_encode(['success' => true, 'logics' => $result]);
    }
}
