<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - <?= $prodi['name'] ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <style>
        .gap-analysis-card {
            border-radius: 15px;
            margin-bottom: 2rem;
        }
        
        .cpl-bar-container {
            margin-bottom: 1.5rem;
        }
        
        .cpl-label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            display: flex;
            justify-content: space-between;
        }
        
        .progress-stacked {
            height: 25px;
            border-radius: 15px;
        }
        
        .bar-stakeholder {
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        }
        
        .bar-alumni {
            background: linear-gradient(90deg, #11998e 0%, #38ef7d 100%);
        }
        
        .gap-indicator {
            font-size: 0.85rem;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
        }
        
        .gap-positive {
            background-color: #d4edda;
            color: #155724;
        }
        
        .gap-negative {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .gap-neutral {
            background-color: #e2e3e5;
            color: #383d41;
        }
        
        .recommendation-box {
            background-color: #f8f9fa;
            border-left: 4px solid #ffc107;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 5px;
        }
        
        .stats-summary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        @media (max-width: 768px) {
            .cpl-label {
                flex-direction: column;
            }
            
            .gap-indicator {
                margin-top: 0.5rem;
            }
        }
    </style>
</head>
<body class="bg-light">

<div class="container py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= site_url('prodi') ?>">Program Studi</a></li>
                    <li class="breadcrumb-item active"><?= $prodi['name'] ?></li>
                </ol>
            </nav>
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-chart-line"></i> Gap Analysis CPL</h2>
                <a href="<?= site_url('stakeholder_survey/listByProdi/' . $prodi_id) ?>" class="btn btn-outline-primary">
                    <i class="fas fa-list"></i> Lihat Detail Survey
                </a>
            </div>
            <p class="text-muted mb-0">Program Studi: <strong><?= $prodi['name'] ?></strong></p>
        </div>
    </div>
    
    <!-- Statistics Summary -->
    <div class="stats-summary">
        <div class="row text-center">
            <div class="col-md-3 col-6 mb-3 mb-md-0">
                <h2 class="mb-0"><?= isset($summary['total_surveys']) ? $summary['total_surveys'] : 0 ?></h2>
                <small>Total Survey Stakeholder</small>
            </div>
            <div class="col-md-3 col-6 mb-3 mb-md-0">
                <h2 class="mb-0"><?= isset($summary['total_stakeholders']) ? $summary['total_stakeholders'] : 0 ?></h2>
                <small>Stakeholder Terlibat</small>
            </div>
            <div class="col-md-3 col-6">
                <h2 class="mb-0"><?= isset($summary['total_alumni']) ? $summary['total_alumni'] : 0 ?></h2>
                <small>Alumni Dinilai</small>
            </div>
            <div class="col-md-3 col-6">
                <h2 class="mb-0"><?= number_format(isset($summary['avg_rating']) ? $summary['avg_rating'] : 0, 2) ?></h2>
                <small>Rata-rata Nilai</small>
            </div>
        </div>
    </div>
    
    <!-- Gap Analysis Chart -->
    <div class="card gap-analysis-card shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Perbandingan Rating per CPL</h5>
            <small class="text-muted">Perbandingan penilaian stakeholder vs alumni self-assessment (Ratio 60:40)</small>
        </div>
        <div class="card-body">
            <canvas id="gapChart" height="100"></canvas>
        </div>
    </div>
    
    <!-- Detailed CPL Analysis -->
    <div class="card gap-analysis-card shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="mb-0"><i class="fas fa-tasks"></i> Analisis Detail per CPL</h5>
        </div>
        <div class="card-body">
            <?php if (empty($combined_averages)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Belum ada data untuk ditampilkan.
                </div>
            <?php else: ?>
                <?php foreach ($combined_averages as $cpl): ?>
                    <div class="cpl-bar-container">
                        <div class="cpl-label">
                            <span><?= htmlspecialchars($cpl['cpl_code']) ?> - <?= htmlspecialchars(substr($cpl['cpl_description'], 0, 80)) ?><?= strlen($cpl['cpl_description']) > 80 ? '...' : '' ?></span>
                            <?php 
                                $gap_class = 'gap-neutral';
                                $gap_text = 'Seimbang';
                                if ($cpl['gap'] > 0.3) {
                                    $gap_class = 'gap-positive';
                                    $gap_text = 'Ekspektasi > Realita';
                                } elseif ($cpl['gap'] < -0.3) {
                                    $gap_class = 'gap-negative';
                                    $gap_text = 'Realita > Ekspektasi';
                                }
                            ?>
                            <span class="gap-indicator <?= $gap_class ?>">
                                <i class="fas fa-<?= $cpl['gap'] > 0.3 ? 'arrow-up' : ($cpl['gap'] < -0.3 ? 'arrow-down' : 'equals') ?>"></i>
                                Gap: <?= number_format($cpl['gap'], 2) ?>
                            </span>
                        </div>
                        
                        <div class="progress progress-stacked">
                            <div class="progress-bar bar-stakeholder" role="progressbar" 
                                 style="width: <?= ($cpl['stakeholder_avg'] / 5) * 100 ?>%"
                                 title="Stakeholder: <?= $cpl['stakeholder_avg'] ?>">
                                <small class="text-white">Stakeholder: <?= $cpl['stakeholder_avg'] ?></small>
                            </div>
                            <div class="progress-bar bar-alumni" role="progressbar"
                                 style="width: <?= ($cpl['alumni_avg'] / 5) * 100 ?>%"
                                 title="Alumni: <?= $cpl['alumni_avg'] ?>">
                                <small class="text-white">Alumni: <?= $cpl['alumni_avg'] ?></small>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-2">
                            <small class="text-muted">Combined (60:40): <strong><?= $cpl['combined_avg'] ?></strong></small>
                            <small class="text-muted">Target: 3.5</small>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Recommendations Summary -->
    <div class="card gap-analysis-card shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="mb-0"><i class="fas fa-lightbulb"></i> Rekomendasi dari Stakeholder</h5>
        </div>
        <div class="card-body">
            <?php if (empty($recommendations)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Belum ada rekomendasi dari stakeholder.
                </div>
            <?php else: ?>
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="border-bottom pb-2"><i class="fas fa-plus-circle text-success"></i> Kompetensi yang Direkomendasikan</h6>
                        <?php 
                        $competencies = [];
                        foreach ($recommendations as $rec) {
                            if (!empty($rec['recommended_competencies'])) {
                                $competencies[] = $rec['recommended_competencies'];
                            }
                        }
                        ?>
                        <?php if (!empty($competencies)): ?>
                            <div class="recommendation-box">
                                <ul class="mb-0">
                                    <?php foreach (array_slice(array_unique($competencies), 0, 10) as $comp): ?>
                                        <li class="mb-2"><?= htmlspecialchars($comp) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">Tidak ada rekomendasi spesifik.</p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="col-md-6">
                        <h6 class="border-bottom pb-2"><i class="fas fa-edit text-primary"></i> Saran Perbaikan Kurikulum</h6>
                        <?php 
                        $suggestions = [];
                        foreach ($recommendations as $rec) {
                            if (!empty($rec['curriculum_suggestions'])) {
                                $suggestions[] = $rec['curriculum_suggestions'];
                            }
                        }
                        ?>
                        <?php if (!empty($suggestions)): ?>
                            <div class="recommendation-box">
                                <ul class="mb-0">
                                    <?php foreach (array_slice(array_unique($suggestions), 0, 10) as $sug): ?>
                                        <li class="mb-2"><?= htmlspecialchars($sug) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">Tidak ada saran spesifik.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Kesesuaian Distribution -->
    <div class="card gap-analysis-card shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="mb-0"><i class="fas fa-pie-chart"></i> Distribusi Kesesuaian CPL</h5>
        </div>
        <div class="card-body">
            <canvas id="kesesuaianChart" height="80"></canvas>
        </div>
    </div>
    
    <!-- Action Buttons -->
    <div class="d-flex justify-content-end gap-2 mt-4">
        <button onclick="window.print()" class="btn btn-outline-secondary">
            <i class="fas fa-print"></i> Cetak Laporan
        </button>
        <a href="<?= site_url('stakeholder_survey/export/' . $prodi_id) ?>" class="btn btn-success">
            <i class="fas fa-file-excel"></i> Export Excel
        </a>
    </div>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Gap Analysis Chart
const ctx = document.getElementById('gapChart').getContext('2d');
const gapChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: [<?php foreach ($combined_averages as $cpl): ?>'<?= htmlspecialchars($cpl['cpl_code']) ?>',<?php endforeach; ?>],
        datasets: [
            {
                label: 'Penilaian Stakeholder',
                data: [<?php foreach ($combined_averages as $cpl): ?><?= $cpl['stakeholder_avg'] ?>,<?php endforeach; ?>],
                backgroundColor: 'rgba(102, 126, 234, 0.8)',
                borderColor: 'rgba(102, 126, 234, 1)',
                borderWidth: 1
            },
            {
                label: 'Self-Assessment Alumni',
                data: [<?php foreach ($combined_averages as $cpl): ?><?= $cpl['alumni_avg'] ?>,<?php endforeach; ?>],
                backgroundColor: 'rgba(17, 153, 142, 0.8)',
                borderColor: 'rgba(17, 153, 142, 1)',
                borderWidth: 1
            },
            {
                label: 'Combined (60:40)',
                data: [<?php foreach ($combined_averages as $cpl): ?><?= $cpl['combined_avg'] ?>,<?php endforeach; ?>],
                backgroundColor: 'rgba(255, 159, 64, 0.8)',
                borderColor: 'rgba(255, 159, 64, 1)',
                borderWidth: 1
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        scales: {
            y: {
                beginAtZero: true,
                max: 5,
                ticks: {
                    stepSize: 1
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': ' + context.parsed.y.toFixed(2);
                    }
                }
            }
        }
    }
});

// Kesesuaian Distribution Chart
const kesesuaianCtx = document.getElementById('kesesuaianChart').getContext('2d');
const kesesuaianChart = new Chart(kesesuaianCtx, {
    type: 'doughnut',
    data: {
        labels: ['Sangat Sesuai', 'Sesuai', 'Kurang Sesuai', 'Tidak Sesuai'],
        datasets: [{
            data: [
                <?= isset($kesesuaian_distribution['sangat_sesuai']) ? $kesesuaian_distribution['sangat_sesuai'] : 0 ?>,
                <?= isset($kesesuaian_distribution['sesuai']) ? $kesesuaian_distribution['sesuai'] : 0 ?>,
                <?= isset($kesesuaian_distribution['kurang']) ? $kesesuaian_distribution['kurang'] : 0 ?>,
                <?= isset($kesesuaian_distribution['tidak_sesuai']) ? $kesesuaian_distribution['tidak_sesuai'] : 0 ?>
            ],
            backgroundColor: [
                'rgba(40, 167, 69, 0.8)',
                'rgba(255, 193, 7, 0.8)',
                'rgba(253, 126, 20, 0.8)',
                'rgba(220, 53, 69, 0.8)'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>

</body>
</html>
