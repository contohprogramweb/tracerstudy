<div class="container-fluid py-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="mb-0 text-primary"><i class="fas fa-user-plus me-2"></i><?php echo $page_title; ?></h5>
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

            <form action="<?php echo site_url('alumni/store'); ?>" method="post" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                
                <div class="row g-4">
                    <!-- Personal Information -->
                    <div class="col-md-6">
                        <h6 class="border-bottom pb-2 mb-3 text-primary"><i class="fas fa-user me-2"></i>Informasi Pribadi</h6>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">NIM <span class="text-danger">*</span></label>
                            <input type="text" name="nim" class="form-control" value="<?php echo set_value('nim'); ?>" required>
                            <small class="text-muted">BR-ALM-002: NIM tidak dapat diubah setelah dibuat</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="nama" class="form-control" value="<?php echo set_value('nama'); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Email</label>
                            <input type="email" name="email" class="form-control" value="<?php echo set_value('email'); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">No. HP</label>
                            <input type="text" name="no_hp" class="form-control" value="<?php echo set_value('no_hp'); ?>">
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
                                <option value="<?php echo $prodi['id']; ?>"><?php echo htmlspecialchars($prodi['nama']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Kohort</label>
                            <select name="kohort_id" class="form-select">
                                <option value="">Pilih Kohort</option>
                                <?php foreach ($kohorts as $kohort): ?>
                                <option value="<?php echo $kohort['id']; ?>"><?php echo htmlspecialchars($kohort['nama']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tahun Lulus <span class="text-danger">*</span></label>
                            <input type="number" name="tahun_lulus" class="form-control" value="<?php echo set_value('tahun_lulus', date('Y')); ?>" min="2000" max="<?php echo date('Y')+1); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tanggal Yudisium</label>
                            <input type="date" name="tanggal_yudisium" class="form-control" value="<?php echo set_value('tanggal_yudisium'); ?>">
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
                                    <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">BR-ALM-003: Belum bekerja tidak boleh isi gaji/perusahaan</small>
                            </div>
                            
                            <div class="col-md-4 employment-field">
                                <label class="form-label">Perusahaan</label>
                                <input type="text" name="perusahaan" id="perusahaan" class="form-control" value="<?php echo set_value('perusahaan'); ?>">
                            </div>
                            
                            <div class="col-md-4 employment-field">
                                <label class="form-label">Jabatan</label>
                                <input type="text" name="jabatan" id="jabatan" class="form-control" value="<?php echo set_value('jabatan'); ?>">
                            </div>
                            
                            <div class="col-md-4 employment-field">
                                <label class="form-label">Gaji (Rp)</label>
                                <input type="text" name="gaji" id="gaji" class="form-control" value="<?php echo set_value('gaji'); ?>" onkeyup="formatCurrency(this)">
                            </div>
                            
                            <div class="col-md-4 employment-field">
                                <label class="form-label">Tanggal Mulai Kerja</label>
                                <input type="date" name="tanggal_mulai_kerja" id="tanggal_mulai_kerja" class="form-control" value="<?php echo set_value('tanggal_mulai_kerja'); ?>">
                                <small class="text-muted">BR-ALM-004: Tidak boleh sebelum yudisium</small>
                            </div>
                            
                            <div class="col-md-4 employment-field">
                                <label class="form-label">Lokasi Kerja</label>
                                <input type="text" name="lokasi_kerja" id="lokasi_kerja" class="form-control" value="<?php echo set_value('lokasi_kerja'); ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top d-flex justify-content-between">
                    <a href="<?php echo site_url('alumni'); ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Simpan Alumni
                    </button>
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
            field.querySelector('input').value = '';
            field.querySelector('input').disabled = true;
        });
    } else {
        fields.forEach(function(field) {
            field.style.display = 'block';
            field.querySelector('input').disabled = false;
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
