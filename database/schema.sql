-- Tabulation System Database Schema
-- MySQL 5.7+ / MariaDB 10.2+

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- ============================================
-- 1. USER & ROLE MANAGEMENT
-- ============================================

CREATE TABLE IF NOT EXISTS `roles` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL UNIQUE,
  `description` TEXT,
  `permissions` TEXT COMMENT 'JSON array of permissions',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `roles` (`name`, `description`, `permissions`) VALUES
('Super Admin', 'Full system access', '["*"]'),
('Event Organizer', 'Organize and manage events, assign staff, manage overall event flow', '["events.*", "users.create", "judges.*", "contestants.*", "criteria.*", "results.view"]'),
('Event Admin', 'Manage events and configurations', '["events.*", "judges.*", "contestants.*", "results.view", "results.release"]'),
('Event Technical Admin', 'Technical setup: criteria, rounds, weights, system configuration', '["events.view", "criteria.*", "rounds.*", "weights.*", "results.view"]'),
('Tabulator', 'View and manage scores', '["scores.view", "scores.edit", "results.view", "results.calculate"]'),
('Judge', 'Submit scores only', '["scores.submit"]'),
('Auditor', 'View-only access', '["results.view"]'),
('Host', 'Display access only', '["display.view"]');

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(255) NOT NULL,
  `role_id` INT(11) NOT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `last_login` TIMESTAMP NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE RESTRICT,
  INDEX `idx_username` (`username`),
  INDEX `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default admin user (password: admin123 - CHANGE THIS!)
-- Note: If login fails, run: php diagnose_auth.php to fix the password hash
INSERT INTO `users` (`username`, `email`, `password_hash`, `full_name`, `role_id`, `is_active`) VALUES
('admin', 'admin@tabulation.local', '$2y$10$JbCcIxAIpF.GPThSTByw..IRhcSx2nDRtJzegtFcs4KL8.J.JPKmC', 'System Administrator', 1, 1)
ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), is_active = 1;

CREATE TABLE IF NOT EXISTS `sessions` (
  `id` VARCHAR(128) NOT NULL,
  `user_id` INT(11) NOT NULL,
  `ip_address` VARCHAR(45),
  `user_agent` TEXT,
  `last_activity` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `data` TEXT,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_last_activity` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 2. EVENT MANAGEMENT
-- ============================================

