-- Migration: Create scoring_formula table for formula management
-- Only Event Technical Admin can view/edit formulas

CREATE TABLE IF NOT EXISTS `scoring_formula` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `event_id` INT(11) NOT NULL,
  `formula_type` ENUM('round', 'level', 'final') NOT NULL DEFAULT 'round',
  `formula_expression` TEXT NOT NULL COMMENT 'Formula expression (e.g., SUM(round_scores) / COUNT(rounds))',
  `description` TEXT COMMENT 'Description of what this formula calculates',
  `is_active` TINYINT(1) DEFAULT 1,
  `created_by` INT(11) NOT NULL,
  `updated_by` INT(11) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`updated_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_event_id` (`event_id`),
  INDEX `idx_formula_type` (`formula_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default formula: Sum all round scores, then average per round, then total per level
INSERT INTO `scoring_formula` (`event_id`, `formula_type`, `formula_expression`, `description`, `created_by`) 
SELECT 1, 'round', 'SUM(round_scores)', 'Sum of all scores in a round', 1 
WHERE NOT EXISTS (SELECT 1 FROM `scoring_formula` WHERE `event_id` = 1 AND `formula_type` = 'round');
