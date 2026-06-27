<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?? 'Perbandingan Kurikulum' ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/fontawesome/css/all.min.css') ?>">
    <style>
        .card { box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .table th { background-color: #f8f9fa; vertical-align: middle; }
        .mk-baru { background-color: #d4edda !important; }
        .mk-dihapus { background-color: #f8d7da !important; }
        .mk-perubahan { background-color: #fff3cd !important; }
    </style>
</head>
<body>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-exchange-alt"></i> <?= $title ?></h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= site_url('kurikulum') ?>">Kurikulum</a></li>
                    <li class="breadcrumb-item active">Perbandingan</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Perbandingan Kurikulum: Tahun <?= $tahun1 ?> vs <?= $tahun2 ?></h5>
            <form method="GET" class="form-inline">
                <input type="hidden" name="prodi_id" value="<?= $prodi_id ?>">
                <label class="mr-2">Tahun 1:</label>
                <select name="tahun1" class="form-control form-control-sm mr-2">
                    <?php for($y = date('Y'); $y >= date('Y')-5; $y--): ?>
                        <option value="<?= $y ?>" <?= $y == $tahun1 ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
                <label class="mr-2">Tahun 2:</label>
                <select name="tahun2" class="form-control form-control-sm mr-2">
                    <?php for($y = date('Y'); $y >= date('Y')-5; $y--): ?>
                        <option value="<?= $y ?>" <?= $y == $tahun2 ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-sync"></i> Bandingkan
                </button>
            </form>
        </div>
        <div class="card-body">
            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card bg-info text-white text-center">
                        <div class="card-body">
                            <h6>Total SKS <?= $tahun1 ?></h6>
                            <h2><?= $sks_1 ?> SKS</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-warning text-white text-center">
                        <div class="card-body">
                            <h6>Total SKS <?= $tahun2 ?></h6>
                            <h2><?= $sks_2 ?> SKS</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-<?= $sks_2 > $sks_1 ? 'success' : 'danger' ?> text-white text-center">
                        <div class="card-body">
                            <h6>Selisih</h6>
                            <h2><?= ($sks_2 - $sks_1) > 0 ? '+' : '' ?><?= $sks_2 - $sks_1 ?> SKS</h2>
                            <small><?= $sks_2 > $sks_1 ? 'Penambahan' : 'Pengurangan' ?></small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Legend -->
            <div class="mb-3">
                <strong>Keterangan:</strong>
                <span class="badge badge-success ml-2">Baru</span>
                <span class="badge badge-danger ml-2">Dihapus</span>
                <span class="badge badge-warning ml-2">Perubahan SKS</span>
            </div>

            <!-- Comparison Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th width="5%">Sem</th>
                            <th width="22%"><?= $tahun1 ?></th>
                            <th width="8%">SKS</th>
                            <th width="22%"><?= $tahun2 ?></th>
                            <th width="8%">SKS</th>
                            <th width="10%">Δ SKS</th>
                            <th width="25%">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $merged = [];
                        foreach($curricula as $c) {
                            $key = $c->semester . '_' . ($c->kode_mk ?? 'UNK');
                            if (!isset($merged[$key])) {
                                $merged[$key] = [
                                    'sem' => $c->semester,
                                    'mk1' => '-',
                                    'sks1' => 0,
                                    'mk2' => '-',
                                    'sks2' => 0,
                                    'kode' => $c->kode_mk ?? 'UNK'
                                ];
                            }
                            if ($c->tahun_kurikulum == $tahun1) {
                                $merged[$key]['mk1'] = $c->nama_mk;
                                $merged[$key]['sks1'] = $c->sks;
                                $merged[$key]['kode'] = $c->kode_mk;
                            } else {
                                $merged[$key]['mk2'] = $c->nama_mk;
                                $merged[$key]['sks2'] = $c->sks;
                                if ($merged[$key]['kode'] == 'UNK') {
                                    $merged[$key]['kode'] = $c->kode_mk;
                                }
                            }
                        }
                        
                        // Sort by semester then by kode
                        uasort($merged, function($a, $b) {
                            if ($a['sem'] == $b['sem']) {
                                return strcmp($a['kode'], $b['kode']);
                            }
                            return $a['sem'] - $b['sem'];
                        });
                        
                        foreach($merged as $item): 
                            $row_class = '';
                            $keterangan = [];
                            
                            if ($item['mk1'] == '-' && $item['mk2'] != '-') {
                                $row_class = 'mk-baru';
                                $keterangan[] = '<span class="badge badge-success">Mata Kuliah Baru</span>';
                            } elseif ($item['mk1'] != '-' && $item['mk2'] == '-') {
                                $row_class = 'mk-dihapus';
                                $keterangan[] = '<span class="badge badge-danger">Dihapus dari Kurikulum</span>';
                            } elseif ($item['sks1'] != $item['sks2']) {
                                $row_class = 'mk-perubahan';
                                $delta = $item['sks2'] - $item['sks1'];
                                $keterangan[] = '<span class="badge badge-warning">Perubahan SKS ('.($delta > 0 ? '+' : '').$delta.')</span>';
                            }
                            
                            if (empty($keterangan)) {
                                $keterangan[] = '<span class="text-muted">Tidak ada perubahan</span>';
                            }
                        ?>
                        <tr class="<?= $row_class ?>">
                            <td class="text-center"><?= $item['sem'] ?></td>
                            <td>
                                <?php if ($item['mk1'] != '-'): ?>
                                    <strong><?= $item['mk1'] ?></strong>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center"><?= $item['sks1'] > 0 ? $item['sks1'] : '-' ?></td>
                            <td>
                                <?php if ($item['mk2'] != '-'): ?>
                                    <strong><?= $item['mk2'] ?></strong>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center"><?= $item['sks2'] > 0 ? $item['sks2'] : '-' ?></td>
                            <td class="text-center font-weight-bold">
                                <?php 
                                if ($item['sks1'] > 0 && $item['sks2'] > 0) {
                                    $delta = $item['sks2'] - $item['sks1'];
                                    echo ($delta > 0 ? '+' : '') . $delta;
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>
                            <td><?= implode(' ', $keterangan) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-4 text-right">
                <a href="<?= site_url('kurikulum?prodi_id='.$prodi_id) ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="fas fa-print"></i> Cetak Laporan
                </button>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/js/jquery.min.js') ?>"></script>
<script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>"></script>
</body>
</html>
