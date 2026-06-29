<div class="container-fluid">
    <!-- Page Title -->
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="text-dark fw-bold mb-1">
                <i class="fas fa-cog me-2"></i><?= esc($page_title) ?>
            </h2>
            <p class="text-muted mb-0"><?= esc($page_subtitle) ?></p>
        </div>
    </div>

    <!-- Custom Report Form -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form id="customReportForm" method="post" action="<?= site_url('prodi/laporan/generate_custom') ?>">
                <div class="row g-4">
                    <!-- Left Column -->
                    <div class="col-md-6">
                        <h5 class="text-dark mb-3">
                            <i class="fas fa-filter me-2"></i>Pilih Kriteria
                        </h5>
                        
                        <div class="mb-3">
                            <label class="form-label">Jenis Laporan <span class="text-danger">*</span></label>
                            <select class="form-select" name="jenis_laporan" id="jenisLaporan" required>
                                <option value="">Pilih Jenis Laporan</option>
                                <option value="tracer_study">Tracer Study</option>
                                <option value="status_alumni">Status Alumni</option>
                                <option value="survey_response">Respon Survei</option>
                                <option value="employment_rate">Tingkat Employment</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Kohort</label>
                            <select class="form-select" name="kohort_id" id="kohortId">
                                <option value="">Semua Kohort</option>
                                <?php foreach ($kohorts as $kohort): ?>
                                <option value="<?= $kohort['id'] ?>"><?= esc($kohort['nama']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tahun Lulus</label>
                            <select class="form-select" name="tahun_lulus" id="tahunLulus">
                                <option value="">Semua Tahun</option>
                                <?php 
                                $current_year = date('Y');
                                for ($i = $current_year; $i >= $current_year - 10; $i--): 
                                ?>
                                <option value="<?= $i ?>"><?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status Kerja</label>
                            <select class="form-select" name="status_kerja" id="statusKerja">
                                <option value="">Semua Status</option>
                                <option value="bekerja">Bekerja</option>
                                <option value="belum_bekerja">Belum Bekerja</option>
                                <option value="wirausaha">Wirausaha</option>
                                <option value="melanjutkan_studi">Melanjutkan Studi</option>
                            </select>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="col-md-6">
                        <h5 class="text-dark mb-3">
                            <i class="fas fa-file-export me-2"></i>Opsi Export
                        </h5>
                        
                        <div class="mb-3">
                            <label class="form-label">Format Output <span class="text-danger">*</span></label>
                            <div class="d-grid gap-2">
                                <div class="form-check p-3 border rounded">
                                    <input class="form-check-input" type="radio" name="format" id="formatPdf" value="pdf" checked>
                                    <label class="form-check-label w-100" for="formatPdf">
                                        <i class="fas fa-file-pdf text-danger me-2"></i>
                                        <strong>PDF Document</strong>
                                        <small class="text-muted d-block">Cocok untuk laporan formal dan presentasi</small>
                                    </label>
                                </div>
                                <div class="form-check p-3 border rounded">
                                    <input class="form-check-input" type="radio" name="format" id="formatExcel" value="excel">
                                    <label class="form-check-label w-100" for="formatExcel">
                                        <i class="fas fa-file-excel text-success me-2"></i>
                                        <strong>Excel Spreadsheet</strong>
                                        <small class="text-muted d-block">Cocok untuk analisis data lebih lanjut</small>
                                    </label>
                                </div>
                                <div class="form-check p-3 border rounded">
                                    <input class="form-check-input" type="radio" name="format" id="formatCsv" value="csv">
                                    <label class="form-check-label w-100" for="formatCsv">
                                        <i class="fas fa-file-csv text-primary me-2"></i>
                                        <strong>CSV File</strong>
                                        <small class="text-muted d-block">Format universal untuk import ke aplikasi lain</small>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Include Charts</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="include_charts" id="includeCharts" checked>
                                <label class="form-check-label" for="includeCharts">Sertakan grafik visualisasi data</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <hr class="my-4">
                <div class="d-flex justify-content-between">
                    <a href="<?= site_url('prodi/laporan') ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Kembali
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-file-export me-2"></i>Generate Laporan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Info Card -->
    <div class="alert alert-info mt-4 mb-0">
        <div class="d-flex">
            <i class="fas fa-info-circle fa-2x me-3"></i>
            <div>
                <h6 class="alert-heading">Tips Membuat Laporan Kustom</h6>
                <ul class="mb-0 small">
                    <li>Pilih kriteria yang spesifik untuk mendapatkan hasil yang lebih fokus</li>
                    <li>Gunakan format PDF untuk laporan resmi dan Excel untuk analisis data</li>
                    <li>Sertakan charts untuk visualisasi yang lebih menarik</li>
                    <li>Laporan akan di-generate secara real-time berdasarkan data terkini</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#customReportForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        const format = $('input[name="format"]:checked').val();
        
        // Build URL based on format
        let downloadUrl = '<?= site_url('prodi/laporan/generate_custom') ?>?' + formData;
        
        // Open in new tab for download
        window.open(downloadUrl, '_blank');
    });
});
</script>
