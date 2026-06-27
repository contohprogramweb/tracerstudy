<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <style>
        .dashboard-card {
            border-radius: 15px;
            transition: transform 0.3s ease;
            margin-bottom: 1.5rem;
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .stat-card.green {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }
        
        .stat-card.orange {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .stat-card.blue {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        .stat-icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
        }
        
        .invitation-card {
            border-left: 4px solid #667eea;
            margin-bottom: 1rem;
        }
        
        .recent-survey-item {
            border-bottom: 1px solid #eee;
            padding: 1rem 0;
        }
        
        .recent-survey-item:last-child {
            border-bottom: none;
        }
        
        @media (max-width: 768px) {
            .stat-number {
                font-size: 1.5rem;
            }
            
            .stat-icon {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body class="bg-light">

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-tachometer-alt"></i> <?= $page_title ?></h2>
                <a href="<?= site_url('stakeholder/survey/create') ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Buat Penilaian Baru
                </a>
            </div>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Total Survey</h6>
                        <div class="stat-number"><?= $stats['total_surveys'] ?></div>
                    </div>
                    <i class="fas fa-clipboard-list stat-icon"></i>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6">
            <div class="stat-card green">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Selesai</h6>
                        <div class="stat-number"><?= $stats['completed_surveys'] ?></div>
                    </div>
                    <i class="fas fa-check-circle stat-icon"></i>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6">
            <div class="stat-card orange">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Pending</h6>
                        <div class="stat-number"><?= $stats['pending_surveys'] ?></div>
                    </div>
                    <i class="fas fa-clock stat-icon"></i>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6">
            <div class="stat-card blue">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Alumni Dinilai</h6>
                        <div class="stat-number"><?= $stats['total_alumni_assessed'] ?></div>
                    </div>
                    <i class="fas fa-users stat-icon"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <!-- Left Column: Recent Surveys -->
        <div class="col-lg-8">
            <div class="card dashboard-card shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="fas fa-history"></i> Survey Terakhir</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_surveys)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p>Belum ada survey yang diisi</p>
                            <a href="<?= site_url('stakeholder/survey/create') ?>" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-plus"></i> Mulai Isi Survey
                            </a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recent_surveys as $survey): ?>
                            <div class="recent-survey-item">
                                <div class="row align-items-center">
                                    <div class="col-md-4">
                                        <h6 class="mb-1"><?= htmlspecialchars($survey['alumni_name']) ?></h6>
                                        <small class="text-muted"><?= htmlspecialchars($survey['prodi_name']) ?></small>
                                    </div>
                                    <div class="col-md-3">
                                        <span class="badge bg-<?= $survey['status'] == 'completed' ? 'success' : 'warning' ?>">
                                            <?= ucfirst($survey['status']) ?>
                                        </span>
                                    </div>
                                    <div class="col-md-3">
                                        <?php if ($survey['average_rating']): ?>
                                            <div class="rating-stars text-warning">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star<?= $i <= round($survey['average_rating']) ? '' : '-o' ?>"></i>
                                                <?php endfor; ?>
                                                <small class="text-muted ms-1">(<?= number_format($survey['average_rating'], 1) ?>)</small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-2 text-end">
                                        <small class="text-muted"><?= date('d/m/Y', strtotime($survey['submitted_at'])) ?></small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Company Profile Summary -->
            <div class="card dashboard-card shadow-sm mt-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="fas fa-building"></i> Profil Perusahaan</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td width="30%"><strong>Nama Perusahaan:</strong></td>
                                    <td><?= htmlspecialchars($stakeholder['company_name']) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Jenis:</strong></td>
                                    <td><?= strtoupper($stakeholder['company_type']) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Industri:</strong></td>
                                    <td><?= ucfirst($stakeholder['industry']) ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td width="30%"><strong>Contact Person:</strong></td>
                                    <td><?= htmlspecialchars($stakeholder['contact_person']) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td><?= htmlspecialchars($stakeholder['email']) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <span class="badge bg-<?= $stakeholder['status'] == 'active' ? 'success' : 'warning' ?>">
                                            <?= ucfirst($stakeholder['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="<?= site_url('stakeholder/profile/edit') ?>" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-edit"></i> Edit Profil
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right Column: Pending Invitations -->
        <div class="col-lg-4">
            <div class="card dashboard-card shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="fas fa-envelope"></i> Undangan Pending</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($pending_invitations)): ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-mail-bulk fa-2x mb-2"></i>
                            <p class="mb-0">Tidak ada undangan pending</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($pending_invitations as $invitation): ?>
                            <div class="card invitation-card shadow-sm">
                                <div class="card-body p-3">
                                    <h6 class="mb-1"><?= htmlspecialchars($invitation['alumni_name']) ?></h6>
                                    <p class="mb-1 small text-muted"><?= htmlspecialchars($invitation['prodi_name']) ?></p>
                                    <p class="mb-2 small text-muted">NIM: <?= htmlspecialchars($invitation['alumni_nim']) ?></p>
                                    <div class="d-grid">
                                        <a href="<?= site_url('stakeholder/survey/' . $invitation['alumni_id']) ?>" 
                                           class="btn btn-primary btn-sm">
                                            <i class="fas fa-pen"></i> Isi Penilaian
                                        </a>
                                    </div>
                                    <small class="text-muted">
                                        <i class="far fa-calendar"></i> <?= date('d/m/Y', strtotime($invitation['sent_at'])) ?>
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="card dashboard-card shadow-sm mt-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="fas fa-bolt"></i> Aksi Cepat</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= site_url('stakeholder/survey/create') ?>" class="btn btn-outline-primary">
                            <i class="fas fa-plus-circle"></i> Buat Penilaian Baru
                        </a>
                        <a href="<?= site_url('stakeholder/profile') ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-user-circle"></i> Lihat Profil
                        </a>
                        <a href="<?= site_url('help/stakeholder') ?>" class="btn btn-outline-info">
                            <i class="fas fa-question-circle"></i> Bantuan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
