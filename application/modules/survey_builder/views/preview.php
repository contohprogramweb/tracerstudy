<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview Survey - <?= htmlspecialchars($survey->title) ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/font-awesome.min.css') ?>">
    <style>
        .survey-container { max-width: 800px; margin: 0 auto; }
        .question-item { background: #f8f9fa; border-radius: 8px; padding: 20px; margin-bottom: 20px; }
        .question-text { font-size: 16px; font-weight: 600; margin-bottom: 15px; }
        .required-mark { color: #dc3545; }
        .form-control:focus { border-color: #007bff; box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25); }
        .rating-stars { display: inline-flex; gap: 5px; }
        .rating-stars input { display: none; }
        .rating-stars label { cursor: pointer; font-size: 24px; color: #ddd; transition: color 0.2s; }
        .rating-stars input:checked ~ label, .rating-stars label:hover, .rating-stars label:hover ~ label { color: #ffc107; }
        .logic-indicator { background: #fff3cd; border-left: 3px solid #ffc107; padding: 8px 12px; margin-top: 10px; font-size: 13px; }
        .status-badge { padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .status-published { background-color: #28a745; color: #fff; }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="survey-container">
            <div class="mb-4">
                <a href="<?= site_url('survey_builder/index') ?>" class="btn btn-secondary btn-sm">
                    <i class="fa fa-arrow-left"></i> Kembali
                </a>
                <?php if ($survey->status === 'draft'): ?>
                    <span class="badge badge-warning ml-2">PREVIEW MODE</span>
                <?php else: ?>
                    <span class="status-badge status-published ml-2">PUBLISHED</span>
                <?php endif; ?>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h2><?= htmlspecialchars($survey->title) ?></h2>
                    <?php if ($survey->description): ?>
                        <p class="text-muted mt-2"><?= nl2br(htmlspecialchars($survey->description)) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <?= form_open('survey/submit/' . $survey->id, ['id' => 'surveyForm']) ?>
                <input type="hidden" name="current_question" id="current_question" value="1">
                
                <?php 
                $grouped_questions = [];
                foreach ($questions as $q) {
                    if (!isset($grouped_questions[$q->order])) {
                        $grouped_questions[$q->order] = [];
                    }
                    $grouped_questions[$q->order][] = $q;
                }
                $question_index = 1;
                ?>

                <?php foreach ($grouped_questions as $order => $q_list): ?>
                    <?php foreach ($q_list as $q): ?>
                        <div class="question-item" id="question_<?= $q->id ?>" data-order="<?= $order ?>">
                            <div class="question-text">
                                <?= $question_index ?>. <?= htmlspecialchars($q->question_text) ?>
                                <?php if ($q->is_required): ?>
                                    <span class="required-mark">*</span>
                                <?php endif; ?>
                            </div>

                            <?php
                            $field_name = 'answer_' . $q->id;
                            ?>

                            <?php if ($q->type === 'short_answer'): ?>
                                <input type="text" name="<?= $field_name ?>" class="form-control" 
                                       <?= $q->is_required ? 'required' : '' ?>>

                            <?php elseif ($q->type === 'long_answer'): ?>
                                <textarea name="<?= $field_name ?>" class="form-control" rows="4"
                                          <?= $q->is_required ? 'required' : '' ?>></textarea>

                            <?php elseif ($q->type === 'number'): ?>
                                <input type="number" name="<?= $field_name ?>" class="form-control"
                                       <?= $q->is_required ? 'required' : '' ?>>

                            <?php elseif ($q->type === 'date'): ?>
                                <input type="date" name="<?= $field_name ?>" class="form-control"
                                       <?= $q->is_required ? 'required' : '' ?>>

                            <?php elseif ($q->type === 'rating'): ?>
                                <div class="rating-stars">
                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                        <input type="radio" name="<?= $field_name ?>" id="q<?= $q->id ?>_<?= $i ?>" value="<?= $i ?>" <?= $q->is_required ? 'required' : '' ?>>
                                        <label for="q<?= $q->id ?>_<?= $i ?>">&#9733;</label>
                                    <?php endfor; ?>
                                </div>
                                <div class="mt-2 text-muted small">
                                    <span>1 = Sangat Tidak Puas</span>
                                    <span class="ml-3">5 = Sangat Puas</span>
                                </div>

                            <?php elseif ($q->type === 'multiple_choice'): ?>
                                <?php $options = explode('|', $q->options); ?>
                                <?php foreach ($options as $idx => $opt): ?>
                                    <div class="form-check">
                                        <input type="radio" name="<?= $field_name ?>" id="<?= $field_name ?>_<?= $idx ?>" 
                                               value="<?= htmlspecialchars($opt) ?>" class="form-check-input"
                                               <?= $q->is_required ? 'required' : '' ?>>
                                        <label for="<?= $field_name ?>_<?= $idx ?>" class="form-check-label">
                                            <?= htmlspecialchars($opt) ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>

                            <?php elseif ($q->type === 'dropdown'): ?>
                                <select name="<?= $field_name ?>" class="form-control" <?= $q->is_required ? 'required' : '' ?>>
                                    <option value="">-- Pilih Jawaban --</option>
                                    <?php $options = explode('|', $q->options); ?>
                                    <?php foreach ($options as $opt): ?>
                                        <option value="<?= htmlspecialchars($opt) ?>"><?= htmlspecialchars($opt) ?></option>
                                    <?php endforeach; ?>
                                </select>

                            <?php elseif ($q->type === 'checkbox'): ?>
                                <?php $options = explode('|', $q->options); ?>
                                <?php foreach ($options as $idx => $opt): ?>
                                    <div class="form-check">
                                        <input type="checkbox" name="<?= $field_name ?>[]" id="<?= $field_name ?>_<?= $idx ?>" 
                                               value="<?= htmlspecialchars($opt) ?>" class="form-check-input">
                                        <label for="<?= $field_name ?>_<?= $idx ?>" class="form-check-label">
                                            <?= htmlspecialchars($opt) ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <?php if (isset($q->condition_value) && $q->condition_value): ?>
                                <div class="logic-indicator">
                                    <i class="fa fa-random"></i> Jika jawaban "<strong><?= htmlspecialchars($q->condition_value) ?></strong>", 
                                    lompat ke pertanyaan berikutnya sesuai aturan logic.
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php $question_index++; ?>
                    <?php endforeach; ?>
                <?php endforeach; ?>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fa fa-paper-plane"></i> Kirim Jawaban
                    </button>
                </div>
            <?= form_close() ?>
        </div>
    </div>

    <script src="<?= base_url('assets/js/jquery.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>"></script>
    <script>
        $(document).ready(function() {
            // Add any preview-specific JavaScript here
            console.log('Survey preview loaded');
        });
    </script>
</body>
</html>
