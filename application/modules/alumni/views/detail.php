<div class="container-fluid py-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
            <div>
                <h5 class="mb-0 text-primary"><i class="fas fa-user-graduate me-2"></i><?php echo $page_title; ?></h5>
                <small class="text-muted"><?php echo $page_subtitle; ?></small>
            </div>
            <div>
                <a href="<?php echo site_url('alumni/edit/' . $alumni->id); ?>" class="btn btn-primary btn-sm me-1">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
                <a href="<?php echo site_url('alumni'); ?>" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </div>
        <div class="card-body">
            <?php if ($this->session->flashdata('message')): ?>
            <div class="alert alert-<?php echo $this->session->flashdata('message_type') ?? 'info'; ?> alert-dismissible fade show" role="alert">
                <i class="fas fa-info-circle me-2"></i>
                <?php echo $this->session->flashdata('message'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <div class="row g-4">
                <!-- Personal Information -->
                <div class="col-md-6">
                    <div class="card h-100 border-primary">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="fas fa-user me-2"></i>Informasi Pribadi</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <th width="40%"><i class="fas fa-id-card me-2"></i>NIM</th>
                                    <td><strong><?php echo htmlspecialchars($alumni->nim); ?></strong></td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-user me-2"></i>Nama</th>
                                    <td><?php echo htmlspecialchars($alumni->nama); ?></td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-envelope me-2"></i>Email</th>
                                    <td>
                                        <?php echo htmlspecialchars($alumni->email ?? '-'); ?>
                                        <?php if ($alumni->email_verified): ?>
                                            <span class="badge bg-success ms-1"><i class="fas fa-check-circle"></i> Verified</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary ms-1">Not Verified</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-phone me-2"></i>No. HP</th>
                                    <td><?php echo htmlspecialchars($alumni->no_hp ?? '-'); ?></td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-map-marker-alt me-2"></i>Domisili</th>
                                    <td>
                                        <?php 
                                        $domisili = [];
                                        if (!empty($alumni->kota_domisili)) $domisili[] = $alumni->kota_domisili;
                                        if (!empty($alumni->provinsi_domisili)) $domisili[] = $alumni->provinsi_domisili;
                                        if (!empty($alumni->negara_domisili)) $domisili[] = $alumni->negara_domisili;
                                        echo !empty($domisili) ? implode(', ', $domisili) : '-';
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th><i class="fab fa-linkedin me-2"></i>Social Media</th>
                                    <td>
                                        <?php if (!empty($alumni->linkedin_url)): ?>
                                            <a href="<?php echo htmlspecialchars($alumni->linkedin_url); ?>" target="_blank" class="me-2">
                                                <i class="fab fa-linkedin text-primary"></i> LinkedIn
                                            </a>
                                        <?php endif; ?>
                                        <?php if (!empty($alumni->instagram_url)): ?>
                                            <a href="<?php echo htmlspecialchars($alumni->instagram_url); ?>" target="_blank">
                                                <i class="fab fa-instagram text-danger"></i> Instagram
                                            </a>
                                        <?php endif; ?>
                                        <?php if (empty($alumni->linkedin_url) && empty($alumni->instagram_url)): ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Academic Information -->
                <div class="col-md-6">
                    <div class="card h-100 border-info">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0"><i class="fas fa-graduation-cap me-2"></i>Informasi Akademik</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <th width="40%"><i class="fas fa-university me-2"></i>Program Studi</th>
                                    <td><?php echo htmlspecialchars($prodi_nama); ?></td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-users me-2"></i>Kohort</th>
                                    <td><?php echo htmlspecialchars($kohort_nama ?? '-'); ?></td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-calendar me-2"></i>Tahun Lulus</th>
                                    <td><?php echo $alumni->tahun_lulus; ?></td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-scroll me-2"></i>Tanggal Yudisium</th>
                                    <td><?php echo !empty($alumni->tanggal_yudisium) ? date('d M Y', strtotime($alumni->tanggal_yudisium)) : '-'; ?></td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-toggle-on me-2"></i>Status Aktif</th>
                                    <td>
                                        <?php if ($alumni->status_aktif): ?>
                                            <span class="badge bg-success">Aktif</span>
                                            <small class="text-muted d-block">BR-ALM-010: Termasuk dalam target populasi IKU</small>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Nonaktif</span>
                                            <small class="text-muted d-block">BR-ALM-010: Dikecualikan dari target populasi IKU</small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-sync me-2"></i>Sync Status</th>
                                    <td>
                                        <?php if (!empty($alumni->synchronized_from_pddikti)): ?>
                                            <span class="badge bg-primary">Synced from PDDikti</span>
                                            <small class="text-muted d-block">Last sync: <?php echo !empty($alumni->last_sync_at) ? date('d M Y H:i', strtotime($alumni->last_sync_at)) : '-'; ?></small>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Manual Entry</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Employment Information -->
                <div class="col-12">
                    <div class="card border-success">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0"><i class="fas fa-briefcase me-2"></i>Informasi Pekerjaan</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <label class="text-muted small fw-bold">STATUS KERJA</label>
                                    <div>
                                        <?php
                                        $status_badges = [
                                            'bekerja' => 'bg-success',
                                            'belum_bekerja' => 'bg-secondary',
                                            'wirausaha' => 'bg-info',
                                            'melanjutkan_studi' => 'bg-warning'
                                        ];
                                        $badge_class = $status_badges[$alumni->status_kerja] ?? 'bg-light';
                                        ?>
                                        <span class="badge <?php echo $badge_class; ?> fs-6">
                                            <?php echo ucfirst(str_replace('_', ' ', $alumni->status_kerja)); ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <?php if ($alumni->status_kerja !== 'belum_bekerja'): ?>
                                <div class="col-md-4">
                                    <label class="text-muted small fw-bold">PERUSAHAAN</label>
                                    <div><?php echo htmlspecialchars($alumni->perusahaan ?? '-'); ?></div>
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="text-muted small fw-bold">JABATAN</label>
                                    <div><?php echo htmlspecialchars($alumni->jabatan ?? '-'); ?></div>
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="text-muted small fw-bold">GAJI</label>
                                    <div>
                                        <?php if (!empty($alumni->gaji)): ?>
                                            Rp <?php echo number_format($alumni->gaji, 0, ',', '.'); ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="text-muted small fw-bold">TANGGAL MULAI KERJA</label>
                                    <div>
                                        <?php if (!empty($alumni->tanggal_mulai_kerja)): ?>
                                            <?php echo date('d M Y', strtotime($alumni->tanggal_mulai_kerja)); ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="text-muted small fw-bold">MASA TUNGGU</label>
                                    <div>
                                        <?php if ($masa_tunggu !== NULL): ?>
                                            <strong class="text-primary"><?php echo $masa_tunggu; ?> bulan</strong>
                                        <?php elseif (!empty($alumni->tanggal_yudisium) && !empty($alumni->tanggal_mulai_kerja)): ?>
                                            <span class="text-danger">Invalid date</span>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="text-muted small fw-bold">LOKASI KERJA</label>
                                    <div><?php echo htmlspecialchars($alumni->lokasi_kerja ?? '-'); ?></div>
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="text-muted small fw-bold">JENIS PEKERJAAN</label>
                                    <div>
                                        <?php
                                        $jenis_labels = [
                                            'full_time' => 'Full Time',
                                            'part_time' => 'Part Time',
                                            'contract' => 'Contract',
                                            'freelance' => 'Freelance'
                                        ];
                                        echo !empty($alumni->jenis_pekerjaan) ? ($jenis_labels[$alumni->jenis_pekerjaan] ?? $alumni->jenis_pekerjaan) : '-';
                                        ?>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="text-muted small fw-bold">KESESUAIAN BIDANG</label>
                                    <div>
                                        <?php
                                        $kesesuaian_labels = [
                                            'sangat_sesuai' => 'Sangat Sesuai',
                                            'sesuai' => 'Sesuai',
                                            'kurang_sesuai' => 'Kurang Sesuai',
                                            'tidak_sesuai' => 'Tidak Sesuai'
                                        ];
                                        $kesesuaian_class = [
                                            'sangat_sesuai' => 'text-success',
                                            'sesuai' => 'text-primary',
                                            'kurang_sesuai' => 'text-warning',
                                            'tidak_sesuai' => 'text-danger'
                                        ];
                                        if (!empty($alumni->kesesuaian_bidang)):
                                        ?>
                                            <span class="<?php echo $kesesuaian_class[$alumni->kesesuaian_bidang] ?? ''; ?>">
                                                <?php echo $kesesuaian_labels[$alumni->kesesuaian_bidang] ?? $alumni->kesesuaian_bidang; ?>
                                            </span>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php else: ?>
                                <div class="col-12">
                                    <div class="alert alert-secondary mb-0">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Alumni ini berstatus <strong>Belum Bekerja</strong>. Data pekerjaan tidak tersedia.
                                        <br><small class="text-muted">BR-ALM-003: Alumni belum_bekerja tidak boleh isi gaji/perusahaan/jabatan</small>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Metadata -->
                <div class="col-12">
                    <div class="card border-light">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-clock me-2"></i>Metadata</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <small class="text-muted">Created At</small>
                                    <div><?php echo !empty($alumni->created_at) ? date('d M Y H:i:s', strtotime($alumni->created_at)) : '-'; ?></div>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted">Updated At</small>
                                    <div><?php echo !empty($alumni->updated_at) ? date('d M Y H:i:s', strtotime($alumni->updated_at)) : '-'; ?></div>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted">Deleted At</small>
                                    <div><?php echo !empty($alumni->deleted_at) ? date('d M Y H:i:s', strtotime($alumni->deleted_at)) : '<span class="text-success">Active</span>'; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
