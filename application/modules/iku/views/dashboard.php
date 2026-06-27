<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4"><i class="fas fa-chart-line"></i> <?= $page_title ?></h2>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Perhitungan</h5>
                    <h2 class="mb-0"><?= $summary['total_calculations'] ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Rata-rata IKU</h5>
                    <h2 class="mb-0"><?= $summary['avg_iku_score'] ?>%</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Tercapai Target</h5>
                    <h2 class="mb-0"><?= $summary['tercapai_count'] + $summary['melampaui_count'] ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h5 class="card-title">Melampaui Target</h5>
                    <h2 class="mb-0"><?= $summary['melampaui_count'] ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="form-inline">
                <div class="form-group mr-3">
                    <label for="tahun" class="mr-2">Tahun:</label>
                    <select name="tahun" id="tahun" class="form-control">
                        <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                            <option value="<?= $y ?>" <?= $y == $tahun ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="form-group mr-3">
                    <label for="fakultas_id" class="mr-2">Fakultas:</label>
                    <select name="fakultas_id" id="fakultas_id" class="form-control">
                        <option value="">Semua Fakultas</option>
                        <!-- Add fakultas options from database -->
                    </select>
                </div>
                <div class="form-group mr-3">
                    <label for="prodi_id" class="mr-2">Prodi:</label>
                    <select name="prodi_id" id="prodi_id" class="form-control">
                        <option value="">Semua Prodi</option>
                        <?php foreach ($prodis as $prodi): ?>
                            <option value="<?= $prodi['id'] ?>" <?= $prodi['id'] == $prodi_id ? 'selected' : '' ?>>
                                <?= $prodi['nama'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="<?= site_url('iku/calculateAll') ?>" class="btn btn-warning ml-2" title="Run from CLI">
                    <i class="fas fa-calculator"></i> Hitung Ulang
                </a>
            </form>
        </div>
    </div>

    <!-- Gauge Chart & Progress -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Gauge Chart IKU-1</h5>
                </div>
                <div class="card-body text-center">
                    <canvas id="gaugeChart" width="300" height="200"></canvas>
                    <p class="mt-3 text-muted">Target: 70%</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Progress per Kohort</h5>
                </div>
                <div class="card-body">
                    <div id="kohortProgress">
                        <?php if (!empty($iku_results)): ?>
                            <?php foreach ($iku_results as $result): ?>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span><?= $result['kohort_nama'] ?> - <?= $result['prodi_nama'] ?: 'Semua Prodi' ?></span>
                                        <span><?= $result['percentage'] ?>%</span>
                                    </div>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar <?= $result['percentage'] >= 70 ? 'bg-success' : 'bg-warning' ?>" 
                                             role="progressbar" 
                                             style="width: <?= $result['percentage'] ?>%"
                                             aria-valuenow="<?= $result['percentage'] ?>" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                            <?= $result['status_capaian'] ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted text-center">Belum ada data perhitungan</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Detail Perhitungan IKU-1</h5>
            <a href="<?= site_url('iku/exportBelmawa') ?>" class="btn btn-sm btn-success">
                <i class="fas fa-download"></i> Export Belmawa
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>No</th>
                            <th>Kohort</th>
                            <th>Prodi</th>
                            <th>Tahun</th>
                            <th>Responden</th>
                            <th>Response Rate</th>
                            <th>Score IKU</th>
                            <th>Status</th>
                            <th>V&V Penalty</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($iku_results)): ?>
                            <?php foreach ($iku_results as $idx => $result): ?>
                                <tr>
                                    <td><?= $idx + 1 ?></td>
                                    <td><?= $result['kohort_nama'] ?></td>
                                    <td><?= $result['prodi_nama'] ?: '-' ?></td>
                                    <td><?= $result['tahun_iku'] ?></td>
                                    <td><?= $result['denominator'] ?></td>
                                    <td>-</td>
                                    <td><strong><?= $result['percentage'] ?>%</strong></td>
                                    <td>
                                        <span class="badge badge-<?= $result['status_capaian'] === 'Melampaui' ? 'success' : ($result['status_capaian'] === 'Tercapai' ? 'info' : 'warning') ?>">
                                            <?= $result['status_capaian'] ?>
                                        </span>
                                    </td>
                                    <td>-</td>
                                    <td>
                                        <a href="<?= site_url('iku/detail/' . $result['id']) ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="text-center text-muted">Belum ada data perhitungan</td>
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
    // Gauge Chart
    const gaugeCtx = document.getElementById('gaugeChart').getContext('2d');
    const avgScore = <?= $summary['avg_iku_score'] ?>;
    
    new Chart(gaugeCtx, {
        type: 'doughnut',
        data: {
            labels: ['Tercapai', 'Belum'],
            datasets: [{
                data: [avgScore, 100 - avgScore],
                backgroundColor: ['#28a745', '#e9ecef'],
                borderWidth: 0
            }]
        },
        options: {
            circumference: 180,
            rotation: 270,
            plugins: {
                tooltip: { enabled: false },
                legend: { display: false }
            },
            cutout: '70%'
        }
    });
</script>
