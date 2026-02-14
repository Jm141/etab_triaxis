-- Migration: Add elimination round functionality
-- Date: 2024-01-03
-- Description: Adds fields to support elimination rounds and contestant advancement

-- Add is_elimination_round to rounds table
ALTER TABLE `rounds` 
ADD COLUMN IF NOT EXISTS `is_elimination_round` TINYINT(1) DEFAULT 0 COMMENT 'Whether this round is an elimination round' AFTER `status`;

-- Add advance_count to event_levels table
ALTER TABLE `event_levels` 
ADD COLUMN IF NOT EXISTS `advance_count` INT(11) NULL COMMENT 'Number of contestants that advance to next level (NULL = all advance)' AFTER `status`;

-- Add qualified_for_level_id to contestants table
ALTER TABLE `contestants` 
ADD COLUMN IF NOT EXISTS `qualified_for_level_id` INT(11) NULL COMMENT 'Level ID that this contestant qualified for' AFTER `status`,
ADD INDEX IF NOT EXISTS `idx_qualified_for_level` (`qualified_for_level_id`),
ADD FOREIGN KEY IF NOT EXISTS `fk_contestant_qualified_level` (`qualified_for_level_id`) REFERENCES `event_levels`(`id`) ON DELETE SET NULL;
