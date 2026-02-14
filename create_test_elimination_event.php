<?php
/**
 * Create Test Event with Elimination
 * This script creates a complete test event with elimination to verify the system works correctly
 */

require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/ScoringEngine.php';

$db = Database::getInstance();
$scoringEngine = new ScoringEngine();

echo "=== Creating Test Event with Elimination ===\n\n";

// Start transaction
$db->getConnection()->beginTransaction();

try {
    // 1. Create Event
    echo "1. Creating Event...\n";
    $db->query(
        "INSERT INTO events (name, description, event_type, venue, event_date, status, created_by)
         VALUES (?, ?, ?, ?, ?, ?, ?)",
        [
            'Test Elimination Event 2026',
            'Test event to verify elimination system works correctly',
            'Pageant',
            'Test Venue',
            date('Y-m-d'),
            'Ongoing',
            1 // Assuming user ID 1 exists
        ]
    );
    $eventId = $db->lastInsertId();
    echo "   ✓ Event created (ID: {$eventId})\n\n";
    
    // 2. Create Levels
    echo "2. Creating Levels...\n";
    
    // Level 1: Preliminaries (no elimination - all advance)
    $db->query(
        "INSERT INTO event_levels (event_id, name, description, `order`, advance_count, status)
         VALUES (?, ?, ?, ?, ?, ?)",
        [$eventId, 'Preliminaries', 'First level - all contestants participate', 1, null, 'Active']
    );
    $level1Id = $db->lastInsertId();
    echo "   ✓ Level 1: Preliminaries (ID: {$level1Id}) - All advance\n";
    
    // Level 2: Semi-Finals (elimination - top 5 advance)
    $db->query(
        "INSERT INTO event_levels (event_id, name, description, `order`, advance_count, status)
         VALUES (?, ?, ?, ?, ?, ?)",
        [$eventId, 'Semi-Finals', 'Elimination level - top 5 advance', 2, 5, 'Active']
    );
    $level2Id = $db->lastInsertId();
    echo "   ✓ Level 2: Semi-Finals (ID: {$level2Id}) - Top 5 advance\n";
    
    // Level 3: Finals (no elimination - all qualified advance)
    $db->query(
        "INSERT INTO event_levels (event_id, name, description, `order`, advance_count, status)
         VALUES (?, ?, ?, ?, ?, ?)",
        [$eventId, 'Finals', 'Final level - all qualified contestants', 3, null, 'Active']
    );
    $level3Id = $db->lastInsertId();
    echo "   ✓ Level 3: Finals (ID: {$level3Id}) - All advance\n\n";
    
    // 3. Create Rounds
    echo "3. Creating Rounds...\n";
    
    // Level 1 Rounds
    $db->query(
        "INSERT INTO rounds (level_id, name, description, `order`, status)
         VALUES (?, ?, ?, ?, ?)",
        [$level1Id, 'Talent Competition', 'First round', 1, 'Active']
    );
    $round1Id = $db->lastInsertId();
    echo "   ✓ Round 1: Talent Competition (ID: {$round1Id})\n";
    
    $db->query(
        "INSERT INTO rounds (level_id, name, description, `order`, status)
         VALUES (?, ?, ?, ?, ?)",
        [$level1Id, 'Q&A Session', 'Second round', 2, 'Active']
    );
    $round2Id = $db->lastInsertId();
    echo "   ✓ Round 2: Q&A Session (ID: {$round2Id})\n";
    
    // Level 2 Rounds (Elimination level)
    $db->query(
        "INSERT INTO rounds (level_id, name, description, `order`, status)
         VALUES (?, ?, ?, ?, ?)",
        [$level2Id, 'Evening Gown', 'Semi-final round 1', 1, 'Active']
    );
    $round3Id = $db->lastInsertId();
    echo "   ✓ Round 3: Evening Gown (ID: {$round3Id})\n";
    
    $db->query(
        "INSERT INTO rounds (level_id, name, description, `order`, status)
         VALUES (?, ?, ?, ?, ?)",
        [$level2Id, 'Swimsuit', 'Semi-final round 2', 2, 'Active']
    );
    $round4Id = $db->lastInsertId();
    echo "   ✓ Round 4: Swimsuit (ID: {$round4Id})\n";
    
    // Level 3 Rounds (Final level)
    $db->query(
        "INSERT INTO rounds (level_id, name, description, `order`, status)
         VALUES (?, ?, ?, ?, ?)",
        [$level3Id, 'Final Q&A', 'Final round', 1, 'Active']
    );
    $round5Id = $db->lastInsertId();
    echo "   ✓ Round 5: Final Q&A (ID: {$round5Id})\n\n";
    
    // 4. Create Contestants (10 contestants)
    echo "4. Creating Contestants...\n";
    $contestantIds = [];
    for ($i = 1; $i <= 10; $i++) {
        $db->query(
            "INSERT INTO contestants (event_id, contestant_number, name, status)
             VALUES (?, ?, ?, ?)",
            [$eventId, $i, "Test Contestant C{$i}", 'Active']
        );
        $contestantIds[] = $db->lastInsertId();
        echo "   ✓ Contestant #{$i}: Test Contestant C{$i}\n";
    }
    echo "\n";
    
    // 5. Create Criteria
    echo "5. Creating Criteria...\n";
    $criteriaIds = [];
    $criteriaNames = ['Performance', 'Presentation', 'Overall'];
    foreach ($criteriaNames as $idx => $name) {
        $db->query(
            "INSERT INTO criteria (name, description, max_score, event_id)
             VALUES (?, ?, ?, ?)",
            [$name, "{$name} criterion", 100, $eventId]
        );
        $criteriaIds[] = $db->lastInsertId();
        echo "   ✓ Criterion: {$name} (Max: 100)\n";
    }
    echo "\n";
    
    // 6. Assign Criteria to Rounds
    echo "6. Assigning Criteria to Rounds...\n";
    foreach ([$round1Id, $round2Id, $round3Id, $round4Id, $round5Id] as $roundId) {
        foreach ($criteriaIds as $criteriaId) {
            $db->query(
                "INSERT INTO criteria_weights (round_id, criteria_id, weight, is_active)
                 VALUES (?, ?, ?, ?)",
                [$roundId, $criteriaId, 33.33, 1]
            );
        }
        echo "   ✓ Assigned 3 criteria to Round {$roundId}\n";
    }
    echo "\n";
    
    // 7. Create Judges (3 judges)
    echo "7. Creating Judges...\n";
    $judgeIds = [];
    for ($i = 1; $i <= 3; $i++) {
        // Check if user exists, if not create one
        $userId = $db->fetchOne("SELECT id FROM users WHERE username = ?", ["judge{$i}"]);
        if (!$userId) {
            $db->query(
                "INSERT INTO users (username, email, full_name, password_hash, role_id)
                 VALUES (?, ?, ?, ?, ?)",
                [
                    "judge{$i}",
                    "judge{$i}@test.com",
                    "Judge {$i}",
                    password_hash('password123', PASSWORD_DEFAULT),
                    3 // Assuming role_id 3 is Judge
                ]
            );
            $userId = $db->lastInsertId();
        } else {
            $userId = $userId['id'];
        }
        
        $db->query(
            "INSERT INTO judges (event_id, user_id, judge_number, is_active)
             VALUES (?, ?, ?, ?)",
            [$eventId, $userId, $i, 1]
        );
        $judgeIds[] = $db->lastInsertId();
        echo "   ✓ Judge #{$i} created\n";
    }
    echo "\n";
    
    // 8. Assign Judges to Rounds
    echo "8. Assigning Judges to Rounds...\n";
    foreach ([$round1Id, $round2Id, $round3Id, $round4Id, $round5Id] as $roundId) {
        foreach ($judgeIds as $judgeId) {
            $db->query(
                "INSERT INTO judge_assignments (judge_id, round_id, is_active)
                 VALUES (?, ?, ?)",
                [$judgeId, $roundId, 1]
            );
        }
        echo "   ✓ Assigned 3 judges to Round {$roundId}\n";
    }
    echo "\n";
    
    // 9. Populate Scores for Level 1 (Preliminaries) - All contestants
    echo "9. Populating Scores for Level 1 (Preliminaries)...\n";
    $baseScores = [
        1 => [95, 90, 92], // Contestant 1: High scores
        2 => [88, 85, 87],
        3 => [92, 88, 90],
        4 => [85, 82, 84],
        5 => [90, 87, 89], // Contestant 5: Top 5
        6 => [82, 80, 81],
        7 => [87, 84, 86],
        8 => [80, 78, 79],
        9 => [75, 73, 74],
        10 => [70, 68, 69]
    ];
    
    foreach ([$round1Id, $round2Id] as $roundIdx => $roundId) {
        foreach ($contestantIds as $contestantIdx => $contestantId) {
            $contestantNum = $contestantIdx + 1;
            foreach ($judgeIds as $judgeIdx => $judgeId) {
                // Create score
                $db->query(
                    "INSERT INTO scores (round_id, contestant_id, judge_id, is_submitted, total_score)
                     VALUES (?, ?, ?, ?, ?)",
                    [$roundId, $contestantId, $judgeId, 1, 0] // total_score will be calculated
                );
                $scoreId = $db->lastInsertId();
                
                // Create score details
                $baseScore = $baseScores[$contestantNum][$judgeIdx];
                $scoreVariation = ($roundIdx * 2) + ($judgeIdx * 1); // Slight variation per round/judge
                $actualScore = $baseScore + $scoreVariation;
                
                foreach ($criteriaIds as $criteriaIdx => $criteriaId) {
                    $criterionScore = round($actualScore / 3 + ($criteriaIdx * 2), 2);
                    $db->query(
                        "INSERT INTO score_details (score_id, criteria_id, raw_score, weighted_score)
                         VALUES (?, ?, ?, ?)",
                        [$scoreId, $criteriaId, $criterionScore, 0]
                    );
                }
                
                // Calculate total score
                $scoringEngine->calculateScore($scoreId);
            }
            echo "   ✓ Scores created for Contestant #{$contestantNum} in Round {$roundId}\n";
        }
    }
    echo "\n";
    
    // 10. Calculate Rankings for Level 1
    echo "10. Calculating Rankings for Level 1...\n";
    foreach ([$round1Id, $round2Id] as $roundId) {
        $rankings = $scoringEngine->calculateRankings($roundId);
        $scoringEngine->saveRankings($roundId, $rankings);
        echo "   ✓ Rankings calculated for Round {$roundId}\n";
    }
    echo "\n";
    
    // 11. Populate Scores for Level 2 (Semi-Finals) - All contestants still
    echo "11. Populating Scores for Level 2 (Semi-Finals)...\n";
    foreach ([$round3Id, $round4Id] as $roundId) {
        foreach ($contestantIds as $contestantIdx => $contestantId) {
            $contestantNum = $contestantIdx + 1;
            foreach ($judgeIds as $judgeIdx => $judgeId) {
                // Create score
                $db->query(
                    "INSERT INTO scores (round_id, contestant_id, judge_id, is_submitted, total_score)
                     VALUES (?, ?, ?, ?, ?)",
                    [$roundId, $contestantId, $judgeId, 1, 0]
                );
                $scoreId = $db->lastInsertId();
                
                // Create score details (similar scores to maintain ranking)
                $baseScore = $baseScores[$contestantNum][$judgeIdx];
                $scoreVariation = ($roundIdx * 2) + ($judgeIdx * 1);
                $actualScore = $baseScore + $scoreVariation;
                
                foreach ($criteriaIds as $criteriaIdx => $criteriaId) {
                    $criterionScore = round($actualScore / 3 + ($criteriaIdx * 2), 2);
                    $db->query(
                        "INSERT INTO score_details (score_id, criteria_id, raw_score, weighted_score)
                         VALUES (?, ?, ?, ?)",
                        [$scoreId, $criteriaId, $criterionScore, 0]
                    );
                }
                
                // Calculate total score
                $scoringEngine->calculateScore($scoreId);
            }
        }
        echo "   ✓ Scores created for all contestants in Round {$roundId}\n";
    }
    echo "\n";
    
    // 12. Calculate Rankings for Level 2
    echo "12. Calculating Rankings for Level 2...\n";
    foreach ([$round3Id, $round4Id] as $roundId) {
        $rankings = $scoringEngine->calculateRankings($roundId);
        $scoringEngine->saveRankings($roundId, $rankings);
        echo "   ✓ Rankings calculated for Round {$roundId}\n";
    }
    echo "\n";
    
    // 13. Process Elimination (mark top 5 as qualified for Finals)
    echo "13. Processing Elimination (Top 5 qualify for Finals)...\n";
    $levelRankings = $scoringEngine->calculateLevelRankings($level2Id);
    
    if (empty($levelRankings)) {
        throw new Exception("No rankings found for Semi-Finals level");
    }
    
    // Get top 5 (with tie handling)
    $advanceCount = 5;
    $qualifiedContestants = array_slice($levelRankings, 0, $advanceCount);
    
    // Get cutoff score
    $lastQualified = end($qualifiedContestants);
    $cutoffScore = $lastQualified['average'] ?? 0;
    
    // Include all tied contestants
    $allQualified = [];
    foreach ($levelRankings as $ranking) {
        $contestantScore = $ranking['average'] ?? 0;
        if (abs($contestantScore - $cutoffScore) <= 0.001) {
            $allQualified[] = $ranking;
        }
    }
    
    // Mark as qualified
    foreach ($allQualified as $contestant) {
        $db->query(
            "UPDATE contestants SET qualified_for_level_id = ? WHERE id = ?",
            [$level3Id, $contestant['contestant_id']]
        );
        echo "   ✓ Contestant #{$contestant['contestant_number']} ({$contestant['name']}) qualified for Finals\n";
    }
    echo "\n";
    
    // Commit transaction
    $db->getConnection()->commit();
    
    echo "=== SUCCESS ===\n\n";
    echo "Event Created: Test Elimination Event 2026 (ID: {$eventId})\n";
    echo "Levels:\n";
    echo "  - Level 1: Preliminaries (All 10 contestants)\n";
    echo "  - Level 2: Semi-Finals (Top 5 advance)\n";
    echo "  - Level 3: Finals (Qualified contestants only)\n\n";
    echo "Qualified for Finals: " . count($allQualified) . " contestants\n";
    echo "\n";
    echo "=== Verification ===\n";
    echo "To verify elimination works:\n";
    echo "1. Log in as a judge (judge1, judge2, or judge3)\n";
    echo "2. Go to Round 5 (Final Q&A)\n";
    echo "3. You should ONLY see the " . count($allQualified) . " qualified contestants\n";
    echo "4. Contestants who didn't qualify should NOT appear\n\n";
    echo "Event ID: {$eventId}\n";
    echo "Round 5 ID: {$round5Id}\n";
    
} catch (Exception $e) {
    $db->getConnection()->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Transaction rolled back.\n";
    exit(1);
}