CREATE TABLE IF NOT EXISTS `events` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `event_type` VARCHAR(50) DEFAULT 'Pageant',
  `venue` VARCHAR(255),
  `event_date` DATE,
  `start_time` TIME,
  `end_time` TIME,
  `timezone` VARCHAR(50) DEFAULT 'UTC',
  `logo_path` VARCHAR(255),
  `status` ENUM('Draft', 'Ongoing', 'Finished', 'Archived') DEFAULT 'Draft',
  `created_by` INT(11) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT,
  INDEX `idx_status` (`status`),
  INDEX `idx_event_date` (`event_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- User-Event Assignments (for filtering events by user)
CREATE TABLE IF NOT EXISTS `user_event_assignments` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `event_id` INT(11) NOT NULL,
  `assigned_by` INT(11) NULL,
  `assigned_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `is_active` TINYINT(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`assigned_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  UNIQUE KEY `unique_user_event` (`user_id`, `event_id`),
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_event_id` (`event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 3. COMPETITION STRUCTURE
-- ============================================

CREATE TABLE IF NOT EXISTS `event_levels` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `event_id` INT(11) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT,
  `order` INT(11) DEFAULT 0,
  `status` ENUM('Pending', 'Active', 'Completed') DEFAULT 'Pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE,
  INDEX `idx_event_id` (`event_id`),
  INDEX `idx_order` (`order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `rounds` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `level_id` INT(11) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT,
  `order` INT(11) DEFAULT 0,
  `status` ENUM('Pending', 'Active', 'Completed') DEFAULT 'Pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`level_id`) REFERENCES `event_levels`(`id`) ON DELETE CASCADE,
  INDEX `idx_level_id` (`level_id`),
  INDEX `idx_order` (`order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `criteria` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `event_id` INT(11) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `max_score` DECIMAL(10,2) NOT NULL DEFAULT 100.00,
  `category` VARCHAR(100),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE,
  INDEX `idx_event_id` (`event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `criteria_weights` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `round_id` INT(11) NOT NULL,
  `criteria_id` INT(11) NOT NULL,
  `weight` DECIMAL(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Percentage weight (0-100)',
  `is_active` TINYINT(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`round_id`) REFERENCES `rounds`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`criteria_id`) REFERENCES `criteria`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_round_criteria` (`round_id`, `criteria_id`),
  INDEX `idx_round_id` (`round_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 4. JUDGE MANAGEMENT
-- ============================================

CREATE TABLE IF NOT EXISTS `judges` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `event_id` INT(11) NOT NULL,
  `judge_number` VARCHAR(50),
  `specialty` VARCHAR(100),
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_judge_event` (`user_id`, `event_id`),
  INDEX `idx_event_id` (`event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `judge_assignments` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `judge_id` INT(11) NOT NULL,
  `round_id` INT(11) NOT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`judge_id`) REFERENCES `judges`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`round_id`) REFERENCES `rounds`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_judge_round` (`judge_id`, `round_id`),
  INDEX `idx_round_id` (`round_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `judge_criteria_assignments` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `judge_id` INT(11) NOT NULL,
  `round_id` INT(11) NOT NULL,
  `criteria_id` INT(11) NOT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`judge_id`) REFERENCES `judges`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`round_id`) REFERENCES `rounds`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`criteria_id`) REFERENCES `criteria`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_judge_round_criteria` (`judge_id`, `round_id`, `criteria_id`),
  INDEX `idx_judge_round` (`judge_id`, `round_id`),
  INDEX `idx_criteria_id` (`criteria_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 5. CONTESTANT MANAGEMENT
-- ============================================

