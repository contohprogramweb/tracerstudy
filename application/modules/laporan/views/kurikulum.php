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
            background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);
            color: white;
            font-weight: 600;
        }
        
        .heatmap-cell {
            text-align: center;
            padding: 10px;
            font-weight: bold;
            border: 1px solid #ddd;
        }
        
        .gap-low { background-color: #d4edda; color: #155724; }
        .gap-medium { background-color: #fff3cd; color: #856404; }
        .gap-high { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>

<div class="container-fluid mt-4">
    <div class="row mb-4 no-print">
        <div class="col-md-12">
            <h2><i class="fa fa-book"></i> <?= $title ?></h2>
            <p class="text-muted">Analisis gap kompetensi dan rekomendasi perbaikan kurikulum</p>
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
                        <a href="<?= site_url('laporan/exportPdf/kurikulum?' . http_build_query($filter)) ?>" class="btn btn-danger btn-export">
                            <i class="fa fa-file-pdf"></i> PDF
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Gap Analysis Heatmap -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="fa fa-th"></i> Heatmap Gap Analysis CPL (Capaian Pembelajaran Lulusan)
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th>CPL / Aspek</th>
                                    <th>Sikap (Attitude)</th>
                                    <th>Pengetahuan (Knowledge)</th>
                                    <th>Keterampilan Umum</th>
                                    <th>Keterampilan Khusus</th>
                                    <th>Rata-rata Gap</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                foreach($gap_matrix as $cpl_code => $aspects): 
                                    $avg_gap = array_sum($aspects) / count($aspects);
                                    $gap_class = $avg_gap < 10 ? 'gap-low' : ($avg_gap < 20 ? 'gap-medium' : 'gap-high');
                                ?>
                                <tr>
                                    <td class="font-weight-bold"><?= $cpl_code ?></td>
                                    <?php foreach($aspects as $aspect_name => $gap_value): 
                                        $cell_class = $gap_value < 10 ? 'gap-low' : ($gap_value < 20 ? 'gap-medium' : 'gap-high');
                                    ?>
                                    <td class="heatmap-cell <?= $cell_class ?>">
                                        <?= number_format($gap_value, 1) ?>%
                                        <br><small><?= $gap_value < 10 ? 'Baik' : ($gap_value < 20 ? 'Cukup' : 'Perlu Perbaikan') ?></small>
                                    </td>
                                    <?php endforeach; ?>
                                    <td class="heatmap-cell <?= $gap_class ?>">
                                        <?= number_format($avg_gap, 1) ?>%
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        <strong>Keterangan:</strong>
                        <span class="badge gap-low p-2 ml-2">Gap < 10% (Baik)</span>
                        <span class="badge gap-medium p-2 ml-2">Gap 10-20% (Cukup)</span>
                        <span class="badge gap-high p-2 ml-2">Gap > 20% (Perlu Perbaikan)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gap by Aspect Chart -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="fa fa-chart-bar"></i> Gap Rata-rata per Aspek Kompetensi
                </div>
                <div class="card-body">
                    <canvas id="barGapAspect"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="fa fa-chart-pie"></i> Distribusi Kategori Gap
                </div>
                <div class="card-body">
                    <canvas id="pieGapCategory"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recommendations -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-success">
                    <i class="fa fa-lightbulb"></i> Rekomendasi Perbaikan Kurikulum
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach(array_chunk($recommendations, 2) as $chunk): ?>
                        <div class="col-md-6">
                            <ul class="list-group">
                                <?php foreach($chunk as $rec): ?>
                                <li class="list-group-item">
                                    <i class="fa fa-check-circle text-success"></i>
                                    <?= $rec ?>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if(empty($recommendations)): ?>
                    <p class="text-muted text-center">Belum ada rekomendasi yang tersedia.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Plan -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-info">
                    <i class="fa fa-tasks"></i> Rencana Tindak Lanjut
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Area Perbaikan</th>
                                <th>Prioritas</th>
                                <th>Target Waktu</th>
                                <th>Penanggung Jawab</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td>Revisi mata kuliah berdasarkan gap analysis</td>
                                <td><span class="badge badge-danger">Tinggi</span></td>
                                <td>Semester Depan</td>
                                <td>Kaprodi & Tim Kurikulum</td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td>Pelatihan dosen untuk kompetensi dengan gap tinggi</td>
                                <td><span class="badge badge-warning">Sedang</span></td>
                                <td>6 Bulan</td>
                                <td>Kaprodi</td>
                            </tr>
                            <tr>
                                <td>3</td>
                                <td>Kerjasama industri untuk praktik kerja</td>
                                <td><span class="badge badge-info">Rendah</span></td>
                                <td>1 Tahun</td>
                                <td>Humas & Kerjasama</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const gapByAspect = <?= json_encode($gap_by_aspect ?? []) ?>;

// Calculate gap categories
const gapMatrix = <?= json_encode($gap_matrix) ?>;
let lowCount = 0, mediumCount = 0, highCount = 0;

for (const cpl in gapMatrix) {
    for (const aspect in gapMatrix[cpl]) {
        const gap = gapMatrix[cpl][aspect];
        if (gap < 10) lowCount++;
        else if (gap < 20) mediumCount++;
        else highCount++;
    }
}

// Bar Chart - Gap by Aspect
new Chart(document.getElementById('barGapAspect'), {
    type: 'bar',
    data: {
        labels: gapByAspect.map(d => d.aspect),
        datasets: [{
            label: 'Gap (%)',
            data: gapByAspect.map(d => d.gap),
            backgroundColor: gapByAspect.map(d => d.gap < 10 ? '#2ecc71' : (d.gap < 20 ? '#f39c12' : '#e74c3c')),
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                title: { display: true, text: 'Gap (%)' }
            }
        },
        plugins: {
            legend: { display: false }
        }
    }
});

// Pie Chart - Gap Categories
new Chart(document.getElementById('pieGapCategory'), {
    type: 'doughnut',
    data: {
        labels: ['Gap Rendah (<10%)', 'Gap Sedang (10-20%)', 'Gap Tinggi (>20%)'],
        datasets: [{
            data: [lowCount, mediumCount, highCount],
            backgroundColor: ['#2ecc71', '#f39c12', '#e74c3c'],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});
</script>

</body>
</html>
