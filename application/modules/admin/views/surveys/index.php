<div class="container-fluid py-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
            <div>
                <h5 class="mb-0 text-primary"><i class="bi bi-card-checklist me-2"></i>Manajemen Survei</h5>
                <small class="text-muted">Kelola survei dan kuesioner</small>
            </div>
            <div>
                <a href="<?= base_url('survey/builder/index') ?>" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle me-1"></i> Buat Survei Baru
                </a>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($surveys)): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Judul Survei</th>
                            <th>Dibuat Oleh</th>
                            <th>Status</th>
                            <th>Tanggal Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($surveys as $survey): ?>
                        <tr>
                            <td><?= htmlspecialchars($survey['title'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($survey['creator'] ?? 'System') ?></td>
                            <td>
                                <?php if ($survey['status'] === 'active'): ?>
                                <span class="badge bg-success">Aktif</span>
                                <?php elseif ($survey['status'] === 'draft'): ?>
                                <span class="badge bg-warning">Draft</span>
                                <?php else: ?>
                                <span class="badge bg-secondary"><?= ucfirst($survey['status']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('d M Y', strtotime($survey['created_at'])) ?></td>
                            <td>
                                <a href="<?= base_url('survey/builder/edit/' . $survey['id']) ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-inbox text-muted fs-1"></i>
                <p class="text-muted mt-2">Belum ada survei</p>
                <a href="<?= base_url('survey/builder/index') ?>" class="btn btn-primary mt-2">
                    <i class="bi bi-plus-circle me-1"></i> Buat Survei Pertama
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
