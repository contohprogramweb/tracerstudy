<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4"><i class="fas fa-list"></i> <?= $page_title ?></h2>
            
            <a href="<?= site_url('iku/dashboard') ?>" class="btn btn-secondary mb-3">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>

    <!-- Calculation Info -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Informasi Perhitungan</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <strong>Kohort:</strong><br>
                    <?= $calculation->kohort_nama ?>
                </div>
                <div class="col-md-3">
                    <strong>Prodi:</strong><br>
                    <?= $calculation->prodi_nama ?: 'Semua Prodi' ?>
                </div>
                <div class="col-md-3">
                    <strong>Tahun IKU:</strong><br>
                    <?= $calculation->tahun_iku ?>
                </div>
                <div class="col-md-3">
                    <strong>Dihitung pada:</strong><br>
                    <?= date('d/m/Y H:i', strtotime($calculation->created_at)) ?>
                </div>
            </div>
            <hr>
            <div class="row text-center">
                <div class="col-md-3">
                    <div class="alert alert-info">
                        <h4><?= $calculation->percentage ?>%</h4>
                        <small>Score IKU</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-success">
                        <h4><?= $calculation->numerator ?></h4>
                        <small>Total Bobot</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-warning">
                        <h4><?= $calculation->denominator ?></h4>
                        <small>Total Responden</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-<?= $calculation->status_capaian === 'Melampaui' ? 'success' : ($calculation->status_capaian === 'Tercapai' ? 'info' : 'warning') ?>">
                        <h4><?= $calculation->status_capaian ?></h4>
                        <small>Status Capaian (Target: <?= $calculation->target_percentage ?>%)</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Alumni Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Detail Perhitungan per Alumni</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm">
                    <thead class="thead-light">
                        <tr>
                            <th>No</th>
                            <th>NIM</th>
                            <th>Nama</th>
                            <th>Status</th>
                            <th>Provinsi Domisili</th>
                            <th>Gaji/Omzet</th>
                            <th>Bobot</th>
                            <th>Kategori</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($alumni_detail)): ?>
                            <?php foreach ($alumni_detail as $idx => $alumni): ?>
                                <tr class="<?= $alumni['bobot'] >= 0.8 ? 'table-success' : '' ?>">
                                    <td><?= $idx + 1 ?></td>
                                    <td><code><?= $alumni['nim'] ?></code></td>
                                    <td><?= $alumni['nama_lengkap'] ?></td>
                                    <td>
                                        <span class="badge badge-<?= $alumni['status_tracing'] === 'sudah_responden' ? 'success' : 'secondary' ?>">
                                            <?= $alumni['status_tracing'] ?>
                                        </span>
                                    </td>
                                    <td><?= $alumni['provinsi_domisili'] ?: '-' ?></td>
                                    <td>
                                        <?php 
                                        $gaji = !empty($alumni['gaji_aktual']) ? $alumni['gaji_aktual'] : $alumni['gaji'];
                                        echo $gaji ? 'Rp ' . number_format($gaji, 0, ',', '.') : '-';
                                        ?>
                                    </td>
                                    <td>
                                        <strong><?= $alumni['bobot'] ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge badge-info"><?= $alumni['kategori'] ?></span>
                                    </td>
                                    <td><small><?= $alumni['keterangan'] ?></small></td>
                                </tr>
                            <?php endforeach; ?>
                            
                            <!-- Summary Row -->
                            <tr class="table-active">
                                <td colspan="6" class="text-right"><strong>Total:</strong></td>
                                <td colspan="3"><strong><?= array_sum(array_column($alumni_detail, 'bobot')) ?></strong></td>
                            </tr>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted">Tidak ada data alumni</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Legend -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">Legenda Bobot</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>Bekerja:</h6>
                    <ul class="small">
                        <li><strong>1.0</strong> - Gaji ≥ 1.2 UMP + Masa tunggu ≤ 6 bulan</li>
                        <li><strong>0.8</strong> - Gaji ≥ 1.2 UMP + Masa tunggu 7-12 bulan</li>
                        <li><strong>0.8</strong> - Gaji < 1.2 UMP + Masa tunggu ≤ 6 bulan</li>
                        <li><strong>0.6</strong> - Gaji < 1.2 UMP + Masa tunggu 7-12 bulan</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6>Lainnya:</h6>
                    <ul class="small">
                        <li><strong>1.0</strong> - Wirausaha (omzet ≥ UMP)</li>
                        <li><strong>0.8</strong> - Wirausaha (omzet < UMP)</li>
                        <li><strong>0.6</strong> - Lanjut Studi</li>
                        <li><strong>0</strong> - Belum Bekerja</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
