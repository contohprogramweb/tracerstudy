<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Gap Analysis CPL' ?> - Alumni System</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/font-awesome.min.css') ?>">
    <script src="<?= base_url('assets/js/chartjs/Chart.min.js') ?>"></script>
    <style>
        .gap-card { border-radius: 10px; padding: 20px; margin-bottom: 20px; }
        .gap-positive { border-left: 5px solid #1cc88a; background: #f0fff4; }
        .gap-negative { border-left: 5px solid #e74a3b; background: #fff5f5; }
        .gap-neutral { border-left: 5px solid #f6c23e; background: #fffbf0; }
        .radar-container { position: relative; height: 400px; margin-bottom: 30px; }
        .cpl-detail-table th { background: #f8f9fa; }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="row mb-4">
            <div class="col-12">
                <h2><i class="fa fa-chart-line"></i> Gap Analysis CPL</h2>
                <p class="text-muted">Program Studi: <strong><?= $prodi['name'] ?></strong></p>
                <hr>
            </div>
        </div>

        <!-- Radar Chart -->
        <div class="row">
            <div class="col-md-8">
                <div class="card shadow gap-card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fa fa-chart-radar"></i> Visualisasi Gap CPL</h5>
                    </div>
                    <div class="card-body">
                        <div class="radar-container">
                            <canvas id="cplRadarChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary Statistics -->
            <div class="col-md-4">
                <div class="card shadow gap-card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fa fa-info-circle"></i> Ringkasan</h5>
                    </div>
                    <div class="card-body">
                        <?php 
                        $positive_gaps = 0;
                        $negative_gaps = 0;
                        $neutral_gaps = 0;
                        foreach ($combined_averages as $cpl_data) {
                            if ($cpl_data['gap'] > 0.2) $positive_gaps++;
                            elseif ($cpl_data['gap'] < -0.2) $negative_gaps++;
                            else $neutral_gaps++;
                        }
                        ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span><i class="fa fa-arrow-up text-success"></i> Stakeholder > Alumni</span>
                                <strong><?= $positive_gaps ?> CPL</strong>
                            </div>
                            <small class="text-muted">Ekspektasi lebih tinggi dari realita</small>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span><i class="fa fa-arrow-down text-danger"></i> Alumni > Stakeholder</span>
                                <strong><?= $negative_gaps ?> CPL</strong>
                            </div>
                            <small class="text-muted">Realita melebihi ekspektasi</small>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span><i class="fa fa-minus text-warning"></i> Seimbang</span>
                                <strong><?= $neutral_gaps ?> CPL</strong>
                            </div>
                            <small class="text-muted">Gap < 0.2</small>
                        </div>
                        <hr>
                        <div class="alert alert-info">
                            <small>
                                <strong>Catatan:</strong><br>
                                Perhitungan menggunakan rasio 60% Stakeholder : 40% Alumni (BR-SUR-007)
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CPL Detail Table -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fa fa-table"></i> Detail Analisis per CPL</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover cpl-detail-table">
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th width="10%">Kode</th>
                                        <th>Deskripsi CPL</th>
                                        <th width="10%" class="text-center">Stakeholder<br><small>(60%)</small></th>
                                        <th width="10%" class="text-center">Alumni<br><small>(40%)</small></th>
                                        <th width="10%" class="text-center">Gabungan</th>
                                        <th width="10%" class="text-center">Gap</th>
                                        <th width="10%">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $no = 1;
                                    foreach ($combined_averages as $cpl_data): 
                                        $gap_class = '';
                                        $gap_icon = '';
                                        $gap_label = '';
                                        
                                        if ($cpl_data['gap'] > 0.2) {
                                            $gap_class = 'text-success';
                                            $gap_icon = 'fa-arrow-up';
                                            $gap_label = 'Perlu Peningkatan';
                                        } elseif ($cpl_data['gap'] < -0.2) {
                                            $gap_class = 'text-danger';
                                            $gap_icon = 'fa-arrow-down';
                                            $gap_label = 'Melebihi Ekspektasi';
                                        } else {
                                            $gap_class = 'text-warning';
                                            $gap_icon = 'fa-check';
                                            $gap_label = 'Baik';
                                        }
                                    ?>
                                    <tr class="<?= $gap_class == 'text-success' ? 'gap-positive' : ($gap_class == 'text-danger' ? 'gap-negative' : 'gap-neutral') ?>">
                                        <td><?= $no++ ?></td>
                                        <td><strong><?= $cpl_data['cpl_code'] ?></strong></td>
                                        <td><?= $cpl_data['cpl_description'] ?></td>
                                        <td class="text-center">
                                            <strong><?= $cpl_data['stakeholder_avg'] ?></strong>
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fa fa-star <?= $i <= round($cpl_data['stakeholder_avg']) ? 'text-warning' : 'text-muted' ?>" style="font-size: 10px;"></i>
                                            <?php endfor; ?>
                                        </td>
                                        <td class="text-center">
                                            <strong><?= $cpl_data['alumni_avg'] ?></strong>
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fa fa-star <?= $i <= round($cpl_data['alumni_avg']) ? 'text-warning' : 'text-muted' ?>" style="font-size: 10px;"></i>
                                            <?php endfor; ?>
                                        </td>
                                        <td class="text-center">
                                            <strong class="text-primary"><?= $cpl_data['combined_avg'] ?></strong>
                                        </td>
                                        <td class="text-center">
                                            <strong class="<?= $gap_class ?>">
                                                <i class="fa <?= $gap_icon ?>"></i>
                                                <?= number_format($cpl_data['gap'], 2) ?>
                                            </strong>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?= $gap_class == 'text-success' ? 'success' : ($gap_class == 'text-danger' ? 'danger' : 'warning') ?>">
                                                <?= $gap_label ?>
                                            </span>
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

        <!-- Recommendations -->
        <?php if (!empty($recommendations)): ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-warning">
                        <h5 class="mb-0"><i class="fa fa-lightbulb"></i> Rekomendasi dari Stakeholder</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="fa fa-graduation-cap"></i> Kompetensi yang Dibutuhkan</h6>
                                <ul class="list-group list-group-flush">
                                    <?php 
                                    $competencies = [];
                                    foreach ($recommendations as $rec) {
                                        if (!empty($rec['recommended_competencies'])) {
                                            $comps = explode(',', $rec['recommended_competencies']);
                                            foreach ($comps as $comp) {
                                                $comp = trim($comp);
                                                if (!empty($comp)) {
                                                    $competencies[$comp] = isset($competencies[$comp]) ? $competencies[$comp] + 1 : 1;
                                                }
                                            }
                                        }
                                    }
                                    arsort($competencies);
                                    $count = 0;
                                    foreach ($competencies as $comp => $freq): 
                                        if ($count++ >= 10) break;
                                    ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?= $comp ?>
                                        <span class="badge badge-primary badge-pill"><?= $freq ?></span>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fa fa-book"></i> Saran Perbaikan Kurikulum</h6>
                                <div class="list-group list-group-flush">
                                    <?php 
                                    $suggestions = array_filter(array_column($recommendations, 'curriculum_suggestions'));
                                    $unique_suggestions = array_unique($suggestions);
                                    $count = 0;
                                    foreach ($unique_suggestions as $suggestion): 
                                        if (empty($suggestion) || $count++ >= 5) continue;
                                    ?>
                                    <div class="list-group-item">
                                        <small><i class="fa fa-quote-left text-muted"></i></small>
                                        <?= htmlspecialchars($suggestion) ?>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Action Buttons -->
        <div class="row mt-4 mb-5">
            <div class="col-12 text-right">
                <a href="<?= site_url('prodi/dashboard/' . $prodi_id) ?>" class="btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> Kembali
                </a>
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="fa fa-print"></i> Cetak Laporan
                </button>
            </div>
        </div>
    </div>

    <script src="<?= base_url('assets/js/jquery.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>"></script>
    <script>
        $(document).ready(function() {
            // Prepare data for radar chart
            var cplLabels = [<?php foreach ($combined_averages as $cpl) echo "'" . $cpl['cpl_code'] . "',"; ?>];
            var stakeholderData = [<?php foreach ($combined_averages as $cpl) echo $cpl['stakeholder_avg'] . ","; ?>];
            var alumniData = [<?php foreach ($combined_averages as $cpl) echo $cpl['alumni_avg'] . ","; ?>];
            var combinedData = [<?php foreach ($combined_averages as $cpl) echo $cpl['combined_avg'] . ","; ?>];

            // Create radar chart
            var ctx = document.getElementById('cplRadarChart').getContext('2d');
            var radarChart = new Chart(ctx, {
                type: 'radar',
                data: {
                    labels: cplLabels,
                    datasets: [
                        {
                            label: 'Penilaian Stakeholder (60%)',
                            data: stakeholderData,
                            backgroundColor: 'rgba(78, 115, 223, 0.2)',
                            borderColor: 'rgba(78, 115, 223, 1)',
                            pointBackgroundColor: 'rgba(78, 115, 223, 1)',
                            borderWidth: 2
                        },
                        {
                            label: 'Penilaian Alumni (40%)',
                            data: alumniData,
                            backgroundColor: 'rgba(28, 200, 138, 0.2)',
                            borderColor: 'rgba(28, 200, 138, 1)',
                            pointBackgroundColor: 'rgba(28, 200, 138, 1)',
                            borderWidth: 2
                        },
                        {
                            label: 'Nilai Gabungan',
                            data: combinedData,
                            backgroundColor: 'rgba(246, 194, 62, 0.2)',
                            borderColor: 'rgba(246, 194, 62, 1)',
                            pointBackgroundColor: 'rgba(246, 194, 62, 1)',
                            borderWidth: 2,
                            borderDash: [5, 5]
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scale: {
                        min: 0,
                        max: 5,
                        ticks: {
                            stepSize: 1,
                            backdropColor: 'transparent'
                        },
                        pointLabels: {
                            fontSize: 12,
                            fontColor: '#666'
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 15
                            }
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>
