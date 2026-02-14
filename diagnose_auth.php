<?php
/**
 * Authentication Diagnostic Script
 * Run this to check and fix login issues
 */

echo "========================================\n";
echo "Authentication Diagnostic Tool\n";
echo "========================================\n\n";

// Load config
$config = require __DIR__ . '/config/database.php';

try {
    // Connect to database
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ Database connection successful\n\n";
    
    // Check if admin user exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin) {
        echo "✗ Admin user NOT FOUND in database!\n";
        echo "Creating admin user...\n";
        
        // Check if roles table exists and has Super Admin
        $stmt = $pdo->prepare("SELECT id FROM roles WHERE name = 'Super Admin'");
        $stmt->execute();
        $role = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$role) {
            echo "✗ Super Admin role not found! Creating roles first...\n";
            // This should have been created by schema, but let's handle it
            $pdo->exec("INSERT INTO roles (name, description, permissions) VALUES ('Super Admin', 'Full system access', '[\"*\"]')");
            $roleId = $pdo->lastInsertId();
        } else {
            $roleId = $role['id'];
        }
        
        // Create admin user
        $hash = password_hash('admin123', PASSWORD_BCRYPT);
        $pdo->prepare("INSERT INTO users (username, email, password_hash, full_name, role_id) VALUES (?, ?, ?, ?, ?)")
            ->execute(['admin', 'admin@tabulation.local', $hash, 'System Administrator', $roleId]);
        
        echo "✓ Admin user created!\n";
        echo "  Username: admin\n";
        echo "  Password: admin123\n\n";
        
    } else {
        echo "✓ Admin user found\n";
        echo "  Username: " . $admin['username'] . "\n";
        echo "  Email: " . $admin['email'] . "\n";
        echo "  Active: " . ($admin['is_active'] ? 'Yes' : 'No') . "\n";
        echo "  Role ID: " . $admin['role_id'] . "\n\n";
        
        // Check if user is active
        if (!$admin['is_active']) {
            echo "⚠ WARNING: Admin user is INACTIVE!\n";
            echo "Activating user...\n";
            $pdo->prepare("UPDATE users SET is_active = 1 WHERE username = 'admin'")->execute();
            echo "✓ User activated\n\n";
        }
        
        // Test password
        echo "Testing password 'admin123'...\n";
        if (password_verify('admin123', $admin['password_hash'])) {
            echo "✓ Password verification SUCCESSFUL!\n";
            echo "Login should work with: admin / admin123\n\n";
        } else {
            echo "✗ Password verification FAILED!\n";
            echo "Current hash: " . substr($admin['password_hash'], 0, 30) . "...\n";
            echo "Fixing password hash...\n";
            
            $newHash = password_hash('admin123', PASSWORD_BCRYPT);
            $pdo->prepare("UPDATE users SET password_hash = ? WHERE username = 'admin'")
                ->execute([$newHash]);
            
            echo "✓ Password hash updated!\n";
            echo "New hash: " . substr($newHash, 0, 30) . "...\n";
            
            // Verify again
            if (password_verify('admin123', $newHash)) {
                echo "✓ New password hash verified!\n";
                echo "You can now login with: admin / admin123\n\n";
            } else {
                echo "✗ ERROR: New hash verification failed (this shouldn't happen!)\n\n";
            }
        }
        
        // Check role
        $stmt = $pdo->prepare("SELECT * FROM roles WHERE id = ?");
        $stmt->execute([$admin['role_id']]);
        $role = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($role) {
            echo "✓ Role found: " . $role['name'] . "\n";
        } else {
            echo "✗ Role not found! This will cause login issues.\n";
        }
    }
    
    echo "\n========================================\n";
    echo "Diagnosis complete!\n";
    echo "========================================\n";
    echo "\nTry logging in now at: http://localhost/tabulation/login\n";
    echo "Username: admin\n";
    echo "Password: admin123\n\n";
    
} catch (PDOException $e) {
    echo "✗ Database Error: " . $e->getMessage() . "\n";
    echo "\nPlease check:\n";
    echo "1. MySQL service is running\n";
    echo "2. Database 'tabulation_system' exists\n";
    echo "3. Credentials in config/database.php are correct\n";
    echo "4. Run database/schema.sql to create tables\n\n";
}



