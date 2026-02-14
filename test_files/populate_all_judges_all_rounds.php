<?php
/**
 * Populate ALL Judges' Scores for ALL Rounds
 * 
 * This ensures every judge has scores for every round.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/ScoringEngine.php';

$db = Database::getInstance();
$scoringEngine = new ScoringEngine();

echo "=== Populating ALL Judges' Scores for ALL Rounds ===\n\n";

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
    "SELECT r.*, el.name as level_name, el.`order` as level_order
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

// Get all judges for this event
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

$totalPopulated = 0;

// Process each round
foreach ($rounds as $round) {
    echo "=== Round: {$round['level_name']} - {$round['name']} (ID: {$round['id']}) ===\n";
    
    // Get criteria for this round
    $criteria = $db->fetchAll(
        "SELECT c.*, cw.id as weight_id, cw.weight
         FROM criteria_weights cw
         JOIN criteria c ON cw.criteria_id = c.id
         WHERE cw.round_id = ? AND cw.is_active = 1
         ORDER BY c.id",
        [$round['id']]
    );
    
    if (empty($criteria)) {
        echo "  ⚠ WARNING: No criteria found. Skipping...\n\n";
        continue;
    }
    
    echo "  Criteria: " . count($criteria) . "\n";
    
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
    
    if (empty($roundJudges)) {
        echo "  ⚠ WARNING: No judges assigned. Skipping...\n\n";
        continue;
    }
    
    echo "  Judges assigned: " . count($roundJudges) . "\n\n";
    
    // Process each judge
    foreach ($roundJudges as $judge) {
        echo "  Processing Judge: {$judge['full_name']} (ID: {$judge['id']})\n";
        
        $judgePopulated = 0;
        
        foreach ($contestants as $contestantIndex => $contestant) {
            // Check if score already exists and is submitted
            $existingScore = $db->fetchOne(
                "SELECT id, is_submitted FROM scores 
                 WHERE round_id = ? AND contestant_id = ? AND judge_id = ?",
                [$round['id'], $contestant['id'], $judge['id']]
            );
            
            // Skip if already submitted (we'll update non-submitted ones)
            if ($existingScore && $existingScore['is_submitted']) {
                continue;
            }
            
            $scoreId = null;
            if ($existingScore) {
                $scoreId = $existingScore['id'];
                // Delete existing score details
                $db->query("DELETE FROM score_details WHERE score_id = ?", [$scoreId]);
            } else {
                // Create new score record
                $db->query(
                    "INSERT INTO scores (judge_id, contestant_id, round_id, is_submitted, is_draft, submitted_at, ip_address)
                     VALUES (?, ?, ?, 1, 0, NOW(), ?)",
                    [$judge['id'], $contestant['id'], $round['id'], $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']
                );
                $scoreId = $db->lastInsertId();
            }
            
            // Generate realistic scores with decimals
            $baseScore = 7.0 + ($contestantIndex * 0.3) + (($judge['id'] % 3) * 0.2);
            
            $rawScores = [];
            $totalRaw = 0;
            
            // Generate scores for each criterion
            foreach ($criteria as $criterionIndex => $criterion) {
                $maxScore = (float)$criterion['max_score'];
                
                // Create variation with decimals
                $variation = sin($contestantIndex + $criterionIndex) * 0.8 + cos($judge['id'] + $criterionIndex) * 0.3;
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
            
            $judgePopulated++;
            $totalPopulated++;
        }
        
        echo "    ✓ Populated {$judgePopulated} scores\n";
    }
    
    echo "\n";
}

echo "=== Summary ===\n";
echo "Total scores populated: {$totalPopulated}\n";
echo "All judges should now have scores for all rounds.\n";
