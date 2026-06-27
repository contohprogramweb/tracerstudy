<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @media print {
            .no-print, .btn-export { display: none !important; }
            body { background: white; }
        }
        
        .filter-section {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .card {
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .card-header {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            font-weight: 600;
        }
        
        .iku-score {
            font-size: 3rem;
            font-weight: bold;
        }
        
        .score-good { color: #2ecc71; }
        .score-warning { color: #f39c12; }
        .score-bad { color: #e74c3c; }
    </style>
</head>
<body>

<div class="container-fluid mt-4">
    <div class="row mb-4 no-print">
        <div class="col-md-12">
            <h2><i class="fa fa-star"></i> <?= $title ?></h2>
            <p class="text-muted">Laporan Indikator Kinerja Utama (IKU-1) - Lulusan Mendapatkan Pekerjaan yang Layak</p>
        </div>
    </div>

    <!-- Filter -->
    <div class="row mb-4 no-print">
        <div class="col-md-12">
            <form method="GET" class="filter-section">
                <div class="form-row align-items-end">
                    <div class="col-md-2">
                        <label>Kohort</label>
                        <select name="kohort_id" class="form-control">
                            <option value="">Pilih Kohort</option>
                            <option value="1" <?= ($filter['kohort_id'] ?? '') == '1' ? 'selected' : '' ?>>2020</option>
                            <option value="2" <?= ($filter['kohort_id'] ?? '') == '2' ? 'selected' : '' ?>>2019</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Program Studi</label>
                        <select name="prodi_id" class="form-control">
                            <option value="">Semua Program Studi</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fa fa-filter"></i> Filter
                        </button>
                    </div>
                    <div class="col-md-5 text-right">
                        <button type="button" onclick="window.print()" class="btn btn-secondary btn-export">
                            <i class="fa fa-print"></i> Cetak
                        </button>
                        <a href="<?= site_url('laporan/exportPdf/iku?' . http_build_query($filter)) ?>" class="btn btn-danger btn-export">
                            <i class="fa fa-file-pdf"></i> PDF
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- IKU Score Summary -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card text-center">
                <div class="card-body">
                    <h4 class="card-title">Skor IKU-1</h4>
                    <?php 
                    $score = $iku_detail['score'] ?? 0;
                    $score_class = $score >= 80 ? 'score-good' : ($score >= 60 ? 'score-warning' : 'score-bad');
                    ?>
                    <div class="iku-score <?= $score_class ?>"><?= number_format($score, 2) ?>%</div>
                    <p class="text-muted">Target: 80%</p>
                    
                    <?php if($score >= 80): ?>
                        <span class="badge badge-success p-2">✓ Mencapai Target</span>
                    <?php elseif($score >= 60): ?>
                        <span class="badge badge-warning p-2">⚠ Hampir Mencapai Target</span>
                    <?php else: ?>
                        <span class="badge badge-danger p-2">✗ Belum Mencapai Target</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Validation & Verification Info -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5>Response Rate</h5>
                    <h3 class="text-primary"><?= number_format($response_rate ?? 0, 1) ?>%</h3>
                    <small class="text-muted">Minimum: 70%</small>
                    <?php if(($response_rate ?? 0) < 70): ?>
                        <p class="text-danger mt-2"><i class="fa fa-exclamation-triangle"></i> Di bawah threshold!</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5>Validation & Verification Rate</h5>
                    <h3 class="text-info"><?= number_format($vv_rate ?? 0, 1) ?>%</h3>
                    <small class="text-muted">Target: 80%</small>
                    <?php if(($vv_rate ?? 0) < 80): ?>
                        <p class="text-warning mt-2"><i class="fa fa-exclamation-circle"></i> Penalty 20% berlaku</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5>Total Responden</h5>
                    <h3 class="text-success"><?= number_format($iku_detail['total_respondents'] ?? 0) ?></h3>
                    <small class="text-muted">dari <?= number_format($iku_detail['total_alumni'] ?? 0) ?> alumni</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Calculation Detail -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="fa fa-calculator"></i> Detail Perhitungan Bobot
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th>Kategori</th>
                                <th>Jumlah Alumni</th>
                                <th>Bobot Rata-rata</th>
                                <th>Total Bobot</th>
                                <th>Kontribusi (%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $categories = [
                                'Bekerja (Gaji ≥ 1.2 UMP, Tunggu ≤ 6 bln)' => ['count' => $iku_detail['kerja_full'] ?? 0, 'weight' => 1.0],
                                'Bekerja (Gaji ≥ 1.2 UMP, Tunggu 7-12 bln)' => ['count' => $iku_detail['kerja_mid'] ?? 0, 'weight' => 0.8],
                                'Bekerja (Gaji < 1.2 UMP, Tunggu ≤ 6 bln)' => ['count' => $iku_detail['kerja_low_wait'] ?? 0, 'weight' => 0.8],
                                'Bekerja (Gaji < 1.2 UMP, Tunggu 7-12 bln)' => ['count' => $iku_detail['kerja_low'] ?? 0, 'weight' => 0.6],
                                'Wirausaha (Omzet ≥ UMP)' => ['count' => $iku_detail['wira_full'] ?? 0, 'weight' => 1.0],
                                'Wirausaha (Omzet < UMP)' => ['count' => $iku_detail['wira_low'] ?? 0, 'weight' => 0.8],
                                'Lanjut Studi' => ['count' => $iku_detail['studi'] ?? 0, 'weight' => 0.6],
                                'Belum Bekerja' => ['count' => $iku_detail['belum'] ?? 0, 'weight' => 0],
                            ];
                            
                            $total_weight = 0;
                            foreach($categories as $cat => $data) {
                                $total_weight += ($data['count'] * $data['weight']);
                            }
                            
                            foreach($categories as $cat => $data): 
                                $cat_weight = $data['count'] * $data['weight'];
                                $contribution = $total_respondents > 0 ? ($cat_weight / $total_respondents) * 100 : 0;
                            ?>
                            <tr>
                                <td><?= $cat ?></td>
                                <td class="text-center"><?= number_format($data['count']) ?></td>
                                <td class="text-center"><?= $data['weight'] ?></td>
                                <td class="text-center"><?= number_format($cat_weight, 2) ?></td>
                                <td class="text-center"><?= number_format($contribution, 2) ?>%</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="thead-light">
                            <tr>
                                <th colspan="3">Total</th>
                                <th class="text-center"><?= number_format($total_weight, 2) ?></th>
                                <th class="text-center">100%</th>
                            </tr>
                        </tfoot>
                    </table>
                    
                    <div class="alert alert-info mt-3">
                        <strong>Rumus:</strong> IKU-1 = (Σ Bobot Responden / Total Responden) × 100<br>
                        <strong>Perhitungan:</strong> (<?= number_format($total_weight, 2) ?> / <?= number_format($total_respondents ?? 1) ?>) × 100 = <strong><?= number_format($score, 2) ?>%</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- History Trend -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="fa fa-chart-line"></i> Tren IKU-1 (5 Periode Terakhir)
                </div>
                <div class="card-body">
                    <canvas id="lineHistory" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const historyData = <?= json_encode($iku_history ?? []) ?>;

new Chart(document.getElementById('lineHistory'), {
    type: 'line',
    data: {
        labels: historyData.map(d => d.periode),
        datasets: [{
            label: 'Skor IKU-1 (%)',
            data: historyData.map(d => d.score),
            borderColor: '#e74c3c',
            backgroundColor: 'rgba(231, 76, 60, 0.2)',
            tension: 0.4,
            fill: true,
            pointBackgroundColor: '#fff',
            pointBorderColor: '#e74c3c',
            pointBorderWidth: 2,
            pointRadius: 6
        },
        {
            label: 'Target (80%)',
            data: historyData.map(() => 80),
            borderColor: '#95a5a6',
            borderDash: [5, 5],
            fill: false,
            pointRadius: 0
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: false,
                min: 0,
                max: 100,
                title: { display: true, text: 'Skor (%)' }
            }
        }
    }
});
</script>

</body>
</html>
