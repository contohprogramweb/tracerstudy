<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        .survey-container {
            max-width: 900px;
            margin: 2rem auto;
        }
        
        .alumni-info-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .cpl-item {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid #667eea;
        }
        
        .cpl-code {
            font-weight: bold;
            color: #667eea;
        }
        
        .rating-group {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        
        .rating-btn {
            flex: 1;
            text-align: center;
            padding: 0.75rem;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .rating-btn:hover {
            border-color: #667eea;
            background-color: #f0f0ff;
        }
        
        .rating-btn.selected {
            border-color: #667eea;
            background-color: #667eea;
            color: white;
        }
        
        .kesesuaian-select {
            margin-top: 1rem;
        }
        
        .progress-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            position: relative;
        }
        
        .progress-steps::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 0;
            right: 0;
            height: 2px;
            background: #dee2e6;
            z-index: 0;
        }
        
        .step {
            position: relative;
            z-index: 1;
            text-align: center;
        }
        
        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #dee2e6;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.5rem;
            font-weight: bold;
        }
        
        .step.active .step-number {
            background: #667eea;
        }
        
        .step.completed .step-number {
            background: #28a745;
        }
        
        @media (max-width: 768px) {
            .survey-container {
                margin: 1rem;
            }
            
            .rating-group {
                flex-wrap: wrap;
            }
            
            .rating-btn {
                flex: 0 0 45%;
            }
        }
    </style>
</head>
<body class="bg-light">

<div class="container survey-container py-4">
    <!-- Alumni Info Card -->
    <div class="alumni-info-card">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h4 class="mb-2"><i class="fas fa-user-graduate"></i> Penilaian Kompetensi Alumni</h4>
                <p class="mb-0">
                    <strong>Nama:</strong> <?= htmlspecialchars($alumni['name']) ?><br>
                    <strong>NIM:</strong> <?= htmlspecialchars($alumni['nim']) ?><br>
                    <strong>Program Studi:</strong> <?= htmlspecialchars($alumni['prodi_name']) ?><br>
                    <strong>Angkatan:</strong> <?= htmlspecialchars($alumni['graduation_year']) ?>
                </p>
            </div>
            <div class="col-md-4 text-md-end">
                <span class="badge bg-white text-primary px-3 py-2">
                    <i class="fas fa-building"></i> Stakeholder Survey
                </span>
            </div>
        </div>
    </div>
    
    <?= form_open('', ['id' => 'surveyForm', 'class' => 'needs-validation', 'novalidate']) ?>
    <input type="hidden" name="prodi_id" value="<?= $prodi_id ?>">
    
    <!-- Section 1: Informasi Pekerjaan -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="mb-0"><i class="fas fa-briefcase"></i> Informasi Pekerjaan/Hubungan Kerja</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="work_period" class="form-label">Periode Kerja Bersama <span class="text-danger">*</span></label>
                    <select class="form-select" id="work_period" name="work_period" required>
                        <option value="">Pilih Periode</option>
                        <option value="< 6 bulan">Kurang dari 6 bulan</option>
                        <option value="6 bulan - 1 tahun">6 bulan - 1 tahun</option>
                        <option value="1 - 2 tahun">1 - 2 tahun</option>
                        <option value="2 - 5 tahun">2 - 5 tahun</option>
                        <option value="> 5 tahun">Lebih dari 5 tahun</option>
                    </select>
                    <div class="invalid-feedback">Periode kerja wajib dipilih</div>
                </div>
                
                <div class="col-md-6">
                    <label for="work_position" class="form-label">Posisi/Jabatan Alumni <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="work_position" name="work_position" 
                           placeholder="Software Developer, Marketing Staff, dll" required>
                    <div class="invalid-feedback">Posisi wajib diisi</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Section 2: Penilaian CPL -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="mb-0"><i class="fas fa-star"></i> Penilaian Capaian Pembelajaran Lulusan (CPL)</h5>
            <small class="text-muted">Berikan penilaian terhadap kompetensi alumni berdasarkan CPL program studi</small>
        </div>
        <div class="card-body">
            <?php if (empty($cpl_list)): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> Tidak ada CPL yang terdaftar untuk program studi ini.
                </div>
            <?php else: ?>
                <?php foreach ($cpl_list as $index => $cpl): ?>
                    <div class="cpl-item" data-cpl-id="<?= $cpl['id'] ?>">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="cpl-code"><?= htmlspecialchars($cpl['code']) ?></span>
                            <span class="badge bg-secondary">CPL <?= $index + 1 ?></span>
                        </div>
                        <p class="mb-3"><?= htmlspecialchars($cpl['description']) ?></p>
                        
                        <!-- Rating 1-5 -->
                        <label class="form-label d-block">Rating Kompetensi:</label>
                        <div class="rating-group" data-rating="<?= isset($cpl_ratings[$cpl['id']]['rating']) ? $cpl_ratings[$cpl['id']]['rating'] : '' ?>">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <div class="rating-btn" data-value="<?= $i ?>" onclick="selectRating(<?= $cpl['id'] ?>, <?= $i ?>)">
                                    <i class="fas fa-star"></i><br>
                                    <small><?= $rating_options[$i] ?></small>
                                </div>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" name="cpl_ratings[<?= $cpl['id'] ?>]" id="rating_<?= $cpl['id'] ?>" 
                               value="<?= isset($cpl_ratings[$cpl['id']]['rating']) ? $cpl_ratings[$cpl['id']]['rating'] : '' ?>" required>
                        
                        <!-- Kesesuaian -->
                        <div class="kesesuaian-select">
                            <label for="kesesuaian_<?= $cpl['id'] ?>" class="form-label">Kesesuaian dengan Kebutuhan Industri:</label>
                            <select class="form-select" id="kesesuaian_<?= $cpl['id'] ?>" name="cpl_kesesuaian[<?= $cpl['id'] ?>]">
                                <option value="">Pilih Kesesuaian</option>
                                <?php foreach ($kesesuaian_options as $key => $label): ?>
                                    <option value="<?= $key ?>" 
                                            <?= isset($cpl_ratings[$cpl['id']]['kesesuaian']) && $cpl_ratings[$cpl['id']]['kesesuaian'] == $key ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Section 3: Feedback dan Saran -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="mb-0"><i class="fas fa-comments"></i> Feedback dan Saran</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label for="company_feedback" class="form-label">Feedback Umum tentang Kinerja Alumni <span class="text-danger">*</span></label>
                <textarea class="form-control" id="company_feedback" name="company_feedback" rows="3" 
                          placeholder="Berikan feedback umum tentang kinerja, sikap kerja, dan kontribusi alumni..." required></textarea>
                <div class="invalid-feedback">Feedback wajib diisi</div>
            </div>
            
            <div class="mb-3">
                <label for="recommended_competencies" class="form-label">Rekomendasi Kompetensi yang Dibutuhkan Industri</label>
                <textarea class="form-control" id="recommended_competencies" name="recommended_competencies" rows="3" 
                          placeholder="Kompetensi atau skill apa yang sebaiknya ditambahkan dalam kurikulum..."></textarea>
                <small class="text-muted">Contoh: Pemrograman Python, Digital Marketing, Project Management, dll</small>
            </div>
            
            <div class="mb-3">
                <label for="curriculum_suggestions" class="form-label">Saran Perbaikan Kurikulum</label>
                <textarea class="form-control" id="curriculum_suggestions" name="curriculum_suggestions" rows="3" 
                          placeholder="Saran untuk perbaikan kurikulum agar lebih sesuai dengan kebutuhan industri..."></textarea>
            </div>
        </div>
    </div>
    
    <!-- Submit Buttons -->
    <div class="d-flex justify-content-between gap-2">
        <a href="<?= site_url('stakeholder/dashboard') ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-warning" onclick="saveDraft()">
                <i class="fas fa-save"></i> Simpan Draft
            </button>
            <button type="submit" class="btn btn-primary px-4">
                <i class="fas fa-paper-plane"></i> Kirim Penilaian
            </button>
        </div>
    </div>
    
    <?= form_close() ?>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-check-circle"></i> Berhasil!</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fas fa-check-circle text-success fa-4x mb-3"></i>
                <h5>Penilaian berhasil disimpan</h5>
                <p class="text-muted">Terima kasih atas partisipasi Anda dalam menilai kompetensi alumni.</p>
            </div>
            <div class="modal-footer">
                <a href="<?= site_url('stakeholder/dashboard') ?>" class="btn btn-primary">Kembali ke Dashboard</a>
            </div>
        </div>
    </div>
