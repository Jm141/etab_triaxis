-- Migration: Add organizer key to events table
-- Event Organizer has one master key per event that tech admins can use to edit scores

ALTER TABLE `events` 
ADD COLUMN `organizer_key` VARCHAR(255) NULL COMMENT 'Master key for organizer to allow tech admin edits',
ADD COLUMN `organizer_key_set_by` INT(11) NULL COMMENT 'User ID who set the organizer key',
ADD COLUMN `organizer_key_set_at` TIMESTAMP NULL COMMENT 'When the organizer key was set',
ADD INDEX `idx_organizer_key` (`organizer_key`),
ADD FOREIGN KEY (`organizer_key_set_by`) REFERENCES `users`(`id`) ON DELETE SET NULL;
