<?php
/**
 * Detailed Verification of All Scores
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

echo "=== Detailed Score Verification ===\n";
echo "Event: {$event['name']} (ID: {$event['id']})\n\n";

// Get all rounds
$rounds = $db->fetchAll(
    "SELECT r.*, el.name as level_name
     FROM rounds r
     JOIN event_levels el ON r.level_id = el.id
     WHERE el.event_id = ?
     ORDER BY el.`order` ASC, r.`order` ASC",
    [$event['id']]
);

// Get all judges
$judges = $db->fetchAll(
    "SELECT j.*, u.full_name, u.username
     FROM judges j
     JOIN users u ON j.user_id = u.id
     WHERE j.event_id = ? AND j.is_active = 1
     ORDER BY j.id",
    [$event['id']]
);

// Get all contestants
$contestants = $db->fetchAll(
    "SELECT * FROM contestants 
     WHERE event_id = ? AND status = 'Active'
     ORDER BY CAST(contestant_number AS UNSIGNED), contestant_number",
    [$event['id']]
);

echo "Total Rounds: " . count($rounds) . "\n";
echo "Total Judges: " . count($judges) . "\n";
echo "Total Contestants: " . count($contestants) . "\n\n";

// Check each round in detail
foreach ($rounds as $round) {
    echo "=== Round: {$round['level_name']} - {$round['name']} (ID: {$round['id']}) ===\n";
    
    // Get judges assigned to this round
    $roundJudges = $db->fetchAll(
        "SELECT j.*, u.full_name, u.username
         FROM judges j
         JOIN users u ON j.user_id = u.id
         JOIN judge_assignments ja ON j.id = ja.judge_id
         WHERE ja.round_id = ? AND ja.is_active = 1 AND j.event_id = ? AND j.is_active = 1
         ORDER BY j.id",
        [$round['id'], $event['id']]
    );
    
    echo "Judges assigned to this round: " . count($roundJudges) . "\n";
    foreach ($roundJudges as $rj) {
        echo "  - {$rj['full_name']} (ID: {$rj['id']})\n";
    }
    echo "\n";
    
    // Check scores per judge
    foreach ($roundJudges as $judge) {
        $judgeScores = $db->fetchAll(
            "SELECT COUNT(*) as count, 
                    COUNT(CASE WHEN is_submitted = 1 THEN 1 END) as submitted_count
             FROM scores
             WHERE round_id = ? AND judge_id = ?",
            [$round['id'], $judge['id']]
        );
        
        $total = $judgeScores[0]['count'];
        $submitted = $judgeScores[0]['submitted_count'];
        
        echo "  Judge: {$judge['full_name']} (ID: {$judge['id']})\n";
        echo "    Total scores: {$total}\n";
        echo "    Submitted scores: {$submitted}\n";
        
        if ($total < count($contestants)) {
            echo "    ⚠ MISSING: Expected " . count($contestants) . ", got {$total}\n";
            
            // Find which contestants are missing
            $missing = $db->fetchAll(
                "SELECT c.id, c.contestant_number, c.name
                 FROM contestants c
                 WHERE c.event_id = ? AND c.status = 'Active'
                   AND c.id NOT IN (
                       SELECT contestant_id FROM scores 
                       WHERE round_id = ? AND judge_id = ?
                   )
                 ORDER BY CAST(c.contestant_number AS UNSIGNED)",
                [$event['id'], $round['id'], $judge['id']]
            );
            
            if (!empty($missing)) {
                echo "    Missing scores for:\n";
                foreach ($missing as $m) {
                    echo "      - #{$m['contestant_number']} {$m['name']}\n";
                }
            }
        } else if ($submitted < count($contestants)) {
            echo "    ⚠ NOT SUBMITTED: {$submitted} submitted, " . count($contestants) . " expected\n";
        } else {
            echo "    ✓ Complete\n";
        }
        echo "\n";
    }
    
    echo "\n";
}

// Summary
echo "=== Summary ===\n";
$totalExpected = count($rounds) * count($judges) * count($contestants);
$totalScores = $db->fetchOne(
    "SELECT COUNT(*) as count FROM scores s
     JOIN rounds r ON s.round_id = r.id
     JOIN event_levels el ON r.level_id = el.id
     WHERE el.event_id = ?",
    [$event['id']]
)['count'];

$submittedScores = $db->fetchOne(
    "SELECT COUNT(*) as count FROM scores s
     JOIN rounds r ON s.round_id = r.id
     JOIN event_levels el ON r.level_id = el.id
     WHERE el.event_id = ? AND s.is_submitted = 1",
    [$event['id']]
)['count'];

echo "Total expected scores: {$totalExpected}\n";
echo "Total scores in database: {$totalScores}\n";
echo "Submitted scores: {$submittedScores}\n";

if ($submittedScores < $totalExpected) {
    echo "⚠ Missing: " . ($totalExpected - $submittedScores) . " scores\n";
} else {
    echo "✓ All scores present and submitted\n";
}
