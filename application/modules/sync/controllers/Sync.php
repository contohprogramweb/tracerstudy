<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Sync Controller
 * 
 * Handles PDDikti synchronization via Web and CLI
 * Supports both web interface triggers and cron job execution
 * 
 * Usage:
 * - Web: /sync/pddikti/2024/PRODI_ID
 * - CLI: php index.php sync cliPddikti 2024 PRODI_ID
 * - Status: /sync/status/JOB_ID
 */
class Sync extends CI_Controller {
    
    protected $sync_library;
    protected $job_model;
    
    public function __construct() {
        parent::__construct();
        
        // Load required models and libraries
        $this->load->model('sync/sync_job_model');
        $this->load->library('pddikti_sync');
        $this->load->helper(['date', 'text']);
        
        // Check if running in CLI mode
        $this->is_cli = is_cli();
        
        // For web requests, check authentication
        if (!$this->is_cli) {
            // Uncomment in production:
            // if (!$this->session->userdata('logged_in')) {
            //     redirect('auth/login');
            // }
            // 
            // // Check admin role
            // if ($this->session->userdata('role') !== 'admin') {
            //     show_error('Unauthorized access', 403);
            // }
        }
    }
    
    /**
     * Trigger PDDikti sync via web interface
     * 
     * URL: /sync/pddikti/[tahun_lulus]/[prodi_id]
     * 
     * @param int|null $tahun_lulus Year to sync
     * @param string|null $prodi_id Study program ID
     * @return void JSON response or redirect
     */
    public function pddikti($tahun_lulus = null, $prodi_id = null) {
        if ($this->is_cli) {
            show_error('This method is for web requests only. Use cliPddikti for CLI.', 400);
        }
        
        // Set defaults
        if (!$tahun_lulus) {
            $tahun_lulus = $this->config->item('default_tahun_lulus') ?: date('Y');
        }
        
        // Validate tahun_lulus
        if (!is_numeric($tahun_lulus) || $tahun_lulus < 2000 || $tahun_lulus > date('Y')) {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false,
                    'message' => 'Invalid tahun_lulus parameter'
                ]));
            return;
        }
        
        // Create sync job
        $payload = [
            'tahun_lulus' => (int)$tahun_lulus,
            'prodi_id' => $prodi_id,
            'triggered_by' => 'web',
            'user_id' => $this->session->userdata('user_id') ?? null
        ];
        
        $job_id = $this->sync_job_model->createJob('pddikti', $payload);
        
        if (!$job_id) {
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false,
                    'message' => 'Failed to create sync job'
                ]));
            return;
        }
        
        // Process job immediately for small batches, queue for large
        $config = $this->config->item('pddikti');
        
        if ($config['use_queue']) {
            // Queue the job for background processing
            $this->sync_job_model->updateJob($job_id, 'queued');
            
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => true,
                    'message' => 'Sync job queued successfully',
                    'job_id' => $job_id,
                    'status' => 'queued',
                    'note' => 'Job will be processed in background'
                ]));
        } else {
            // Process immediately
            $result = $this->processSyncJob($job_id);
            
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($result));
        }
    }
    
    /**
     * Trigger PDDikti sync via CLI (for cron jobs)
     * 
     * CLI: php index.php sync cliPddikti [tahun_lulus] [prodi_id]
     * 
     * Expected to be called by cron:
     * 0 1 * * * cd /path/to/app && php index.php sync cliPddikti
     */
    public function cliPddikti() {
        if (!$this->is_cli) {
            show_error('This method is for CLI execution only.', 400);
        }
        
        echo "===========================================\n";
        echo "PDDikti Sync - CLI Execution\n";
        echo "Started: " . date('Y-m-d H:i:s') . "\n";
        echo "===========================================\n\n";
        
        // Get parameters from CLI
        $tahun_lulus = $this->input->cli('tahun_lulus');
        $prodi_id = $this->input->cli('prodi_id');
        
        // If no parameters, sync default year
        if (!$tahun_lulus) {
            $config = $this->config->item('pddikti');
            $tahun_lulus = $config['default_tahun_lulus'] ?? date('Y');
            echo "Using default tahun_lulus: {$tahun_lulus}\n";
        }
        
        echo "Parameters:\n";
        echo "  - Tahun Lulus: {$tahun_lulus}\n";
        echo "  - Prodi ID: " . ($prodi_id ?? 'All') . "\n\n";
        
        // Create sync job
        $payload = [
            'tahun_lulus' => (int)$tahun_lulus,
            'prodi_id' => $prodi_id,
            'triggered_by' => 'cli',
            'cron_schedule' => $this->config->item('pddikti')['sync_schedule'] ?? null
        ];
        
        $job_id = $this->sync_job_model->createJob('pddikti', $payload);
        
        if (!$job_id) {
            echo "ERROR: Failed to create sync job\n";
            exit(1);
        }
        
        echo "Job created with ID: {$job_id}\n\n";
        
        // Process the job
        $result = $this->processSyncJob($job_id);
        
        // Output results
        echo "\n===========================================\n";
        echo "Sync Results:\n";
        echo "===========================================\n";
        echo "Status: " . strtoupper($result['status']) . "\n";
        echo "Fetched: {$result['fetched']} records\n";
        echo "Inserted: {$result['inserted']} new alumni\n";
        echo "Updated: {$result['updated']} existing alumni\n";
        echo "Skipped: {$result['skipped']} inactive alumni\n";
        echo "Failed: {$result['failed']} records\n";
        
        if (!empty($result['errors'])) {
            echo "\nErrors:\n";
            foreach ($result['errors'] as $error) {
                echo "  - NIM: {$error['nim'] ?? 'unknown'} | ";
                echo "Error: " . (is_array($error['errors']) ? implode(', ', $error['errors']) : $error['error']) . "\n";
            }
        }
        
        echo "\n===========================================\n";
        echo "Completed: " . date('Y-m-d H:i:s') . "\n";
        echo "Duration: {$result['duration']} seconds\n";
        echo "===========================================\n";
        
        // Exit with error code if there were failures
        if ($result['status'] === 'failed' || $result['failed'] > 0) {
            exit(1);
        }
        
        exit(0);
    }
    
    /**
     * Check sync job status
     * 
     * URL: /sync/status/[job_id]
     * 
     * @param int $job_id Job ID
     * @return void JSON response
     */
    public function status($job_id) {
        if ($this->is_cli) {
            show_error('This method is for web requests only.', 400);
        }
        
        $job = $this->sync_job_model->getJob($job_id);
        
        if (!$job) {
            $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false,
                    'message' => 'Job not found'
                ]));
            return;
        }
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'job' => $job
            ]));
    }
    
    /**
     * List all sync jobs
     * 
     * URL: /sync/jobs/[page]/[per_page]
     * 
     * @param int $page Page number
     * @param int $per_page Items per page
     * @return void JSON response or view
     */
    public function jobs($page = 1, $per_page = 20) {
        if ($this->is_cli) {
            show_error('This method is for web requests only.', 400);
        }
        
        $jobs = $this->sync_job_model->getJobs($page, $per_page);
        $total = $this->sync_job_model->countJobs();
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'jobs' => $jobs,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $per_page,
                    'total' => $total,
                    'total_pages' => ceil($total / $per_page)
                ]
            ]));
    }
    
    /**
     * Retry a failed sync job
     * 
     * URL: /sync/retry/[job_id]
     * 
     * @param int $job_id Job ID
     * @return void JSON response
     */
    public function retry($job_id) {
        if ($this->is_cli) {
            show_error('This method is for web requests only.', 400);
        }
        
        $job = $this->sync_job_model->getJob($job_id);
        
        if (!$job) {
            $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false,
                    'message' => 'Job not found'
                ]));
            return;
        }
        
        if ($job['status'] !== 'failed') {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false,
                    'message' => 'Only failed jobs can be retried'
                ]));
            return;
        }
        
        // Check max retries
        $config = $this->config->item('sync_jobs');
        if ($job['retry_count'] >= $config['max_job_retries']) {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false,
                    'message' => 'Maximum retry attempts reached'
                ]));
            return;
        }
        
        // Reset job status and increment retry count
        $this->sync_job_model->updateJob($job_id, 'pending', null, $job['retry_count'] + 1);
        
        // Process the job
        $result = $this->processSyncJob($job_id);
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'message' => 'Job retried successfully',
                'result' => $result
            ]));
    }
    
    /**
     * Cancel a running or queued sync job
     * 
     * URL: /sync/cancel/[job_id]
     * 
     * @param int $job_id Job ID
     * @return void JSON response
     */
    public function cancel($job_id) {
        if ($this->is_cli) {
            show_error('This method is for web requests only.', 400);
        }
        
        $job = $this->sync_job_model->getJob($job_id);
        
        if (!$job) {
            $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false,
                    'message' => 'Job not found'
                ]));
            return;
        }
        
        if (!in_array($job['status'], ['pending', 'queued', 'processing'])) {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false,
                    'message' => 'Cannot cancel job with status: ' . $job['status']
                ]));
            return;
        }
        
        $this->sync_job_model->updateJob($job_id, 'cancelled');
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'message' => 'Job cancelled successfully'
            ]));
    }
    
    /**
     * Process a sync job
     * 
     * @param int $job_id Job ID to process
     * @return array Processing result
     */
    protected function processSyncJob($job_id) {
        $start_time = microtime(true);
        
        // Get job details
        $job = $this->sync_job_model->getJob($job_id);
        
        if (!$job) {
            return [
                'success' => false,
                'status' => 'failed',
                'message' => 'Job not found'
            ];
        }
        
        // Update job status to processing
        $this->sync_job_model->updateJob($job_id, 'processing');
        
        try {
            // Initialize PDDikti sync library
            $config = $this->config->item('pddikti');
            $this->pddikti_sync->connect($config);
            
            if (!$this->pddikti_sync->isConnected()) {
                throw new Exception("Failed to connect to PDDikti API: " . $this->pddikti_sync->getLastError());
            }
            
            // Execute sync batch
            $result = $this->pddikti_sync->syncBatch(
                $job['payload']['tahun_lulus'],
                $job['payload']['prodi_id'] ?? null
            );
            
            // Determine final status
            if ($result['failed'] > 0 && $result['fetched'] === 0) {
                $status = 'failed';
            } elseif ($result['failed'] > 0) {
                $status = 'partial';
            } else {
                $status = 'success';
            }
            
            // Calculate duration
            $duration = round(microtime(true) - $start_time, 2);
            
            // Update job with results
            $this->sync_job_model->updateJob($job_id, $status, $result);
            
            // Add duration to result
            $result['duration'] = $duration;
            $result['job_id'] = $job_id;
            $result['status'] = $status;
            
            // Send notification if failed
            if ($status === 'failed' && $config['notify_on_failure']) {
                $this->sendFailureNotification($job, $result);
            }
            
            return $result;
            
        } catch (Exception $e) {
            log_message('error', "Sync job {$job_id} failed: " . $e->getMessage());
            
            $error_result = [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ];
            
            $this->sync_job_model->updateJob($job_id, 'failed', $error_result);
            
            return [
                'success' => false,
                'status' => 'failed',
                'message' => $e->getMessage(),
                'job_id' => $job_id,
                'duration' => round(microtime(true) - $start_time, 2)
            ];
        }
    }
    
    /**
     * Send failure notification email
     * 
     * @param array $job Job details
     * @param array $result Sync result
     * @return void
     */
    protected function sendFailureNotification($job, $result) {
        $config = $this->config->item('pddikti');
        
        $subject = "[ALERT] PDDikti Sync Failed - Job #{$job['id']}";
        
        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .alert { color: #dc3545; }
                .stats { background: #f8f9fa; padding: 15px; margin: 10px 0; }
            </style>
        </head>
        <body>
            <h2 class='alert'>⚠️ PDDikti Synchronization Failed</h2>
            
            <p><strong>Job ID:</strong> {$job['id']}</p>
            <p><strong>Triggered By:</strong> {$job['triggered_by']}</p>
            <p><strong>Tahun Lulus:</strong> {$job['payload']['tahun_lulus']}</p>
            <p><strong>Prodi ID:</strong> " . ($job['payload']['prodi_id'] ?? 'All') . "</p>
            
            <div class='stats'>
                <h3>Statistics:</h3>
                <ul>
                    <li>Fetched: {$result['fetched']}</li>
                    <li>Inserted: {$result['inserted']}</li>
                    <li>Updated: {$result['updated']}</li>
                    <li>Skipped: {$result['skipped']}</li>
                    <li class='alert'>Failed: {$result['failed']}</li>
                </ul>
            </div>
            
            <p><strong>Error:</strong> " . ($result['error'] ?? 'Multiple errors occurred') . "</p>
            
            <p>Please check the logs and retry the sync job.</p>
            
            <hr>
            <small>This is an automated message from Alumni Management System</small>
        </body>
        </html>
        ";
        
        // Load email library and send
        $this->load->library('email');
        
        $this->email->from('noreply@university.ac.id', 'Alumni System');
        $this->email->to($config['notification_email']);
        $this->email->subject($subject);
        $this->email->message($message);
        $this->email->set_mailtype('html');
        
        if ($this->email->send()) {
            log_message('info', "Failure notification sent to {$config['notification_email']}");
        } else {
            log_message('error', "Failed to send failure notification: " . $this->email->print_debugger());
        }
    }
    
    /**
     * Cleanup old sync jobs (maintenance task)
     * 
     * CLI: php index.php sync cleanup
     * 
     * @return void
     */
    public function cleanup() {
        if (!$this->is_cli) {
            show_error('This method is for CLI execution only.', 400);
        }
        
        $config = $this->config->item('sync_jobs');
        $days = $config['cleanup_after_days'] ?? 30;
        
        echo "Cleaning up sync jobs older than {$days} days...\n";
        
        $deleted = $this->sync_job_model->cleanupOldJobs($days);
        
        echo "Deleted {$deleted} old jobs.\n";
        
        exit(0);
    }
}
