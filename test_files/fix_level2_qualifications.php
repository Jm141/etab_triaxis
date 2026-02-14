<?php
require_once 'core/Database.php';

$db = Database::getInstance();

echo "=== Checking Event 17 Current Status ===\n";

// Check current qualifications
$contestants = $db->fetchAll("
    SELECT c.id, c.contestant_number, c.name, c.qualified_for_level_id
    FROM contestants c
    WHERE c.event_id = 17 AND c.status = 'Active'
    ORDER BY c.contestant_number
");

echo "Current qualifications:\n";
foreach ($contestants as $c) {
    echo "Contestant #{$c['contestant_number']} ({$c['name']}) - Qualified for Level ID: " . ($c['qualified_for_level_id'] ?? 'NULL') . "\n";
}

// Check Level 2 scores
echo "\n=== Checking Level 2 Scores ===\n";
$level2Scores = $db->fetchAll("
    SELECT s.contestant_id, c.contestant_number, c.name, COUNT(s.id) as score_count
    FROM scores s
    JOIN contestants c ON s.contestant_id = c.id
    JOIN rounds r ON s.round_id = r.id
    WHERE r.level_id = 49
    GROUP BY s.contestant_id, c.contestant_number, c.name
    ORDER BY c.contestant_number
");

foreach ($level2Scores as $score) {
    echo "Contestant #{$score['contestant_number']} ({$score['name']}) - Score entries: {$score['score_count']}\n";
}

echo "\n=== Fixing Qualifications ===\n";

// Reset: All 5 contestants should be qualified for Level 2 only
$db->query("UPDATE contestants SET qualified_for_level_id = 49 WHERE event_id = 17 AND id IN (1,2,3,4,5)");
$db->query("UPDATE contestants SET qualified_for_level_id = NULL WHERE event_id = 17 AND id IN (6,7)");

echo "Fixed: Contestants 1-5 qualified for Level 2, 6-7 eliminated\n";

// Check again
$contestants = $db->fetchAll("
    SELECT c.id, c.contestant_number, c.name, c.qualified_for_level_id
    FROM contestants c
    WHERE c.event_id = 17 AND c.status = 'Active'
    ORDER BY c.contestant_number
");

echo "\nUpdated qualifications:\n";
foreach ($contestants as $c) {
    echo "Contestant #{$c['contestant_number']} ({$c['name']}) - Qualified for Level ID: " . ($c['qualified_for_level_id'] ?? 'NULL') . "\n";
}

echo "\n=== Done ===\n";
