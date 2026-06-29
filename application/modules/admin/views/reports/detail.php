<div class="container-fluid py-4">
    <!-- Back Button -->
    <div class="mb-4">
        <a href="<?= base_url('admin/reports') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Kembali ke Laporan
        </a>
    </div>
    
    <!-- Survey Info -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="bi bi-card-checklist me-2"></i>Informasi Survei</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h4><?= htmlspecialchars($survey['title']) ?></h4>
                    <p class="text-muted"><?= htmlspecialchars($survey['description'] ?? '-') ?></p>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th width="150">Status:</th>
                            <td>
                                <?php if ($survey['status'] == 'active'): ?>
                                    <span class="badge bg-success">Aktif</span>
                                <?php elseif ($survey['status'] == 'draft'): ?>
                                    <span class="badge bg-warning">Draft</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><?= htmlspecialchars($survey['status']) ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Total Respons:</th>
                            <td><span class="badge bg-primary"><?= number_format($total_responses) ?></span></td>
                        </tr>
                        <tr>
                            <th>Dibuat:</th>
                            <td><?= date('d M Y', strtotime($survey['created_at'])) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Questions and Responses -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>Pertanyaan dan Respons</h5>
        </div>
        <div class="card-body">
            <?php if (!empty($questions)): ?>
            <div class="accordion" id="questionsAccordion">
                <?php foreach ($questions as $index => $question): ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading<?= $question['id'] ?>">
                        <button class="accordion-button <?= $index > 0 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $question['id'] ?>">
                            <strong><?= $index + 1 ?>. <?= htmlspecialchars($question['question_text']) ?></strong>
                            <span class="badge bg-info ms-2"><?= htmlspecialchars($question['question_type']) ?></span>
                        </button>
                    </h2>
                    <div id="collapse<?= $question['id'] ?>" class="accordion-collapse collapse <?= $index == 0 ? 'show' : '' ?>" data-bs-parent="#questionsAccordion">
                        <div class="accordion-body">
                            <p class="text-muted mb-3">Tipe: <?= htmlspecialchars($question['question_type']) ?></p>
                            <!-- Response statistics will be shown here -->
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                Statistik detail respons untuk pertanyaan ini akan ditampilkan di sini.
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-inbox text-muted fs-1"></i>
                <p class="text-muted mt-2">Belum ada pertanyaan dalam survei ini</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Actions -->
    <div class="mt-4">
        <a href="<?= base_url('admin/reports/export/excel/' . $survey['id']) ?>" class="btn btn-success">
            <i class="bi bi-file-earmark-excel me-2"></i>Export ke Excel
        </a>
        <a href="<?= base_url('admin/reports/export/pdf/' . $survey['id']) ?>" class="btn btn-danger">
            <i class="bi bi-file-earmark-pdf me-2"></i>Export ke PDF
        </a>
    </div>
</div>
