<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pertanyaan - <?= htmlspecialchars($survey->title) ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/font-awesome.min.css') ?>">
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-md-12">
                <a href="<?= site_url('survey_builder/edit/' . $survey->id) ?>" class="btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> Kembali ke Edit Survey
                </a>
                <h3 class="mt-3"><i class="fa fa-question-circle"></i> Kelola Pertanyaan</h3>
                <p class="text-muted">Survey: <strong><?= htmlspecialchars($survey->title) ?></strong></p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="d-flex justify-content-between mb-3">
                    <h5>Daftar Pertanyaan (Total: <?= count($questions) ?>)</h5>
                    <?php if ($survey->status === 'draft'): ?>
                        <a href="<?= site_url('survey_question/create/' . $survey->id) ?>" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Tambah Pertanyaan Baru
                        </a>
                    <?php endif; ?>
                </div>

                <?php if (empty($questions)): ?>
                    <div class="alert alert-info">
                        Belum ada pertanyaan. Klik "Tambah Pertanyaan Baru" untuk memulai.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="40%">Pertanyaan</th>
                                    <th width="15%">Tipe</th>
                                    <th width="10%">Wajib</th>
                                    <th width="10%">Inti</th>
                                    <th width="10%">Urutan</th>
                                    <th width="10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($questions as $idx => $q): ?>
                                    <tr class="<?= $q->is_core ? 'table-danger' : '' ?>">
                                        <td><?= $idx + 1 ?></td>
                                        <td><?= htmlspecialchars($q->question_text) ?></td>
                                        <td><span class="badge badge-secondary"><?= strtoupper($q->type) ?></span></td>
                                        <td><?= $q->is_required ? '<span class="text-success">Ya</span>' : '<span class="text-muted">Tidak</span>' ?></td>
                                        <td><?= $q->is_core ? '<span class="badge badge-danger"><i class="fa fa-lock"></i> Ya</span>' : '<span class="text-muted">Tidak</span>' ?></td>
                                        <td><?= $q->order ?></td>
                                        <td>
                                            <?php if (!$q->is_core && $survey->status === 'draft'): ?>
                                                <a href="<?= site_url('survey_question/edit/' . $survey->id . '/' . $q->id) ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-light" disabled title="Pertanyaan inti tidak dapat diubah">
                                                    <i class="fa fa-lock"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="<?= base_url('assets/js/jquery.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>"></script>
</body>
</html>
