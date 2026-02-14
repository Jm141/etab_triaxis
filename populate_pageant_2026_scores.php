<?php
/**
 * Populate Judge Scores for Pageant 2026
 * 
 * This script populates scores for all judges in the Pageant 2026 event
 * and generates a summary document for manual calculation verification.
 * 
 * Usage: php populate_pageant_2026_scores.php
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/ScoringEngine.php';

// Initialize
$db = Database::getInstance();
$scoringEngine = new ScoringEngine();

echo "=== Populate Judge Scores for Pageant 2026 ===\n\n";

// Find the Pageant 2026 event
$event = $db->fetchOne(
    "SELECT * FROM events 
     WHERE name LIKE '%pageant%2026%' OR name LIKE '%pagaent%2026%' OR name LIKE '%2026%pageant%' OR name LIKE '%2026%pagaent%'
     ORDER BY id DESC
     LIMIT 1"
);

if (!$event) {
    echo "ERROR: Pageant 2026 event not found.\n";
    echo "Available events:\n";
    $allEvents = $db->fetchAll("SELECT id, name FROM events ORDER BY id DESC LIMIT 10");
    foreach ($allEvents as $e) {
        echo "  - ID {$e['id']}: {$e['name']}\n";
    }
    exit(1);
}

echo "Found Event: {$event['name']} (ID: {$event['id']})\n\n";

// Get all rounds for this event
$rounds = $db->fetchAll(
    "SELECT r.*, el.name as level_name, el.`order` as level_order
     FROM rounds r
     JOIN event_levels el ON r.level_id = el.id
     WHERE el.event_id = ?
     ORDER BY el.`order` ASC, r.`order` ASC",
    [$event['id']]
);

if (empty($rounds)) {
    echo "ERROR: No rounds found for this event.\n";
    exit(1);
}

echo "=== Rounds ===\n";
foreach ($rounds as $round) {
    echo "  - Round ID {$round['id']}: {$round['level_name']} - {$round['name']}\n";
}
echo "\n";

// Get all judges for this event
$judges = $db->fetchAll(
    "SELECT j.*, u.full_name, u.username
     FROM judges j
     JOIN users u ON j.user_id = u.id
     WHERE j.event_id = ? AND j.is_active = 1
     ORDER BY j.id",
    [$event['id']]
);

if (empty($judges)) {
    echo "ERROR: No active judges found for this event.\n";
    exit(1);
}

echo "=== Judges ===\n";
foreach ($judges as $judge) {
    echo "  - Judge ID {$judge['id']}: {$judge['full_name']} ({$judge['username']})\n";
}
echo "\n";

// Get all contestants for this event
$contestants = $db->fetchAll(
    "SELECT * FROM contestants 
     WHERE event_id = ? AND status = 'Active'
     ORDER BY CAST(contestant_number AS UNSIGNED), contestant_number",
    [$event['id']]
);

if (empty($contestants)) {
    echo "ERROR: No active contestants found for this event.\n";
    exit(1);
}

echo "=== Contestants ===\n";
foreach ($contestants as $contestant) {
    echo "  - Contestant #{$contestant['contestant_number']}: {$contestant['name']} (ID: {$contestant['id']})\n";
}
echo "\n";

// Score summary array
$scoreSummary = [];

// Process each round
foreach ($rounds as $round) {
    echo "=== Processing Round: {$round['level_name']} - {$round['name']} (ID: {$round['id']}) ===\n";
    
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
        echo "  WARNING: No criteria found for this round. Skipping...\n\n";
        continue;
    }
    
    echo "  Criteria:\n";
    foreach ($criteria as $c) {
        echo "    - {$c['name']}: Max = {$c['max_score']}, Weight = {$c['weight']}\n";
    }
    echo "\n";
    
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
        echo "  WARNING: No judges assigned to this round. Skipping...\n\n";
        continue;
    }
    
    echo "  Judges assigned: " . count($roundJudges) . "\n\n";
    
    // Generate scores for each judge-contestant combination
    foreach ($roundJudges as $judge) {
        echo "  Processing Judge: {$judge['full_name']} (ID: {$judge['id']})\n";
        
        foreach ($contestants as $contestantIndex => $contestant) {
            // Generate realistic scores (varying by contestant and judge)
            // Use a pattern that creates variety but realistic scores
            $baseScore = 7.0 + ($contestantIndex * 0.3) + (($judge['id'] % 3) * 0.2);
            
            // Check if score already exists
            $existingScore = $db->fetchOne(
                "SELECT id, is_submitted FROM scores 
                 WHERE round_id = ? AND contestant_id = ? AND judge_id = ?",
                [$round['id'], $contestant['id'], $judge['id']]
            );
            
            // Update existing scores even if submitted to add decimal values
            $scoreId = null;
            if ($existingScore) {
                $scoreId = $existingScore['id'];
                // Delete existing score details
                $db->query("DELETE FROM score_details WHERE score_id = ?", [$scoreId]);
                // Reset submission status to allow update
                $db->query("UPDATE scores SET is_submitted = 0 WHERE id = ?", [$scoreId]);
            } else {
                // Create new score record
                $db->query(
                    "INSERT INTO scores (judge_id, contestant_id, round_id, is_submitted, is_draft, submitted_at, ip_address)
                     VALUES (?, ?, ?, 1, 0, NOW(), ?)",
                    [$judge['id'], $contestant['id'], $round['id'], $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']
                );
                $scoreId = $db->lastInsertId();
            }
            
            $rawScores = [];
            $totalRaw = 0;
            
            // Generate scores for each criterion
            foreach ($criteria as $criterionIndex => $criterion) {
                // Generate score based on base score, adjusted for each criterion
                $maxScore = (float)$criterion['max_score'];
                
                // Create variation: some contestants score higher in certain criteria
                // Use more complex variation to get diverse decimal values
                $variation = sin($contestantIndex + $criterionIndex) * 0.8 + cos($judge['id'] + $criterionIndex) * 0.3;
                
                // Add random decimal component (0.0 to 0.999)
                $randomDecimal = mt_rand(0, 999) / 1000;
                
                // Calculate base score with variations
                $rawScore = min($maxScore, max(0, $baseScore + $variation + ($randomDecimal * 0.5)));
                
                // Round to 3 decimal places to ensure we have decimals
                $rawScore = round($rawScore, 3);
                
                // If we got a whole number, add a small decimal component
                if ($rawScore == floor($rawScore)) {
                    $rawScore += mt_rand(1, 999) / 1000;
                    $rawScore = min($maxScore, $rawScore);
                    $rawScore = round($rawScore, 3);
                }
                
                // Ensure it doesn't exceed max
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
            
            // Get calculated total
            $calculatedScore = $db->fetchOne(
                "SELECT total_score FROM scores WHERE id = ?",
                [$scoreId]
            );
            
            // Store in summary
            $scoreSummary[] = [
                'round_id' => $round['id'],
                'round_name' => $round['name'],
                'level_name' => $round['level_name'],
                'judge_id' => $judge['id'],
                'judge_name' => $judge['full_name'],
                'contestant_id' => $contestant['id'],
                'contestant_number' => $contestant['contestant_number'],
                'contestant_name' => $contestant['name'],
                'raw_scores' => $rawScores,
                'total_raw' => $totalRaw,
                'total_score' => $calculatedScore['total_score'] ?? 0
            ];
            
            echo "    Contestant #{$contestant['contestant_number']}: Total Raw = {$totalRaw}, Weighted = " . ($calculatedScore['total_score'] ?? 0) . "\n";
        }
        
        echo "\n";
    }
    
    echo "\n";
}

// Generate summary document
$summaryFile = __DIR__ . '/PAGEANT_2026_SCORES_SUMMARY.md';
$summaryContent = "# Pageant 2026 - Judge Scores Summary\n\n";
$summaryContent .= "**Event:** {$event['name']}\n";
$summaryContent .= "**Generated:** " . date('Y-m-d H:i:s') . "\n\n";
$summaryContent .= "---\n\n";

// Group by round
$roundsGrouped = [];
foreach ($scoreSummary as $score) {
    $roundKey = $score['round_id'];
    if (!isset($roundsGrouped[$roundKey])) {
        $roundsGrouped[$roundKey] = [
            'round_name' => $score['round_name'],
            'level_name' => $score['level_name'],
            'scores' => []
        ];
    }
    $roundsGrouped[$roundKey]['scores'][] = $score;
}

foreach ($roundsGrouped as $roundId => $roundData) {
    $summaryContent .= "## {$roundData['level_name']} - {$roundData['round_name']}\n\n";
    
    // Group by judge
    $judgesGrouped = [];
    foreach ($roundData['scores'] as $score) {
        $judgeKey = $score['judge_id'];
        if (!isset($judgesGrouped[$judgeKey])) {
            $judgesGrouped[$judgeKey] = [
                'judge_name' => $score['judge_name'],
                'scores' => []
            ];
        }
        $judgesGrouped[$judgeKey]['scores'][] = $score;
    }
    
    foreach ($judgesGrouped as $judgeId => $judgeData) {
        $summaryContent .= "### Judge: {$judgeData['judge_name']}\n\n";
        $summaryContent .= "| Contestant | ";
        
        // Get criteria names for header
        $firstScore = $judgeData['scores'][0];
        $criteriaNames = [];
        foreach ($firstScore['raw_scores'] as $criteriaId => $rawScore) {
            $criterion = $db->fetchOne("SELECT name, max_score FROM criteria WHERE id = ?", [$criteriaId]);
            if ($criterion) {
                $criteriaNames[$criteriaId] = $criterion['name'];
                $summaryContent .= "{$criterion['name']} (Max: {$criterion['max_score']}) | ";
            }
        }
        $summaryContent .= "Total Raw | Weighted Total |\n";
        $summaryContent .= "|----------|";
        foreach ($criteriaNames as $id => $name) {
            $summaryContent .= str_repeat('-', strlen($name) + 10) . "|";
        }
        $summaryContent .= "-----------|----------------|\n";
        
        // Sort by contestant number
        usort($judgeData['scores'], function($a, $b) {
            return (int)$a['contestant_number'] - (int)$b['contestant_number'];
        });
        
        foreach ($judgeData['scores'] as $score) {
            $summaryContent .= "| #{$score['contestant_number']} - {$score['contestant_name']} | ";
            foreach ($criteriaNames as $criteriaId => $name) {
                $rawScore = $score['raw_scores'][$criteriaId] ?? 0;
                $summaryContent .= sprintf("%.3f | ", $rawScore);
            }
            $summaryContent .= sprintf("%.3f | %.3f |\n", $score['total_raw'], $score['total_score']);
        }
        
        $summaryContent .= "\n";
    }
    
    $summaryContent .= "---\n\n";
}

// Add calculation guide
$summaryContent .= "## Manual Calculation Guide\n\n";
$summaryContent .= "### Formula:\n";
$summaryContent .= "For each criterion:\n";
$summaryContent .= "```\n";
$summaryContent .= "Weighted Score = Raw Score × (Weight / 100)\n";
$summaryContent .= "```\n\n";
$summaryContent .= "Total Weighted Score = Sum of all Weighted Scores\n\n";
$summaryContent .= "### Example:\n";
$summaryContent .= "If a contestant receives:\n";
$summaryContent .= "- Criterion 1: Raw Score = 8.5, Weight = 30%\n";
$summaryContent .= "- Criterion 2: Raw Score = 7.0, Weight = 40%\n";
$summaryContent .= "- Criterion 3: Raw Score = 9.0, Weight = 30%\n\n";
$summaryContent .= "Calculation:\n";
$summaryContent .= "```\n";
$summaryContent .= "Weighted 1 = 8.5 × (30/100) = 2.550\n";
$summaryContent .= "Weighted 2 = 7.0 × (40/100) = 2.800\n";
$summaryContent .= "Weighted 3 = 9.0 × (30/100) = 2.700\n";
$summaryContent .= "Total = 2.550 + 2.800 + 2.700 = 8.050\n";
$summaryContent .= "```\n";

file_put_contents($summaryFile, $summaryContent);

echo "=== Summary Generated ===\n";
echo "Summary file saved to: {$summaryFile}\n";
echo "Total scores populated: " . count($scoreSummary) . "\n";
echo "\n";
echo "You can now manually verify the calculations using the summary document.\n";
