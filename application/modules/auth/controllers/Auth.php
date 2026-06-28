<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Auth Controller
 * 
 * Menangani autentikasi multi-role dengan rate limiting, forgot password, dan email verification
 * 
 * Business Rules:
 * - BR-SEC-004: Session timeout 30 menit idle, extend saat isi survey (heartbeat)
 * - BR-SEC-005: Rate limit login 5 attempt per IP per menit, lock 30 menit
 * - BR-ALM-005: Alumni belum verifikasi email boleh isi survey tapi tidak masuk IKU
 */
class Auth extends MY_Controller {

    private $max_login_attempts = 5;
    private $lockout_duration = 1800; // 30 menit dalam detik
    private $session_timeout = 1800; // 30 menit dalam detik

    public function __construct()
    {
        parent::__construct();
        $this->load->model('auth/User_model');
        $this->load->library('form_validation');
        $this->load->library('tracer_encryption');
        $this->load->library('auth_lib'); // Load auth_lib
        $this->load->helper('date');
        
        // Jika sudah login, redirect ke dashboard sesuai role
        if ($this->auth_lib->isLoggedIn()) {
            redirect($this->_get_dashboard_url());
        }
    }

    /**
     * Halaman Login
     * 
     * BR-SEC-005: Rate limit login 5 attempt per IP per menit
     */
    public function login()
    {
        $data['title'] = 'Login - Tracer Study';
        $data['page'] = 'auth/login';

        if ($this->input->post()) {
            $username = $this->input->post('username', TRUE);
            $password = $this->input->post('password', TRUE);
            $ip_address = $this->input->ip_address();

            // Cek rate limiting
            if ($this->_is_rate_limited($ip_address, $username)) {
                $this->session->set_flashdata('error', 
                    'Terlalu banyak percobaan login. Silakan coba lagi dalam 30 menit.');
                redirect('login');
            }

            // Validasi input
            $this->form_validation->set_rules('username', 'Username/Email', 'required|trim');
            $this->form_validation->set_rules('password', 'Password', 'required|trim');

            if ($this->form_validation->run() == FALSE) {
                $this->load->view('templates/auth_layout', $data);
                return;
            }

            // Attempt login
            $user = $this->User_model->getUserByUsernameOrEmail($username);

            if ($user && password_verify($password, $user->password_hash)) {
                // Cek status user
                if ($user->status === 'inactive') {
                    $this->_log_activity(null, 'login_failed', 'Account inactive', $ip_address);
                    $this->_record_login_attempt($ip_address, $username, FALSE);
                    $this->session->set_flashdata('error', 'Akun Anda tidak aktif. Hubungi administrator.');
                    redirect('login');
                }

                // Set session
                $session_data = [
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'role' => $user->role,
                    'profile_id' => $user->profile_id,
                    'is_email_verified' => ($user->status === 'active'),
                    'logged_in' => TRUE,
                    'login_time' => time(),
                    'last_activity' => time()
                ];

                $this->session->set_userdata($session_data);

                // Update last_login
                $this->User_model->updateLastLogin($user->id);

                // Log activity
                $this->_log_activity($user->id, 'login_success', 'Login berhasil', $ip_address);
                $this->_record_login_attempt($ip_address, $username, TRUE);

                // Redirect berdasarkan role
                redirect($this->_get_dashboard_url());
            } else {
                // Login gagal
                $this->_log_activity(null, 'login_failed', 'Invalid credentials', $ip_address);
                $this->_record_login_attempt($ip_address, $username, FALSE);
                $this->session->set_flashdata('error', 'Username atau password salah.');
                redirect('login');
            }
        }

        $this->load->view('templates/auth_layout', $data);
    }

    /**
     * Logout
     * Destroy session dan log activity
     */
    public function logout()
    {
        $user_id = $this->session->userdata('user_id');
        $ip_address = $this->input->ip_address();

        if ($user_id) {
            $this->_log_activity($user_id, 'logout', 'Logout berhasil', $ip_address);
        }

        $this->session->sess_destroy();
        redirect('login');
    }

