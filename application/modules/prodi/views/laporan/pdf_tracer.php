<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Tracer Study - <?= esc($prodi_info['nama_prodi'] ?? 'Program Studi') ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #007bff; padding-bottom: 20px; margin-bottom: 30px; }
        .header h1 { margin: 0; color: #007bff; }
        .header h2 { margin: 10px 0; color: #666; }
        .info-section { background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .footer { margin-top: 40px; text-align: center; font-size: 12px; color: #666; border-top: 1px solid #dee2e6; padding-top: 20px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #dee2e6; padding: 12px; text-align: left; }
        th { background: #007bff; color: white; }
        tr:nth-child(even) { background: #f8f9fa; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Tracer Study</h1>
        <h2><?= esc($prodi_info['nama_prodi'] ?? 'Program Studi') ?></h2>
        <p>Tahun Lulus: <?= $tahun ?></p>
    </div>
    <div class="info-section">
        <strong>Informasi Laporan:</strong><br>
        Tanggal Generate: <?= $generated_at ?><br>
        Program Studi: <?= esc($prodi_info['nama_prodi'] ?? '-') ?>
    </div>
    <h3>Ringkasan Data Alumni</h3>
    <table>
        <tr><th>Total Alumni</th><td><?= number_format($total_alumni) ?></td></tr>
    </table>
    <div class="footer">
        <p>Laporan ini di-generate otomatis oleh Sistem Tracer Study</p>
        <p>&copy; <?= date('Y') ?> Universitas</p>
    </div>
</body>
</html>
