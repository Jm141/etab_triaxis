<?php
/**
 * Migration: Add elimination round functionality
 * Run this file to add the necessary database fields
 */

require_once __DIR__ . '/../../core/Database.php';

$db = Database::getInstance();

echo "Adding elimination round fields...\n";

try {
    $db->getConnection()->beginTransaction();
    
    // Check if is_elimination_round column exists
    $columns = $db->fetchAll("SHOW COLUMNS FROM rounds LIKE 'is_elimination_round'");
    if (empty($columns)) {
        $db->query("ALTER TABLE `rounds` ADD COLUMN `is_elimination_round` TINYINT(1) DEFAULT 0 COMMENT 'Whether this round is an elimination round' AFTER `status`");
        echo "  ✓ Added is_elimination_round to rounds table\n";
    } else {
        echo "  → is_elimination_round already exists\n";
    }
    
    // Check if advance_count column exists
    $columns = $db->fetchAll("SHOW COLUMNS FROM event_levels LIKE 'advance_count'");
    if (empty($columns)) {
        $db->query("ALTER TABLE `event_levels` ADD COLUMN `advance_count` INT(11) NULL COMMENT 'Number of contestants that advance to next level (NULL = all advance)' AFTER `status`");
        echo "  ✓ Added advance_count to event_levels table\n";
    } else {
        echo "  → advance_count already exists\n";
    }
    
    // Check if qualified_for_level_id column exists
    $columns = $db->fetchAll("SHOW COLUMNS FROM contestants LIKE 'qualified_for_level_id'");
    if (empty($columns)) {
        $db->query("ALTER TABLE `contestants` ADD COLUMN `qualified_for_level_id` INT(11) NULL COMMENT 'Level ID that this contestant qualified for' AFTER `status`");
        echo "  ✓ Added qualified_for_level_id to contestants table\n";
    } else {
        echo "  → qualified_for_level_id already exists\n";
    }
    
    // Add index if it doesn't exist
    $indexes = $db->fetchAll("SHOW INDEX FROM contestants WHERE Key_name = 'idx_qualified_for_level'");
    if (empty($indexes)) {
        $db->query("ALTER TABLE `contestants` ADD INDEX `idx_qualified_for_level` (`qualified_for_level_id`)");
        echo "  ✓ Added index for qualified_for_level_id\n";
    }
    
    $db->getConnection()->commit();
    echo "\n✓ Migration completed successfully!\n";
    
} catch (Exception $e) {
    $db->getConnection()->rollBack();
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
