<?php
require_once 'core/Database.php';

$db = Database::getInstance();

echo "=== Checking All Events ===\n";

$allEvents = $db->fetchAll("SELECT id, name FROM events ORDER BY id");
foreach ($allEvents as $event) {
    echo "Event {$event['id']}: {$event['name']}\n";
}

echo "\n=== Checking if Event 16 exists ===\n";
$event16 = $db->fetchOne("SELECT * FROM events WHERE id = 16");
if ($event16) {
    echo "Event 16 exists: {$event16['name']}\n";
} else {
    echo "Event 16 does not exist!\n";
}

echo "\n=== Checking Event 14 Status ===\n";

// Check Event 14 contestants
$contestants14 = $db->fetchAll("
    SELECT c.id, c.contestant_number, c.name, c.qualified_for_level_id
    FROM contestants c
    WHERE c.event_id = 14 AND c.status = 'Active'
    ORDER BY c.contestant_number
");

echo "Event 14 (QUEEN of PULUPANDAN) contestants:\n";
foreach ($contestants14 as $c) {
    echo "Contestant #{$c['contestant_number']} ({$c['name']}) - Qualified for Level ID: " . ($c['qualified_for_level_id'] ?? 'NULL') . "\n";
}

echo "\n=== Done ===\n";
