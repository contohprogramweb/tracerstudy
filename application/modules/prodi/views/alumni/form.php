<div class="container-fluid">
    <!-- Page Title -->
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="text-dark fw-bold mb-1">
                <i class="fas fa-<?= isset($alumni) ? 'edit' : 'plus' ?> me-2"></i><?= esc($page_title) ?>
            </h2>
            <p class="text-muted mb-0"><?= esc($page_subtitle) ?></p>
        </div>
    </div>

    <!-- Form Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if (validation_errors()): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <ul class="mb-0">
                    <?php echo validation_errors('<li>', '</li>'); ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?= $this->session->flashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <form method="post" action="">
                <div class="row g-4">
                    <!-- Left Column -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="nim" class="form-label">NIM <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= form_error('nim') ? 'is-invalid' : '' ?>" 
                                   id="nim" name="nim" 
                                   value="<?= set_value('nim', $alumni->nim ?? '') ?>" 
                                   placeholder="Masukkan NIM"
                                   <?= isset($alumni) ? '' : 'required' ?>>
                            <?php if (form_error('nim')): ?>
                            <div class="invalid-feedback"><?= form_error('nim') ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= form_error('nama') ? 'is-invalid' : '' ?>" 
                                   id="nama" name="nama" 
                                   value="<?= set_value('nama', $alumni->nama ?? '') ?>" 
                                   placeholder="Masukkan nama lengkap"
                                   required>
                            <?php if (form_error('nama')): ?>
                            <div class="invalid-feedback"><?= form_error('nama') ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="prodi_id" class="form-label">Program Studi <span class="text-danger">*</span></label>
                            <select class="form-select <?= form_error('prodi_id') ? 'is-invalid' : '' ?>" 
                                    id="prodi_id" name="prodi_id" required>
                                <option value="">Pilih Program Studi</option>
                                <?php foreach ($prodis as $prodi): ?>
                                <option value="<?= $prodi['id'] ?>" 
                                        <?= set_select('prodi_id', $prodi['id'], (isset($alumni) && $alumni->prodi_id == $prodi['id'])) ?>>
                                    <?= esc($prodi['nama']) ?> (<?= esc($prodi['kode']) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (form_error('prodi_id')): ?>
                            <div class="invalid-feedback"><?= form_error('prodi_id') ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="tahun_lulus" class="form-label">Tahun Lulus <span class="text-danger">*</span></label>
                            <input type="number" class="form-control <?= form_error('tahun_lulus') ? 'is-invalid' : '' ?>" 
                                   id="tahun_lulus" name="tahun_lulus" 
                                   value="<?= set_value('tahun_lulus', $alumni->tahun_lulus ?? '') ?>" 
                                   placeholder="Contoh: 2023"
                                   min="2000" max="<?= date('Y') + 1 ?>"
                                   required>
                            <?php if (form_error('tahun_lulus')): ?>
                            <div class="invalid-feedback"><?= form_error('tahun_lulus') ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="status_kerja" class="form-label">Status Kerja</label>
                            <select class="form-select" id="status_kerja" name="status_kerja">
                                <option value="belum_bekerja" <?= set_select('status_kerja', 'belum_bekerja', (isset($alumni) && $alumni->status_kerja == 'belum_bekerja')) ?>>Belum Bekerja</option>
                                <option value="bekerja" <?= set_select('status_kerja', 'bekerja', (isset($alumni) && $alumni->status_kerja == 'bekerja')) ?>>Bekerja</option>
                                <option value="wirausaha" <?= set_select('status_kerja', 'wirausaha', (isset($alumni) && $alumni->status_kerja == 'wirausaha')) ?>>Wirausaha</option>
                                <option value="melanjutkan_studi" <?= set_select('status_kerja', 'melanjutkan_studi', (isset($alumni) && $alumni->status_kerja == 'melanjutkan_studi')) ?>>Melanjutkan Studi</option>
                            </select>
                        </div>

                        <!-- Additional info card -->
                        <div class="card bg-light border-0 mt-4">
                            <div class="card-body">
                                <h6 class="card-title text-muted mb-3">
                                    <i class="fas fa-info-circle me-2"></i>Informasi
                                </h6>
                                <ul class="small text-muted mb-0">
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Pastikan NIM yang dimasukkan unik dan valid
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Tahun lulus harus antara 2000 hingga <?= date('Y') + 1 ?>
                                    </li>
                                    <li>
                                        <i class="fas fa-check text-success me-2"></i>
                                        Status kerja dapat diubah nanti sesuai perkembangan alumni
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <hr class="my-4">
                <div class="d-flex justify-content-between">
                    <a href="<?= site_url('prodi/alumni') ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Kembali
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i><?= isset($alumni) ? 'Update' : 'Simpan' ?> Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Auto-format NIM input (optional)
$('#nim').on('input', function() {
    this.value = this.value.toUpperCase();
});

// Validate year on the fly
$('#tahun_lulus').on('change', function() {
    const year = parseInt(this.value);
    const minYear = 2000;
    const maxYear = new Date().getFullYear() + 1;
    
    if (year < minYear || year > maxYear) {
        $(this).addClass('is-invalid');
        $(this).next('.invalid-feedback').remove();
        $(this).after('<div class="invalid-feedback">Tahun lulus harus antara ' + minYear + ' dan ' + maxYear + '</div>');
    } else {
        $(this).removeClass('is-invalid');
        $(this).next('.invalid-feedback').remove();
    }
});
</script>
