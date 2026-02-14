<?php
require_once 'core/Database.php';

$db = Database::getInstance();

echo "=== Deleting All Events Except 14 and 16 ===\n";

// Get all events except 14 and 16
$eventsToDelete = $db->fetchAll("
    SELECT id, name FROM events 
    WHERE id NOT IN (14, 16)
    ORDER BY id
");

echo "Events to delete:\n";
foreach ($eventsToDelete as $event) {
    echo "Event {$event['id']}: {$event['name']}\n";
}

// Delete events (this will cascade delete related data)
foreach ($eventsToDelete as $event) {
    echo "\nDeleting Event {$event['id']}...\n";
    $db->query("DELETE FROM events WHERE id = ?", [$event['id']]);
    echo "Event {$event['id']} deleted.\n";
}

echo "\n=== Checking Remaining Events ===\n";

$remainingEvents = $db->fetchAll("SELECT id, name FROM events ORDER BY id");
foreach ($remainingEvents as $event) {
    echo "Event {$event['id']}: {$event['name']}\n";
}

echo "\n=== Checking Event 16 Status ===\n";

// Check Event 16 contestants
$contestants = $db->fetchAll("
    SELECT c.id, c.contestant_number, c.name, c.qualified_for_level_id
    FROM contestants c
    WHERE c.event_id = 16 AND c.status = 'Active'
    ORDER BY c.contestant_number
");

foreach ($contestants as $c) {
    echo "Contestant #{$c['contestant_number']} ({$c['name']}) - Qualified for Level ID: " . ($c['qualified_for_level_id'] ?? 'NULL') . "\n";
}

echo "\n=== Done ===\n";
