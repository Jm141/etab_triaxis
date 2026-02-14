# Database Migrations

This directory contains database migration files. Migrations are SQL scripts that modify your database schema.

## How Migrations Work

1. Each migration file is a `.sql` file with a timestamp prefix
2. Migrations are executed in order (by filename)
3. The system tracks which migrations have been run
4. Only pending migrations are executed

## Migration File Naming

Format: `YYYY_MM_DD_HHMMSS_description.sql`

Example: `2024_01_15_143022_add_notifications_table.sql`

## Creating a New Migration

```bash
php migrate.php --create=add_notifications_table
```

This creates a new file in `database/migrations/` with a template.

## Running Migrations

```bash
# Run pending migrations
php migrate.php

# Show migration status
php migrate.php --status

# Fresh start (drops all tables and re-runs all migrations)
php migrate.php --fresh

# Rollback last batch
php migrate.php --rollback
```

## Migration Best Practices

1. **Always use IF NOT EXISTS** for CREATE TABLE statements
2. **Use transactions** for complex migrations (handled automatically)
3. **Test migrations** on a development database first
4. **One migration = one logical change**
5. **Don't modify existing migration files** - create new ones instead

## Example Migration

```sql
-- Migration: add_notifications_table
-- Created: 2024-01-15 14:30:22

CREATE TABLE IF NOT EXISTS `notifications` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `message` TEXT NOT NULL,
  `is_read` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## Notes

- Migrations are tracked in the `migrations` table
- Each batch of migrations runs in a transaction
- If a migration fails, the entire batch is rolled back
- The `--fresh` option will DROP ALL TABLES (use with caution!)



