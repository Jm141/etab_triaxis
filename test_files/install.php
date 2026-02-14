<?php
/**
 * Installation Helper Script
 * Run this once to set up the database
 */

// Check if already installed
if (file_exists(__DIR__ . '/.installed')) {
    die("System is already installed. Delete .installed file to reinstall.");
}

echo "Tabulation System Installation\n";
echo "==============================\n\n";

// Load database config
$config = require __DIR__ . '/config/database.php';

try {
    // Connect without database first
    $dsn = "mysql:host={$config['host']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ Connected to MySQL server\n";
    
    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$config['dbname']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ Database '{$config['dbname']}' created\n";
    
    // Select database
    $pdo->exec("USE `{$config['dbname']}`");
    
    // Read and execute schema
    $schema = file_get_contents(__DIR__ . '/database/schema.sql');
    
    // Remove comments and split by semicolon
    $statements = array_filter(
        array_map('trim', explode(';', $schema)),
        function($stmt) {
            return !empty($stmt) && 
                   !preg_match('/^--/', $stmt) && 
                   !preg_match('/^SET/', $stmt) &&
                   !preg_match('/^\/\*/', $stmt);
        }
    );
    
    foreach ($statements as $statement) {
        if (!empty(trim($statement))) {
            try {
                $pdo->exec($statement);
            } catch (PDOException $e) {
                // Ignore "already exists" errors
                if (strpos($e->getMessage(), 'already exists') === false) {
                    echo "Warning: " . $e->getMessage() . "\n";
                }
            }
        }
    }
    
    echo "✓ Database schema imported\n";
    echo "✓ Installation complete!\n\n";
    
    echo "Default Login Credentials:\n";
    echo "  Username: admin\n";
    echo "  Password: admin123\n\n";
    echo "⚠️  IMPORTANT: Change the default password immediately!\n\n";
    
    // Create .installed file
    file_put_contents(__DIR__ . '/.installed', date('Y-m-d H:i:s'));
    
    echo "You can now access the system at: http://localhost/tabulation\n";
    
} catch (PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
}



