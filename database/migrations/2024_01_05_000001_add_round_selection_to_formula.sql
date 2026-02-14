-- Migration: Add round selection to scoring formula
-- Allows formulas to specify which rounds to combine/include

ALTER TABLE `scoring_formula` 
ADD COLUMN IF NOT EXISTS `round_ids` TEXT NULL COMMENT 'JSON array of round IDs to include in calculation (NULL = all rounds)' AFTER `formula_type`,
ADD COLUMN IF NOT EXISTS `level_ids` TEXT NULL COMMENT 'JSON array of level IDs to include in calculation (NULL = all levels)' AFTER `round_ids`;
