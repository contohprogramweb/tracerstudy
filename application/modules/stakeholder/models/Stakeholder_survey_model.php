<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model: Stakeholder_survey_model
 * Mengelola survey penilaian stakeholder terhadap alumni
 * 
 * Business Rules:
 * - BR-SUR-006: Stakeholder survey wajib linked ke alumni atau prodi
 * - BR-SUR-007: Rating kompetensi di-average 60:40 (stakeholder:alumni)
 */
class Stakeholder_survey_model extends CI_Model {

    private $table = 'stakeholder_surveys';
    private $table_cpl = 'stakeholder_survey_cpl_ratings';
    private $table_invitations = 'stakeholder_survey_invitations';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Create new stakeholder survey
     * 
     * @param array $data
     * @return int Insert ID
     */
    public function create($data)
    {
        // BR-SUR-006: Ensure linked to alumni and prodi
        if (empty($data['alumni_id']) || empty($data['prodi_id'])) {
            throw new Exception('Survey harus linked ke alumni dan prodi (BR-SUR-006)');
        }

        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    /**
     * Get survey by ID
     * 
     * @param int $id
     * @return array|null
     */
    public function getById($id)
    {
        $query = $this->db->get_where($this->table, ['id' => $id]);
        return $query->row_array();
    }

    /**
     * Update survey
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);
    }

    /**
     * Delete survey
     * 
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        // Delete CPL ratings first
        $this->db->delete($this->table_cpl, ['survey_id' => $id]);
        
        // Delete survey
        return $this->db->delete($this->table, ['id' => $id]);
    }

    /**
     * Save CPL ratings for a survey
     * 
     * @param array $ratings_data
     * @return bool
     */
    public function saveCplRatings($ratings_data)
    {
        if (empty($ratings_data)) {
            return false;
        }

        return $this->db->insert_batch($this->table_cpl, $ratings_data);
    }

    /**
     * Get CPL ratings for a survey
     * 
     * @param int $survey_id
     * @return array
     */
    public function getCplRatings($survey_id)
    {
        $this->db->where('survey_id', $survey_id);
        $query = $this->db->get($this->table_cpl);
        
        $result = [];
        foreach ($query->result_array() as $row) {
            $result[$row['cpl_id']] = $row;
        }
        return $result;
    }

    /**
     * Delete CPL ratings for a survey
     * 
     * @param int $survey_id
     * @return bool
     */
    public function deleteCplRatings($survey_id)
    {
        return $this->db->delete($this->table_cpl, ['survey_id' => $survey_id]);
    }

    /**
     * Get surveys by alumni ID
     * 
     * @param int $alumni_id
     * @return array
     */
    public function getByAlumni($alumni_id)
    {
        $this->db->select('ss.*, s.company_name, sh.contact_person as stakeholder_name');
        $this->db->from($this->table . ' ss');
        $this->db->join('stakeholders sh', 'ss.stakeholder_id = sh.id');
        $this->db->where('ss.alumni_id', $alumni_id);
        $this->db->order_by('ss.submitted_at', 'DESC');
        
        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Get surveys by stakeholder ID
     * 
     * @param int $stakeholder_id
     * @param string $status
     * @return array
     */
    public function getByStakeholder($stakeholder_id, $status = null)
    {
        $this->db->select('ss.*, a.name as alumni_name, a.nim, p.name as prodi_name');
        $this->db->from($this->table . ' ss');
        $this->db->join('alumni a', 'ss.alumni_id = a.id');
        $this->db->join('prodis p', 'ss.prodi_id = p.id');
        $this->db->where('ss.stakeholder_id', $stakeholder_id);
        
        if ($status) {
            $this->db->where('ss.status', $status);
        }
        
        $this->db->order_by('ss.submitted_at', 'DESC');
        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Get surveys by prodi ID
     * 
     * @param int $prodi_id
     * @param string $year
     * @param string $status
     * @return array
     */
    public function getByProdi($prodi_id, $year = null, $status = null)
    {
        $this->db->select('ss.*, a.name as alumni_name, a.nim, a.graduation_year, 
                          sh.company_name, sh.contact_person as stakeholder_name');
        $this->db->from($this->table . ' ss');
        $this->db->join('alumni a', 'ss.alumni_id = a.id');
        $this->db->join('stakeholders sh', 'ss.stakeholder_id = sh.id');
        $this->db->where('ss.prodi_id', $prodi_id);
        
        if ($year) {
            $this->db->where('YEAR(ss.submitted_at)', $year);
        }
        
        if ($status) {
            $this->db->where('ss.status', $status);
        }
        
        $this->db->order_by('ss.submitted_at', 'DESC');
        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Get survey by alumni and stakeholder
     * 
     * @param int $alumni_id
     * @param int $stakeholder_id
     * @param string $status
     * @return array|null
     */
    public function getByAlumniAndStakeholder($alumni_id, $stakeholder_id, $status = null)
    {
        $this->db->where('alumni_id', $alumni_id);
        $this->db->where('stakeholder_id', $stakeholder_id);
        
        if ($status) {
            $this->db->where('status', $status);
        }
        
        $query = $this->db->get($this->table);
        return $query->row_array();
    }

    /**
     * Count surveys by stakeholder
     * 
     * @param int $stakeholder_id
     * @param string $status
     * @return int
     */
    public function countByStakeholder($stakeholder_id, $status = null)
    {
        $this->db->where('stakeholder_id', $stakeholder_id);
        
        if ($status) {
            $this->db->where('status', $status);
        }
        
        return $this->db->count_all_results($this->table);
    }

    /**
     * Count unique alumni assessed by stakeholder
     * 
     * @param int $stakeholder_id
     * @return int
     */
    public function countUniqueAlumni($stakeholder_id)
    {
        $this->db->select('DISTINCT alumni_id');
        $this->db->where('stakeholder_id', $stakeholder_id);
        $query = $this->db->get($this->table);
        return $query->num_rows();
    }

    /**
     * Get recent surveys by stakeholder
     * 
     * @param int $stakeholder_id
     * @param int $limit
     * @return array
     */
    public function getRecentByStakeholder($stakeholder_id, $limit = 5)
    {
        $this->db->select('ss.*, a.name as alumni_name, sh.company_name');
        $this->db->from($this->table . ' ss');
        $this->db->join('alumni a', 'ss.alumni_id = a.id');
        $this->db->join('stakeholders sh', 'ss.stakeholder_id = sh.id');
        $this->db->where('ss.stakeholder_id', $stakeholder_id);
        $this->db->order_by('ss.submitted_at', 'DESC');
        $this->db->limit($limit);
        
        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Get average rating by CPL for a prodi
     * 
     * @param int $prodi_id
     * @return array
     */
    public function getAverageRatingByCpl($prodi_id)
    {
        $this->db->select('scr.cpl_id, AVG(scr.rating) as average_rating, COUNT(*) as total_responses');
        $this->db->from($this->table_cpl . ' scr');
        $this->db->join($this->table . ' ss', 'scr.survey_id = ss.id');
        $this->db->where('ss.prodi_id', $prodi_id);
        $this->db->where('ss.status', 'completed');
        $this->db->group_by('scr.cpl_id');
        
        $query = $this->db->get();
        
        $result = [];
        foreach ($query->result_array() as $row) {
            $result[$row['cpl_id']] = $row['average_rating'];
        }
        return $result;
    }

    /**
     * Get summary statistics by alumni
     * 
     * @param int $alumni_id
     * @return array
     */
    public function getSummaryByAlumni($alumni_id)
    {
        $this->db->select('
            COUNT(*) as total_surveys,
            AVG(average_rating) as avg_rating,
            MIN(average_rating) as min_rating,
            MAX(average_rating) as max_rating,
            COUNT(DISTINCT stakeholder_id) as total_stakeholders
        ');
        $this->db->where('alumni_id', $alumni_id);
        $this->db->where('status', 'completed');
        
        $query = $this->db->get($this->table);
        return $query->row_array();
    }

    /**
     * Get summary statistics by prodi
     * 
     * @param int $prodi_id
     * @param string $year
     * @return array
     */
    public function getSummaryByProdi($prodi_id, $year = null)
    {
        $this->db->select('
            COUNT(*) as total_surveys,
            AVG(average_rating) as avg_rating,
            MIN(average_rating) as min_rating,
            MAX(average_rating) as max_rating,
            COUNT(DISTINCT ss.stakeholder_id) as total_stakeholders,
            COUNT(DISTINCT ss.alumni_id) as total_alumni
        ');
        $this->db->from($this->table . ' ss');
        $this->db->where('ss.prodi_id', $prodi_id);
        $this->db->where('ss.status', 'completed');
        
        if ($year) {
            $this->db->where('YEAR(ss.submitted_at)', $year);
        }
        
        $query = $this->db->get();
        return $query->row_array();
    }

    /**
     * Get recommendations summary by prodi
     * 
     * @param int $prodi_id
     * @return array
     */
    public function getRecommendationsSummary($prodi_id)
    {
        $this->db->select('recommended_competencies, curriculum_suggestions');
        $this->db->where('prodi_id', $prodi_id);
        $this->db->where('status', 'completed');
        $this->db->where('recommended_competencies IS NOT NULL', null, FALSE);
        
        $query = $this->db->get($this->table);
        return $query->result_array();
    }

    /**
     * Create invitation for stakeholder
     * 
     * @param array $data
     * @return int Insert ID
     */
    public function createInvitation($data)
    {
        $this->db->insert($this->table_invitations, $data);
        return $this->db->insert_id();
    }

    /**
     * Update invitation
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateInvitation($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->table_invitations, $data);
    }

    /**
     * Get invitation
     * 
     * @param int $alumni_id
     * @param int $stakeholder_id
     * @return array|null
     */
    public function getInvitation($alumni_id, $stakeholder_id)
    {
        $this->db->where('alumni_id', $alumni_id);
        $this->db->where('stakeholder_id', $stakeholder_id);
        $this->db->order_by('created_at', 'DESC');
        
        $query = $this->db->get($this->table_invitations);
        return $query->row_array();
    }

    /**
     * Get pending invitations for stakeholder
     * 
     * @param int $stakeholder_id
     * @return array
     */
    public function getPendingInvitations($stakeholder_id)
    {
        $this->db->select('ssi.*, a.name as alumni_name, a.nim, p.name as prodi_name');
        $this->db->from($this->table_invitations . ' ssi');
        $this->db->join('alumni a', 'ssi.alumni_id = a.id');
        $this->db->join('prodis p', 'ssi.prodi_id = p.id');
        $this->db->where('ssi.stakeholder_id', $stakeholder_id);
        $this->db->where('ssi.status', 'sent');
        $this->db->order_by('ssi.sent_at', 'DESC');
        
        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Get CPL kesesuaian distribution
     * 
     * @param int $prodi_id
     * @return array
     */
    public function getKesesuaianDistribution($prodi_id)
    {
        $this->db->select('scr.kesesuaian, COUNT(*) as total');
        $this->db->from($this->table_cpl . ' scr');
        $this->db->join($this->table . ' ss', 'scr.survey_id = ss.id');
        $this->db->where('ss.prodi_id', $prodi_id);
        $this->db->where('ss.status', 'completed');
        $this->db->where('scr.kesesuaian IS NOT NULL');
        $this->db->group_by('scr.kesesuaian');
        
        $query = $this->db->get();
        
        $result = [];
        foreach ($query->result_array() as $row) {
            $result[$row['kesesuaian']] = $row['total'];
        }
        return $result;
    }

    /**
     * Calculate combined average rating (BR-SUR-007: 60:40 ratio)
     * 
     * @param int $prodi_id
     * @param int $cpl_id
     * @return float
     */
    public function getCombinedAverageRating($prodi_id, $cpl_id)
    {
        // Get stakeholder average
        $stakeholder_avg = $this->getAverageRatingByCpl($prodi_id);
        $stakeholder_avg = isset($stakeholder_avg[$cpl_id]) ? $stakeholder_avg[$cpl_id] : 0;
        
        // Get alumni average (from Alumni_survey_model)
        $CI =& get_instance();
        $CI->load->model('Alumni_survey_model');
        $alumni_avg = $CI->Alumni_survey_model->getAverageRatingByCpl($prodi_id);
        $alumni_avg = isset($alumni_avg[$cpl_id]) ? $alumni_avg[$cpl_id] : 0;
        
        // BR-SUR-007: 60% stakeholder + 40% alumni
        return ($stakeholder_avg * 0.6) + ($alumni_avg * 0.4);
    }

    /**
     * Export data for reporting
     * 
     * @param int $prodi_id
     * @param string $year
     * @return array
     */
    public function exportData($prodi_id, $year = null)
    {
        $this->db->select('
            ss.*,
            a.name as alumni_name,
            a.nim,
            a.graduation_year,
            sh.company_name,
            sh.contact_person as stakeholder_name,
            sh.industry
        ');
        $this->db->from($this->table . ' ss');
        $this->db->join('alumni a', 'ss.alumni_id = a.id');
        $this->db->join('stakeholders sh', 'ss.stakeholder_id = sh.id');
        $this->db->where('ss.prodi_id', $prodi_id);
        $this->db->where('ss.status', 'completed');
        
        if ($year) {
            $this->db->where('YEAR(ss.submitted_at)', $year);
        }
        
        $this->db->order_by('ss.submitted_at', 'DESC');
        $query = $this->db->get();
        return $query->result_array();
    }
}
