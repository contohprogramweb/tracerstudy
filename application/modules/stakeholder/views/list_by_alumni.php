<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Riwayat Penilaian Alumni' ?> - Alumni System</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/font-awesome.min.css') ?>">
</head>
<body>
    <div class="container mt-4">
        <h2><i class="fa fa-history"></i> <?= $page_title ?></h2>
        <p class="text-muted">Alumni: <strong><?= $alumni['name'] ?> (<?= $alumni['nim'] ?>)</strong></p>
        <hr>

        <!-- Summary Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h3><?= $summary['total_surveys'] ?? 0 ?></h3>
                        <small>Total Penilaian</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h3><?= number_format($summary['avg_rating'] ?? 0, 2) ?></h3>
                        <small>Rata-rata Rating</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h3><?= $summary['total_stakeholders'] ?? 0 ?></h3>
                        <small>Stakeholder Penilai</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body text-center">
                        <h3><?= $summary['min_rating'] ?? 0 ?> - <?= $summary['max_rating'] ?? 0 ?></h3>
                        <small>Range Rating</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Survey List -->
        <div class="card shadow">
            <div class="card-header bg-white">
                <h5 class="mb-0">Daftar Penilaian</h5>
            </div>
            <div class="card-body">
                <?php if (empty($surveys)): ?>
                    <p class="text-muted text-center">Belum ada penilaian dari stakeholder.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Perusahaan</th>
                                    <th>Penilai</th>
                                    <th>Posisi</th>
                                    <th>Rating</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($surveys as $survey): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($survey['submitted_at'])) ?></td>
                                    <td><?= $survey['company_name'] ?></td>
                                    <td><?= $survey['stakeholder_name'] ?></td>
                                    <td><?= $survey['work_position'] ?></td>
                                    <td>
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fa fa-star <?= $i <= round($survey['average_rating']) ? 'text-warning' : 'text-muted' ?>"></i>
                                        <?php endfor; ?>
                                        <?= number_format($survey['average_rating'], 1) ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $survey['status'] == 'completed' ? 'success' : 'warning' ?>">
                                            <?= ucfirst($survey['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?= site_url('stakeholder_survey/detail/' . $survey['id']) ?>" class="btn btn-sm btn-info">
                                            <i class="fa fa-eye"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="mt-4">
            <a href="<?= site_url('alumni/dashboard') ?>" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
    <script src="<?= base_url('assets/js/jquery.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>"></script>
</body>
</html>
