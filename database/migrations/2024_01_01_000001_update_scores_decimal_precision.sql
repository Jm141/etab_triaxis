-- Migration: Update scores and score_details tables to support 3 decimal places
-- This allows more precise scoring (0.001 precision)

-- Update scores table
ALTER TABLE `scores` 
MODIFY COLUMN `total_score` DECIMAL(10,3) DEFAULT 0.000;

-- Update score_details table
ALTER TABLE `score_details` 
MODIFY COLUMN `raw_score` DECIMAL(10,3) NOT NULL,
MODIFY COLUMN `weighted_score` DECIMAL(10,3) DEFAULT 0.000;

-- Update rankings table if it exists
ALTER TABLE `rankings` 
MODIFY COLUMN `total_score` DECIMAL(10,3) NOT NULL,
MODIFY COLUMN `average_score` DECIMAL(10,3);

