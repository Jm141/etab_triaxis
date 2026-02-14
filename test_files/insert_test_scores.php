<?php
/**
 * Insert Test Scores for Printing Test
 * 
 * This script inserts test scores into the database and marks them as submitted
 * so you can test the printing functionality.
 * 
 * Usage: php insert_test_scores.php [round_id]
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/ScoringEngine.php';

// Initialize
$db = Database::getInstance();
$scoringEngine = new ScoringEngine();

echo "=== Insert Test Scores for Printing Test ===\n\n";

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
    
    echo "Using Round ID: {$roundId} ({$round['event_name']} - {$round['level_name']} - {$round['name']})\n\n";
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

echo "=== Criteria ===\n";
$totalMaxScore = 0;
foreach ($criteria as $c) {
    echo sprintf("  - %s: Max Score = %.3f\n", $c['name'], $c['max_score']);
    $totalMaxScore += $c['max_score'];
}
echo "  Total Max Score: {$totalMaxScore}\n\n";

// Get contestants for this round
$contestants = $db->fetchAll(
    "SELECT * FROM contestants 
     WHERE event_id = ? AND status = 'Active'
     ORDER BY CAST(contestant_number AS UNSIGNED), contestant_number",
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

// Get judges assigned to this round (need judge.id, not user.id)
$judges = $db->fetchAll(
    "SELECT DISTINCT j.id as judge_id, u.id as user_id, u.username, u.full_name
     FROM users u
     JOIN roles r ON u.role_id = r.id
     JOIN judges j ON u.id = j.user_id
     JOIN judge_assignments ja ON j.id = ja.judge_id
     WHERE r.name = 'Judge' AND ja.round_id = ? AND u.is_active = 1 AND ja.is_active = 1",
    [$roundId]
);

if (empty($judges)) {
    echo "WARNING: No judges assigned to round {$roundId}. Using any available judges...\n\n";
    // Get any judge
    $judges = $db->fetchAll(
        "SELECT DISTINCT j.id as judge_id, u.id as user_id, u.username, u.full_name
         FROM users u
         JOIN roles r ON u.role_id = r.id
         JOIN judges j ON u.id = j.user_id
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

// Test score patterns - varied scores for realistic testing
$testPatterns = [
    // Pattern 1: High scores (85-95%)
    'high' => function($max, $index) {
        $percentages = [0.90, 0.85, 0.92, 0.88, 0.95];
        return round($max * ($percentages[$index % count($percentages)]), 3);
    },
    
    // Pattern 2: Medium scores (70-80%)
    'medium' => function($max, $index) {
        $percentages = [0.75, 0.70, 0.80, 0.72, 0.78];
        return round($max * ($percentages[$index % count($percentages)]), 3);
    },
    
    // Pattern 3: Mixed scores (60-90%)
    'mixed' => function($max, $index) {
        $percentages = [0.85, 0.65, 0.90, 0.70, 0.80];
        return round($max * ($percentages[$index % count($percentages)]), 3);
    },
    
    // Pattern 4: Low scores (50-70%)
    'low' => function($max, $index) {
        $percentages = [0.60, 0.55, 0.65, 0.50, 0.70];
        return round($max * ($percentages[$index % count($percentages)]), 3);
    },
    
    // Pattern 5: Excellent scores (90-100%)
    'excellent' => function($max, $index) {
        $percentages = [0.95, 0.92, 0.98, 0.90, 1.00];
        return round($max * ($percentages[$index % count($percentages)]), 3);
    },
];

echo "=== Inserting Test Scores ===\n\n";

$scoreCount = 0;
$insertedScores = [];

foreach ($contestants as $contestantIndex => $contestant) {
    foreach ($judges as $judgeIndex => $judge) {
        // Use different patterns for variety
        $patternNames = ['high', 'medium', 'mixed', 'low', 'excellent'];
        $pattern = $patternNames[($contestantIndex + $judgeIndex) % count($patternNames)];
        
        // Check if score already exists
        $existingScore = $db->fetchOne(
            "SELECT id FROM scores 
             WHERE round_id = ? AND contestant_id = ? AND judge_id = ?",
            [$roundId, $contestant['id'], $judge['judge_id']]
        );
        
        if ($existingScore) {
            $scoreId = $existingScore['id'];
            echo "  Updating score for Contestant #{$contestant['contestant_number']} (Judge: {$judge['username']})...\n";
        } else {
            // Create new score
            $db->query(
                "INSERT INTO scores (round_id, contestant_id, judge_id, is_submitted, is_draft, created_at)
                 VALUES (?, ?, ?, 1, 0, NOW())",
                [$roundId, $contestant['id'], $judge['judge_id']]
            );
            $scoreId = $db->lastInsertId();
            echo "  Creating score for Contestant #{$contestant['contestant_number']} (Judge: {$judge['username']})...\n";
        }
        
        $rawScores = [];
        $totalRaw = 0;
        
        // Fill score details
        foreach ($criteria as $index => $criterion) {
            // Calculate test score based on pattern
            $rawScore = $testPatterns[$pattern]($criterion['max_score'], $index);
            
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
        
        // Calculate and save final score
        $finalScore = round(($totalRaw / $totalMaxScore) * 100, 3);
        
        // Update score with calculated total
        $db->query(
            "UPDATE scores SET total_score = ?, is_submitted = 1, is_draft = 0 WHERE id = ?",
            [$finalScore, $scoreId]
        );
        
        // Calculate using ScoringEngine to ensure weighted scores are set
        $scoringEngine->calculateScore($scoreId);
        
        $insertedScores[] = [
            'score_id' => $scoreId,
            'contestant_number' => $contestant['contestant_number'],
            'contestant_name' => $contestant['name'],
            'judge' => $judge['username'],
            'raw_scores' => $rawScores,
            'total_raw' => $totalRaw,
            'final_score' => $finalScore
        ];
        
        echo "    Raw Scores: " . implode(', ', array_map(function($r) { return number_format($r, 3); }, $rawScores)) . "\n";
        echo "    Total Raw: {$totalRaw}, Final Score: {$finalScore}\n";
        
        $scoreCount++;
    }
}

echo "\n=== Calculating Rankings ===\n\n";

// Calculate rankings
$rankings = $scoringEngine->calculateRankings($roundId);
$scoringEngine->saveRankings($roundId, $rankings);

echo "Rankings calculated:\n";
foreach ($rankings as $ranking) {
    echo sprintf(
        "  Rank %d: Contestant #%s (%s) - Average: %.3f\n",
        $ranking['rank'],
        $ranking['contestant_number'],
        $ranking['name'],
        $ranking['average']
    );
}

echo "\n=== Summary ===\n";
echo "Total scores inserted/updated: {$scoreCount}\n";
echo "Round ID: {$roundId}\n";
echo "Event: {$round['event_name']}\n";
echo "Level: {$round['level_name']}\n";
echo "Round: {$round['name']}\n";

echo "\n=== How to Test Printing ===\n";
echo "1. Log in as Super Admin, Event Admin, Event Organizer, or Event Technical Admin\n";
echo "2. Navigate to: Score Management\n";
echo "3. Select Round ID: {$roundId}\n";
echo "4. Click 'Print Scores' button\n";
echo "5. The print view should show all scores grouped by contestant and judge\n\n";

echo "=== Direct Print URL ===\n";
echo "http://localhost/tabulation/score-management/print/{$roundId}\n\n";

echo "=== Test Complete ===\n";

