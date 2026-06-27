<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Survey - <?= htmlspecialchars($survey->title) ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/font-awesome.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/jquery-ui.min.css') ?>">
    <style>
        .question-card { border: 1px solid #ddd; border-radius: 8px; padding: 15px; margin-bottom: 10px; background: #fff; cursor: move; }
        .question-card:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .question-card.core { border-left: 4px solid #dc3545; }
        .question-handle { color: #6c757d; margin-right: 10px; }
        .tab-content { padding: 20px 0; }
        .nav-tabs .nav-link.active { font-weight: bold; }
        .question-type-badge { background: #e9ecef; padding: 3px 8px; border-radius: 4px; font-size: 11px; }
        .core-badge { background: #dc3545; color: white; padding: 3px 8px; border-radius: 4px; font-size: 11px; }
        .ui-sortable-helper { box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
        .ui-sortable-placeholder { visibility: visible !important; background: #f0f0f0; border: 2px dashed #ccc; }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <a href="<?= site_url('survey_builder/index') ?>" class="btn btn-secondary btn-sm">
                            <i class="fa fa-arrow-left"></i> Kembali
                        </a>
                        <h3 class="mt-2 d-inline"><i class="fa fa-edit"></i> Edit Survey</h3>
                        <span class="badge badge-warning ml-2">DRAFT</span>
                    </div>
                    <div>
                        <button onclick="publishSurvey()" class="btn btn-success">
                            <i class="fa fa-check"></i> Publish Survey
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success"><?= $this->session->flashdata('success') ?></div>
        <?php endif; ?>
        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger"><?= $this->session->flashdata('error') ?></div>
        <?php endif; ?>

        <ul class="nav nav-tabs" id="surveyTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="info-tab" data-toggle="tab" href="#info" role="tab">
                    <i class="fa fa-info-circle"></i> Informasi Survey
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="questions-tab" data-toggle="tab" href="#questions" role="tab">
                    <i class="fa fa-question-circle"></i> Pertanyaan 
                    <span class="badge badge-primary"><?= count($questions) ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="logic-tab" data-toggle="tab" href="#logic" role="tab">
                    <i class="fa fa-random"></i> Logic Jump
                </a>
            </li>
        </ul>

        <div class="tab-content" id="surveyTabContent">
            <!-- Tab Informasi -->
            <div class="tab-pane fade show active" id="info" role="tabpanel">
                <?= form_open('survey_builder/update/' . $survey->id) ?>
                    <div class="form-group">
                        <label for="title">Judul Survey</label>
                        <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($survey->title) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Deskripsi</label>
                        <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($survey->description ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Simpan Perubahan</button>
                <?= form_close() ?>
            </div>

            <!-- Tab Pertanyaan -->
            <div class="tab-pane fade" id="questions" role="tabpanel">
                <div class="d-flex justify-content-between mb-3">
                    <h5>Daftar Pertanyaan</h5>
                    <a href="<?= site_url('survey_question/create/' . $survey->id) ?>" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus"></i> Tambah Pertanyaan
                    </a>
                </div>

                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i> <strong>Tips:</strong> Drag & drop pertanyaan untuk mengubah urutan. 
                    Pertanyaan inti (merah) tidak dapat dihapus atau diubah.
                </div>

                <div id="questions-list">
                    <?php if (empty($questions)): ?>
                        <p class="text-muted text-center py-4">Belum ada pertanyaan. Tambahkan pertanyaan pertama Anda!</p>
                    <?php else: ?>
                        <?php foreach ($questions as $q): ?>
                            <div class="question-card <?= $q->is_core ? 'core' : '' ?>" data-id="<?= $q->id ?>">
                                <div class="d-flex align-items-start">
                                    <span class="question-handle"><i class="fa fa-arrows-alt"></i></span>
                                    <div class="flex-grow-1">
                                        <div class="mb-2">
                                            <span class="question-type-badge"><?= strtoupper($q->type) ?></span>
                                            <?php if ($q->is_core): ?>
                                                <span class="core-badge"><i class="fa fa-lock"></i> CORE</span>
                                            <?php endif; ?>
                                            <span class="badge badge-secondary">Order: <?= $q->order ?></span>
                                        </div>
                                        <p class="mb-2"><strong><?= htmlspecialchars($q->question_text) ?></strong></p>
                                        <?php if ($q->options): ?>
                                            <small class="text-muted">Opsi: <?= str_replace('|', ', ', htmlspecialchars($q->options)) ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="ml-3">
                                        <?php if (!$q->is_core): ?>
                                            <a href="<?= site_url('survey_question/edit/' . $survey->id . '/' . $q->id) ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <button onclick="deleteQuestion(<?= $q->id ?>)" class="btn btn-sm btn-outline-danger">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-light" disabled title="Pertanyaan inti tidak dapat diubah">
                                                <i class="fa fa-lock"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tab Logic -->
            <div class="tab-pane fade" id="logic" role="tabpanel">
                <div class="d-flex justify-content-between mb-3">
                    <h5>Logic Jump / Conditional Branching</h5>
                    <a href="<?= site_url('survey_logic/create/' . $survey->id) ?>" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus"></i> Tambah Logic
                    </a>
                </div>
                <div class="alert alert-warning">
                    <i class="fa fa-exclamation-triangle"></i> <strong>BR-SUR-003:</strong> Logic jump tidak boleh membuat circular reference.
                    Sistem akan otomatis mendeteksi dan menolak logic yang menyebabkan siklus.
                </div>
                <div id="logic-list">
                    <p class="text-muted text-center py-4">
                        Logic jump akan ditampilkan di sini setelah ditambahkan.
                        <br><small>Klik "Tambah Logic" untuk membuat aturan conditional branching.</small>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= base_url('assets/js/jquery.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/jquery-ui.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>"></script>
    <script>
        $(document).ready(function() {
            // Enable drag-drop reordering
            $('#questions-list').sortable({
                handle: '.question-handle',
                placeholder: 'ui-sortable-placeholder',
                update: function(event, ui) {
                    var orders = {};
                    $('.question-card').each(function(index) {
                        var qId = $(this).data('id');
                        orders[qId] = index + 1;
                        $(this).find('.badge-secondary').text('Order: ' + (index + 1));
                    });

                    $.ajax({
                        url: '<?= site_url('survey_question/reorder') ?>',
                        type: 'POST',
                        data: { survey_id: <?= $survey->id ?>, orders: orders },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                // Show temporary success message
                                $('#questions-list').prepend(
                                    '<div class="alert alert-success alert-dismissible fade show">' +
                                    response.message + 
                                    '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
                                    '</div>'
                                );
                            }
                        }
                    });
                }
            });

            // Load logics on logic tab click
            $('#logic-tab').on('shown.bs.tab', function() {
                loadLogics();
            });
        });

        function deleteQuestion(id) {
            if (!confirm('Apakah Anda yakin ingin menghapus pertanyaan ini?')) return;

            $.ajax({
                url: '<?= site_url('survey_question/delete/' . $survey->id) ?>/' + id,
                type: 'DELETE',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan';
                    alert('Error: ' + msg);
                }
            });
        }

        function publishSurvey() {
            if (!confirm('Yakin ingin mempublikasikan survey ini? Pastikan sudah memiliki minimal 20 pertanyaan inti.')) {
                return;
            }

            $.ajax({
                url: '<?= site_url('survey_builder/publish/' . $survey->id) ?>',
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        window.location = '<?= site_url('survey_builder/preview/' . $survey->id) ?>';
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan';
                    alert('Error: ' + msg);
                }
            });
        }

        function loadLogics() {
            $.ajax({
                url: '<?= site_url('survey_logic/get_logics/' . $survey->id) ?>',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.logics.length === 0) {
                        $('#logic-list').html('<p class="text-muted text-center py-4">Belum ada logic jump.</p>');
                        return;
                    }

                    let html = '<div class="table-responsive"><table class="table table-bordered">';
                    html += '<thead class="thead-light"><tr><th>No</th><th>Pertanyaan Sumber</th><th>Kondisi</th><th>Lompat ke</th><th>Aksi</th></tr></thead><tbody>';
                    
                    response.logics.forEach((logic, idx) => {
                        html += `<tr>
                            <td>${idx + 1}</td>
                            <td>${logic.question_text}</td>
                            <td><code>${logic.condition_value}</code></td>
                            <td>${logic.target_text} (Order: ${logic.target_order})</td>
                            <td>
                                <button onclick="deleteLogic(${logic.id})" class="btn btn-sm btn-danger">
                                    <i class="fa fa-trash"></i> Hapus
                                </button>
                            </td>
                        </tr>`;
                    });
                    
                    html += '</tbody></table></div>';
                    $('#logic-list').html(html);
                }
            });
        }

        function deleteLogic(id) {
            if (!confirm('Hapus logic jump ini?')) return;

            $.ajax({
                url: '<?= site_url('survey_logic/delete/' . $survey->id) ?>/' + id,
                type: 'DELETE',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        loadLogics();
                    } else {
                        alert('Error: ' + response.message);
                    }
                }
            });
        }
    </script>
</body>
</html>
