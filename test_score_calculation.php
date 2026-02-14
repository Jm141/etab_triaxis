<?php
/**
 * Test Score Calculation Script
 * 
 * This script fills in test scores and verifies the calculation is correct.
 * 
 * Usage: php test_score_calculation.php [round_id]
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/ScoringEngine.php';

// Initialize
$db = Database::getInstance();
$scoringEngine = new ScoringEngine();

echo "=== Score Calculation Test Script ===\n\n";

// Get round ID from command line or use first available round
$roundId = isset($argv[1]) ? (int)$argv[1] : null;

if (!$roundId) {
    // Get first round with criteria
    $round = $db->fetchOne(
        "SELECT r.*, el.event_id, el.name as level_name, e.name as event_name
         FROM rounds r
         JOIN event_levels el ON r.level_id = el.id
         JOIN events e ON el.event_id = e.id
         WHERE r.id IN (
             SELECT DISTINCT cw.round_id 
             FROM criteria_weights cw
         )
         ORDER BY r.id
         LIMIT 1"
    );
    
    if (!$round) {
        echo "ERROR: No rounds with criteria found. Please create a round with criteria first.\n";
        exit(1);
    }
    
    $roundId = $round['id'];
    echo "Using Round ID: {$roundId} ({$round['event_name']} - {$round['level_name']} - {$round['name']})\n\n";
} else {
    $round = $db->fetchOne(
        "SELECT r.*, el.event_id, el.name as level_name, e.name as event_name
         FROM rounds r
         JOIN event_levels el ON r.level_id = el.id
         JOIN events e ON el.event_id = e.id
         WHERE r.id = ?",
        [$roundId]
    );
    
    if (!$round) {
        echo "ERROR: Round ID {$roundId} not found.\n";
        exit(1);
    }
    
    echo "Testing Round ID: {$roundId} ({$round['event_name']} - {$round['level_name']} - {$round['name']})\n\n";
}

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
    echo "ERROR: No criteria found for round {$roundId}.\n";
    exit(1);
}

echo "=== Criteria for Round ===\n";
$totalMaxScore = 0;
foreach ($criteria as $c) {
    $autoWeight = ($c['max_score'] / array_sum(array_column($criteria, 'max_score'))) * 100;
    echo sprintf(
        "  - %s: Max Score = %.3f, Auto Weight = %.2f%%\n",
        $c['name'],
        $c['max_score'],
        $autoWeight
    );
    $totalMaxScore += $c['max_score'];
}
echo "  Total Max Score: {$totalMaxScore}\n\n";

// Get contestants for this round
$contestants = $db->fetchAll(
    "SELECT * FROM contestants 
     WHERE event_id = ? AND status = 'Active'
     ORDER BY CAST(contestant_number AS UNSIGNED), contestant_number
     LIMIT 5",
    [$round['event_id']]
);

if (empty($contestants)) {
    echo "ERROR: No active contestants found for event {$round['event_id']}.\n";
    exit(1);
}

echo "=== Contestants ===\n";
foreach ($contestants as $c) {
    echo "  - #{$c['contestant_number']}: {$c['name']}\n";
}
echo "\n";

// Get judges assigned to this round
$judges = $db->fetchAll(
    "SELECT DISTINCT u.id, u.username, u.full_name
     FROM users u
     JOIN roles r ON u.role_id = r.id
     JOIN judges j ON u.id = j.user_id
     JOIN judge_assignments ja ON j.id = ja.judge_id
     WHERE r.name = 'Judge' AND ja.round_id = ? AND u.is_active = 1 AND ja.is_active = 1
     LIMIT 3",
    [$roundId]
);

if (empty($judges)) {
    echo "WARNING: No judges assigned to round {$roundId}. Using any available judges...\n\n";
    // Get any judge user
    $judges = $db->fetchAll(
        "SELECT DISTINCT u.id, u.username, u.full_name
         FROM users u
         JOIN roles r ON u.role_id = r.id
         WHERE r.name = 'Judge' AND u.is_active = 1
         LIMIT 3"
    );
}

if (empty($judges)) {
    echo "ERROR: No judges found in the system.\n";
    exit(1);
}

echo "=== Judges ===\n";
foreach ($judges as $j) {
    echo "  - {$j['username']} ({$j['full_name']})\n";
}
echo "\n";

// Test data: Different score patterns for testing
$testScores = [
    // Pattern 1: Perfect scores (100%)
    'perfect' => function($max) { return $max; },
    
    // Pattern 2: 80% scores
    'eighty' => function($max) { return round($max * 0.8, 3); },
    
    // Pattern 3: 50% scores
    'fifty' => function($max) { return round($max * 0.5, 3); },
    
    // Pattern 4: Mixed scores (high, medium, low)
    'mixed' => function($max, $index) {
        $percentages = [0.9, 0.7, 0.6, 0.8, 0.5];
        return round($max * ($percentages[$index % count($percentages)]), 3);
    },
    
    // Pattern 5: Decimal precision test
    'decimal' => function($max) { return round($max * 0.833, 3); }, // 83.3%
];

echo "=== Filling Test Scores ===\n\n";

$scoreCount = 0;
$testCases = [];

foreach ($contestants as $contestantIndex => $contestant) {
    foreach ($judges as $judgeIndex => $judge) {
        // Use different patterns for variety
        $pattern = ['perfect', 'eighty', 'fifty', 'mixed', 'decimal'][($contestantIndex + $judgeIndex) % 5];
        
        // Check if score already exists
        $existingScore = $db->fetchOne(
            "SELECT id FROM scores 
             WHERE round_id = ? AND contestant_id = ? AND judge_id = ?",
            [$roundId, $contestant['id'], $judge['id']]
        );
        
        if ($existingScore) {
            $scoreId = $existingScore['id'];
            echo "  Score exists for Contestant #{$contestant['contestant_number']} (Judge: {$judge['username']}) - Updating...\n";
        } else {
            // Create new score
            $db->query(
                "INSERT INTO scores (round_id, contestant_id, judge_id, is_submitted, is_draft, created_at)
                 VALUES (?, ?, ?, 0, 1, NOW())",
                [$roundId, $contestant['id'], $judge['id']]
            );
            $scoreId = $db->lastInsertId();
            echo "  Created score for Contestant #{$contestant['contestant_number']} (Judge: {$judge['username']})...\n";
        }
        
        $rawScores = [];
        $totalRaw = 0;
        
        // Fill score details
        foreach ($criteria as $index => $criterion) {
            // Calculate test score based on pattern
            if ($pattern === 'mixed') {
                $rawScore = $testScores['mixed']($criterion['max_score'], $index);
            } else {
                $rawScore = $testScores[$pattern]($criterion['max_score']);
            }
            
            $rawScores[] = $rawScore;
            $totalRaw += $rawScore;
            
            // Check if detail exists
            $existingDetail = $db->fetchOne(
                "SELECT id FROM score_details 
                 WHERE score_id = ? AND criteria_id = ?",
                [$scoreId, $criterion['id']]
            );
            
            if ($existingDetail) {
                $db->query(
                    "UPDATE score_details SET raw_score = ? WHERE id = ?",
                    [$rawScore, $existingDetail['id']]
                );
            } else {
                $db->query(
                    "INSERT INTO score_details (score_id, criteria_id, raw_score, weighted_score)
                     VALUES (?, ?, ?, 0)",
                    [$scoreId, $criterion['id'], $rawScore]
                );
            }
        }
        
        // Calculate expected final score
        $expectedFinal = round(($totalRaw / $totalMaxScore) * 100, 3);
        
        $testCases[] = [
            'score_id' => $scoreId,
            'contestant' => $contestant,
            'judge' => $judge,
            'raw_scores' => $rawScores,
            'total_raw' => $totalRaw,
            'expected_final' => $expectedFinal,
            'pattern' => $pattern
        ];
        
        echo "    Raw Scores: " . implode(', ', array_map(function($r) { return number_format($r, 3); }, $rawScores)) . "\n";
        echo "    Total Raw: {$totalRaw}, Expected Final: {$expectedFinal}\n";
        
        $scoreCount++;
    }
}

echo "\n=== Calculating Scores ===\n\n";

// Calculate all scores
foreach ($testCases as $testCase) {
    $calculatedFinal = $scoringEngine->calculateScore($testCase['score_id']);
    
    $passed = abs($calculatedFinal - $testCase['expected_final']) < 0.001;
    $status = $passed ? '✓ PASS' : '✗ FAIL';
    
    echo sprintf(
        "%s Contestant #%s (Judge: %s)\n",
        $status,
        $testCase['contestant']['contestant_number'],
        $testCase['judge']['username']
    );
    echo sprintf(
        "    Expected: %.3f, Calculated: %.3f, Difference: %.6f\n",
        $testCase['expected_final'],
        $calculatedFinal,
        abs($calculatedFinal - $testCase['expected_final'])
    );
    
    if (!$passed) {
        echo "    ⚠ WARNING: Calculation mismatch!\n";
    }
}

echo "\n=== Marking Scores as Submitted ===\n\n";

// Mark all test scores as submitted so rankings can be calculated
foreach ($testCases as $testCase) {
    $db->query(
        "UPDATE scores SET is_submitted = 1, is_draft = 0 WHERE id = ?",
        [$testCase['score_id']]
    );
}
echo "All scores marked as submitted.\n\n";

echo "=== Calculating Rankings ===\n\n";

// Calculate rankings
$rankings = $scoringEngine->calculateRankings($roundId);
$scoringEngine->saveRankings($roundId, $rankings);

echo "Rankings:\n";
foreach ($rankings as $ranking) {
    echo sprintf(
        "  Rank %d: Contestant #%s - Average: %.3f\n",
        $ranking['rank'],
        $ranking['contestant_number'],
        $ranking['average']
    );
}

echo "\n=== Verification Summary ===\n\n";

// Verify all calculations
$allPassed = true;
$verificationResults = [];

foreach ($testCases as $testCase) {
    $score = $db->fetchOne(
        "SELECT total_score FROM scores WHERE id = ?",
        [$testCase['score_id']]
    );
    
    $calculated = $score['total_score'];
    $expected = $testCase['expected_final'];
    $diff = abs($calculated - $expected);
    $passed = $diff < 0.001;
    
    if (!$passed) {
        $allPassed = false;
    }
    
    $verificationResults[] = [
        'contestant' => $testCase['contestant']['contestant_number'],
        'judge' => $testCase['judge']['username'],
        'expected' => $expected,
        'calculated' => $calculated,
        'diff' => $diff,
        'passed' => $passed
    ];
}

// Display verification table
echo str_pad("Contestant", 15) . str_pad("Judge", 20) . str_pad("Expected", 12) . str_pad("Calculated", 12) . str_pad("Diff", 12) . "Status\n";
echo str_repeat("-", 80) . "\n";

foreach ($verificationResults as $result) {
    echo sprintf(
        "%-15s %-20s %-12.3f %-12.3f %-12.6f %s\n",
        "#" . $result['contestant'],
        $result['judge'],
        $result['expected'],
        $result['calculated'],
        $result['diff'],
        $result['passed'] ? '✓ PASS' : '✗ FAIL'
    );
}

echo "\n";

if ($allPassed) {
    echo "✓ ALL CALCULATIONS PASSED!\n";
    echo "The scoring system is working correctly.\n";
} else {
    echo "✗ SOME CALCULATIONS FAILED!\n";
    echo "Please review the differences above.\n";
}

echo "\n=== Test Complete ===\n";
echo "Total scores created/updated: {$scoreCount}\n";
echo "Round ID: {$roundId}\n";

