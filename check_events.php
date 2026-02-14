<?php
require_once 'core/Database.php';

$db = Database::getInstance();

echo "=== Checking Events ===\n";

// Get all events
$events = $db->fetchAll("SELECT id, name FROM events ORDER BY id");
foreach ($events as $event) {
    echo "Event {$event['id']}: {$event['name']}\n";
}

echo "\n=== Checking Event Levels ===\n";

// Get all levels across all events
$levels = $db->fetchAll("
    SELECT el.id, el.name, el.order, el.advance_count, el.event_id, e.name as event_name
    FROM event_levels el
    JOIN events e ON el.event_id = e.id
    ORDER BY e.id, el.order
");

foreach ($levels as $level) {
    echo "Event {$level['event_id']} ({$level['event_name']}) - Level {$level['order']}: {$level['name']} (ID: {$level['id']}) - advance_count: " . ($level['advance_count'] ?? 'NULL') . "\n";
}

echo "\n=== Done ===\n";
