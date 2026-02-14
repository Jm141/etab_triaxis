<?php
/**
 * Test Reports After Score Population
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

echo "=== Testing Reports After Score Population ===\n";
echo "Event: {$event['name']} (ID: {$event['id']})\n\n";

// Test Round Report
$round = $db->fetchOne(
    "SELECT r.*, el.name as level_name
     FROM rounds r
     JOIN event_levels el ON r.level_id = el.id
     WHERE el.event_id = ?
     ORDER BY r.id
     LIMIT 1",
    [$event['id']]
);

if ($round) {
    echo "=== Round Report Test ===\n";
    echo "Round: {$round['level_name']} - {$round['name']} (ID: {$round['id']})\n";
    
    // Get judges
    $judges = $db->fetchAll(
        "SELECT DISTINCT j.id, j.judge_number, u.full_name as judge_name
         FROM judges j
         JOIN users u ON j.user_id = u.id
         JOIN judge_assignments ja ON j.id = ja.judge_id
         WHERE ja.round_id = ? AND ja.is_active = 1
         ORDER BY j.judge_number",
        [$round['id']]
    );
    
    echo "Judges: " . count($judges) . "\n";
    
    // Get contestants
    $contestants = $db->fetchAll(
        "SELECT DISTINCT c.id, c.contestant_number, c.name
         FROM contestants c
         JOIN scores s ON c.id = s.contestant_id
         WHERE s.round_id = ? AND s.is_submitted = 1 AND c.status = 'Active'
         ORDER BY CAST(c.contestant_number AS UNSIGNED)",
        [$round['id']]
    );
    
    echo "Contestants with scores: " . count($contestants) . "\n";
    
    if (count($contestants) > 0) {
        echo "✓ Round report should display data\n";
        
        // Test a sample contestant
        $sampleContestant = $contestants[0];
        $sampleScores = [];
        foreach ($judges as $judge) {
            $score = $db->fetchOne(
                "SELECT total_score FROM scores
                 WHERE round_id = ? AND contestant_id = ? AND judge_id = ? AND is_submitted = 1",
                [$round['id'], $sampleContestant['id'], $judge['id']]
            );
            $sampleScores[$judge['id']] = $score ? $score['total_score'] : 0;
        }
        
        $total = array_sum($sampleScores);
        echo "  Sample: Contestant #{$sampleContestant['contestant_number']} - Total: {$total}\n";
    } else {
        echo "⚠ Round report will show 'No scores available'\n";
    }
    echo "\n";
}

// Test Level Report
$level = $db->fetchOne(
    "SELECT * FROM event_levels WHERE event_id = ? ORDER BY `order` LIMIT 1",
    [$event['id']]
);

if ($level) {
    echo "=== Level Report Test ===\n";
    echo "Level: {$level['name']} (ID: {$level['id']})\n";
    
    $rounds = $db->fetchAll(
        "SELECT * FROM rounds WHERE level_id = ? ORDER BY `order`",
        [$level['id']]
    );
    
    echo "Rounds in level: " . count($rounds) . "\n";
    
    $contestants = $db->fetchAll(
        "SELECT * FROM contestants WHERE event_id = ? AND status = 'Active' ORDER BY CAST(contestant_number AS UNSIGNED)",
        [$event['id']]
    );
    
    $hasScores = false;
    foreach ($contestants as $contestant) {
        foreach ($rounds as $round) {
            $scores = $db->fetchAll(
                "SELECT s.total_score FROM scores s
                 WHERE s.round_id = ? AND s.contestant_id = ? AND s.is_submitted = 1",
                [$round['id'], $contestant['id']]
            );
            if (!empty($scores)) {
                $hasScores = true;
                break 2;
            }
        }
    }
    
    if ($hasScores) {
        echo "✓ Level report should display data\n";
    } else {
        echo "⚠ Level report will show 'No scores available'\n";
    }
    echo "\n";
}

// Test Judge Report
$judge = $db->fetchOne(
    "SELECT j.*, u.full_name as judge_name
     FROM judges j
     JOIN users u ON j.user_id = u.id
     WHERE j.event_id = ? AND j.is_active = 1
     LIMIT 1",
    [$event['id']]
);

if ($judge) {
    echo "=== Judge Report Test ===\n";
    echo "Judge: {$judge['judge_name']} (ID: {$judge['id']})\n";
    
    $scores = $db->fetchAll(
        "SELECT s.* FROM scores s
         JOIN rounds r ON s.round_id = r.id
         JOIN event_levels el ON r.level_id = el.id
         WHERE s.judge_id = ? AND s.is_submitted = 1 AND el.event_id = ?",
        [$judge['id'], $event['id']]
    );
    
    echo "Scores submitted: " . count($scores) . "\n";
    
    if (count($scores) > 0) {
        echo "✓ Judge report should display data\n";
    } else {
        echo "⚠ Judge report will show 'No scores submitted'\n";
    }
    echo "\n";
}

echo "=== Summary ===\n";
echo "All reports should now work with populated scores.\n";
echo "Access reports at:\n";
echo "  - Round: /tabulation/events/{$event['id']}/reports/round/{$round['id']}\n";
echo "  - Level: /tabulation/events/{$event['id']}/reports/level/{$level['id']}\n";
echo "  - Judge: /tabulation/events/{$event['id']}/reports/judge/{$judge['id']}\n";
