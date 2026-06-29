<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Profil - Tracer Study' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white"><h5 class="mb-0">Profil Saya</h5></div>
        <div class="card-body">
            <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success"><?= $this->session->flashdata('success') ?></div>
            <?php endif; ?>
            <table class="table table-bordered">
                <tr><th width="200">Username</th><td><?= htmlspecialchars($user->username ?? '-') ?></td></tr>
                <tr><th>Email</th><td><?= htmlspecialchars($user->email ?? '-') ?></td></tr>
                <tr><th>Role</th><td><?= htmlspecialchars($user->role ?? '-') ?></td></tr>
                <tr><th>Status</th><td><?= htmlspecialchars($user->status ?? '-') ?></td></tr>
                <tr><th>Login Terakhir</th><td><?= htmlspecialchars($user->last_login ?? '-') ?></td></tr>
            </table>
            <a href="<?= base_url('auth/change-password') ?>" class="btn btn-outline-primary">Ganti Password</a>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
