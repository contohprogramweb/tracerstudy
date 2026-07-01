<?php $this->load->view('templates/header'); ?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-key"></i> Ganti Password</h5>
    </div>
    <div class="card-body">
        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success"><?= $this->session->flashdata('success') ?></div>
        <?php endif; ?>
        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger"><?= $this->session->flashdata('error') ?></div>
        <?php endif; ?>
        
        <form method="post" action="<?= base_url('prodi/profile/change-password') ?>">
            <div class="mb-3">
                <label for="old_password" class="form-label">Password Lama</label>
                <input type="password" class="form-control" id="old_password" name="old_password" required>
            </div>
            <div class="mb-3">
                <label for="new_password" class="form-label">Password Baru</label>
                <input type="password" class="form-control" id="new_password" name="new_password" required minlength="8">
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="8">
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle"></i> Ubah Password
            </button>
            <a href="<?= base_url('prodi/profile') ?>" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </form>
    </div>
</div>

<?php $this->load->view('templates/footer'); ?>
