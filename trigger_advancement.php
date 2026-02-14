<?php
require_once 'core/Database.php';
require_once 'core/ScoringEngine.php';

$scoringEngine = new ScoringEngine();

echo "=== Manually Triggering Advancement Process ===\n";

// Get rounds for Level 1 and Level 2
$db = Database::getInstance();

$level1Rounds = $db->fetchAll("SELECT id FROM rounds WHERE level_id = 42");
$level2Rounds = $db->fetchAll("SELECT id FROM rounds WHERE level_id = 43");

echo "Level 1 rounds: " . count($level1Rounds) . "\n";
echo "Level 2 rounds: " . count($level2Rounds) . "\n";

// Try to trigger advancement for Level 1 rounds
foreach ($level1Rounds as $round) {
    echo "Processing Level 1 Round {$round['id']}...\n";
    $result = $scoringEngine->autoCalculateIfComplete($round['id']);
    echo "Result: " . ($result ? 'CALCULATED' : 'NOT COMPLETE') . "\n";
}

// Try to trigger advancement for Level 2 rounds
foreach ($level2Rounds as $round) {
    echo "Processing Level 2 Round {$round['id']}...\n";
    $result = $scoringEngine->autoCalculateIfComplete($round['id']);
    echo "Result: " . ($result ? 'CALCULATED' : 'NOT COMPLETE') . "\n";
}

echo "\n=== Checking Results ===\n";

// Check contestants again
$contestants = $db->fetchAll("
    SELECT c.id, c.contestant_number, c.name, c.qualified_for_level_id
    FROM contestants c
    WHERE c.event_id = 15 AND c.status = 'Active'
    ORDER BY c.contestant_number
");

foreach ($contestants as $c) {
    echo "Contestant #{$c['contestant_number']} ({$c['name']}) - Qualified for Level ID: " . ($c['qualified_for_level_id'] ?? 'NULL') . "\n";
}

echo "\n=== Done ===\n";
