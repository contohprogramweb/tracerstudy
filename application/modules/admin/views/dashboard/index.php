<div class="container-fluid py-4">
    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <!-- Total Users -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 bg-primary bg-opacity-10 p-3 rounded">
                            <i class="bi bi-people-fill text-primary fs-3"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Users</h6>
                            <h3 class="mb-0 fw-bold"><?= number_format($total_users) ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Total Alumni -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 bg-success bg-opacity-10 p-3 rounded">
                            <i class="bi bi-person-badge-fill text-success fs-3"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Alumni</h6>
                            <h3 class="mb-0 fw-bold"><?= number_format($total_alumni) ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Total Surveys -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 bg-info bg-opacity-10 p-3 rounded">
                            <i class="bi bi-card-checklist text-info fs-3"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Surveys</h6>
                            <h3 class="mb-0 fw-bold"><?= number_format($total_surveys) ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Total Responses -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 bg-warning bg-opacity-10 p-3 rounded">
                            <i class="bi bi-file-earmark-text text-warning fs-3"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Responses</h6>
                            <h3 class="mb-0 fw-bold"><?= number_format($total_responses) ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- User Roles Statistics -->
    <div class="row g-4 mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-person-gear me-2"></i>Distribusi Role User</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-2">
                            <div class="p-3 bg-danger bg-opacity-10 rounded text-center">
                                <h4 class="mb-0 text-danger fw-bold"><?= number_format($total_super_admin) ?></h4>
                                <small class="text-muted">Super Admin</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="p-3 bg-primary bg-opacity-10 rounded text-center">
                                <h4 class="mb-0 text-primary fw-bold"><?= number_format($total_admin_pusat) ?></h4>
                                <small class="text-muted">Admin Pusat Karir</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="p-3 bg-success bg-opacity-10 rounded text-center">
                                <h4 class="mb-0 text-success fw-bold"><?= number_format($total_admin_prodi) ?></h4>
                                <small class="text-muted">Admin Prodi</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="p-3 bg-info bg-opacity-10 rounded text-center">
                                <h4 class="mb-0 text-info fw-bold"><?= number_format($total_admin_fakultas) ?></h4>
                                <small class="text-muted">Admin Fakultas</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="p-3 bg-teal bg-opacity-10 rounded text-center">
                                <h4 class="mb-0 text-teal fw-bold"><?= number_format($total_dosen) ?></h4>
                                <small class="text-muted">Dosen</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="p-3 bg-purple bg-opacity-10 rounded text-center">
                                <h4 class="mb-0 text-purple fw-bold"><?= number_format($total_reviewer) ?></h4>
                                <small class="text-muted">Reviewer</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Survey Status & Recent Activities -->
    <div class="row g-4 mb-4">
        <!-- Survey Status -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-pie-chart me-2"></i>Status Survei</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="p-4 bg-success bg-opacity-10 rounded text-center">
                                <i class="bi bi-check-circle-fill text-success fs-1 mb-2"></i>
                                <h3 class="mb-0 text-success fw-bold"><?= number_format($active_surveys) ?></h3>
                                <small class="text-muted">Survei Aktif</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-4 bg-warning bg-opacity-10 rounded text-center">
                                <i class="bi bi-pencil-square text-warning fs-1 mb-2"></i>
                                <h3 class="mb-0 text-warning fw-bold"><?= number_format($draft_surveys) ?></h3>
                                <small class="text-muted">Draft</small>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="progress" style="height: 10px;">
                            <?php 
                            $total = $active_surveys + $draft_surveys;
                            $active_percent = $total > 0 ? ($active_surveys / $total) * 100 : 0;
                            ?>
                            <div class="progress-bar bg-success" role="progressbar" style="width: <?= $active_percent ?>%" aria-valuenow="<?= $active_percent ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            <div class="progress-bar bg-warning" role="progressbar" style="width: <?= 100 - $active_percent ?>%" aria-valuenow="<?= 100 - $active_percent ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <small class="text-muted mt-2 d-block">Response Rate: <strong><?= $response_rate ?>%</strong></small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Activities -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Aktivitas Terbaru</h5>
                    <a href="<?= base_url('admin/audit') ?>" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($recent_activities)): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recent_activities as $activity): ?>
                        <div class="list-group-item px-3 py-3 border-bottom">
                            <div class="d-flex w-100 justify-content-between align-items-start">
                                <div>
                                    <span class="badge bg-<?= get_badge_color($activity['action'] ?? '') ?> me-2"><?= strtoupper($activity['action'] ?? '') ?></span>
                                    <strong><?= htmlspecialchars($activity['username'] ?? 'System') ?></strong>
                                    <p class="mb-1 text-muted small"><?= htmlspecialchars(substr($activity['description'] ?? '', 0, 80)) ?><?= strlen($activity['description'] ?? '') > 80 ? '...' : '' ?></p>
                                    <small class="text-muted"><?= date('d M Y H:i', strtotime($activity['created_at'])) ?></small>
                                </div>
                                <small class="text-muted"><?= htmlspecialchars($activity['table_name'] ?? '-') ?></small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-inbox text-muted fs-1"></i>
                        <p class="text-muted mt-2">Belum ada aktivitas</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="row g-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-lightning-charge me-2"></i>Aksi Cepat</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <a href="<?= base_url('admin/users') ?>" class="btn btn-outline-primary w-100 py-3">
                                <i class="bi bi-people fs-4 d-block mb-2"></i>
                                Kelola User
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?= base_url('survey/builder/index') ?>" class="btn btn-outline-success w-100 py-3">
                                <i class="bi bi-plus-circle fs-4 d-block mb-2"></i>
                                Buat Survei Baru
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?= base_url('alumni/alumni/import') ?>" class="btn btn-outline-info w-100 py-3">
                                <i class="bi bi-upload fs-4 d-block mb-2"></i>
                                Import Alumni
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?= base_url('laporan/laporan/index') ?>" class="btn btn-outline-secondary w-100 py-3">
                                <i class="bi bi-file-earmark-bar-graph fs-4 d-block mb-2"></i>
                                Lihat Laporan
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Helper function for badge colors
function get_badge_color($action) {
    $colors = [
        'create' => 'success',
        'insert' => 'success',
        'update' => 'warning',
        'edit' => 'warning',
        'delete' => 'danger',
        'login' => 'info',
        'logout' => 'secondary',
        'export' => 'primary',
        'import' => 'primary',
        'sync' => 'dark'
    ];
    return $colors[strtolower($action)] ?? 'light';
}
?>
