<?php
/**
 * Database Migration Runner
 * 
 * Usage: php migrate.php [options]
 * 
 * Options:
 *   --fresh          Drop all tables and re-run all migrations
 *   --rollback       Rollback last batch of migrations
 *   --status         Show migration status
 *   --create=name    Create a new migration file
 */

require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/Session.php';

class MigrationRunner {
    private $db;
    private $migrationsDir;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->migrationsDir = __DIR__ . '/database/migrations';
        
        // Create migrations directory if it doesn't exist
        if (!is_dir($this->migrationsDir)) {
            mkdir($this->migrationsDir, 0755, true);
        }
    }
    
    public function ensureMigrationsTable() {
        // Check if migrations table exists
        $result = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM information_schema.tables 
             WHERE table_schema = DATABASE() AND table_name = 'migrations'"
        );
        
        if ($result['count'] == 0) {
            // Create migrations table
            $sql = file_get_contents(__DIR__ . '/database/migrations_table.sql');
            $this->db->getConnection()->exec($sql);
            echo "✓ Created migrations tracking table\n";
        }
    }
    
    public function getExecutedMigrations() {
        $this->ensureMigrationsTable();
        
        $migrations = $this->db->fetchAll(
            "SELECT migration_name FROM migrations ORDER BY executed_at"
        );
        
        return array_column($migrations, 'migration_name');
    }
    
    public function getMigrationFiles() {
        $files = glob($this->migrationsDir . '/*.sql');
        $migrations = [];
        
        foreach ($files as $file) {
            $name = basename($file, '.sql');
            $migrations[$name] = $file;
        }
        
        ksort($migrations);
        return $migrations;
    }
    
    public function run($fresh = false) {
        echo "========================================\n";
        echo "Database Migration Runner\n";
        echo "========================================\n\n";
        
        if ($fresh) {
            echo "⚠ WARNING: Fresh mode will drop all tables!\n";
            echo "Press Ctrl+C to cancel, or wait 3 seconds...\n";
            sleep(3);
            
            $this->dropAllTables();
        }
        
        $this->ensureMigrationsTable();
        
        $executed = $this->getExecutedMigrations();
        $files = $this->getMigrationFiles();
        
        $pending = array_diff(array_keys($files), $executed);
        
        if (empty($pending)) {
            echo "✓ No pending migrations\n";
            return;
        }
        
        echo "Found " . count($pending) . " pending migration(s)\n\n";
        
        // Get next batch number
        $maxBatch = $this->db->fetchOne("SELECT MAX(batch) as max_batch FROM migrations")['max_batch'] ?? 0;
        $batch = $maxBatch + 1;
        
        $this->db->getConnection()->beginTransaction();
        
        try {
            foreach ($pending as $migrationName) {
                echo "Running: {$migrationName}... ";
                
                $file = $files[$migrationName];
                $sql = file_get_contents($file);
                
                // Remove comments and split by semicolon
                $statements = array_filter(
                    array_map('trim', explode(';', $sql)),
                    function($stmt) {
                        return !empty($stmt) && 
                               !preg_match('/^--/', $stmt) && 
                               !preg_match('/^\/\*/', $stmt) &&
                               !preg_match('/^SET/', $stmt);
                    }
                );
                
                foreach ($statements as $statement) {
                    if (!empty(trim($statement))) {
                        $this->db->getConnection()->exec($statement . ';');
                    }
                }
                
                // Record migration
                $this->db->query(
                    "INSERT INTO migrations (migration_name, batch) VALUES (?, ?)",
                    [$migrationName, $batch]
                );
                
                echo "✓\n";
            }
            
            $this->db->getConnection()->commit();
            
            echo "\n✓ All migrations completed successfully!\n";
            echo "Batch: {$batch}\n";
            
        } catch (Exception $e) {
            $this->db->getConnection()->rollBack();
            echo "\n✗ Error: " . $e->getMessage() . "\n";
            echo "Migration batch rolled back.\n";
            exit(1);
        }
    }
    
    public function rollback() {
        echo "========================================\n";
        echo "Rollback Last Migration Batch\n";
        echo "========================================\n\n";
        
        $this->ensureMigrationsTable();
        
        $lastBatch = $this->db->fetchOne(
            "SELECT MAX(batch) as max_batch FROM migrations"
        )['max_batch'] ?? null;
        
        if (!$lastBatch) {
            echo "No migrations to rollback\n";
            return;
        }
        
        $migrations = $this->db->fetchAll(
            "SELECT migration_name FROM migrations WHERE batch = ? ORDER BY executed_at DESC",
            [$lastBatch]
        );
        
        if (empty($migrations)) {
            echo "No migrations in last batch\n";
            return;
        }
        
        echo "Rolling back batch {$lastBatch} (" . count($migrations) . " migration(s))...\n\n";
        
        echo "⚠ WARNING: This will remove migration records.\n";
        echo "You may need to manually reverse schema changes.\n";
        echo "Press Ctrl+C to cancel, or wait 3 seconds...\n";
        sleep(3);
        
        $this->db->query(
            "DELETE FROM migrations WHERE batch = ?",
            [$lastBatch]
        );
        
        echo "✓ Rolled back batch {$lastBatch}\n";
        echo "Note: Schema changes were not automatically reversed.\n";
        echo "You may need to manually drop tables or columns.\n";
    }
    
    public function status() {
        echo "========================================\n";
        echo "Migration Status\n";
        echo "========================================\n\n";
        
        $this->ensureMigrationsTable();
        
        $executed = $this->getExecutedMigrations();
        $files = $this->getMigrationFiles();
        
        echo "Executed Migrations: " . count($executed) . "\n";
        echo "Available Migrations: " . count($files) . "\n\n";
        
        if (empty($executed)) {
            echo "No migrations have been executed yet.\n\n";
        } else {
            echo "Executed:\n";
            foreach ($executed as $name) {
                $info = $this->db->fetchOne(
                    "SELECT batch, executed_at FROM migrations WHERE migration_name = ?",
                    [$name]
                );
                echo "  ✓ {$name} (Batch {$info['batch']}, {$info['executed_at']})\n";
            }
            echo "\n";
        }
        
        $pending = array_diff(array_keys($files), $executed);
        if (!empty($pending)) {
            echo "Pending:\n";
            foreach ($pending as $name) {
                echo "  ○ {$name}\n";
            }
        } else {
            echo "✓ All migrations are up to date\n";
        }
    }
    
    public function create($name) {
        if (empty($name)) {
            die("Migration name is required\n");
        }
        
        // Sanitize name
        $name = preg_replace('/[^a-zA-Z0-9_]/', '_', $name);
        $name = strtolower($name);
        
        $timestamp = date('Y_m_d_His');
        $filename = "{$timestamp}_{$name}.sql";
        $filepath = $this->migrationsDir . '/' . $filename;
        
        $template = "-- Migration: {$name}\n";
        $template .= "-- Created: " . date('Y-m-d H:i:s') . "\n\n";
        $template .= "-- Add your SQL statements below\n";
        $template .= "-- Example:\n";
        $template .= "-- CREATE TABLE IF NOT EXISTS `new_table` (\n";
        $template .= "--   `id` INT(11) NOT NULL AUTO_INCREMENT,\n";
        $template .= "--   `name` VARCHAR(255) NOT NULL,\n";
        $template .= "--   PRIMARY KEY (`id`)\n";
        $template .= "-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n";
        
        file_put_contents($filepath, $template);
        
        echo "✓ Created migration file: {$filename}\n";
        echo "Location: {$filepath}\n";
    }
    
    private function dropAllTables() {
        echo "Dropping all tables...\n";
        
        $tables = $this->db->fetchAll(
            "SELECT table_name FROM information_schema.tables 
             WHERE table_schema = DATABASE() 
             AND table_name != 'migrations'"
        );
        
        $this->db->getConnection()->exec("SET FOREIGN_KEY_CHECKS = 0");
        
        foreach ($tables as $table) {
            // Handle both lowercase and uppercase column names
            $tableName = $table['table_name'] ?? $table['TABLE_NAME'] ?? null;
            
            if (!$tableName) {
                // Try to get first value if key doesn't exist
                $tableName = reset($table);
            }
            
            if ($tableName) {
                echo "  Dropping {$tableName}... ";
                $this->db->getConnection()->exec("DROP TABLE IF EXISTS `{$tableName}`");
                echo "✓\n";
            }
        }
        
        $this->db->getConnection()->exec("SET FOREIGN_KEY_CHECKS = 1");
        
        // Clear migrations table
        $this->db->getConnection()->exec("TRUNCATE TABLE migrations");
        
        echo "✓ All tables dropped\n\n";
    }
}

