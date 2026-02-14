<?php
require_once 'core/Database.php';

$db = Database::getInstance();

// Check contestants qualified for Level 2 (assuming event ID 1)
echo "=== Checking Contestant Qualifications ===\n";

// Get all active contestants
$allContestants = $db->fetchAll("
    SELECT c.id, c.contestant_number, c.name, c.qualified_for_level_id
    FROM contestants c
    WHERE c.event_id = 1 AND c.status = 'Active'
    ORDER BY c.contestant_number
");

echo "\nAll Active Contestants:\n";
foreach ($allContestants as $c) {
    echo "Contestant #{$c['contestant_number']} ({$c['name']}) - Qualified for Level ID: " . ($c['qualified_for_level_id'] ?? 'NULL') . "\n";
}

// Check scores for Level 2 rounds
echo "\n=== Checking Scores for Level 2 ===\n";
$level2Rounds = $db->fetchAll("
    SELECT r.id, r.name
    FROM rounds r
    JOIN event_levels el ON r.level_id = el.id
    WHERE el.event_id = 1 AND el.order = 2
");

foreach ($level2Rounds as $round) {
    echo "\nRound: {$round['name']} (ID: {$round['id']})\n";
    
    $scores = $db->fetchAll("
        SELECT s.contestant_id, c.contestant_number, c.name, s.is_submitted
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

echo "\n=== Done ===\n";
