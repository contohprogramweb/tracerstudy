<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Status Alumni - <?= htmlspecialchars($prodi_info['nama_prodi'] ?? 'Program Studi') ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            color: #007bff;
        }
        .header h2 {
            margin: 10px 0;
            color: #666;
        }
        .info-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin: 20px 0;
        }
        .stat-card {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 20px;
            text-align: center;
        }
        .stat-card h3 {
            margin: 0;
            color: #666;
            font-size: 14px;
        }
        .stat-card .value {
            font-size: 36px;
            font-weight: bold;
            color: #007bff;
            margin: 10px 0;
        }
        .status-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .status-table th,
        .status-table td {
            border: 1px solid #dee2e6;
            padding: 12px;
            text-align: left;
        }
        .status-table th {
            background: #007bff;
            color: white;
        }
        .status-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #dee2e6;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Status Alumni</h1>
        <h2><?= htmlspecialchars($prodi_info['nama_prodi'] ?? 'Program Studi') ?></h2>
        <p>Tahun Lulus: <?= $tahun ?></p>
    </div>

    <div class="info-section">
        <strong>Informasi Laporan:</strong><br>
        Tanggal Generate: <?= $generated_at ?><br>
        Program Studi: <?= htmlspecialchars($prodi_info['nama_prodi'] ?? '-') ?><br>
        Kode Prodi: <?= htmlspecialchars($prodi_info['kode_prodi'] ?? '-') ?>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Alumni</h3>
            <div class="value"><?= number_format($total_alumni) ?></div>
        </div>
        <div class="stat-card">
            <h3>Tahun</h3>
            <div class="value" style="font-size: 24px;"><?= $tahun ?></div>
        </div>
    </div>

    <h3>Breakdown Status Kerja</h3>
    <table class="status-table">
        <thead>
            <tr>
                <th>Status</th>
                <th>Jumlah</th>
                <th>Persentase</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $statuses = [
                'bekerja' => 'Bekerja',
                'belum_bekerja' => 'Belum Bekerja',
                'wirausaha' => 'Wirausaha',
                'melanjutkan_studi' => 'Melanjutkan Studi'
            ];
            $total = $total_alumni > 0 ? $total_alumni : 1;
            foreach ($statuses as $key => $label): 
                $count = isset($status_breakdown[$key]) ? $status_breakdown[$key] : 0;
                $percentage = round(($count / $total) * 100, 2);
            ?>
            <tr>
                <td><?= $label ?></td>
                <td><?= number_format($count) ?></td>
                <td><?= $percentage ?>%</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer">
        <p>Laporan ini di-generate otomatis oleh Sistem Tracer Study</p>
        <p>&copy; <?= date('Y') ?> Universitas - All Rights Reserved</p>
    </div>
</body>
</html>
