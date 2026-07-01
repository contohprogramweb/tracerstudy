<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Tracer Study - Sistem Pelacakan Alumni' ?></title>
    
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
        
        /* Navbar */
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
        
        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 100px 0 80px;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="rgba(255,255,255,0.1)" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,112C672,96,768,96,864,112C960,128,1056,160,1152,165C1248,171,1344,149,1392,138.7L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom;
            background-size: cover;
        }
        
        .hero-section h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }
        
        .hero-section p {
            font-size: 1.2rem;
            opacity: 0.95;
            margin-bottom: 2rem;
        }
        
        .hero-btn {
            background: white;
            color: var(--primary-color);
            padding: 0.75rem 2rem;
            border-radius: 30px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: transform 0.3s, box-shadow 0.3s;
            margin: 0.5rem;
        }
        
        .hero-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            color: var(--primary-color);
        }
        
        .hero-btn-outline {
            background: transparent;
            border: 2px solid white;
            color: white;
        }
        
        .hero-btn-outline:hover {
            background: white;
            color: var(--primary-color);
        }
        
        /* Features Section */
        .features-section {
            padding: 80px 0;
            background: #f8f9fa;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .section-title h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 1rem;
        }
        
        .section-title p {
            color: #666;
            font-size: 1.1rem;
        }
        
        .feature-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: transform 0.3s, box-shadow 0.3s;
            height: 100%;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }
        
        .feature-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            color: white;
        }
        
        .feature-card h3 {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #333;
        }
        
        .feature-card p {
            color: #666;
            line-height: 1.6;
        }
        
        /* About Section */
        .about-section {
            padding: 80px 0;
            background: white;
        }
        
        .about-content {
            display: flex;
            align-items: center;
            gap: 3rem;
        }
        
        .about-image {
            flex: 1;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .about-image img {
            width: 100%;
            height: auto;
            display: block;
        }
        
        .about-text {
            flex: 1;
        }
        
        .about-text h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: #333;
        }
        
        .about-text p {
            color: #666;
            line-height: 1.8;
            margin-bottom: 1.5rem;
        }
        
        .about-stats {
            display: flex;
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }
        
        /* CTA Section */
        .cta-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 80px 0;
            text-align: center;
        }
        
        .cta-section h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .cta-section p {
            font-size: 1.2rem;
            opacity: 0.95;
            margin-bottom: 2rem;
        }
        
        /* Footer */
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
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero-section h1 {
                font-size: 2rem;
            }
            
            .about-content {
                flex-direction: column;
            }
            
            .about-stats {
                flex-wrap: wrap;
                justify-content: center;
            }
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
                        <a class="nav-link" href="#about">Tentang</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Fitur</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Kontak</a>
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

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <h1>Sistem Tracer Study Perguruan Tinggi</h1>
                    <p>Platform terintegrasi untuk melacak perkembangan alumni, menganalisis kualitas pendidikan, dan membangun koneksi dengan stakeholder industri.</p>
                    <div>
                        <a href="<?= site_url('login') ?>" class="hero-btn">
                            <i class="bi bi-box-arrow-in-right"></i> Masuk Sekarang
                        </a>
                        <a href="#about" class="hero-btn hero-btn-outline">
                            <i class="bi bi-info-circle"></i> Pelajari Lebih
                        </a>
                    </div>
                </div>
                <div class="col-lg-5 text-center">
                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 500 400'%3E%3Crect fill='rgba(255,255,255,0.2)' width='500' height='400' rx='20'/%3E%3Ccircle cx='250' cy='150' r='60' fill='rgba(255,255,255,0.3)'/%3E%3Crect x='150' y='240' width='200' height='120' rx='10' fill='rgba(255,255,255,0.3)'/%3E%3C/svg%3E" 
                         alt="Tracer Study Illustration" 
                         class="img-fluid" 
                         style="max-width: 100%; height: auto; border-radius: 20px;">
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features-section">
        <div class="container">
            <div class="section-title">
                <h2>Fitur Utama</h2>
                <p>Temukan berbagai fitur yang membantu menghubungkan alumni dengan institusi</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-person-badge"></i>
                        </div>
                        <h3>Profil Alumni</h3>
                        <p>Kelola profil profesional Anda dengan lengkap, termasuk riwayat pendidikan, pengalaman kerja, dan pencapaian karir.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                        <h3>Survei Online</h3>
                        <p>Isi survei tracer study dengan mudah untuk membantu institusi mengevaluasi dan meningkatkan kualitas pendidikan.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                        <h3>Analisis IKU</h3>
                        <p>Pemantauan Indikator Kinerja Utama untuk mengukur keberhasilan program studi dan fakultas.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-briefcase"></i>
                        </div>
                        <h3>Koneksi Stakeholder</h3>
                        <p>Hubungan langsung dengan dunia industri dan stakeholder untuk peluang kerjasama dan rekrutmen.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-journal-check"></i>
                        </div>
                        <h3>Laporan Komprehensif</h3>
                        <p>Akses laporan detail tentang perkembangan alumni, tingkat kepuasan, dan analisis kurikulum.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h3>Keamanan Data</h3>
                        <p>Sistem keamanan berlapis dengan enkripsi data dan proteksi privasi informasi pribadi Anda.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about-section">
        <div class="container">
            <div class="about-content">
                <div class="about-image">
                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 600 400'%3E%3Crect fill='%23f0f0f0' width='600' height='400'/%3E%3Crect x='50' y='50' width='200' height='300' fill='%23667eea' rx='10'/%3E%3Crect x='280' y='50' width='270' height='140' fill='%23764ba2' rx='10'/%3E%3Crect x='280' y='210' width='270' height='140' fill='%23667eea' rx='10'/%3E%3C/svg%3E" 
                         alt="About Tracer Study" 
                         class="img-fluid">
                </div>
                <div class="about-text">
                    <h2>Tentang Tracer Study</h2>
                    <p>Sistem Tracer Study adalah platform digital yang dirancang untuk membantu perguruan tinggi dalam melacak dan memantau perkembangan alumni setelah lulus.</p>
                    <p>Dengan sistem ini, institusi dapat mengumpulkan data penting tentang karir alumni, tingkat kepuasan terhadap pendidikan yang diterima, serta masukan untuk pengembangan kurikulum yang lebih relevan dengan kebutuhan industri.</p>
                    <div class="about-stats">
                        <div class="stat-item">
                            <div class="stat-number">1000+</div>
                            <div class="stat-label">Alumni Terdaftar</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">50+</div>
                            <div class="stat-label">Program Studi</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">200+</div>
                            <div class="stat-label">Stakeholder</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <h2>Siap untuk Terhubung?</h2>
            <p>Bergabunglah dengan sistem Tracer Study dan berkontribusi dalam pengembangan pendidikan tinggi</p>
            <a href="<?= site_url('login') ?>" class="hero-btn">
                <i class="bi bi-box-arrow-in-right"></i> Login Sekarang
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contact">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3><i class="bi bi-mortarboard"></i> Tracer Study</h3>
                    <p>Sistem pelacakan alumni terintegrasi untuk perguruan tinggi Indonesia. Membangun koneksi antara alumni, institusi, dan industri.</p>
                </div>
                <div class="footer-section">
                    <h3>Tautan Cepat</h3>
                    <p><a href="<?= site_url() ?>">Beranda</a></p>
                    <p><a href="<?= site_url('login') ?>">Login</a></p>
                    <p><a href="#about">Tentang Kami</a></p>
                    <p><a href="#features">Fitur</a></p>
                </div>
                <div class="footer-section">
                    <h3>Kontak</h3>
                    <p><i class="bi bi-geo-alt"></i> Kampus Universitas</p>
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
    
    <script>
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
        
        // Navbar background on scroll
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.style.boxShadow = '0 4px 20px rgba(0,0,0,0.15)';
            } else {
                navbar.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
            }
        });
    </script>
</body>
</html>
