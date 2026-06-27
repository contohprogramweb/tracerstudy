-- Migration untuk fitur Export Belmawa
-- Tabel jobs untuk tracking background processing

CREATE TABLE IF NOT EXISTS `jobs` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `job_type` VARCHAR(50) NOT NULL COMMENT 'export_belmawa, calculate_iku, etc',
  `kohort_id` INT(11) DEFAULT NULL,
  `prodi_id` INT(11) DEFAULT NULL,
  `filename` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
  `progress` TINYINT(3) DEFAULT 0 COMMENT '0-100',
  `result_message` TEXT DEFAULT NULL,
  `retry_count` TINYINT(3) DEFAULT 0,
  `created_at` DATETIME NOT NULL,
  `completed_at` DATETIME DEFAULT NULL,
  `user_id` INT(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_job_type` (`job_type`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel export_logs untuk audit trail (BR-IKU-006: Immutable)

CREATE TABLE IF NOT EXISTS `export_logs` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `kohort_id` INT(11) NOT NULL,
  `prodi_id` INT(11) NOT NULL,
  `filename` VARCHAR(255) NOT NULL,
  `status` ENUM('generated', 'sent_to_belmawa', 'rejected') DEFAULT 'generated',
  `sent_at` DATETIME DEFAULT NULL,
  `sent_by` INT(11) DEFAULT NULL,
  `belmawa_feedback` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_kohort_prodi_sent` (`kohort_id`, `prodi_id`, `status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel download_logs untuk audit trail download (BR-IKU-006)

CREATE TABLE IF NOT EXISTS `download_logs` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `filename` VARCHAR(255) NOT NULL,
  `user_id` INT(11) NOT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `downloaded_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_downloaded_at` (`downloaded_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tambah kolom feedback di tabel tracer untuk import feedback Belmawa

ALTER TABLE `tracer` 
ADD COLUMN `belmawa_feedback` TEXT DEFAULT NULL AFTER `updated_at`,
ADD COLUMN `imported_from_belmawa` TINYINT(1) DEFAULT 0 COMMENT 'Flag data imported from belmawa feedback';

-- Insert default roles jika belum ada (untuk BR-IKU-005)

INSERT INTO `roles` (`name`, `description`, `created_at`) 
VALUES 
  ('admin_pusat_karir', 'Administrator Pusat Karir - Dapat export data Belmawa', NOW())
ON DUPLICATE KEY UPDATE `description` = VALUES(`description`);

