<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install PWA - Survey Tracer Study</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .install-container {
            max-width: 600px;
            padding: 2rem;
        }
        
        .install-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }
        
        .install-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .install-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: white;
            font-size: 2.5rem;
        }
        
        .install-title {
            font-size: 1.8rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .install-subtitle {
            color: #666;
            font-size: 1rem;
        }
        
        .step-list {
            list-style: none;
            padding: 0;
            margin: 2rem 0;
        }
        
        .step-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .step-item:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }
        
        .step-number {
            width: 35px;
            height: 35px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.1rem;
            margin-right: 1rem;
            flex-shrink: 0;
        }
        
        .step-content {
            flex: 1;
        }
        
        .step-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.3rem;
        }
        
        .step-description {
            color: #666;
            font-size: 0.9rem;
            margin: 0;
        }
        
        .browser-section {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px solid #e9ecef;
        }
        
        .browser-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        
        .browser-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 1rem;
        }
        
        .browser-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .browser-card:hover {
            background: #e9ecef;
            transform: translateY(-3px);
        }
        
        .browser-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .browser-name {
            font-weight: 600;
            font-size: 0.9rem;
            color: #333;
        }
        
        .install-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 1rem 2rem;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            width: 100%;
            margin-top: 1.5rem;
            transition: all 0.3s ease;
        }
        
        .install-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .install-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .benefits-box {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            border-radius: 15px;
            padding: 1.5rem;
            margin-top: 1.5rem;
        }
        
        .benefits-title {
            font-weight: 600;
            color: #667eea;
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }
        
        .benefit-item {
            display: flex;
            align-items: center;
            margin-bottom: 0.7rem;
            color: #555;
        }
        
        .benefit-check {
            color: #28a745;
            margin-right: 0.7rem;
            font-size: 1.1rem;
        }
        
        .skip-link {
            display: block;
            text-align: center;
            margin-top: 1rem;
            color: #666;
            text-decoration: none;
        }
        
        .skip-link:hover {
            color: #333;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="install-card">
            <div class="install-header">
                <div class="install-icon">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <h1 class="install-title">Install Aplikasi</h1>
                <p class="install-subtitle">Pasang Survey Tracer Study di perangkat Anda untuk akses lebih mudah</p>
            </div>
            
            <div id="install-prompt" style="display: none;">
                <button id="install-button" class="install-btn" data-install-pwa>
                    <i class="fas fa-download"></i> Install Sekarang
                </button>
            </div>
            
            <ul class="step-list">
                <li class="step-item">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <div class="step-title">Klik Menu Browser</div>
                        <p class="step-description">Tap ikon menu (⋮ atau ☰) di browser Anda</p>
                    </div>
                </li>
                <li class="step-item">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <div class="step-title">Pilih "Install App"</div>
                        <p class="step-description">Cari opsi "Install App", "Add to Home Screen", atau "Install"</p>
                    </div>
                </li>
                <li class="step-item">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <div class="step-title">Konfirmasi Install</div>
                        <p class="step-description">Klik "Install" atau "Add" untuk menyelesaikan</p>
                    </div>
                </li>
            </ul>
            
            <div class="browser-section">
                <h3 class="browser-title">Panduan Berdasarkan Browser</h3>
                <div class="browser-grid">
                    <div class="browser-card" onclick="showBrowserInfo('chrome')">
                        <div class="browser-icon" style="color: #4285F4;">
                            <i class="fab fa-chrome"></i>
                        </div>
                        <div class="browser-name">Chrome</div>
                    </div>
                    <div class="browser-card" onclick="showBrowserInfo('edge')">
                        <div class="browser-icon" style="color: #0078D7;">
                            <i class="fab fa-edge"></i>
                        </div>
                        <div class="browser-name">Edge</div>
                    </div>
                    <div class="browser-card" onclick="showBrowserInfo('safari')">
                        <div class="browser-icon" style="color: #000000;">
                            <i class="fab fa-safari"></i>
                        </div>
                        <div class="browser-name">Safari</div>
                    </div>
                    <div class="browser-card" onclick="showBrowserInfo('firefox')">
                        <div class="browser-icon" style="color: #FF7139;">
                            <i class="fab fa-firefox"></i>
                        </div>
                        <div class="browser-name">Firefox</div>
                    </div>
                </div>
                
                <div id="browser-info" class="alert alert-info mt-3" style="display: none;">
                    <i class="fas fa-info-circle"></i> <span id="browser-info-text"></span>
                </div>
            </div>
            
            <div class="benefits-box">
                <div class="benefits-title">
                    <i class="fas fa-star"></i> Keuntungan Install Aplikasi
                </div>
                <div class="benefit-item">
                    <i class="fas fa-check-circle benefit-check"></i>
                    <span>Akses cepat dari home screen</span>
                </div>
                <div class="benefit-item">
                    <i class="fas fa-check-circle benefit-check"></i>
                    <span>Bisa digunakan saat offline</span>
                </div>
                <div class="benefit-item">
                    <i class="fas fa-check-circle benefit-check"></i>
                    <span>Notifikasi untuk survey baru</span>
                </div>
                <div class="benefit-item">
                    <i class="fas fa-check-circle benefit-check"></i>
                    <span>Data tersimpan otomatis</span>
                </div>
                <div class="benefit-item">
                    <i class="fas fa-check-circle benefit-check"></i>
                    <span>Tidak perlu download dari app store</span>
                </div>
            </div>
            
            <a href="/survey_builder/survey" class="skip-link">
                <i class="fas fa-times-circle"></i> Lewati dan lanjutkan survey
            </a>
        </div>
    </div>

    <!-- PWA Resources -->
    <link rel="manifest" href="/assets/pwa/manifest.json">
    <meta name="theme-color" content="#667eea">
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/pwa/pwa.js"></script>
    <script>
        let deferredPrompt = null;
        
        $(document).ready(function() {
            // Listen for beforeinstallprompt event
            window.addEventListener('beforeinstallprompt', function(e) {
                e.preventDefault();
                deferredPrompt = e;
                $('#install-prompt').show();
                console.log('Install prompt available');
            });
            
            // Handle install button click
            $('#install-button').on('click', function() {
                if (deferredPrompt) {
                    deferredPrompt.prompt();
                    deferredPrompt.userChoice.then(function(choiceResult) {
                        if (choiceResult.outcome === 'accepted') {
                            console.log('User accepted the install prompt');
                            showSuccessMessage();
                        } else {
                            console.log('User dismissed the install prompt');
                        }
                        deferredPrompt = null;
                    });
                }
            });
            
            // Check if already installed
            if (window.matchMedia('(display-mode: standalone)').matches || 
                window.navigator.standalone === true) {
                showAlreadyInstalled();
            }
            
            // Browser info messages
            window.showBrowserInfo = function(browser) {
                const infoText = {
                    chrome: 'Chrome: Klik menu ⋮ (titik tiga) > "Install Survey Tracer Study" atau "Add to Home screen"',
                    edge: 'Edge: Klik menu ⋮ (titik tiga) > "Apps" > "Install this site as an app"',
                    safari: 'Safari (iOS): Tap tombol Share > Scroll ke bawah > "Add to Home Screen"',
                    firefox: 'Firefox: Klik menu ☰ (garis tiga) > "Install" atau "Add to Home screen"'
                };
                
                $('#browser-info-text').text(infoText[browser]);
                $('#browser-info').fadeIn();
                
                setTimeout(function() {
                    $('#browser-info').fadeOut();
                }, 5000);
            };
            
            function showSuccessMessage() {
                alert('Terima kasih! Aplikasi sedang diinstall. Anda akan dapat mengaksesnya dari home screen setelah instalasi selesai.');
            }
            
            function showAlreadyInstalled() {
                $('#install-prompt').html('<div class="alert alert-success"><i class="fas fa-check-circle"></i> Aplikasi sudah terinstall di perangkat Anda!</div>');
                $('#install-prompt').show();
            }
        });
    </script>
</body>
</html>
