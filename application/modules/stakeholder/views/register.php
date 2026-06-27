<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Registrasi Stakeholder' ?> - Alumni System</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/font-awesome.min.css') ?>">
    <style>
        .registration-form { max-width: 800px; margin: 0 auto; }
        .form-section { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .form-section h4 { color: #495057; margin-bottom: 15px; border-bottom: 2px solid #dee2e6; padding-bottom: 10px; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="registration-form">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0"><i class="fa fa-building"></i> <?= $page_title ?></h3>
                </div>
                <div class="card-body">
                    <?= form_open('stakeholder/register', ['class' => 'needs-validation']) ?>
                    
                    <!-- Informasi Perusahaan -->
                    <div class="form-section">
                        <h4><i class="fa fa-building"></i> Informasi Perusahaan/Instansi</h4>
                        
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Nama Perusahaan <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="text" name="company_name" class="form-control" 
                                       value="<?= set_value('company_name') ?>" required minlength="3">
                                <?= form_error('company_name', '<small class="text-danger">', '</small>') ?>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Jenis Instansi <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <select name="company_type" class="form-control" required>
                                    <option value="">-- Pilih Jenis --</option>
                                    <option value="BUMN" <?= set_value('company_type') == 'BUMN' ? 'selected' : '' ?>>BUMN</option>
                                    <option value="BUMS" <?= set_value('company_type') == 'BUMS' ? 'selected' : '' ?>>BUMS/Swasta</option>
                                    <option value="PNS" <?= set_value('company_type') == 'PNS' ? 'selected' : '' ?>>Instansi Pemerintah</option>
                                    <option value="Startup" <?= set_value('company_type') == 'Startup' ? 'selected' : '' ?>>Startup</option>
                                    <option value="NGO" <?= set_value('company_type') == 'NGO' ? 'selected' : '' ?>>LSM/NGO</option>
                                    <option value="Lainnya" <?= set_value('company_type') == 'Lainnya' ? 'selected' : '' ?>>Lainnya</option>
                                </select>
                                <?= form_error('company_type', '<small class="text-danger">', '</small>') ?>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Bidang Industri <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <select name="industry" class="form-control" required>
                                    <option value="">-- Pilih Industri --</option>
                                    <option value="Teknologi Informasi" <?= set_value('industry') == 'Teknologi Informasi' ? 'selected' : '' ?>>Teknologi Informasi</option>
                                    <option value="Keuangan" <?= set_value('industry') == 'Keuangan' ? 'selected' : '' ?>>Keuangan/Perbankan</option>
                                    <option value="Manufaktur" <?= set_value('industry') == 'Manufaktur' ? 'selected' : '' ?>>Manufaktur</option>
                                    <option value="Kesehatan" <?= set_value('industry') == 'Kesehatan' ? 'selected' : '' ?>>Kesehatan</option>
                                    <option value="Pendidikan" <?= set_value('industry') == 'Pendidikan' ? 'selected' : '' ?>>Pendidikan</option>
                                    <option value="Retail" <?= set_value('industry') == 'Retail' ? 'selected' : '' ?>>Retail/Perdagangan</option>
                                    <option value="Konstruksi" <?= set_value('industry') == 'Konstruksi' ? 'selected' : '' ?>>Konstruksi</option>
                                    <option value="Lainnya" <?= set_value('industry') == 'Lainnya' ? 'selected' : '' ?>>Lainnya</option>
                                </select>
                                <?= form_error('industry', '<small class="text-danger">', '</small>') ?>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Alamat Lengkap <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <textarea name="address" class="form-control" rows="3" required><?= set_value('address') ?></textarea>
                                <?= form_error('address', '<small class="text-danger">', '</small>') ?>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Kota/Kabupaten</label>
                            <div class="col-sm-9">
                                <input type="text" name="city" class="form-control" value="<?= set_value('city') ?>">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Provinsi</label>
                            <div class="col-sm-9">
                                <input type="text" name="province" class="form-control" value="<?= set_value('province') ?>">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Website</label>
                            <div class="col-sm-9">
                                <input type="url" name="website" class="form-control" value="<?= set_value('website') ?>" placeholder="https://">
                            </div>
                        </div>
                    </div>

                    <!-- Informasi Kontak Person -->
                    <div class="form-section">
                        <h4><i class="fa fa-user"></i> Informasi Kontak Person</h4>
                        
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Nama Kontak Person <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="text" name="contact_person" class="form-control" 
                                       value="<?= set_value('contact_person') ?>" required>
                                <?= form_error('contact_person', '<small class="text-danger">', '</small>') ?>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Jabatan</label>
                            <div class="col-sm-9">
                                <input type="text" name="position" class="form-control" value="<?= set_value('position') ?>">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Email <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="email" name="email" class="form-control" 
                                       value="<?= set_value('email') ?>" required>
                                <?= form_error('email', '<small class="text-danger">', '</small>') ?>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Telepon/HP <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="text" name="phone" class="form-control" 
                                       value="<?= set_value('phone') ?>" required>
                                <?= form_error('phone', '<small class="text-danger">', '</small>') ?>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> 
                        Setelah registrasi, Anda akan menerima email verifikasi. Silakan klik link verifikasi untuk mengaktifkan akun.
                    </div>

                    <div class="form-group row">
                        <div class="col-sm-9 offset-sm-3">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fa fa-save"></i> Daftar Sekarang
                            </button>
                            <a href="<?= site_url('dashboard') ?>" class="btn btn-secondary btn-lg ml-2">
                                <i class="fa fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </div>

                    <?= form_close() ?>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= base_url('assets/js/jquery.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>"></script>
    <script>
        // Form validation
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.getElementsByClassName('needs-validation');
                Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();
    </script>
</body>
</html>
