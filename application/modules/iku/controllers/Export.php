<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller untuk Export Data Belmawa
 * Menangani export template Excel sesuai format tracerstudy.kemdikbud.go.id
 */
class Export extends CI_Controller {

    public function __construct() {
        parent::__construct();
        
        // BR-IKU-005: Authorization Check - Hanya Admin Pusat Karir/Super Admin
        $role = $this->session->userdata('role');
        if (!in_array($role, ['admin_pusat_karir', 'super_admin'])) {
            show_error('Akses Ditolak: Anda tidak memiliki izin untuk melakukan export data Belmawa.', 403);
        }

        $this->load->library('BelmawaTemplate');
        $this->load->model('iku/job_model');
        $this->load->library('email');
    }

    /**
     * Trigger Export Template Belmawa
     * URL: iku/export/belmawa/{kohort_id}/{prodi_id}
     * 
     * @param int $kohort_id
     * @param int $prodi_id
     */
    public function belmawa($kohort_id, $prodi_id) {
        // Validasi input
        if (!is_numeric($kohort_id) || !is_numeric($prodi_id)) {
            $this->output->set_status_header(400);
            echo json_encode(['status' => false, 'message' => 'Parameter tidak valid']);
            return;
        }

        // Create job record untuk tracking
        $job_id = $this->_createJobRecord($kohort_id, $prodi_id);

        // Eksekusi generate Excel
        $result = $this->BelmawaTemplate->generate($kohort_id, $prodi_id);

        if ($result['status']) {
            // Update job status
            $this->job_model->update($job_id, ['status' => 'completed', 'result_message' => 'Selesai']);
            
            // Kirim Notifikasi Email
            $this->_sendNotificationEmail($result['file']);

            echo json_encode([
                'status' => true, 
                'message' => 'Export berhasil', 
                'filename' => $result['file'],
                'download_url' => site_url('iku/export/download/' . $result['file'])
            ]);
        } else {
            $this->job_model->update($job_id, ['status' => 'failed', 'result_message' => $result['message']]);
            $this->output->set_status_header(422);
            echo json_encode(['status' => false, 'message' => $result['message']]);
        }
    }

    /**
     * Download File Hasil Export
     * URL: iku/export/download/{filename}
     * 
     * @param string $filename
     */
    public function download($filename) {
        // Sanitasi filename untuk mencegah directory traversal
        $filename = basename($filename);
        $filepath = FCPATH . 'uploads/exports/' . $filename;
        
        if (file_exists($filepath)) {
            // BR-IKU-006: Log akses download untuk audit trail
            $this->db->insert('download_logs', [
                'filename' => $filename,
                'user_id' => $this->session->userdata('user_id'),
                'downloaded_at' => date('Y-m-d H:i:s'),
                'ip_address' => $this->input->ip_address()
            ]);

            force_download($filepath, null);
        } else {
            show_error('File tidak ditemukan atau sudah dihapus.', 404);
        }
    }

    /**
     * Cek Status Background Job
     * URL: iku/export/status/{job_id}
     * 
     * @param int $job_id
     */
    public function status($job_id) {
        $job = $this->job_model->get_by_id($job_id);
        
        if (!$job) {
            echo json_encode(['status' => false, 'message' => 'Job ID tidak ditemukan']);
            return;
        }

        $response = [
            'job_id' => $job->id,
            'status' => $job->status, // pending, processing, completed, failed
            'progress' => $job->progress ?? 0,
            'message' => $job->result_message
        ];

        if ($job->status == 'completed' && $job->filename) {
            $response['download_url'] = site_url('iku/export/download/' . $job->filename);
        }

        echo json_encode($response);
    }

    /**
     * Form view untuk export (opsional, jika perlu UI form sebelum export)
     * URL: iku/export/form
     */
    public function form() {
        $data['page_title'] = 'Export Data Belmawa';
        $data['kohorts'] = $this->db->get('kohorts')->result();
        $data['prodis'] = $this->db->get('study_programs')->result();
        
        $this->load->view('iku/export', $data);
    }

    // --- Private Helpers ---

    /**
     * Create record job untuk tracking background processing
     */
    private function _createJobRecord($kohort_id, $prodi_id) {
        $data = [
            'job_type' => 'export_belmawa',
            'kohort_id' => $kohort_id,
            'prodi_id' => $prodi_id,
            'status' => 'processing',
            'progress' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'user_id' => $this->session->userdata('user_id')
        ];
        $this->db->insert('jobs', $data);
        return $this->db->insert_id();
    }

    /**
     * Kirim notifikasi email saat export selesai
     */
    private function _sendNotificationEmail($filename) {
        $config_email = $this->config->item('email_protocol');
        if (!$config_email) {
            log_message('warning', 'Email configuration not found, skipping notification');
            return;
        }

        $email_admin = $this->config->item('admin_email');
        if (!$email_admin) {
            $email_admin = 'admin@university.ac.id'; // Default fallback
        }

        $subject = "Export Belmawa Selesai: $filename";
        $message = "
            <html>
            <head>
                <title>Notifikasi Export Belmawa</title>
            </head>
            <body>
                <h2>Export Berhasil</h2>
                <p>File export Belmawa telah siap diunduh.</p>
                <ul>
                    <li><strong>Filename:</strong> $filename</li>
                    <li><strong>Waktu:</strong> " . date('d/m/Y H:i:s') . "</li>
                </ul>
                <p>Silakan login ke sistem untuk mengunduh file.</p>
                <br>
                <small>Ini adalah email otomatis dari Sistem Tracer Study.</small>
            </body>
            </html>
        ";
        
        $this->email->from('no-reply@university.ac.id', 'Sistem Tracer Study');
        $this->email->to($email_admin);
        $this->email->subject($subject);
        $this->email->message($message);
        
        if ($this->email->send()) {
            log_message('info', 'Notifikasi email export terkirim ke ' . $email_admin);
        } else {
            log_message('error', 'Gagal kirim notifikasi email: ' . $this->email->print_debugger());
        }
    }
}
