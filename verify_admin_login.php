<?php
/**
 * Quick Admin Login Verification
 * Run this to verify admin login is working
 */

require_once __DIR__ . '/config/database.php';

try {
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare("SELECT u.*, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.username = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin) {
        echo "❌ ERROR: Admin user not found!\n";
        echo "Run: php diagnose_auth.php to create/fix the admin user.\n";
        exit(1);
    }
    
    echo "✓ Admin user found\n";
    echo "  Username: " . $admin['username'] . "\n";
    echo "  Active: " . ($admin['is_active'] ? 'Yes' : 'No') . "\n";
    echo "  Role: " . $admin['role_name'] . "\n\n";
    
    if (!$admin['is_active']) {
        echo "⚠ WARNING: Admin is inactive! Activating...\n";
        $pdo->prepare("UPDATE users SET is_active = 1 WHERE username = 'admin'")->execute();
        echo "✓ Admin activated\n\n";
    }
    
    if (password_verify('admin123', $admin['password_hash'])) {
        echo "✅ SUCCESS: Password verification passed!\n";
        echo "You can login with:\n";
        echo "  Username: admin\n";
        echo "  Password: admin123\n\n";
        echo "Login URL: http://localhost/tabulation/login\n";
        exit(0);
    } else {
        echo "❌ ERROR: Password verification failed!\n";
        echo "Run: php diagnose_auth.php to fix the password.\n";
        exit(1);
    }
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
    exit(1);
}

