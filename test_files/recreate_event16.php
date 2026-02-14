<?php
require_once 'core/Database.php';

$db = Database::getInstance();

echo "=== Recreating Event 16: sfdsf ===\n";

// Create the event
$db->query("
    INSERT INTO events (id, name, status, created_by, created_at) 
    VALUES (16, 'sfdsf', 'Ongoing', 1, NOW())
");
echo "Event 16 created.\n";

// Create levels
$db->query("
    INSERT INTO event_levels (id, event_id, name, `order`, advance_count, status) VALUES
    (45, 16, 'weq', 1, 5, 'Active'),
    (46, 16, 'sgs', 2, 3, 'Active'),
    (47, 16, '133', 3, NULL, 'Active')
");
echo "Levels created.\n";

// Create rounds
$db->query("
    INSERT INTO rounds (id, level_id, name, `order`, status) VALUES
    (77, 45, 'ds', 1, 'Active'),
    (78, 46, 'da', 1, 'Active'),
    (79, 47, 'ad', 1, 'Active')
");
echo "Rounds created.\n";

// Create contestants
$db->query("
    INSERT INTO contestants (id, event_id, contestant_number, name, status) VALUES
    (1, 16, '1', 'Emma Rodriguez', 'Active'),
    (2, 16, '2', 'Michael Chen', 'Active'),
    (3, 16, '3', 'Sophia Martinez', 'Active'),
    (4, 16, '4', 'James Wilson', 'Active'),
    (5, 16, '5', 'Olivia Brown', 'Active'),
    (6, 16, '6', 'William Davis', 'Active'),
    (7, 16, '7', 'Isabella Garcia', 'Active')
");
echo "Contestants created.\n";

echo "\n=== Event 16 Recreated ===\n";

// Check the recreated event
$contestants = $db->fetchAll("
    SELECT c.id, c.contestant_number, c.name, c.qualified_for_level_id
    FROM contestants c
    WHERE c.event_id = 16 AND c.status = 'Active'
    ORDER BY c.contestant_number
");

foreach ($contestants as $c) {
    echo "Contestant #{$c['contestant_number']} ({$c['name']}) - Qualified for Level ID: " . ($c['qualified_for_level_id'] ?? 'NULL') . "\n";
}

echo "\n=== Done ===\n";
