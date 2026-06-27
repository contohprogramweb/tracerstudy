-- Survey Builder Module Database Schema
-- Created: 2024

-- Table: surveys
CREATE TABLE IF NOT EXISTS surveys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    created_by INT NOT NULL,
    published_at DATETIME NULL,
    published_by INT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NULL,
    deleted_at DATETIME NULL,
    INDEX idx_status (status),
    INDEX idx_created_by (created_by),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: survey_questions
CREATE TABLE IF NOT EXISTS survey_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    survey_id INT NOT NULL,
    question_text TEXT NOT NULL,
    type ENUM('short_answer', 'long_answer', 'multiple_choice', 'dropdown', 'checkbox', 'rating', 'date', 'number') NOT NULL,
    options TEXT NULL COMMENT 'Pipe-separated options for choice questions',
    is_required TINYINT(1) DEFAULT 0,
    is_core TINYINT(1) DEFAULT 0 COMMENT 'Core questions from Belmawa cannot be deleted/modified',
    `order` INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NULL,
    FOREIGN KEY (survey_id) REFERENCES surveys(id) ON DELETE CASCADE,
    INDEX idx_survey_id (survey_id),
    INDEX idx_order (`order`),
    INDEX idx_is_core (is_core)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: survey_logics
CREATE TABLE IF NOT EXISTS survey_logics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    survey_id INT NOT NULL,
    question_id INT NOT NULL COMMENT 'Source question',
    condition_value VARCHAR(255) NOT NULL COMMENT 'Value that triggers the jump',
    target_question_id INT NOT NULL COMMENT 'Question to jump to',
    created_at DATETIME NOT NULL,
    FOREIGN KEY (survey_id) REFERENCES surveys(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES survey_questions(id) ON DELETE CASCADE,
    FOREIGN KEY (target_question_id) REFERENCES survey_questions(id) ON DELETE CASCADE,
    INDEX idx_survey_id (survey_id),
    INDEX idx_question_id (question_id),
    INDEX idx_target (target_question_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: survey_responses
CREATE TABLE IF NOT EXISTS survey_responses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    survey_id INT NOT NULL,
    respondent_id INT NULL COMMENT 'User ID if logged in, NULL for anonymous',
    respondent_email VARCHAR(255) NULL,
    submitted_at DATETIME NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    FOREIGN KEY (survey_id) REFERENCES surveys(id) ON DELETE CASCADE,
    INDEX idx_survey_id (survey_id),
    INDEX idx_respondent (respondent_id),
    INDEX idx_submitted_at (submitted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: survey_answers
CREATE TABLE IF NOT EXISTS survey_answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    response_id INT NOT NULL,
    question_id INT NOT NULL,
    answer_text TEXT NULL,
    answer_value VARCHAR(255) NULL COMMENT 'For numeric/rating answers',
    created_at DATETIME NOT NULL,
    FOREIGN KEY (response_id) REFERENCES survey_responses(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES survey_questions(id) ON DELETE CASCADE,
    INDEX idx_response_id (response_id),
    INDEX idx_question_id (question_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- View: Survey statistics
CREATE OR REPLACE VIEW v_survey_stats AS
SELECT 
    s.id,
    s.title,
    s.status,
    COUNT(DISTINCT q.id) as total_questions,
    COUNT(DISTINCT CASE WHEN q.is_core = 1 THEN q.id END) as core_questions,
    COUNT(DISTINCT r.id) as total_responses,
    COUNT(DISTINCT r.respondent_id) as unique_respondents
FROM surveys s
LEFT JOIN survey_questions q ON s.id = q.survey_id
LEFT JOIN survey_responses r ON s.id = r.survey_id
GROUP BY s.id, s.title, s.status;

-- Insert sample data for testing (optional)
-- INSERT INTO surveys (title, description, status, created_by, created_at) VALUES
-- ('Survey Kepuasan Alumni 2024', 'Survey tahunan untuk mengukur kepuasan alumni terhadap program studi', 'draft', 1, NOW());

