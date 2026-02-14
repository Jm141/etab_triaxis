<?php
require_once 'core/Database.php';

$db = Database::getInstance();

echo "=== Fixing Event 17 Level 2 Qualifications ===\n";

// First, let's set all contestants 1-5 to Level 2 qualification
$db->query("UPDATE contestants SET qualified_for_level_id = 49 WHERE event_id = 17 AND id IN (1,2,3,4,5)");
echo "Set contestants 1-5 to Level 2 qualification\n";

// Set contestants 6-7 to NULL (eliminated)
$db->query("UPDATE contestants SET qualified_for_level_id = NULL WHERE event_id = 17 AND id IN (6,7)");
echo "Set contestants 6-7 to eliminated\n";

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
