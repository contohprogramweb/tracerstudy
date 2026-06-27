<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Penilaian Kompetensi Alumni' ?> - Alumni System</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/font-awesome.min.css') ?>">
    <style>
        .cpl-item { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 15px; border-left: 4px solid #4e73df; }
        .cpl-header { font-weight: bold; color: #495057; margin-bottom: 10px; }
        .rating-group { display: inline-flex; gap: 5px; }
        .rating-btn { width: 40px; height: 40px; border-radius: 50%; border: 2px solid #dee2e6; background: white; cursor: pointer; transition: all 0.2s; }
        .rating-btn:hover { border-color: #f6c23e; background: #fff3cd; }
        .rating-btn.active { background: #f6c23e; border-color: #f6c23e; color: white; }
        .alumni-info { background: #e3f2fd; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h2 class="mb-4"><i class="fa fa-edit"></i> <?= $page_title ?></h2>

                <!-- Alumni Info -->
                <div class="alumni-info">
                    <div class="row">
                        <div class="col-md-6">
                            <h5><i class="fa fa-user"></i> Data Alumni</h5>
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td width="150"><strong>Nama:</strong></td>
                                    <td><?= $alumni['name'] ?></td>
                                </tr>
                                <tr>
                                    <td><strong>NIM:</strong></td>
                                    <td><?= $alumni['nim'] ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Prodi:</strong></td>
                                    <td><?= $alumni['prodi_name'] ?? '-' ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5><i class="fa fa-briefcase"></i> Informasi Pekerjaan</h5>
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td width="150"><strong>Perusahaan:</strong></td>
                                    <td><?= $alumni['company_name'] ?? '-' ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Posisi:</strong></td>
                                    <td><?= $alumni['position'] ?? '-' ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Masa Kerja:</strong></td>
                                    <td><?= $alumni['work_duration'] ?? '-' ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <?= form_open('stakeholder/submit/' . $alumni_id, ['id' => 'surveyForm']) ?>
                <input type="hidden" name="prodi_id" value="<?= $prodi_id ?>">

                <!-- Work Period & Position -->
                <div class="card shadow mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fa fa-briefcase"></i> Informasi Penilaian</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Masa Kerja Alumni di Perusahaan <span class="text-danger">*</span></label>
                                <select name="work_period" class="form-control" required>
                                    <option value="">-- Pilih Masa Kerja --</option>
                                    <?php foreach ($work_periods as $value => $label): ?>
                                        <option value="<?= $value ?>" <?= set_value('work_period') == $value ? 'selected' : '' ?>>
                                            <?= $label ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?= form_error('work_period', '<small class="text-danger">', '</small>') ?>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Posisi/Jabatan <span class="text-danger">*</span></label>
                                <input type="text" name="work_position" class="form-control" 
                                       value="<?= set_value('work_position') ?>" required>
                                <?= form_error('work_position', '<small class="text-danger">', '</small>') ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CPL Assessment -->
                <div class="card shadow mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fa fa-star"></i> Penilaian Capaian Pembelajaran Lulusan (CPL)</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">
                            Berikan penilaian untuk setiap CPL berdasarkan kinerja alumni di perusahaan Anda.
                            <br><strong>Skala:</strong> 1 = Sangat Kurang, 2 = Kurang, 3 = Cukup, 4 = Baik, 5 = Sangat Baik
                        </p>

                        <?php if (empty($cpl_list)): ?>
                            <div class="alert alert-warning">
                                <i class="fa fa-exclamation-triangle"></i> Belum ada CPL yang terdaftar untuk prodi ini.
                            </div>
                        <?php else: ?>
                            <?php foreach ($cpl_list as $index => $cpl): ?>
                            <div class="cpl-item">
                                <div class="cpl-header">
                                    CPL <?= $index + 1 ?>: <?= $cpl['code'] ?>
                                </div>
                                <p class="mb-3"><?= $cpl['description'] ?></p>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="d-block mb-2">Rating Kompetensi:</label>
                                        <div class="rating-group">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <button type="button" class="rating-btn" data-cpl="<?= $cpl['id'] ?>" data-rating="<?= $i ?>" title="<?= $rating_options[$i] ?>">
                                                    <?= $i ?>
                                                </button>
                                            <?php endfor; ?>
                                        </div>
                                        <input type="hidden" name="cpl_ratings[<?= $cpl['id'] ?>]" class="rating-input" value="" required>
                                        <small class="rating-text text-muted ml-2"></small>
                                    </div>
                                    <div class="col-md-6">
                                        <label>Kesesuaian dengan Kebutuhan Industri:</label>
                                        <select name="cpl_kesesuaian[<?= $cpl['id'] ?>]" class="form-control form-control-sm mt-1">
                                            <option value="">-- Pilih --</option>
                                            <?php foreach ($kesesuaian_options as $value => $label): ?>
                                                <option value="<?= $value ?>"><?= $label ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Feedback & Recommendations -->
                <div class="card shadow mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fa fa-comments"></i> Masukan dan Saran</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Feedback Umum tentang Kinerja Alumni <span class="text-danger">*</span></label>
                            <textarea name="company_feedback" class="form-control" rows="4" 
                                      placeholder="Deskripsikan kelebihan dan kekurangan alumni dalam bekerja..." required><?= set_value('company_feedback') ?></textarea>
                            <?= form_error('company_feedback', '<small class="text-danger">', '</small>') ?>
                        </div>

                        <div class="form-group">
                            <label>Kompetensi yang Dibutuhkan Industri <span class="text-info">(Opsional)</span></label>
                            <textarea name="recommended_competencies" class="form-control" rows="3" 
                                      placeholder="Sebutkan kompetensi/keterampilan yang dibutuhkan industri saat ini..."><?= set_value('recommended_competencies') ?></textarea>
                            <small class="form-text text-muted">Contoh: Analisis data, Machine Learning, Digital Marketing, dll.</small>
                        </div>

                        <div class="form-group">
                            <label>Saran Perbaikan Kurikulum <span class="text-info">(Opsional)</span></label>
                            <textarea name="curriculum_suggestions" class="form-control" rows="3" 
                                      placeholder="Berikan saran untuk perbaikan kurikulum agar sesuai dengan kebutuhan industri..."><?= set_value('curriculum_suggestions') ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-lg" id="btnSubmit">
                        <i class="fa fa-save"></i> Submit Penilaian
                    </button>
                    <a href="<?= site_url('stakeholder/dashboard') ?>" class="btn btn-secondary btn-lg ml-2">
                        <i class="fa fa-times"></i> Batal
                    </a>
                </div>

                <?= form_close() ?>
            </div>
        </div>
    </div>

    <script src="<?= base_url('assets/js/jquery.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>"></script>
    <script>
        $(document).ready(function() {
            // Rating button click handler
            $('.rating-btn').click(function() {
                var cplId = $(this).data('cpl');
                var rating = $(this).data('rating');
                
                // Update active state
                $('.rating-btn[data-cpl="' + cplId + '"]').removeClass('active');
                $(this).addClass('active');
                
                // Update hidden input
                $('input[name="cpl_ratings[' + cplId + ']"]').val(rating);
                
                // Update text
                var labels = ['', 'Sangat Kurang', 'Kurang', 'Cukup', 'Baik', 'Sangat Baik'];
                $('.rating-btn[data-cpl="' + cplId + '"]').first().siblings('.rating-text').text(labels[rating]);
            });

            // Form validation
            $('#surveyForm').submit(function(e) {
                var hasError = false;
                var emptyRatings = [];
                
                $('.rating-input').each(function(index) {
                    if (!$(this).val()) {
                        hasError = true;
                        emptyRatings.push(index + 1);
                    }
                });
                
                if (hasError) {
                    e.preventDefault();
                    alert('Harap isi semua rating CPL! CPL yang belum diisi: ' + emptyRatings.join(', '));
                    return false;
                }
                
                if (!confirm('Apakah Anda yakin ingin mengirim penilaian ini?')) {
                    e.preventDefault();
                    return false;
                }
            });
        });
    </script>
</body>
</html>