    /**
     * Forgot Password
     * Kirim email reset password dengan token
     */
    public function forgotPassword()
    {
        $data['title'] = 'Lupa Password - Tracer Study';
        $data['page'] = 'auth/forgot_password';

        if ($this->input->post()) {
            $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email');

            if ($this->form_validation->run() == FALSE) {
                $this->load->view('templates/auth_layout', $data);
                return;
            }

            $email = $this->input->post('email', TRUE);
            $user = $this->User_model->getUserByEmail($email);

            if ($user) {
                // Generate reset token
                $reset_token = bin2hex(random_bytes(32));
                $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

                // Simpan token (bisa di tabel users atau password_resets)
                $this->User_model->saveResetToken($user->id, $reset_token, $expires_at);

                // Kirim email (implementasi sesuai konfigurasi email)
                $reset_link = site_url("reset-password/{$reset_token}");
                $this->_sendResetEmail($user->email, $user->username, $reset_link);

                $this->session->set_flashdata('success', 
                    'Link reset password telah dikirim ke email Anda. Berlaku selama 1 jam.');
            } else {
                // Jangan beri tahu apakah email terdaftar atau tidak (security)
                $this->session->set_flashdata('success', 
                    'Jika email terdaftar, link reset password telah dikirim.');
            }

            redirect('forgot-password');
        }

        $this->load->view('templates/auth_layout', $data);
    }

    /**
     * Reset Password
     * Validasi token dan update password
     */
    public function resetPassword($token)
    {
        $data['title'] = 'Reset Password - Tracer Study';
        $data['page'] = 'auth/reset_password';
        $data['token'] = $token;

        // Validasi token
        $token_valid = $this->User_model->validateResetToken($token);

        if (!$token_valid) {
            $this->session->set_flashdata('error', 'Token reset password tidak valid atau sudah kadaluarsa.');
            redirect('forgot-password');
            return;
        }

        if ($this->input->post()) {
            $this->form_validation->set_rules('password', 'Password Baru', 'required|trim|min_length[8]');
            $this->form_validation->set_rules('confirm_password', 'Konfirmasi Password', 'required|trim|matches[password]');

            if ($this->form_validation->run() == FALSE) {
                $this->load->view('templates/auth_layout', $data);
                return;
            }

            $password = $this->input->post('password', TRUE);
            $user_id = $token_valid->user_id;

            // Update password
            if ($this->User_model->resetPassword($user_id, $password)) {
                // Hapus token setelah digunakan
                $this->User_model->invalidateResetToken($token);

                // Log activity
                $this->_log_activity($user_id, 'password_reset', 'Password berhasil direset', $this->input->ip_address());

                $this->session->set_flashdata('success', 'Password berhasil direset. Silakan login.');
                redirect('login');
            } else {
                $this->session->set_flashdata('error', 'Gagal mereset password. Silakan coba lagi.');
            }
        }

        $this->load->view('templates/auth_layout', $data);
    }

    /**
     * Verify Email
     * Verifikasi OTP/email verification
     */
    public function verifyEmail($token)
    {
        // Validasi token verifikasi email
        $user = $this->User_model->getUserByVerificationToken($token);

        if ($user) {
            // Activate user
            if ($this->User_model->verifyEmail($user->id)) {
                $this->session->set_flashdata('success', 'Email berhasil diverifikasi. Silakan login.');
                
                // Log activity
                $this->_log_activity($user->id, 'email_verified', 'Email berhasil diverifikasi', $this->input->ip_address());
            } else {
                $this->session->set_flashdata('error', 'Gagal memverifikasi email.');
            }
        } else {
            $this->session->set_flashdata('error', 'Token verifikasi tidak valid atau sudah kadaluarsa.');
        }

        redirect('login');
    }

