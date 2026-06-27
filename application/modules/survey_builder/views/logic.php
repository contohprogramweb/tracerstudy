<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logic Jump - <?= htmlspecialchars($survey->title) ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/font-awesome.min.css') ?>">
    <style>
        .logic-card { border: 1px solid #ddd; border-radius: 8px; padding: 15px; margin-bottom: 15px; background: #fff; }
        .logic-arrow { font-size: 24px; color: #007bff; margin: 0 10px; }
        .condition-badge { background: #ffc107; color: #000; padding: 3px 8px; border-radius: 4px; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-md-12">
                <a href="<?= site_url('survey_builder/edit/' . $survey->id) ?>" class="btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> Kembali ke Edit Survey
                </a>
                <h3 class="mt-3"><i class="fa fa-random"></i> Logic Jump Builder</h3>
                <p class="text-muted">Survey: <strong><?= htmlspecialchars($survey->title) ?></strong></p>
            </div>
        </div>

        <div class="alert alert-warning">
            <i class="fa fa-exclamation-triangle"></i> <strong>BR-SUR-003:</strong> Logic jump tidak boleh membuat circular reference.
            Sistem akan otomatis mendeteksi dan menolak logic yang menyebabkan siklus menggunakan algoritma DFS.
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fa fa-plus"></i> Tambah Logic Baru</h5>
                    </div>
                    <div class="card-body">
                        <?= form_open('survey_logic/store/' . $survey->id, ['id' => 'logicForm']) ?>
                            <div class="form-group">
                                <label for="question_id">Pertanyaan Sumber *</label>
                                <select name="question_id" id="question_id" class="form-control" required>
                                    <option value="">-- Pilih Pertanyaan --</option>
                                    <?php foreach ($questions as $q): ?>
                                        <?php if (in_array($q->type, ['multiple_choice', 'dropdown', 'checkbox', 'rating'])): ?>
                                            <option value="<?= $q->id ?>" data-type="<?= $q->type ?>" <?= $q->options ? 'data-options="' . htmlspecialchars($q->options) . '"' : '' ?>>
                                                #<?= $q->order ?>: <?= substr(htmlspecialchars($q->question_text), 0, 50) ?>... [<?= $q->type ?>]
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                                <small class="form-text text-muted">Hanya pertanyaan pilihan/rating yang bisa memiliki logic</small>
                            </div>

                            <div class="form-group">
                                <label for="condition_value">Nilai Kondisi *</label>
                                <input type="text" name="condition_value" id="condition_value" class="form-control" required
                                       placeholder="Contoh: Ya, atau 5 untuk rating">
                                <small class="form-text text-muted">Jika jawaban sesuai nilai ini, maka akan lompat</small>
                            </div>

                            <div class="form-group">
                                <label for="target_question_id">Lompat ke Pertanyaan *</label>
                                <select name="target_question_id" id="target_question_id" class="form-control" required>
                                    <option value="">-- Pilih Pertanyaan Tujuan --</option>
                                    <?php foreach ($questions as $q): ?>
                                        <option value="<?= $q->id ?>">#<?= $q->order ?>: <?= substr(htmlspecialchars($q->question_text), 0, 50) ?>...</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div id="cycle_warning" class="alert alert-danger d-none">
                                <i class="fa fa-times-circle"></i> Logic ini akan membuat circular reference!
                            </div>

                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fa fa-save"></i> Simpan Logic
                            </button>
                        <?= form_close() ?>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fa fa-list"></i> Daftar Logic Jump</h5>
                    </div>
                    <div class="card-body">
                        <div id="logic_list">
                            <?php if (empty($logics)): ?>
                                <p class="text-muted text-center py-4">Belum ada logic jump. Tambahkan logic pertama Anda!</p>
                            <?php else: ?>
                                <?php foreach ($logics as $logic): ?>
                                    <?php 
                                    $source = null;
                                    $target = null;
                                    foreach ($questions as $q) {
                                        if ($q->id == $logic->question_id) $source = $q;
                                        if ($q->id == $logic->target_question_id) $target = $q;
                                    }
                                    ?>
                                    <div class="logic-card">
                                        <div class="d-flex align-items-center flex-wrap">
                                            <div class="flex-grow-1">
                                                <strong>#<?= $source->order ?>:</strong> <?= htmlspecialchars($source->question_text) ?>
                                            </div>
                                            <div class="logic-arrow">
                                                <i class="fa fa-arrow-right"></i>
                                            </div>
                                            <div>
                                                <span class="condition-badge">Jika "<?= htmlspecialchars($logic->condition_value) ?>"</span>
                                            </div>
                                            <div class="logic-arrow">
                                                <i class="fa fa-long-arrow-alt-right"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <strong>#<?= $target->order ?>:</strong> <?= htmlspecialchars($target->question_text) ?>
                                            </div>
                                            <div class="ml-3">
                                                <button onclick="deleteLogic(<?= $logic->id ?>)" class="btn btn-sm btn-danger">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= base_url('assets/js/jquery.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>"></script>
    <script>
        $(document).ready(function() {
            // Check for cycle on target selection
            $('#target_question_id').on('change', function() {
                checkCycle();
            });

            $('#question_id').on('change', function() {
                updateConditionPlaceholder();
            });
        });

        function updateConditionPlaceholder() {
            var selected = $('#question_id option:selected');
            var type = selected.data('type');
            var options = selected.data('options');
            
            if (type === 'rating') {
                $('#condition_value').attr('placeholder', 'Contoh: 5 (untuk rating 5 bintang)');
            } else if (options) {
                var firstOption = options.split('|')[0];
                $('#condition_value').attr('placeholder', 'Contoh: ' + firstOption);
            }
        }

        function checkCycle() {
            var questionId = $('#question_id').val();
            var targetId = $('#target_question_id').val();
            var surveyId = <?= $survey->id ?>;

            if (!questionId || !targetId) return;

            $.ajax({
                url: '<?= site_url('survey_logic/validate_cycle_ajax') ?>',
                type: 'POST',
                data: { survey_id: surveyId, question_id: questionId, target_id: targetId },
                dataType: 'json',
                success: function(response) {
                    if (response.has_cycle) {
                        $('#cycle_warning').removeClass('d-none');
                        $('#logicForm button[type="submit"]').prop('disabled', true);
                    } else {
                        $('#cycle_warning').addClass('d-none');
                        $('#logicForm button[type="submit"]').prop('disabled', false);
                    }
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
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                }
            });
        }

        // Form submission
        $('#logicForm').on('submit', function(e) {
            e.preventDefault();
            
            $.ajax({
                url: this.action,
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
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
        });
    </script>
</body>
</html>
