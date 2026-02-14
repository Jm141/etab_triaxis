<?php
require_once 'core/Database.php';

$db = Database::getInstance();

echo "=== Checking All Levels ===\n";

// Get all levels
$levels = $db->fetchAll("
    SELECT el.id, el.name, el.order, el.advance_count, COUNT(r.id) as round_count
    FROM event_levels el
    LEFT JOIN rounds r ON el.id = r.level_id
    WHERE el.event_id = 1
    GROUP BY el.id, el.name, el.order, el.advance_count
    ORDER BY el.order
");

foreach ($levels as $level) {
    echo "Level {$level['order']}: {$level['name']} (ID: {$level['id']}) - {$level['round_count']} rounds, advance_count: " . ($level['advance_count'] ?? 'NULL') . "\n";
    
    // Check if this level's rounds have rankings
    $rounds = $db->fetchAll("SELECT id FROM rounds WHERE level_id = ?", [$level['id']]);
    foreach ($rounds as $round) {
        $hasRankings = $db->fetchOne("SELECT COUNT(*) as count FROM rankings WHERE round_id = ?", [$round['id']]);
        echo "  Round {$round['id']}: " . ($hasRankings['count'] > 0 ? 'HAS' : 'NO') . " rankings\n";
    }
}

echo "\n=== Checking Contestant Qualifications ===\n";

// Check all contestants and their qualifications
$contestants = $db->fetchAll("
    SELECT c.id, c.contestant_number, c.name, c.qualified_for_level_id
    FROM contestants c
    WHERE c.event_id = 1 AND c.status = 'Active'
    ORDER BY c.contestant_number
");

foreach ($contestants as $c) {
    echo "Contestant #{$c['contestant_number']} ({$c['name']}) - Qualified for Level ID: " . ($c['qualified_for_level_id'] ?? 'NULL') . "\n";
}

echo "\n=== Done ===\n";
