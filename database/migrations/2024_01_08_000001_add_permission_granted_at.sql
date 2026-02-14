-- Migration: Add permission_granted_at column to scores table
-- Date: 2024-01-08
-- Description: Adds permission_granted_at column to track when permission was granted for score editing

-- Add permission_granted_at column
ALTER TABLE `scores` 
ADD COLUMN IF NOT EXISTS `permission_granted_at` TIMESTAMP NULL 
COMMENT 'Timestamp when permission to edit was granted' 
AFTER `permission_responded_at`;
