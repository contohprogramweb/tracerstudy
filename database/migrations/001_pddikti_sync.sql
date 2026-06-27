-- ============================================
-- PDDikti Sync Database Schema
-- ============================================
-- This migration creates tables for sync job management
-- and sync operation logging

-- --------------------------------------------
-- Table: sync_jobs
-- Purpose: Store and track synchronization jobs
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS `sync_jobs` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` VARCHAR(50) NOT NULL COMMENT 'Job type: pddikti, import, export',
  `status` ENUM('pending', 'queued', 'processing', 'success', 'partial', 'failed', 'cancelled') NOT NULL DEFAULT 'pending',
  `payload` TEXT NOT NULL COMMENT 'JSON encoded job parameters',
  `result` TEXT NULL COMMENT 'JSON encoded job result',
  `retry_count` TINYINT(3) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL,
  `started_at` DATETIME NULL,
  `completed_at` DATETIME NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_type` (`type`),
  INDEX `idx_status` (`status`),
  INDEX `idx_created_at` (`created_at`),
  INDEX `idx_completed_at` (`completed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------
-- Table: sync_logs
-- Purpose: Log all synchronization operations
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS `sync_logs` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `sync_type` VARCHAR(50) NOT NULL COMMENT 'Type of sync: pddikti, manual_import, etc',
  `sync_date` DATETIME NOT NULL,
  `tahun_lulus` INT(4) NULL COMMENT 'Graduation year being synced',
  `prodi_id` VARCHAR(50) NULL COMMENT 'Study program ID filter',
  `fetched_count` INT(11) UNSIGNED NOT NULL DEFAULT 0,
  `inserted_count` INT(11) UNSIGNED NOT NULL DEFAULT 0,
  `updated_count` INT(11) UNSIGNED NOT NULL DEFAULT 0,
  `skipped_count` INT(11) UNSIGNED NOT NULL DEFAULT 0,
  `failed_count` INT(11) UNSIGNED NOT NULL DEFAULT 0,
  `errors` TEXT NULL COMMENT 'JSON encoded error details',
  `status` ENUM('success', 'partial', 'failed') NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_sync_type` (`sync_type`),
  INDEX `idx_sync_date` (`sync_date`),
  INDEX `idx_tahun_lulus` (`tahun_lulus`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------
-- Table: config
-- Purpose: Store application configuration including API keys
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS `config` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `config_key` VARCHAR(100) NOT NULL,
  `config_value` TEXT NOT NULL,
  `description` VARCHAR(255) NULL,
  `is_encrypted` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_config_key` (`config_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------
-- Insert default PDDikti configuration
-- --------------------------------------------
INSERT INTO `config` (`config_key`, `config_value`, `description`, `is_encrypted`, `created_at`, `updated_at`)
VALUES 
  ('pddikti_api_url', 'https://neo.feeder.kemdikbud.go.id/rest', 'PDDikti NeoFeeder API URL', 0, NOW(), NOW()),
  ('pddikti_api_key', '', 'PDDikti API Key - UPDATE THIS', 1, NOW(), NOW()),
  ('pddikti_api_secret', '', 'PDDikti API Secret - UPDATE THIS', 1, NOW(), NOW()),
  ('pddikti_api_last_rotation', NULL, 'Last API key rotation timestamp', 0, NOW(), NOW())
ON DUPLICATE KEY UPDATE 
  `updated_at` = NOW();

-- --------------------------------------------
-- Add columns to alumni table for PDDikti tracking
-- --------------------------------------------
ALTER TABLE `alumni` 
ADD COLUMN IF NOT EXISTS `sumber_data` ENUM('manual', 'pddikti', 'import') DEFAULT 'manual' AFTER `status_aktif`,
ADD COLUMN IF NOT EXISTS `last_sync` DATETIME NULL AFTER `sumber_data`,
ADD COLUMN IF NOT EXISTS `verified_pddikti` TINYINT(1) NOT NULL DEFAULT 0 AFTER `last_sync`;

CREATE INDEX IF NOT EXISTS `idx_sumber_data` ON `alumni` (`sumber_data`);
CREATE INDEX IF NOT EXISTS `idx_verified_pddikti` ON `alumni` (`verified_pddikti`);

-- --------------------------------------------
-- End of Migration
-- --------------------------------------------
