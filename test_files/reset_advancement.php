<?php
require_once 'core/Database.php';
require_once 'core/ScoringEngine.php';

$db = Database::getInstance();

echo "=== Clearing All Qualifications ===\n";

// Clear all qualifications for this event
$db->query("UPDATE contestants SET qualified_for_level_id = NULL WHERE event_id = 15");

echo "All qualifications cleared.\n";

echo "\n=== Re-triggering Advancement Process ===\n";

$scoringEngine = new ScoringEngine();

// Get rounds for Level 1 and Level 2
$level1Rounds = $db->fetchAll("SELECT id FROM rounds WHERE level_id = 42");
$level2Rounds = $db->fetchAll("SELECT id FROM rounds WHERE level_id = 43");

// Trigger advancement for Level 1 rounds
foreach ($level1Rounds as $round) {
    echo "Processing Level 1 Round {$round['id']}...\n";
    $result = $scoringEngine->autoCalculateIfComplete($round['id']);
    echo "Result: " . ($result ? 'CALCULATED' : 'NOT COMPLETE') . "\n";
}

echo "\n=== Checking Level 1 Results ===\n";

// Check contestants after Level 1 advancement
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
