<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * AuditHook - Hook untuk mencatat activity log secara otomatis
 * 
 * Digunakan untuk intercept operasi database dan mencatatnya ke activity_logs
 * BR-SEC-001: Activity log tidak dapat dihapus oleh siapapun
 */
class AuditHook {
    protected $CI;

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->database();
    }

    /**
     * Log aktivitas manual yang dipanggil dari controller/model
     * 
     * @param string $action (create, update, delete, login, logout, export, import, sync)
     * @param string $module (nama tabel/modul)
     * @param mixed $description (string atau array)
     * @param int|null $user_id
     * @param mixed|null $old_val
     * @param mixed|null $new_val
     * 
     * @return int|false Insert ID atau false jika gagal
     */
    public function log($action, $module, $description, $user_id = null, $old_val = null, $new_val = null) {
        if (empty($user_id)) {
            // Coba ambil dari session jika ada
            if ($this->CI->session->userdata('logged_in')) {
                $user_data = $this->CI->session->userdata('user_data');
                $user_id = $user_data['id'] ?? null;
            } else {
                $user_id = null; // System action
            }
        }

        // PERBAIKAN: Sesuaikan nama kolom dengan schema tabel activity_logs
        // Kolom: action, module, table_name, record_id, old_values, new_values, ip_address, user_agent
        $desc_str = is_array($description) 
            ? json_encode($description, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) 
            : $description;

        $data = [
            'user_id'    => $user_id,
            'action'     => $action,           // 'activity_type' -> 'action'
            'module'     => $module,
            'table_name' => $module,
            'record_id'  => null,
            'old_values' => $old_val !== null ? json_encode($old_val, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
            'new_values' => $new_val !== null 
                ? json_encode($new_val, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) 
                : ($desc_str ? json_encode(['description' => $desc_str]) : null),
            'ip_address' => $this->CI->input->ip_address(),
            'user_agent' => $this->CI->input->user_agent(),
        ];

        try {
            $this->CI->db->insert('activity_logs', $data);
            return $this->CI->db->insert_id();
        } catch (Exception $e) {
            log_message('error', 'AuditHook failed to write log: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * log_activity - dipanggil via post_controller hook (terdaftar di hooks.php)
     * Hanya mencatat request POST/PUT/DELETE yang mengubah data
     */
    public function log_activity() {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if ($method === 'GET') {
            return;
        }

        if (!$this->CI->session->userdata('logged_in')) {
            return;
        }

        $user_id = $this->CI->session->userdata('user_id');

        $this->log(
            strtolower($method),
            $this->CI->router->class . '/' . $this->CI->router->method,
            'Request: ' . $this->CI->uri->uri_string(),
            $user_id
        );
    }

    /**
     * Hook yang dipanggil setelah constructor controller
     */
    public function postControllerConstructor() {
        // Tracking global request - opsional
    }

    /**
     * Hook yang dipanggil sebelum shutdown
     */
    public function postSystem() {
        // Final logging jika diperlukan
    }
}
