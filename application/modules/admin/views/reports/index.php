<div class="container-fluid py-4">
    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
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
        
        <div class="col-md-4">
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
        
        <div class="col-md-4">
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
    
    <!-- Alumni by Graduation Year -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Alumni Berdasarkan Tahun Lulus</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($alumni_by_year)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tahun Lulus</th>
                                    <th class="text-end">Jumlah Alumni</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($alumni_by_year as $year): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($year['graduation_year'] ?? '-') ?></strong></td>
                                    <td class="text-end"><span class="badge bg-primary"><?= number_format($year['total']) ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-inbox text-muted fs-1"></i>
                        <p class="text-muted mt-2">Belum ada data alumni</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Survey Response Statistics -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-pie-chart me-2"></i>Respons per Survei</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($survey_responses)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Judul Survei</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-end">Respons</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($survey_responses as $sr): ?>
                                <tr>
                                    <td>
                                        <a href="<?= base_url('admin/reports/detail/' . $sr['id']) ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($sr['title'] ?? '-') ?>
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($sr['status'] == 'active'): ?>
                                            <span class="badge bg-success">Aktif</span>
                                        <?php elseif ($sr['status'] == 'draft'): ?>
                                            <span class="badge bg-warning">Draft</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?= htmlspecialchars($sr['status']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end"><span class="badge bg-info"><?= number_format($sr['response_count']) ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-inbox text-muted fs-1"></i>
                        <p class="text-muted mt-2">Belum ada survei</p>
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
                        <div class="col-md-4">
                            <a href="<?= base_url('admin/surveys') ?>" class="btn btn-outline-primary w-100 py-3">
                                <i class="bi bi-card-list fs-4 d-block mb-2"></i>
                                Kelola Survei
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="<?= base_url('admin/alumni') ?>" class="btn btn-outline-success w-100 py-3">
                                <i class="bi bi-people fs-4 d-block mb-2"></i>
                                Data Alumni
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="<?= base_url('admin/dashboard') ?>" class="btn btn-outline-secondary w-100 py-3">
                                <i class="bi bi-arrow-left-circle fs-4 d-block mb-2"></i>
                                Kembali ke Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
