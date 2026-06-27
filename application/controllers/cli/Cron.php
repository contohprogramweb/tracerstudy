<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Cron Controller
 * 
 * CLI-only controller for handling scheduled cron jobs
 * All methods are designed to be called from command line only
 * 
 * Usage:
 * php index.php cli/cron/method_name [params]
 */
class Cron extends CI_Controller {
    
    protected $job_queue;
    
    public function __construct()
    {
        parent::__construct();
        
        // Ensure CLI access only
        if (!$this->input->is_cli_request()) {
            show_error('Cron jobs can only be accessed from CLI', 403);
        }
        
        $this->load->library('jobqueue');
        $this->load->model('survey_model');
        $this->load->model('user_model');
        $this->load->library('email');
        
        log_message('info', 'Cron job started: ' . $this->router->fetch_method());
    }
    
    /**
     * Calculate IKU (Indikator Kinerja Utama) metrics
     * 
     * @param int|null $kohort_id Specific kohort ID or null for all
     * @param int|null $prodi_id Specific program studi ID or null for all
     */
    public function iku_calculate($kohort_id = null, $prodi_id = null)
    {
        echo "Starting IKU calculation...\n";
        
        try {
            $this->db->trans_start();
            
            // Build query conditions
            $conditions = [];
            if ($kohort_id) {
                $conditions['kohort_id'] = $kohort_id;
            }
            if ($prodi_id) {
                $conditions['prodi_id'] = $prodi_id;
            }
            
            // Get all alumni for calculation
            $alumni = $this->survey_model->get_alumni($conditions);
            
            $calculated = 0;
            foreach ($alumni as $alum) {
                // Calculate IKU indicators
                $iku_data = $this->_calculate_alumni_iku($alum);
                
                // Save to database
                $this->survey_model->save_iku_result($alum->id, $iku_data);
                $calculated++;
            }
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                throw new Exception("Database transaction failed");
            }
            
            echo "IKU calculation completed. Processed {$calculated} alumni records.\n";
            log_message('info', "IKU calculation completed: {$calculated} records");
            
        } catch (Exception $e) {
            $this->_log_error('iku_calculate', $e);
            exit(1);
        }
    }
    
    /**
     * Send reminder notifications to users who haven't completed surveys
     */
    public function reminder()
    {
        echo "Starting reminder process...\n";
        
        try {
            // Get pending surveys
            $pending_surveys = $this->survey_model->get_pending_surveys();
            
            $sent_count = 0;
            foreach ($pending_surveys as $survey) {
                // Check if reminder already sent recently
                if ($this->_reminder_already_sent($survey->id)) {
                    continue;
                }
                
                // Send reminder via email
                $this->_send_reminder_email($survey);
                
                // Optionally send WhatsApp notification
                if (!empty($survey->whatsapp_number)) {
                    $this->_send_whatsapp_reminder($survey);
                }
                
                $sent_count++;
            }
            
            echo "Reminder process completed. Sent {$sent_count} reminders.\n";
            log_message('info', "Reminder process completed: {$sent_count} reminders sent");
            
        } catch (Exception $e) {
            $this->_log_error('reminder', $e);
            exit(1);
        }
    }
    
    /**
     * Sync data with PDDikti (Pangkalan Data Pendidikan Tinggi)
     * 
     * @param int|null $tahun_lulus Graduation year or null for current year
     * @param int|null $prodi_id Specific program studi ID or null for all
     */
    public function pddikti_sync($tahun_lulus = null, $prodi_id = null)
    {
        echo "Starting PDDikti sync...\n";
        
        try {
            if (!$tahun_lulus) {
                $tahun_lulus = date('Y');
            }
            
            // Fetch data from PDDikti API
            $pddikti_data = $this->_fetch_pddikti_data($tahun_lulus, $prodi_id);
            
            $synced_count = 0;
            foreach ($pddikti_data as $record) {
                // Match with local alumni record
                $alumni = $this->survey_model->find_by_nim($record['nim']);
                
                if ($alumni) {
                    // Update alumni data with PDDikti info
                    $this->survey_model->update_from_pddikti($alumni->id, $record);
                    $synced_count++;
                } else {
                    log_message('warning', "PDDikti record not matched: NIM {$record['nim']}");
                }
            }
            
            echo "PDDikti sync completed. Synced {$synced_count} records.\n";
            log_message('info', "PDDikti sync completed: {$synced_count} records");
            
        } catch (Exception $e) {
            $this->_log_error('pddikti_sync', $e);
            exit(1);
        }
    }
    
    /**
     * Process pending export jobs
     */
    public function export_processor()
    {
        echo "Starting export processor...\n";
        
        try {
            // Get pending export jobs from queue
            $job = $this->job_queue->pop('export');
            
            if (!$job) {
                echo "No pending export jobs.\n";
                return;
            }
            
            $payload = json_decode($job->payload, true);
            
            // Process the export
            $result = $this->_process_export($payload);
            
            // Mark job as complete
            $this->db->where('id', $job->id);
            $this->db->delete('jobs');
            
            echo "Export completed: {$result['filename']}\n";
            log_message('info', "Export processed: {$result['filename']}");
            
        } catch (Exception $e) {
            $this->job_queue->fail($job->id ?? 0, $e);
            $this->_log_error('export_processor', $e);
            exit(1);
        }
    }
    
    /**
     * Rotate API keys for security
     */
    public function api_key_rotate()
    {
        echo "Starting API key rotation...\n";
        
        try {
            // Get all active API keys older than 30 days
            $this->db->where('created_at <', date('Y-m-d H:i:s', strtotime('-30 days')));
            $old_keys = $this->db->get('api_keys')->result();
            
            $rotated_count = 0;
            foreach ($old_keys as $key) {
                // Generate new API key
                $new_key = bin2hex(random_bytes(32));
                
                // Update in database
                $this->db->where('id', $key->id);
                $this->db->update('api_keys', [
                    'api_key' => $new_key,
                    'created_at' => date('Y-m-d H:i:s'),
                    'previous_key' => $key->api_key // Keep for grace period
                ]);
                
                // Notify user about key rotation
                $this->_notify_key_rotation($key->user_id, $new_key);
                
                $rotated_count++;
            }
            
            // Clean up expired previous keys (grace period over)
            $this->db->where('updated_at <', date('Y-m-d H:i:s', strtotime('-7 days')));
            $this->db->update('api_keys', ['previous_key' => null]);
            
            echo "API key rotation completed. Rotated {$rotated_count} keys.\n";
            log_message('info', "API key rotation completed: {$rotated_count} keys");
            
        } catch (Exception $e) {
            $this->_log_error('api_key_rotate', $e);
            exit(1);
        }
    }
    
    /**
     * Archive old log files
     */
    public function log_archive()
    {
        echo "Starting log archive...\n";
        
        try {
            $log_dir = WRITEPATH . 'logs/';
            $archive_dir = WRITEPATH . 'logs/archive/';
            
            // Create archive directory if not exists
            if (!file_exists($archive_dir)) {
                mkdir($archive_dir, 0755, TRUE);
            }
            
            // Get log files older than 30 days
            $old_logs = glob($log_dir . '*.php');
            $cutoff_time = strtotime('-30 days');
            
            $archived_count = 0;
            foreach ($old_logs as $log_file) {
                if (filemtime($log_file) < $cutoff_time) {
                    $filename = basename($log_file);
                    $archive_name = 'archive_' . date('Y-m-d') . '_' . $filename;
                    
                    // Compress and move to archive
                    $compressed_file = $archive_dir . $archive_name . '.gz';
                    
                    $data = file_get_contents($log_file);
                    $compressed = gzencode($data, 9);
                    file_put_contents($compressed_file, $compressed);
                    
                    // Delete original
                    unlink($log_file);
                    
                    $archived_count++;
                }
            }
            
            // Clean up archives older than 1 year
            $old_archives = glob($archive_dir . '*.gz');
            $year_ago = strtotime('-1 year');
            
            foreach ($old_archives as $archive) {
                if (filemtime($archive) < $year_ago) {
                    unlink($archive);
                }
            }
            
            echo "Log archive completed. Archived {$archived_count} files.\n";
            log_message('info', "Log archive completed: {$archived_count} files");
            
        } catch (Exception $e) {
            $this->_log_error('log_archive', $e);
            exit(1);
        }
    }
    
    /**
     * Process notification queue
     */
    public function notification_queue()
    {
        echo "Starting notification queue processor...\n";
        
        try {
            // Get pending notifications from queue
            $job = $this->job_queue->pop('notifications');
            
            if (!$job) {
                echo "No pending notifications.\n";
                return;
            }
            
            $payload = json_decode($job->payload, true);
            
            // Process based on notification type
            switch ($payload['type']) {
                case 'email':
                    $this->_send_email_notification($payload);
                    break;
                    
                case 'push':
                    $this->_send_push_notification($payload);
                    break;
                    
                case 'whatsapp':
                    $this->_send_whatsapp_notification($payload);
                    break;
                    
                default:
                    throw new Exception("Unknown notification type: {$payload['type']}");
            }
            
            // Mark job as complete
            $this->db->where('id', $job->id);
            $this->db->delete('jobs');
            
            echo "Notification sent successfully.\n";
            log_message('info', "Notification processed: {$payload['type']}");
            
        } catch (Exception $e) {
            if (isset($job->id)) {
                $this->job_queue->fail($job->id, $e);
            }
            $this->_log_error('notification_queue', $e);
            exit(1);
        }
    }
    
    // =========================================================================
    // HELPER METHODS
    // =========================================================================
    
    /**
     * Calculate IKU metrics for a single alumni
     */
    private function _calculate_alumni_iku($alum)
    {
        // IKU-1: Continuing education within 6 months
        $iku1 = $this->_check_continuing_education($alum);
        
        // IKU-2: Employability status
        $iku2 = $this->_check_employability($alum);
        
        // IKU-3: Job relevance to field of study
        $iku3 = $this->_check_job_relevance($alum);
        
        return [
            'iku1' => $iku1,
            'iku2' => $iku2,
            'iku3' => $iku3,
            'calculated_at' => date('Y-m-d H:i:s')
        ];
    }
    
    private function _check_continuing_education($alum)
    {
        // Implementation for IKU-1 calculation
        return [
            'status' => !empty($alum->continuing_education),
            'institution' => $alum->continuing_education ?? null,
            'started_within_6_months' => true // Logic to check
        ];
    }
    
    private function _check_employability($alum)
    {
        // Implementation for IKU-2 calculation
        return [
            'employed' => !empty($alum->employment_status),
            'entrepreneur' => $alum->employment_type === 'entrepreneur',
            'waiting' => $alum->employment_status === 'waiting'
        ];
    }
    
    private function _check_job_relevance($alum)
    {
        // Implementation for IKU-3 calculation
        return [
            'relevant' => $alum->job_relevance === 'yes',
            'field_match' => $alum->job_field_match ?? null
        ];
    }
    
    private function _reminder_already_sent($survey_id)
    {
        // Check if reminder sent in last 7 days
        $this->db->where('survey_id', $survey_id);
        $this->db->where('type', 'reminder');
        $this->db->where('sent_at >', date('Y-m-d H:i:s', strtotime('-7 days')));
        $query = $this->db->get('notification_logs');
        
        return $query->num_rows() > 0;
    }
    
    private function _send_reminder_email($survey)
    {
        $this->email->from('noreply@yoursite.com', 'Survey System');
        $this->email->to($survey->email);
        $this->email->subject('Reminder: Complete Your Survey');
        
        $message = $this->load->view('emails/reminder', [
            'survey' => $survey,
            'link' => site_url('survey/fill/' . $survey->token)
        ], TRUE);
        
        $this->email->message($message);
        $this->email->send();
        
        // Log notification
        $this->db->insert('notification_logs', [
            'survey_id' => $survey->id,
            'type' => 'reminder',
            'channel' => 'email',
            'sent_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    private function _send_whatsapp_reminder($survey)
    {
        // Integration with WhatsApp API (e.g., Fonnte, Twilio)
        $message = "Halo {$survey->name},\n\n";
        $message .= "Ini adalah pengingat untuk mengisi survei tracer study.\n";
        $message .= "Silakan klik link berikut: " . site_url('survey/fill/' . $survey->token);
        
        // Call WhatsApp API
        // $this->whatsapp_api->send($survey->whatsapp_number, $message);
        
        log_message('info', "WhatsApp reminder sent to {$survey->whatsapp_number}");
    }
    
    private function _fetch_pddikti_data($tahun_lulus, $prodi_id = null)
    {
        // Implementation for fetching data from PDDikti API
        // This is a placeholder - implement actual API call
        
        $api_url = config_item('pddikti_api_url');
        $api_key = config_item('pddikti_api_key');
        
        // Example API call using cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url . "/mahasiswa/lulus/{$tahun_lulus}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $api_key,
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true) ?? [];
    }
    
    private function _process_export($payload)
    {
        // Process export based on type
        switch ($payload['export_type']) {
            case 'csv':
                return $this->_export_csv($payload);
            case 'excel':
                return $this->_export_excel($payload);
            case 'pdf':
                return $this->_export_pdf($payload);
            default:
                throw new Exception("Unknown export type: {$payload['export_type']}");
        }
    }
    
    private function _export_csv($payload)
    {
        // Generate CSV export
        $filename = 'export_' . date('YmdHis') . '.csv';
        $filepath = WRITEPATH . 'exports/' . $filename;
        
        // Implementation for CSV generation
        // ...
        
        return ['filename' => $filename, 'path' => $filepath];
    }
    
    private function _export_excel($payload)
    {
        // Generate Excel export
        $filename = 'export_' . date('YmdHis') . '.xlsx';
        $filepath = WRITEPATH . 'exports/' . $filename;
        
        // Implementation for Excel generation using PHPSpreadsheet
        // ...
        
        return ['filename' => $filename, 'path' => $filepath];
    }
    
    private function _export_pdf($payload)
    {
        // Generate PDF export
        $filename = 'export_' . date('YmdHis') . '.pdf';
        $filepath = WRITEPATH . 'exports/' . $filename;
        
        // Implementation for PDF generation using TCPDF or DomPDF
        // ...
        
        return ['filename' => $filename, 'path' => $filepath];
    }
    
    private function _notify_key_rotation($user_id, $new_key)
    {
        // Send notification to user about API key rotation
        $user = $this->user_model->get_by_id($user_id);
        
        $this->email->from('noreply@yoursite.com', 'API Security');
        $this->email->to($user->email);
        $this->email->subject('API Key Rotated for Security');
        
        $message = "Your API key has been automatically rotated for security purposes.\n\n";
        $message .= "New API Key: {$new_key}\n\n";
        $message .= "Please update your applications with the new key.";
        
        $this->email->message($message);
        $this->email->send();
    }
    
    private function _send_email_notification($payload)
    {
        $this->email->from($payload['from'] ?? 'noreply@yoursite.com');
        $this->email->to($payload['to']);
        $this->email->subject($payload['subject']);
        $this->email->message($payload['message']);
        $this->email->send();
    }
    
    private function _send_push_notification($payload)
    {
        // Implementation for push notification (Firebase, OneSignal, etc.)
        // ...
        log_message('info', "Push notification sent to {$payload['user_id']}");
    }
    
    private function _send_whatsapp_notification($payload)
    {
        // Implementation for WhatsApp notification
        // ...
        log_message('info', "WhatsApp notification sent to {$payload['phone']}");
    }
    
    /**
     * Log error and send alert email
     */
    private function _log_error($job_name, $exception)
    {
        $error_msg = "Cron job '{$job_name}' failed: " . $exception->getMessage();
        $error_msg .= "\nFile: " . $exception->getFile();
        $error_msg .= "\nLine: " . $exception->getLine();
        $error_msg .= "\nTrace:\n" . $exception->getTraceAsString();
        
        log_message('error', $error_msg);
        
        // Send alert email to admin
        $admin_email = $this->config->item('admin_email');
        if ($admin_email) {
            $this->email->from('cron@yoursite.com', 'Cron Job Monitor');
            $this->email->to($admin_email);
            $this->email->subject('[CRON ERROR] ' . $job_name . ' Failed');
            $this->email->message($error_msg);
            $this->email->send();
        }
    }
}
