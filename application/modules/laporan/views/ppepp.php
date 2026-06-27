<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
            background: linear-gradient(135deg, #1abc9c 0%, #16a085 100%);
            color: white;
            font-weight: 600;
        }
        
        .ppepp-cycle {
            position: relative;
            padding: 30px;
        }
        
        .cycle-step {
            text-align: center;
            padding: 20px;
            border-radius: 50%;
            background: #1abc9c;
            color: white;
            width: 120px;
            height: 120px;
            display: inline-flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            margin: 10px;
        }
        
        .cycle-arrow {
            font-size: 24px;
            color: #1abc9c;
            vertical-align: middle;
        }
    </style>
</head>
<body>

<div class="container-fluid mt-4">
    <div class="row mb-4 no-print">
        <div class="col-md-12">
            <h2><i class="fa fa-sync"></i> <?= $title ?></h2>
            <p class="text-muted">Siklus Penjaminan Mutu PPEPP (Penetapan, Pelaksanaan, Evaluasi, Pengendalian, Peningkatan)</p>
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
                            <?php for($y=date('Y'); $y>=date('Y')-5; $y--): ?>
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
                        <a href="<?= site_url('laporan/exportPdf/ppepp?' . http_build_query($filter)) ?>" class="btn btn-danger btn-export">
                            <i class="fa fa-file-pdf"></i> PDF
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- PPEPP Cycle Visualization -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="fa fa-retweet"></i> Siklus PPEPP
                </div>
                <div class="card-body">
                    <div class="text-center ppepp-cycle">
                        <div class="cycle-step">
                            <i class="fa fa-bullseye fa-2x mb-2"></i>
                            <strong>Penetapan</strong>
                            <small>Standar & Target</small>
                        </div>
                        <span class="cycle-arrow"><i class="fa fa-arrow-right"></i></span>
                        <div class="cycle-step">
                            <i class="fa fa-cogs fa-2x mb-2"></i>
                            <strong>Pelaksanaan</strong>
                            <small>Implementasi</small>
                        </div>
                        <span class="cycle-arrow"><i class="fa fa-arrow-right"></i></span>
                        <div class="cycle-step">
                            <i class="fa fa-clipboard-check fa-2x mb-2"></i>
                            <strong>Evaluasi</strong>
                            <small>Monitoring & Asesmen</small>
                        </div>
                        <span class="cycle-arrow"><i class="fa fa-arrow-right"></i></span>
                        <div class="cycle-step">
                            <i class="fa fa-chart-line fa-2x mb-2"></i>
                            <strong>Pengendalian</strong>
                            <small>Analisis & Koreksi</small>
                        </div>
                        <span class="cycle-arrow"><i class="fa fa-arrow-right"></i></span>
                        <div class="cycle-step">
                            <i class="fa fa-arrow-up fa-2x mb-2"></i>
                            <strong>Peningkatan</strong>
                            <small>Continuous Improvement</small>
                        </div>
                        <br>
                        <span class="cycle-arrow"><i class="fa fa-arrow-left"></i></span>
                        <small class="d-block mt-3 text-muted">Siklus berlanjut secara terus-menerus</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Indikator Mutu -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="fa fa-list-check"></i> Indikator Kinerja Utama (IKU)
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>No</th>
                                <th>Indikator</th>
                                <th>Target</th>
                                <th>Realisasi</th>
                                <th>Capaian (%)</th>
                                <th>Status</th>
                                <th>Tindak Lanjut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($indikator_mutu)): ?>
                                <?php foreach($indikator_mutu as $idx => $item): 
                                    $capaian = ($item['target'] > 0) ? ($item['realisasi'] / $item['target']) * 100 : 0;
                                    $status_class = $capaian >= 100 ? 'badge-success' : ($capaian >= 80 ? 'badge-warning' : 'badge-danger');
                                ?>
                                <tr>
                                    <td><?= $idx + 1 ?></td>
                                    <td><?= $item['nama_indikator'] ?></td>
                                    <td><?= number_format($item['target'], 1) ?>%</td>
                                    <td><?= number_format($item['realisasi'], 1) ?>%</td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar <?= $capaian >= 100 ? 'bg-success' : ($capaian >= 80 ? 'bg-warning' : 'bg-danger') ?>" 
                                                 role="progressbar" style="width: <?= min($capaian, 100) ?>%;" 
                                                 aria-valuenow="<?= $capaian ?>" aria-valuemin="0" aria-valuemax="100">
                                                <?= number_format($capaian, 1) ?>%
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge <?= $status_class ?>"><?= $capaian >= 100 ? 'Tercapai' : ($capaian >= 80 ? 'Hampir Tercapai' : 'Belum Tercapai') ?></span></td>
                                    <td><?= $item['tindak_lanjut'] ?? '-' ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Data indikator mutu belum tersedia</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Tindak Lanjut -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <i class="fa fa-tasks"></i> Rencana Tindak Lanjut (RTL)
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Masalah/Kendala</th>
                                <th>Akar Masalah</th>
                                <th>Rencana Aksi</th>
                                <th>Penanggung Jawab</th>
                                <th>Timeline</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($tindak_lanjut)): ?>
                                <?php foreach($tindak_lanjut as $idx => $item): ?>
                                <tr>
                                    <td><?= $idx + 1 ?></td>
                                    <td><?= $item['masalah'] ?></td>
                                    <td><?= $item['akar_masalah'] ?></td>
                                    <td><?= $item['rencana_aksi'] ?></td>
                                    <td><?= $item['penanggung_jawab'] ?></td>
                                    <td><?= $item['timeline'] ?></td>
                                    <td>
                                        <?php if($item['status'] == 'selesai'): ?>
                                            <span class="badge badge-success">Selesai</span>
                                        <?php elseif($item['status'] == 'proses'): ?>
                                            <span class="badge badge-warning">Dalam Proses</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Belum Dimulai</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Belum ada rencana tindak lanjut</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary PPEPP -->
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Total Indikator</h5>
                    <h2 class="text-primary"><?= count($indikator_mutu ?? []) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Indikator Tercapai</h5>
                    <?php 
                    $tercapai = 0;
                    foreach($indikator_mutu ?? [] as $item) {
                        $capaian = ($item['target'] > 0) ? ($item['realisasi'] / $item['target']) * 100 : 0;
                        if ($capaian >= 100) $tercapai++;
                    }
                    ?>
                    <h2 class="text-success"><?= $tercapai ?></h2>
                    <small class="text-muted"><?= count($indikator_mutu ?? []) > 0 ? number_format(($tercapai / count($indikator_mutu)) * 100, 1) : 0 ?>% dari total</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Tindak Lanjut Selesai</h5>
                    <?php 
                    $rtl_selesai = 0;
                    foreach($tindak_lanjut ?? [] as $item) {
                        if ($item['status'] == 'selesai') $rtl_selesai++;
                    }
                    ?>
                    <h2 class="text-info"><?= $rtl_selesai ?></h2>
                    <small class="text-muted">dari <?= count($tindak_lanjut ?? []) ?> RTL</small>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
