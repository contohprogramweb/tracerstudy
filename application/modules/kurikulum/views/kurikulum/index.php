<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?? 'Manajemen Kurikulum' ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/fontawesome/css/all.min.css') ?>">
    <style>
        .card { box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .table th { background-color: #f8f9fa; }
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
            <h2><i class="fas fa-book"></i> <?= $title ?></h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active">Kurikulum</li>
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
            <h5 class="mb-0">Daftar Mata Kuliah - Tahun <?= $tahun ?></h5>
            <div>
                <a href="<?= site_url("kurikulum/compare?prodi_id=$prodi_id") ?>" class="btn btn-info btn-sm mr-2">
                    <i class="fas fa-exchange-alt"></i> Bandingkan Kurikulum
                </a>
                <a href="<?= site_url("kurikulum/create?prodi_id=$prodi_id") ?>" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Tambah Mata Kuliah
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="alert alert-info">
                        <strong>Total SKS:</strong> <?= $total_sks ?> SKS
                    </div>
                </div>
                <div class="col-md-6 text-right">
                    <form method="GET" class="form-inline justify-content-end">
                        <input type="hidden" name="prodi_id" value="<?= $prodi_id ?>">
                        <label class="mr-2">Tahun:</label>
                        <select name="tahun" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                            <?php for($y = date('Y'); $y >= date('Y')-5; $y--): ?>
                                <option value="<?= $y ?>" <?= $y == $tahun ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th width="10%">Kode</th>
                            <th width="25%">Nama Mata Kuliah</th>
                            <th width="5%">Sem</th>
                            <th width="5%">SKS</th>
                            <th width="15%">Jenis</th>
                            <th width="20%">CPL Terkait</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($curricula)): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                    <p>Belum ada mata kuliah untuk tahun ini</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1; foreach($curricula as $mk): 
                                $cpl_array = json_decode($mk->cpl_related, true) ?? [];
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><strong><?= $mk->kode_mk ?></strong></td>
                                <td><?= $mk->nama_mk ?></td>
                                <td class="text-center"><?= $mk->semester ?></td>
                                <td class="text-center"><?= $mk->sks ?></td>
                                <td><span class="badge badge-secondary"><?= ucfirst($mk->jenis) ?></span></td>
                                <td>
                                    <?php foreach($cpl_array as $cpl): ?>
                                        <span class="badge badge-info"><?= $cpl ?></span>
                                    <?php endforeach; ?>
                                </td>
                                <td>
                                    <a href="<?= site_url('kurikulum/edit/'.$mk->id) ?>" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?= site_url('kurikulum/delete/'.$mk->id) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus mata kuliah ini?')">
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
