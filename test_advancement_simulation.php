<?php
require_once 'core/Database.php';

$db = Database::getInstance();

echo "=== Simulating Level 2 → Level 3 Advancement ===\n";

// Simulate advancement: contestants 1, 2, 3 advance to Level 3
$db->query("UPDATE contestants SET qualified_for_level_id = 50 WHERE id IN (139, 140, 141)"); // Contestants 1, 2, 3
$db->query("UPDATE contestants SET qualified_for_level_id = NULL WHERE id IN (142, 143)"); // Contestants 4, 5 eliminated

echo "Advanced contestants 1, 2, 3 to Level 3\n";
echo "Eliminated contestants 4, 5\n";

// Test Level 2 report query
$level2 = $db->fetchOne("SELECT * FROM event_levels WHERE event_id = 17 AND `order` = 2");

$contestants = $db->fetchAll(
    "SELECT DISTINCT c.* 
     FROM contestants c
     JOIN scores s ON c.id = s.contestant_id
     JOIN rounds r ON s.round_id = r.id
     WHERE c.event_id = ? AND c.status = 'Active' AND r.level_id = ?
     ORDER BY CAST(c.contestant_number AS UNSIGNED)",
    [17, $level2['id']]
);

echo "\nLevel 2 Report - Contestants who participated (should still show all 5):\n";
foreach ($contestants as $c) {
    echo "Contestant #{$c['contestant_number']} ({$c['name']})\n";
}

echo "\nCurrent qualifications:\n";
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
