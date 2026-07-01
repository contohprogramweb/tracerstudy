<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Tentang Tracer Study' ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }
        
        .navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 1rem 0;
        }
        
        .navbar-brand {
            font-weight: 700;
            color: var(--primary-color) !important;
            font-size: 1.5rem;
        }
        
        .nav-link {
            color: #333 !important;
            font-weight: 500;
            margin: 0 0.5rem;
            transition: color 0.3s;
        }
        
        .nav-link:hover {
            color: var(--primary-color) !important;
        }
        
        .btn-login {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            font-weight: 600;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 80px 0 60px;
            text-align: center;
        }
        
        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .page-header p {
            font-size: 1.1rem;
            opacity: 0.95;
        }
        
        .content-section {
            padding: 80px 0;
        }
        
        .content-card {
            background: white;
            border-radius: 15px;
            padding: 2.5rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
        }
        
        .content-card h2 {
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            font-weight: 600;
        }
        
        .content-card p {
            color: #666;
            line-height: 1.8;
            margin-bottom: 1rem;
        }
        
        .vision-mission {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .vm-card {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
        }
        
        .vm-card h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .vm-card p {
            opacity: 0.95;
            line-height: 1.6;
        }
        
        footer {
            background: #2c3e50;
            color: white;
            padding: 60px 0 30px;
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .footer-section h3 {
            font-size: 1.2rem;
            margin-bottom: 1rem;
            color: white;
        }
        
        .footer-section p,
        .footer-section a {
            color: rgba(255,255,255,0.7);
            line-height: 1.8;
            text-decoration: none;
        }
        
        .footer-section a:hover {
            color: white;
        }
        
        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid rgba(255,255,255,0.1);
            color: rgba(255,255,255,0.7);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="<?= site_url() ?>">
                <i class="bi bi-mortarboard"></i> Tracer Study
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= site_url() ?>">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= site_url('about') ?>">Tentang</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= site_url() ?>#features">Fitur</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= site_url('contact') ?>">Kontak</a>
                    </li>
                    <li class="nav-item ms-2">
                        <a class="btn btn-login" href="<?= site_url('login') ?>">
                            <i class="bi bi-box-arrow-in-right"></i> Login
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <h1>Tentang Tracer Study</h1>
            <p>Mengenal lebih dekat sistem pelacakan alumni perguruan tinggi</p>
        </div>
    </section>

    <!-- Content Section -->
    <section class="content-section">
        <div class="container">
            <div class="content-card">
                <h2>Apa itu Tracer Study?</h2>
                <p>Tracer Study adalah studi pelacakan terhadap alumni untuk mendapatkan informasi tentang perkembangan mereka setelah lulus dari perguruan tinggi. Sistem ini membantu institusi pendidikan dalam mengevaluasi kualitas pendidikan yang telah diberikan.</p>
                <p>Dengan mengumpulkan data tentang karir alumni, tingkat kepuasan terhadap pendidikan, dan relevansi kurikulum dengan kebutuhan industri, perguruan tinggi dapat terus meningkatkan kualitas pendidikannya.</p>
            </div>
            
            <div class="content-card">
                <h2>Tujuan Sistem</h2>
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-check-circle-fill text-primary fs-4 me-3"></i>
                            <div>
                                <h5 class="fw-bold mb-2">Memetakan Profil Alumni</h5>
                                <p class="text-muted mb-0">Mengetahui posisi dan peran alumni di dunia kerja serta kontribusi mereka bagi masyarakat.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-check-circle-fill text-primary fs-4 me-3"></i>
                            <div>
                                <h5 class="fw-bold mb-2">Evaluasi Kurikulum</h5>
                                <p class="text-muted mb-0">Mendapatkan masukan untuk pengembangan kurikulum yang relevan dengan kebutuhan industri.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-check-circle-fill text-primary fs-4 me-3"></i>
                            <div>
                                <h5 class="fw-bold mb-2">Mengukur IKU</h5>
                                <p class="text-muted mb-0">Menghitung Indikator Kinerja Utama perguruan tinggi sesuai standar Kemendikbud.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-check-circle-fill text-primary fs-4 me-3"></i>
                            <div>
                                <h5 class="fw-bold mb-2">Menjalin Jaringan</h5>
                                <p class="text-muted mb-0">Membangun koneksi berkelanjutan antara alumni, institusi, dan stakeholder industri.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="vision-mission">
                <div class="vm-card">
                    <i class="bi bi-eye fs-1 mb-3"></i>
                    <h3>Visi</h3>
                    <p>Menjadi sistem tracer study terdepan yang mendukung peningkatan kualitas pendidikan tinggi Indonesia melalui data alumni yang akurat dan komprehensif.</p>
                </div>
                <div class="vm-card">
                    <i class="bi bi-bullseye fs-1 mb-3"></i>
                    <h3>Misi</h3>
                    <p>• Menyediakan platform digital yang mudah digunakan<br>
                    • Mengumpulkan data alumni secara berkala dan sistematis<br>
                    • Menganalisis data untuk pengambilan keputusan strategis<br>
                    • Membangun ekosistem alumni yang berkelanjutan</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3><i class="bi bi-mortarboard"></i> Tracer Study</h3>
                    <p>Sistem pelacakan alumni terintegrasi untuk perguruan tinggi Indonesia.</p>
                </div>
                <div class="footer-section">
                    <h3>Tautan Cepat</h3>
                    <p><a href="<?= site_url() ?>">Beranda</a></p>
                    <p><a href="<?= site_url('about') ?>">Tentang</a></p>
                    <p><a href="<?= site_url('login') ?>">Login</a></p>
                </div>
                <div class="footer-section">
                    <h3>Kontak</h3>
                    <p><i class="bi bi-envelope"></i> tracerstudy@university.edu</p>
                    <p><i class="bi bi-telephone"></i> (021) 1234-5678</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> Tracer Study System. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
