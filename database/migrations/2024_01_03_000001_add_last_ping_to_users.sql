-- Add last_ping column to users table for judge ping functionality
-- Migration: 2024_01_03_000001_add_last_ping_to_users.sql

ALTER TABLE `users` 
ADD COLUMN `last_ping` TIMESTAMP NULL DEFAULT NULL COMMENT 'Last time the user pinged the server (for connectivity checking)',
ADD INDEX `idx_last_ping` (`last_ping`);
