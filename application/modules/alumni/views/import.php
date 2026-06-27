<div class="container-fluid py-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
            <div>
                <h5 class="mb-0 text-primary"><i class="fas fa-file-import me-2"></i><?php echo $page_title; ?></h5>
                <small class="text-muted"><?php echo $page_subtitle; ?></small>
            </div>
            <div>
                <a href="<?php echo site_url('public/assets/templates/template_import_alumni.xlsx'); ?>" class="btn btn-success btn-sm" download>
                    <i class="fas fa-download me-1"></i> Download Template
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
                <!-- Upload Section -->
                <div class="col-md-6">
                    <div class="card h-100 border-primary">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="fas fa-upload me-2"></i>Upload File Excel/CSV</h6>
                        </div>
                        <div class="card-body">
                            <form action="<?php echo site_url('alumni/process_import'); ?>" method="post" enctype="multipart/form-data">
                                <?php echo csrf_field(); ?>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Pilih File</label>
                                    <input type="file" name="import_file" class="form-control" accept=".xlsx,.xls,.csv" required>
                                    <small class="text-muted">Format: .xlsx, .xls, atau .csv (Max 10MB)</small>
                                </div>
                                
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-info-circle me-2"></i>Panduan Import:</h6>
                                    <ul class="mb-0 small">
                                        <li><strong>BR-ALM-006:</strong> Kolom wajib: NIM, Nama, Prodi, Tahun Lulus</li>
                                        <li><strong>BR-ALM-002:</strong> NIM duplicate akan dilewati (skip)</li>
                                        <li>Baris pertama dianggap sebagai header</li>
                                        <li>Prodi harus sesuai dengan nama di sistem</li>
                                    </ul>
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-cloud-upload-alt me-2"></i>Upload dan Import
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Preview/Info Section -->
                <div class="col-md-6">
                    <div class="card h-100 border-info">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0"><i class="fas fa-table me-2"></i>Format Template</h6>
                        </div>
                        <div class="card-body">
                            <h6 class="fw-bold">Struktur Kolom Excel:</h6>
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th width="10%">No</th>
                                        <th>Kolom</th>
                                        <th>Wajib</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td><code>NIM</code></td>
                                        <td><span class="badge bg-danger">Ya</span></td>
                                        <td>Nomor Induk Mahasiswa (unik)</td>
                                    </tr>
                                    <tr>
                                        <td>2</td>
                                        <td><code>Nama</code></td>
                                        <td><span class="badge bg-danger">Ya</span></td>
                                        <td>Nama lengkap alumni</td>
                                    </tr>
                                    <tr>
                                        <td>3</td>
                                        <td><code>Email</code></td>
                                        <td><span class="badge bg-warning">Tidak</span></td>
                                        <td>Email aktif alumni</td>
                                    </tr>
                                    <tr>
                                        <td>4</td>
                                        <td><code>No HP</code></td>
                                        <td><span class="badge bg-warning">Tidak</span></td>
                                        <td>Nomor telepon/WhatsApp</td>
                                    </tr>
                                    <tr>
                                        <td>5</td>
                                        <td><code>Prodi</code></td>
                                        <td><span class="badge bg-danger">Ya</span></td>
                                        <td>Nama program studi</td>
                                    </tr>
                                    <tr>
                                        <td>6</td>
                                        <td><code>Kohort</code></td>
                                        <td><span class="badge bg-warning">Tidak</span></td>
                                        <td>Nama/tahun kohort</td>
                                    </tr>
                                    <tr>
                                        <td>7</td>
                                        <td><code>Tahun Lulus</code></td>
                                        <td><span class="badge bg-danger">Ya</span></td>
                                        <td>Tahun kelulusan (4 digit)</td>
                                    </tr>
                                    <tr>
                                        <td>8</td>
                                        <td><code>Tgl Yudisium</code></td>
                                        <td><span class="badge bg-warning">Tidak</span></td>
                                        <td>Format: YYYY-MM-DD</td>
                                    </tr>
                                </tbody>
                            </table>
                            
                            <div class="alert alert-warning mt-3">
                                <h6><i class="fas fa-exclamation-triangle me-2"></i>Catatan Penting:</h6>
                                <ul class="mb-0 small">
                                    <li>Data yang sudah ada dengan NIM sama akan di-skip</li>
                                    <li>Alumni baru akan memiliki status default "Belum Bekerja"</li>
                                    <li>Email verified default: No</li>
                                    <li>Status aktif default: Yes</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Import Logs -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card border-secondary">
                        <div class="card-header bg-secondary text-white">
                            <h6 class="mb-0"><i class="fas fa-history me-2"></i>Riwayat Import</h6>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small">Log import terakhir dapat dilihat di menu Audit Trail > Activity Logs dengan filter action "import"</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
