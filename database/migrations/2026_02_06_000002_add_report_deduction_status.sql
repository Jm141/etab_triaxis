-- Add request/response columns to report_deductions
ALTER TABLE `report_deductions`
  ADD COLUMN `status` ENUM('requested', 'granted', 'denied') NOT NULL DEFAULT 'requested' AFTER `reason`,
  ADD COLUMN `requested_by` INT(11) NULL AFTER `status`,
  ADD COLUMN `requested_at` TIMESTAMP NULL AFTER `requested_by`,
  ADD COLUMN `responded_by` INT(11) NULL AFTER `requested_at`,
  ADD COLUMN `responded_at` TIMESTAMP NULL AFTER `responded_by`,
  ADD INDEX `idx_status` (`status`),
  ADD CONSTRAINT `fk_report_deductions_requested_by` FOREIGN KEY (`requested_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_report_deductions_responded_by` FOREIGN KEY (`responded_by`) REFERENCES `users`(`id`) ON DELETE SET NULL;
