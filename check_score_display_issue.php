<?php
/**
 * Check Score Display Issue
 * Verifies if scores are properly structured for display
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

echo "=== Checking Score Display Issues ===\n";
echo "Event: {$event['name']} (ID: {$event['id']})\n\n";

// Get a sample round
$round = $db->fetchOne(
    "SELECT r.*, el.name as level_name
     FROM rounds r
     JOIN event_levels el ON r.level_id = el.id
     WHERE el.event_id = ?
     ORDER BY r.id
     LIMIT 1",
    [$event['id']]
);

if (!$round) {
    echo "ERROR: No rounds found.\n";
    exit(1);
}

echo "Testing Round: {$round['level_name']} - {$round['name']} (ID: {$round['id']})\n\n";

// Get a judge
$judge = $db->fetchOne(
    "SELECT j.*, u.full_name, u.username
     FROM judges j
     JOIN users u ON j.user_id = u.id
     JOIN judge_assignments ja ON j.id = ja.judge_id
     WHERE ja.round_id = ? AND ja.is_active = 1 AND j.event_id = ? AND j.is_active = 1
     LIMIT 1",
    [$round['id'], $event['id']]
);

if (!$judge) {
    echo "ERROR: No judges found for this round.\n";
    exit(1);
}

echo "Testing Judge: {$judge['full_name']} (ID: {$judge['id']})\n\n";

// Simulate what the controller does
$allScores = $db->fetchAll(
    "SELECT s.*, c.id as contestant_id
     FROM scores s
     JOIN contestants c ON s.contestant_id = c.id
     WHERE s.round_id = ? AND s.judge_id = ? AND c.event_id = ?",
    [$round['id'], $judge['id'], $event['id']]
);

echo "Scores found: " . count($allScores) . "\n";

if (empty($allScores)) {
    echo "⚠ NO SCORES FOUND for this judge-round combination!\n";
    echo "This would cause empty display in the interface.\n";
    exit(1);
}

// Get score details
$scoreIds = array_column($allScores, 'id');
$allScoreDetails = [];

if (!empty($scoreIds)) {
    $placeholders = implode(',', array_fill(0, count($scoreIds), '?'));
    $details = $db->fetchAll(
        "SELECT * FROM score_details WHERE score_id IN ($placeholders)",
        $scoreIds
    );
    
    foreach ($details as $detail) {
        if (!isset($allScoreDetails[$detail['score_id']])) {
            $allScoreDetails[$detail['score_id']] = [];
        }
        $allScoreDetails[$detail['score_id']][$detail['criteria_id']] = $detail;
    }
}

echo "Score details found: " . count($details) . "\n\n";

// Check each score
echo "Sample Scores:\n";
foreach (array_slice($allScores, 0, 3) as $score) {
    echo "  Score ID: {$score['id']}, Contestant ID: {$score['contestant_id']}\n";
    echo "    is_submitted: " . ($score['is_submitted'] ? '1' : '0') . "\n";
    echo "    total_score: {$score['total_score']}\n";
    
    if (isset($allScoreDetails[$score['id']])) {
        echo "    Score Details: " . count($allScoreDetails[$score['id']]) . " criteria\n";
        foreach ($allScoreDetails[$score['id']] as $criteriaId => $detail) {
            echo "      Criteria {$criteriaId}: raw_score = {$detail['raw_score']}, weighted = {$detail['weighted_score']}\n";
        }
    } else {
        echo "    ⚠ NO SCORE DETAILS FOUND!\n";
    }
    echo "\n";
}

// Check if this matches what the view expects
$contestants = $db->fetchAll(
    "SELECT * FROM contestants WHERE event_id = ? AND status = 'Active' ORDER BY CAST(contestant_number AS UNSIGNED)",
    [$event['id']]
);

$criteria = $db->fetchAll(
    "SELECT c.*, cw.weight
     FROM criteria_weights cw
     JOIN criteria c ON cw.criteria_id = c.id
     WHERE cw.round_id = ? AND cw.is_active = 1
     ORDER BY c.id",
    [$round['id']]
);

echo "Contestants: " . count($contestants) . "\n";
echo "Criteria: " . count($criteria) . "\n\n";

// Simulate how the view builds scoreDetails array
$scoreDetails = [];
foreach ($contestants as $contestant) {
    foreach ($allScores as $score) {
        if ($score['contestant_id'] == $contestant['id']) {
            if (isset($allScoreDetails[$score['id']])) {
                $scoreDetails[$contestant['id']] = $allScoreDetails[$score['id']];
            }
            break;
        }
    }
}

echo "Score details array (as view expects): " . count($scoreDetails) . " contestants have scores\n";

// Check if values would display correctly
echo "\nChecking display values:\n";
foreach (array_slice($contestants, 0, 3) as $contestant) {
    echo "  Contestant #{$contestant['contestant_number']}:\n";
    if (isset($scoreDetails[$contestant['id']])) {
        foreach ($criteria as $criterion) {
            if (isset($scoreDetails[$contestant['id']][$criterion['id']])) {
                $detail = $scoreDetails[$contestant['id']][$criterion['id']];
                $value = number_format($detail['raw_score'], 3, '.', '');
                echo "    {$criterion['name']}: {$value} ✓\n";
            } else {
                echo "    {$criterion['name']}: EMPTY ⚠\n";
            }
        }
    } else {
        echo "    NO SCORES FOUND ⚠\n";
    }
    echo "\n";
}

echo "=== Summary ===\n";
if (count($scoreDetails) == count($contestants)) {
    echo "✓ All contestants have scores - should display correctly\n";
} else {
    echo "⚠ Only " . count($scoreDetails) . " of " . count($contestants) . " contestants have scores\n";
    echo "This would cause empty fields in the interface.\n";
}
