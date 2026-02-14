# Database Migration Guide

## Quick Start

### 1. Set Up Migration System (One Time)

```bash
# Create migrations tracking table
mysql -u root -p tabulation_system < database/migrations_table.sql
```

Or run the migration script once - it will create the table automatically.

### 2. Create a New Migration

```bash
php migrate.php --create=add_new_feature_table
```

This creates a file like: `database/migrations/2024_01_15_143022_add_new_feature_table.sql`

### 3. Edit the Migration File

Open the created file and add your SQL:

```sql
-- Migration: add_new_feature_table
-- Created: 2024-01-15 14:30:22

CREATE TABLE IF NOT EXISTS `new_feature` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 4. Run the Migration

```bash
php migrate.php
```

## Common Commands

### Check Migration Status

```bash
php migrate.php --status
```

Shows:
- Which migrations have been executed
- Which migrations are pending
- Batch numbers

### Run Pending Migrations

```bash
php migrate.php
```

Automatically runs all pending migrations in order.

### Fresh Start (Development Only!)

```bash
php migrate.php --fresh
```

⚠️ **WARNING**: This drops ALL tables and re-runs all migrations. Use only in development!

### Rollback Last Batch

```bash
php migrate.php --rollback
```

Removes the migration records from the last batch. Note: This does NOT automatically reverse schema changes - you may need to manually drop tables/columns.

## Migration Examples

### Adding a New Table

```sql
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `message` TEXT NOT NULL,
  `is_read` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Adding Columns to Existing Table

```sql
ALTER TABLE `users` 
ADD COLUMN `phone` VARCHAR(20) NULL AFTER `email`,
ADD COLUMN `avatar` VARCHAR(255) NULL AFTER `phone`;
```

### Adding Indexes

```sql
ALTER TABLE `scores` 
ADD INDEX `idx_contestant_round` (`contestant_id`, `round_id`);
```

### Creating Views

```sql
CREATE OR REPLACE VIEW `judge_scores_summary` AS
SELECT 
    j.id as judge_id,
    u.full_name as judge_name,
    COUNT(DISTINCT s.contestant_id) as contestants_scored,
    AVG(s.total_score) as average_score
FROM judges j
JOIN users u ON j.user_id = u.id
LEFT JOIN scores s ON j.id = s.judge_id
GROUP BY j.id, u.full_name;
```

## Best Practices

1. **Always use IF NOT EXISTS** for CREATE statements
2. **Test on development first** before running on production
3. **One logical change per migration** - don't mix unrelated changes
4. **Use descriptive names** - `add_user_preferences_table` not `migration_1`
5. **Don't modify executed migrations** - create a new migration to fix issues
6. **Backup before major migrations** - especially in production

## Troubleshooting

### Migration Fails

If a migration fails:
1. Check the error message
2. Fix the SQL in the migration file
3. The batch is automatically rolled back
4. Fix the issue and run `php migrate.php` again

### Migration Already Executed

If you need to modify a migration that's already been run:
1. Don't edit the existing migration file
2. Create a new migration to make the changes
3. Example: If `001_add_table.sql` was run, create `002_modify_table.sql`

### Reset Everything (Development)

```bash
# Drop all tables and start fresh
php migrate.php --fresh
```

## Integration with Existing Schema

The migration system works alongside your existing `schema.sql` file:

- **Initial Setup**: Use `schema.sql` for fresh installations
- **Updates**: Use migrations for incremental changes
- **Both can coexist**: Migrations track what's been applied

## Production Deployment

For production:

1. **Backup the database first**
2. Test migrations on staging
3. Run `php migrate.php --status` to see what will run
4. Run `php migrate.php` to apply migrations
5. Verify the changes

## Migration File Structure

```
database/
├── migrations/
│   ├── 2024_01_15_143022_add_notifications.sql
│   ├── 2024_01_20_091500_add_user_preferences.sql
│   └── README.md
├── migrations_table.sql
└── schema.sql
```

## Advanced Usage

### Custom Migration Scripts

You can also create PHP migration scripts for complex logic:

```php
// database/migrations/2024_01_15_143022_complex_migration.php
<?php
$db = Database::getInstance();

// Complex logic here
$users = $db->fetchAll("SELECT * FROM users");
foreach ($users as $user) {
    // Process each user
}
```

But SQL migrations are recommended for most cases.

## Need Help?

- Check `database/migrations/README.md` for more examples
- Review existing migration files for patterns
- Test on development database first



