<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notification extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('notification_lib');
        $this->load->model('notification/Notification_model');
        $this->load->helper('text');
    }

    /**
     * Web: List Notifikasi User
     */
    public function index() {
        if (!$this->session->userdata('logged_in')) redirect('auth/login');
        
        $user_id = $this->session->userdata('user_id');
        $data['notifications'] = $this->Notification_model->get_my_notifications($user_id);
        $data['title'] = 'Notifikasi Saya';
        
        $this->load->view('notification/index', $data);
    }

    /**
     * Web: Pengaturan Notifikasi
     */
    public function settings() {
        if (!$this->session->userdata('logged_in')) redirect('auth/login');

        $user_id = $this->session->userdata('user_id');
        
        if ($this->input->post()) {
            $data = [
                'channel_email' => $this->input->post('channel_email') ? 1 : 0,
                'channel_whatsapp' => $this->input->post('channel_whatsapp') ? 1 : 0,
                'channel_telegram' => $this->input->post('channel_telegram') ? 1 : 0,
                'reminder_survey' => $this->input->post('reminder_survey') ? 1 : 0,
                'alert_iku' => $this->input->post('alert_iku') ? 1 : 0,
            ];
            $this->Notification_model->save_settings($user_id, $data);
            $this->session->set_flashdata('success', 'Pengaturan disimpan');
            redirect('notification/settings');
        }

        $data['settings'] = $this->Notification_model->get_user_settings($user_id);
        $data['title'] = 'Pengaturan Notifikasi';
        $this->load->view('notification/settings', $data);
    }

    /**
     * Web: Test Kirim
     */
    public function test() {
        if (!$this->session->userdata('logged_in')) redirect('auth/login');
        
        $user_id = $this->session->userdata('user_id');
        $user = $this->db->get_where('users', ['id' => $user_id])->row();
        
        $type = $this->input->get('channel'); // email, wa, tg
        
        $msg = "<h3>Test Notifikasi</h3><p>Ini adalah pesan uji coba dari sistem Tracer Study.</p><p>Waktu: ".date('H:i:s')."</p>";
        
        if ($type == 'email') {
            $res = $this->notification_lib->sendEmail($user->email, 'Test Email', $msg);
        } elseif ($type == 'wa') {
            $res = $this->notification_lib->sendWhatsApp($user->phone, "Test WA: Ini pesan uji coba.");
        } elseif ($type == 'tg') {
            $res = $this->notification_lib->sendTelegram($user->telegram_chat_id, "Test TG: Ini pesan uji coba.");
        } else {
            // Queue test
            $this->notification_lib->queueNotification($user_id, 'test', 'Test Queue', $msg);
            $res = true;
        }

        echo json_encode(['status' => $res, 'message' => $res ? 'Dikirim' : 'Gagal']);
    }

    /**
     * CLI: Process Queue
     * Usage: php index.php notification processQueue
     */
    public function processQueue() {
        // Hanya bisa diakses via CLI
        if (!$this->input->is_cli_request()) {
            show_error('Command ini hanya bisa dijalankan via CLI');
        }
        
        echo "Memproses antrian notifikasi...\n";
        $this->notification_lib->processQueue();
        echo "Selesai.\n";
    }

    /**
     * CLI: Send Reminders
     * Usage: php index.php notification sendReminders
     */
    public function sendReminders() {
        if (!$this->input->is_cli_request()) {
            show_error('Command ini hanya bisa dijalankan via CLI');
        }

        echo "Mengirim reminder survey (H-7, H-3, H-1)...\n";
        $this->notification_lib->sendReminders();
        echo "Selesai.\n";
    }
}
