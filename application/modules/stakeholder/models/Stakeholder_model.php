<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model: Stakeholder_model
 * Mengelola data stakeholder/DUDI/Employer
 */
class Stakeholder_model extends CI_Model {

    private $table = 'stakeholders';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Insert new stakeholder
     * 
     * @param array $data
     * @return int Insert ID
     */
    public function insert($data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    /**
     * Get stakeholder by ID
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
     * Get stakeholder by user ID
     * 
     * @param int $user_id
     * @return array|null
     */
    public function getByUserId($user_id)
    {
        $query = $this->db->get_where($this->table, ['user_id' => $user_id]);
        return $query->row_array();
    }

    /**
     * Get stakeholder by verification token
     * 
     * @param string $token
     * @return array|null
     */
    public function getByToken($token)
    {
        $query = $this->db->get_where($this->table, ['verification_token' => $token]);
        return $query->row_array();
    }

    /**
     * Get stakeholder by email
     * 
     * @param string $email
     * @return array|null
     */
    public function getByEmail($email)
    {
        $query = $this->db->get_where($this->table, ['email' => $email]);
        return $query->row_array();
    }

    /**
     * Update stakeholder data
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
     * Delete stakeholder
     * 
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        return $this->db->delete($this->table, ['id' => $id]);
    }

    /**
     * Get all stakeholders with pagination
     * 
     * @param int $limit
     * @param int $offset
     * @param string $status
     * @return array
     */
    public function getAll($limit = 20, $offset = 0, $status = null)
    {
        if ($status) {
            $this->db->where('status', $status);
        }
        
        $this->db->order_by('created_at', 'DESC');
        $query = $this->db->get($this->table, $limit, $offset);
        return $query->result_array();
    }

    /**
     * Count total stakeholders
     * 
     * @param string $status
     * @return int
     */
    public function count($status = null)
    {
        if ($status) {
            $this->db->where('status', $status);
        }
        return $this->db->count_all_results($this->table);
    }

    /**
     * Get stakeholders by industry
     * 
     * @param string $industry
     * @return array
     */
    public function getByIndustry($industry)
    {
        $query = $this->db->get_where($this->table, ['industry' => $industry]);
        return $query->result_array();
    }

    /**
     * Get stakeholders by company type
     * 
     * @param string $type
     * @return array
     */
    public function getByCompanyType($type)
    {
        $query = $this->db->get_where($this->table, ['company_type' => $type]);
        return $query->result_array();
    }

    /**
     * Search stakeholders
     * 
     * @param string $keyword
     * @return array
     */
    public function search($keyword)
    {
        $this->db->group_start();
        $this->db->like('company_name', $keyword);
        $this->db->or_like('contact_person', $keyword);
        $this->db->or_like('email', $keyword);
        $this->db->or_like('industry', $keyword);
        $this->db->group_end();
        
        $query = $this->db->get($this->table);
        return $query->result_array();
    }

    /**
     * Get active stakeholders
     * 
     * @return array
     */
    public function getActive()
    {
        $query = $this->db->get_where($this->table, ['status' => 'active']);
        return $query->result_array();
    }

    /**
     * Get stakeholders for dropdown selection
     * 
     * @return array
     */
    public function getDropdown()
    {
        $this->db->select('id, company_name, contact_person');
        $this->db->where('status', 'active');
        $this->db->order_by('company_name', 'ASC');
        $query = $this->db->get($this->table);
        
        $result = [];
        foreach ($query->result_array() as $row) {
            $result[$row['id']] = $row['company_name'] . ' - ' . $row['contact_person'];
        }
        return $result;
    }

    /**
     * Get stakeholder statistics
     * 
     * @return array
     */
    public function getStatistics()
    {
        $stats = [];
        
        // Total by status
        $this->db->select('status, COUNT(*) as total');
        $this->db->group_by('status');
        $query = $this->db->get($this->table);
        foreach ($query->result_array() as $row) {
            $stats['by_status'][$row['status']] = $row['total'];
        }
        
        // Total by industry
        $this->db->select('industry, COUNT(*) as total');
        $this->db->group_by('industry');
        $this->db->order_by('total', 'DESC');
        $query = $this->db->get($this->table);
        foreach ($query->result_array() as $row) {
            $stats['by_industry'][$row['industry']] = $row['total'];
        }
        
        // Total by company type
        $this->db->select('company_type, COUNT(*) as total');
        $this->db->group_by('company_type');
        $query = $this->db->get($this->table);
        foreach ($query->result_array() as $row) {
            $stats['by_company_type'][$row['company_type']] = $row['total'];
        }
        
        // Recent registrations (last 30 days)
        $this->db->where('created_at >=', date('Y-m-d H:i:s', strtotime('-30 days')));
        $stats['recent_count'] = $this->db->count_all_results($this->table);
        
        return $stats;
    }

    /**
     * Link stakeholder to user account
     * 
     * @param int $stakeholder_id
     * @param int $user_id
     * @return bool
     */
    public function linkToUser($stakeholder_id, $user_id)
    {
        return $this->update($stakeholder_id, ['user_id' => $user_id]);
    }

    /**
     * Verify stakeholder email
     * 
     * @param string $token
     * @return bool
     */
    public function verifyEmail($token)
    {
        $stakeholder = $this->getByToken($token);
        
        if ($stakeholder) {
            return $this->update($stakeholder['id'], [
                'status' => 'active',
                'verified_at' => date('Y-m-d H:i:s'),
                'verification_token' => null
            ]);
        }
        
        return false;
    }
}
