<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Dashboard - Tracer Study</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; }
        h1, h2, h3 { color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .summary { display: table; width: 100%; margin-bottom: 20px; }
        .summary-row { display: table-row; }
        .summary-cell { display: table-cell; padding: 10px; border: 1px solid #ddd; text-align: center; width: 25%; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f5f5f5; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #666; }
    </style>
</head>
<body>

<div class="header">
    <h1>LAPORAN DASHBOARD TRACER STUDY</h1>
    <p>Tahun: <?= $tahun ?? date('Y') ?></p>
    <p>Dicetak pada: <?= date('d/m/Y H:i:s') ?></p>
</div>

<h3>Ringkasan Statistik</h3>
<div class="summary">
    <div class="summary-row">
        <div class="summary-cell">
            <strong>Total Responden</strong><br>
            <?= number_format($total_responden ?? 0) ?>
        </div>
        <div class="summary-cell">
            <strong>Rata-rata Gaji</strong><br>
            Rp <?= number_format($avg_salary ?? 0, 0, ',', '.') ?>
        </div>
        <div class="summary-cell">
            <strong>Masa Tunggu</strong><br>
            <?= number_format($avg_wait_time ?? 0, 1) ?> Bulan
        </div>
        <div class="summary-cell">
            <strong>Skor IKU-1</strong><br>
            <?= number_format($iku_score ?? 0, 1) ?>%
        </div>
    </div>
</div>

<h3>Distribusi Status Kerja</h3>
<table>
    <thead>
        <tr>
            <th>Status</th>
            <th>Jumlah</th>
            <th>Persentase</th>
        </tr>
    </thead>
    <tbody>
        <?php if(!empty($status_kerja)): ?>
            <?php 
            $total = array_sum(array_column($status_kerja, 'count'));
            foreach($status_kerja as $item): 
            ?>
            <tr>
                <td><?= $item['status'] ?></td>
                <td><?= number_format($item['count']) ?></td>
                <td><?= number_format(($item['count'] / $total) * 100, 1) ?>%</td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="3" class="text-center">Tidak ada data</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<h3>Rata-rata Gaji per Program Studi</h3>
<table>
    <thead>
        <tr>
            <th>Program Studi</th>
            <th>Rata-rata Gaji (Rp)</th>
        </tr>
    </thead>
    <tbody>
        <?php if(!empty($gaji_prodi)): ?>
            <?php foreach($gaji_prodi as $item): ?>
            <tr>
                <td><?= $item['prodi'] ?></td>
                <td>Rp <?= number_format($item['avg_salary'], 0, ',', '.') ?></td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="2" class="text-center">Tidak ada data</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<div class="footer">
    <p>Laporan ini dihasilkan secara otomatis oleh Sistem Tracer Study</p>
</div>

</body>
</html>
