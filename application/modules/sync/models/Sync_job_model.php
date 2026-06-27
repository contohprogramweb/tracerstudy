<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Sync Job Model
 * 
 * Manages synchronization jobs for PDDikti and other sync operations
 * Handles job creation, status updates, queue management, and cleanup
 */
class Sync_job_model extends MY_Model {
    
    protected $table_name = 'sync_jobs';
    protected $primary_key = 'id';
    protected $soft_keys = [];
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    
    /**
     * Create a new sync job
     * 
     * @param string $type Job type (e.g., 'pddikti', 'import', 'export')
     * @param array $payload Job parameters and data
     * @return int|false Job ID on success, false on failure
     */
    public function createJob($type, $payload) {
        $data = [
            'type' => $type,
            'status' => 'pending',
            'payload' => json_encode($payload),
            'retry_count' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($this->db->insert($this->table_name, $data)) {
            return $this->db->insert_id();
        }
        
        return false;
    }
    
    /**
     * Update job status and result
     * 
     * @param int $id Job ID
     * @param string $status New status (pending, queued, processing, success, partial, failed, cancelled)
     * @param array|null $result Job result data
     * @param int|null $retry_count Retry count
     * @return bool Success status
     */
    public function updateJob($id, $status, $result = null, $retry_count = null) {
        $data = [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($result !== null) {
            $data['result'] = json_encode($result);
        }
        
        if ($retry_count !== null) {
            $data['retry_count'] = $retry_count;
        }
        
        // Set completed_at if job is finished
        if (in_array($status, ['success', 'partial', 'failed', 'cancelled'])) {
            $data['completed_at'] = date('Y-m-d H:i:s');
        }
        
        // Set started_at if transitioning to processing
        if ($status === 'processing') {
            $data['started_at'] = date('Y-m-d H:i:s');
        }
        
        $this->db->where($this->primary_key, $id);
        return $this->db->update($this->table_name, $data);
    }
    
    /**
     * Get a single job by ID
     * 
     * @param int $id Job ID
     * @return array|null Job data or null if not found
     */
    public function getJob($id) {
        $query = $this->db->get_where($this->table_name, [$this->primary_key => $id]);
        $job = $query->row_array();
        
        if ($job) {
            // Decode JSON fields
            $job['payload'] = json_decode($job['payload'], true);
            $job['result'] = !empty($job['result']) ? json_decode($job['result'], true) : null;
        }
        
        return $job;
    }
    
    /**
     * Get pending jobs for processing
     * 
     * @param int $limit Maximum number of jobs to return
     * @return array Array of pending jobs
     */
    public function getPendingJobs($limit = 10) {
        $this->db->where('status', 'pending');
        $this->db->or_where('status', 'queued');
        $this->db->order_by('created_at', 'ASC');
        $this->db->limit($limit);
        
        $query = $this->db->get($this->table_name);
        $jobs = $query->result_array();
        
        // Decode JSON fields
        foreach ($jobs as &$job) {
            $job['payload'] = json_decode($job['payload'], true);
            $job['result'] = !empty($job['result']) ? json_decode($job['result'], true) : null;
        }
        
        return $jobs;
    }
    
    /**
     * Get all jobs with pagination
     * 
     * @param int $page Page number
     * @param int $per_page Items per page
     * @param string|null $type Filter by job type
     * @param string|null $status Filter by status
     * @return array Array of jobs
     */
    public function getJobs($page = 1, $per_page = 20, $type = null, $status = null) {
        $offset = ($page - 1) * $per_page;
        
        if ($type) {
            $this->db->where('type', $type);
        }
        
        if ($status) {
            $this->db->where('status', $status);
        }
        
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($per_page, $offset);
        
        $query = $this->db->get($this->table_name);
        $jobs = $query->result_array();
        
        // Decode JSON fields
        foreach ($jobs as &$job) {
            $job['payload'] = json_decode($job['payload'], true);
            $job['result'] = !empty($job['result']) ? json_decode($job['result'], true) : null;
        }
        
        return $jobs;
    }
    
    /**
     * Count total jobs
     * 
     * @param string|null $type Filter by job type
     * @param string|null $status Filter by status
     * @return int Total count
     */
    public function countJobs($type = null, $status = null) {
        if ($type) {
            $this->db->where('type', $type);
        }
        
        if ($status) {
            $this->db->where('status', $status);
        }
        
        return $this->db->count_all_results($this->table_name);
    }
    
    /**
     * Process a job (mark as processing)
     * 
     * @param int $id Job ID
     * @return bool Success status
     */
    public function processJob($id) {
        return $this->updateJob($id, 'processing');
    }
    
    /**
     * Get jobs that are stuck in processing (timeout handling)
     * 
     * @param int $timeout_seconds Timeout threshold in seconds
     * @return array Array of stuck jobs
     */
    public function getStuckJobs($timeout_seconds = 3600) {
        $threshold = date('Y-m-d H:i:s', time() - $timeout_seconds);
        
        $this->db->where('status', 'processing');
        $this->db->where('updated_at <', $threshold);
        
        $query = $this->db->get($this->table_name);
        $jobs = $query->result_array();
        
        // Decode JSON fields
        foreach ($jobs as &$job) {
            $job['payload'] = json_decode($job['payload'], true);
            $job['result'] = !empty($job['result']) ? json_decode($job['result'], true) : null;
        }
        
        return $jobs;
    }
    
    /**
     * Mark stuck jobs as failed
     * 
     * @param int $timeout_seconds Timeout threshold in seconds
     * @return int Number of jobs marked as failed
     */
    public function failStuckJobs($timeout_seconds = 3600) {
        $stuck_jobs = $this->getStuckJobs($timeout_seconds);
        $count = 0;
        
        foreach ($stuck_jobs as $job) {
            $error_result = [
                'error' => 'Job timeout - exceeded ' . $timeout_seconds . ' seconds',
                'auto_failed' => true
            ];
            
            if ($this->updateJob($job['id'], 'failed', $error_result)) {
                $count++;
                log_message('warning', "Auto-failed stuck job #{$job['id']}");
            }
        }
        
        return $count;
    }
    
    /**
     * Get statistics about sync jobs
     * 
     * @param string|null $type Filter by job type
     * @return array Statistics
     */
    public function getStats($type = null) {
        if ($type) {
            $this->db->where('type', $type);
        }
        
        // Total jobs
        $total = $this->db->count_all_results($this->table_name);
        
        // Jobs by status
        $this->db->select('status, COUNT(*) as count');
        if ($type) {
            $this->db->where('type', $type);
        }
        $this->db->group_by('status');
        $query = $this->db->get($this->table_name);
        $by_status = $query->result_array();
        
        // Recent jobs (last 24 hours)
        $this->db->where('created_at >=', date('Y-m-d H:i:s', strtotime('-24 hours')));
        if ($type) {
            $this->db->where('type', $type);
        }
        $recent_total = $this->db->count_all_results($this->table_name);
        
        // Success rate (last 7 days)
        $this->db->where('created_at >=', date('Y-m-d H:i:s', strtotime('-7 days')));
        $this->db->where('status', 'success');
        if ($type) {
            $this->db->where('type', $type);
        }
        $success_count = $this->db->count_all_results($this->table_name);
        
        $this->db->where('created_at >=', date('Y-m-d H:i:s', strtotime('-7 days')));
        $this->db->where_in('status', ['success', 'partial', 'failed']);
        if ($type) {
            $this->db->where('type', $type);
        }
        $completed_count = $this->db->count_all_results($this->table_name);
        
        $success_rate = $completed_count > 0 ? round(($success_count / $completed_count) * 100, 2) : 0;
        
        return [
            'total' => $total,
            'by_status' => $by_status,
            'recent_24h' => $recent_total,
            'success_rate_7d' => $success_rate
        ];
    }
    
    /**
     * Cleanup old completed jobs
     * 
     * @param int $days Days to keep (delete older than this)
     * @return int Number of deleted jobs
     */
    public function cleanupOldJobs($days = 30) {
        $threshold = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $this->db->where_in('status', ['success', 'partial', 'failed', 'cancelled']);
        $this->db->where('completed_at <', $threshold);
        
        // Get count before deleting
        $count = $this->db->count_all_results($this->table_name);
        
        if ($count > 0) {
            $this->db->delete($this->table_name);
            log_message('info', "Cleaned up {$count} old sync jobs");
        }
        
        return $count;
    }
    
    /**
     * Cancel all pending/queued jobs of a specific type
     * 
     * @param string $type Job type to cancel
     * @return int Number of cancelled jobs
     */
    public function cancelPendingJobs($type) {
        $this->db->where('type', $type);
        $this->db->where_in('status', ['pending', 'queued']);
        
        $count = $this->db->count_all_results($this->table_name);
        
        if ($count > 0) {
            $this->db->where('type', $type);
            $this->db->where_in('status', ['pending', 'queued']);
            $this->db->update($this->table_name, [
                'status' => 'cancelled',
                'updated_at' => date('Y-m-d H:i:s'),
                'completed_at' => date('Y-m-d H:i:s')
            ]);
            
            log_message('info', "Cancelled {$count} pending {$type} jobs");
        }
        
        return $count;
    }
    
    /**
     * Get last successful job of a type
     * 
     * @param string $type Job type
     * @return array|null Last successful job or null
     */
    public function getLastSuccessfulJob($type) {
        $this->db->where('type', $type);
        $this->db->where('status', 'success');
        $this->db->order_by('completed_at', 'DESC');
        $this->db->limit(1);
        
        $query = $this->db->get($this->table_name);
        $job = $query->row_array();
        
        if ($job) {
            $job['payload'] = json_decode($job['payload'], true);
            $job['result'] = !empty($job['result']) ? json_decode($job['result'], true) : null;
        }
        
        return $job;
    }
    
    /**
     * Check if a job is currently running for a specific type and payload
     * 
     * @param string $type Job type
     * @param array $payload Payload to check
     * @return bool True if running job exists
     */
    public function isJobRunning($type, $payload) {
        $payload_json = json_encode($payload);
        
        $this->db->where('type', $type);
        $this->db->where('payload', $payload_json);
        $this->db->where_in('status', ['pending', 'queued', 'processing']);
        
        return $this->db->count_all_results($this->table_name) > 0;
    }
    
    /**
     * Re-queue a failed job
     * 
     * @param int $id Job ID
     * @return bool Success status
     */
    public function requeueJob($id) {
        $job = $this->getJob($id);
        
        if (!$job || $job['status'] !== 'failed') {
            return false;
        }
        
        $config = $this->config->item('sync_jobs');
        
        if ($job['retry_count'] >= $config['max_job_retries']) {
            return false;
        }
        
        return $this->updateJob($id, 'pending', null, $job['retry_count'] + 1);
    }
}
