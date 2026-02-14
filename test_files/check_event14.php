<?php
require_once 'core/Database.php';

$db = Database::getInstance();

echo "=== Current Events ===\n";

$events = $db->fetchAll("SELECT id, name FROM events ORDER BY id");
foreach ($events as $event) {
    echo "Event {$event['id']}: {$event['name']}\n";
}

echo "\n=== Event 14: QUEEN of PULUPANDAN Details ===\n";

// Check levels
$levels = $db->fetchAll("
    SELECT el.id, el.name, el.order, el.advance_count
    FROM event_levels el
    WHERE el.event_id = 14
    ORDER BY el.order
");

foreach ($levels as $level) {
    echo "Level {$level['order']}: {$level['name']} (ID: {$level['id']}) - advance_count: " . ($level['advance_count'] ?? 'NULL') . "\n";
    
    // Check rounds and rankings
    $rounds = $db->fetchAll("SELECT id, name FROM rounds WHERE level_id = ?", [$level['id']]);
    foreach ($rounds as $round) {
        $hasRankings = $db->fetchOne("SELECT COUNT(*) as count FROM rankings WHERE round_id = ?", [$round['id']]);
        echo "  Round {$round['id']} ({$round['name']}): " . ($hasRankings['count'] > 0 ? 'HAS' : 'NO') . " rankings\n";
    }
}

echo "\n=== Event 14 Contestants ===\n";
$contestants = $db->fetchAll("
    SELECT c.id, c.contestant_number, c.name, c.qualified_for_level_id
    FROM contestants c
    WHERE c.event_id = 14 AND c.status = 'Active'
    ORDER BY c.contestant_number
");

foreach ($contestants as $c) {
    echo "Contestant #{$c['contestant_number']} ({$c['name']}) - Qualified for Level ID: " . ($c['qualified_for_level_id'] ?? 'NULL') . "\n";
}

echo "\n=== Done ===\n";
