<div class="container-fluid">
    <!-- Page Title -->
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="text-dark fw-bold mb-1">
                <i class="fas fa-chart-bar me-2"></i><?= esc($page_title) ?>
            </h2>
            <p class="text-muted mb-0"><?= esc($page_subtitle) ?></p>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form id="filterForm" method="get" action="<?= site_url('prodi/laporan') ?>">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Tahun</label>
                        <select class="form-select form-select-sm" name="tahun" id="filterTahun">
                            <option value="">Semua Tahun</option>
                            <?php 
                            $current_year = date('Y');
                            for ($i = $current_year; $i >= $current_year - 5; $i--): 
                            ?>
                            <option value="<?= $i ?>" <?= set_select('tahun', $i) ?>><?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Jenis Laporan</label>
                        <select class="form-select form-select-sm" name="jenis" id="filterJenis">
                            <option value="">Semua Jenis</option>
                            <option value="tracer_study">Tracer Study</option>
                            <option value="survey">Survey</option>
                            <option value="alumni_status">Status Alumni</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small text-muted">Program Studi</label>
                        <input type="text" class="form-control form-control-sm" value="<?= esc($prodi_info['nama_prodi'] ?? '-') ?>" readonly>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-filter me-1"></i>Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Reports Grid -->
    <div class="row g-4">
        <!-- Tracer Study Report -->
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="bg-primary bg-gradient rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 70px; height: 70px;">
                        <i class="fas fa-file-alt text-white fa-2x"></i>
                    </div>
                    <h5 class="card-title text-dark mb-2">Laporan Tracer Study</h5>
                    <p class="card-text text-muted small mb-3">
                        Laporan lengkap hasil tracer study alumni program studi
                    </p>
                    <a href="<?= site_url('prodi/laporan/generate/tracer') ?>" class="btn btn-primary btn-sm" target="_blank">
                        <i class="fas fa-download me-2"></i>Generate PDF
                    </a>
                </div>
            </div>
        </div>

        <!-- Alumni Status Report -->
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="bg-success bg-gradient rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 70px; height: 70px;">
                        <i class="fas fa-users text-white fa-2x"></i>
                    </div>
                    <h5 class="card-title text-dark mb-2">Status Alumni</h5>
                    <p class="card-text text-muted small mb-3">
                        Statistik status kerja dan kelanjutan studi alumni
                    </p>
                    <a href="<?= site_url('prodi/laporan/generate/status') ?>" class="btn btn-success btn-sm" target="_blank">
                        <i class="fas fa-download me-2"></i>Generate PDF
                    </a>
                </div>
            </div>
        </div>

        <!-- Survey Results Report -->
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="bg-info bg-gradient rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 70px; height: 70px;">
                        <i class="fas fa-clipboard-check text-white fa-2x"></i>
                    </div>
                    <h5 class="card-title text-dark mb-2">Hasil Survei</h5>
                    <p class="card-text text-muted small mb-3">
                        Ringkasan hasil survei untuk program studi
                    </p>
                    <a href="<?= site_url('prodi/laporan/generate/survey') ?>" class="btn btn-info btn-sm text-white" target="_blank">
                        <i class="fas fa-download me-2"></i>Generate PDF
                    </a>
                </div>
            </div>
        </div>

        <!-- Employment Report -->
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="bg-warning bg-gradient rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 70px; height: 70px;">
                        <i class="fas fa-briefcase text-white fa-2x"></i>
                    </div>
                    <h5 class="card-title text-dark mb-2">Keterlibatan Kerja</h5>
                    <p class="card-text text-muted small mb-3">
                        Analisis keterlibatan alumni di dunia kerja
                    </p>
                    <a href="<?= site_url('prodi/laporan/generate/employment') ?>" class="btn btn-warning btn-sm" target="_blank">
                        <i class="fas fa-download me-2"></i>Generate PDF
                    </a>
                </div>
            </div>
        </div>

        <!-- Export Data Alumni -->
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="bg-danger bg-gradient rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 70px; height: 70px;">
                        <i class="fas fa-table text-white fa-2x"></i>
                    </div>
                    <h5 class="card-title text-dark mb-2">Data Alumni (Excel)</h5>
                    <p class="card-text text-muted small mb-3">
                        Ekspor data lengkap alumni dalam format Excel
                    </p>
                    <a href="<?= site_url('prodi/laporan/export/alumni') ?>" class="btn btn-danger btn-sm">
                        <i class="fas fa-file-excel me-2"></i>Export Excel
                    </a>
                </div>
            </div>
        </div>

        <!-- Custom Report -->
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="bg-secondary bg-gradient rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 70px; height: 70px;">
                        <i class="fas fa-cog text-white fa-2x"></i>
                    </div>
                    <h5 class="card-title text-dark mb-2">Laporan Kustom</h5>
                    <p class="card-text text-muted small mb-3">
                        Buat laporan sesuai kebutuhan spesifik Anda
                    </p>
                    <a href="<?= site_url('prodi/laporan/custom') ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-edit me-2"></i>Buat Laporan
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Reports Table -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 text-dark fw-bold">
                        <i class="fas fa-history me-2"></i>Riwayat Laporan
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th width="15%">Tanggal</th>
                                    <th width="25%">Jenis Laporan</th>
                                    <th width="20%">Tahun</th>
                                    <th width="20%">Status</th>
                                    <th width="20%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2"></i>
                                        <p>Belum ada riwayat laporan yang dihasilkan</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        
        const tahun = $('#filterTahun').val();
        const jenis = $('#filterJenis').val();
        
        // Redirect with filters
        let url = '<?= site_url('prodi/laporan') ?>?';
        if (tahun) url += 'tahun=' + tahun + '&';
        if (jenis) url += 'jenis=' + jenis;
        
        window.location.href = url;
    });
});
</script>

<style>
.card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}
.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}
</style>
