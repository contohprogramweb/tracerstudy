<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Users Controller - Manajemen User Admin
 * 
 * Mengelola user sistem (CRUD user, role management)
 */
class Users extends Admin_Controller {

    public function __construct() {
        parent::__construct();
        
        // Hanya super_admin dan admin_pusat_karir yang bisa akses
        $this->auth_lib->requireRole(['super_admin', 'admin_pusat_karir']);
        
        $this->load->model('auth/user_model');
        $this->load->helper('tracer_audit');
    }

    /**
     * Index: List semua user
     */
    public function index() {
        $data['page_title'] = 'Manajemen User';
        $data['page_subtitle'] = 'Kelola pengguna sistem';
        
        // Ambil semua user dengan role bukan alumni
        $this->db->select('id, username, email, role, created_at, updated_at');
        $this->db->where('role !=', 'alumni');
        $this->db->order_by('created_at', 'DESC');
        $data['users'] = $this->db->get('users')->result_array();
        
        // Load view
        $this->load->view('admin/templates/header', $data);
        $this->load->view('admin/users/index', $data);
        $this->load->view('admin/templates/footer');
    }
    
    /**
     * API: Get data user untuk DataTables
     */
    public function get_data() {
        $draw = $this->input->get('draw');
        $start = $this->input->get('start');
        $length = $this->input->get('length');
        $search = $this->input->get('search')['value'];
        
        $this->db->select('id, username, email, role, created_at');
        $this->db->from('users');
        $this->db->where('role !=', 'alumni');
        
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('username', $search);
            $this->db->or_like('email', $search);
            $this->db->or_like('role', $search);
            $this->db->group_end();
        }
        
        $total_records = $this->db->count_all_results('', false);
        
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($length, $start);
        $query = $this->db->get();
        $results = $query->result_array();
        
        $data = [];
        foreach ($results as $row) {
            $nestedData = [];
            $nestedData[] = htmlspecialchars($row['username']);
            $nestedData[] = htmlspecialchars($row['email']);
            $nestedData[] = '<span class="badge bg-' . $this->_get_role_badge($row['role']) . '">' . str_replace('_', ' ', strtoupper($row['role'])) . '</span>';
            $nestedData[] = date('d M Y', strtotime($row['created_at']));
            $nestedData[] = '
                <a href="' . site_url('admin/users/edit/' . $row['id']) . '" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteUser(' . $row['id'] . ')"><i class="bi bi-trash"></i></button>
            ';
            $data[] = $nestedData;
        }
        
        $output = [
            "draw" => intval($draw),
            "recordsTotal" => $total_records,
            "recordsFiltered" => $total_records,
            "data" => $data
        ];
        
        echo json_encode($output);
    }
    
    /**
     * Tambah user baru
     */
    public function add() {
        $data['page_title'] = 'Tambah User';
        $data['page_subtitle'] = 'Tambah pengguna baru';
        
        if ($this->input->post()) {
            $this->form_validation->set_rules('username', 'Username', 'required|is_unique[users.username]');
            $this->form_validation->set_rules('email', 'Email', 'required|valid_email|is_unique[users.email]');
            $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');
            $this->form_validation->set_rules('role', 'Role', 'required');
            
            if ($this->form_validation->run()) {
                $user_data = [
                    'username' => $this->input->post('username'),
                    'email' => $this->input->post('email'),
                    'password' => password_hash($this->input->post('password'), PASSWORD_DEFAULT),
                    'role' => $this->input->post('role'),
                    'is_email_verified' => 1,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                $this->db->insert('users', $user_data);
                $user_id = $this->db->insert_id();
                
                audit_log('create', 'users', 'Menambah user baru: ' . $user_data['username'], $this->session->userdata('user_id'));
                
                $this->session->set_flashdata('message', 'User berhasil ditambahkan');
                $this->session->set_flashdata('message_type', 'success');
                redirect('admin/users');
            }
        }
        
        $data['roles'] = ['super_admin', 'admin_pusat_karir', 'admin_prodi', 'admin_fakultas', 'dosen', 'reviewer'];
        
        $this->load->view('admin/templates/header', $data);
        $this->load->view('admin/users/form', $data);
        $this->load->view('admin/templates/footer');
    }
    
    /**
     * Edit user
     */
    public function edit($id) {
        $data['page_title'] = 'Edit User';
        $data['page_subtitle'] = 'Ubah data pengguna';
        
        $user = $this->db->get_where('users', ['id' => $id])->row_array();
        
        if (!$user) {
            show_404();
        }
        
        if ($this->input->post()) {
            $this->form_validation->set_rules('username', 'Username', 'required');
            $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
            $this->form_validation->set_rules('role', 'Role', 'required');
            
            if ($this->form_validation->run()) {
                $update_data = [
                    'username' => $this->input->post('username'),
                    'email' => $this->input->post('email'),
                    'role' => $this->input->post('role'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                if (!empty($this->input->post('password'))) {
                    $update_data['password'] = password_hash($this->input->post('password'), PASSWORD_DEFAULT);
                }
                
                $this->db->where('id', $id)->update('users', $update_data);
                
                audit_log('update', 'users', 'Mengedit user: ' . $user['username'], $this->session->userdata('user_id'));
                
                $this->session->set_flashdata('message', 'User berhasil diupdate');
                $this->session->set_flashdata('message_type', 'success');
                redirect('admin/users');
            }
        }
        
        $data['user'] = $user;
        
        // Ambil semua role yang unik dari database, termasuk role user yang sedang diedit
        $this->db->select('role', FALSE);
        $this->db->distinct();
        $roles_query = $this->db->get('users');
        $db_roles = $roles_query->result_array();
        $db_roles = array_column($db_roles, 'role');
        
        // Tambahkan role standar untuk admin
        $standard_roles = ['super_admin', 'admin_pusat_karir', 'admin_prodi', 'admin_fakultas', 'dosen', 'reviewer'];
        $data['roles'] = array_unique(array_merge($standard_roles, $db_roles));
        sort($data['roles']);
        
        $this->load->view('admin/templates/header', $data);
        $this->load->view('admin/users/form', $data);
        $this->load->view('admin/templates/footer');
    }
    
    /**
     * Delete user
     */
    public function delete($id) {
        $user = $this->db->get_where('users', ['id' => $id])->row_array();
        
        if ($user) {
            $this->db->delete('users', ['id' => $id]);
            audit_log('delete', 'users', 'Menghapus user: ' . $user['username'], $this->session->userdata('user_id'));
            
            echo json_encode(['success' => true, 'message' => 'User berhasil dihapus']);
        } else {
            echo json_encode(['success' => false, 'message' => 'User tidak ditemukan']);
        }
    }
    
    private function _get_role_badge($role) {
        // Normalisasi role untuk memastikan kecocokan
        $role = trim(strtolower($role));
        
        $colors = [
            'super_admin' => 'danger',
            'admin_pusat_karir' => 'primary',
            'admin_pusat' => 'primary', // Alias untuk kompatibilitas
            'admin' => 'primary', // Alias untuk kompatibilitas
            'admin_prodi' => 'success',
            'prodi_admin' => 'success', // Alias untuk kompatibilitas
            'admin_fakultas' => 'info',
            'dosen' => 'teal',
            'reviewer' => 'purple',
            'alumni' => 'secondary',
            'stakeholder' => 'info'
        ];
        
        return isset($colors[$role]) ? $colors[$role] : 'light';
    }
}
