-- Migration tracking table
-- Run this first to set up the migration system

CREATE TABLE IF NOT EXISTS `migrations` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `migration_name` VARCHAR(255) NOT NULL UNIQUE,
  `batch` INT(11) NOT NULL,
  `executed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_migration_name` (`migration_name`),
  INDEX `idx_batch` (`batch`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



