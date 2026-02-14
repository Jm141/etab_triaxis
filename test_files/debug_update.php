<?php
require_once 'core/Database.php';

$db = Database::getInstance();

echo "=== Debugging Event 17 Contestants ===\n";

// Get actual contestant IDs for Event 17
$contestants = $db->fetchAll("
    SELECT c.id, c.contestant_number, c.name, c.qualified_for_level_id
    FROM contestants c
    WHERE c.event_id = 17 AND c.status = 'Active'
    ORDER BY c.contestant_number
");

echo "Actual contestant data:\n";
foreach ($contestants as $c) {
    echo "ID: {$c['id']} - Contestant #{$c['contestant_number']} ({$c['name']}) - Qualified for Level ID: " . ($c['qualified_for_level_id'] ?? 'NULL') . "\n";
}

// Now update using the actual IDs
echo "\n=== Updating Using Actual IDs ===\n";

$actualIds = array_column($contestants, 'id');
$level2Ids = array_slice($actualIds, 0, 5); // First 5 for Level 2
$eliminatedIds = array_slice($actualIds, 5, 2); // Last 2 eliminated

echo "Level 2 IDs: " . implode(', ', $level2Ids) . "\n";
echo "Eliminated IDs: " . implode(', ', $eliminatedIds) . "\n";

// Update Level 2 contestants
foreach ($level2Ids as $id) {
    $result = $db->query("UPDATE contestants SET qualified_for_level_id = 49 WHERE id = ?", [$id]);
    echo "Updated contestant ID {$id}: " . ($result ? 'SUCCESS' : 'FAILED') . "\n";
}

// Update eliminated contestants
foreach ($eliminatedIds as $id) {
    $result = $db->query("UPDATE contestants SET qualified_for_level_id = NULL WHERE id = ?", [$id]);
    echo "Eliminated contestant ID {$id}: " . ($result ? 'SUCCESS' : 'FAILED') . "\n";
}

// Check final results
echo "\n=== Final Results ===\n";
$contestants = $db->fetchAll("
    SELECT c.id, c.contestant_number, c.name, c.qualified_for_level_id
    FROM contestants c
    WHERE c.event_id = 17 AND c.status = 'Active'
    ORDER BY c.contestant_number
");

foreach ($contestants as $c) {
    echo "ID: {$c['id']} - Contestant #{$c['contestant_number']} ({$c['name']}) - Qualified for Level ID: " . ($c['qualified_for_level_id'] ?? 'NULL') . "\n";
}

echo "\n=== Done ===\n";
