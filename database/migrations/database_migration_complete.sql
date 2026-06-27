-- Migration Database untuk Modul Kurikulum, CPL, dan Notifikasi
-- Jalankan file ini di database MySQL Anda

-- Tabel Kurikulum Mata Kuliah
CREATE TABLE IF NOT EXISTS `kurikulum_mata_kuliah` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prodi_id` int(11) NOT NULL,
  `tahun_kurikulum` year NOT NULL,
  `semester` tinyint NOT NULL COMMENT '1-8',
  `kode_mk` varchar(20) NOT NULL,
  `nama_mk` varchar(255) NOT NULL,
  `sks` tinyint NOT NULL DEFAULT 3,
  `jenis` enum('Wajib','Pilihan','Wajib Prodi','Wajib Universitas') DEFAULT 'Wajib',
  `cpl_related` text COMMENT 'JSON array of CPL IDs',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `prodi_tahun` (`prodi_id`, `tahun_kurikulum`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel CPL (Capaian Pembelajaran Lulusan)
CREATE TABLE IF NOT EXISTS `cpl` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prodi_id` int(11) NOT NULL,
  `kode_cpl` varchar(20) NOT NULL,
  `deskripsi` text NOT NULL,
  `aspect` enum('Sikap','Pengetahuan','Keterampilan Umum','Keterampilan Khusus') NOT NULL,
  `target_industri` decimal(3,2) DEFAULT 4.00 COMMENT 'Target skor dari industri (max 5)',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `prodi_aspect` (`prodi_id`, `aspect`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Mapping CPL ke SN-Dikti dan KKNI
CREATE TABLE IF NOT EXISTS `cpl_mapping` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cpl_id` int(11) NOT NULL,
  `type` enum('SN_DIPTI','KKNI') NOT NULL,
  `code` varchar(50) DEFAULT NULL COMMENT 'Kode SN-Dikti',
  `level` tinyint DEFAULT NULL COMMENT 'Level KKNI (6-9)',
  `description` text,
  `descriptor` text COMMENT 'Deskripsi KKNI',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `cpl_id` (`cpl_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Detail Survey Alumni (untuk penilaian CPL)
CREATE TABLE IF NOT EXISTS `tracer_survey_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `survey_id` int(11) NOT NULL,
  `cpl_id` int(11) NOT NULL,
  `rating` tinyint NOT NULL COMMENT '1-5',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `survey_cpl` (`survey_id`, `cpl_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Detail Survey Stakeholder (untuk penilaian CPL)
CREATE TABLE IF NOT EXISTS `stakeholder_survey_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `survey_id` int(11) NOT NULL,
  `cpl_id` int(11) NOT NULL,
  `rating` tinyint NOT NULL COMMENT '1-5',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `survey_cpl` (`survey_id`, `cpl_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Pengaturan Notifikasi per User
CREATE TABLE IF NOT EXISTS `notification_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `channel_email` tinyint(1) DEFAULT 1,
  `channel_whatsapp` tinyint(1) DEFAULT 0,
  `channel_telegram` tinyint(1) DEFAULT 0,
  `reminder_survey` tinyint(1) DEFAULT 1,
  `reminder_verifikasi` tinyint(1) DEFAULT 1,
  `alert_iku` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Antrian Notifikasi
CREATE TABLE IF NOT EXISTS `notification_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `recipient` varchar(255) NOT NULL,
  `channel` enum('email','whatsapp','telegram') NOT NULL,
  `type` varchar(50) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `attachment_path` varchar(255) DEFAULT NULL,
  `status` enum('pending','sent','failed','retry') DEFAULT 'pending',
  `retry_count` int(11) DEFAULT 0,
  `max_retry` int(11) DEFAULT 3,
  `scheduled_at` datetime DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `read_at` datetime DEFAULT NULL,
  `error_message` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `status_schedule` (`status`, `scheduled_at`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Log Notifikasi
CREATE TABLE IF NOT EXISTS `notification_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `queue_id` int(11) DEFAULT NULL,
  `channel` varchar(20) NOT NULL,
  `recipient` varchar(255) NOT NULL,
  `status` varchar(20) NOT NULL,
  `message` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `channel` (`channel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- View untuk Monitoring Gap CPL
CREATE OR REPLACE VIEW `v_cpl_gap_monitoring` AS
SELECT 
    c.id as cpl_id,
    c.kode_cpl,
    c.deskripsi,
    c.aspect,
    c.target_industri,
    COALESCE(AVG(ts.rating), 0) as avg_alumni_rating,
    COALESCE(AVG(ss.rating), 0) as avg_stakeholder_rating,
    (COALESCE(AVG(ss.rating), 0) * 0.6 + COALESCE(AVG(ts.rating), 0) * 0.4) as combined_score,
    (c.target_industri - (COALESCE(AVG(ss.rating), 0) * 0.6 + COALESCE(AVG(ts.rating), 0) * 0.4)) as gap
FROM cpl c
LEFT JOIN tracer_survey_details ts ON c.id = ts.cpl_id
LEFT JOIN stakeholder_survey_details ss ON c.id = ss.cpl_id
GROUP BY c.id;
