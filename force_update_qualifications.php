<?php
require_once 'core/Database.php';

$db = Database::getInstance();

echo "=== Force Updating Event 17 Qualifications ===\n";

// Update each contestant individually
$contestants = [1, 2, 3, 4, 5];
foreach ($contestants as $id) {
    $result = $db->query("UPDATE contestants SET qualified_for_level_id = 49 WHERE id = ? AND event_id = 17", [$id]);
    echo "Updated contestant {$id}: " . ($result ? 'SUCCESS' : 'FAILED') . "\n";
}

// Set eliminated contestants
$eliminated = [6, 7];
foreach ($eliminated as $id) {
    $result = $db->query("UPDATE contestants SET qualified_for_level_id = NULL WHERE id = ? AND event_id = 17", [$id]);
    echo "Eliminated contestant {$id}: " . ($result ? 'SUCCESS' : 'FAILED') . "\n";
}

// Check the results
$contestants = $db->fetchAll("
    SELECT c.id, c.contestant_number, c.name, c.qualified_for_level_id
    FROM contestants c
    WHERE c.event_id = 17 AND c.status = 'Active'
    ORDER BY c.contestant_number
");

echo "\nFinal qualifications:\n";
foreach ($contestants as $c) {
    echo "Contestant #{$c['contestant_number']} ({$c['name']}) - Qualified for Level ID: " . ($c['qualified_for_level_id'] ?? 'NULL') . "\n";
}

echo "\n=== Done ===\n";
