<?php
/**
 * Populate Scores on All Rounds
 * 
 * This script generates test scores for all rounds in all events.
 * It creates scores for all judges and all contestants in each round.
 * 
 * Usage: php populate_all_rounds_scores.php [event_id]
 * If event_id is not provided, it will populate all events.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/ScoringEngine.php';

// Initialize
$db = Database::getInstance();
$scoringEngine = new ScoringEngine();

echo "=== Populate Scores on All Rounds ===\n\n";

// Get event ID from command line or use all events
$eventId = isset($argv[1]) ? (int)$argv[1] : null;

if ($eventId) {
    $events = $db->fetchAll("SELECT * FROM events WHERE id = ?", [$eventId]);
    if (empty($events)) {
        echo "ERROR: Event ID {$eventId} not found.\n";
        exit(1);
    }
} else {
    $events = $db->fetchAll("SELECT * FROM events ORDER BY id");
    if (empty($events)) {
        echo "ERROR: No events found.\n";
        exit(1);
    }
}

echo "Found " . count($events) . " event(s) to process.\n\n";

$totalRounds = 0;
$totalScores = 0;

foreach ($events as $event) {
    echo "=== Processing Event: {$event['name']} (ID: {$event['id']}) ===\n";
    
    // Get all rounds for this event
    $rounds = $db->fetchAll(
        "SELECT r.*, el.name as level_name, el.order as level_order
         FROM rounds r
         JOIN event_levels el ON r.level_id = el.id
         WHERE el.event_id = ?
         ORDER BY el.order, r.order",
        [$event['id']]
    );
    
    if (empty($rounds)) {
        echo "  No rounds found for this event.\n\n";
        continue;
    }
    
    echo "  Found " . count($rounds) . " round(s).\n\n";
    
    foreach ($rounds as $round) {
        echo "  Processing Round: {$round['name']} (Level: {$round['level_name']})\n";
        $totalRounds++;
        
        // Get criteria for this round
        $criteria = $db->fetchAll(
            "SELECT c.*, cw.weight
             FROM criteria_weights cw
             JOIN criteria c ON cw.criteria_id = c.id
             WHERE cw.round_id = ? AND cw.is_active = 1
             ORDER BY c.id",
            [$round['id']]
        );
        
        if (empty($criteria)) {
            echo "    ⚠ No criteria found. Skipping.\n";
            continue;
        }
        
        // Get contestants for this round (considering elimination)
        $currentLevel = $db->fetchOne(
            "SELECT * FROM event_levels WHERE id = ?",
            [$round['level_id']]
        );
        
        $previousLevel = $db->fetchOne(
            "SELECT * FROM event_levels 
             WHERE event_id = ? AND `order` < ?
             ORDER BY `order` DESC LIMIT 1",
            [$event['id'], $currentLevel['order']]
        );
        
        if ($previousLevel && $previousLevel['advance_count']) {
            // Get qualified contestants from previous level
            $contestants = $db->fetchAll(
                "SELECT c.*
                 FROM contestants c
                 WHERE c.event_id = ? AND c.status = 'Active' AND c.qualified_for_level_id = ?
                 ORDER BY CAST(c.contestant_number AS UNSIGNED), c.contestant_number",
                [$event['id'], $currentLevel['id']]
            );
        } else {
            // First level OR previous level had no elimination - show all active contestants
            $contestants = $db->fetchAll(
                "SELECT c.*
                 FROM contestants c
                 WHERE c.event_id = ? AND c.status = 'Active'
                 ORDER BY CAST(c.contestant_number AS UNSIGNED), c.contestant_number",
                [$event['id']]
            );
        }
        
        if (empty($contestants)) {
            echo "    ⚠ No contestants found. Skipping.\n";
            continue;
        }
        
        // Get judges assigned to this round
        $judges = $db->fetchAll(
            "SELECT DISTINCT j.id, j.judge_number, u.full_name as judge_name, j.user_id
             FROM judges j
             JOIN users u ON j.user_id = u.id
             JOIN judge_assignments ja ON j.id = ja.judge_id
             WHERE ja.round_id = ? AND ja.is_active = 1
             ORDER BY j.judge_number",
            [$round['id']]
        );
        
        if (empty($judges)) {
            echo "    ⚠ No judges assigned. Skipping.\n";
            continue;
        }
        
        echo "    Contestants: " . count($contestants) . ", Judges: " . count($judges) . ", Criteria: " . count($criteria) . "\n";
        
        $roundScoreCount = 0;
        
        // Generate scores for each contestant-judge combination
        foreach ($contestants as $contestantIndex => $contestant) {
            foreach ($judges as $judgeIndex => $judge) {
                // Check if score already exists
                $existingScore = $db->fetchOne(
                    "SELECT id FROM scores 
                     WHERE round_id = ? AND contestant_id = ? AND judge_id = ?",
                    [$round['id'], $contestant['id'], $judge['id']]
                );
                
                if ($existingScore) {
                    $scoreId = $existingScore['id'];
                    // Update existing score
                    $db->query(
                        "UPDATE scores SET is_submitted = 1, is_draft = 0 WHERE id = ?",
                        [$scoreId]
                    );
                } else {
                    // Create new score
                    $db->query(
                        "INSERT INTO scores (round_id, contestant_id, judge_id, is_submitted, is_draft, created_at)
                         VALUES (?, ?, ?, 1, 0, NOW())",
                        [$round['id'], $contestant['id'], $judge['id']]
                    );
                    $scoreId = $db->lastInsertId();
                }
                
                // Generate varied scores based on contestant and judge index
                $baseScore = 70 + (($contestantIndex % 5) * 5) + (($judgeIndex % 3) * 2);
                $baseScore = min(100, max(50, $baseScore)); // Keep between 50-100
                
                // Clear existing score details
                $db->query("DELETE FROM score_details WHERE score_id = ?", [$scoreId]);
                
                // Generate score details for each criterion
                foreach ($criteria as $criterionIndex => $criterion) {
                    // Vary scores slightly per criterion
                    $variation = ($criterionIndex % 3) * 2 - 2; // -2, 0, 2
                    $percentage = ($baseScore + $variation) / 100;
                    $percentage = max(0.5, min(1.0, $percentage)); // Keep between 50-100%
                    
                    $rawScore = round($criterion['max_score'] * $percentage, 3);
                    
                    // Insert score detail
                    $db->query(
                        "INSERT INTO score_details (score_id, criteria_id, raw_score, weighted_score)
                         VALUES (?, ?, ?, 0)",
                        [$scoreId, $criterion['id'], $rawScore]
                    );
                }
                
                // Calculate the score
                $scoringEngine->calculateScore($scoreId);
                $roundScoreCount++;
                $totalScores++;
            }
        }
        
        echo "    ✓ Generated {$roundScoreCount} scores for this round.\n";
        
        // Calculate rankings for this round
        $scoringEngine->calculateRankings($round['id']);
        echo "    ✓ Calculated rankings for this round.\n\n";
    }
    
    echo "\n";
}

echo "=== Summary ===\n";
echo "Total Rounds Processed: {$totalRounds}\n";
echo "Total Scores Generated: {$totalScores}\n";
echo "\n=== Complete ===\n";
echo "All scores have been populated and rankings calculated.\n";
