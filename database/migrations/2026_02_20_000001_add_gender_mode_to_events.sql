-- Add gender_mode field to events table
-- This field determines how contestants are displayed in scoring tables
-- 'single' = Traditional one row per contestant
-- 'mr_miss' = Grouped Male and Female rows per contestant number

ALTER TABLE `events` 
ADD COLUMN `gender_mode` ENUM('single', 'mr_miss') DEFAULT 'single' 
AFTER `event_type`;

-- Add index for better performance
ALTER TABLE `events` ADD INDEX `idx_gender_mode` (`gender_mode`);
