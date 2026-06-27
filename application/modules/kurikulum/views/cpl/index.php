<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?? 'Daftar CPL' ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/fontawesome/css/all.min.css') ?>">
    <style>
        .card { box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .badge-sikap { background-color: #6f42c1; }
        .badge-pengetahuan { background-color: #007bff; }
        .badge-keterampilan_umum { background-color: #28a745; }
        .badge-keterampilan_khusus { background-color: #fd7e14; }
    </style>
</head>
<body>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-bullseye"></i> <?= $title ?></h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= site_url('kurikulum') ?>">Kurikulum & CPL</a></li>
                    <li class="breadcrumb-item active">Daftar CPL</li>
                </ol>
            </nav>
        </div>
    </div>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> <?= $this->session->flashdata('success') ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Capaian Pembelajaran Lulusan (CPL)</h5>
            <div>
                <a href="<?= site_url('gap_analysis?prodi_id='.$prodi_id) ?>" class="btn btn-info btn-sm mr-2">
                    <i class="fas fa-chart-line"></i> Gap Analysis
                </a>
                <a href="<?= site_url('cpl/create/'.$prodi_id) ?>" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Tambah CPL
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th width="10%">Kode</th>
                            <th width="35%">Deskripsi</th>
                            <th width="15%">Aspek</th>
                            <th width="10%">Target</th>
                            <th width="10%">Mapping</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($cpls)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                    <p>Belum ada CPL untuk prodi ini</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1; foreach($cpls as $cpl): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><strong><?= $cpl->kode_cpl ?></strong></td>
                                <td><?= $cpl->deskripsi ?></td>
                                <td>
                                    <span class="badge badge-<?= $cpl->aspect ?>">
                                        <?= ucfirst(str_replace('_', ' ', $cpl->aspect)) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-success" role="progressbar" 
                                             style="width: <?= ($cpl->target_industri / 5) * 100 ?>%">
                                            <?= number_format($cpl->target_industri, 1) ?>/5.0
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <a href="<?= site_url('cpl/mapping/'.$cpl->id) ?>" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-project-diagram"></i> Mapping
                                    </a>
                                </td>
                                <td>
                                    <a href="<?= site_url('cpl/edit/'.$prodi_id.'/'.$cpl->id) ?>" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?= site_url('cpl/delete/'.$prodi_id.'/'.$cpl->id) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus CPL ini?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/js/jquery.min.js') ?>"></script>
<script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>"></script>
</body>
</html>
