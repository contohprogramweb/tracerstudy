<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @media print {
            .no-print, .btn-export { display: none !important; }
            body { background: white; }
            table { font-size: 11px; }
        }
        
        .filter-section {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .card {
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .card-header {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            font-weight: 600;
        }
        
        .banpt-section {
            border-left: 4px solid #3498db;
            padding-left: 15px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="container-fluid mt-4">
    <div class="row mb-4 no-print">
        <div class="col-md-12">
            <h2><i class="fa fa-university"></i> <?= $title ?></h2>
            <p class="text-muted">Laporan Evaluasi Diri untuk Akreditasi BAN-PT Kriteria 9 (Luaran dan Capaian Tridharma)</p>
        </div>
    </div>

    <!-- Filter -->
    <div class="row mb-4 no-print">
        <div class="col-md-12">
            <form method="GET" class="filter-section">
                <div class="form-row align-items-end">
                    <div class="col-md-2">
                        <label>Tahun</label>
                        <select name="tahun" class="form-control">
                            <?php for($y=date('Y'); $y>=date('Y')-5; $y--): ?>
                                <option value="<?= $y ?>" <?= ($filter['tahun'] ?? date('Y')) == $y ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Program Studi</label>
                        <select name="prodi_id" class="form-control">
                            <option value="">Semua Program Studi</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fa fa-filter"></i> Filter
                        </button>
                    </div>
                    <div class="col-md-5 text-right">
                        <button type="button" onclick="window.print()" class="btn btn-secondary btn-export">
                            <i class="fa fa-print"></i> Cetak
                        </button>
                        <a href="<?= site_url('laporan/exportPdf/banpt?' . http_build_query($filter)) ?>" class="btn btn-danger btn-export">
                            <i class="fa fa-file-pdf"></i> PDF
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Kriteria 9.1: IPK Lulusan -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    9.1 - IPK Lulusan
                </div>
                <div class="card-body">
                    <div class="banpt-section">
                        <h5>Statistik IPK Lulusan (<?= $filter['tahun'] ?? date('Y') ?>)</h5>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Program Studi</th>
                                    <th>Jumlah Lulusan</th>
                                    <th>IPK Rata-rata</th>
                                    <th>IPK Tertinggi</th>
                                    <th>IPK Terendah</th>
                                    <th>Lulus dengan Pujian (Cum Laude)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($kriteria_9['ipk_by_prodi'])): ?>
                                    <?php foreach($kriteria_9['ipk_by_prodi'] as $row): ?>
                                    <tr>
                                        <td><?= $row['prodi'] ?></td>
                                        <td><?= number_format($row['jumlah']) ?></td>
                                        <td><?= number_format($row['avg_ipk'], 2) ?></td>
                                        <td><?= number_format($row['max_ipk'], 2) ?></td>
                                        <td><?= number_format($row['min_ipk'], 2) ?></td>
                                        <td><?= number_format($row['cumlaude_count']) ?> (<?= number_format($row['cumlaude_pct'], 1) ?>%)</td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="6" class="text-center">Data tidak tersedia</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Kriteria 9.2: Waktu Tunggu -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    9.2 - Waktu Tunggu Mendapatkan Pekerjaan Pertama
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Program Studi</th>
                                <th>Rata-rata Waktu Tunggu (Bulan)</th>
                                <th>Median (Bulan)</th>
                                <th>% Bekerja < 6 Bulan</th>
                                <th>% Bekerja 6-12 Bulan</th>
                                <th>% Bekerja > 12 Bulan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($kriteria_9['wait_time_by_prodi'])): ?>
                                <?php foreach($kriteria_9['wait_time_by_prodi'] as $row): ?>
                                <tr>
                                    <td><?= $row['prodi'] ?></td>
                                    <td><?= number_format($row['avg_wait'], 1) ?></td>
                                    <td><?= number_format($row['median_wait'], 1) ?></td>
                                    <td><?= number_format($row['pct_under_6m'], 1) ?>%</td>
                                    <td><?= number_format($row['pct_6_12m'], 1) ?>%</td>
                                    <td><?= number_format($row['pct_over_12m'], 1) ?>%</td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center">Data tidak tersedia</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Kriteria 9.3: Kesesuaian Bidang Kerja -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    9.3 - Kesesuaian Bidang Kerja dengan Bidang Studi
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Program Studi</th>
                                <th>Sangat Sesuai</th>
                                <th>Sesuai</th>
                                <th>Kurang Sesuai</th>
                                <th>Tidak Sesuai</th>
                                <th>Total Bekerja</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($kesesuaian_bidang)): ?>
                                <?php foreach($kesesuaian_bidang as $row): ?>
                                <tr>
                                    <td><?= $row['prodi'] ?></td>
                                    <td><?= number_format($row['sangat_sesuai']) ?> (<?= number_format($row['pct_sangat'], 1) ?>%)</td>
                                    <td><?= number_format($row['sesuai']) ?> (<?= number_format($row['pct_sesuai'], 1) ?>%)</td>
                                    <td><?= number_format($row['kurang']) ?> (<?= number_format($row['pct_kurang'], 1) ?>%)</td>
                                    <td><?= number_format($row['tidak']) ?> (<?= number_format($row['pct_tidak'], 1) ?>%)</td>
                                    <td><?= number_format($row['total']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center">Data tidak tersedia</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Kriteria 9.4: Prestasi Mahasiswa -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    9.4 - Prestasi/Penghargaan Mahasiswa (Tingkat Nasional & Internasional)
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Mahasiswa</th>
                                <th>Program Studi</th>
                                <th>Jenis Prestasi</th>
                                <th>Tingkat</th>
                                <th>Tahun</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($mahasiswa_prestasi)): ?>
                                <?php foreach($mahasiswa_prestasi as $idx => $row): ?>
                                <tr>
                                    <td><?= $idx + 1 ?></td>
                                    <td><?= $row['nama'] ?></td>
                                    <td><?= $row['prodi'] ?></td>
                                    <td><?= $row['jenis_prestasi'] ?></td>
                                    <td>
                                        <?php if($row['tingkat'] == 'internasional'): ?>
                                            <span class="badge badge-danger">Internasional</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">Nasional</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $row['tahun'] ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center">Tidak ada prestasi yang tercatat</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Kriteria 9.5: Tinjauan Pembelajaran -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    9.5 - Tinjauan Pembelajaran (Review of Learning)
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <strong>Indikator:</strong> Adanya mekanisme tinjauan pembelajaran secara berkala untuk memastikan kesesuaian proses pembelajaran dengan kebutuhan stakeholder.
                    </div>
                    
                    <h6>Mekanisme Tinjauan Pembelajaran:</h6>
                    <ul>
                        <li>Survey kepuasan mahasiswa setiap semester</li>
                        <li>Tracer study tahunan terhadap alumni</li>
                        <li>Survey kepuasan pengguna lulusan (stakeholder)</li>
                        <li>Focus Group Discussion (FGD) dengan industri</li>
                        <li>Review kurikulum setiap 2-4 tahun</li>
                    </ul>
                    
                    <h6 class="mt-4">Hasil Tinjauan Terakhir:</h6>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Aspek</th>
                                <th>Skor Kepuasan</th>
                                <th>Tindak Lanjut</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Kesesuaian Materi dengan Kebutuhan Industri</td>
                                <td>4.2 / 5.0</td>
                                <td>Revisi RPS mata kuliah inti</td>
                                <td><span class="badge badge-success">Selesai</span></td>
                            </tr>
                            <tr>
                                <td>Ketersediaan Fasilitas Praktikum</td>
                                <td>3.8 / 5.0</td>
                                <td>Pengadaan laboratorium baru</td>
                                <td><span class="badge badge-warning">Proses</span></td>
                            </tr>
                            <tr>
                                <td>Kompetensi Dosen</td>
                                <td>4.5 / 5.0</td>
                                <td>Program sertifikasi dosen</td>
                                <td><span class="badge badge-success">Selesai</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
