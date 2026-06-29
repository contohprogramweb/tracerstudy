<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terjadi Kesalahan - 500</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
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
            color: #4facfe;
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
            background: #4facfe;
            color: white;
            padding: 12px 30px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-back:hover {
            background: #3a9be0;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(79, 172, 254, 0.4);
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
        
        .support-info {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 14px;
            color: #95a5a6;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">⚠️</div>
        <div class="error-code">500</div>
        <h1 class="error-title">Terjadi Kesalahan</h1>
        <p class="error-message">
            Maaf, terjadi kesalahan pada server kami. 
            Tim teknis telah diberitahu dan sedang menangani masalah ini.
        </p>
        <a href="javascript:history.back()" class="btn-back">← Kembali</a>
        <a href="<?php echo base_url(); ?>" class="btn-home">Beranda</a>
        <div class="support-info">
            Jika masalah berlanjut, silakan hubungi administrator sistem.
        </div>
    </div>
</body>
</html>