// Main execution
$runner = new MigrationRunner();

$args = $argv;
array_shift($args); // Remove script name

if (empty($args)) {
    // Default: run pending migrations
    $runner->run();
} else {
    $command = $args[0];
    
    // Check if command contains = (for --create=name format)
    if (strpos($command, '=') !== false) {
        list($cmd, $value) = explode('=', $command, 2);
        $command = $cmd;
    } else {
        $value = $args[1] ?? null;
    }
    
    switch ($command) {
        case '--fresh':
            $runner->run(true);
            break;
            
        case '--rollback':
            $runner->rollback();
            break;
            
        case '--status':
            $runner->status();
            break;
            
        case '--create':
            if (empty($value)) {
                die("Usage: php migrate.php --create=migration_name\n");
            }
            $runner->create($value);
            break;
            
        default:
            echo "Usage: php migrate.php [options]\n\n";
            echo "Options:\n";
            echo "  (no args)          Run pending migrations\n";
            echo "  --fresh            Drop all tables and re-run all migrations\n";
            echo "  --rollback         Rollback last batch of migrations\n";
            echo "  --status           Show migration status\n";
            echo "  --create=name      Create a new migration file\n\n";
            echo "Examples:\n";
            echo "  php migrate.php\n";
            echo "  php migrate.php --status\n";
            echo "  php migrate.php --create=add_notifications_table\n";
            echo "  php migrate.php --fresh\n";
            break;
    }
}

