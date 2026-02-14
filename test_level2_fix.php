<?php
require_once 'core/Database.php';

$db = Database::getInstance();

echo "=== Testing Event 17 Level 2 Report Fix ===\n";

// Get Level 2 info
$level2 = $db->fetchOne("SELECT * FROM event_levels WHERE event_id = 17 AND `order` = 2");
echo "Level 2: {$level2['name']} (ID: {$level2['id']})\n";

// Test the new query for Level 2 contestants
$contestants = $db->fetchAll(
    "SELECT DISTINCT c.* 
     FROM contestants c
     JOIN scores s ON c.id = s.contestant_id
     JOIN rounds r ON s.round_id = r.id
     WHERE c.event_id = ? AND c.status = 'Active' AND r.level_id = ?
     ORDER BY CAST(c.contestant_number AS UNSIGNED)",
    [17, $level2['id']]
);

echo "\nContestants who participated in Level 2 (should be 5):\n";
foreach ($contestants as $c) {
    echo "Contestant #{$c['contestant_number']} ({$c['name']})\n";
}

echo "\nCurrent qualifications (for reference):\n";
$allContestants = $db->fetchAll("
    SELECT c.id, c.contestant_number, c.name, c.qualified_for_level_id
    FROM contestants c
    WHERE c.event_id = 17 AND c.status = 'Active'
    ORDER BY c.contestant_number
");

foreach ($allContestants as $c) {
    echo "Contestant #{$c['contestant_number']} ({$c['name']}) - Qualified for Level ID: " . ($c['qualified_for_level_id'] ?? 'NULL') . "\n";
}

echo "\n=== Done ===\n";
