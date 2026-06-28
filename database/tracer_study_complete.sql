-- ============================================================================
-- DATABASE: Tracer Study Perguruan Tinggi v3.1 - COMPLETE SCHEMA
-- COMPATIBILITY: MySQL 5.7+ / MariaDB 10.2+
-- DESCRIPTION: Full schema with HMVC support, IKU calculations, Survey Builder
-- VERSION: 3.1.0
-- AUTHOR: Generated for SRS Tracer Study v3.1
-- USAGE: Import this single file to create complete database with sample data
-- ============================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;

-- Create Database
CREATE DATABASE IF NOT EXISTS `tracer_study_v31` 
DEFAULT CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `tracer_study_v31`;

-- ============================================================================
-- 1. MASTER DATA: Fakultas & Program Studi
-- ============================================================================

CREATE TABLE `fakultas` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `kode` VARCHAR(20) NOT NULL UNIQUE,
  `nama` VARCHAR(100) NOT NULL,
  `dekan` VARCHAR(100) DEFAULT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `program_studi` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `fakultas_id` INT UNSIGNED NOT NULL,
  `kode` VARCHAR(20) NOT NULL,
  `nama` VARCHAR(150) NOT NULL,
  `jenjang` ENUM('D3', 'D4', 'S1', 'S2', 'S3') NOT NULL,
  `akreditasi` ENUM('A', 'B', 'C', 'Unggul', 'Baik Sekali', 'Baik', 'Tidak Terakreditasi') DEFAULT NULL,
  `tahun_akreditasi` YEAR DEFAULT NULL,
  `ketua_prodi` VARCHAR(100) DEFAULT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `website` VARCHAR(255) DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  CONSTRAINT `fk_prodi_fakultas` 
    FOREIGN KEY (`fakultas_id`) REFERENCES `fakultas`(`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE,
  
  UNIQUE KEY `uk_prodi_kode` (`kode`, `jenjang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX `idx_prodi_fakultas` ON `program_studi`(`fakultas_id`);
CREATE INDEX `idx_prodi_jenjang` ON `program_studi`(`jenjang`);

-- ============================================================================
-- 2. AUTHENTICATION & USERS
-- ============================================================================

CREATE TABLE `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `role` ENUM('super_admin', 'admin', 'prodi_admin', 'staff', 'alumni', 'stakeholder') NOT NULL DEFAULT 'alumni',
  `prodi_id` INT UNSIGNED DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `is_verified` TINYINT(1) DEFAULT 0,
  `last_login` DATETIME DEFAULT NULL,
  `password_changed_at` DATETIME DEFAULT NULL,
  `must_change_password` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  CONSTRAINT `fk_user_prodi` 
    FOREIGN KEY (`prodi_id`) REFERENCES `program_studi`(`id`) 
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX `idx_users_role` ON `users`(`role`);
CREATE INDEX `idx_users_email` ON `users`(`email`);
CREATE INDEX `idx_users_active` ON `users`(`is_active`);

CREATE TABLE `login_attempts` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `ip_address` VARBINARY(16) NOT NULL,
  `login_type` ENUM('email', 'username') NOT NULL,
  `login_identifier` VARCHAR(100) NOT NULL,
  `user_agent` VARCHAR(255) DEFAULT NULL,
  `attempted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  INDEX `idx_login_ip_time` (`ip_address`, `attempted_at`),
  INDEX `idx_login_identifier` (`login_identifier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `api_keys` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `key_hash` VARCHAR(64) NOT NULL UNIQUE,
  `name` VARCHAR(100) NOT NULL,
  `scopes` JSON NOT NULL COMMENT 'Array of allowed scopes e.g. ["read:alumni", "write:survey"]',
  `expires_at` DATETIME DEFAULT NULL,
  `last_used_at` DATETIME DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  CONSTRAINT `fk_apikey_user` 
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE,
  
  INDEX `idx_apikey_hash` (`key_hash`),
  INDEX `idx_apikey_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 3. ALUMNI DATA (With PII Encryption Fields)
-- ============================================================================

CREATE TABLE `kohorts` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `prodi_id` INT UNSIGNED NOT NULL,
  `tahun_masuk` YEAR NOT NULL,
  `tahun_lulus` YEAR NOT NULL,
  `nama_kohort` VARCHAR(50) NOT NULL,
  `jumlah_mahasiswa` INT UNSIGNED DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  CONSTRAINT `fk_kohort_prodi` 
    FOREIGN KEY (`prodi_id`) REFERENCES `program_studi`(`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE,
  
  UNIQUE KEY `uk_kohort_prodi_tahun` (`prodi_id`, `tahun_masuk`, `tahun_lulus`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX `idx_kohort_tahun` ON `kohorts`(`tahun_lulus`);

CREATE TABLE `alumni` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `prodi_id` INT UNSIGNED NOT NULL,
  `kohort_id` INT UNSIGNED DEFAULT NULL,
  `nim` VARCHAR(20) NOT NULL,
  `nama_lengkap` VARCHAR(150) NOT NULL,
  
  -- Encrypted PII Fields (Store AES-256 encrypted strings)
  `nik_encrypted` VARBINARY(255) DEFAULT NULL COMMENT 'AES encrypted NIK',
  `tempat_lahir` VARCHAR(100) DEFAULT NULL,
  `tanggal_lahir` DATE DEFAULT NULL,
  `jenis_kelamin` ENUM('L', 'P') DEFAULT NULL,
  `agama` VARCHAR(50) DEFAULT NULL,
  
  -- Contact Info
  `email_pribadi` VARCHAR(100) DEFAULT NULL,
  `no_hp` VARCHAR(20) DEFAULT NULL,
  `no_hp_whatsapp` VARCHAR(20) DEFAULT NULL,
  `alamat_domisili` TEXT DEFAULT NULL,
  `provinsi_domisili` VARCHAR(100) DEFAULT NULL,
  `kota_domisili` VARCHAR(100) DEFAULT NULL,
  `kode_pos` VARCHAR(10) DEFAULT NULL,
  
  -- Academic Info
  `tanggal_lulus` DATE NOT NULL,
  `ipk` DECIMAL(3,2) DEFAULT NULL,
  `predikat` VARCHAR(50) DEFAULT NULL,
  `judul_skripsi` TEXT DEFAULT NULL,
  
  -- Status
  `is_verified` TINYINT(1) DEFAULT 0,
  `verification_token` VARCHAR(64) DEFAULT NULL,
  `status_tracing` ENUM('belum_responden', 'sudah_responden', 'data_diverifikasi', 'data_ditolak') DEFAULT 'belum_responden',
  
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  CONSTRAINT `fk_alumni_user` 
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) 
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_alumni_prodi` 
    FOREIGN KEY (`prodi_id`) REFERENCES `program_studi`(`id`) 
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_alumni_kohort` 
    FOREIGN KEY (`kohort_id`) REFERENCES `kohorts`(`id`) 
    ON DELETE SET NULL ON UPDATE CASCADE,
  
  UNIQUE KEY `uk_alumni_nim_prodi` (`nim`, `prodi_id`),
  UNIQUE KEY `uk_alumni_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX `idx_alumni_prodi` ON `alumni`(`prodi_id`);
CREATE INDEX `idx_alumni_kohort` ON `alumni`(`kohort_id`);
CREATE INDEX `idx_alumni_status` ON `alumni`(`status_tracing`);
CREATE INDEX `idx_alumni_tahun_lulus` ON `alumni`(`tanggal_lulus`);

-- ============================================================================
-- 4. SURVEY BUILDER MODULE
-- ============================================================================

CREATE TABLE `surveys` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(200) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `type` ENUM('tracer_study', 'stakeholder', 'iku_data', 'custom') NOT NULL DEFAULT 'tracer_study',
  `prodi_id` INT UNSIGNED DEFAULT NULL,
  `tahun_periode` YEAR NOT NULL,
  `start_date` DATETIME NOT NULL,
  `end_date` DATETIME NOT NULL,
  `is_active` TINYINT(1) DEFAULT 0,
  `allow_multiple_responses` TINYINT(1) DEFAULT 0,
  `require_auth` TINYINT(1) DEFAULT 1,
  `show_progress_bar` TINYINT(1) DEFAULT 1,
  `thank_you_message` TEXT DEFAULT NULL,
  `created_by` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  CONSTRAINT `fk_survey_prodi` 
    FOREIGN KEY (`prodi_id`) REFERENCES `program_studi`(`id`) 
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_survey_creator` 
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) 
    ON DELETE RESTRICT ON UPDATE CASCADE,
  
  INDEX `idx_survey_type` (`type`),
  INDEX `idx_survey_period` (`tahun_periode`),
  INDEX `idx_survey_active` (`is_active`, `start_date`, `end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `survey_sections` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `survey_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `order` INT UNSIGNED NOT NULL DEFAULT 0,
  `is_required` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  CONSTRAINT `fk_section_survey` 
    FOREIGN KEY (`survey_id`) REFERENCES `surveys`(`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE,
  
  INDEX `idx_section_order` (`survey_id`, `order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `survey_questions` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `survey_id` INT UNSIGNED NOT NULL,
  `section_id` INT UNSIGNED DEFAULT NULL,
  `parent_question_id` INT UNSIGNED DEFAULT NULL COMMENT 'For matrix/sub-questions',
  `code` VARCHAR(50) DEFAULT NULL COMMENT 'Question code e.g. A1, B2',
  `question_text` TEXT NOT NULL,
  `question_type` ENUM('text', 'textarea', 'number', 'date', 'radio', 'checkbox', 'dropdown', 'matrix', 'file', 'scale_likert') NOT NULL,
  `options` JSON DEFAULT NULL COMMENT 'Options for radio/checkbox/dropdown',
  `is_required` TINYINT(1) DEFAULT 0,
  `is_belma_inti` TINYINT(1) DEFAULT 0 COMMENT 'Flag for BELMA core questions',
  `order` INT UNSIGNED NOT NULL DEFAULT 0,
  `validation_rule` VARCHAR(255) DEFAULT NULL,
  `help_text` TEXT DEFAULT NULL,
  `placeholder` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  CONSTRAINT `fk_question_survey` 
    FOREIGN KEY (`survey_id`) REFERENCES `surveys`(`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_question_section` 
    FOREIGN KEY (`section_id`) REFERENCES `survey_sections`(`id`) 
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_question_parent` 
    FOREIGN KEY (`parent_question_id`) REFERENCES `survey_questions`(`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE,
  
  INDEX `idx_question_survey` (`survey_id`),
  INDEX `idx_question_section` (`section_id`),
  INDEX `idx_question_belma` (`is_belma_inti`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `survey_logic` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `question_id` INT UNSIGNED NOT NULL,
  `condition_field` VARCHAR(50) NOT NULL COMMENT 'Field to check (e.g., answer_value)',
  `condition_operator` ENUM('equals', 'not_equals', 'contains', 'greater_than', 'less_than', 'in_array') NOT NULL,
  `condition_value` VARCHAR(255) NOT NULL COMMENT 'Value to compare',
  `target_question_id` INT UNSIGNED NOT NULL COMMENT 'Jump to this question',
  `logic_type` ENUM('skip_to', 'display_if', 'hide_if') NOT NULL DEFAULT 'skip_to',
  `order` INT UNSIGNED DEFAULT 0,
  
  CONSTRAINT `fk_logic_question` 
    FOREIGN KEY (`question_id`) REFERENCES `survey_questions`(`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_logic_target` 
    FOREIGN KEY (`target_question_id`) REFERENCES `survey_questions`(`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE,
  
  CHECK (`question_id` != `target_question_id`),
  INDEX `idx_logic_question` (`question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `survey_responses` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `survey_id` INT UNSIGNED NOT NULL,
  `alumni_id` INT UNSIGNED DEFAULT NULL,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `stakeholder_id` INT UNSIGNED DEFAULT NULL,
  `response_token` VARCHAR(64) NOT NULL UNIQUE COMMENT 'Public token for anonymous access',
  `ip_address` VARBINARY(16) DEFAULT NULL,
  `user_agent` VARCHAR(255) DEFAULT NULL,
  `started_at` DATETIME DEFAULT NULL,
  `submitted_at` DATETIME DEFAULT NULL,
  `status` ENUM('draft', 'submitted', 'partial') NOT NULL DEFAULT 'draft',
  `completion_percentage` DECIMAL(5,2) DEFAULT 0.00,
  `device_info` JSON DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  CONSTRAINT `fk_response_survey` 
    FOREIGN KEY (`survey_id`) REFERENCES `surveys`(`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_response_alumni` 
    FOREIGN KEY (`alumni_id`) REFERENCES `alumni`(`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_response_user` 
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) 
    ON DELETE SET NULL ON UPDATE CASCADE,
  
  UNIQUE KEY `uk_response_survey_alumni` (`survey_id`, `alumni_id`),
  INDEX `idx_response_token` (`response_token`),
  INDEX `idx_response_status` (`status`),
  INDEX `idx_response_submitted` (`submitted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `survey_answers` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `response_id` BIGINT UNSIGNED NOT NULL,
  `question_id` INT UNSIGNED NOT NULL,
  `answer_value` TEXT DEFAULT NULL COMMENT 'Text/Number/Date answer',
  `answer_array` JSON DEFAULT NULL COMMENT 'Array answers for checkbox/matrix',
  `file_path` VARCHAR(255) DEFAULT NULL COMMENT 'Uploaded file path',
  `answered_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  CONSTRAINT `fk_answer_response` 
    FOREIGN KEY (`response_id`) REFERENCES `survey_responses`(`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_answer_question` 
    FOREIGN KEY (`question_id`) REFERENCES `survey_questions`(`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE,
  
  INDEX `idx_answer_response` (`response_id`),
  INDEX `idx_answer_question` (`question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `survey_progress` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `survey_id` INT UNSIGNED NOT NULL,
  `alumni_id` INT UNSIGNED NOT NULL,
  `answers` TEXT NOT NULL COMMENT 'JSON encoded answers',
  `current_question_id` INT UNSIGNED DEFAULT NULL COMMENT 'Last active question',
  `progress_percent` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  
  CONSTRAINT `fk_progress_survey` 
    FOREIGN KEY (`survey_id`) REFERENCES `surveys`(`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_progress_alumni` 
    FOREIGN KEY (`alumni_id`) REFERENCES `alumni`(`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE,
  
  UNIQUE KEY `uk_progress_survey_alumni` (`survey_id`, `alumni_id`),
  INDEX `idx_progress_updated` (`updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 5. STAKEHOLDER MODULE
-- ============================================================================

CREATE TABLE `stakeholders` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `nama_instansi` VARCHAR(200) NOT NULL,
  `jenis_instansi` ENUM('Pemerintahan', 'Swasta', 'BUMN', 'BUMD', 'LSM', 'Internasional', 'Startup', 'Wirausaha') NOT NULL,
  `bidang_usaha` VARCHAR(100) DEFAULT NULL,
  `alamat` TEXT DEFAULT NULL,
  `provinsi` VARCHAR(100) DEFAULT NULL,
  `kota` VARCHAR(100) DEFAULT NULL,
  `website` VARCHAR(255) DEFAULT NULL,
  `namakontak_pic` VARCHAR(100) DEFAULT NULL,
  `jabatan_pic` VARCHAR(100) DEFAULT NULL,
  `email_pic` VARCHAR(100) DEFAULT NULL,
  `no_hp_pic` VARCHAR(20) DEFAULT NULL,
  `is_verified` TINYINT(1) DEFAULT 0,
  `rating_reputasi` TINYINT UNSIGNED DEFAULT NULL COMMENT '1-5 star rating',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  CONSTRAINT `fk_stakeholder_user` 
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) 
    ON DELETE SET NULL ON UPDATE CASCADE,
  
  INDEX `idx_stakeholder_jenis` (`jenis_instansi`),
  INDEX `idx_stakeholder_provinsi` (`provinsi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `stakeholder_surveys` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `stakeholder_id` INT UNSIGNED NOT NULL,
  `alumni_id` INT UNSIGNED NOT NULL,
  `survey_id` INT UNSIGNED NOT NULL,
  `response_id` BIGINT UNSIGNED DEFAULT NULL,
  `tanggal_penilaian` DATE NOT NULL,
  `masa_kerja_bulan` INT UNSIGNED DEFAULT NULL,
  `status_pekerjaan` ENUM('Tetap', 'Kontrak', 'Freelance', 'Magang') DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  CONSTRAINT `fk_ss_stakeholder` 
    FOREIGN KEY (`stakeholder_id`) REFERENCES `stakeholders`(`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ss_alumni` 
    FOREIGN KEY (`alumni_id`) REFERENCES `alumni`(`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ss_survey` 
    FOREIGN KEY (`survey_id`) REFERENCES `surveys`(`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ss_response` 
    FOREIGN KEY (`response_id`) REFERENCES `survey_responses`(`id`) 
    ON DELETE SET NULL ON UPDATE CASCADE,
  
  UNIQUE KEY `uk_ss_unique` (`stakeholder_id`, `alumni_id`, `survey_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 6. KURIKULUM & CPL
-- ============================================================================

CREATE TABLE `kurikulum` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `prodi_id` INT UNSIGNED NOT NULL,
  `tahun_mulai` YEAR NOT NULL,
  `tahun_selesai` YEAR DEFAULT NULL,
  `nama_kurikulum` VARCHAR(100) NOT NULL,
  `deskripsi` TEXT DEFAULT NULL,
  `total_sks` INT UNSIGNED NOT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  CONSTRAINT `fk_kurikulum_prodi` 
    FOREIGN KEY (`prodi_id`) REFERENCES `program_studi`(`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE,
  
  UNIQUE KEY `uk_kurikulum_prodi_tahun` (`prodi_id`, `tahun_mulai`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cpl` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `kurikulum_id` INT UNSIGNED NOT NULL,
  `kode_cpl` VARCHAR(20) NOT NULL,
  `jenis` ENUM('Sikap', 'Pengetahuan', 'Keterampilan_Umum', 'Keterampilan_Khusus') NOT NULL,
  `deskripsi` TEXT NOT NULL,
  `level` ENUM('1', '2', '3', '4', '5', '6', '7', '8', '9') DEFAULT NULL COMMENT 'KKNI Level',
  `target_industri` DECIMAL(3,2) DEFAULT 4.00,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  CONSTRAINT `fk_cpl_kurikulum` 
    FOREIGN KEY (`kurikulum_id`) REFERENCES `kurikulum`(`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE,
  
  UNIQUE KEY `uk_cpl_kode` (`kurikulum_id`, `kode_cpl`),
  INDEX `idx_cpl_jenis` (`jenis`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cpl_mapping` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `cpl_id` INT UNSIGNED NOT NULL,
  `type` ENUM('SN_DIPTI', 'KKNI') NOT NULL,
  `code` VARCHAR(50) DEFAULT NULL COMMENT 'Kode SN-Dikti',
  `level` TINYINT DEFAULT NULL COMMENT 'Level KKNI (6-9)',
  `description` TEXT DEFAULT NULL,
  `descriptor` TEXT DEFAULT NULL COMMENT 'Deskripsi KKNI',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  CONSTRAINT `fk_cplmap_cpl` 
    FOREIGN KEY (`cpl_id`) REFERENCES `cpl`(`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE,
  
  INDEX `idx_cplmap_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `kurikulum_mata_kuliah` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `prodi_id` INT UNSIGNED NOT NULL,
  `tahun_kurikulum` YEAR NOT NULL,
  `semester` TINYINT NOT NULL,
  `kode_mk` VARCHAR(20) NOT NULL,
  `nama_mk` VARCHAR(255) NOT NULL,
  `sks` TINYINT NOT NULL,
  `jenis` ENUM('wajib', 'pilihan', 'lintas_minat') DEFAULT 'wajib',
  `cpl_related` JSON DEFAULT NULL COMMENT 'Array kode CPL yang terkait',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  INDEX `idx_mk_prodi_tahun` (`prodi_id`, `tahun_kurikulum`),
  INDEX `idx_mk_semester` (`semester`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 7. IKU (Indikator Kinerja Utama) CALCULATIONS
-- ============================================================================

CREATE TABLE `iku_calculations` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `prodi_id` INT UNSIGNED NOT NULL,
  `kohort_id` INT UNSIGNED NOT NULL,
  `tahun_iku` YEAR NOT NULL,
  `iku_number` TINYINT UNSIGNED NOT NULL COMMENT '1-8 for IKU indicators',
  `numerator` INT UNSIGNED NOT NULL DEFAULT 0,
  `denominator` INT UNSIGNED NOT NULL DEFAULT 0,
  `percentage` DECIMAL(5,2) DEFAULT 0.00,
  `target_percentage` DECIMAL(5,2) DEFAULT 0.00,
  `status_capaian` ENUM('Belum', 'Tercapai', 'Melampaui') DEFAULT 'Belum',
  `mapping_data` JSON DEFAULT NULL COMMENT 'Mapping alumni IDs used in calculation',
  `verified_by` INT UNSIGNED DEFAULT NULL,
  `verified_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  CONSTRAINT `fk_iku_prodi` 
    FOREIGN KEY (`prodi_id`) REFERENCES `program_studi`(`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_iku_kohort` 
    FOREIGN KEY (`kohort_id`) REFERENCES `kohorts`(`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_iku_verifier` 
    FOREIGN KEY (`verified_by`) REFERENCES `users`(`id`) 
    ON DELETE SET NULL ON UPDATE CASCADE,
  
  UNIQUE KEY `uk_iku_unique` (`tahun_iku`, `prodi_id`, `kohort_id`, `iku_number`),
  INDEX `idx_iku_tahun` (`tahun_iku`),
  INDEX `idx_iku_number` (`iku_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 8. VERIFIKASI & AUDIT
-- ============================================================================

CREATE TABLE `verifikasi_data` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `alumni_id` INT UNSIGNED NOT NULL,
  `verifikator_id` INT UNSIGNED NOT NULL,
  `jenis_verifikasi` ENUM('data_pribadi', 'pekerjaan', 'kelanjutan_studi', 'wirausaha', 'semua') NOT NULL,
  `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  `catatan` TEXT DEFAULT NULL,
  `bukti_files` JSON DEFAULT NULL COMMENT 'Array of file paths',
  `verified_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  CONSTRAINT `fk_verif_alumni` 
    FOREIGN KEY (`alumni_id`) REFERENCES `alumni`(`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_verif_user` 
    FOREIGN KEY (`verifikator_id`) REFERENCES `users`(`id`) 
    ON DELETE RESTRICT ON UPDATE CASCADE,
  
  INDEX `idx_verif_status` (`status`),
  INDEX `idx_verif_alumni` (`alumni_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `activity_logs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `action` VARCHAR(100) NOT NULL,
  `module` VARCHAR(50) DEFAULT NULL,
  `table_name` VARCHAR(50) DEFAULT NULL,
  `record_id` INT UNSIGNED DEFAULT NULL,
  `old_values` JSON DEFAULT NULL,
  `new_values` JSON DEFAULT NULL,
  `ip_address` VARBINARY(16) DEFAULT NULL,
  `user_agent` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  CONSTRAINT `fk_log_user` 
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) 
    ON DELETE SET NULL ON UPDATE CASCADE,
  
  INDEX `idx_log_user` (`user_id`),
  INDEX `idx_log_action` (`action`),
  INDEX `idx_log_module` (`module`),
  INDEX `idx_log_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 9. NOTIFICATIONS & SYSTEM
-- ============================================================================

CREATE TABLE `notifications` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `type` ENUM('info', 'warning', 'success', 'error', 'survey_invite', 'verification') NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `message` TEXT NOT NULL,
  `link` VARCHAR(255) DEFAULT NULL,
  `is_read` TINYINT(1) DEFAULT 0,
  `read_at` DATETIME DEFAULT NULL,
  `scheduled_at` DATETIME DEFAULT NULL,
  `sent_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  CONSTRAINT `fk_notif_user` 
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE,
  
  INDEX `idx_notif_user_read` (`user_id`, `is_read`),
  INDEX `idx_notif_scheduled` (`scheduled_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `notification_settings` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `channel_email` TINYINT(1) DEFAULT 1,
  `channel_whatsapp` TINYINT(1) DEFAULT 0,
  `channel_telegram` TINYINT(1) DEFAULT 0,
  `reminder_survey` TINYINT(1) DEFAULT 1,
  `reminder_verifikasi` TINYINT(1) DEFAULT 1,
  `alert_iku` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  CONSTRAINT `fk_notifset_user` 
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE,
  
  UNIQUE KEY `uk_notifset_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `notification_queue` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `recipient` VARCHAR(255) NOT NULL,
  `channel` ENUM('email', 'whatsapp', 'telegram') NOT NULL,
  `type` VARCHAR(50) NOT NULL,
  `subject` VARCHAR(255) DEFAULT NULL,
  `message` TEXT NOT NULL,
  `attachment_path` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('pending', 'sent', 'failed', 'retry') DEFAULT 'pending',
  `retry_count` INT DEFAULT 0,
  `max_retry` INT DEFAULT 3,
  `scheduled_at` DATETIME DEFAULT NULL,
  `sent_at` DATETIME DEFAULT NULL,
  `error_message` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  INDEX `idx_queue_status` (`status`),
  INDEX `idx_queue_scheduled` (`scheduled_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `ump_provinsi` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `provinsi` VARCHAR(100) NOT NULL,
  `tahun` YEAR NOT NULL,
  `nominal` DECIMAL(12,2) NOT NULL,
  `perubahan_persen` DECIMAL(5,2) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  UNIQUE KEY `uk_ump_tahun_provinsi` (`tahun`, `provinsi`),
  INDEX `idx_ump_tahun` (`tahun`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 10. JOB QUEUE SYSTEM
-- ============================================================================

CREATE TABLE `jobs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `queue` VARCHAR(50) NOT NULL DEFAULT 'default',
  `payload` JSON NOT NULL,
  `attempts` TINYINT UNSIGNED DEFAULT 0,
  `reserved_at` DATETIME DEFAULT NULL,
  `available_at` DATETIME NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  INDEX `idx_jobs_queue_available` (`queue`, `available_at`),
  INDEX `idx_jobs_reserved` (`reserved_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `failed_jobs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `job_id` BIGINT UNSIGNED DEFAULT NULL,
  `queue` VARCHAR(50) NOT NULL,
  `payload` JSON NOT NULL,
  `exception` TEXT NOT NULL,
  `failed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  CONSTRAINT `fk_failed_job` 
    FOREIGN KEY (`job_id`) REFERENCES `jobs`(`id`) 
    ON DELETE SET NULL ON UPDATE CASCADE,
  
  INDEX `idx_failed_at` (`failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sync_jobs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `type` VARCHAR(50) NOT NULL COMMENT 'Job type: pddikti, import, export',
  `status` ENUM('pending', 'queued', 'processing', 'success', 'partial', 'failed', 'cancelled') NOT NULL DEFAULT 'pending',
  `payload` TEXT NOT NULL COMMENT 'JSON encoded job parameters',
  `result` TEXT NULL COMMENT 'JSON encoded job result',
  `retry_count` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL,
  `started_at` DATETIME NULL,
  `completed_at` DATETIME NULL,
  `updated_at` DATETIME NOT NULL,
  
  INDEX `idx_sync_type` (`type`),
  INDEX `idx_sync_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sync_logs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `sync_type` VARCHAR(50) NOT NULL COMMENT 'Type of sync: pddikti, manual_import, etc',
  `sync_date` DATETIME NOT NULL,
  `tahun_lulus` INT UNSIGNED DEFAULT NULL COMMENT 'Graduation year being synced',
  `prodi_id` VARCHAR(50) DEFAULT NULL COMMENT 'Study program ID filter',
  `fetched_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `inserted_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `updated_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `skipped_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `failed_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `errors` TEXT NULL COMMENT 'JSON encoded error details',
  `status` ENUM('success', 'partial', 'failed') NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  INDEX `idx_sync_type` (`sync_type`),
  INDEX `idx_sync_date` (`sync_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `config` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `config_key` VARCHAR(100) NOT NULL,
  `config_value` TEXT NOT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `is_encrypted` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  
  UNIQUE KEY `uk_config_key` (`config_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 11. CACHE & SESSION
-- ============================================================================

CREATE TABLE `cache` (
  `key` VARCHAR(255) NOT NULL PRIMARY KEY,
  `value` MEDIUMTEXT NOT NULL,
  `expiration` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  INDEX `idx_cache_expiration` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `ci_sessions` (
  `id` VARCHAR(128) NOT NULL PRIMARY KEY,
  `ip_address` VARBINARY(16) NOT NULL,
  `timestamp` INT UNSIGNED DEFAULT 0 NOT NULL,
  `data` MEDIUMBLOB NOT NULL,
  
  KEY `ci_sessions_timestamp` (`timestamp`),
  KEY `ci_sessions_ip` (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 12. EXPORT LOGS
-- ============================================================================

CREATE TABLE `export_logs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `kohort_id` INT UNSIGNED NOT NULL,
  `prodi_id` INT UNSIGNED NOT NULL,
  `filename` VARCHAR(255) NOT NULL,
  `status` ENUM('generated', 'sent_to_belmawa', 'rejected') DEFAULT 'generated',
  `sent_at` DATETIME DEFAULT NULL,
  `sent_by` INT UNSIGNED DEFAULT NULL,
  `belmawa_feedback` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  
  INDEX `idx_export_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `download_logs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `filename` VARCHAR(255) NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `downloaded_at` DATETIME NOT NULL,
  
  INDEX `idx_download_user` (`user_id`),
  INDEX `idx_download_date` (`downloaded_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SEED DATA
-- ============================================================================

-- 1. Fakultas
INSERT INTO `fakultas` (`kode`, `nama`, `dekan`, `email`, `is_active`) VALUES
('FT', 'Fakultas Teknik', 'Dr. Eng. Ahmad Fauzi', 'dekan@ft.univ.ac.id', 1),
('FEB', 'Fakultas Ekonomi dan Bisnis', 'Prof. Dr. Siti Aminah, SE., M.Si', 'dekan@feb.univ.ac.id', 1),
('FISIP', 'Fakultas Ilmu Sosial dan Ilmu Politik', 'Dr. Budi Santoso, M.Si', 'dekan@fisip.univ.ac.id', 1),
('FKIP', 'Fakultas Keguruan dan Ilmu Pendidikan', 'Prof. Dr. Ratna Dewi, M.Pd', 'dekan@fkip.univ.ac.id', 1),
('FAPERTA', 'Fakultas Pertanian', 'Dr. Ir. Joko Widodo, M.P', 'dekan@faperta.univ.ac.id', 1);

-- 2. Program Studi
INSERT INTO `program_studi` (`fakultas_id`, `kode`, `nama`, `jenjang`, `akreditasi`, `tahun_akreditasi`, `ketua_prodi`, `email`, `is_active`) VALUES
(1, 'TI', 'Teknik Informatika', 'S1', 'Unggul', 2023, 'Dr. Rina Kusuma, M.Kom', 'prodi@ti.univ.ac.id', 1),
(1, 'TE', 'Teknik Elektro', 'S1', 'A', 2022, 'Dr. Agus Setiawan, M.T', 'prodi@te.univ.ac.id', 1),
(1, 'TM', 'Teknik Mesin', 'S1', 'A', 2022, 'Dr. Ir. Bambang Wijaya, M.T', 'prodi@tm.univ.ac.id', 1),
(2, 'AK', 'Akuntansi', 'S1', 'Unggul', 2023, 'Prof. Dr. Linda Hartati, Ak., M.Si', 'prodi@ak.univ.ac.id', 1),
(2, 'MN', 'Manajemen', 'S1', 'A', 2022, 'Dr. Eko Prasetyo, SE., M.M', 'prodi@mn.univ.ac.id', 1),
(3, 'ILKOM', 'Ilmu Komunikasi', 'S1', 'A', 2022, 'Dr. Maya Sari, M.Ikom', 'prodi@ilkom.univ.ac.id', 1),
(3, 'HI', 'Hubungan Internasional', 'S1', 'B', 2021, 'Dr. Andi Pratama, M.Si', 'prodi@hi.univ.ac.id', 1),
(4, 'PGSD', 'Pendidikan Guru Sekolah Dasar', 'S1', 'Unggul', 2023, 'Prof. Dr. Sri Wahyuni, M.Pd', 'prodi@pgsd.univ.ac.id', 1),
(5, 'AGRO', 'Agroteknologi', 'S1', 'A', 2022, 'Dr. Ir. Hadi Purnomo, M.P', 'prodi@agro.univ.ac.id', 1);

-- 3. UMP Provinsi 2024-2026 (Sample Data)
INSERT INTO `ump_provinsi` (`provinsi`, `tahun`, `nominal`, `perubahan_persen`) VALUES
('DKI Jakarta', 2024, 5067381.00, 5.20),
('DKI Jakarta', 2025, 5330885.00, 5.20),
('DKI Jakarta', 2026, 5608091.00, 5.20),
('Jawa Barat', 2024, 2064891.00, 6.50),
('Jawa Barat', 2025, 2199109.00, 6.50),
('Jawa Barat', 2026, 2342051.00, 6.50),
('Jawa Tengah', 2024, 2096156.00, 7.00),
('Jawa Tengah', 2025, 2242887.00, 7.00),
('Jawa Tengah', 2026, 2399889.00, 7.00),
('Jawa Timur', 2024, 2130298.00, 6.80),
('Jawa Timur', 2025, 2275158.00, 6.80),
('Jawa Timur', 2026, 2429869.00, 6.80),
('Banten', 2024, 2906093.00, 5.50),
('Banten', 2025, 3065928.00, 5.50),
('Banten', 2026, 3234554.00, 5.50),
('Sumatera Utara', 2024, 2851319.00, 6.00),
('Sumatera Utara', 2025, 3022398.00, 6.00),
('Sumatera Utara', 2026, 3203742.00, 6.00),
('Sulawesi Selatan', 2024, 3586696.00, 5.80),
('Sulawesi Selatan', 2025, 3794724.00, 5.80),
('Sulawesi Selatan', 2026, 4014818.00, 5.80);

-- 4. Super Admin User (Password: password - BCrypt Hash)
-- IMPORTANT: Change password immediately after first login!
INSERT INTO `users` (`username`, `email`, `password_hash`, `full_name`, `role`, `is_active`, `is_verified`, `must_change_password`) VALUES
('superadmin', 'superadmin@univ.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator Sistem', 'super_admin', 1, 1, 1);

-- 5. PDDikti Configuration
INSERT INTO `config` (`config_key`, `config_value`, `description`, `is_encrypted`, `created_at`, `updated_at`) VALUES
('pddikti_api_url', 'https://neo.feeder.kemdikbud.go.id/rest', 'PDDikti NeoFeeder API URL', 0, NOW(), NOW()),
('pddikti_api_key', '', 'PDDikti API Key - UPDATE THIS', 1, NOW(), NOW()),
('pddikti_api_secret', '', 'PDDikti API Secret - UPDATE THIS', 1, NOW(), NOW());

-- ============================================================================
-- TRIGGERS & STORED PROCEDURES
-- ============================================================================

DELIMITER $$

-- Trigger: Prevent deletion from activity_logs (IMMUTABLE)
CREATE TRIGGER `trg_prevent_log_delete` 
BEFORE DELETE ON `activity_logs`
FOR EACH ROW
BEGIN
  SIGNAL SQLSTATE '45000' 
  SET MESSAGE_TEXT = 'Activity logs are immutable and cannot be deleted';
END$$

-- Trigger: Auto-update completion percentage on survey_responses
CREATE TRIGGER `trg_update_completion_pct` 
AFTER INSERT ON `survey_answers`
FOR EACH ROW
BEGIN
  DECLARE total_q INT;
  DECLARE answered_q INT;
  DECLARE pct DECIMAL(5,2);
  
  SELECT COUNT(*) INTO total_q FROM survey_questions WHERE survey_id = (
    SELECT survey_id FROM survey_responses WHERE id = NEW.response_id
  );
  
  SELECT COUNT(DISTINCT question_id) INTO answered_q 
  FROM survey_answers WHERE response_id = NEW.response_id;
  
  IF total_q > 0 THEN
    SET pct = (answered_q / total_q) * 100;
    UPDATE survey_responses 
    SET completion_percentage = pct, updated_at = NOW()
    WHERE id = NEW.response_id;
  END IF;
END$$

DELIMITER ;

-- ============================================================================
-- VIEWS FOR REPORTING
-- ============================================================================

CREATE OR REPLACE VIEW `v_alumni_summary` AS
SELECT 
  a.id,
  a.nim,
  a.nama_lengkap,
  ps.nama AS prodi_nama,
  f.nama AS fakultas_nama,
  k.tahun_lulus,
  a.status_tracing,
  a.is_verified,
  a.email_pribadi,
  a.no_hp_whatsapp
FROM alumni a
JOIN program_studi ps ON a.prodi_id = ps.id
JOIN fakultas f ON ps.fakultas_id = f.id
LEFT JOIN kohorts k ON a.kohort_id = k.id;

CREATE OR REPLACE VIEW `v_iku_dashboard` AS
SELECT 
  ps.kode AS prodi_kode,
  ps.nama AS prodi_nama,
  ic.tahun_iku,
  ic.iku_number,
  ic.numerator,
  ic.denominator,
  ic.percentage,
  ic.target_percentage,
  ic.status_capaian,
  CASE 
    WHEN ic.percentage >= ic.target_percentage THEN '✅ Tercapai'
    ELSE '❌ Belum Tercapai'
  END AS status_visual
FROM iku_calculations ic
JOIN program_studi ps ON ic.prodi_id = ps.id
ORDER BY ic.tahun_iku DESC, ps.kode, ic.iku_number;

CREATE OR REPLACE VIEW `v_survey_response_rate` AS
SELECT 
  s.id AS survey_id,
  s.title,
  s.tahun_periode,
  COUNT(DISTINCT sr.id) AS total_responses,
  COUNT(DISTINCT CASE WHEN sr.status = 'submitted' THEN sr.id END) AS completed_responses,
  COUNT(DISTINCT CASE WHEN sr.status = 'draft' THEN sr.id END) AS draft_responses,
  ROUND(COUNT(DISTINCT CASE WHEN sr.status = 'submitted' THEN sr.id END) * 100.0 / 
        NULLIF(COUNT(DISTINCT sr.id), 0), 2) AS completion_rate
FROM surveys s
LEFT JOIN survey_responses sr ON s.id = sr.survey_id
GROUP BY s.id, s.title, s.tahun_periode;

CREATE OR REPLACE VIEW `v_cpl_gap_monitoring` AS
SELECT
  c.id as cpl_id,
  c.kode_cpl,
  c.jenis as aspect,
  c.target_industri,
  COUNT(DISTINCT cm.id) as total_mappings,
  MAX(CASE WHEN cm.type = 'SN_DIPTI' THEN 1 ELSE 0 END) as has_sn_dikti,
  MAX(CASE WHEN cm.type = 'KKNI' THEN 1 ELSE 0 END) as has_kkni
FROM cpl c
LEFT JOIN cpl_mapping cm ON c.id = cm.cpl_id
GROUP BY c.id, c.kode_cpl, c.jenis, c.target_industri;

-- ============================================================================
-- END OF SCHEMA
-- ============================================================================

COMMIT;
