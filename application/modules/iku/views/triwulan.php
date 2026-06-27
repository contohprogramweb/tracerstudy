<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4"><i class="fas fa-calendar-alt"></i> <?= $page_title ?></h2>
            
            <a href="<?= site_url('iku/dashboard') ?>" class="btn btn-secondary mb-3">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>

    <!-- Quarter Info -->
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><?= $quarter_info['label'] ?></h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <strong>Periode:</strong><br>
                    <?= date('d F Y', strtotime($quarter_info['start_date'])) ?> - 
                    <?= date('d F Y', strtotime($quarter_info['end_date'])) ?>
                </div>
                <div class="col-md-4">
                    <strong>Total Perhitungan:</strong><br>
                    <?= count($iku_results) ?> kali
                </div>
                <div class="col-md-4">
                    <strong>Rata-rata IKU:</strong><br>
                    <?php 
                    $avg = 0;
                    if (!empty($iku_results)) {
                        $avg = array_sum(array_column($iku_results, 'percentage')) / count($iku_results);
                    }
                    echo round($avg, 2) . '%';
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Trend Chart -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Trend Triwulan Sebelumnya</h5>
        </div>
        <div class="card-body">
            <canvas id="trendChart" height="80"></canvas>
        </div>
    </div>

    <!-- Quarter Navigation -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="form-inline justify-content-center">
                <div class="form-group mr-3">
                    <label for="tahun" class="mr-2">Tahun:</label>
                    <select name="tahun" id="tahun" class="form-control">
                        <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                            <option value="<?= $y ?>" <?= $y == $tahun ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="form-group mr-3">
                    <label for="quarter" class="mr-2">Triwulan:</label>
                    <select name="quarter" id="quarter" class="form-control">
                        <option value="1" <?= $quarter == 1 ? 'selected' : '' ?>>Triwulan 1 (Jan-Mar)</option>
                        <option value="2" <?= $quarter == 2 ? 'selected' : '' ?>>Triwulan 2 (Apr-Jun)</option>
                        <option value="3" <?= $quarter == 3 ? 'selected' : '' ?>>Triwulan 3 (Jul-Sep)</option>
                        <option value="4" <?= $quarter == 4 ? 'selected' : '' ?>>Triwulan 4 (Oct-Dec)</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Tampilkan</button>
            </form>
        </div>
    </div>

    <!-- Results Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Hasil Perhitungan IKU - <?= $quarter_info['label'] ?></h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Kohort</th>
                            <th>Prodi</th>
                            <th>Responden</th>
                            <th>Score IKU</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($iku_results)): ?>
                            <?php foreach ($iku_results as $idx => $result): ?>
                                <tr>
                                    <td><?= $idx + 1 ?></td>
                                    <td><?= date('d/m/Y', strtotime($result['created_at'])) ?></td>
                                    <td><?= $result['kohort_nama'] ?></td>
                                    <td><?= $result['prodi_nama'] ?: '-' ?></td>
                                    <td><?= $result['denominator'] ?></td>
                                    <td><strong><?= $result['percentage'] ?>%</strong></td>
                                    <td>
                                        <span class="badge badge-<?= $result['status_capaian'] === 'Melampaui' ? 'success' : ($result['status_capaian'] === 'Tercapai' ? 'info' : 'warning') ?>">
                                            <?= $result['status_capaian'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?= site_url('iku/detail/' . $result['id']) ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted">Belum ada data perhitungan pada periode ini</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    const trendData = <?= json_encode($trend) ?>;
    
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: trendData.map(d => d.quarter),
            datasets: [{
                label: 'Rata-rata Score IKU',
                data: trendData.map(d => d.avg_score),
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: false,
                    min: 0,
                    max: 100
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            }
        }
    });
</script>
