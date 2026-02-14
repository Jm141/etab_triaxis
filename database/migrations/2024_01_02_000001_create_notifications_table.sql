-- Migration: Create notifications table for permission requests
-- This table stores notifications for permission requests between judges and admins

CREATE TABLE IF NOT EXISTS `notifications` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL COMMENT 'User who will receive the notification',
  `type` VARCHAR(50) NOT NULL COMMENT 'Type: permission_request, permission_granted, permission_denied, score_edited',
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT,
  `related_type` VARCHAR(50) COMMENT 'Type of related entity: score, etc.',
  `related_id` INT(11) COMMENT 'ID of related entity',
  `is_read` TINYINT(1) DEFAULT 0,
  `read_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_is_read` (`is_read`),
  INDEX `idx_type` (`type`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add permission_request_status to scores table
ALTER TABLE `scores` 
ADD COLUMN `permission_request_status` ENUM('none', 'judge_requested', 'admin_requested', 'granted', 'denied') DEFAULT 'none' AFTER `admin_edit_allowed`,
ADD COLUMN `permission_requested_by` INT(11) NULL COMMENT 'User ID who requested permission' AFTER `permission_request_status`,
ADD COLUMN `permission_requested_at` TIMESTAMP NULL AFTER `permission_requested_by`,
ADD COLUMN `permission_responded_by` INT(11) NULL COMMENT 'User ID who responded to permission request' AFTER `permission_requested_at`,
ADD COLUMN `permission_responded_at` TIMESTAMP NULL AFTER `permission_responded_by`,
ADD FOREIGN KEY (`permission_requested_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
ADD FOREIGN KEY (`permission_responded_by`) REFERENCES `users`(`id`) ON DELETE SET NULL;

-- Add is_draft column to scores table to track auto-saved scores
ALTER TABLE `scores`
ADD COLUMN `is_draft` TINYINT(1) DEFAULT 1 COMMENT '1 = draft (auto-saved), 0 = final (submitted)' AFTER `is_submitted`;

