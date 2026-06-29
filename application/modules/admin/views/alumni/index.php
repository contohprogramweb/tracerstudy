<div class="container-fluid py-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
            <div>
                <h5 class="mb-0 text-primary"><i class="bi bi-person-badge me-2"></i>Data Alumni</h5>
                <small class="text-muted">Kelola data alumni</small>
            </div>
            <div>
                <a href="<?= base_url('alumni/alumni/import') ?>" class="btn btn-primary btn-sm">
                    <i class="bi bi-upload me-1"></i> Import Alumni
                </a>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($alumni)): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>NIM</th>
                            <th>Nama</th>
                            <th>Prodi</th>
                            <th>Angkatan</th>
                            <th>Email</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($alumni as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['nim'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($item['nama'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($item['prodi'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($item['angkatan'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($item['email'] ?? '-') ?></td>
                            <td>
                                <?php if ($item['user_id']): ?>
                                <span class="badge bg-success">Terdaftar</span>
                                <?php else: ?>
                                <span class="badge bg-warning">Belum Terdaftar</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-inbox text-muted fs-1"></i>
                <p class="text-muted mt-2">Belum ada data alumni</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
