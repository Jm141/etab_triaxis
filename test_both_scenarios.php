<?php
require_once 'core/Database.php';

$db = Database::getInstance();

echo "=== Testing Judge Scoring Interface - Both Scenarios ===\n";

// Get Level 2 info
$level2 = $db->fetchOne("SELECT * FROM event_levels WHERE event_id = 17 AND `order` = 2");
echo "Level 2: {$level2['name']} (ID: {$level2['id']})\n";

// Scenario 1: When scores exist (current situation)
echo "\n--- Scenario 1: Scores exist ---\n";
$contestantsWithScores = $db->fetchAll(
    "SELECT DISTINCT c.*
     FROM contestants c
     JOIN scores s ON c.id = s.contestant_id
     JOIN rounds r ON s.round_id = r.id
     WHERE c.event_id = ? 
     AND c.status = 'Active'
     AND r.level_id = ?
     ORDER BY CAST(c.contestant_number AS UNSIGNED), c.contestant_number",
    [17, $level2['id']]
);

echo "Contestants with scores in Level 2: " . count($contestantsWithScores) . "\n";
foreach ($contestantsWithScores as $c) {
    echo "Contestant #{$c['contestant_number']} ({$c['name']})\n";
}

// Scenario 2: When no scores exist (fallback)
echo "\n--- Scenario 2: No scores (fallback to qualified) ---\n";
$qualifiedContestants = $db->fetchAll(
    "SELECT c.*
     FROM contestants c
     WHERE c.event_id = ? 
     AND c.status = 'Active'
     AND c.qualified_for_level_id = ?
     ORDER BY CAST(c.contestant_number AS UNSIGNED), c.contestant_number",
    [17, $level2['id']]
);

echo "Contestants qualified for Level 2: " . count($qualifiedContestants) . "\n";
foreach ($qualifiedContestants as $c) {
    echo "Contestant #{$c['contestant_number']} ({$c['name']})\n";
}

echo "\n=== Logic Flow ===\n";
echo "1. Try to get contestants with scores in Level 2\n";
echo "2. If found, show them (for ongoing/completed levels)\n";
echo "3. If none found, show qualified contestants (for new levels)\n";

echo "\n=== Done ===\n";
