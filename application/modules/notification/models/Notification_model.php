<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notification_model extends CI_Model {

    public function get_user_settings($user_id) {
        if (!$user_id) return null;
        return $this->db->get_where('notification_settings', ['user_id' => $user_id])->row();
    }

    public function save_settings($user_id, $data) {
        $exists = $this->db->get_where('notification_settings', ['user_id' => $user_id])->row();
        if ($exists) {
            $this->db->where('user_id', $user_id)->update('notification_settings', $data);
        } else {
            $data['user_id'] = $user_id;
            $this->db->insert('notification_settings', $data);
        }
    }

    public function get_my_notifications($user_id, $limit = 50) {
        // BR-NOT-003: Scope by user
        $this->db->where('user_id', $user_id);
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($limit);
        return $this->db->get('notification_queue')->result();
    }

    public function mark_as_read($id, $user_id) {
        // Hanya bisa update milik sendiri
        $this->db->where('id', $id);
        $this->db->where('user_id', $user_id);
        return $this->db->update('notification_queue', ['read_at' => date('Y-m-d H:i:s')]);
    }
}
