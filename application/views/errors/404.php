<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Tidak Ditemukan - 404</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .error-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
            text-align: center;
            max-width: 500px;
            width: 100%;
        }
        
        .error-icon {
            font-size: 80px;
            margin-bottom: 20px;
        }
        
        .error-code {
            font-size: 72px;
            font-weight: bold;
            color: #f5576c;
            margin-bottom: 10px;
        }
        
        .error-title {
            font-size: 24px;
            color: #2c3e50;
            margin-bottom: 15px;
        }
        
        .error-message {
            font-size: 16px;
            color: #7f8c8d;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .btn-back {
            display: inline-block;
            background: #f5576c;
            color: white;
            padding: 12px 30px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-back:hover {
            background: #e0455a;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(245, 87, 108, 0.4);
        }
        
        .btn-home {
            display: inline-block;
            background: #95a5a6;
            color: white;
            padding: 12px 30px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-left: 10px;
        }
        
        .btn-home:hover {
            background: #7f8c8d;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">🔍</div>
        <div class="error-code">404</div>
        <h1 class="error-title">Halaman Tidak Ditemukan</h1>
        <p class="error-message">
            Maaf, halaman yang Anda cari tidak dapat ditemukan. 
            Mungkin URL salah atau halaman telah dipindahkan.
        </p>
        <a href="javascript:history.back()" class="btn-back">← Kembali</a>
        <a href="<?php echo base_url(); ?>" class="btn-home">Beranda</a>
    </div>
</body>
</html>
