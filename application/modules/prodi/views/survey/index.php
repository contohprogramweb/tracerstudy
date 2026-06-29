<div class="container-fluid">
    <!-- Page Title -->
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="text-dark fw-bold mb-1">
                <i class="fas fa-clipboard-list me-2"></i><?= esc($page_title) ?>
            </h2>
            <p class="text-muted mb-0"><?= esc($page_subtitle) ?></p>
        </div>
    </div>

    <!-- Surveys Grid -->
    <?php if (!empty($surveys)): ?>
    <div class="row g-4">
        <?php foreach ($surveys as $survey): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <!-- Status Badge -->
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <span class="badge bg-<?= $survey['status'] === 'active' ? 'success' : ($survey['status'] === 'draft' ? 'warning' : 'secondary') ?>">
                            <?= ucfirst($survey['status']) ?>
                        </span>
                        <small class="text-muted">
                            <i class="far fa-calendar me-1"></i>
                            <?= date('d/m/Y', strtotime($survey['created_at'])) ?>
                        </small>
                    </div>

                    <!-- Title -->
                    <h5 class="card-title text-dark mb-2"><?= esc($survey['title']) ?></h5>
                    
                    <!-- Description -->
                    <p class="card-text text-muted small mb-3">
                        <?= esc(substr($survey['description'] ?? '', 0, 100)) ?><?= strlen($survey['description'] ?? '') > 100 ? '...' : '' ?>
                    </p>

                    <!-- Info -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">Pertanyaan</small>
                            <small class="fw-bold"><?= $survey['total_questions'] ?? 0 ?></small>
                        </div>
                        <div class="d-flex justify-content-between">
                            <small class="text-muted">Durasi</small>
                            <small class="fw-bold"><?= $survey['duration_minutes'] ?? 0 ?> menit</small>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="d-grid gap-2">
                        <?php if ($survey['status'] === 'active'): ?>
                        <a href="<?= site_url('alumni/survey/fill/' . $survey['id']) ?>" class="btn btn-primary btn-sm" target="_blank">
                            <i class="fas fa-pen-to-square me-2"></i>Isi Survei
                        </a>
                        <?php else: ?>
                        <button class="btn btn-secondary btn-sm" disabled>
                            <i class="fas fa-lock me-2"></i>Survei Tidak Aktif
                        </button>
                        <?php endif; ?>
                        <a href="<?= site_url('prodi/survey/view/' . $survey['id']) ?>" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-eye me-2"></i>Lihat Detail
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <!-- Empty State -->
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-inbox text-muted fa-4x mb-3"></i>
            <h5 class="text-muted">Belum Ada Survei</h5>
            <p class="text-muted small">Belum ada survei yang ditujukan untuk program studi ini.</p>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}
.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}
</style>
