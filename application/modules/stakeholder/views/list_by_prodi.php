<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Survey Stakeholder per Prodi' ?> - Alumni System</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/font-awesome.min.css') ?>">
</head>
<body>
    <div class="container-fluid mt-4">
        <h2><i class="fa fa-university"></i> <?= $page_title ?></h2>
        <p class="text-muted">Program Studi: <strong><?= $prodi['name'] ?></strong></p>
        <hr>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <?= form_open('', ['method' => 'get', 'class' => 'form-inline']) ?>
                <label class="mr-2">Tahun:</label>
                <select name="year" class="form-control mr-3">
                    <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                        <option value="<?= $y ?>" <?= $filter_year == $y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
                <label class="mr-2">Status:</label>
                <select name="status" class="form-control mr-3">
                    <option value="all" <?= $filter_status == 'all' ? 'selected' : '' ?>>Semua</option>
                    <option value="completed" <?= $filter_status == 'completed' ? 'selected' : '' ?>>Completed</option>
                    <option value="pending" <?= $filter_status == 'pending' ? 'selected' : '' ?>>Pending</option>
                </select>
                <button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i> Filter</button>
                <?= form_close() ?>
            </div>
        </div>

        <!-- Summary Statistics -->
        <div class="row mb-4">
            <div class="col-md-2">
                <div class="card bg-primary text-white text-center">
                    <div class="card-body">
                        <h3><?= $summary['total_surveys'] ?? 0 ?></h3>
                        <small>Total Survey</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-success text-white text-center">
                    <div class="card-body">
                        <h3><?= number_format($summary['avg_rating'] ?? 0, 2) ?></h3>
                        <small>Avg Rating</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-info text-white text-center">
                    <div class="card-body">
                        <h3><?= $summary['total_stakeholders'] ?? 0 ?></h3>
                        <small>Stakeholder</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-warning text-white text-center">
                    <div class="card-body">
                        <h3><?= $summary['total_alumni'] ?? 0 ?></h3>
                        <small>Alumni Dinilai</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-dark text-white text-center">
                    <div class="card-body">
                        <a href="<?= site_url('stakeholder/gapAnalysis/' . $prodi_id) ?>" class="btn btn-light btn-sm">
                            <i class="fa fa-chart-line"></i> Lihat Gap Analysis
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- CPL Averages -->
        <div class="card shadow mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fa fa-star"></i> Rata-rata Penilaian per CPL</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="bg-light">
                            <tr>
                                <th>Kode CPL</th>
                                <?php foreach ($cpl_list as $cpl): ?>
                                    <th><?= $cpl['code'] ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Rating</strong></td>
                                <?php foreach ($cpl_list as $cpl): ?>
                                    <td class="text-center">
                                        <strong><?= isset($cpl_averages[$cpl['id']]) ? number_format($cpl_averages[$cpl['id']], 2) : '0.00' ?></strong>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Survey List -->
        <div class="card shadow">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fa fa-list"></i> Daftar Survey</h5>
            </div>
            <div class="card-body">
                <?php if (empty($surveys)): ?>
                    <p class="text-muted text-center">Belum ada survey stakeholder.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Alumni</th>
                                    <th>Angkatan</th>
                                    <th>Perusahaan</th>
                                    <th>Penilai</th>
                                    <th>Posisi</th>
                                    <th>Rating</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($surveys as $survey): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($survey['submitted_at'])) ?></td>
                                    <td><?= $survey['alumni_name'] ?></td>
                                    <td><?= $survey['graduation_year'] ?></td>
                                    <td><?= $survey['company_name'] ?></td>
                                    <td><?= $survey['stakeholder_name'] ?></td>
                                    <td><?= $survey['work_position'] ?></td>
                                    <td>
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fa fa-star <?= $i <= round($survey['average_rating']) ? 'text-warning' : 'text-muted' ?>" style="font-size:10px;"></i>
                                        <?php endfor; ?>
                                        <?= number_format($survey['average_rating'], 1) ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $survey['status'] == 'completed' ? 'success' : 'warning' ?>">
                                            <?= ucfirst($survey['status']) ?>
                                        </span>
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
            <a href="<?= site_url('prodi/dashboard') ?>" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
    <script src="<?= base_url('assets/js/jquery.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>"></script>
</body>
</html>
