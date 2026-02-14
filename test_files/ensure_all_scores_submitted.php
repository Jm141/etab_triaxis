<?php
/**
 * Ensure All Scores Are Submitted
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Database.php';

$db = Database::getInstance();

$event = $db->fetchOne(
    "SELECT * FROM events 
     WHERE name LIKE '%pageant%2026%' OR name LIKE '%pagaent%2026%' OR name LIKE '%2026%pageant%' OR name LIKE '%2026%pagaent%'
     ORDER BY id DESC
     LIMIT 1"
);

if (!$event) {
    echo "ERROR: Event not found.\n";
    exit(1);
}

echo "=== Ensuring All Scores Are Submitted ===\n";
echo "Event: {$event['name']} (ID: {$event['id']})\n\n";

// Mark all scores as submitted
$result = $db->query(
    "UPDATE scores s
     JOIN rounds r ON s.round_id = r.id
     JOIN event_levels el ON r.level_id = el.id
     SET s.is_submitted = 1
     WHERE el.event_id = ? AND s.is_submitted = 0",
    [$event['id']]
);

$updated = $result->rowCount();
echo "Updated {$updated} scores to is_submitted = 1\n\n";

// Verify
$stats = $db->fetchAll(
    "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN is_submitted = 1 THEN 1 ELSE 0 END) as submitted,
        SUM(CASE WHEN is_submitted = 0 THEN 1 ELSE 0 END) as not_submitted
     FROM scores s
     JOIN rounds r ON s.round_id = r.id
     JOIN event_levels el ON r.level_id = el.id
     WHERE el.event_id = ?",
    [$event['id']]
);

$stat = $stats[0];
echo "Final Status:\n";
echo "  Total scores: {$stat['total']}\n";
echo "  Submitted: {$stat['submitted']}\n";
echo "  Not submitted: {$stat['not_submitted']}\n";

if ($stat['not_submitted'] == 0) {
    echo "\n✓ All scores are now submitted!\n";
} else {
    echo "\n⚠ Still have {$stat['not_submitted']} scores not submitted\n";
}
