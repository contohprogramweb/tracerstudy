<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?? 'Gap Analysis CPL' ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/fontawesome/css/all.min.css') ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .card { box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .gap-high { background-color: #f8d7da !important; }
        .gap-medium { background-color: #fff3cd !important; }
        .gap-low { background-color: #d4edda !important; }
        .chart-container { position: relative; height: 400px; }
    </style>
</head>
<body>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-chart-line"></i> <?= $title ?></h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= site_url('kurikulum') ?>">Kurikulum & CPL</a></li>
                    <li class="breadcrumb-item active">Gap Analysis</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
            <h5 class="mb-0">Gap Analysis CPL vs Industri (Tahun <?= $tahun ?>)</h5>
            <a href="<?= site_url('gap_analysis/rekomendasi?prodi_id='.$prodi_id.'&tahun='.$tahun) ?>" class="btn btn-light btn-sm">
                <i class="fas fa-lightbulb"></i> Lihat Rekomendasi
            </a>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <strong>Metodologi (KUR-003):</strong> Skor Realisasi = (60% Stakeholder + 40% Alumni). 
                Gap = Target Industri - Skor Realisasi.
                <br><br>
                <span class="badge badge-danger">Gap > 0.5</span> Perlu Perbaikan Mendesak
                <span class="badge badge-warning ml-2">Gap 0 - 0.5</span> Perlu Perbaikan
                <span class="badge badge-success ml-2">Gap ≤ 0</span> Sesuai Target
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover text-center">
                    <thead class="thead-light">
                        <tr>
                            <th class="text-left">Kode CPL</th>
                            <th>Aspek</th>
                            <th>Target Industri</th>
                            <th>Skor Alumni (40%)</th>
                            <th>Skor Stakeholder (60%)</th>
                            <th>Skor Gabungan</th>
                            <th>Gap Value</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $high_gap = 0;
                        $medium_gap = 0;
                        $low_gap = 0;
                        
                        foreach($gap_data as $item): 
                            $bg_class = 'gap-low';
                            if ($item['gap'] > 0.5) {
                                $bg_class = 'gap-high';
                                $high_gap++;
                            } elseif ($item['gap'] > 0) {
                                $bg_class = 'gap-medium';
                                $medium_gap++;
                            } else {
                                $low_gap++;
                            }
                        ?>
                        <tr class="<?= $bg_class ?>">
                            <td class="text-left font-weight-bold"><?= $item['kode_cpl'] ?></td>
                            <td><?= ucfirst(str_replace('_', ' ', $item['aspect'])) ?></td>
                            <td><?= number_format($item['target'], 2) ?></td>
                            <td><?= number_format($item['alumni_score'], 2) ?></td>
                            <td><?= number_format($item['stakeholder_score'], 2) ?></td>
                            <td class="font-weight-bold"><?= number_format($item['combined_score'], 2) ?></td>
                            <td class="font-weight-bold text-<?= $item['gap'] > 0 ? 'danger' : 'success' ?>">
                                <?= ($item['gap'] > 0 ? '+' : '') . number_format($item['gap'], 2) ?>
                            </td>
                            <td><small><?= $item['status'] ?></small></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="thead-light">
                        <tr>
                            <td colspan="2"><strong>Summary</strong></td>
                            <td colspan="6">
                                <span class="badge badge-danger"><?= $high_gap ?> Mendesak</span>
                                <span class="badge badge-warning"><?= $medium_gap ?> Perlu Perbaikan</span>
                                <span class="badge badge-success"><?= $low_gap ?> Sesuai</span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-12">
                    <h5 class="mb-3">Visualisasi Gap: Target vs Realisasi</h5>
                    <div class="chart-container">
                        <canvas id="gapChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-12 text-right">
                    <a href="<?= site_url('gap_analysis/exportPdf/'.$prodi_id.'?tahun='.$tahun) ?>" class="btn btn-danger">
                        <i class="fas fa-file-pdf"></i> Export PDF
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const ctx = document.getElementById('gapChart').getContext('2d');
    const data = <?= json_encode($gap_data) ?>;
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.map(d => d.kode_cpl),
            datasets: [
                {
                    label: 'Target Industri',
                    data: data.map(d => d.target),
                    backgroundColor: '#3498db',
                    order: 2
                },
                {
                    label: 'Realisasi Lulusan',
                    data: data.map(d => d.combined_score),
                    backgroundColor: '#2ecc71',
                    order: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { 
                    beginAtZero: true, 
                    max: 5, 
                    title: { display: true, text: 'Skala 1-5' } 
                }
            },
            plugins: {
                title: { display: true, text: 'Perbandingan Target Industri vs Capaian Lulusan' },
                tooltip: {
                    callbacks: {
                        afterBody: function(context) {
                            const idx = context[0].dataIndex;
                            const gap = data[idx].gap;
                            return 'Gap: ' + (gap > 0 ? '+' : '') + gap.toFixed(2);
                        }
                    }
                }
            }
        }
    });
</script>

</body>
</html>
