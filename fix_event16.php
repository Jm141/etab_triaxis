<?php
require_once 'core/Database.php';
require_once 'core/ScoringEngine.php';

$db = Database::getInstance();

echo "=== Fixing Event 16: sfdsf Advancement ===\n";

// Clear all qualifications for this event
$db->query("UPDATE contestants SET qualified_for_level_id = NULL WHERE event_id = 16");
echo "All qualifications cleared.\n";

$scoringEngine = new ScoringEngine();

// Get rounds for Level 1 and Level 2
$level1Rounds = $db->fetchAll("SELECT id FROM rounds WHERE level_id = 45");
$level2Rounds = $db->fetchAll("SELECT id FROM rounds WHERE level_id = 46");

echo "\n=== Processing Level 1 Advancement ===\n";
foreach ($level1Rounds as $round) {
    echo "Processing Level 1 Round {$round['id']}...\n";
    $result = $scoringEngine->autoCalculateIfComplete($round['id']);
    echo "Result: " . ($result ? 'CALCULATED' : 'NOT COMPLETE') . "\n";
}

echo "\n=== Checking Level 1 Results ===\n";
$contestants = $db->fetchAll("
    SELECT c.id, c.contestant_number, c.name, c.qualified_for_level_id
    FROM contestants c
    WHERE c.event_id = 16 AND c.status = 'Active'
    ORDER BY c.contestant_number
");

foreach ($contestants as $c) {
    echo "Contestant #{$c['contestant_number']} ({$c['name']}) - Qualified for Level ID: " . ($c['qualified_for_level_id'] ?? 'NULL') . "\n";
}

echo "\n=== Processing Level 2 Advancement ===\n";
foreach ($level2Rounds as $round) {
    echo "Processing Level 2 Round {$round['id']}...\n";
    $result = $scoringEngine->autoCalculateIfComplete($round['id']);
    echo "Result: " . ($result ? 'CALCULATED' : 'NOT COMPLETE') . "\n";
}

echo "\n=== Final Results ===\n";
$contestants = $db->fetchAll("
    SELECT c.id, c.contestant_number, c.name, c.qualified_for_level_id
    FROM contestants c
    WHERE c.event_id = 16 AND c.status = 'Active'
    ORDER BY c.contestant_number
");

foreach ($contestants as $c) {
    echo "Contestant #{$c['contestant_number']} ({$c['name']}) - Qualified for Level ID: " . ($c['qualified_for_level_id'] ?? 'NULL') . "\n";
}

echo "\n=== Summary ===\n";
$level2Qualified = $db->fetchAll("SELECT * FROM contestants WHERE event_id = 16 AND qualified_for_level_id = 46");
$level3Qualified = $db->fetchAll("SELECT * FROM contestants WHERE event_id = 16 AND qualified_for_level_id = 47");

echo "Qualified for Level 2: " . count($level2Qualified) . " contestants\n";
echo "Qualified for Level 3: " . count($level3Qualified) . " contestants\n";

echo "\n=== Done ===\n";
