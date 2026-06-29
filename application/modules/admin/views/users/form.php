<div class="container-fluid py-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
            <div>
                <h5 class="mb-0 text-primary"><i class="bi bi-person-plus me-2"></i><?= isset($page_title) ? $page_title : 'Form User' ?></h5>
                <small class="text-muted"><?= isset($page_subtitle) ? $page_subtitle : '' ?></small>
            </div>
            <div>
                <a href="<?= base_url('admin/users') ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </div>
        <div class="card-body">
            <?php if (validation_errors()): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <?= validation_errors() ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>
            
            <?php if ($this->session->flashdata('message')): ?>
            <div class="alert alert-<?= $this->session->flashdata('message_type') ?? 'success' ?> alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                <?= $this->session->flashdata('message') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>
            
            <form method="post" action="">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Username <span class="text-danger">*</span></label>
                        <input type="text" name="username" class="form-control" value="<?= isset($user['username']) ? htmlspecialchars($user['username']) : set_value('username') ?>" required>
                        <small class="text-muted">Username untuk login</small>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" value="<?= isset($user['email']) ? htmlspecialchars($user['email']) : set_value('email') ?>" required>
                        <small class="text-muted">Email valid untuk notifikasi</small>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Role <span class="text-danger">*</span></label>
                        <select name="role" class="form-select" required>
                            <option value="">Pilih Role</option>
                            <?php foreach ($roles as $role): ?>
                            <option value="<?= $role ?>" <?= (isset($user['role']) && $user['role'] === $role) || set_value('role') === $role ? 'selected' : '' ?>>
                                <?= str_replace('_', ' ', ucwords($role, '_')) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Hak akses user</small>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Password <?= !isset($user) ? '<span class="text-danger">*</span>' : '(kosongkan jika tidak diubah)' ?></label>
                        <input type="password" name="password" class="form-control" <?= !isset($user) ? 'required' : '' ?>>
                        <small class="text-muted">Minimal 6 karakter</small>
                    </div>
                </div>
                
                <hr class="my-4">
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i> Simpan
                    </button>
                    <a href="<?= base_url('admin/users') ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-2"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
