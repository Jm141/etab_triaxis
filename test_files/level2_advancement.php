<?php
require_once 'core/Database.php';
require_once 'core/ScoringEngine.php';

$db = Database::getInstance();

echo "=== Triggering Level 2 Advancement ===\n";

$scoringEngine = new ScoringEngine();

// Get rounds for Level 2
$level2Rounds = $db->fetchAll("SELECT id FROM rounds WHERE level_id = 43");

// Trigger advancement for Level 2 rounds
foreach ($level2Rounds as $round) {
    echo "Processing Level 2 Round {$round['id']}...\n";
    $result = $scoringEngine->autoCalculateIfComplete($round['id']);
    echo "Result: " . ($result ? 'CALCULATED' : 'NOT COMPLETE') . "\n";
}

echo "\n=== Final Results ===\n";

// Check contestants after Level 2 advancement
$contestants = $db->fetchAll("
    SELECT c.id, c.contestant_number, c.name, c.qualified_for_level_id
    FROM contestants c
    WHERE c.event_id = 15 AND c.status = 'Active'
    ORDER BY c.contestant_number
");

foreach ($contestants as $c) {
    echo "Contestant #{$c['contestant_number']} ({$c['name']}) - Qualified for Level ID: " . ($c['qualified_for_level_id'] ?? 'NULL') . "\n";
}

echo "\n=== Summary ===\n";
$level2Qualified = $db->fetchAll("SELECT * FROM contestants WHERE event_id = 15 AND qualified_for_level_id = 43");
$level3Qualified = $db->fetchAll("SELECT * FROM contestants WHERE event_id = 15 AND qualified_for_level_id = 44");

echo "Qualified for Level 2: " . count($level2Qualified) . " contestants\n";
echo "Qualified for Level 3: " . count($level3Qualified) . " contestants\n";

echo "\n=== Done ===\n";
