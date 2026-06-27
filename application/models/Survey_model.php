<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Survey Model
 * Handles survey data operations including offline sync support
 */
class Survey_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Save survey response to database
     * @param array $data Survey response data
     * @return int|bool Response ID on success, FALSE on failure
     */
    public function save_response($data) {
        // Prepare response data
        $response_data = [
            'survey_id' => $data['survey_id'],
            'respondent_id' => isset($data['respondent_id']) ? $data['respondent_id'] : null,
            'answers' => json_encode($data['answers']),
            'status' => isset($data['status']) ? $data['status'] : 'completed',
            'submitted_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Check if table exists, if not create it
        if (!$this->db->table_exists('survey_responses')) {
            $this->create_responses_table();
        }

        // Insert response
        if ($this->db->insert('survey_responses', $response_data)) {
            return $this->db->insert_id();
        }

        return false;
    }

    /**
     * Get survey by ID
     * @param int $survey_id
     * @return object|null
     */
    public function get_survey($survey_id) {
        $query = $this->db->where('id', $survey_id)->get('surveys');
        return $query->row();
    }

    /**
     * Get survey questions
     * @param int $survey_id
     * @return array
     */
    public function get_questions($survey_id) {
        $query = $this->db
            ->where('survey_id', $survey_id)
            ->order_by('order', 'ASC')
            ->get('survey_questions');
        return $query->result_array();
    }

    /**
     * Check if respondent already submitted
     * @param int $survey_id
     * @param string $respondent_id
     * @return bool
     */
    public function has_submitted($survey_id, $respondent_id) {
        $query = $this->db
            ->where('survey_id', $survey_id)
            ->where('respondent_id', $respondent_id)
            ->where('status', 'completed')
            ->get('survey_responses');
        return $query->num_rows() > 0;
    }

    /**
     * Get pending offline submissions (for admin/sync purposes)
     * @return array
     */
    public function get_pending_sync() {
        $query = $this->db
            ->where('sync_status', 'pending')
            ->get('survey_responses');
        return $query->result_array();
    }

    /**
     * Mark response as synced
     * @param int $response_id
     * @return bool
     */
    public function mark_as_synced($response_id) {
        return $this->db
            ->where('id', $response_id)
            ->update('survey_responses', [
                'sync_status' => 'synced',
                'synced_at' => date('Y-m-d H:i:s')
            ]);
    }

    /**
     * Create survey_responses table if not exists
     */
    private function create_responses_table() {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS survey_responses (
                id INT AUTO_INCREMENT PRIMARY KEY,
                survey_id INT NOT NULL,
                respondent_id VARCHAR(100),
                answers TEXT,
                status VARCHAR(20) DEFAULT 'completed',
                sync_status VARCHAR(20) DEFAULT 'synced',
                submitted_at DATETIME,
                synced_at DATETIME,
                created_at DATETIME,
                updated_at DATETIME,
                INDEX idx_survey (survey_id),
                INDEX idx_respondent (respondent_id),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    /**
     * Cache survey questions for offline use
     * @param int $survey_id
     * @param array $questions
     */
    public function cache_questions($survey_id, $questions) {
        $cache_data = [
            'survey_id' => $survey_id,
            'questions' => $questions,
            'cached_at' => time(),
            'expires_at' => time() + (7 * 24 * 60 * 60) // 7 days
        ];

        // Store in a cache table or file
        $this->db->replace('survey_cache', [
            'cache_key' => 'questions_' . $survey_id,
            'cache_value' => json_encode($cache_data),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get cached survey questions
     * @param int $survey_id
     * @return array|null
     */
    public function get_cached_questions($survey_id) {
        $query = $this->db
            ->where('cache_key', 'questions_' . $survey_id)
            ->get('survey_cache');
        
        $row = $query->row();
        if ($row) {
            $data = json_decode($row->cache_value, true);
            // Check if cache is still valid
            if ($data && $data['expires_at'] > time()) {
                return $data['questions'];
            }
        }
        return null;
    }
}
