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
            table { font-size: 12px; }
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
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            color: white;
            font-weight: 600;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-bekerja { background-color: #2ecc71; color: white; }
        .status-wirausaha { background-color: #3498db; color: white; }
        .status-studi { background-color: #f1c40f; color: white; }
        .status-belum { background-color: #95a5a6; color: white; }
    </style>
</head>
<body>

<div class="container-fluid mt-4">
    <div class="row mb-4 no-print">
        <div class="col-md-12">
            <h2><i class="fa fa-briefcase"></i> <?= $title ?></h2>
            <p class="text-muted">Detail status kerja lulusan berdasarkan tracer study</p>
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
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="">Semua Status</option>
                            <option value="bekerja">Bekerja</option>
                            <option value="wirausaha">Wirausaha</option>
                            <option value="studi">Lanjut Studi</option>
                            <option value="belum">Belum Bekerja</option>
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
                        <a href="<?= site_url('laporan/exportPdf/status_kerja?' . http_build_query($filter)) ?>" class="btn btn-danger btn-export">
                            <i class="fa fa-file-pdf"></i> PDF
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <?php foreach($summary as $item): ?>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">
                        <?php if($item['status'] == 'bekerja'): ?>
                            <span class="status-badge status-bekerja">Bekerja</span>
                        <?php elseif($item['status'] == 'wirausaha'): ?>
                            <span class="status-badge status-wirausaha">Wirausaha</span>
                        <?php elseif($item['status'] == 'studi'): ?>
                            <span class="status-badge status-studi">Lanjut Studi</span>
                        <?php else: ?>
                            <span class="status-badge status-belum">Belum Bekerja</span>
                        <?php endif; ?>
                    </h5>
                    <h2 class="mt-3"><?= number_format($item['count']) ?></h2>
                    <p class="text-muted"><?= number_format($item['percentage'], 1) ?>% dari total</p>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Detail Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="fa fa-table"></i> Detail Data per Alumni
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>NIM</th>
                                    <th>Nama</th>
                                    <th>Program Studi</th>
                                    <th>Tahun Lulus</th>
                                    <th>Status</th>
                                    <th>Nama Perusahaan/Lembaga</th>
                                    <th>Posisi/Jabatan</th>
                                    <th>Masa Tunggu (Bulan)</th>
                                    <th>Gaji (Rp)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($results)): ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted">Tidak ada data untuk ditampilkan</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach($results as $row): ?>
                                    <tr>
                                        <td><?= $row->nim ?></td>
                                        <td><?= $row->nama ?></td>
                                        <td><?= $row->prodi ?></td>
                                        <td><?= $row->tahun_lulus ?></td>
                                        <td>
                                            <?php if($row->status == 'bekerja'): ?>
                                                <span class="status-badge status-bekerja">Bekerja</span>
                                            <?php elseif($row->status == 'wirausaha'): ?>
                                                <span class="status-badge status-wirausaha">Wirausaha</span>
                                            <?php elseif($row->status == 'studi'): ?>
                                                <span class="status-badge status-studi">Lanjut Studi</span>
                                            <?php else: ?>
                                                <span class="status-badge status-belum">Belum Bekerja</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $row->perusahaan ?? '-' ?></td>
                                        <td><?= $row->posisi ?? '-' ?></td>
                                        <td class="text-center"><?= $row->masa_tunggu ?? 0 ?></td>
                                        <td class="text-right">Rp <?= number_format($row->gaji ?? 0, 0, ',', '.') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
