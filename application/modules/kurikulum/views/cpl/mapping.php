<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?? 'Pemetaan CPL' ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/fontawesome/css/all.min.css') ?>">
    <style>
        .card { box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
    </style>
</head>
<body>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-project-diagram"></i> Pemetaan CPL - <?= $cpl->kode_cpl ?></h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= site_url('kurikulum') ?>">Kurikulum & CPL</a></li>
                    <li class="breadcrumb-item"><a href="<?= site_url('cpl/index/'.$cpl->prodi_id) ?>">Daftar CPL</a></li>
                    <li class="breadcrumb-item active">Pemetaan</li>
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

    <div class="row">
        <div class="col-md-6">
            <!-- Form Pemetaan SN-Dikti -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-university"></i> Pemetaan SN-Dikti</h5>
                </div>
                <div class="card-body">
                    <form action="<?= site_url('cpl/saveSnDikti/'.$cpl->id) ?>" method="POST">
                        <div class="form-group">
                            <label for="sn_code">Kode SN-Dikti <span class="text-danger">*</span></label>
                            <input type="text" name="sn_code" id="sn_code" class="form-control" placeholder="Contoh: S-1-TI-01" required>
                            <small class="form-text text-muted">Format: Jenjang-Prodi-Nomor</small>
                        </div>
                        <div class="form-group">
                            <label for="sn_desc">Deskripsi Kesesuaian <span class="text-danger">*</span></label>
                            <textarea name="sn_desc" id="sn_desc" class="form-control" rows="3" placeholder="Jelaskan kesesuaian CPL ini dengan standar SN-Dikti" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Mapping SN-Dikti
                        </button>
                    </form>
                </div>
            </div>

            <!-- Form Pemetaan KKNI -->
            <div class="card mt-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-layer-group"></i> Pemetaan KKNI</h5>
                </div>
                <div class="card-body">
                    <form action="<?= site_url('cpl/saveKkni/'.$cpl->id) ?>" method="POST">
                        <div class="form-group">
                            <label for="kkni_level">Level KKNI <span class="text-danger">*</span></label>
                            <select name="kkni_level" id="kkni_level" class="form-control" required>
                                <option value="">Pilih Level</option>
                                <option value="5">Level 5 (Diploma III)</option>
                                <option value="6" selected>Level 6 (Sarjana/Diploma IV)</option>
                                <option value="7">Level 7 (Magister/Spesialis)</option>
                                <option value="8">Level 8 (Doktor)</option>
                                <option value="9">Level 9 (Profesor Riset)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="kkni_desc">Deskripsi Descriptor <span class="text-danger">*</span></label>
                            <textarea name="kkni_desc" id="kkni_desc" class="form-control" rows="3" placeholder="Jelaskan descriptor KKNI yang sesuai" required></textarea>
                            <small class="form-text text-muted">Sesuaikan dengan deskripsi level KKNI terpilih</small>
                        </div>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Simpan Mapping KKNI
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <!-- Info CPL -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Informasi CPL</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th width="30%">Kode</th>
                            <td><?= $cpl->kode_cpl ?></td>
                        </tr>
                        <tr>
                            <th>Aspek</th>
                            <td><span class="badge badge-info"><?= ucfirst(str_replace('_', ' ', $cpl->aspect)) ?></span></td>
                        </tr>
                        <tr>
                            <th>Deskripsi</th>
                            <td><?= $cpl->deskripsi ?></td>
                        </tr>
                        <tr>
                            <th>Target Industri</th>
                            <td><?= number_format($cpl->target_industri, 1) ?> / 5.0</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Riwayat Mapping -->
            <div class="card mt-4">
                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-history"></i> Riwayat Mapping</h5>
                    <span class="badge badge-light"><?= count($mappings) ?> Mapping</span>
                </div>
                <div class="card-body">
                    <?php if (empty($mappings)): ?>
                        <p class="text-muted text-center py-4">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <br>Belum ada mapping untuk CPL ini
                        </p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>Tipe</th>
                                        <th>Kode/Level</th>
                                        <th>Deskripsi</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($mappings as $map): ?>
                                    <tr>
                                        <td>
                                            <span class="badge badge-<?= $map->type == 'SN_DIPTI' ? 'primary' : 'success' ?>">
                                                <?= str_replace('_', '-', $map->type) ?>
                                            </span>
                                        </td>
                                        <td><?= $map->code ?? 'Level ' . $map->level ?></td>
                                        <td><?= $map->description ?? $map->descriptor ?></td>
                                        <td>
                                            <a href="#" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus mapping ini?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- KUR-006 Info -->
            <div class="card mt-4 bg-light">
                <div class="card-body">
                    <h6><i class="fas fa-info-circle"></i> Tentang Pemetaan CPL</h6>
                    <p class="mb-0 small">
                        <strong>KUR-006:</strong> Pemetaan CPL ke SN-Dikti dan KKNI wajib dilakukan untuk memastikan 
                        kurikulum memenuhi standar nasional pendidikan tinggi. Mapping ini digunakan untuk akreditasi 
                        BAN-PT dan penjaminan mutu internal.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/js/jquery.min.js') ?>"></script>
<script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>"></script>
</body>
</html>
