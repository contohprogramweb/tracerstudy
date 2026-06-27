<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CLI Controller untuk Export Belmawa
 * Digunakan untuk background processing via cron job
 */
class Export_cli extends CI_Controller {

    public function __construct() {
        parent::__construct();
        
        // Hanya bisa diakses via CLI
        if (!$this->input->is_cli_request()) {
            show_error('Script ini hanya dapat dijalankan melalui command line', 403);
        }
        
        $this->load->library('BelmawaTemplate');
        $this->load->model('iku/job_model');
    }

    /**
     * Process semua pending export jobs
     * Usage: php index.php export_cli run_all_pending
     */
    public function run_all_pending() {
        echo "[" . date('Y-m-d H:i:s') . "] Starting export queue processor...\n";
        
        $pending_jobs = $this->db
            ->where('status', 'pending')
            ->where('job_type', 'export_belmawa')
            ->order_by('created_at', 'ASC')
            ->get('jobs')
            ->result();
        
        if (empty($pending_jobs)) {
            echo "No pending jobs found.\n";
            return;
        }
        
        echo "Found " . count($pending_jobs) . " pending job(s).\n";
        
        $success_count = 0;
        $fail_count = 0;
        
        foreach ($pending_jobs as $job) {
            echo "\nProcessing Job ID: {$job->id}\n";
            echo "  Kohort: {$job->kohort_id}, Prodi: {$job->prodi_id}\n";
            
            // Update status to processing
            $this->db->update('jobs', ['status' => 'processing'], ['id' => $job->id]);
            
            try {
                $result = $this->BelmawaTemplate->generate($job->kohort_id, $job->prodi_id);
                
                if ($result['status']) {
                    $this->db->update('jobs', [
                        'status' => 'completed',
                        'filename' => $result['file'],
                        'result_message' => 'Export berhasil',
                        'completed_at' => date('Y-m-d H:i:s')
                    ], ['id' => $job->id]);
                    
                    echo "  Status: SUCCESS - File: {$result['file']}\n";
                    $success_count++;
                    
                    // Send notification email
                    $this->_sendNotificationEmail($result['file']);
                } else {
                    $this->db->update('jobs', [
                        'status' => 'failed',
                        'result_message' => $result['message'],
                        'completed_at' => date('Y-m-d H:i:s')
                    ], ['id' => $job->id]);
                    
                    echo "  Status: FAILED - " . $result['message'] . "\n";
                    $fail_count++;
                }
            } catch (Exception $e) {
                $this->db->update('jobs', [
                    'status' => 'failed',
                    'result_message' => 'Exception: ' . $e->getMessage(),
                    'completed_at' => date('Y-m-d H:i:s')
                ], ['id' => $job->id]);
                
                echo "  Status: EXCEPTION - " . $e->getMessage() . "\n";
                $fail_count++;
            }
        }
        
        echo "\n[" . date('Y-m-d H:i:s') . "] Queue processing completed.\n";
        echo "Success: $success_count, Failed: $fail_count\n";
    }

    /**
     * Retry failed jobs
     * Usage: php index.php export_cli retry_failed
     */
    public function retry_failed() {
        echo "[" . date('Y-m-d H:i:s') . "] Retrying failed jobs...\n";
        
        $failed_jobs = $this->db
            ->where('status', 'failed')
            ->where('job_type', 'export_belmawa')
            ->order_by('completed_at', 'ASC')
            ->limit(10) // Max 10 retries per run
            ->get('jobs')
            ->result();
        
        if (empty($failed_jobs)) {
            echo "No failed jobs to retry.\n";
            return;
        }
        
        echo "Found " . count($failed_jobs) . " failed job(s) to retry.\n";
        
        foreach ($failed_jobs as $job) {
            echo "\nRetrying Job ID: {$job->id}\n";
            
            // Reset to pending
            $this->db->update('jobs', [
                'status' => 'pending',
                'result_message' => null,
                'retry_count' => ($job->retry_count ?? 0) + 1
            ], ['id' => $job->id]);
        }
        
        echo "Jobs reset to pending. Run 'run_all_pending' to process.\n";
    }

    /**
     * Cleanup old export files
     * Usage: php index.php export_cli cleanup_old_files [days]
     */
    public function cleanup_old_files($days = 30) {
        echo "[" . date('Y-m-d H:i:s') . "] Cleaning up export files older than $days days...\n";
        
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-$days days"));
        
        $old_jobs = $this->db
            ->where('status', 'completed')
            ->where('job_type', 'export_belmawa')
            ->where('completed_at <', $cutoff_date)
            ->get('jobs')
            ->result();
        
        $deleted_count = 0;
        
        foreach ($old_jobs as $job) {
            if (!empty($job->filename)) {
                $filepath = FCPATH . 'uploads/exports/' . $job->filename;
                
                if (file_exists($filepath)) {
                    if (unlink($filepath)) {
                        echo "  Deleted: {$job->filename}\n";
                        $deleted_count++;
                    }
                }
            }
        }
        
        echo "Deleted $deleted_count file(s).\n";
    }

    // --- Private Helpers ---

    private function _sendNotificationEmail($filename) {
        $email_admin = $this->config->item('admin_email');
        if (!$email_admin) {
            $email_admin = 'admin@university.ac.id';
        }

        $subject = "Export Belmawa Selesai: $filename";
        $message = "File export Belmawa telah siap diunduh.<br>Filename: $filename<br><br>Silakan login ke sistem untuk mengunduh.";
        
        $this->load->library('email');
        $this->email->from('no-reply@university.ac.id', 'Sistem Tracer Study');
        $this->email->to($email_admin);
        $this->email->subject($subject);
        $this->email->message($message);
        
        if ($this->email->send()) {
            echo "  Notification email sent to $email_admin\n";
        } else {
            echo "  Failed to send notification email\n";
        }
    }
}
