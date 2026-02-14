-- Add preparation_only field to judge_assignments table
-- This field allows tech admins to assign rounds to judges for preparation purposes
-- but these rounds won't be visible to the judges themselves

ALTER TABLE `judge_assignments` 
ADD COLUMN `is_preparation_only` TINYINT(1) DEFAULT 0 COMMENT 'If true, round is assigned for preparation only and not visible to judge';

-- Add index for better query performance
ALTER TABLE `judge_assignments` 
ADD INDEX `idx_preparation_only` (`is_preparation_only`);
