<div class="container-fluid py-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="mb-0 text-primary"><i class="fas fa-user-edit me-2"></i><?php echo $page_title; ?></h5>
            <small class="text-muted"><?php echo $page_subtitle; ?></small>
        </div>
        <div class="card-body">
            <?php if (validation_errors()): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo validation_errors(); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <form action="<?php echo site_url('alumni/update/' . $alumni->id); ?>" method="post">
                <?php echo csrf_field(); ?>
                
                <div class="row g-4">
                    <!-- Personal Information -->
                    <div class="col-md-6">
                        <h6 class="border-bottom pb-2 mb-3 text-primary"><i class="fas fa-user me-2"></i>Informasi Pribadi</h6>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">NIM</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($alumni->nim); ?>" disabled>
                            <small class="text-muted">BR-ALM-002: NIM tidak dapat diubah (immutable)</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="nama" class="form-control" value="<?php echo set_value('nama', $alumni->nama); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Email</label>
                            <input type="email" name="email" class="form-control" value="<?php echo set_value('email', $alumni->email); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">No. HP</label>
                            <input type="text" name="no_hp" class="form-control" value="<?php echo set_value('no_hp', $alumni->no_hp); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Email Verified</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="email_verified" id="email_verified" <?php echo $alumni->email_verified ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="email_verified">
                                    <?php echo $alumni->email_verified ? '<span class="badge bg-success">Verified</span>' : '<span class="badge bg-secondary">Not Verified</span>'; ?>
                                </label>
                            </div>
                            <small class="text-muted">BR-ALM-005: Alumni belum verifikasi email boleh isi survey tapi tidak masuk IKU</small>
                        </div>
                    </div>

                    <!-- Academic Information -->
                    <div class="col-md-6">
                        <h6 class="border-bottom pb-2 mb-3 text-primary"><i class="fas fa-graduation-cap me-2"></i>Informasi Akademik</h6>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Program Studi <span class="text-danger">*</span></label>
                            <select name="prodi_id" class="form-select" required>
                                <option value="">Pilih Prodi</option>
                                <?php foreach ($prodis as $prodi): ?>
                                <option value="<?php echo $prodi['id']; ?>" <?php echo ($prodi['id'] == $alumni->prodi_id) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($prodi['nama']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Kohort</label>
                            <select name="kohort_id" class="form-select">
                                <option value="">Pilih Kohort</option>
                                <?php foreach ($kohorts as $kohort): ?>
                                <option value="<?php echo $kohort['id']; ?>" <?php echo ($kohort['id'] == $alumni->kohort_id) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($kohort['nama']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tahun Lulus</label>
                            <input type="number" name="tahun_lulus" class="form-control" value="<?php echo set_value('tahun_lulus', $alumni->tahun_lulus); ?>" min="2000" max="<?php echo date('Y')+1; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tanggal Yudisium</label>
                            <input type="date" name="tanggal_yudisium" class="form-control" value="<?php echo set_value('tanggal_yudisium', $alumni->tanggal_yudisium); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Status Aktif</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="status_aktif" id="status_aktif" <?php echo $alumni->status_aktif ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="status_aktif">Aktif</label>
                            </div>
                            <small class="text-muted">BR-ALM-010: Alumni nonaktif dikecualikan dari target populasi</small>
                        </div>
                    </div>

                    <!-- Employment Information -->
                    <div class="col-12">
                        <h6 class="border-bottom pb-2 mb-3 text-primary"><i class="fas fa-briefcase me-2"></i>Informasi Pekerjaan</h6>
                        
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Status Kerja <span class="text-danger">*</span></label>
                                <select name="status_kerja" id="status_kerja" class="form-select" required onchange="toggleEmploymentFields()">
                                    <?php foreach ($status_kerja_options as $value => $label): ?>
                                    <option value="<?php echo $value; ?>" <?php echo ($alumni->status_kerja === $value) ? 'selected' : ''; ?>>
                                        <?php echo $label; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">BR-ALM-003: Belum bekerja tidak boleh isi gaji/perusahaan</small>
                            </div>
                            
                            <div class="col-md-4 employment-field">
                                <label class="form-label">Perusahaan</label>
                                <input type="text" name="perusahaan" id="perusahaan" class="form-control" value="<?php echo set_value('perusahaan', $alumni->perusahaan); ?>">
                            </div>
                            
                            <div class="col-md-4 employment-field">
                                <label class="form-label">Jabatan</label>
                                <input type="text" name="jabatan" id="jabatan" class="form-control" value="<?php echo set_value('jabatan', $alumni->jabatan); ?>">
                            </div>
                            
                            <div class="col-md-4 employment-field">
                                <label class="form-label">Gaji (Rp)</label>
                                <input type="text" name="gaji" id="gaji" class="form-control" value="<?php echo set_value('gaji', $alumni->gaji ? number_format($alumni->gaji, 0, ',', '.') : ''); ?>" onkeyup="formatCurrency(this)">
                            </div>
                            
                            <div class="col-md-4 employment-field">
                                <label class="form-label">Tanggal Mulai Kerja</label>
                                <input type="date" name="tanggal_mulai_kerja" id="tanggal_mulai_kerja" class="form-control" value="<?php echo set_value('tanggal_mulai_kerja', $alumni->tanggal_mulai_kerja); ?>">
                                <small class="text-muted">BR-ALM-004: Tidak boleh sebelum yudisium</small>
                            </div>
                            
                            <div class="col-md-4 employment-field">
                                <label class="form-label">Lokasi Kerja</label>
                                <input type="text" name="lokasi_kerja" id="lokasi_kerja" class="form-control" value="<?php echo set_value('lokasi_kerja', $alumni->lokasi_kerja); ?>">
                            </div>
                            
                            <div class="col-md-4 employment-field">
                                <label class="form-label">Jenis Pekerjaan</label>
                                <select name="jenis_pekerjaan" id="jenis_pekerjaan" class="form-select">
                                    <option value="">Pilih Jenis</option>
                                    <option value="full_time" <?php echo ($alumni->jenis_pekerjaan === 'full_time') ? 'selected' : ''; ?>>Full Time</option>
                                    <option value="part_time" <?php echo ($alumni->jenis_pekerjaan === 'part_time') ? 'selected' : ''; ?>>Part Time</option>
                                    <option value="contract" <?php echo ($alumni->jenis_pekerjaan === 'contract') ? 'selected' : ''; ?>>Contract</option>
                                    <option value="freelance" <?php echo ($alumni->jenis_pekerjaan === 'freelance') ? 'selected' : ''; ?>>Freelance</option>
                                </select>
                            </div>
                            
                            <div class="col-md-4 employment-field">
                                <label class="form-label">Kesesuaian Bidang</label>
                                <select name="kesesuaian_bidang" id="kesesuaian_bidang" class="form-select">
                                    <option value="">Pilih Kesesuaian</option>
                                    <option value="sangat_sesuai" <?php echo ($alumni->kesesuaian_bidang === 'sangat_sesuai') ? 'selected' : ''; ?>>Sangat Sesuai</option>
                                    <option value="sesuai" <?php echo ($alumni->kesesuaian_bidang === 'sesuai') ? 'selected' : ''; ?>>Sesuai</option>
                                    <option value="kurang_sesuai" <?php echo ($alumni->kesesuaian_bidang === 'kurang_sesuai') ? 'selected' : ''; ?>>Kurang Sesuai</option>
                                    <option value="tidak_sesuai" <?php echo ($alumni->kesesuaian_bidang === 'tidak_sesuai') ? 'selected' : ''; ?>>Tidak Sesuai</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top d-flex justify-content-between">
                    <a href="<?php echo site_url('alumni/detail/' . $alumni->id); ?>" class="btn btn-info">
                        <i class="fas fa-eye me-1"></i> Lihat Detail
                    </a>
                    <div>
                        <a href="<?php echo site_url('alumni'); ?>" class="btn btn-secondary me-2">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Update Alumni
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Toggle employment fields based on status
function toggleEmploymentFields() {
    var status = document.getElementById('status_kerja').value;
    var fields = document.querySelectorAll('.employment-field');
    
    if (status === 'belum_bekerja') {
        fields.forEach(function(field) {
            field.style.display = 'none';
            field.querySelector('input, select').value = '';
            field.querySelector('input, select').disabled = true;
        });
    } else {
        fields.forEach(function(field) {
            field.style.display = 'block';
            field.querySelector('input, select').disabled = false;
        });
    }
}

// Format currency input
function formatCurrency(input) {
    var value = input.value.replace(/[^0-9]/g, '');
    if (value) {
        input.value = parseInt(value).toLocaleString('id-ID');
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleEmploymentFields();
});
</script>
