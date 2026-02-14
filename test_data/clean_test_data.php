<?php
/**
 * Clean Test Data from System
 * 
 * This script removes all test data created by insert_test_data_to_system.php
 * 
 * Usage: php test_data/clean_test_data.php [event_id]
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Database.php';

$db = Database::getInstance();

echo "========================================\n";
echo "Clean Test Data Script\n";
echo "========================================\n\n";

// Get event ID from command line or find test events
$eventId = $argv[1] ?? null;

if ($eventId) {
    $event = $db->fetchOne("SELECT * FROM events WHERE id = ?", [$eventId]);
    if (!$event) {
        die("❌ Event ID {$eventId} not found!\n");
    }
    echo "Cleaning Event: {$event['name']} (ID: {$eventId})\n\n";
} else {
    // Find test events
    $testEvents = $db->fetchAll(
        "SELECT * FROM events WHERE name LIKE 'Test Beauty Pageant%' ORDER BY id DESC"
    );
    
    if (empty($testEvents)) {
        echo "No test events found.\n";
        exit(0);
    }
    
    echo "Found " . count($testEvents) . " test event(s):\n";
    foreach ($testEvents as $event) {
        echo "  - ID {$event['id']}: {$event['name']}\n";
    }
    echo "\n";
    
    $eventId = $testEvents[0]['id'];
    echo "Cleaning Event ID: {$eventId}\n\n";
}

// Get event details
$event = $db->fetchOne("SELECT * FROM events WHERE id = ?", [$eventId]);

// Delete scores and score details
echo "--- Deleting Scores ---\n";
$rounds = $db->fetchAll(
    "SELECT r.id FROM rounds r
     JOIN event_levels el ON r.level_id = el.id
     WHERE el.event_id = ?",
    [$eventId]
);

$scoreCount = 0;
foreach ($rounds as $round) {
    $scores = $db->fetchAll("SELECT id FROM scores WHERE round_id = ?", [$round['id']]);
    foreach ($scores as $score) {
        $db->query("DELETE FROM score_details WHERE score_id = ?", [$score['id']]);
        $scoreCount++;
    }
    $db->query("DELETE FROM scores WHERE round_id = ?", [$round['id']]);
}
echo "  ✓ Deleted {$scoreCount} score entries\n";

// Delete rankings
echo "--- Deleting Rankings ---\n";
$rankingCount = 0;
foreach ($rounds as $round) {
    $count = $db->query("DELETE FROM rankings WHERE round_id = ?", [$round['id']]);
    $rankingCount++;
}
echo "  ✓ Deleted rankings for {$rankingCount} rounds\n";

// Delete judge assignments
echo "--- Deleting Judge Assignments ---\n";
$judges = $db->fetchAll("SELECT id FROM judges WHERE event_id = ?", [$eventId]);
$assignmentCount = 0;
foreach ($judges as $judge) {
    $count = $db->query("DELETE FROM judge_assignments WHERE judge_id = ?", [$judge['id']]);
    $assignmentCount++;
}
echo "  ✓ Deleted judge assignments\n";

// Delete judges
echo "--- Deleting Judges ---\n";
$db->query("DELETE FROM judges WHERE event_id = ?", [$eventId]);
echo "  ✓ Deleted judges\n";

// Delete test judge users
echo "--- Deleting Test Judge Users ---\n";
$testJudges = $db->fetchAll(
    "SELECT id FROM users WHERE username LIKE 'test_judge%'"
);
$userCount = 0;
foreach ($testJudges as $user) {
    $db->query("DELETE FROM user_event_assignments WHERE user_id = ?", [$user['id']]);
    $db->query("DELETE FROM users WHERE id = ?", [$user['id']]);
    $userCount++;
}
echo "  ✓ Deleted {$userCount} test judge users\n";

// Delete criteria weights
echo "--- Deleting Criteria Weights ---\n";
foreach ($rounds as $round) {
    $db->query("DELETE FROM criteria_weights WHERE round_id = ?", [$round['id']]);
}
echo "  ✓ Deleted criteria weights\n";

// Delete criteria
echo "--- Deleting Criteria ---\n";
$db->query("DELETE FROM criteria WHERE event_id = ?", [$eventId]);
echo "  ✓ Deleted criteria\n";

// Delete rounds
echo "--- Deleting Rounds ---\n";
$roundCount = 0;
foreach ($rounds as $round) {
    $db->query("DELETE FROM rounds WHERE id = ?", [$round['id']]);
    $roundCount++;
}
echo "  ✓ Deleted {$roundCount} rounds\n";

// Delete levels
echo "--- Deleting Levels ---\n";
$db->query("DELETE FROM event_levels WHERE event_id = ?", [$eventId]);
echo "  ✓ Deleted levels\n";

// Delete contestants
echo "--- Deleting Contestants ---\n";
$db->query("DELETE FROM contestants WHERE event_id = ?", [$eventId]);
echo "  ✓ Deleted contestants\n";

// Delete event
echo "--- Deleting Event ---\n";
$db->query("DELETE FROM events WHERE id = ?", [$eventId]);
echo "  ✓ Deleted event\n";

echo "\n========================================\n";
echo "✅ Test Data Cleaned Successfully!\n";
echo "========================================\n\n";

echo "All test data has been removed from the system.\n";
echo "You can now manually input data using the test data guide.\n\n";
