<?php
/**
 * Fix Admin Account - Ensure admin is active
 */

require_once __DIR__ . '/core/Database.php';

$db = Database::getInstance();

echo "========================================\n";
echo "Fix Admin Account Status\n";
echo "========================================\n\n";

// Activate admin user
$result = $db->query(
    "UPDATE users SET is_active = 1 WHERE username = 'admin'"
);

$admin = $db->fetchOne("SELECT * FROM users WHERE username = 'admin'");

if ($admin) {
    echo "✓ Admin account status updated\n";
    echo "  Username: {$admin['username']}\n";
    echo "  Active: " . ($admin['is_active'] ? 'Yes' : 'No') . "\n";
    echo "  Role ID: {$admin['role_id']}\n\n";
    
    if (!$admin['is_active']) {
        echo "⚠ Still inactive! Trying again...\n";
        $db->query("UPDATE users SET is_active = 1 WHERE username = 'admin'");
        echo "✓ Activated\n\n";
    }
    
    echo "You can now login with:\n";
    echo "  Username: admin\n";
    echo "  Password: admin123\n\n";
} else {
    echo "✗ Admin user not found!\n";
    echo "Run: php diagnose_auth.php\n";
}



