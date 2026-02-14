<?php
/**
 * Migration: Add deduction permission request fields to scores table
 */

require_once __DIR__ . '/../../core/Database.php';

$db = Database::getInstance();

try {
    $db->getConnection()->beginTransaction();
    
    // Check if columns exist, if not add them
    $columns = $db->fetchAll("SHOW COLUMNS FROM scores LIKE 'deduction_request_status'");
    if (empty($columns)) {
        // Check if deduction_reason exists, if not add after point_deduction
        $deductionReasonExists = $db->fetchAll("SHOW COLUMNS FROM scores LIKE 'deduction_reason'");
        if (!empty($deductionReasonExists)) {
            $db->query("ALTER TABLE scores 
                        ADD COLUMN deduction_request_status ENUM('none', 'requested', 'granted', 'denied', 'applied') DEFAULT 'none' 
                        AFTER deduction_reason");
        } else {
            // Check if point_deduction exists
            $pointDeductionExists = $db->fetchAll("SHOW COLUMNS FROM scores LIKE 'point_deduction'");
            if (!empty($pointDeductionExists)) {
                $db->query("ALTER TABLE scores 
                            ADD COLUMN deduction_request_status ENUM('none', 'requested', 'granted', 'denied', 'applied') DEFAULT 'none' 
                            AFTER point_deduction");
            } else {
                $db->query("ALTER TABLE scores 
                            ADD COLUMN deduction_request_status ENUM('none', 'requested', 'granted', 'denied', 'applied') DEFAULT 'none'");
            }
        }
        echo "Added deduction_request_status column\n";
    } else {
        echo "deduction_request_status column already exists\n";
    }
    
    $columns = $db->fetchAll("SHOW COLUMNS FROM scores LIKE 'deduction_requested_by'");
    if (empty($columns)) {
        $db->query("ALTER TABLE scores 
                    ADD COLUMN deduction_requested_by INT(11) NULL 
                    AFTER deduction_request_status,
                    ADD FOREIGN KEY (deduction_requested_by) REFERENCES users(id) ON DELETE SET NULL");
        echo "Added deduction_requested_by column\n";
    } else {
        echo "deduction_requested_by column already exists\n";
    }
    
    $columns = $db->fetchAll("SHOW COLUMNS FROM scores LIKE 'deduction_requested_at'");
    if (empty($columns)) {
        $db->query("ALTER TABLE scores 
                    ADD COLUMN deduction_requested_at TIMESTAMP NULL 
                    AFTER deduction_requested_by");
        echo "Added deduction_requested_at column\n";
    } else {
        echo "deduction_requested_at column already exists\n";
    }
    
    $columns = $db->fetchAll("SHOW COLUMNS FROM scores LIKE 'deduction_responded_by'");
    if (empty($columns)) {
        $db->query("ALTER TABLE scores 
                    ADD COLUMN deduction_responded_by INT(11) NULL 
                    AFTER deduction_requested_at,
                    ADD FOREIGN KEY (deduction_responded_by) REFERENCES users(id) ON DELETE SET NULL");
        echo "Added deduction_responded_by column\n";
    } else {
        echo "deduction_responded_by column already exists\n";
    }
    
    $columns = $db->fetchAll("SHOW COLUMNS FROM scores LIKE 'deduction_responded_at'");
    if (empty($columns)) {
        $db->query("ALTER TABLE scores 
                    ADD COLUMN deduction_responded_at TIMESTAMP NULL 
                    AFTER deduction_responded_by");
        echo "Added deduction_responded_at column\n";
    } else {
        echo "deduction_responded_at column already exists\n";
    }
    
    $columns = $db->fetchAll("SHOW COLUMNS FROM scores LIKE 'deduction_pending_amount'");
    if (empty($columns)) {
        $db->query("ALTER TABLE scores 
                    ADD COLUMN deduction_pending_amount DECIMAL(10,3) NULL 
                    AFTER deduction_responded_at");
        echo "Added deduction_pending_amount column\n";
    } else {
        echo "deduction_pending_amount column already exists\n";
    }
    
    $columns = $db->fetchAll("SHOW COLUMNS FROM scores LIKE 'deduction_pending_reason'");
    if (empty($columns)) {
        $db->query("ALTER TABLE scores 
                    ADD COLUMN deduction_pending_reason TEXT NULL 
                    AFTER deduction_pending_amount");
        echo "Added deduction_pending_reason column\n";
    } else {
        echo "deduction_pending_reason column already exists\n";
    }
    
    $db->getConnection()->commit();
    echo "\nMigration completed successfully!\n";
    
} catch (Exception $e) {
    if ($db->getConnection()->inTransaction()) {
        $db->getConnection()->rollBack();
    }
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
