<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?? 'Rekomendasi Perbaikan Kurikulum' ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/fontawesome/css/all.min.css') ?>">
    <style>
        .card { box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .priority-high { border-left: 5px solid #dc3545; }
        .priority-medium { border-left: 5px solid #ffc107; }
        .priority-low { border-left: 5px solid #28a745; }
    </style>
</head>
<body>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-lightbulb"></i> <?= $title ?></h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= site_url('kurikulum') ?>">Kurikulum & CPL</a></li>
                    <li class="breadcrumb-item"><a href="<?= site_url('gap_analysis?prodi_id='.$prodi_id) ?>">Gap Analysis</a></li>
                    <li class="breadcrumb-item active">Rekomendasi</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Ringkasan Gap</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <h6>Tahun Analisis</h6>
                        <h3><?= $tahun ?></h3>
                    </div>
                    
                    <?php 
                    $high = 0; $medium = 0; $low = 0;
                    foreach($gap_summary as $item):
                        if ($item['gap'] > 0.5) $high++;
                        elseif ($item['gap'] > 0) $medium++;
                        else $low++;
                    endforeach;
                    ?>
                    
                    <div class="list-group">
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            Perlu Perbaikan Mendesak
                            <span class="badge badge-danger badge-pill"><?= $high ?></span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            Perlu Perbaikan
                            <span class="badge badge-warning badge-pill"><?= $medium ?></span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            Sesuai Target
                            <span class="badge badge-success badge-pill"><?= $low ?></span>
                        </div>
                    </div>
                    
                    <div class="mt-3 text-center">
                        <a href="<?= site_url('gap_analysis?prodi_id='.$prodi_id.'&tahun='.$tahun) ?>" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali ke Gap Analysis
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Rekomendasi Perbaikan Kurikulum</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recommendations)): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> Tidak ada rekomendasi khusus. Semua CPL sudah memenuhi target!
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Rekomendasi ini dihasilkan berdasarkan analisis gap antara target industri dan capaian lulusan (data tracer study & stakeholder survey).
                        </div>
                        
                        <div class="timeline">
                            <?php foreach($recommendations as $rec): ?>
                                <div class="card mb-3 priority-<?= strtolower($rec['priority']) ?>">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="mb-0">
                                                <i class="fas fa-bullseye"></i> CPL <?= $rec['cpl'] ?>
                                            </h6>
                                            <span class="badge badge-<?= $rec['priority'] == 'HIGH' ? 'danger' : ($rec['priority'] == 'MEDIUM' ? 'warning' : 'success') ?>">
                                                <?= $rec['priority'] ?>
                                            </span>
                                        </div>
                                        <p class="mb-0"><?= $rec['text'] ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="mt-4 p-3 bg-light rounded">
                            <h6><i class="fas fa-tasks"></i> Tindak Lanjut yang Disarankan:</h6>
                            <ol class="mb-0">
                                <li>Form tim review kurikulum untuk CPL dengan prioritas HIGH</li>
                                <li>Lakukan FGD dengan stakeholder industri untuk CPL terkait</li>
                                <li>Review RPS dan materi pembelajaran</li>
                                <li>Jadwalkan implementasi perubahan pada semester berikutnya</li>
                                <li>Monitor dampak perubahan melalui tracer study periode selanjutnya</li>
                            </ol>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mt-3 text-right">
                        <button onclick="window.print()" class="btn btn-secondary">
                            <i class="fas fa-print"></i> Cetak
                        </button>
                        <a href="<?= site_url('gap_analysis/exportPdf/'.$prodi_id.'?tahun='.$tahun) ?>" class="btn btn-danger">
                            <i class="fas fa-file-pdf"></i> Export PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/js/jquery.min.js') ?>"></script>
<script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>"></script>
</body>
</html>
