-- Migration untuk Modul Kurikulum dan CPL
-- Dibuat untuk mendukung manajemen kurikulum, CPL, dan gap analysis

-- Tabel kurikulum_mata_kuliah
CREATE TABLE IF NOT EXISTS kurikulum_mata_kuliah (
    id INT AUTO_INCREMENT PRIMARY KEY,
    prodi_id INT NOT NULL,
    tahun_kurikulum YEAR NOT NULL,
    semester TINYINT NOT NULL CHECK (semester BETWEEN 1 AND 14),
    kode_mk VARCHAR(20) NOT NULL,
    nama_mk VARCHAR(255) NOT NULL,
    sks TINYINT NOT NULL CHECK (sks > 0),
    jenis ENUM('wajib', 'pilihan', 'lintas_minat') DEFAULT 'wajib',
    cpl_related JSON COMMENT 'Array kode CPL yang terkait',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    updated_by INT,
    INDEX idx_prodi_tahun (prodi_id, tahun_kurikulum),
    INDEX idx_semester (semester),
    INDEX idx_kode_mk (kode_mk)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel cpl (Capaian Pembelajaran Lulusan)
CREATE TABLE IF NOT EXISTS cpl (
    id INT AUTO_INCREMENT PRIMARY KEY,
    prodi_id INT NOT NULL,
    kode_cpl VARCHAR(20) NOT NULL,
    deskripsi TEXT NOT NULL,
    aspect ENUM('sikap', 'pengetahuan', 'keterampilan_umum', 'keterampilan_khusus') NOT NULL,
    target_industri DECIMAL(3,2) DEFAULT 4.00 CHECK (target_industri BETWEEN 1 AND 5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    updated_by INT,
    UNIQUE KEY unique_kode_cpl (prodi_id, kode_cpl),
    INDEX idx_aspect (aspect),
    FOREIGN KEY (prodi_id) REFERENCES study_programs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel cpl_mapping (Pemetaan CPL ke SN-Dikti dan KKNI)
CREATE TABLE IF NOT EXISTS cpl_mapping (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cpl_id INT NOT NULL,
    type ENUM('SN_DIPTI', 'KKNI') NOT NULL,
    code VARCHAR(50) NULL COMMENT 'Kode SN-Dikti',
    level TINYINT NULL COMMENT 'Level KKNI (5-9)',
    description TEXT NULL COMMENT 'Deskripsi untuk SN-Dikti',
    descriptor TEXT NULL COMMENT 'Descriptor untuk KKNI',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT,
    INDEX idx_cpl (cpl_id),
    INDEX idx_type (type),
    FOREIGN KEY (cpl_id) REFERENCES cpl(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data untuk testing (opsional)
-- INSERT INTO cpl (prodi_id, kode_cpl, deskripsi, aspect, target_industri) VALUES
-- (1, 'CPL-01', 'Mampu menerapkan pemikiran logis, kritis, sistematis, dan inovatif dalam konteks pengembangan atau implementasi ilmu pengetahuan dan teknologi', 'sikap', 4.5),
-- (1, 'CPL-02', 'Mampu menunjukkan kinerja mandiri, bermutu, dan terukur', 'sikap', 4.0),
-- (1, 'CPL-03', 'Menguasai konsep teoritis bidang pengetahuan tertentu secara umum dan konsep teoritis bagian khusus dalam bidang pengetahuan tersebut secara mendalam', 'pengetahuan', 4.5),
-- (1, 'CPL-04', 'Mampu mengambil keputusan yang tepat berdasarkan analisis informasi dan data', 'keterampilan_umum', 4.0),
-- (1, 'CPL-05', 'Mampu merancang solusi teknis berdasarkan analisis kebutuhan', 'keterampilan_khusus', 4.5);

-- View untuk summary kurikulum per prodi
CREATE OR REPLACE VIEW v_kurikulum_summary AS
SELECT 
    prodi_id,
    tahun_kurikulum,
    COUNT(*) as total_mk,
    SUM(sks) as total_sks,
    AVG(sks) as avg_sks,
    SUM(CASE WHEN jenis = 'wajib' THEN 1 ELSE 0 END) as mk_wajib,
    SUM(CASE WHEN jenis = 'pilihan' THEN 1 ELSE 0 END) as mk_pilihan
FROM kurikulum_mata_kuliah
GROUP BY prodi_id, tahun_kurikulum;

-- View untuk monitoring gap CPL
CREATE OR REPLACE VIEW v_cpl_gap_monitoring AS
SELECT 
    c.id as cpl_id,
    c.kode_cpl,
    c.aspect,
    c.target_industri,
    COUNT(DISTINCT cm.id) as total_mappings,
    MAX(CASE WHEN cm.type = 'SN_DIPTI' THEN 1 ELSE 0 END) as has_sn_dikti,
    MAX(CASE WHEN cm.type = 'KKNI' THEN 1 ELSE 0 END) as has_kkni
FROM cpl c
LEFT JOIN cpl_mapping cm ON c.id = cm.cpl_id
GROUP BY c.id, c.kode_cpl, c.aspect, c.target_industri;

COMMENT ON TABLE kurikulum_mata_kuliah IS 'Menyimpan data mata kuliah per kurikulum prodi';
COMMENT ON TABLE cpl IS 'Capaian Pembelajaran Lulusan per program studi';
COMMENT ON TABLE cpl_mapping IS 'Pemetaan CPL ke standar nasional (SN-Dikti, KKNI) - KUR-006';
