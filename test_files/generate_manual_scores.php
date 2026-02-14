<?php
/**
 * Generate Manual Score Input Data
 * Creates a formatted list of all scores for manual input
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

echo "=== Generating Manual Score Input Data ===\n";
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

// Get all judges
$judges = $db->fetchAll(
    "SELECT j.*, u.full_name, u.username
     FROM judges j
     JOIN users u ON j.user_id = u.id
     WHERE j.event_id = ? AND j.is_active = 1
     ORDER BY j.id",
    [$event['id']]
);

$output = [];
$output[] = "=" . str_repeat("=", 80);
$output[] = "MANUAL SCORE INPUT DATA";
$output[] = "Event: {$event['name']}";
$output[] = "Generated: " . date('Y-m-d H:i:s');
$output[] = "=" . str_repeat("=", 80);
$output[] = "";

// Generate scores for each round
foreach ($rounds as $round) {
    $output[] = "";
    $output[] = str_repeat("-", 80);
    $output[] = "ROUND: {$round['level_name']} - {$round['name']} (Round ID: {$round['id']})";
    $output[] = str_repeat("-", 80);
    $output[] = "";
    
    // Get criteria
    $criteria = $db->fetchAll(
        "SELECT c.*, cw.weight
         FROM criteria_weights cw
         JOIN criteria c ON cw.criteria_id = c.id
         WHERE cw.round_id = ? AND cw.is_active = 1
         ORDER BY c.id",
        [$round['id']]
    );
    
    if (empty($criteria)) {
        $output[] = "⚠ WARNING: No criteria found for this round!";
        $output[] = "";
        continue;
    }
    
    $output[] = "Criteria:";
    foreach ($criteria as $criterion) {
        $output[] = "  - {$criterion['name']} (Max: {$criterion['max_score']}, Weight: {$criterion['weight']}%)";
    }
    $output[] = "";
    
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
        $output[] = "⚠ WARNING: No judges assigned to this round!";
        $output[] = "";
        continue;
    }
    
    // Generate scores for each judge and contestant
    foreach ($roundJudges as $judge) {
        $output[] = "";
        $output[] = "JUDGE: {$judge['full_name']} (Judge ID: {$judge['id']}, Username: {$judge['username']})";
        $output[] = str_repeat(".", 80);
        
        foreach ($contestants as $contestantIndex => $contestant) {
            $output[] = "";
            $output[] = "  Contestant #{$contestant['contestant_number']}: {$contestant['name']} (Contestant ID: {$contestant['id']})";
            
            // Generate realistic scores with decimals
            $baseScore = 7.0 + ($contestantIndex * 0.3) + (($judge['id'] % 3) * 0.2);
            
            $scores = [];
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
                
                $scores[] = [
                    'criteria' => $criterion['name'],
                    'criteria_id' => $criterion['id'],
                    'max_score' => $maxScore,
                    'raw_score' => $rawScore
                ];
            }
            
            // Display scores
            foreach ($scores as $score) {
                $output[] = "    {$score['criteria']} (ID: {$score['criteria_id']}, Max: {$score['max_score']}): {$score['raw_score']}";
            }
            
            // Calculate total
            $totalRaw = array_sum(array_column($scores, 'raw_score'));
            $output[] = "    → Total Raw Score: " . number_format($totalRaw, 3);
        }
    }
}

$output[] = "";
$output[] = "=" . str_repeat("=", 80);
$output[] = "END OF MANUAL SCORE INPUT DATA";
$output[] = "=" . str_repeat("=", 80);

// Write to file
$filename = 'MANUAL_SCORE_INPUT_DATA.txt';
file_put_contents($filename, implode("\n", $output));

echo "✓ Generated manual score input data\n";
echo "File saved: {$filename}\n";
echo "Total rounds: " . count($rounds) . "\n";
echo "Total judges: " . count($judges) . "\n";
echo "Total contestants: " . count($contestants) . "\n";
echo "\n";
echo "You can now use this file to manually input scores.\n";
echo "Format: Round → Judge → Contestant → Criteria Scores\n";
