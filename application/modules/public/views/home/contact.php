<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Kontak Kami - Tracer Study' ?></title>
    
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
        
        .contact-section {
            padding: 80px 0;
            background: #f8f9fa;
        }
        
        .contact-card {
            background: white;
            border-radius: 15px;
            padding: 2.5rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            height: 100%;
        }
        
        .contact-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 2rem;
        }
        
        .contact-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            margin-right: 1rem;
            flex-shrink: 0;
        }
        
        .contact-info h5 {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #333;
        }
        
        .contact-info p {
            color: #666;
            margin: 0;
            line-height: 1.6;
        }
        
        .map-container {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            height: 400px;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
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
            <h1>Hubungi Kami</h1>
            <p>Kami siap membantu Anda dengan pertanyaan dan kebutuhan informasi</p>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact-section">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="contact-card">
                        <h2 class="mb-4">Informasi Kontak</h2>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="bi bi-geo-alt"></i>
                            </div>
                            <div class="contact-info">
                                <h5>Alamat</h5>
                                <p>Kampus Universitas<br>Jl. Pendidikan No. 123<br>Kota Universitas, 12345</p>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="bi bi-envelope"></i>
                            </div>
                            <div class="contact-info">
                                <h5>Email</h5>
                                <p>tracerstudy@university.edu<br>support@tracerstudy.edu</p>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="bi bi-telephone"></i>
                            </div>
                            <div class="contact-info">
                                <h5>Telepon</h5>
                                <p>(021) 1234-5678<br>(021) 8765-4321</p>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="bi bi-clock"></i>
                            </div>
                            <div class="contact-info">
                                <h5>Jam Operasional</h5>
                                <p>Senin - Jumat: 08.00 - 16.00 WIB<br>Sabtu - Minggu: Tutup</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-7">
                    <div class="contact-card">
                        <h2 class="mb-4">Lokasi Kami</h2>
                        <div class="map-container">
                            <div class="text-center">
                                <i class="bi bi-geo-alt fs-1 mb-3"></i>
                                <p class="mb-0">Peta Lokasi<br>(Integrasikan dengan Google Maps)</p>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <h5 class="mb-3">Butuh Bantuan?</h5>
                            <p class="text-muted">Jika Anda mengalami kesulitan dalam menggunakan sistem atau memiliki pertanyaan, jangan ragu untuk menghubungi tim support kami melalui email atau telepon di atas.</p>
                            <p class="text-muted">Untuk masalah teknis, harap sertakan screenshot error dan deskripsi lengkap masalah yang Anda alami.</p>
                        </div>
                    </div>
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
