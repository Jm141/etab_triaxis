# Database Backups

This directory contains automatic database backups created when events are finished.

## How It Works

- When an event status changes to "Finished", an automatic backup is created
- Backups are stored with the format: `event_{eventId}_{eventName}_{timestamp}.sql`
- Each backup is recorded in the `event_backups` table

## Backup Contents

Backups include all event-related tables:
- events
- event_levels
- rounds
- criteria
- criteria_weights
- judges
- judge_assignments
- contestants
- scores
- score_details
- notifications
- user_event_assignments
- scoring_formula
- event_backups

## Restoring a Backup

To restore a backup, use MySQL command line:

```bash
mysql -u root -p tabulation_system < backups/event_1_EventName_2024-01-15_143022.sql
```

Or using phpMyAdmin:
1. Select your database
2. Go to Import tab
3. Choose the backup file
4. Click Go

## Manual Backup

You can also create a manual backup using the DatabaseBackup class:

```php
require_once __DIR__ . '/core/DatabaseBackup.php';
$backup = new DatabaseBackup();
$backup->createEventBackup($eventId);
```

## Cleanup

The system automatically keeps the latest 3 backups per event. Older backups can be manually deleted if needed.
