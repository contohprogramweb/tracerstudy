<div class="container-fluid">
    <!-- Page Title -->
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="text-dark fw-bold">
                <i class="fas fa-tachometer-alt me-2"></i><?= htmlspecialchars($page_title) ?>
            </h2>
            <p class="text-muted"><?= htmlspecialchars($page_subtitle) ?></p>
        </div>
    </div>

    <!-- Prodi Info Card -->
    <?php if ($prodi_info): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-gradient rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="fas fa-graduation-cap text-white fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-1 text-dark"><?= htmlspecialchars($prodi_info['nama_prodi'] ?? $prodi_info['nama'] ?? 'Nama Prodi Tidak Ditemukan') ?></h5>
                            <p class="text-muted mb-0 small">Kode Prodi: <?= htmlspecialchars($prodi_info['kode_prodi'] ?? $prodi_info['kode'] ?? 'N/A') ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <!-- Total Alumni -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-gradient rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fas fa-users text-white fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Alumni</h6>
                            <h3 class="mb-0 fw-bold text-dark"><?= number_format($total_alumni) ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alumni Bekerja -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-gradient rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fas fa-briefcase text-white fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Bekerja</h6>
                            <h3 class="mb-0 fw-bold text-success"><?= number_format($alumni_bekerja) ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Wirausaha -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-gradient rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fas fa-store text-white fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Wirausaha</h6>
                            <h3 class="mb-0 fw-bold text-info"><?= number_format($alumni_wirausaha) ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Melanjutkan Studi -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-gradient rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fas fa-book-reader text-white fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Melanjutkan Studi</h6>
                            <h3 class="mb-0 fw-bold text-warning"><?= number_format($alumni_melanjutkan_studi) ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Survey Statistics -->
    <div class="row g-4 mb-4">
        <!-- Total Surveys -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-purple bg-gradient rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fas fa-clipboard-list text-white fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Survei</h6>
                            <h3 class="mb-0 fw-bold text-dark"><?= number_format($total_surveys) ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Surveys -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-gradient rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fas fa-check-circle text-white fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Survei Aktif</h6>
                            <h3 class="mb-0 fw-bold text-success"><?= number_format($active_surveys) ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Responses -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-danger bg-gradient rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fas fa-comments text-white fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Respon</h6>
                            <h3 class="mb-0 fw-bold text-danger"><?= number_format($total_responses) ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Row -->
    <div class="row g-4">
        <!-- Recent Activities -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 text-dark fw-bold">
                        <i class="fas fa-history me-2"></i>Aktivitas Terbaru
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($recent_activities)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th width="20%">Tanggal</th>
                                    <th width="25%">User</th>
                                    <th width="35%">Aktivitas</th>
                                    <th width="20%">Modul</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_activities as $activity): ?>
                                <tr>
                                    <td>
                                        <small class="text-muted">
                                            <?= date('d/m/Y H:i', strtotime($activity['created_at'])) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?= htmlspecialchars($activity['username'] ?? 'System') ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($activity['activity'] ?? '-') ?></td>
                                    <td>
                                        <span class="badge bg-primary"><?= htmlspecialchars($activity['module'] ?? '-') ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-inbox text-muted fa-3x mb-3"></i>
                        <p class="text-muted">Belum ada aktivitas tercatat</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 text-dark fw-bold">
                        <i class="fas fa-chart-pie me-2"></i>Status Alumni
                    </h5>
                </div>
                <div class="card-body">
                    <?php 
                    $total = $total_alumni > 0 ? $total_alumni : 1;
                    $bekerja_pct = round(($alumni_bekerja / $total) * 100, 1);
                    $belum_bekerja_pct = round(($alumni_belum_bekerja / $total) * 100, 1);
                    $wirausaha_pct = round(($alumni_wirausaha / $total) * 100, 1);
                    $melanjutkan_pct = round(($alumni_melanjutkan_studi / $total) * 100, 1);
                    ?>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small text-muted">Bekerja</span>
                            <span class="small fw-bold"><?= $bekerja_pct ?>%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: <?= $bekerja_pct ?>%"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small text-muted">Belum Bekerja</span>
                            <span class="small fw-bold"><?= $belum_bekerja_pct ?>%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-danger" role="progressbar" style="width: <?= $belum_bekerja_pct ?>%"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small text-muted">Wirausaha</span>
                            <span class="small fw-bold"><?= $wirausaha_pct ?>%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-info" role="progressbar" style="width: <?= $wirausaha_pct ?>%"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small text-muted">Melanjutkan Studi</span>
                            <span class="small fw-bold"><?= $melanjutkan_pct ?>%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: <?= $melanjutkan_pct ?>%"></div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="text-center">
                        <h6 class="text-muted mb-2">Response Rate Survei</h6>
                        <h2 class="fw-bold text-primary"><?= $response_rate ?>%</h2>
                        <small class="text-muted">Dari total survei yang ditujukan ke prodi ini</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-purple {
    background-color: #6f42c1 !important;
}
.card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}
.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}
</style>
