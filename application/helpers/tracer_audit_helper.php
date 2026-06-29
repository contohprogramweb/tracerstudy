<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Helper function untuk Audit Trail
 * Bisa dipanggil dari controller/model mana saja
 */

if (!function_exists('audit_log')) {
    /**
     * Fungsi global untuk mencatat audit trail
     * 
     * @param string $action (create, update, delete, login, export, import, sync)
     * @param string $module (nama tabel atau modul)
     * @param mixed $description (string atau array detail)
     * @param int|null $user_id (opsional, jika null ambil dari session)
     * @param mixed $old_values (opsional, array data lama)
     * @param mixed $new_values (opsional, array data baru)
     * 
     * @example
     * audit_log('create', 'alumni', 'Create new alumni data', $user_id, null, $data);
     * audit_log('update', 'survey', 'Update survey questions', null, $old_data, $new_data);
     * audit_log('login', 'auth', 'User login successful', $user_id);
     * audit_log('export', 'iku', 'Export IKU report to Excel', $user_id);
     */
    function audit_log($action, $module, $description, $user_id = null, $old_values = null, $new_values = null) {
        $CI =& get_instance();
        
        // Load dependencies jika belum
        $CI->load->database();
        
        // Jika user_id tidak diberikan, coba ambil dari session
        if ($user_id === null && $CI->session->userdata('logged_in')) {
            $user_data = $CI->session->userdata('user_data');
            $user_id = $user_data['id'] ?? null;
        }
        
        // PERBAIKAN: Sesuaikan nama kolom dengan schema tabel activity_logs
        $desc_str = is_array($description) 
            ? json_encode($description, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) 
            : $description;

        $data = [
            'user_id'    => $user_id,
            'action'     => $action,
            'module'     => $module,
            'table_name' => $module,
            'record_id'  => null,
            'old_values' => $old_values !== null ? json_encode($old_values, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
            'new_values' => $new_values !== null 
                ? json_encode($new_values, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) 
                : ($desc_str ? json_encode(['description' => $desc_str]) : null),
            'ip_address' => $CI->input->ip_address(),
            'user_agent' => $CI->input->user_agent(),
        ];
        
        // Insert ke database - BR-SEC-001: Tidak bisa dihapus
        try {
            $CI->db->insert('activity_logs', $data);
            return $CI->db->insert_id();
        } catch (Exception $e) {
            log_message('error', 'Failed to write audit log: ' . $e->getMessage());
            return false;
        }
    }
}
