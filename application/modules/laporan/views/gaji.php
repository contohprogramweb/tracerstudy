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
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
            color: white;
            font-weight: 600;
        }
        
        .stat-box {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-value {
            font-size: 1.8rem;
            font-weight: bold;
            color: #e67e22;
        }
    </style>
</head>
<body>

<div class="container-fluid mt-4">
    <div class="row mb-4 no-print">
        <div class="col-md-12">
            <h2><i class="fa fa-money-bill"></i> <?= $title ?></h2>
            <p class="text-muted">Analisis distribusi gaji lulusan berdasarkan tracer study</p>
        </div>
    </div>

    <!-- Filter -->
    <div class="row mb-4 no-print">
        <div class="col-md-12">
            <form method="GET" class="filter-section">
                <div class="form-row align-items-end">
                    <div class="col-md-2">
                        <label>Tahun</label>
                        <select name="tahun" class="form-control">
                            <?php for($y=date('Y'); $y>=date('Y')-10; $y--): ?>
                                <option value="<?= $y ?>" <?= ($filter['tahun'] ?? date('Y')) == $y ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
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
                        <a href="<?= site_url('laporan/exportPdf/gaji?' . http_build_query($filter)) ?>" class="btn btn-danger btn-export">
                            <i class="fa fa-file-pdf"></i> PDF
                        </a>
                        <a href="<?= site_url('laporan/exportExcel/gaji?' . http_build_query($filter)) ?>" class="btn btn-success btn-export">
                            <i class="fa fa-file-excel"></i> Excel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-box">
                <div class="stat-value">Rp <?= number_format($stats['avg'] ?? 0, 0, ',', '.') ?></div>
                <div class="text-muted">Rata-rata Gaji</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-box">
                <div class="stat-value">Rp <?= number_format($stats['median'] ?? 0, 0, ',', '.') ?></div>
                <div class="text-muted">Median Gaji</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-box">
                <div class="stat-value">Rp <?= number_format($stats['min'] ?? 0, 0, ',', '.') ?></div>
                <div class="text-muted">Gaji Terendah</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-box">
                <div class="stat-value">Rp <?= number_format($stats['max'] ?? 0, 0, ',', '.') ?></div>
                <div class="text-muted">Gaji Tertinggi</div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="fa fa-chart-pie"></i> Distribusi Range Gaji
                </div>
                <div class="card-body">
                    <canvas id="pieRanges"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="fa fa-chart-bar"></i> Rata-rata Gaji per Program Studi
                </div>
                <div class="card-body">
                    <canvas id="barProdi"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Table -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="fa fa-table"></i> Detail Distribusi Gaji
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Range Gaji (Rp)</th>
                                <th>Jumlah Alumni</th>
                                <th>Persentase</th>
                                <th>Grafik</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $max_count = max(array_column($ranges, 'count'));
                            foreach($ranges as $item): 
                                $percentage = ($item['count'] / array_sum(array_column($ranges, 'count'))) * 100;
                                $bar_width = ($item['count'] / $max_count) * 100;
                            ?>
                            <tr>
                                <td><?= $item['range'] ?></td>
                                <td><?= number_format($item['count']) ?></td>
                                <td><?= number_format($percentage, 1) ?>%</td>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-warning" role="progressbar" 
                                             style="width: <?= $bar_width ?>%;" 
                                             aria-valuenow="<?= $bar_width ?>" aria-valuemin="0" aria-valuemax="100">
                                            <?= number_format($percentage, 0) ?>%
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const rangesData = <?= json_encode($ranges) ?>;
const prodiData = <?= json_encode($by_prodi) ?>;

// Pie Chart - Range Distribution
new Chart(document.getElementById('pieRanges'), {
    type: 'doughnut',
    data: {
        labels: rangesData.map(d => d.range),
        datasets: [{
            data: rangesData.map(d => d.count),
            backgroundColor: ['#f39c12', '#e67e22', '#d35400', '#e74c3c', '#c0392b', '#9b59b6'],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom' },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((context.raw / total) * 100).toFixed(1);
                        return context.label + ': ' + context.raw + ' (' + percentage + '%)';
                    }
                }
            }
        }
    }
});

// Bar Chart - Salary by Prodi
new Chart(document.getElementById('barProdi'), {
    type: 'bar',
    data: {
        labels: prodiData.map(d => d.prodi),
        datasets: [{
            label: 'Rata-rata Gaji (Juta IDR)',
            data: prodiData.map(d => (d.avg_salary / 1000000).toFixed(2)),
            backgroundColor: '#f39c12',
            borderColor: '#e67e22',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                title: { display: true, text: 'Juta Rupiah' }
            }
        },
        plugins: {
            legend: { display: false }
        }
    }
});
</script>

</body>
</html>
