-- Add organizer assignment system for judges
-- This allows event organizers to have their own pool of judges

-- Create table for judge-organizer assignments
CREATE TABLE IF NOT EXISTS `judge_organizer_assignments` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `organizer_id` INT(11) NOT NULL,
  `judge_id` INT(11) NOT NULL,
  `event_id` INT(11) NOT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`organizer_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`judge_id`) REFERENCES `judges`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_organizer_judge_event` (`organizer_id`, `judge_id`, `event_id`),
  INDEX `idx_organizer_id` (`organizer_id`),
  INDEX `idx_judge_id` (`judge_id`),
  INDEX `idx_event_id` (`event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Assigns judges to specific event organizers';
