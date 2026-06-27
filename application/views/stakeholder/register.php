<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Registrasi Stakeholder</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        .registration-card {
            max-width: 800px;
            margin: 2rem auto;
            border-radius: 15px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        
        .card-header-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 2rem;
        }
        
        .form-section {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .form-section-title {
            color: #667eea;
            font-weight: 600;
            margin-bottom: 1rem;
            border-bottom: 2px solid #667eea;
            padding-bottom: 0.5rem;
        }
        
        .industry-icon {
            font-size: 2rem;
            color: #667eea;
            margin-right: 1rem;
        }
        
        @media (max-width: 768px) {
            .registration-card {
                margin: 1rem;
            }
            
            .card-header-custom {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body class="bg-light">

<div class="container py-4">
    <div class="registration-card card border-0">
        <div class="card-header card-header-custom text-center">
            <h2><i class="fas fa-handshake"></i> Registrasi Stakeholder/DUDI</h2>
            <p class="mb-0">Daftar sebagai mitra industri untuk menilai kompetensi alumni</p>
        </div>
        
        <div class="card-body p-4">
            <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> <?= $this->session->flashdata('success') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($this->session->flashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i> <?= $this->session->flashdata('error') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?= form_open('', ['class' => 'needs-validation', 'novalidate']) ?>
            
            <!-- Section 1: Informasi Perusahaan -->
            <div class="form-section">
                <h5 class="form-section-title">
                    <i class="fas fa-building"></i> Informasi Perusahaan/Instansi
                </h5>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="company_name" class="form-label">Nama Perusahaan/Instansi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="company_name" name="company_name" 
                               value="<?= set_value('company_name') ?>" required>
                        <div class="invalid-feedback">Nama perusahaan wajib diisi</div>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="company_type" class="form-label">Jenis Instansi <span class="text-danger">*</span></label>
                        <select class="form-select" id="company_type" name="company_type" required>
                            <option value="">Pilih Jenis Instansi</option>
                            <option value="pt" <?= set_select('company_type', 'pt') ?>>PT (Perseroan Terbatas)</option>
                            <option value="cv" <?= set_select('company_type', 'cv') ?>>CV</option>
                            <option value="pd" <?= set_select('company_type', 'pd') ?>>Perusahaan Daerah</option>
                            <option value="bumn" <?= set_select('company_type', 'bumn') ?>>BUMN</option>
                            <option value="pns" <?= set_select('company_type', 'pns') ?>>Instansi Pemerintah</option>
                            <option value="swasta" <?= set_select('company_type', 'swasta') ?>>Swasta Lainnya</option>
                            <option value="startup" <?= set_select('company_type', 'startup') ?>>Startup</option>
                            <option value="ngo" <?= set_select('company_type', 'ngo') ?>>NGO/LSM</option>
                        </select>
                        <div class="invalid-feedback">Jenis instansi wajib dipilih</div>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="industry" class="form-label">Bidang Industri <span class="text-danger">*</span></label>
                        <select class="form-select" id="industry" name="industry" required>
                            <option value="">Pilih Bidang Industri</option>
                            <option value="teknologi" <?= set_select('industry', 'teknologi') ?>>Teknologi Informasi</option>
                            <option value="manufaktur" <?= set_select('industry', 'manufaktur') ?>>Manufaktur</option>
                            <option value="keuangan" <?= set_select('industry', 'keuangan') ?>>Keuangan & Perbankan</option>
                            <option value="kesehatan" <?= set_select('industry', 'kesehatan') ?>>Kesehatan</option>
                            <option value="pendidikan" <?= set_select('industry', 'pendidikan') ?>>Pendidikan</option>
                            <option value="retail" <?= set_select('industry', 'retail') ?>>Retail & Perdagangan</option>
                            <option value="konstruksi" <?= set_select('industry', 'konstruksi') ?>>Konstruksi</option>
                            <option value="telekomunikasi" <?= set_select('industry', 'telekomunikasi') ?>>Telekomunikasi</option>
                            <option value="energi" <?= set_select('industry', 'energi') ?>>Energi & Pertambangan</option>
                            <option value="lainnya" <?= set_select('industry', 'lainnya') ?>>Lainnya</option>
                        </select>
                        <div class="invalid-feedback">Bidang industri wajib dipilih</div>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="website" class="form-label">Website Perusahaan</label>
                        <input type="url" class="form-control" id="website" name="website" 
                               value="<?= set_value('website') ?>" placeholder="https://www.example.com">
                    </div>
                    
                    <div class="col-12">
                        <label for="address" class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="address" name="address" rows="2" 
                                  required><?= set_value('address') ?></textarea>
                        <div class="invalid-feedback">Alamat wajib diisi</div>
                    </div>
                    
                    <div class="col-md-4">
                        <label for="city" class="form-label">Kota/Kabupaten</label>
                        <input type="text" class="form-control" id="city" name="city" 
                               value="<?= set_value('city') ?>">
                    </div>
                    
                    <div class="col-md-4">
                        <label for="province" class="form-label">Provinsi</label>
                        <input type="text" class="form-control" id="province" name="province" 
                               value="<?= set_value('province') ?>">
                    </div>
                    
                    <div class="col-md-4">
                        <label for="postal_code" class="form-label">Kode Pos</label>
                        <input type="text" class="form-control" id="postal_code" name="postal_code" 
                               value="<?= set_value('postal_code') ?>" maxlength="5">
                    </div>
                </div>
            </div>
            
            <!-- Section 2: Informasi Contact Person -->
            <div class="form-section">
                <h5 class="form-section-title">
                    <i class="fas fa-user-tie"></i> Informasi Contact Person
                </h5>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="contact_person" class="form-label">Nama Contact Person <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="contact_person" name="contact_person" 
                               value="<?= set_value('contact_person') ?>" required>
                        <div class="invalid-feedback">Nama contact person wajib diisi</div>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="position" class="form-label">Jabatan/Posisi</label>
                        <input type="text" class="form-control" id="position" name="position" 
                               value="<?= set_value('position') ?>" placeholder="HRD Manager, Supervisor, dll">
                    </div>
                    
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?= set_value('email') ?>" required>
                        <div class="invalid-feedback">Email valid wajib diisi</div>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="phone" class="form-label">Nomor Telepon/WhatsApp <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" id="phone" name="phone" 
                               value="<?= set_value('phone') ?>" required placeholder="08xxxxxxxxxx">
                        <div class="invalid-feedback">Nomor telepon wajib diisi</div>
                    </div>
                </div>
            </div>
            
            <!-- Terms and Conditions -->
            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" id="agree_terms" required>
                <label class="form-check-label" for="agree_terms">
                    Saya menyetujui <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Syarat dan Ketentuan</a> 
                    sebagai stakeholder dalam sistem tracer study alumni
                </label>
                <div class="invalid-feedback">Anda harus menyetujui syarat dan ketentuan</div>
            </div>
            
            <!-- Submit Button -->
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="<?= site_url('home') ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <button type="submit" class="btn btn-primary btn-lg px-5">
                    <i class="fas fa-paper-plane"></i> Daftar Sekarang
                </button>
            </div>
            
            <?= form_close() ?>
        </div>
    </div>
</div>

<!-- Terms Modal -->
<div class="modal fade" id="termsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Syarat dan Ketentuan Stakeholder</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <ol>
                    <li>Stakeholder wajib memberikan informasi yang benar dan akurat</li>
                    <li>Data yang diberikan akan digunakan untuk keperluan tracer study dan akreditasi</li>
                    <li>Stakeholder bersedia menilai kompetensi alumni secara objektif</li>
                    <li>Informasi pribadi stakeholder akan dijaga kerahasiaannya</li>
                    <li>Stakeholder dapat mengundurkan diri kapan saja dengan pemberitahuan</li>
                </ol>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
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
                form.classList.add('was-validated')
            }, false)
        })
})()
</script>

</body>
</html>
