<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Survey Baru</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/font-awesome.min.css') ?>">
    <style>
        .core-question-item { padding: 10px; border-left: 3px solid #dc3545; background: #f8f9fa; margin-bottom: 8px; }
        .info-box { background: #e7f3ff; border-left: 4px solid #007bff; padding: 15px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="row mb-4">
            <div class="col-md-12">
                <a href="<?= site_url('survey_builder/index') ?>" class="btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> Kembali
                </a>
                <h2 class="mt-3"><i class="fa fa-plus-circle"></i> Buat Survey Baru</h2>
            </div>
        </div>

        <?php if (validation_errors()): ?>
            <div class="alert alert-danger">
                <?= validation_errors() ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Informasi Survey</h5>
                    </div>
                    <div class="card-body">
                        <?= form_open('survey_builder/store') ?>
                            <div class="form-group">
                                <label for="title">Judul Survey <span class="text-danger">*</span></label>
                                <input type="text" name="title" id="title" class="form-control" 
                                       value="<?= set_value('title') ?>" required maxlength="255"
                                       placeholder="Contoh: Survey Kepuasan Alumni 2024">
                                <small class="form-text text-muted">Masukkan judul yang jelas dan deskriptif</small>
                            </div>

                            <div class="form-group">
                                <label for="description">Deskripsi</label>
                                <textarea name="description" id="description" class="form-control" rows="4"
                                          placeholder="Deskripsikan tujuan dan scope survey ini..."><?= set_value('description') ?></textarea>
                                <small class="form-text text-muted">Deskripsi akan ditampilkan kepada responden sebelum memulai survey</small>
                            </div>

                            <hr>

                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" name="add_core" id="add_core" class="custom-control-input" value="1" checked>
                                    <label class="custom-control-label" for="add_core">
                                        <strong>Tambahkan 20 Pertanyaan Inti Belmawa</strong>
                                    </label>
                                </div>
                                <small class="form-text text-muted">
                                    Pertanyaan inti adalah standar dari Belmawa yang wajib ada untuk publish survey.
                                    Minimal 20 pertanyaan inti diperlukan sebelum survey dapat dipublikasikan.
                                </small>
                            </div>

                            <div class="form-group mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-save"></i> Buat Survey
                                </button>
                                <a href="<?= site_url('survey_builder/index') ?>" class="btn btn-secondary">Batal</a>
                            </div>
                        <?= form_close() ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-header">
                        <h6><i class="fa fa-info-circle"></i> Tentang Pertanyaan Inti</h6>
                    </div>
                    <div class="card-body">
                        <div class="info-box">
                            <p class="mb-2"><strong>BR-SUR-002:</strong> Minimal 20 pertanyaan inti diperlukan untuk publish survey.</p>
                            <p class="mb-0"><strong>BR-SUR-001:</strong> Pertanyaan inti tidak dapat dihapus atau diubah setelah dibuat.</p>
                        </div>

                        <h6>Daftar 20 Pertanyaan Inti:</h6>
                        <small class="text-muted">Preview pertanyaan yang akan ditambahkan:</small>
                        
                        <div style="max-height: 400px; overflow-y: auto;" class="mt-2">
                            <?php foreach ($core_questions as $index => $q): ?>
                                <div class="core-question-item">
                                    <small class="text-muted">#<?= $index + 1 ?> [<?= ucfirst($q['type']) ?>]</small>
                                    <p class="mb-0 small"><?= htmlspecialchars($q['question_text']) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= base_url('assets/js/jquery.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>"></script>
</body>
</html>
