<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Survey Question Model
 * Handles question CRUD operations
 */
class Survey_question_model extends MY_Model {

    protected $table_name = 'survey_questions';
    protected $primary_key = 'id';

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Get all questions for a survey
     */
    public function get_by_survey($survey_id) {
        $this->db->where('survey_id', $survey_id);
        $this->db->order_by('order', 'ASC');
        return $this->db->get('survey_questions')->result();
    }

    /**
     * Get question by ID
     */
    public function get_by_id($id) {
        return $this->db->get_where('survey_questions', ['id' => $id])->row();
    }

    /**
     * Insert new question
     */
    public function insert($data) {
        $this->db->insert('survey_questions', $data);
        return $this->db->insert_id();
    }

    /**
     * Update question
     */
    public function update($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('survey_questions', $data);
    }

    /**
     * Delete question
     */
    public function delete($id) {
        // Also delete associated logic jumps
        $this->db->delete('survey_logics', ['question_id' => $id]);
        $this->db->delete('survey_logics', ['target_question_id' => $id]);
        
        return $this->db->delete('survey_questions', ['id' => $id]);
    }

    /**
     * Get max order for a survey
     */
    public function get_max_order($survey_id) {
        $this->db->select_max('order');
        $this->db->where('survey_id', $survey_id);
        $result = $this->db->get('survey_questions')->row();
        
        return $result->order ?? 0;
    }

    /**
     * Update question order
     */
    public function update_order($id, $order) {
        $data = ['order' => $order];
        $this->db->where('id', $id);
        return $this->db->update('survey_questions', $data);
    }

    /**
     * Reorder questions after deletion
     */
    public function reorder_after_delete($survey_id, $deleted_order) {
        $this->db->where('survey_id', $survey_id);
        $this->db->where('order >', $deleted_order);
        $this->db->set('order', 'order - 1', FALSE);
        return $this->db->update('survey_questions');
    }

    /**
     * Get questions by type
     */
    public function get_by_type($survey_id, $type) {
        $this->db->where('survey_id', $survey_id);
        $this->db->where('type', $type);
        $this->db->order_by('order', 'ASC');
        return $this->db->get('survey_questions')->result();
    }

    /**
     * Count questions in a survey
     */
    public function count_by_survey($survey_id) {
        $this->db->where('survey_id', $survey_id);
        return $this->db->count_all_results('survey_questions');
    }

    /**
     * Get next question after a given question
     */
    public function get_next_question($survey_id, $current_order) {
        $this->db->where('survey_id', $survey_id);
        $this->db->where('order >', $current_order);
        $this->db->order_by('order', 'ASC');
        $this->db->limit(1);
        return $this->db->get('survey_questions')->row();
    }

    /**
     * Check if question has logic jumps
     */
    public function has_logic($question_id) {
        $this->db->where('question_id', $question_id);
        return $this->db->count_all_results('survey_logics') > 0;
    }
}
