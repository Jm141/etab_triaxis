-- Migration: Add overwrite key and point deduction fields to scores table
-- Allows organizer to provide overwrite key for tech admin to edit scores without judge permission

ALTER TABLE `scores` 
ADD COLUMN `overwrite_key` VARCHAR(255) NULL COMMENT 'Organizer-provided key to allow editing without judge permission',
ADD COLUMN `overwrite_key_set_by` INT(11) NULL COMMENT 'User ID who set the overwrite key (organizer)',
ADD COLUMN `overwrite_key_set_at` TIMESTAMP NULL COMMENT 'When the overwrite key was set',
ADD COLUMN `overwrite_key_used_by` INT(11) NULL COMMENT 'User ID who used the overwrite key (tech admin)',
ADD COLUMN `overwrite_key_used_at` TIMESTAMP NULL COMMENT 'When the overwrite key was used',
ADD COLUMN `point_deduction` DECIMAL(10,3) DEFAULT 0.000 COMMENT 'Point deduction applied by organizer',
ADD COLUMN `deduction_reason` TEXT NULL COMMENT 'Reason for point deduction',
ADD COLUMN `deduction_applied_by` INT(11) NULL COMMENT 'User ID who applied the deduction',
ADD COLUMN `deduction_applied_at` TIMESTAMP NULL COMMENT 'When the deduction was applied',
ADD INDEX `idx_overwrite_key` (`overwrite_key`),
ADD FOREIGN KEY (`overwrite_key_set_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
ADD FOREIGN KEY (`overwrite_key_used_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
ADD FOREIGN KEY (`deduction_applied_by`) REFERENCES `users`(`id`) ON DELETE SET NULL;
