<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * User Model
 * 
 * PERBAIKAN: Method disesuaikan dengan nama method yang ada di MY_Model
 * (get_by, get_by_id, get_all, insert, update, dll.)
 */
class User_model extends MY_Model {

    protected $table_name = 'users';
    protected $primary_key = 'id';
    protected $soft_delete = FALSE;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get user by username or email
     */
    public function getUserByUsernameOrEmail($username_or_email)
    {
        // MY_Model tidak punya where()->first() fluent chain,
        // gunakan raw DB query
        $this->db->where('username', $username_or_email);
        $this->db->or_where('email', $username_or_email);
        $query = $this->db->get($this->table_name);
        return $query->row();
    }

    /**
     * Get user by email
     */
    public function getUserByEmail($email)
    {
        return $this->get_by(array('email' => $email));
    }

    /**
     * Get user by ID
     */
    public function getUserById($id)
    {
        $this->db->where($this->primary_key, $id);
        if ($this->soft_delete) {
            $this->db->where($this->deleted_field, NULL);
        }
        $query = $this->db->get($this->table_name);
        $user = $query->row();
        
        // Pastikan selalu mengembalikan object
        if (is_array($user)) {
            return (object) $user;
        }
        
        return $user;
    }

    /**
     * Create new user
     */
    public function createUser($data)
    {
        if (isset($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
            unset($data['password']);
        }

        if (!isset($data['status'])) {
            $data['status'] = 'pending_verification';
        }

        return $this->insert($data);
    }

    /**
     * Update user
     */
    public function updateUser($id, $data)
    {
        if (isset($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
            unset($data['password']);
        }

        return $this->update($id, $data);
    }

    /**
     * Verify email user
     */
    public function verifyEmail($user_id)
    {
        return $this->update($user_id, array(
            'status'     => 'active',
            'updated_at' => date('Y-m-d H:i:s')
        ));
    }

    /**
     * Update last login timestamp
     */
    public function updateLastLogin($user_id)
    {
        return $this->update($user_id, array(
            'last_login' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ));
    }

    /**
     * Save reset password token
     */
    public function saveResetToken($user_id, $token, $expires_at)
    {
        return $this->update($user_id, array(
            'reset_token'         => hash('sha256', $token),
            'reset_token_expires' => $expires_at,
            'updated_at'          => date('Y-m-d H:i:s')
        ));
    }

    /**
     * Validate reset token
     */
    public function validateResetToken($token)
    {
        $hashed_token = hash('sha256', $token);
        $now          = date('Y-m-d H:i:s');

        $this->db->where('reset_token', $hashed_token);
        $this->db->where('reset_token_expires >', $now);
        $this->db->where('status !=', 'inactive');
        $query = $this->db->get($this->table_name);

        return $query->row();
    }

    /**
     * Reset password
     */
    public function resetPassword($user_id, $new_password)
    {
        return $this->update($user_id, array(
            'password_hash'       => password_hash($new_password, PASSWORD_BCRYPT),
            'reset_token'         => NULL,
            'reset_token_expires' => NULL,
            'updated_at'          => date('Y-m-d H:i:s')
        ));
    }

    /**
     * Invalidate reset token
     */
    public function invalidateResetToken($token)
    {
        $hashed_token = hash('sha256', $token);

        $this->db->where('reset_token', $hashed_token);
        return $this->db->update($this->table_name, array(
            'reset_token'         => NULL,
            'reset_token_expires' => NULL,
            'updated_at'          => date('Y-m-d H:i:s')
        ));
    }

    /**
     * Get user by verification token
     */
    public function getUserByVerificationToken($token)
    {
        $hashed_token = hash('sha256', $token);

        $this->db->where('verification_token', $hashed_token);
        $this->db->where('verification_token_expires >', date('Y-m-d H:i:s'));
        $query = $this->db->get($this->table_name);

        return $query->row();
    }

    /**
     * Save verification token
     */
    public function saveVerificationToken($user_id, $token, $expires_at)
    {
        return $this->update($user_id, array(
            'verification_token'         => hash('sha256', $token),
            'verification_token_expires' => $expires_at,
            'updated_at'                 => date('Y-m-d H:i:s')
        ));
    }

    /**
     * Check if user has permission
     */
    public function hasPermission($user_id, $module, $action)
    {
        $user = $this->get_by_id($user_id);

        if (!$user) {
            return FALSE;
        }

        if ($user->role === 'super_admin') {
            return TRUE;
        }

        $role_permissions = $this->_getRolePermissions($user->role);

        return isset($role_permissions[$module]) && in_array($action, $role_permissions[$module]);
    }

    /**
     * Check if user has access to prodi
     */
    public function hasProdiAccess($user_id, $prodi_id)
    {
        $user = $this->get_by_id($user_id);

        if (!$user) {
            return FALSE;
        }

        if (in_array($user->role, array('super_admin', 'admin_pusat_karir'))) {
            return TRUE;
        }

        if (in_array($user->role, array('admin_prodi', 'admin_fakultas', 'dosen'))) {
            if ($user->profile_id == $prodi_id) {
                return TRUE;
            }

            $this->db->where('user_id', $user_id);
            $this->db->where('prodi_id', $prodi_id);
            $query = $this->db->get('user_prodi_access');

            if ($query && $query->num_rows() > 0) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Get users by role
     */
    public function getUsersByRole($role)
    {
        return $this->get_all(array('role' => $role));
    }

    /**
     * Get active users
     */
    public function getActiveUsers()
    {
        return $this->get_all(array('status' => 'active'));
    }

    /**
     * Count users by role
     */
    public function countByRole($role)
    {
        return $this->count(array('role' => $role));
    }

    /**
     * Get role permissions mapping
     */
    private function _getRolePermissions($role)
    {
        $permissions = array(
            'admin_pusat_karir' => array(
                'alumni'      => array('create', 'read', 'update', 'export'),
                'survey'      => array('create', 'read', 'update', 'publish'),
                'iku'         => array('read', 'calculate'),
                'kurikulum'   => array('read'),
                'laporan'     => array('create', 'read', 'export'),
                'stakeholder' => array('create', 'read', 'update')
            ),
            'admin_prodi' => array(
                'alumni'      => array('create', 'read', 'update'),
                'survey'      => array('read'),
                'iku'         => array('read'),
                'kurikulum'   => array('create', 'read', 'update'),
                'laporan'     => array('read', 'export'),
                'stakeholder' => array('read')
            ),
            'admin_fakultas' => array(
                'alumni'      => array('read', 'export'),
                'survey'      => array('read'),
                'iku'         => array('read'),
                'kurikulum'   => array('read'),
                'laporan'     => array('read', 'export'),
                'stakeholder' => array('read')
            ),
            'dosen' => array(
                'alumni'    => array('read'),
                'survey'    => array('read'),
                'iku'       => array('read'),
                'kurikulum' => array('read'),
                'laporan'   => array('read')
            ),
            'reviewer' => array(
                'alumni'  => array('read'),
                'survey'  => array('read'),
                'iku'     => array('read'),
                'laporan' => array('read')
            )
        );

        return isset($permissions[$role]) ? $permissions[$role] : array();
    }
}
