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
        body { background-color: #f5f6fa; }
        
        @media print {
            .no-print, .sidebar, .filter-section, .btn-export { display: none !important; }
            .card { break-inside: avoid; border: 1px solid #ddd !important; box-shadow: none !important; }
            body { background: white; }
            .chart-container { page-break-inside: avoid; }
        }
        
        .chart-container { 
            position: relative; 
            height: 300px; 
            width: 100%; 
            margin-bottom: 20px;
        }
        
        .gauge-container { 
            position: relative; 
            height: 200px; 
            width: 100%; 
            display: flex; 
            justify-content: center; 
            align-items: center;
        }
        
        .stat-card { 
            background: #fff; 
            padding: 20px; 
            border-radius: 8px; 
            box-shadow: 0 2px 8px rgba(0,0,0,0.1); 
            margin-bottom: 20px;
            transition: transform 0.2s;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
        }
        
        .stat-value { 
            font-size: 2rem; 
            font-weight: bold; 
            color: #2c3e50; 
        }
        
        .stat-label { 
            color: #7f8c8d; 
            font-size: 0.9rem; 
            text-transform: uppercase;
            letter-spacing: 0.5px;
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: 600;
            border-radius: 8px 8px 0 0 !important;
        }
        
        .btn-export {
            margin-left: 5px;
        }
    </style>
</head>
<body>

<div class="container-fluid mt-4">
    <!-- Header -->
    <div class="row mb-4 no-print">
        <div class="col-md-12">
            <h2><i class="fa fa-chart-line"></i> <?= $title ?></h2>
            <p class="text-muted">Dashboard analitik tracer study dengan real-time data</p>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="row mb-4 no-print">
        <div class="col-md-12">
            <form method="GET" class="filter-section">
                <div class="form-row align-items-end">
                    <div class="col-md-2">
                        <label for="tahun">Tahun</label>
                        <select name="tahun" id="tahun" class="form-control">
                            <?php for($y=date('Y'); $y>=date('Y')-10; $y--): ?>
                                <option value="<?= $y ?>" <?= $y == $tahun_filter ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="prodi_id">Program Studi</label>
                        <select name="prodi_id" id="prodi_id" class="form-control">
                            <option value="">Semua Program Studi</option>
                            <!-- Populate from database in real implementation -->
                            <option value="1" <?= $prodi_filter == 1 ? 'selected' : '' ?>>Teknik Informatika</option>
                            <option value="2" <?= $prodi_filter == 2 ? 'selected' : '' ?>>Sistem Informasi</option>
                            <option value="3" <?= $prodi_filter == 3 ? 'selected' : '' ?>>Teknik Elektro</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="kohort_id">Kohort</label>
                        <select name="kohort_id" id="kohort_id" class="form-control">
                            <option value="">Semua Kohort</option>
                            <option value="1">2020</option>
                            <option value="2">2019</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fa fa-filter"></i> Filter
                        </button>
                    </div>
                    <div class="col-md-3 text-right">
                        <button type="button" onclick="window.print()" class="btn btn-secondary btn-export">
                            <i class="fa fa-print"></i> Cetak
                        </button>
                        <a href="<?= site_url('laporan/exportPdf/dashboard?tahun=' . $tahun_filter) ?>" class="btn btn-danger btn-export">
                            <i class="fa fa-file-pdf"></i> PDF
                        </a>
                        <a href="<?= site_url('laporan/exportExcel/dashboard?tahun=' . $tahun_filter) ?>" class="btn btn-success btn-export">
                            <i class="fa fa-file-excel"></i> Excel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="row">
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-value" style="color: #667eea;"><?= number_format($iku_score, 1) ?>%</div>
                <div class="stat-label">Skor IKU-1</div>
                <small class="text-muted">Target: <?= $iku_target ?>%</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-value" style="color: #2ecc71;"><?= number_format($total_responden) ?></div>
                <div class="stat-label">Total Responden</div>
                <small class="text-muted">Tahun <?= $tahun_filter ?></small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-value" style="color: #f39c12;">Rp <?= number_format($avg_salary ?? 0, 0, ',', '.') ?></div>
                <div class="stat-label">Rata-rata Gaji</div>
                <small class="text-muted">Per Bulan</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-value" style="color: #9b59b6;"><?= number_format($avg_wait_time ?? 0, 1) ?></div>
                <div class="stat-label">Masa Tunggu</div>
                <small class="text-muted">Dalam Bulan</small>
            </div>
        </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <i class="fa fa-chart-pie"></i> Distribusi Status Kerja
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="pieStatus"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <i class="fa fa-tachometer-alt"></i> Capaian IKU-1 vs Target
                </div>
                <div class="card-body">
                    <div class="gauge-container">
                        <canvas id="gaugeIku"></canvas>
                    </div>
                    <p class="text-center mt-2 text-muted">
                        <?php if($iku_score >= $iku_target): ?>
                            <span class="badge badge-success">✓ Mencapai Target</span>
                        <?php else: ?>
                            <span class="badge badge-warning">⚠ Belum Mencapai Target</span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <i class="fa fa-check-circle"></i> Kesesuaian Kompetensi
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="doughnutKompetensi"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 2 -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="fa fa-chart-bar"></i> Rata-rata Gaji per Program Studi
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="barGaji"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="fa fa-chart-line"></i> Tren Jumlah Responden (5 Tahun Terakhir)
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="lineTren"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Radar Chart Full Width -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="fa fa-crosshairs"></i> Profil Kompetensi: Lulusan vs Ekspektasi Industri
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height: 400px;">
                        <canvas id="radarKompetensi"></canvas>
                    </div>
                    <div class="mt-3">
                        <h6>Keterangan:</h6>
                        <ul class="list-inline">
                            <li class="list-inline-item"><span style="color: #2ecc71;">●</span> Skor Penilaian Lulusan</li>
                            <li class="list-inline-item"><span style="color: #e74c3c;">●</span> Ekspektasi Industri/Stakeholder</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="row mt-4 mb-5 no-print">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-secondary">
                    <i class="fa fa-link"></i> Akses Cepat Laporan Detail
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <a href="<?= site_url('laporan/statusKerja') ?>" class="btn btn-outline-primary btn-block btn-sm">
                                <i class="fa fa-briefcase"></i><br>Status Kerja
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="<?= site_url('laporan/gaji') ?>" class="btn btn-outline-success btn-block btn-sm">
                                <i class="fa fa-money-bill"></i><br>Distribusi Gaji
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="<?= site_url('laporan/masaTunggu') ?>" class="btn btn-outline-warning btn-block btn-sm">
                                <i class="fa fa-clock"></i><br>Masa Tunggu
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="<?= site_url('laporan/kompetensi') ?>" class="btn btn-outline-info btn-block btn-sm">
                                <i class="fa fa-graduation-cap"></i><br>Kompetensi
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="<?= site_url('laporan/kurikulum') ?>" class="btn btn-outline-danger btn-block btn-sm">
                                <i class="fa fa-book"></i><br>Evaluasi Kurikulum
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="<?= site_url('laporan/iku') ?>" class="btn btn-outline-dark btn-block btn-sm">
                                <i class="fa fa-star"></i><br>IKU-1
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Data dari PHP
const statusData = <?= json_encode($status_kerja ?? []) ?>;
const gajiData = <?= json_encode($gaji_prodi ?? []) ?>;
const trenData = <?= json_encode($tren_responden ?? []) ?>;
const ikuScore = <?= $iku_score ?? 0 ?>;
const ikuTarget = <?= $iku_target ?? 80 ?>;
const kompData = <?= json_encode($kompetensi_match ?? []) ?>;
const radarData = <?= json_encode($radar_data ?? []) ?>;

// Pie Chart - Status Kerja
if (statusData.length > 0) {
    new Chart(document.getElementById('pieStatus'), {
        type: 'pie',
        data: {
            labels: statusData.map(d => d.status),
            datasets: [{
                data: statusData.map(d => d.count),
                backgroundColor: ['#2ecc71', '#3498db', '#f1c40f', '#95a5a6', '#e74c3c'],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false,
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
}

// Gauge Chart - IKU Score
new Chart(document.getElementById('gaugeIku'), {
    type: 'doughnut',
    data: {
        labels: ['Capaian', 'Sisa'],
        datasets: [{
            data: [ikuScore, Math.max(0, 100 - ikuScore)],
            backgroundColor: [ikuScore >= ikuTarget ? '#2ecc71' : '#e74c3c', '#ecf0f1'],
            borderWidth: 0
        }]
    },
    options: { 
        responsive: true, 
        maintainAspectRatio: false,
        circumference: 180,
        rotation: 270,
        cutout: '75%',
        plugins: { 
            legend: { display: false }, 
            tooltip: { enabled: false },
            title: {
                display: true,
                text: ikuScore.toFixed(1) + '%',
                position: 'bottom',
                font: { size: 24, weight: 'bold' },
                color: ikuScore >= ikuTarget ? '#2ecc71' : '#e74c3c'
            }
        }
    }
});

// Doughnut Chart - Kompetensi Match
if (kompData.length > 0) {
    new Chart(document.getElementById('doughnutKompetensi'), {
        type: 'doughnut',
        data: {
            labels: kompData.map(d => d.kategori),
            datasets: [{
                data: kompData.map(d => d.persen),
                backgroundColor: ['#27ae60', '#f39c12', '#c0392b'],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false,
            cutout: '60%',
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
}

// Bar Chart - Gaji per Prodi
if (gajiData.length > 0) {
    new Chart(document.getElementById('barGaji'), {
        type: 'bar',
        data: {
            labels: gajiData.map(d => d.prodi),
            datasets: [{
                label: 'Rata-rata Gaji (Juta IDR)',
                data: gajiData.map(d => (d.avg_salary / 1000000).toFixed(2)),
                backgroundColor: '#3498db',
                borderColor: '#2980b9',
                borderWidth: 1
            }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false,
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
}

// Line Chart - Tren Responden
if (trenData.length > 0) {
    new Chart(document.getElementById('lineTren'), {
        type: 'line',
        data: {
            labels: trenData.map(d => d.tahun),
            datasets: [{
                label: 'Jumlah Responden',
                data: trenData.map(d => d.jumlah),
                borderColor: '#9b59b6',
                backgroundColor: 'rgba(155, 89, 182, 0.2)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#9b59b6',
                pointBorderWidth: 2,
                pointRadius: 5
            }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false,
            scales: { 
                y: { 
                    beginAtZero: true,
                    title: { display: true, text: 'Jumlah Alumni' }
                }
            }
        }
    });
}

// Radar Chart - Kompetensi
if (radarData.length > 0) {
    new Chart(document.getElementById('radarKompetensi'), {
        type: 'radar',
        data: {
            labels: radarData.map(d => d.kompetensi),
            datasets: [
                {
                    label: 'Skor Lulusan',
                    data: radarData.map(d => d.skor_lulusan),
                    borderColor: '#2ecc71',
                    backgroundColor: 'rgba(46, 204, 113, 0.2)',
                    pointBackgroundColor: '#2ecc71',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                },
                {
                    label: 'Ekspektasi Industri',
                    data: radarData.map(d => d.skor_industri),
                    borderColor: '#e74c3c',
                    backgroundColor: 'rgba(231, 76, 60, 0.2)',
                    pointBackgroundColor: '#e74c3c',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }
            ]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false,
            scales: { 
                r: { 
                    min: 0, 
                    max: 5,
                    ticks: { stepSize: 1 }
                }
            }
        }
    });
}
</script>

</body>
</html>