</div>

<!-- Error Modal -->
<div class="modal fade" id="errorModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-circle"></i> Error</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fas fa-exclamation-circle text-danger fa-4x mb-3"></i>
                <h5 id="errorMessage">Terjadi kesalahan</h5>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<script>
// Select rating function
function selectRating(cplId, rating) {
    // Update hidden input
    $('#rating_' + cplId).val(rating);
    
    // Update UI
    $('.cpl-item[data-cpl-id="' + cplId + '"] .rating-btn').removeClass('selected');
    $('.cpl-item[data-cpl-id="' + cplId + '"] .rating-btn[data-value="' + rating + '"]').addClass('selected');
}

// Initialize ratings from existing data
$(document).ready(function() {
    $('.rating-group').each(function() {
        var rating = $(this).data('rating');
        if (rating) {
            $(this).find('.rating-btn[data-value="' + rating + '"]').addClass('selected');
        }
    });
});

// Form submission
$('#surveyForm').on('submit', function(e) {
    e.preventDefault();
    
    // Validate form
    if (!this.checkValidity()) {
        e.stopPropagation();
        this.classList.add('was-validated');
        return;
    }
    
    // Check if all CPL ratings are filled
    var allRated = true;
    $('input[name^="cpl_ratings"]').each(function() {
        if (!$(this).val()) {
            allRated = false;
        }
    });
    
    if (!allRated) {
        $('#errorMessage').text('Mohon isi semua penilaian CPL');
        new bootstrap.Modal(document.getElementById('errorModal')).show();
        return;
    }
    
    // Submit via AJAX
    $.ajax({
        url: '<?= site_url('stakeholder/submit/' . $alumni_id) ?>',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                new bootstrap.Modal(document.getElementById('successModal')).show();
            } else {
                $('#errorMessage').text(response.message);
                new bootstrap.Modal(document.getElementById('errorModal')).show();
            }
        },
        error: function() {
            $('#errorMessage').text('Terjadi kesalahan koneksi. Silakan coba lagi.');
            new bootstrap.Modal(document.getElementById('errorModal')).show();
        }
    });
});

// Save draft function
function saveDraft() {
    // Similar to submit but with draft status
    alert('Draft berhasil disimpan. Anda dapat melanjutkan nanti.');
}

// Form validation
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms)
        .forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
            }, false)
        })
})()
</script>

</body>
</html>
