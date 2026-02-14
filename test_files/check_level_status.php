<?php
require_once 'core/Database.php';

$db = Database::getInstance();

echo "=== Checking Level Completion Status ===\n";

// Check Level 1 completion
$level1 = $db->fetchOne("
    SELECT el.id, el.advance_count, COUNT(r.id) as round_count
    FROM event_levels el
    LEFT JOIN rounds r ON el.id = r.level_id
    WHERE el.event_id = 1 AND el.order = 1
    GROUP BY el.id, el.advance_count
");

if ($level1) {
    echo "Level 1: {$level1['round_count']} rounds, advance_count: " . ($level1['advance_count'] ?? 'NULL') . "\n";
    
    // Check if Level 1 rounds have rankings
    $level1Rounds = $db->fetchAll("SELECT id FROM rounds WHERE level_id = ?", [$level1['id']]);
    foreach ($level1Rounds as $round) {
        $hasRankings = $db->fetchOne("SELECT COUNT(*) as count FROM rankings WHERE round_id = ?", [$round['id']]);
        echo "  Round {$round['id']}: " . ($hasRankings['count'] > 0 ? 'HAS' : 'NO') . " rankings\n";
    }
}

// Check Level 2 contestants
echo "\n=== Level 2 Contestants ===\n";
$level2Contestants = $db->fetchAll("
    SELECT c.id, c.contestant_number, c.name, c.qualified_for_level_id
    FROM contestants c
    WHERE c.event_id = 1 AND c.status = 'Active'
    ORDER BY c.contestant_number
");

foreach ($level2Contestants as $c) {
    echo "Contestant #{$c['contestant_number']} ({$c['name']}) - Qualified for Level ID: " . ($c['qualified_for_level_id'] ?? 'NULL') . "\n";
}

echo "\n=== Done ===\n";