CREATE TABLE IF NOT EXISTS `contestants` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `event_id` INT(11) NOT NULL,
  `contestant_number` VARCHAR(50) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `team_name` VARCHAR(255),
  `category` VARCHAR(100),
  `photo_path` VARCHAR(255),
  `bio` TEXT,
  `status` ENUM('Active', 'Disqualified', 'Withdrawn') DEFAULT 'Active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_event_number` (`event_id`, `contestant_number`),
  INDEX `idx_event_id` (`event_id`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 6. SCORING SYSTEM
-- ============================================

CREATE TABLE IF NOT EXISTS `scores` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `judge_id` INT(11) NOT NULL,
  `contestant_id` INT(11) NOT NULL,
  `round_id` INT(11) NOT NULL,
  `total_score` DECIMAL(10,3) DEFAULT 0.000,
  `is_submitted` TINYINT(1) DEFAULT 0,
  `is_locked` TINYINT(1) DEFAULT 0,
  `admin_edit_allowed` TINYINT(1) DEFAULT 0 COMMENT 'Judge permission for admin to edit',
  `admin_edited_by` INT(11) NULL COMMENT 'User ID who edited (admin/tabulator)',
  `admin_edited_at` TIMESTAMP NULL,
  `submitted_at` TIMESTAMP NULL,
  `ip_address` VARCHAR(45),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`judge_id`) REFERENCES `judges`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`contestant_id`) REFERENCES `contestants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`round_id`) REFERENCES `rounds`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`admin_edited_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  UNIQUE KEY `unique_judge_contestant_round` (`judge_id`, `contestant_id`, `round_id`),
  INDEX `idx_contestant_round` (`contestant_id`, `round_id`),
  INDEX `idx_judge_id` (`judge_id`),
  INDEX `idx_is_submitted` (`is_submitted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `score_details` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `score_id` INT(11) NOT NULL,
  `criteria_id` INT(11) NOT NULL,
  `raw_score` DECIMAL(10,3) NOT NULL,
  `weighted_score` DECIMAL(10,3) DEFAULT 0.000,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`score_id`) REFERENCES `scores`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`criteria_id`) REFERENCES `criteria`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_score_criteria` (`score_id`, `criteria_id`),
  INDEX `idx_score_id` (`score_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Report Deductions (Round/Level/Final aggregate deductions)
CREATE TABLE IF NOT EXISTS `report_deductions` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `event_id` INT(11) NOT NULL,
  `scope_type` ENUM('round', 'level', 'final') NOT NULL,
  `scope_id` INT(11) NOT NULL DEFAULT 0,
  `contestant_id` INT(11) NOT NULL,
  `deduction` DECIMAL(10,3) NOT NULL DEFAULT 0.000,
  `reason` TEXT NULL,
  `status` ENUM('requested', 'granted', 'denied') NOT NULL DEFAULT 'requested',
  `requested_by` INT(11) NULL,
  `requested_at` TIMESTAMP NULL,
  `responded_by` INT(11) NULL,
  `responded_at` TIMESTAMP NULL,
  `created_by` INT(11) NULL,
  `updated_by` INT(11) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_report_deduction` (`event_id`, `scope_type`, `scope_id`, `contestant_id`),
  INDEX `idx_event_scope` (`event_id`, `scope_type`, `scope_id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_contestant_id` (`contestant_id`),
  FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`contestant_id`) REFERENCES `contestants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`requested_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`responded_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`updated_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 7. RANKINGS & RESULTS
-- ============================================

CREATE TABLE IF NOT EXISTS `rankings` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `event_id` INT(11) NOT NULL,
  `level_id` INT(11),
  `round_id` INT(11),
  `contestant_id` INT(11) NOT NULL,
  `rank` INT(11) NOT NULL,
  `total_score` DECIMAL(10,3) NOT NULL,
  `average_score` DECIMAL(10,3),
  `calculated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`level_id`) REFERENCES `event_levels`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`round_id`) REFERENCES `rounds`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`contestant_id`) REFERENCES `contestants`(`id`) ON DELETE CASCADE,
  INDEX `idx_event_round_rank` (`event_id`, `round_id`, `rank`),
  INDEX `idx_contestant_id` (`contestant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `result_snapshots` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `event_id` INT(11) NOT NULL,
  `round_id` INT(11),
  `snapshot_data` TEXT COMMENT 'JSON data of rankings',
  `is_public` TINYINT(1) DEFAULT 0,
  `released_by` INT(11),
  `released_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`round_id`) REFERENCES `rounds`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`released_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_event_id` (`event_id`),
  INDEX `idx_is_public` (`is_public`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 8. AUDIT & LOGGING
-- ============================================

CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11),
  `event_id` INT(11),
  `action` VARCHAR(100) NOT NULL,
  `table_name` VARCHAR(100),
  `record_id` INT(11),
  `old_values` TEXT COMMENT 'JSON',
  `new_values` TEXT COMMENT 'JSON',
  `ip_address` VARCHAR(45),
  `user_agent` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE SET NULL,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_event_id` (`event_id`),
  INDEX `idx_action` (`action`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `login_logs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11),
  `username` VARCHAR(100),
  `ip_address` VARCHAR(45),
  `user_agent` TEXT,
  `status` ENUM('Success', 'Failed') NOT NULL,
  `failure_reason` VARCHAR(255),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 9. SYSTEM CONFIGURATION
-- ============================================

CREATE TABLE IF NOT EXISTS `system_config` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `config_key` VARCHAR(100) NOT NULL UNIQUE,
  `config_value` TEXT,
  `description` TEXT,
  `updated_by` INT(11),
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`updated_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_config_key` (`config_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `system_config` (`config_key`, `config_value`, `description`) VALUES
('site_name', 'Tabulation System', 'System name'),
('session_timeout', '3600', 'Session timeout in seconds'),
('max_score_decimal_places', '2', 'Decimal places for scores'),
('enable_audit_log', '1', 'Enable audit logging'),
('tie_breaking_method', 'average', 'Tie breaking method: average, median, highest');

