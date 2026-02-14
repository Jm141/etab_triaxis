<?php
/**
 * Test Report Generation Directly
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

echo "=== Testing Report Generation ===\n";
echo "Event: {$event['name']} (ID: {$event['id']})\n\n";

// Test Round Report
echo "=== Round Report Test ===\n";
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
    
    if (count($contestants) > 0 && count($judges) > 0) {
        echo "✓ Round report should work\n";
        echo "URL: /tabulation/events/{$event['id']}/reports/round/{$round['id']}\n";
    } else {
        echo "⚠ Round report may show 'No scores available'\n";
    }
    echo "\n";
}

// Test Level Report
echo "=== Level Report Test ===\n";
$level = $db->fetchOne(
    "SELECT * FROM event_levels WHERE event_id = ? ORDER BY `order` LIMIT 1",
    [$event['id']]
);

if ($level) {
    echo "Level: {$level['name']} (ID: {$level['id']})\n";
    
    $rounds = $db->fetchAll(
        "SELECT * FROM rounds WHERE level_id = ? ORDER BY `order`",
        [$level['id']]
    );
    
    echo "Rounds in level: " . count($rounds) . "\n";
    
    $hasScores = false;
    foreach ($rounds as $r) {
        $scores = $db->fetchOne(
            "SELECT COUNT(*) as count FROM scores 
             WHERE round_id = ? AND is_submitted = 1",
            [$r['id']]
        );
        if ($scores['count'] > 0) {
            $hasScores = true;
            break;
        }
    }
    
    if ($hasScores) {
        echo "✓ Level report should work\n";
        echo "URL: /tabulation/events/{$event['id']}/reports/level/{$level['id']}\n";
    } else {
        echo "⚠ Level report may show 'No scores available'\n";
    }
    echo "\n";
}

// Test Judge Report
echo "=== Judge Report Test ===\n";
$judge = $db->fetchOne(
    "SELECT j.*, u.full_name as judge_name
     FROM judges j
     JOIN users u ON j.user_id = u.id
     WHERE j.event_id = ? AND j.is_active = 1
     LIMIT 1",
    [$event['id']]
);

if ($judge) {
    echo "Judge: {$judge['judge_name']} (ID: {$judge['id']})\n";
    
    $scores = $db->fetchOne(
        "SELECT COUNT(*) as count FROM scores s
         JOIN rounds r ON s.round_id = r.id
         JOIN event_levels el ON r.level_id = el.id
         WHERE s.judge_id = ? AND s.is_submitted = 1 AND el.event_id = ?",
        [$judge['id'], $event['id']]
    );
    
    echo "Scores submitted: " . $scores['count'] . "\n";
    
    if ($scores['count'] > 0) {
        echo "✓ Judge report should work\n";
        echo "URL: /tabulation/events/{$event['id']}/reports/judge/{$judge['id']}\n";
    } else {
        echo "⚠ Judge report may show 'No scores submitted'\n";
    }
    echo "\n";
}

echo "=== Summary ===\n";
echo "If reports are not generating, check:\n";
echo "1. User role is 'Super Admin' or 'Event Technical Admin'\n";
echo "2. Event ID is correct: {$event['id']}\n";
echo "3. Scores are submitted (is_submitted = 1)\n";
echo "4. Judges are assigned to rounds\n";
echo "5. Browser console for JavaScript errors\n";
