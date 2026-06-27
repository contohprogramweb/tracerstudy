<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Data IKU - Belmawa</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        @media print {
            .no-print { display: none; }
        }
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 30px; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td, .info-table th { border: 1px solid #000; padding: 8px; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table td, .data-table th { border: 1px solid #000; padding: 6px; font-size: 12px; }
        .watermark { 
            position: fixed; 
            top: 50%; 
            left: 50%; 
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 100px;
            color: rgba(200, 200, 200, 0.3);
            z-index: -1;
        }
    </style>
</head>
<body>
    <div class="watermark">IMMUTABLE</div>
    
    <div class="container mt-4">
        <!-- Header -->
        <div class="header no-print">
            <h3>LAPORAN INDIKATOR KINERJA UTAMA (IKU)</h3>
            <h5>Format Export Belmawa</h5>
        </div>

        <!-- Info Table -->
        <table class="info-table">
            <tr>
                <th width="30%">Institusi</th>
                <td>: <?= $export_data['institution_code'] ?: '(Diisi sesuai kode institusi)' ?></td>
            </tr>
            <tr>
                <th>Program Studi</th>
                <td>: <?= $export_data['study_program_code'] ?: '(Diisi sesuai kode prodi)' ?></td>
            </tr>
            <tr>
                <th>Tahun Akademik</th>
                <td>: <?= $export_data['academic_year'] ?></td>
            </tr>
            <tr>
                <th>Indikator</th>
                <td>: IKU <?= $export_data['iku_number'] ?> - Lulusan Mendapat Pekerjaan yang Layak</td>
            </tr>
            <tr>
                <th>Persentase</th>
                <td>: <strong><?= $export_data['percentage'] ?>%</strong></td>
            </tr>
            <tr>
                <th>Status Capaian</th>
                <td>: <?= $export_data['achievement_status'] ?></td>
            </tr>
            <tr>
                <th>Tanggal Perhitungan</th>
                <td>: <?= date('d/m/Y H:i', strtotime($export_data['calculation_date'])) ?></td>
            </tr>
            <?php if ($immutable): ?>
            <tr>
                <th>Status Data</th>
                <td>: <strong style="color: red;">IMMUTABLE (Tidak dapat diubah)</strong></td>
            </tr>
            <?php endif; ?>
        </table>

        <!-- Calculation Details -->
        <h5 class="mt-4">Detail Perhitungan</h5>
        <table class="data-table mb-4">
            <thead>
                <tr class="table-light">
                    <th>Komponen</th>
                    <th>Nilai</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Numerator (Σ Bobot)</td>
                    <td><?= $export_data['numerator'] ?></td>
                    <td>Total bobot dari semua responden</td>
                </tr>
                <tr>
                    <td>Denominator (Total Responden)</td>
                    <td><?= $export_data['denominator'] ?></td>
                    <td>Jumlah alumni responden</td>
                </tr>
                <tr>
                    <td>Rumus</td>
                    <td colspan="2">(Numerator / Denominator) × 100</td>
                </tr>
                <tr>
                    <td>Hasil</td>
                    <td><strong><?= $export_data['percentage'] ?>%</strong></td>
                    <td>Target: <?= $export_data['target'] ?>%</td>
                </tr>
            </tbody>
        </table>

        <!-- Formula Explanation -->
        <h5 class="mt-4">Formula Bobot per Alumni</h5>
        <table class="data-table mb-4">
            <thead>
                <tr class="table-light">
                    <th>Status</th>
                    <th>Kondisi</th>
                    <th>Bobot</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td rowspan="4">Bekerja</td>
                    <td>Gaji ≥ 1.2 UMP + Masa tunggu ≤ 6 bulan</td>
                    <td>1.0</td>
                </tr>
                <tr>
                    <td>Gaji ≥ 1.2 UMP + Masa tunggu 7-12 bulan</td>
                    <td>0.8</td>
                </tr>
                <tr>
                    <td>Gaji < 1.2 UMP + Masa tunggu ≤ 6 bulan</td>
                    <td>0.8</td>
                </tr>
                <tr>
                    <td>Gaji < 1.2 UMP + Masa tunggu 7-12 bulan</td>
                    <td>0.6</td>
                </tr>
                <tr>
                    <td rowspan="2">Wirausaha</td>
                    <td>Omzet ≥ UMP</td>
                    <td>1.0</td>
                </tr>
                <tr>
                    <td>Omzet < UMP</td>
                    <td>0.8</td>
                </tr>
                <tr>
                    <td>Lanjut Studi</td>
                    <td>-</td>
                    <td>0.6</td>
                </tr>
                <tr>
                    <td>Belum Bekerja</td>
                    <td>-</td>
                    <td>0</td>
                </tr>
            </tbody>
        </table>

        <!-- Business Rules Applied -->
        <h5 class="mt-4">Business Rules yang Diterapkan</h5>
        <ul class="small">
            <li><strong>BR-IKU-001:</strong> IKU hanya valid jika response rate ≥ 30%</li>
            <li><strong>BR-IKU-002:</strong> Sumber kebenaran gaji_aktual > gaji_range</li>
            <li><strong>BR-IKU-003:</strong> V&V rate < 80% = pengurangan skor 20%</li>
            <li><strong>BR-IKU-004:</strong> Auto-calc daily 02:00 WIB + real-time saat update</li>
            <li><strong>BR-IKU-006:</strong> Data sudah dikirim immutable</li>
            <li><strong>BR-IKU-007:</strong> UMP provinsi domisili alumni</li>
            <li><strong>BR-SUR-008:</strong> Prioritas bekerja > wirausaha > studi > belum</li>
        </ul>

        <!-- Footer -->
        <div class="mt-5 pt-4 border-top">
            <div class="row">
                <div class="col-6 text-left">
                    <p>Dicetak pada: <?= $generated_at ?> WIB</p>
                </div>
                <div class="col-6 text-right">
                    <p>Admin Pusat Karir / Super Admin</p>
                </div>
            </div>
        </div>

        <!-- Action Buttons (No Print) -->
        <div class="mt-4 no-print">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print"></i> Cetak / Simpan PDF
            </button>
            <a href="<?= site_url('iku/dashboard') ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <button onclick="downloadCSV()" class="btn btn-success">
                <i class="fas fa-file-csv"></i> Download CSV
            </button>
        </div>
    </div>

    <script>
        function downloadCSV() {
            const data = [
                ['Institusi', '<?= $export_data['institution_code'] ?>'],
                ['Program Studi', '<?= $export_data['study_program_code'] ?>'],
                ['Tahun Akademik', '<?= $export_data['academic_year'] ?>'],
                ['IKU Number', '<?= $export_data['iku_number'] ?>'],
                ['Numerator', '<?= $export_data['numerator'] ?>'],
                ['Denominator', '<?= $export_data['denominator'] ?>'],
                ['Percentage', '<?= $export_data['percentage'] ?>'],
                ['Target', '<?= $export_data['target'] ?>'],
                ['Status', '<?= $export_data['achievement_status'] ?>'],
                ['Calculation Date', '<?= $export_data['calculation_date'] ?>']
            ];

            let csvContent = "data:text/csv;charset=utf-8,";
            data.forEach(row => {
                csvContent += row.join(",") + "\n";
            });

            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "iku_export_<?= $export_data['academic_year'] ?>.csv");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
</body>
</html>
