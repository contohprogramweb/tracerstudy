-- Survey Progress Table for Auto-save Feature
-- Supports BR-SUR-005: Auto-save ke localStorage, bisa dilanjutkan 7 hari

CREATE TABLE IF NOT EXISTS `survey_progress` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `survey_id` INT(11) UNSIGNED NOT NULL,
  `alumni_id` INT(11) UNSIGNED NOT NULL,
  `answers` TEXT NOT NULL COMMENT 'JSON encoded answers',
  `current_question_id` INT(11) NULL COMMENT 'Last active question',
  `progress_percent` TINYINT(3) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_survey_alumni` (`survey_id`, `alumni_id`),
  INDEX `idx_survey_id` (`survey_id`),
  INDEX `idx_alumni_id` (`alumni_id`),
  INDEX `idx_updated_at` (`updated_at`),
  FOREIGN KEY (`survey_id`) REFERENCES `surveys`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`alumni_id`) REFERENCES `alumni`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add certificate generation flag to surveys table
ALTER TABLE `surveys`
ADD COLUMN IF NOT EXISTS `generate_certificate` TINYINT(1) NOT NULL DEFAULT 0 AFTER `deleted_at`,
ADD COLUMN IF NOT EXISTS `certificate_template` VARCHAR(255) NULL AFTER `generate_certificate`;

-- Create certificates upload directory placeholder
-- Note: This is handled by the application code

-- End of Migration
