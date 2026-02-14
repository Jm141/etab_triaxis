<?php
require_once 'core/Database.php';

$db = Database::getInstance();

echo "=== Event 17: rew ===\n";

// Get levels
$levels = $db->fetchAll("
    SELECT el.id, el.name, el.order, el.advance_count
    FROM event_levels el
    WHERE el.event_id = 17
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

// Check contestants
echo "\n=== Contestants ===\n";
$contestants = $db->fetchAll("
    SELECT c.id, c.contestant_number, c.name, c.qualified_for_level_id
    FROM contestants c
    WHERE c.event_id = 17 AND c.status = 'Active'
    ORDER BY c.contestant_number
");

foreach ($contestants as $c) {
    echo "Contestant #{$c['contestant_number']} ({$c['name']}) - Qualified for Level ID: " . ($c['qualified_for_level_id'] ?? 'NULL') . "\n";
}

echo "\n=== Checking Level 2 Scores ===\n";

// Get Level 2 ID
$level2 = $db->fetchOne("SELECT id FROM event_levels WHERE event_id = 17 AND `order` = 2");
if ($level2) {
    $level2Rounds = $db->fetchAll("SELECT id, name FROM rounds WHERE level_id = ?", [$level2['id']]);
    
    foreach ($level2Rounds as $round) {
        echo "\nRound {$round['id']} ({$round['name']}):\n";
        
        // Check scores for this round
        $scores = $db->fetchAll("
            SELECT s.contestant_id, c.contestestant_number, c.name, s.is_submitted
            FROM scores s
            JOIN contestants c ON s.contestant_id = c.id
            WHERE s.round_id = ?
            GROUP BY s.contestant_id, c.contestant_number, c.name, s.is_submitted
            ORDER BY c.contestant_number
        ", [$round['id']]);
        
        foreach ($scores as $score) {
            echo "  Contestant #{$score['contestant_number']} ({$score['name']}) - Submitted: " . ($score['is_submitted'] ? 'YES' : 'NO') . "\n";
        }
    }
}

echo "\n=== Done ===\n";
