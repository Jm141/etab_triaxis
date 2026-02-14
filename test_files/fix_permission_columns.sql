-- Fix missing permission columns in scores table
-- Run this if you get "Column not found: permission_requested_by" error

-- Check if columns exist, if not add them
ALTER TABLE `scores`
ADD COLUMN IF NOT EXISTS `permission_request_status` ENUM('none', 'judge_requested', 'admin_requested', 'granted', 'denied') DEFAULT 'none' AFTER `is_locked`,
ADD COLUMN IF NOT EXISTS `permission_requested_by` INT(11) NULL AFTER `permission_request_status`,
ADD COLUMN IF NOT EXISTS `permission_requested_at` TIMESTAMP NULL AFTER `permission_requested_by`,
ADD COLUMN IF NOT EXISTS `permission_responded_by` INT(11) NULL AFTER `permission_requested_at`,
ADD COLUMN IF NOT EXISTS `permission_responded_at` TIMESTAMP NULL AFTER `permission_responded_by`;

-- Add foreign keys if they don't exist
-- Note: MySQL doesn't support IF NOT EXISTS for foreign keys, so we'll check manually
-- If you get an error about foreign key already existing, that's fine - it means it's already there

-- Add index for better performance
CREATE INDEX IF NOT EXISTS `idx_scores_permission_status` ON `scores` (`permission_request_status`);
CREATE INDEX IF NOT EXISTS `idx_scores_permission_requested_by` ON `scores` (`permission_requested_by`);

