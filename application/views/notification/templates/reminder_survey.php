<!DOCTYPE html>
<html>
<head><style>body{font-family:Arial,sans-serif;}</style></head>
<body>
    <h2>Reminder Pengisian Survei</h2>
    <p>Yth. <?= $nama_alumni ?? 'Alumni' ?>,</p>
    <p>Kami mencatat Anda belum menyelesaikan survei tracer study.</p>
    <p><strong>Batas Waktu:</strong> <?= $tanggal_deadline ?? '-' ?></p>
    <p>Silakan klik tombol di bawah ini:</p>
    <a href="<?= $link_survey ?? '#' ?>" style="background:#007bff;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;">Isi Survei Sekarang</a>
    <p>Terima kasih atas kontribusi Anda.</p>
</body>
</html>
