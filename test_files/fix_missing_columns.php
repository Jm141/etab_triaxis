<?php
/**
 * Fix Missing Permission Columns in Scores Table
 * Run this script to add missing columns if migration hasn't been run
 * 
 * Usage: php fix_missing_columns.php
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Database.php';

echo "========================================\n";
echo "Fixing Missing Permission Columns\n";
echo "========================================\n\n";

$db = Database::getInstance();

try {
    $db->getConnection()->beginTransaction();
    
    // Check if columns exist
    $columns = $db->fetchAll("SHOW COLUMNS FROM scores LIKE 'permission_request_status'");
    
    if (empty($columns)) {
        echo "Adding permission columns to scores table...\n";
        
        // Add permission_request_status column
        $db->query("
            ALTER TABLE `scores` 
            ADD COLUMN `permission_request_status` ENUM('none', 'judge_requested', 'admin_requested', 'granted', 'denied') DEFAULT 'none' 
            AFTER `admin_edit_allowed`
        ");
        echo "  ✓ Added permission_request_status column\n";
        
        // Add permission_requested_by column
        $db->query("
            ALTER TABLE `scores` 
            ADD COLUMN `permission_requested_by` INT(11) NULL 
            AFTER `permission_request_status`
        ");
        echo "  ✓ Added permission_requested_by column\n";
        
        // Add permission_requested_at column
        $db->query("
            ALTER TABLE `scores` 
            ADD COLUMN `permission_requested_at` TIMESTAMP NULL 
            AFTER `permission_requested_by`
        ");
        echo "  ✓ Added permission_requested_at column\n";
        
        // Add permission_responded_by column
        $db->query("
            ALTER TABLE `scores` 
            ADD COLUMN `permission_responded_by` INT(11) NULL 
            AFTER `permission_requested_at`
        ");
        echo "  ✓ Added permission_responded_by column\n";
        
        // Add permission_responded_at column
        $db->query("
            ALTER TABLE `scores` 
            ADD COLUMN `permission_responded_at` TIMESTAMP NULL 
            AFTER `permission_responded_by`
        ");
        echo "  ✓ Added permission_responded_at column\n";
        
        // Add foreign keys
        try {
            $db->query("
                ALTER TABLE `scores`
                ADD CONSTRAINT `fk_scores_permission_requested_by` 
                FOREIGN KEY (`permission_requested_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
            ");
            echo "  ✓ Added foreign key for permission_requested_by\n";
        } catch (Exception $e) {
            echo "  → Foreign key for permission_requested_by may already exist\n";
        }
        
        try {
            $db->query("
                ALTER TABLE `scores`
                ADD CONSTRAINT `fk_scores_permission_responded_by` 
                FOREIGN KEY (`permission_responded_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
            ");
            echo "  ✓ Added foreign key for permission_responded_by\n";
        } catch (Exception $e) {
            echo "  → Foreign key for permission_responded_by may already exist\n";
        }
        
    } else {
        echo "Permission columns already exist. Skipping...\n";
    }
    
    // Check if is_draft column exists
    $draftColumn = $db->fetchAll("SHOW COLUMNS FROM scores LIKE 'is_draft'");
    
    if (empty($draftColumn)) {
        echo "\nAdding is_draft column to scores table...\n";
        $db->query("
            ALTER TABLE `scores`
            ADD COLUMN `is_draft` TINYINT(1) DEFAULT 1 
            COMMENT '1 = draft (auto-saved), 0 = final (submitted)' 
            AFTER `is_submitted`
        ");
        echo "  ✓ Added is_draft column\n";
    } else {
        echo "is_draft column already exists. Skipping...\n";
    }
    
    // Check if notifications table exists
    $notificationsTable = $db->fetchOne("SHOW TABLES LIKE 'notifications'");
    
    if (!$notificationsTable) {
        echo "\nCreating notifications table...\n";
        $db->query("
            CREATE TABLE IF NOT EXISTS `notifications` (
              `id` INT(11) NOT NULL AUTO_INCREMENT,
              `user_id` INT(11) NOT NULL COMMENT 'User who will receive the notification',
              `type` VARCHAR(50) NOT NULL COMMENT 'Type: permission_request, permission_granted, permission_denied, score_edited',
              `title` VARCHAR(255) NOT NULL,
              `message` TEXT,
              `related_type` VARCHAR(50) COMMENT 'Type of related entity: score, etc.',
              `related_id` INT(11) COMMENT 'ID of related entity',
              `is_read` TINYINT(1) DEFAULT 0,
              `read_at` TIMESTAMP NULL,
              `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
              INDEX `idx_user_id` (`user_id`),
              INDEX `idx_is_read` (`is_read`),
              INDEX `idx_type` (`type`),
              INDEX `idx_created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        echo "  ✓ Created notifications table\n";
    } else {
        echo "notifications table already exists. Skipping...\n";
    }
    
    $db->getConnection()->commit();
    
    echo "\n========================================\n";
    echo "✅ Fix Complete!\n";
    echo "========================================\n\n";
    echo "All required columns and tables have been added.\n";
    echo "You can now view scores as Event Organizer/Admin.\n\n";
    
} catch (Exception $e) {
    try {
        $db->getConnection()->rollBack();
    } catch (Exception $rollbackError) {
        // Transaction may already be committed, ignore rollback error
    }
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

