<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notification {
    protected $CI;

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->database();
        $this->CI->load->library('email');
        $this->CI->load->model('notification_model');
    }

    /**
     * Kirim Email Langsung
     */
    public function sendEmail($to, $subject, $message, $attachment = null) {
        $config = [
            'mailtype' => 'html',
            'charset' => 'utf-8',
            'newline' => "\r\n"
        ];
        $this->CI->email->initialize($config);
        $this->CI->email->from('no-reply@tracer.univ.ac.id', 'Tracer Study System');
        $this->CI->email->to($to);
        $this->CI->email->subject($subject);
        $this->CI->email->message($message);

        if ($attachment && file_exists($attachment)) {
            $this->CI->email->attach($attachment);
        }

        if ($this->CI->email->send()) {
            $this->_log('email', $to, 'success', 'Email sent successfully');
            return true;
        } else {
            $error = $this->CI->email->print_data(true);
            $this->_log('email', $to, 'failed', $error);
            return false;
        }
    }

    /**
     * Kirim WhatsApp (Integrasi API Contoh: Fonnte/Twilio)
     */
    public function sendWhatsApp($to, $message) {
        // Format nomor: 628xxx
        $api_url = 'https://api.fonnte.com/send'; // Ganti dengan provider asli
        $token = $this->CI->config->item('wa_api_token');

        $data = [
            'target' => $to,
            'message' => $message,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: '.$token]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 200 && json_decode($response)->status ?? false) {
            $this->_log('whatsapp', $to, 'success', 'WA sent');
            return true;
        } else {
            $this->_log('whatsapp', $to, 'failed', $response ?? 'Curl error');
            return false;
        }
    }

    /**
     * Kirim Telegram (Bot API)
     */
    public function sendTelegram($to, $message) {
        $bot_token = $this->CI->config->item('telegram_bot_token');
        $url = "https://api.telegram.org/bot$bot_token/sendMessage";

        $data = [
            'chat_id' => $to,
            'text' => $message,
            'parse_mode' => 'HTML'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = json_decode($response, true);

        if ($httpCode == 200 && ($result['ok'] ?? false)) {
            $this->_log('telegram', $to, 'success', 'TG sent');
            return true;
        } else {
            $this->_log('telegram', $to, 'failed', $result['description'] ?? 'Unknown error');
            return false;
        }
    }

    /**
     * Queue Notifikasi ke Database
     * BR-NOT-003: Scope by role handled here if user_id provided
     */
    public function queueNotification($user_id = null, $type, $subject, $message, $attachment = null, $schedule_time = null) {
        // Ambil preferensi user
        $settings = $this->CI->notification_model->get_user_settings($user_id);
        $user_data = $user_id ? $this->CI->db->get_where('users', ['id' => $user_id])->row() : null;

        $channels = [];
        
        // Tentukan channel berdasarkan setting
        if (!$settings || $settings->channel_email) $channels[] = 'email';
        if ($settings && $settings->channel_whatsapp) $channels[] = 'whatsapp';
        if ($settings && $settings->channel_telegram) $channels[] = 'telegram';

        // Fallback ke email jika tidak ada setting atau semua off
        if (empty($channels)) $channels = ['email'];

        $recipient_email = $user_data->email ?? null;
        $recipient_wa = $user_data->phone ?? null;
        $recipient_tg = $user_data->telegram_chat_id ?? null;

        foreach ($channels as $channel) {
            $recipient = null;
            if ($channel == 'email') $recipient = $recipient_email;
            elseif ($channel == 'whatsapp') $recipient = $recipient_wa;
            elseif ($channel == 'telegram') $recipient = $recipient_tg;

            if ($recipient) {
                $data = [
                    'user_id' => $user_id,
                    'recipient' => $recipient,
                    'channel' => $channel,
                    'type' => $type,
                    'subject' => $subject,
                    'message' => $message,
                    'attachment_path' => $attachment,
                    'scheduled_at' => $schedule_time,
                    'status' => 'pending'
                ];
                $this->CI->db->insert('notification_queue', $data);
            }
        }
    }

    /**
     * Proses Queue (Dipanggil oleh Cron/CLI)
     * BR-NOT-002: Retry logic & Fallback
     */
    public function processQueue() {
        $now = date('Y-m-d H:i:s');
        
        // Ambil pending yang sudah waktunya atau langsung
        $this->CI->db->where('status', 'pending');
        $this->CI->db->group_start();
        $this->CI->db->where('scheduled_at <=', $now);
        $this->CI->db->or_where('scheduled_at IS NULL');
        $this->CI->db->group_end();
        $this->CI->db->limit(50); // Batch processing
        $queue = $this->CI->db->get('notification_queue')->result();

        foreach ($queue as $item) {
            $success = false;

            if ($item->channel == 'email') {
                $success = $this->sendEmail($item->recipient, $item->subject, $item->message, $item->attachment_path);
            } elseif ($item->channel == 'whatsapp') {
                $success = $this->sendWhatsApp($item->recipient, $item->message);
            } elseif ($item->channel == 'telegram') {
                $success = $this->sendTelegram($item->recipient, $item->message);
            }

            if ($success) {
                $this->CI->db->where('id', $item->id)->update('notification_queue', [
                    'status' => 'sent',
                    'sent_at' => date('Y-m-d H:i:s')
                ]);
            } else {
                $retry_count = $item->retry_count + 1;
                if ($retry_count >= $item->max_retry) {
                    // Max retry reached
                    // BR-NOT-002: Fallback ke Email jika WA/TG gagal total
                    if ($item->channel != 'email') {
                        $this->queueNotification($item->user_id, $item->type.'_fallback', $item->subject, $item->message, $item->attachment_path);
                    }
                    
                    $this->CI->db->where('id', $item->id)->update('notification_queue', [
                        'status' => 'failed',
                        'error_message' => 'Max retries reached'
                    ]);
                } else {
                    // Retry lagi nanti (bisa dijadwalkan ulang beberapa menit kemudian)
                    $this->CI->db->where('id', $item->id)->update('notification_queue', [
                        'status' => 'retry',
                        'retry_count' => $retry_count,
                        'scheduled_at' => date('Y-m-d H:i:s', strtotime('+5 minutes'))
                    ]);
                }
            }
        }
    }

    /**
     * Kirim Reminder Survey (BR-NOT-001)
     * Dipanggil daily 08:00 WIB
     */
    public function sendReminders() {
        $today = date('Y-m-d');
        $h7 = date('Y-m-d', strtotime('+7 days'));
        $h3 = date('Y-m-d', strtotime('+3 days'));
        $h1 = date('Y-m-d', strtotime('+1 day'));

        // Cari survey yang belum diisi dan mendekati deadline
        $this->CI->db->select('surveys.*, alumni.email, alumni.phone, alumni.name');
        $this->CI->db->join('alumni', 'alumni.id = surveys.alumni_id');
        $this->CI->db->where('surveys.status', 'pending');
        $this->CI->db->where_in('surveys.deadline_date', [$h7, $h3, $h1]);
        $surveys = $this->CI->db->get('surveys')->result();

        foreach ($surveys as $s) {
            $days_left = floor((strtotime($s->deadline_date) - time()) / 86400);
            $subject = "Reminder Survey Tracer Study (H-$days_left)";
            
            $message = "Yth. {$s->name},<br><br>";
            $message .= "Ini adalah pengingat bahwa Anda belum mengisi survei tracer study.<br>";
            $message .= "Batas waktu: <strong>{$s->deadline_date}</strong> (H-$days_left lagi).<br><br>";
            $message .= "Mohon segera mengisi melalui link berikut:<br>";
            $message .= "<a href='".site_url('survey/fill/'.$s->token)."'>".site_url('survey/fill/'.$s->token)."</a><br><br>";
            $message .= "Terima kasih.";

            $this->queueNotification($s->alumni_id, 'reminder_survey', $subject, $message);
        }
    }

    private function _log($channel, $recipient, $status, $message) {
        $this->CI->db->insert('notification_logs', [
            'channel' => $channel,
            'recipient' => $recipient,
            'status' => $status,
            'message' => substr($message, 0, 500)
        ]);
    }
}
