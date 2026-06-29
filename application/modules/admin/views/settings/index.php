<div class="container-fluid py-4">
    <?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i><?= $this->session->flashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-circle me-2"></i><?= $this->session->flashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <?php if ($this->session->flashdata('info')): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="bi bi-info-circle me-2"></i><?= $this->session->flashdata('info') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <form action="<?= base_url('admin/settings/save') ?>" method="POST">
        <!-- General Settings -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-gear me-2"></i>Pengaturan Umum</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <label for="site_name" class="col-sm-3 col-form-label">Nama Situs <span class="text-danger">*</span></label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="site_name" name="site_name" value="<?= htmlspecialchars($settings['site_name'] ?? '') ?>" required>
                        <small class="text-muted">Nama yang ditampilkan di header dan title halaman</small>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <label for="site_description" class="col-sm-3 col-form-label">Deskripsi Situs</label>
                    <div class="col-sm-9">
                        <textarea class="form-control" id="site_description" name="site_description" rows="2"><?= htmlspecialchars($settings['site_description'] ?? '') ?></textarea>
                        <small class="text-muted">Deskripsi singkat tentang sistem tracer study</small>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <label for="logo_text" class="col-sm-3 col-form-label">Teks Logo</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="logo_text" name="logo_text" value="<?= htmlspecialchars($settings['logo_text'] ?? '') ?>">
                        <small class="text-muted">Teks yang ditampilkan di sidebar sebagai logo</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Appearance Settings -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-palette me-2"></i>Tampilan</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <label for="primary_color" class="col-sm-3 col-form-label">Warna Utama</label>
                    <div class="col-sm-3">
                        <input type="color" class="form-control form-control-color" id="primary_color" name="primary_color" value="<?= htmlspecialchars($settings['primary_color'] ?? '#667eea') ?>" style="width: 100%;">
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted">Warna utama untuk tema aplikasi</small>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <label for="secondary_color" class="col-sm-3 col-form-label">Warna Sekunder</label>
                    <div class="col-sm-3">
                        <input type="color" class="form-control form-control-color" id="secondary_color" name="secondary_color" value="<?= htmlspecialchars($settings['secondary_color'] ?? '#764ba2') ?>" style="width: 100%;">
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted">Warna sekunder untuk gradient dan aksen</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Registration Settings -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-person-plus me-2"></i>Pendaftaran</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Aktifkan Pendaftaran</label>
                    <div class="col-sm-9">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="enable_registration" name="enable_registration" <?= ($settings['enable_registration'] ?? '1') == '1' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="enable_registration">
                                Izinkan pengguna baru mendaftar
                            </label>
                        </div>
                        <small class="text-muted d-block mt-1">Jika dimatikan, hanya admin yang bisa menambahkan user</small>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Verifikasi Email</label>
                    <div class="col-sm-9">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="require_email_verification" name="require_email_verification" <?= ($settings['require_email_verification'] ?? '1') == '1' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="require_email_verification">
                                Wajibkan verifikasi email setelah pendaftaran
                            </label>
                        </div>
                        <small class="text-muted d-block mt-1">User harus verifikasi email sebelum bisa login</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Contact Settings -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-envelope me-2"></i>Kontak</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <label for="admin_email" class="col-sm-3 col-form-label">Email Admin <span class="text-danger">*</span></label>
                    <div class="col-sm-9">
                        <input type="email" class="form-control" id="admin_email" name="admin_email" value="<?= htmlspecialchars($settings['admin_email'] ?? '') ?>" required>
                        <small class="text-muted">Email administrator untuk notifikasi sistem</small>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <label for="contact_phone" class="col-sm-3 col-form-label">Telepon</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="contact_phone" name="contact_phone" value="<?= htmlspecialchars($settings['contact_phone'] ?? '') ?>">
                        <small class="text-muted">Nomor telepon kontak pusat karir</small>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <label for="contact_address" class="col-sm-3 col-form-label">Alamat</label>
                    <div class="col-sm-9">
                        <textarea class="form-control" id="contact_address" name="contact_address" rows="2"><?= htmlspecialchars($settings['contact_address'] ?? '') ?></textarea>
                        <small class="text-muted">Alamat lengkap pusat karir/kampus</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-2"></i>Simpan Pengaturan
            </button>
            <a href="<?= base_url('admin/settings/reset') ?>" class="btn btn-outline-warning" onclick="return confirm('Apakah Anda yakin ingin mereset semua pengaturan ke nilai default?')">
                <i class="bi bi-arrow-counterclockwise me-2"></i>Reset ke Default
            </a>
            <a href="<?= base_url('admin/dashboard') ?>" class="btn btn-outline-secondary">
                <i class="bi bi-x-lg me-2"></i>Batal
            </a>
        </div>
    </form>
</div>

<script>
// Preview color selection
document.getElementById('primary_color').addEventListener('input', function(e) {
    document.documentElement.style.setProperty('--primary-color', e.target.value);
});

document.getElementById('secondary_color').addEventListener('input', function(e) {
    document.documentElement.style.setProperty('--secondary-color', e.target.value);
});
</script>
