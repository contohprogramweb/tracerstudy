<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Auth Library
 * 
 * Middleware untuk otorisasi dan pengecekan sesi
 * 
 * Features:
 * - checkRole($allowed_roles) - Middleware otorisasi berdasarkan role
 * - checkPermission($module, $action) - Granular permission check
 * - getCurrentUser() - Get current logged in user data
 * - isLoggedIn() - Check if user is logged in
 * - hasProdiAccess($prodi_id) - Check prodi access
 * - requireLogin() - Redirect to login if not authenticated
 * - requireRole($roles) - Redirect if role not allowed
 */
class Auth_lib {

    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->database();
        // PERBAIKAN: Load model dengan prefix modul HMVC
        $this->CI->load->model('auth/User_model', 'User_model');
    }

    /**
     * Check if user is logged in
     * 
     * @return bool TRUE if logged in
     */
    public function isLoggedIn()
    {
        $logged_in = $this->CI->session->userdata('logged_in');
        
        if (!$logged_in) {
            return FALSE;
        }

        // Check session timeout (BR-SEC-004: 30 menit idle)
        $last_activity = $this->CI->session->userdata('last_activity');
        $timeout = 1800; // 30 menit dalam detik

        if ($last_activity && (time() - $last_activity > $timeout)) {
            $this->logout();
            return FALSE;
        }

        // Update last activity
        $this->CI->session->set_userdata('last_activity', time());

        return TRUE;
    }

    /**
     * Get current logged in user data
     * 
     * @return object|FALSE User data or FALSE if not logged in
     */
    public function getCurrentUser()
    {
        if (!$this->isLoggedIn()) {
            return FALSE;
        }

        $user_id = $this->CI->session->userdata('user_id');
        
        if (!$user_id) {
            return FALSE;
        }

        return $this->CI->User_model->getUserById($user_id);
    }

    /**
     * Check if current user has specified role(s)
     * 
     * @param string|array $allowed_roles Single role or array of roles
     * @return bool TRUE if user has one of the allowed roles
     */
    public function checkRole($allowed_roles)
    {
        if (!$this->isLoggedIn()) {
            return FALSE;
        }

        $current_role = $this->CI->session->userdata('role');

        if (is_array($allowed_roles)) {
            return in_array($current_role, $allowed_roles);
        }

        return $current_role === $allowed_roles;
    }

    /**
     * Check if current user has permission for module/action
     * 
     * @param string $module Module name
     * @param string $action Action name (create, read, update, delete)
     * @return bool TRUE if user has permission
     */
    public function checkPermission($module, $action)
    {
        if (!$this->isLoggedIn()) {
            return FALSE;
        }

        $user_id = $this->CI->session->userdata('user_id');
        
        return $this->CI->User_model->hasPermission($user_id, $module, $action);
    }

    /**
     * Check if current user has access to specific prodi
     * 
     * @param int $prodi_id Prodi ID
     * @return bool TRUE if user has access
     */
    public function hasProdiAccess($prodi_id)
    {
        if (!$this->isLoggedIn()) {
            return FALSE;
        }

        $user_id = $this->CI->session->userdata('user_id');
        
        return $this->CI->User_model->hasProdiAccess($user_id, $prodi_id);
    }

    /**
     * Require login - redirect to login if not authenticated
     * 
     * Usage: Call this in controller constructor
     */
    public function requireLogin()
    {
        if (!$this->isLoggedIn()) {
            $this->CI->session->set_flashdata('error', 'Silakan login terlebih dahulu.');
            redirect('login');
            exit;
        }
    }

    /**
     * Require specific role(s) - redirect if role not allowed
     * 
     * @param string|array $roles Required role(s)
     * @param string $redirect_url URL to redirect if not authorized (default: dashboard)
     */
    public function requireRole($roles, $redirect_url = NULL)
    {
        $this->requireLogin();

        if (!$this->checkRole($roles)) {
            $this->CI->session->set_flashdata('error', 'Anda tidak memiliki akses ke halaman ini.');
            
            if ($redirect_url) {
                redirect($redirect_url);
                exit;
            } else {
                redirect($this->_getDefaultDashboard());
                exit;
            }
        }
    }

    /**
     * Require permission - redirect if not authorized
     * 
     * @param string $module Module name
     * @param string $action Action name
     * @param string $redirect_url URL to redirect if not authorized
     */
    public function requirePermission($module, $action, $redirect_url = NULL)
    {
        $this->requireLogin();

        if (!$this->checkPermission($module, $action)) {
            $this->CI->session->set_flashdata('error', 'Anda tidak memiliki izin untuk melakukan aksi ini.');
            
            if ($redirect_url) {
                redirect($redirect_url);
            } else {
                redirect($this->_getDefaultDashboard());
            }
        }
    }

    /**
     * Require prodi access - redirect if no access
     * 
     * @param int $prodi_id Prodi ID
     * @param string $redirect_url URL to redirect if not authorized
     */
    public function requireProdiAccess($prodi_id, $redirect_url = NULL)
    {
        $this->requireLogin();

        if (!$this->hasProdiAccess($prodi_id)) {
            $this->CI->session->set_flashdata('error', 'Anda tidak memiliki akses ke program studi ini.');
            
            if ($redirect_url) {
                redirect($redirect_url);
            } else {
                redirect($this->_getDefaultDashboard());
            }
        }
    }

    /**
     * Check if alumni email is verified
     * BR-ALM-005: Alumni belum verifikasi email boleh isi survey tapi tidak masuk IKU
     * 
     * @return bool TRUE if email is verified
     */
    public function isEmailVerified()
    {
        if (!$this->isLoggedIn()) {
            return FALSE;
        }

        return (bool) $this->CI->session->userdata('is_email_verified');
    }

    /**
     * Get current user role
     * 
     * @return string|FALSE Role name or FALSE if not logged in
     */
    public function getRole()
    {
        if (!$this->isLoggedIn()) {
            return FALSE;
        }

        return $this->CI->session->userdata('role');
    }

    /**
     * Get current user ID
     * 
     * @return int|FALSE User ID or FALSE if not logged in
     */
    public function getUserId()
    {
        if (!$this->isLoggedIn()) {
            return FALSE;
        }

        return $this->CI->session->userdata('user_id');
    }

    /**
     * Logout user
     */
    public function logout()
    {
        $this->CI->session->sess_destroy();
    }

    /**
     * Get default dashboard URL based on role
     * 
     * @return string Dashboard URL
     */
    private function _getDefaultDashboard()
    {
        $role = $this->CI->session->userdata('role');
        
        switch ($role) {
            case 'super_admin':
            case 'admin_pusat_karir':
                return 'admin/dashboard';
            case 'admin_prodi':
            case 'admin_fakultas':
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

    /**
     * Extend session (heartbeat)
     * BR-SEC-004: Extend session saat user masih aktif (misal saat isi survey)
     */
    public function extendSession()
    {
        if ($this->isLoggedIn()) {
            $this->CI->session->set_userdata('last_activity', time());
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Get session remaining time
     * 
     * @return int Remaining seconds before timeout
     */
    public function getSessionRemainingTime()
    {
        if (!$this->isLoggedIn()) {
            return 0;
        }

        $last_activity = $this->CI->session->userdata('last_activity');
        $timeout = 1800; // 30 menit

        if (!$last_activity) {
            return $timeout;
        }

        $elapsed = time() - $last_activity;
        $remaining = $timeout - $elapsed;

        return max(0, $remaining);
    }
}
