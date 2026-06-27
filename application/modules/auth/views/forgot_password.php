<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Lupa Password - Tracer Study' ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .forgot-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
        }
        
        .forgot-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .forgot-header h2 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        
        .forgot-header p {
            margin: 10px 0 0;
            opacity: 0.9;
            font-size: 14px;
        }
        
        .forgot-body {
            padding: 40px;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
            transition: transform 0.2s;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .forgot-footer {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            font-size: 14px;
        }
        
        .forgot-footer a {
            color: #667eea;
            text-decoration: none;
        }
        
        .forgot-footer a:hover {
            text-decoration: underline;
        }
        
        .input-group-text {
            background-color: #f8f9fa;
        }
        
        .alert {
            border-radius: 10px;
        }
        
        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .info-box i {
            color: #2196F3;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="forgot-card">
        <div class="forgot-header">
            <h2><i class="bi bi-key"></i> Lupa Password</h2>
            <p>Reset password akun Anda</p>
        </div>
        
        <div class="forgot-body">
            <?php if ($this->session->flashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <?= $this->session->flashdata('error') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill"></i>
                    <?= $this->session->flashdata('success') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php else: ?>
                <div class="info-box">
                    <i class="bi bi-info-circle"></i>
                    <strong>Info:</strong> Masukkan email yang terdaftar. Link reset password akan dikirim ke email Anda dan berlaku selama 1 jam.
                </div>
            <?php endif; ?>
            
            <?php echo validation_errors('<div class="alert alert-warning">', '</div>'); ?>
            
            <form action="<?= site_url('forgot-password') ?>" method="post">
                <div class="mb-4">
                    <label for="email" class="form-label">Email</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-envelope"></i>
                        </span>
                        <input type="email" 
                               class="form-control" 
                               id="email" 
                               name="email" 
                               placeholder="Masukkan email Anda"
                               value="<?= set_value('email') ?>"
                               required
                               autofocus>
                    </div>
                    <div class="form-text">Kami akan mengirim link reset password ke email ini.</div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-submit w-100">
                    <i class="bi bi-send"></i> Kirim Link Reset
                </button>
            </form>
        </div>
        
        <div class="forgot-footer">
            <p class="mb-0">
                <a href="<?= site_url('login') ?>"><i class="bi bi-arrow-left"></i> Kembali ke Login</a>
            </p>
        </div>
    </div>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert:not(.alert-success):not(.alert-danger)');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>
