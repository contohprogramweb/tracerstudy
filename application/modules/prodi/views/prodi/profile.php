<?php $this->load->view('prodi/templates/header'); ?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-person-circle"></i> Informasi Profil</h5>
    </div>
    <div class="card-body">
        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success"><?= $this->session->flashdata('success') ?></div>
        <?php endif; ?>
        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger"><?= $this->session->flashdata('error') ?></div>
        <?php endif; ?>

        <table class="table table-bordered">
            <tr>
                <th width="200">Username</th>
                <td><?= htmlspecialchars($user->username ?? '-') ?></td>
            </tr>
            <tr>
                <th>Email</th>
                <td><?= htmlspecialchars($user->email ?? '-') ?></td>
            </tr>
            <tr>
                <th>Role</th>
                <td><?= htmlspecialchars($user->role ?? '-') ?></td>
            </tr>
            <tr>
                <th>Status</th>
                <td><?= htmlspecialchars($user->status ?? '-') ?></td>
            </tr>
            <tr>
                <th>Login Terakhir</th>
                <td><?= htmlspecialchars($user->last_login ?? '-') ?></td>
            </tr>
        </table>

        <a href="<?= base_url('prodi/profile/change-password') ?>" class="btn btn-outline-primary">
            <i class="bi bi-key"></i> Ganti Password
        </a>
    </div>
</div>

<?php $this->load->view('prodi/templates/footer'); ?>
