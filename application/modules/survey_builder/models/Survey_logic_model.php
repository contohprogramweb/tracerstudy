<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Survey Logic Model
 * Handles logic jump/conditional branching operations with cycle detection
 */
class Survey_logic_model extends MY_Model {

    protected $table_name = 'survey_logics';
    protected $primary_key = 'id';

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Get all logics for a survey
     */
    public function get_by_survey($survey_id) {
        $this->db->where('survey_id', $survey_id);
        return $this->db->get('survey_logics')->result();
    }

    /**
     * Get logic by ID
     */
    public function get_by_id($id) {
        return $this->db->get_where('survey_logics', ['id' => $id])->row();
    }

    /**
     * Insert new logic jump
     */
    public function insert($data) {
        $this->db->insert('survey_logics', $data);
        return $this->db->insert_id();
    }

    /**
     * Delete logic jump
     */
    public function delete($id) {
        return $this->db->delete('survey_logics', ['id' => $id]);
    }

    /**
     * Get logics for a specific question
     */
    public function get_by_question($question_id) {
        $this->db->where('question_id', $question_id);
        return $this->db->get('survey_logics')->result();
    }

    /**
     * BR-SUR-003: Validate cycle using DFS (Depth-First Search)
     * Detects if adding a logic jump from question_id to target_id creates a cycle
     */
    public function validate_cycle($survey_id, $start_question_id, $target_question_id) {
        // Build adjacency list of existing logic jumps
        $adjacency = $this->_build_adjacency_list($survey_id);
        
        // Add the proposed new edge temporarily
        if (!isset($adjacency[$start_question_id])) {
            $adjacency[$start_question_id] = [];
        }
        $adjacency[$start_question_id][] = $target_question_id;

        // Perform DFS to detect cycle
        $visited = [];
        $recursion_stack = [];
        
        return $this->_has_cycle_dfs($target_question_id, $adjacency, $visited, $recursion_stack, $start_question_id);
    }

    /**
     * Build adjacency list from existing logics
     */
    private function _build_adjacency_list($survey_id) {
        $this->db->select('question_id, target_question_id');
        $this->db->where('survey_id', $survey_id);
        $logics = $this->db->get('survey_logics')->result();

        $adjacency = [];
        foreach ($logics as $logic) {
            if (!isset($adjacency[$logic->question_id])) {
                $adjacency[$logic->question_id] = [];
            }
            $adjacency[$logic->question_id][] = $logic->target_question_id;
        }

        return $adjacency;
    }

    /**
     * DFS algorithm to detect cycle
     * Returns TRUE if cycle is detected that would lead back to original_start
     */
    private function _has_cycle_dfs($node, &$adjacency, &$visited, &$recursion_stack, $original_start) {
        // Mark current node as visited and add to recursion stack
        $visited[$node] = true;
        $recursion_stack[$node] = true;

        // Visit all adjacent nodes
        if (isset($adjacency[$node])) {
            foreach ($adjacency[$node] as $neighbor) {
                // If neighbor is not visited, recurse
                if (!isset($visited[$neighbor])) {
                    if ($this->_has_cycle_dfs($neighbor, $adjacency, $visited, $recursion_stack, $original_start)) {
                        return true;
                    }
                }
                // If neighbor is in recursion stack, we found a cycle
                elseif (isset($recursion_stack[$neighbor]) && $recursion_stack[$neighbor]) {
                    // Check if this cycle involves the original start node
                    if ($neighbor == $original_start) {
                        return true;
                    }
                }
            }
        }

        // Remove node from recursion stack
        unset($recursion_stack[$node]);
        return false;
    }

    /**
     * Get all questions reachable from a given question via logic jumps
     */
    public function get_reachable_questions($survey_id, $start_question_id) {
        $adjacency = $this->_build_adjacency_list($survey_id);
        $reachable = [];
        $visited = [];

        $this->_dfs_reachable($start_question_id, $adjacency, $visited, $reachable);

        return $reachable;
    }

    /**
     * DFS to find all reachable nodes
     */
    private function _dfs_reachable($node, &$adjacency, &$visited, &$reachable) {
        if (isset($visited[$node])) {
            return;
        }

        $visited[$node] = true;

        if (isset($adjacency[$node])) {
            foreach ($adjacency[$node] as $neighbor) {
                $reachable[] = $neighbor;
                $this->_dfs_reachable($neighbor, $adjacency, $visited, $reachable);
            }
        }
    }

    /**
     * Count logic jumps in a survey
     */
    public function count_by_survey($survey_id) {
        $this->db->where('survey_id', $survey_id);
        return $this->db->count_all_results('survey_logics');
    }

    /**
     * Delete all logics for a survey
     */
    public function delete_by_survey($survey_id) {
        return $this->db->delete('survey_logics', ['survey_id' => $survey_id]);
    }

    /**
     * Get complex logic paths for visualization
     */
    public function get_logic_paths($survey_id) {
        $this->db->select('l.*, q.question_text as source_text, t.question_text as target_text, q.order as source_order, t.order as target_order');
        $this->db->from('survey_logics l');
        $this->db->join('survey_questions q', 'l.question_id = q.id');
        $this->db->join('survey_questions t', 'l.target_question_id = t.id');
        $this->db->where('l.survey_id', $survey_id);
        $this->db->order_by('q.order', 'ASC');
        
        return $this->db->get()->result();
    }
}
