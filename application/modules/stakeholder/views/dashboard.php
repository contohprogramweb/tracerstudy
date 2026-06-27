<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Dashboard Stakeholder' ?> - Alumni System</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/font-awesome.min.css') ?>">
    <style>
        .stat-card { border-radius: 10px; padding: 20px; margin-bottom: 20px; }
        .stat-card h3 { font-size: 2rem; margin: 10px 0; }
        .bg-gradient-primary { background: linear-gradient(45deg, #4e73df, #224abe); color: white; }
        .bg-gradient-success { background: linear-gradient(45deg, #1cc88a, #13855c); color: white; }
        .bg-gradient-info { background: linear-gradient(45deg, #36b9cc, #258391); color: white; }
        .bg-gradient-warning { background: linear-gradient(45deg, #f6c23e, #dda20a); color: white; }
        .company-profile { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Sidebar/Company Profile -->
            <div class="col-md-3">
                <div class="company-profile card shadow">
                    <div class="card-body text-center">
                        <i class="fa fa-building fa-4x text-primary mb-3"></i>
                        <h5><?= $stakeholder['company_name'] ?></h5>
                        <p class="text-muted"><?= $stakeholder['industry'] ?></p>
                        <hr>
                        <p class="mb-1"><i class="fa fa-user"></i> <?= $stakeholder['contact_person'] ?></p>
                        <p class="mb-1"><i class="fa fa-envelope"></i> <?= $stakeholder['email'] ?></p>
                        <p class="mb-1"><i class="fa fa-phone"></i> <?= $stakeholder['phone'] ?></p>
                        <span class="badge badge-<?= $stakeholder['status'] == 'active' ? 'success' : 'warning' ?>">
                            <?= ucfirst($stakeholder['status']) ?>
                        </span>
                    </div>
                </div>

                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="fa fa-bars"></i> Menu</h6>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="<?= site_url('stakeholder/dashboard') ?>" class="list-group-item list-group-item-action active">
                            <i class="fa fa-dashboard"></i> Dashboard
                        </a>
                        <a href="<?= site_url('stakeholder/survey_list') ?>" class="list-group-item list-group-item-action">
                            <i class="fa fa-list"></i> Riwayat Penilaian
                        </a>
                        <a href="<?= site_url('stakeholder/profile') ?>" class="list-group-item list-group-item-action">
                            <i class="fa fa-user"></i> Profil Saya
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <h2 class="mb-4"><i class="fa fa-dashboard"></i> <?= $page_title ?></h2>

                <!-- Statistics Cards -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="stat-card bg-gradient-primary shadow">
                            <h6>Total Penilaian</h6>
                            <h3><?= $stats['total_surveys'] ?></h3>
                            <i class="fa fa-file-text fa-2x opacity-50"></i>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card bg-gradient-warning shadow">
                            <h6>Penilaian Pending</h6>
                            <h3><?= $stats['pending_surveys'] ?></h3>
                            <i class="fa fa-clock-o fa-2x opacity-50"></i>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card bg-gradient-success shadow">
                            <h6>Selesai Dinilai</h6>
                            <h3><?= $stats['completed_surveys'] ?></h3>
                            <i class="fa fa-check-circle fa-2x opacity-50"></i>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card bg-gradient-info shadow">
                            <h6>Alumni Dinilai</h6>
                            <h3><?= $stats['total_alumni_assessed'] ?></h3>
                            <i class="fa fa-users fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>

                <!-- Recent Surveys -->
                <div class="card shadow mt-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fa fa-history"></i> Penilaian Terakhir</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recent_surveys)): ?>
                            <p class="text-muted text-center">Belum ada penilaian.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Nama Alumni</th>
                                            <th>Prodi</th>
                                            <th>Posisi Kerja</th>
                                            <th>Rating</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_surveys as $survey): ?>
                                        <tr>
                                            <td><?= date('d/m/Y', strtotime($survey['submitted_at'])) ?></td>
                                            <td><?= $survey['alumni_name'] ?></td>
                                            <td><?= $survey['prodi_name'] ?></td>
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
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                        <div class="text-right mt-3">
                            <a href="<?= site_url('stakeholder/survey_list') ?>" class="btn btn-sm btn-outline-primary">
                                Lihat Semua <i class="fa fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Pending Invitations -->
                <?php if (!empty($pending_invitations)): ?>
                <div class="card shadow mt-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fa fa-envelope"></i> Undangan Penilaian Pending</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Tanggal Kirim</th>
                                        <th>Nama Alumni</th>
                                        <th>NIM</th>
                                        <th>Prodi</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pending_invitations as $invitation): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($invitation['sent_at'])) ?></td>
                                        <td><?= $invitation['alumni_name'] ?></td>
                                        <td><?= $invitation['nim'] ?></td>
                                        <td><?= $invitation['prodi_name'] ?></td>
                                        <td>
                                            <a href="<?= site_url('stakeholder/survey/' . $invitation['alumni_id']) ?>" 
                                               class="btn btn-sm btn-primary">
                                                <i class="fa fa-edit"></i> Isi Penilaian
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="<?= base_url('assets/js/jquery.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>"></script>
</body>
</html>
