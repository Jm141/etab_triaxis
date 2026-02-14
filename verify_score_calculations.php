<?php
/**
 * Verify Score Calculations
 * 
 * This script verifies that the score calculations in the database are correct
 * by manually recalculating a sample of scores and comparing them.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Database.php';

$db = Database::getInstance();

echo "=== Verifying Score Calculations ===\n\n";

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

// Get a sample of scores to verify (first 5 scores)
$sampleScores = $db->fetchAll(
    "SELECT s.*, c.contestant_number, c.name as contestant_name, r.name as round_name
     FROM scores s
     JOIN contestants c ON s.contestant_id = c.id
     JOIN rounds r ON s.round_id = r.id
     JOIN event_levels el ON r.level_id = el.id
     WHERE el.event_id = ? AND s.is_submitted = 1
     ORDER BY s.id
     LIMIT 5",
    [$event['id']]
);

if (empty($sampleScores)) {
    echo "ERROR: No submitted scores found in database.\n";
    exit(1);
}

echo "Verifying " . count($sampleScores) . " sample scores...\n\n";

$allCorrect = true;
$errors = [];

foreach ($sampleScores as $score) {
    echo "--- Score ID: {$score['id']} ---\n";
    echo "Contestant: #{$score['contestant_number']} - {$score['contestant_name']}\n";
    echo "Round: {$score['round_name']}\n";
    echo "Stored Total Score: {$score['total_score']}\n";
    
    // Get criteria and weights for this round
    $criteriaWeights = $db->fetchAll(
        "SELECT cw.*, c.max_score, c.name as criteria_name
         FROM criteria_weights cw
         JOIN criteria c ON cw.criteria_id = c.id
         WHERE cw.round_id = ? AND cw.is_active = 1
         ORDER BY c.id",
        [$score['round_id']]
    );
    
    // Get score details
    $scoreDetails = $db->fetchAll(
        "SELECT sd.*, c.name as criteria_name, c.max_score
         FROM score_details sd
         JOIN criteria c ON sd.criteria_id = c.id
         WHERE sd.score_id = ?
         ORDER BY c.id",
        [$score['id']]
    );
    
    // Calculate total max score
    $totalMaxScore = array_sum(array_column($criteriaWeights, 'max_score'));
    
    echo "\nCriteria Breakdown:\n";
    $calculatedTotalRaw = 0;
    $calculatedTotalWeighted = 0;
    
    foreach ($scoreDetails as $detail) {
        $rawScore = (float)$detail['raw_score'];
        $maxScore = (float)$detail['max_score'];
        $storedWeighted = (float)$detail['weighted_score'];
        
        // Calculate weight automatically (proportional to max_score)
        $autoWeight = ($maxScore / $totalMaxScore) * 100;
        
        // Calculate normalized score
        $normalizedScore = ($rawScore / $maxScore) * 100;
        
        // Calculate weighted score
        $calculatedWeighted = ($normalizedScore * $autoWeight) / 100;
        $calculatedWeighted = round($calculatedWeighted, 3);
        
        $calculatedTotalRaw += $rawScore;
        $calculatedTotalWeighted += $calculatedWeighted;
        
        echo "  {$detail['criteria_name']}:\n";
        echo "    Raw Score: {$rawScore} (Max: {$maxScore})\n";
        echo "    Weight: " . number_format($autoWeight, 2) . "%\n";
        echo "    Stored Weighted: {$storedWeighted}\n";
        echo "    Calculated Weighted: {$calculatedWeighted}\n";
        
        // Check if weighted score matches
        $diff = abs($storedWeighted - $calculatedWeighted);
        if ($diff > 0.001) {
            echo "    ⚠ WARNING: Weighted score mismatch! (Difference: {$diff})\n";
            $allCorrect = false;
            $errors[] = "Score ID {$score['id']}, Criteria '{$detail['criteria_name']}': Stored={$storedWeighted}, Calculated={$calculatedWeighted}";
        } else {
            echo "    ✓ Weighted score matches\n";
        }
        echo "\n";
    }
    
    $calculatedTotalWeighted = round($calculatedTotalWeighted, 3);
    $storedTotal = (float)$score['total_score'];
    
    echo "Total Raw Score: {$calculatedTotalRaw}\n";
    echo "Stored Total Weighted: {$storedTotal}\n";
    echo "Calculated Total Weighted: {$calculatedTotalWeighted}\n";
    
    // Check if total matches
    $totalDiff = abs($storedTotal - $calculatedTotalWeighted);
    if ($totalDiff > 0.001) {
        echo "⚠ WARNING: Total score mismatch! (Difference: {$totalDiff})\n";
        $allCorrect = false;
        $errors[] = "Score ID {$score['id']}: Total Stored={$storedTotal}, Calculated={$calculatedTotalWeighted}";
    } else {
        echo "✓ Total score matches\n";
    }
    
    echo "\n";
}

// Summary
echo "=== Verification Summary ===\n";
if ($allCorrect) {
    echo "✅ ALL CALCULATIONS ARE CORRECT!\n";
    echo "All " . count($sampleScores) . " sample scores have been verified.\n";
} else {
    echo "❌ ERRORS FOUND:\n";
    foreach ($errors as $error) {
        echo "  - {$error}\n";
    }
}

echo "\n";
echo "Total scores in database: ";
$totalCount = $db->fetchOne("SELECT COUNT(*) as count FROM scores WHERE is_submitted = 1")['count'];
echo $totalCount . "\n";
