<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * JobQueue Library
 * 
 * Handles background job processing with database queue
 * Supports retry logic and dead letter queue for failed jobs
 */
class JobQueue {
    
    protected $CI;
    protected $max_attempts = 3;
    
    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->database();
        log_message('info', 'JobQueue Library Initialized');
    }
    
    /**
     * Add a job to the queue
     * 
     * @param string $queue Queue name
     * @param array $payload Job data
     * @param int $delay Delay in seconds before job is available (default: 0)
     * @return int Job ID
     */
    public function push($queue, $payload, $delay = 0)
    {
        $data = [
            'queue' => $queue,
            'payload' => json_encode($payload),
            'attempts' => 0,
            'available_at' => date('Y-m-d H:i:s', time() + $delay),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->CI->db->insert('jobs', $data);
        $job_id = $this->CI->db->insert_id();
        
        log_message('info', "Job {$job_id} added to queue '{$queue}'");
        
        return $job_id;
    }
    
    /**
     * Get the next available job from the queue
     * 
     * @param string $queue Queue name
     * @return object|null Job object or null if no jobs available
     */
    public function pop($queue)
    {
        $now = date('Y-m-d H:i:s');
        
        $this->CI->db->where('queue', $queue);
        $this->CI->db->where('attempts <', $this->max_attempts);
        $this->CI->db->where('available_at <=', $now);
        $this->CI->db->where('reserved_at IS NULL');
        $this->CI->db->order_by('created_at', 'ASC');
        $this->CI->db->limit(1);
        
        $query = $this->CI->db->get('jobs');
        
        if ($query->num_rows() === 0) {
            return null;
        }
        
        $job = $query->row();
        
        // Mark job as reserved
        $this->CI->db->where('id', $job->id);
        $this->CI->db->update('jobs', [
            'reserved_at' => date('Y-m-d H:i:s')
        ]);
        
        return $job;
    }
    
    /**
     * Process a job
     * 
     * @param int $job_id Job ID
     * @param callable $handler Function to process the job
     * @return bool Success status
     */
    public function process($job_id, $handler)
    {
        $this->CI->db->where('id', $job_id);
        $query = $this->CI->db->get('jobs');
        
        if ($query->num_rows() === 0) {
            log_message('error', "Job {$job_id} not found");
            return false;
        }
        
        $job = $query->row();
        $payload = json_decode($job->payload, true);
        
        try {
            // Execute the handler
            call_user_func($handler, $payload, $job_id);
            
            // Job completed successfully - remove from queue
            $this->CI->db->where('id', $job_id);
            $this->CI->db->delete('jobs');
            
            log_message('info', "Job {$job_id} completed successfully");
            return true;
            
        } catch (Exception $e) {
            // Job failed
            return $this->fail($job_id, $e);
        }
    }
    
    /**
     * Mark a job as failed
     * 
     * @param int $job_id Job ID
     * @param Exception $exception The exception that caused the failure
     * @return bool Will retry or move to dead letter queue
     */
    public function fail($job_id, $exception)
    {
        $this->CI->db->where('id', $job_id);
        $query = $this->CI->db->get('jobs');
        
        if ($query->num_rows() === 0) {
            return false;
        }
        
        $job = $query->row();
        $new_attempts = $job->attempts + 1;
        
        log_message('error', "Job {$job_id} failed (attempt {$new_attempts}/{$this->max_attempts}): " . $exception->getMessage());
        
        if ($new_attempts >= $this->max_attempts) {
            // Move to dead letter queue
            $this->CI->db->insert('failed_jobs', [
                'job_id' => $job_id,
                'queue' => $job->queue,
                'payload' => $job->payload,
                'exception' => $exception->getMessage(),
                'failed_at' => date('Y-m-d H:i:s')
            ]);
            
            // Remove from active jobs
            $this->CI->db->where('id', $job_id);
            $this->CI->db->delete('jobs');
            
            log_message('error', "Job {$job_id} moved to dead letter queue after {$this->max_attempts} attempts");
            
            // Send alert email if configured
            $this->_send_failure_alert($job_id, $job->queue, $exception);
            
            return false;
        } else {
            // Schedule retry with exponential backoff
            $delay = pow(2, $new_attempts) * 60; // 2, 4, 8 minutes
            $this->CI->db->where('id', $job_id);
            $this->CI->db->update('jobs', [
                'attempts' => $new_attempts,
                'reserved_at' => null,
                'available_at' => date('Y-m-d H:i:s', time() + $delay)
            ]);
            
            log_message('info', "Job {$job_id} scheduled for retry in {$delay} seconds");
            return true;
        }
    }
    
    /**
     * Retry a failed job manually
     * 
     * @param int $job_id Job ID from failed_jobs table
     * @return bool Success status
     */
    public function retry($job_id)
    {
        $this->CI->db->where('job_id', $job_id);
        $query = $this->CI->db->get('failed_jobs');
        
        if ($query->num_rows() === 0) {
            log_message('error', "Failed job {$job_id} not found");
            return false;
        }
        
        $failed_job = $query->row();
        
        // Re-add to active jobs queue
        $new_job_id = $this->push($failed_job->queue, json_decode($failed_job->payload, true));
        
        // Remove from failed jobs
        $this->CI->db->where('job_id', $job_id);
        $this->CI->db->delete('failed_jobs');
        
        log_message('info', "Failed job {$job_id} re-queued as job {$new_job_id}");
        
        return true;
    }
    
    /**
     * Get queue statistics
     * 
     * @param string|null $queue Specific queue or null for all
     * @return array Statistics
     */
    public function stats($queue = null)
    {
        $stats = [];
        
        if ($queue) {
            $queues = [$queue];
        } else {
            $this->CI->db->select('DISTINCT queue');
            $queues_query = $this->CI->db->get('jobs');
            $queues = $queues_query->result_array();
            $queues = array_column($queues, 'queue');
        }
        
        foreach ($queues as $q) {
            $this->CI->db->where('queue', $q);
            $stats[$q]['pending'] = $this->CI->db->count_all_results('jobs');
            
            $this->CI->db->where('queue', $q);
            $this->CI->db->where('reserved_at IS NOT NULL');
            $stats[$q]['processing'] = $this->CI->db->count_all_results('jobs');
        }
        
        // Failed jobs count
        $stats['failed_total'] = $this->CI->db->count_all('failed_jobs');
        
        return $stats;
    }
    
    /**
     * Clear completed/old jobs
     * 
     * @param int $older_than Days old
     * @return int Number of rows affected
     */
    public function cleanup($older_than = 7)
    {
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$older_than} days"));
        
        // Note: Completed jobs are already deleted, this is for safety
        $this->CI->db->where('created_at <', $cutoff);
        $deleted = $this->CI->db->delete('jobs');
        
        return $this->CI->db->affected_rows();
    }
    
    /**
     * Send failure alert email
     * 
     * @param int $job_id Job ID
     * @param string $queue Queue name
     * @param Exception $exception Exception details
     */
    private function _send_failure_alert($job_id, $queue, $exception)
    {
        $admin_email = $this->CI->config->item('admin_email');
        
        if (empty($admin_email)) {
            return;
        }
        
        $this->CI->load->library('email');
        
        $subject = "[ALERT] Job {$job_id} Failed After {$this->max_attempts} Attempts";
        $message = "Job ID: {$job_id}\n";
        $message .= "Queue: {$queue}\n";
        $message .= "Time: " . date('Y-m-d H:i:s') . "\n";
        $message .= "Error: " . $exception->getMessage() . "\n";
        $message .= "\nPlease investigate and fix the issue.";
        
        $this->CI->email->from('noreply@yoursite.com', 'Job Queue System');
        $this->CI->email->to($admin_email);
        $this->CI->email->subject($subject);
        $this->CI->email->message($message);
        $this->CI->email->send();
    }
}
