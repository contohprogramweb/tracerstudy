<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * User Model
 * 
 * Menangani operasi database untuk user dengan support multi-role
 */
class User_model extends MY_Model {

    protected $table_name = 'users';
    protected $primary_key = 'id';
    protected $soft_delete = FALSE; // Users tidak menggunakan soft delete

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get user by username or email
     */
    public function getUserByUsernameOrEmail($username_or_email)
    {
        return $this->where('(username = ? OR email = ?)', [$username_or_email, $username_or_email])
                    ->first();
    }

    /**
     * Get user by email
     */
    public function getUserByEmail($email)
    {
        return $this->where('email', $email)->first();
    }

    /**
     * Get user by ID
     */
    public function getUserById($id)
    {
        return $this->find($id);
    }

    /**
     * Create new user
     * 
     * @param array $data Data user
     * @return int|bool User ID atau FALSE jika gagal
     */
    public function createUser($data)
    {
        // Hash password jika ada
        if (isset($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
            unset($data['password']);
        }

        // Set default status
        if (!isset($data['status'])) {
            $data['status'] = 'pending_verification';
        }

        return $this->insert($data);
    }

    /**
     * Update user
     * 
     * @param int $id User ID
     * @param array $data Data yang akan diupdate
     * @return bool TRUE jika berhasil
     */
    public function updateUser($id, $data)
    {
        // Hash password jika ada
        if (isset($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
            unset($data['password']);
        }

        return $this->update($id, $data);
    }

    /**
     * Verify email user
     * 
     * @param int $user_id User ID
     * @return bool TRUE jika berhasil
     */
    public function verifyEmail($user_id)
    {
        $data = [
            'status' => 'active',
            'updated_at' => date('Y-m-d H:i:s')
        ];

        return $this->update($user_id, $data);
    }

    /**
     * Update last login timestamp
     * 
     * @param int $user_id User ID
     * @return bool TRUE jika berhasil
     */
    public function updateLastLogin($user_id)
    {
        $data = [
            'last_login' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        return $this->update($user_id, $data);
    }

    /**
     * Save reset password token
     * 
     * @param int $user_id User ID
     * @param string $token Reset token
     * @param string $expires_at Expiration time
     * @return bool TRUE jika berhasil
     */
    public function saveResetToken($user_id, $token, $expires_at)
    {
        $data = [
            'reset_token' => hash('sha256', $token),
            'reset_token_expires' => $expires_at,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        return $this->update($user_id, $data);
    }

    /**
     * Validate reset token
     * 
     * @param string $token Reset token
     * @return object|FALSE User data jika valid, FALSE jika tidak
     */
    public function validateResetToken($token)
    {
        $hashed_token = hash('sha256', $token);
        $now = date('Y-m-d H:i:s');

        $this->db->where('reset_token', $hashed_token);
        $this->db->where('reset_token_expires >', $now);
        $this->db->where('status !=', 'inactive');
        $query = $this->db->get($this->table_name);

        return $query->row();
    }

    /**
     * Reset password
     * 
     * @param int $user_id User ID
     * @param string $new_password New password
     * @return bool TRUE jika berhasil
     */
    public function resetPassword($user_id, $new_password)
    {
        $data = [
            'password_hash' => password_hash($new_password, PASSWORD_BCRYPT),
            'reset_token' => NULL,
            'reset_token_expires' => NULL,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        return $this->update($user_id, $data);
    }

    /**
     * Invalidate reset token
     * 
     * @param string $token Reset token
     * @return bool TRUE jika berhasil
     */
    public function invalidateResetToken($token)
    {
        $hashed_token = hash('sha256', $token);

        $data = [
            'reset_token' => NULL,
            'reset_token_expires' => NULL,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        return $this->db->where('reset_token', $hashed_token)
                        ->update($this->table_name, $data);
    }

    /**
     * Get user by verification token
     * 
     * @param string $token Verification token
     * @return object|FALSE User data jika ditemukan
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
     * 
     * @param int $user_id User ID
     * @param string $token Verification token
     * @param string $expires_at Expiration time
     * @return bool TRUE jika berhasil
     */
    public function saveVerificationToken($user_id, $token, $expires_at)
    {
        $data = [
            'verification_token' => hash('sha256', $token),
            'verification_token_expires' => $expires_at,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        return $this->update($user_id, $data);
    }

    /**
     * Check if user has permission
     * 
     * @param int $user_id User ID
     * @param string $module Module name
     * @param string $action Action name
     * @return bool TRUE jika memiliki permission
     */
    public function hasPermission($user_id, $module, $action)
    {
        $user = $this->find($user_id);
        
        if (!$user) {
            return FALSE;
        }

        // Super admin memiliki semua permission
        if ($user->role === 'super_admin') {
            return TRUE;
        }

        // Check permission berdasarkan role dan module
        // Implementasi bisa disesuaikan dengan tabel permissions jika ada
        $role_permissions = $this->_getRolePermissions($user->role);

        if (isset($role_permissions[$module]) && in_array($action, $role_permissions[$module])) {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Check if user has access to prodi
     * 
     * @param int $user_id User ID
     * @param int $prodi_id Prodi ID
     * @return bool TRUE jika memiliki akses
     */
    public function hasProdiAccess($user_id, $prodi_id)
    {
        $user = $this->find($user_id);
        
        if (!$user) {
            return FALSE;
        }

        // Super admin dan admin_pusat_karir memiliki akses ke semua prodi
        if (in_array($user->role, ['super_admin', 'admin_pusat_karir'])) {
            return TRUE;
        }

        // Admin prodi dan dosen hanya memiliki akses ke prodi tertentu
        // Cek dari profile_id atau tabel user_prodi_access jika ada
        if (in_array($user->role, ['admin_prodi', 'dosen'])) {
            // Asumsi profile_id menyimpan prodi_id untuk admin_prodi dan dosen
            if ($user->profile_id == $prodi_id) {
                return TRUE;
            }

            // Cek dari tabel user_prodi_access jika ada
            $this->db->where('user_id', $user_id);
            $this->db->where('prodi_id', $prodi_id);
            $query = $this->db->get('user_prodi_access');
            
            if ($query->num_rows() > 0) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Get role permissions
     * 
     * @param string $role Role name
     * @return array Permission mapping
     */
    private function _getRolePermissions($role)
    {
        // Default permissions per role
        $permissions = [
            'super_admin' => [
                'alumni' => ['create', 'read', 'update', 'delete', 'export'],
                'survey' => ['create', 'read', 'update', 'delete', 'publish'],
                'iku' => ['create', 'read', 'update', 'delete', 'calculate'],
                'kurikulum' => ['create', 'read', 'update', 'delete'],
                'laporan' => ['create', 'read', 'export'],
                'stakeholder' => ['create', 'read', 'update', 'delete'],
                'users' => ['create', 'read', 'update', 'delete']
            ],
            'admin_pusat_karir' => [
                'alumni' => ['create', 'read', 'update', 'export'],
                'survey' => ['create', 'read', 'update', 'publish'],
                'iku' => ['read', 'calculate'],
                'kurikulum' => ['read'],
                'laporan' => ['create', 'read', 'export'],
                'stakeholder' => ['create', 'read', 'update']
            ],
            'admin_prodi' => [
                'alumni' => ['create', 'read', 'update'],
                'survey' => ['read'],
                'iku' => ['read'],
                'kurikulum' => ['create', 'read', 'update'],
                'laporan' => ['read', 'export'],
                'stakeholder' => ['read']
            ],
            'dosen' => [
                'alumni' => ['read'],
                'survey' => ['read'],
                'iku' => ['read'],
                'kurikulum' => ['read'],
                'laporan' => ['read']
            ],
            'reviewer' => [
                'alumni' => ['read'],
                'survey' => ['read'],
                'iku' => ['read'],
                'laporan' => ['read']
            ]
        ];

        return isset($permissions[$role]) ? $permissions[$role] : [];
    }

    /**
     * Get users by role
     * 
     * @param string $role Role name
     * @return array Array of users
     */
    public function getUsersByRole($role)
    {
        return $this->where('role', $role)->findAll();
    }

    /**
     * Get active users
     * 
     * @return array Array of active users
     */
    public function getActiveUsers()
    {
        return $this->where('status', 'active')->findAll();
    }

    /**
     * Count users by role
     * 
     * @param string $role Role name
     * @return int Count
     */
    public function countByRole($role)
    {
        return $this->where('role', $role)->countAll();
    }
}
