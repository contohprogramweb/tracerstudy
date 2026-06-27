<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Survey Model
 * Handles survey CRUD operations and related queries
 */
class Survey_model extends MY_Model {

    protected $table_name = 'surveys';
    protected $primary_key = 'id';

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Get all surveys with stats
     */
    public function get_all_surveys() {
        $this->db->select('s.*, 
                          COUNT(DISTINCT q.id) as total_questions,
                          COUNT(DISTINCT CASE WHEN q.is_core = 1 THEN q.id END) as core_questions,
                          u.username as created_by_name');
        $this->db->from('surveys s');
        $this->db->join('users u', 's.created_by = u.id', 'left');
        $this->db->join('survey_questions q', 's.id = q.survey_id', 'left');
        $this->db->group_by('s.id');
        $this->db->order_by('s.created_at', 'DESC');
        
        return $this->db->get()->result();
    }

    /**
     * Get survey by ID
     */
    public function get_by_id($id) {
        return $this->db->get_where('surveys', ['id' => $id])->row();
    }

    /**
     * Insert new survey
     */
    public function insert($data) {
        $this->db->insert('surveys', $data);
        return $this->db->insert_id();
    }

    /**
     * Update survey
     */
    public function update($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('surveys', $data);
    }

    /**
     * Delete survey (soft delete if column exists)
     */
    public function delete($id) {
        // Check if soft delete column exists
        if ($this->db->field_exists('deleted_at', 'surveys')) {
            $this->db->where('id', $id);
            return $this->db->update('surveys', ['deleted_at' => date('Y-m-d H:i:s')]);
        }
        
        return $this->db->delete('surveys', ['id' => $id]);
    }

    /**
     * Get questions for a survey
     */
    public function get_questions($survey_id) {
        $this->db->where('survey_id', $survey_id);
        $this->db->order_by('order', 'ASC');
        return $this->db->get('survey_questions')->result();
    }

    /**
     * Get questions with logic jumps
     */
    public function get_questions_with_logic($survey_id) {
        $this->db->select('q.*, l.condition_value, l.target_question_id');
        $this->db->from('survey_questions q');
        $this->db->join('survey_logics l', 'q.id = l.question_id', 'left');
        $this->db->where('q.survey_id', $survey_id);
        $this->db->order_by('q.order', 'ASC');
        
        return $this->db->get()->result();
    }

    /**
     * Get logic jumps for a survey
     */
    public function get_logics($survey_id) {
        $this->db->where('survey_id', $survey_id);
        return $this->db->get('survey_logics')->result();
    }

    /**
     * Count core questions in a survey
     */
    public function count_core_questions($survey_id) {
        $this->db->where('survey_id', $survey_id);
        $this->db->where('is_core', 1);
        return $this->db->count_all_results('survey_questions');
    }

    /**
     * Insert question
     */
    public function insert_question($data) {
        $this->db->insert('survey_questions', $data);
        return $this->db->insert_id();
    }

    /**
     * Insert logic jump
     */
    public function insert_logic($data) {
        $this->db->insert('survey_logics', $data);
        return $this->db->insert_id();
    }

    /**
     * Check if survey can be published
     */
    public function can_publish($survey_id) {
        $core_count = $this->count_core_questions($survey_id);
        return $core_count >= 20;
    }

    /**
     * Get survey statistics
     */
    public function get_stats($survey_id) {
        $this->db->select('COUNT(*) as total_responses');
        $this->db->from('survey_responses');
        $this->db->where('survey_id', $survey_id);
        $total = $this->db->get()->row()->total_responses ?? 0;

        $this->db->select('COUNT(DISTINCT respondent_id) as unique_respondents');
        $this->db->from('survey_responses');
        $this->db->where('survey_id', $survey_id);
        $unique = $this->db->get()->row()->unique_respondents ?? 0;

        return [
            'total_responses' => $total,
            'unique_respondents' => $unique
        ];
    }
}
