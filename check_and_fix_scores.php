<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Database.php';

$db = Database::getInstance();

// Check scores status
$stats = $db->fetchAll("SELECT is_submitted, COUNT(*) as count FROM scores GROUP BY is_submitted");
echo "Score Status:\n";
foreach ($stats as $stat) {
    echo "  is_submitted = " . ($stat['is_submitted'] ? '1' : '0') . ": " . $stat['count'] . " scores\n";
}

// Mark all scores as submitted
$stmt = $db->query("UPDATE scores SET is_submitted = 1 WHERE is_submitted = 0");
$updated = $stmt->rowCount();
echo "\nUpdated $updated scores to is_submitted = 1\n";
