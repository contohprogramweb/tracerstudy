<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Employment</title>
    <style>body{font-family:Arial,sans-serif;margin:20px}h1{color:#007bff}.footer{margin-top:40px;text-align:center;font-size:12px;color:#666;border-top:1px solid #dee2e6;padding-top:20px}</style>
</head>
<body>
    <h1>Laporan Keterlibatan Kerja</h1>
    <p>Program Studi: <?= htmlspecialchars($prodi_info['nama_prodi'] ?? '-') ?></p>
    <p>Tahun: <?= $tahun ?></p>
    <p>Dibuat: <?= $generated_at ?></p>
    <div class="footer"><p>&copy; <?= date('Y') ?> Universitas</p></div>
</body>
</html>
