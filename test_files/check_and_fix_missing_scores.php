<?php
/**
 * Check and Fix Missing Scores
 * 
 * This script checks which rounds and judges are missing scores
 * and repopulates them.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/ScoringEngine.php';

$db = Database::getInstance();
$scoringEngine = new ScoringEngine();

echo "=== Checking Missing Scores ===\n\n";

// Find the Pageant 2026 event
$event = $db->fetchOne(
    "SELECT * FROM events 
     WHERE name LIKE '%pageant%2026%' OR name LIKE '%pagaent%2026%' OR name LIKE '%2026%pageant%' OR name LIKE '%2026%pagaent%'
     ORDER BY id DESC
     LIMIT 1"
);

if (!$event) {
    echo "ERROR: Pageant 2026 event not found.\n";
    exit(1);
}

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

// Get all contestants
$contestants = $db->fetchAll(
    "SELECT * FROM contestants 
     WHERE event_id = ? AND status = 'Active'
     ORDER BY CAST(contestant_number AS UNSIGNED), contestant_number",
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

echo "Rounds: " . count($rounds) . "\n";
echo "Contestants: " . count($contestants) . "\n";
echo "Judges: " . count($judges) . "\n\n";

// Check each round
$missingScores = [];
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
    
    echo "  Judges assigned: " . count($roundJudges) . "\n";
    
    // Get criteria
    $criteria = $db->fetchAll(
        "SELECT c.*, cw.id as weight_id, cw.weight
         FROM criteria_weights cw
         JOIN criteria c ON cw.criteria_id = c.id
         WHERE cw.round_id = ? AND cw.is_active = 1
         ORDER BY c.id",
        [$round['id']]
    );
    
    if (empty($criteria)) {
        echo "  ⚠ WARNING: No criteria found for this round!\n\n";
        continue;
    }
    
    echo "  Criteria: " . count($criteria) . "\n";
    
    // Check scores for each judge-contestant combination
    foreach ($roundJudges as $judge) {
        foreach ($contestants as $contestant) {
            $existingScore = $db->fetchOne(
                "SELECT id, is_submitted FROM scores 
                 WHERE round_id = ? AND contestant_id = ? AND judge_id = ?",
                [$round['id'], $contestant['id'], $judge['id']]
            );
            
            if (!$existingScore || !$existingScore['is_submitted']) {
                $missingScores[] = [
                    'round_id' => $round['id'],
                    'round_name' => $round['name'],
                    'judge_id' => $judge['id'],
                    'judge_name' => $judge['full_name'],
                    'contestant_id' => $contestant['id'],
                    'contestant_number' => $contestant['contestant_number'],
                    'contestant_name' => $contestant['name']
                ];
            }
        }
    }
    
    // Count existing scores
    $scoreCount = $db->fetchOne(
        "SELECT COUNT(*) as count FROM scores 
         WHERE round_id = ? AND is_submitted = 1",
        [$round['id']]
    )['count'];
    
    $expectedCount = count($roundJudges) * count($contestants);
    echo "  Scores: {$scoreCount} / {$expectedCount}\n";
    
    if ($scoreCount < $expectedCount) {
        echo "  ⚠ Missing: " . ($expectedCount - $scoreCount) . " scores\n";
    } else {
        echo "  ✓ All scores present\n";
    }
    echo "\n";
}

if (empty($missingScores)) {
    echo "=== All Scores Present ===\n";
    echo "No missing scores found. All rounds have complete scores.\n";
    exit(0);
}

echo "=== Found " . count($missingScores) . " Missing Scores ===\n";
echo "Repopulating missing scores...\n\n";

// Group by round and judge for easier processing
$grouped = [];
foreach ($missingScores as $missing) {
    $key = $missing['round_id'] . '_' . $missing['judge_id'];
    if (!isset($grouped[$key])) {
        $grouped[$key] = [
            'round_id' => $missing['round_id'],
            'round_name' => $missing['round_name'],
            'judge_id' => $missing['judge_id'],
            'judge_name' => $missing['judge_name'],
            'contestants' => []
        ];
    }
    $grouped[$key]['contestants'][] = [
        'id' => $missing['contestant_id'],
        'number' => $missing['contestant_number'],
        'name' => $missing['contestant_name']
    ];
}

// Repopulate missing scores
$populatedCount = 0;
foreach ($grouped as $group) {
    $roundId = $group['round_id'];
    $judgeId = $group['judge_id'];
    
    echo "Processing: {$group['round_name']} - {$group['judge_name']}\n";
    
    // Get criteria for this round
    $criteria = $db->fetchAll(
        "SELECT c.*, cw.id as weight_id, cw.weight
         FROM criteria_weights cw
         JOIN criteria c ON cw.criteria_id = c.id
         WHERE cw.round_id = ? AND cw.is_active = 1
         ORDER BY c.id",
        [$roundId]
    );
    
    if (empty($criteria)) {
        echo "  ⚠ Skipping - no criteria\n\n";
        continue;
    }
    
    foreach ($group['contestants'] as $contestant) {
        $contestantIndex = array_search($contestant['id'], array_column($contestants, 'id'));
        
        // Generate realistic scores with decimals
        $baseScore = 7.0 + ($contestantIndex * 0.3) + (($judgeId % 3) * 0.2);
        
        // Check if score exists (even if not submitted)
        $existingScore = $db->fetchOne(
            "SELECT id FROM scores 
             WHERE round_id = ? AND contestant_id = ? AND judge_id = ?",
            [$roundId, $contestant['id'], $judgeId]
        );
        
        $scoreId = null;
        if ($existingScore) {
            $scoreId = $existingScore['id'];
            // Delete existing score details
            $db->query("DELETE FROM score_details WHERE score_id = ?", [$scoreId]);
            // Reset to not submitted
            $db->query("UPDATE scores SET is_submitted = 0 WHERE id = ?", [$scoreId]);
        } else {
            // Create new score record
            $db->query(
                "INSERT INTO scores (judge_id, contestant_id, round_id, is_submitted, is_draft, submitted_at, ip_address)
                 VALUES (?, ?, ?, 1, 0, NOW(), ?)",
                [$judgeId, $contestant['id'], $roundId, $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']
            );
            $scoreId = $db->lastInsertId();
        }
        
        $rawScores = [];
        $totalRaw = 0;
        
        // Generate scores for each criterion
        foreach ($criteria as $criterionIndex => $criterion) {
            $maxScore = (float)$criterion['max_score'];
            
            // Create variation with decimals
            $variation = sin($contestantIndex + $criterionIndex) * 0.8 + cos($judgeId + $criterionIndex) * 0.3;
            $randomDecimal = mt_rand(0, 999) / 1000;
            
            $rawScore = min($maxScore, max(0, $baseScore + $variation + ($randomDecimal * 0.5)));
            $rawScore = round($rawScore, 3);
            
            // Ensure we have decimals
            if ($rawScore == floor($rawScore)) {
                $rawScore += mt_rand(1, 999) / 1000;
                $rawScore = min($maxScore, $rawScore);
                $rawScore = round($rawScore, 3);
            }
            
            if ($rawScore > $maxScore) {
                $rawScore = $maxScore;
            }
            
            $rawScores[$criterion['id']] = $rawScore;
            $totalRaw += $rawScore;
            
            // Insert score detail
            $db->query(
                "INSERT INTO score_details (score_id, criteria_id, raw_score, weighted_score)
                 VALUES (?, ?, ?, 0)",
                [$scoreId, $criterion['id'], $rawScore]
            );
        }
        
        // Calculate weighted score
        $scoringEngine->calculateScore($scoreId);
        
        // Mark as submitted
        $db->query("UPDATE scores SET is_submitted = 1 WHERE id = ?", [$scoreId]);
        
        $populatedCount++;
    }
    
    echo "  ✓ Populated scores for " . count($group['contestants']) . " contestants\n\n";
}

echo "=== Summary ===\n";
echo "Populated {$populatedCount} missing scores\n";
echo "All rounds should now have complete scores.\n";
