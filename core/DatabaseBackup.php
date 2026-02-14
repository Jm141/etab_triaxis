<?php
/**
 * Database Backup Utility
 * Creates backups when events are finished
 */

class DatabaseBackup {
    
    private $db;
    private $backupDir;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->backupDir = __DIR__ . '/../backups';
        
        // Create backups directory if it doesn't exist
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }
    
    /**
     * Create a backup for a specific event
     * @param int $eventId The event ID
     * @return string|false Backup file path on success, false on failure
     */
    public function createEventBackup($eventId) {
        try {
            // Get event information
            $event = $this->db->fetchOne(
                "SELECT * FROM events WHERE id = ?",
                [$eventId]
            );
            
            if (!$event) {
                error_log("DatabaseBackup: Event ID {$eventId} not found");
                return false;
            }
            
            // Check if backup already exists for this event
            $existingBackup = $this->getEventBackup($eventId);
            if ($existingBackup) {
                error_log("DatabaseBackup: Backup already exists for event ID {$eventId}");
                return $existingBackup;
            }
            
            // Get database configuration
            $dbConfig = require __DIR__ . '/../config/database.php';
            $dbName = $dbConfig['dbname'] ?? $dbConfig['database'] ?? 'tabulation_system';
            $dbHost = $dbConfig['host'] ?? 'localhost';
            $dbUser = $dbConfig['username'] ?? 'root';
            $dbPass = $dbConfig['password'] ?? '';
            
            // Create backup filename with event name and timestamp
            $eventName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $event['name']);
            $timestamp = date('Y-m-d_His');
            $filename = "event_{$eventId}_{$eventName}_{$timestamp}.sql";
            $filepath = $this->backupDir . '/' . $filename;
            
            // Build mysqldump command
            // On Windows, use full path to mysqldump if available
            $mysqldump = 'mysqldump';
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                // Try common MySQL paths on Windows
                $mysqlPaths = [
                    'C:\\xampp\\mysql\\bin\\mysqldump.exe',
                    'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe',
                    'C:\\Program Files\\MySQL\\MySQL Server 5.7\\bin\\mysqldump.exe',
                    'mysqldump' // Fallback to PATH
                ];
                
                foreach ($mysqlPaths as $path) {
                    if (file_exists($path) || $path === 'mysqldump') {
                        $mysqldump = $path;
                        break;
                    }
                }
            }
            
            // Build command
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $command = sprintf(
                    '"%s" -h%s -u%s -p%s %s %s > %s 2>&1',
                    $mysqldump,
                    escapeshellarg($dbHost),
                    escapeshellarg($dbUser),
                    escapeshellarg($dbPass),
                    escapeshellarg($dbName),
                    $this->getEventTables(),
                    escapeshellarg($filepath)
                );
            } else {
                $command = sprintf(
                    '%s -h%s -u%s -p%s %s %s > %s 2>&1',
                    escapeshellarg($mysqldump),
                    escapeshellarg($dbHost),
                    escapeshellarg($dbUser),
                    escapeshellarg($dbPass),
                    escapeshellarg($dbName),
                    $this->getEventTables(),
                    escapeshellarg($filepath)
                );
            }
            
            // Execute backup
            exec($command, $output, $returnVar);
            
            if ($returnVar !== 0) {
                error_log("DatabaseBackup: mysqldump failed for event ID {$eventId}. Output: " . implode("\n", $output));
                return false;
            }
            
            // Verify backup file was created and has content
            if (!file_exists($filepath) || filesize($filepath) < 100) {
                error_log("DatabaseBackup: Backup file appears to be empty or missing for event ID {$eventId}");
                return false;
            }
            
            // Create backup record in database
            $this->db->query(
                "INSERT INTO event_backups (event_id, backup_file, backup_size, created_at)
                 VALUES (?, ?, ?, NOW())",
                [$eventId, $filename, filesize($filepath)]
            );
            
            error_log("DatabaseBackup: Successfully created backup for event ID {$eventId}: {$filename}");
            return $filepath;
            
        } catch (Exception $e) {
            error_log("DatabaseBackup: Exception creating backup for event ID {$eventId}: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get list of tables related to an event
     * @return string Space-separated table names for mysqldump
     */
    private function getEventTables() {
        // List of tables that contain event data
        $tables = [
            'events',
            'event_levels',
            'rounds',
            'criteria',
            'criteria_weights',
            'judges',
            'judge_assignments',
            'contestants',
            'scores',
            'score_details',
            'notifications',
            'user_event_assignments',
            'scoring_formula',
            'event_backups'
        ];
        
        return implode(' ', $tables);
    }
    
    /**
     * Get existing backup for an event
     * @param int $eventId The event ID
     * @return string|false Backup file path if exists, false otherwise
     */
    public function getEventBackup($eventId) {
        $backup = $this->db->fetchOne(
            "SELECT backup_file FROM event_backups WHERE event_id = ? ORDER BY created_at DESC LIMIT 1",
            [$eventId]
        );
        
        if ($backup && file_exists($this->backupDir . '/' . $backup['backup_file'])) {
            return $this->backupDir . '/' . $backup['backup_file'];
        }
        
        return false;
    }
    
    /**
     * List all backups for an event
     * @param int $eventId The event ID
     * @return array List of backup records
     */
    public function listEventBackups($eventId) {
        return $this->db->fetchAll(
            "SELECT * FROM event_backups WHERE event_id = ? ORDER BY created_at DESC",
            [$eventId]
        );
    }
    
    /**
     * Delete old backups (keep only the latest N backups per event)
     * @param int $keepCount Number of backups to keep per event (default: 3)
     * @return int Number of backups deleted
     */
    public function cleanupOldBackups($keepCount = 3) {
        $deleted = 0;
        
        // Get all events with backups
        $events = $this->db->fetchAll(
            "SELECT DISTINCT event_id FROM event_backups"
        );
        
        foreach ($events as $event) {
            // Get backups for this event, ordered by date (newest first)
            $backups = $this->db->fetchAll(
                "SELECT * FROM event_backups 
                 WHERE event_id = ? 
                 ORDER BY created_at DESC",
                [$event['event_id']]
            );
            
            // Delete backups beyond the keep count
            if (count($backups) > $keepCount) {
                $toDelete = array_slice($backups, $keepCount);
                
                foreach ($toDelete as $backup) {
                    $filepath = $this->backupDir . '/' . $backup['backup_file'];
                    if (file_exists($filepath)) {
                        unlink($filepath);
                    }
                    $this->db->query(
                        "DELETE FROM event_backups WHERE id = ?",
                        [$backup['id']]
                    );
                    $deleted++;
                }
            }
        }
        
        return $deleted;
    }
}
