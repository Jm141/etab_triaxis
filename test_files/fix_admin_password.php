<?php
/**
 * Fix Admin Password Script
 * Run this to set the correct admin password
 */

require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/Session.php';

$db = Database::getInstance();

// Generate password hash for 'admin123'
$password = 'admin123';
$hash = password_hash($password, PASSWORD_BCRYPT);

echo "========================================\n";
echo "Fix Admin Password Script\n";
echo "========================================\n\n";
echo "Password Hash for 'admin123':\n";
echo $hash . "\n\n";

// Update admin user
try {
    $result = $db->query(
        "UPDATE users SET password_hash = ? WHERE username = 'admin'",
        [$hash]
    );
    
    echo "✓ Admin password updated successfully!\n";
    echo "You can now login with:\n";
    echo "  Username: admin\n";
    echo "  Password: admin123\n\n";
    
    // Verify
    $user = $db->fetchOne("SELECT username, password_hash FROM users WHERE username = 'admin'");
    if ($user && password_verify('admin123', $user['password_hash'])) {
        echo "✓ Password verification successful!\n";
    } else {
        echo "✗ Password verification failed!\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