    /**
     * Check Session Heartbeat
     * Extend session saat user masih aktif (misal saat isi survey)
     * 
     * BR-SEC-004: Session timeout 30 menit idle
     */
    public function checkSession()
    {
        if ($this->auth_lib->isLoggedIn()) {
            $this->session->set_userdata('last_activity', time());
            echo json_encode(['status' => 'success', 'message' => 'Session extended']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Session expired']);
        }
    }

    /**
     * Cek rate limiting login
     * 
     * BR-SEC-005: Max 5 attempt per IP per menit, lock 30 menit
     */
    private function _is_rate_limited($ip_address, $username)
    {
        $this->load->database();
        
        // Hitung jumlah attempt dalam 1 menit terakhir
        $one_minute_ago = date('Y-m-d H:i:s', strtotime('-1 minute'));
        
        $this->db->where('ip_address', $ip_address);
        $this->db->where('attempt_time >=', $one_minute_ago);
        $this->db->where('success', 0);
        $query = $this->db->get('login_attempts');
        
        $failed_attempts = $query->num_rows();
        
        if ($failed_attempts >= $this->max_login_attempts) {
            // Cek apakah ada lockout aktif
            $last_attempt = $query->row();
            if ($last_attempt) {
                $time_since_last = time() - strtotime($last_attempt->attempt_time);
                if ($time_since_last < $this->lockout_duration) {
                    return TRUE; // Masih dalam lockout period
                }
            }
        }
        
        return FALSE;
    }

    /**
     * Record login attempt ke database
     */
    private function _record_login_attempt($ip_address, $username, $success)
    {
        $this->load->database();
        
        $data = [
            'ip_address' => $ip_address,
            'username' => $username,
            'success' => $success ? 1 : 0
        ];
        
        $this->db->insert('login_attempts', $data);
        
        // Cleanup old records (lebih dari 24 jam)
        $this->db->delete('login_attempts', ['attempt_time <' => date('Y-m-d H:i:s', strtotime('-24 hours'))]);
    }

    /**
     * Log activity ke tabel activity_logs
     */
    private function _log_activity($user_id, $activity_type, $description, $ip_address = NULL)
    {
        $this->load->database();
        
        $data = [
            'user_id' => $user_id,
            'activity_type' => $activity_type,
            'description' => $description,
            'ip_address' => $ip_address ?: $this->input->ip_address(),
            'user_agent' => $this->input->user_agent()
        ];
        
        $this->db->insert('activity_logs', $data);
    }

    /**
     * Kirim email reset password
     */
    private function _sendResetEmail($to, $username, $reset_link)
    {
        $this->load->library('email');
        
        $config['protocol'] = 'smtp';
        $config['smtp_host'] = $this->config->item('smtp_host');
        $config['smtp_port'] = $this->config->item('smtp_port');
        $config['smtp_user'] = $this->config->item('smtp_user');
        $config['smtp_pass'] = $this->config->item('smtp_pass');
        $config['mailtype'] = 'html';
        $config['charset'] = 'utf-8';
        $config['newline'] = "\r\n";
        
        $this->email->initialize($config);
        $this->email->from('noreply@tracerstudy.edu', 'Tracer Study System');
        $this->email->to($to);
        $this->email->subject('Reset Password - Tracer Study');
        
        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .button { 
                    background-color: #007bff; 
                    color: white; 
                    padding: 10px 20px; 
                    text-decoration: none; 
                    border-radius: 5px;
                }
            </style>
        </head>
        <body>
            <h2>Reset Password</h2>
            <p>Halo {$username},</p>
            <p>Anda menerima email ini karena ada permintaan reset password untuk akun Anda.</p>
            <p>Klik tombol di bawah ini untuk reset password:</p>
            <p><a href='{$reset_link}' class='button'>Reset Password</a></p>
            <p>Link ini berlaku selama 1 jam.</p>
            <p>Jika Anda tidak meminta reset password, abaikan email ini.</p>
            <hr>
            <p><small>Tracer Study System - Perguruan Tinggi</small></p>
        </body>
        </html>
        ";
        
        $this->email->message($message);
        $this->email->send();
    }

    /**
     * Get dashboard URL berdasarkan role
     */
    private function _get_dashboard_url()
    {
        $role = $this->session->userdata('role');
        
        switch ($role) {
            case 'super_admin':
            case 'admin_pusat_karir':
                return 'admin/dashboard';
            case 'admin_prodi':
            case 'dosen':
                return 'prodi/dashboard';
            case 'alumni':
                return 'alumni/dashboard';
            case 'stakeholder':
                return 'stakeholder/dashboard';
            case 'reviewer':
                return 'reviewer/dashboard';
            default:
                return 'dashboard';
        }
    }
}
