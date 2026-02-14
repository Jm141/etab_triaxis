<?php
/**
 * Delete and Repopulate ALL Scores for ALL Judges and ALL Rounds
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/ScoringEngine.php';

$db = Database::getInstance();
$scoringEngine = new ScoringEngine();

echo "=== Deleting and Repopulating ALL Scores ===\n\n";

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

// Delete all existing scores for this event
echo "Deleting existing scores...\n";
$db->query(
    "DELETE sd FROM score_details sd
     JOIN scores s ON sd.score_id = s.id
     JOIN rounds r ON s.round_id = r.id
     JOIN event_levels el ON r.level_id = el.id
     WHERE el.event_id = ?",
    [$event['id']]
);

$db->query(
    "DELETE s FROM scores s
     JOIN rounds r ON s.round_id = r.id
     JOIN event_levels el ON r.level_id = el.id
     WHERE el.event_id = ?",
    [$event['id']]
);

echo "✓ Deleted all existing scores\n\n";

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

$totalPopulated = 0;

// Process each round
foreach ($rounds as $round) {
    echo "=== Round: {$round['level_name']} - {$round['name']} (ID: {$round['id']}) ===\n";
    
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
        echo "  ⚠ WARNING: No criteria found. Skipping...\n\n";
        continue;
    }
    
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
    
    echo "  Judges: " . count($roundJudges) . ", Criteria: " . count($criteria) . "\n";
    
    // Process each judge
    foreach ($roundJudges as $judge) {
        foreach ($contestants as $contestantIndex => $contestant) {
            // Generate realistic scores with decimals
            $baseScore = 7.0 + ($contestantIndex * 0.3) + (($judge['id'] % 3) * 0.2);
            
            // Create score record
            $db->query(
                "INSERT INTO scores (judge_id, contestant_id, round_id, is_submitted, is_draft, submitted_at, ip_address)
                 VALUES (?, ?, ?, 1, 0, NOW(), ?)",
                [$judge['id'], $contestant['id'], $round['id'], $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']
            );
            $scoreId = $db->lastInsertId();
            
            $rawScores = [];
            
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
                
                // Insert score detail
                $db->query(
                    "INSERT INTO score_details (score_id, criteria_id, raw_score, weighted_score)
                     VALUES (?, ?, ?, 0)",
                    [$scoreId, $criterion['id'], $rawScore]
                );
            }
            
            // Calculate weighted score
            $scoringEngine->calculateScore($scoreId);
            
            $totalPopulated++;
        }
    }
    
    echo "  ✓ Populated " . (count($roundJudges) * count($contestants)) . " scores\n\n";
}

echo "=== Summary ===\n";
echo "Total scores populated: {$totalPopulated}\n";
echo "Expected: " . (count($rounds) * count($judges) * count($contestants)) . "\n";

if ($totalPopulated == (count($rounds) * count($judges) * count($contestants))) {
    echo "✓ All scores populated successfully!\n";
} else {
    echo "⚠ Some scores may be missing. Check the output above.\n";
}
