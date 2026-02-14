<?php
/**
 * Verify Elimination Test Event
 */

require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/ScoringEngine.php';

$db = Database::getInstance();
$scoringEngine = new ScoringEngine();

$eventId = 8; // Test event ID

echo "=== Verifying Elimination Test Event ===\n\n";

// Get event
$event = $db->fetchOne("SELECT * FROM events WHERE id = ?", [$eventId]);
if (!$event) {
    echo "ERROR: Event not found.\n";
    exit(1);
}

echo "Event: {$event['name']}\n\n";

// Get levels
$levels = $db->fetchAll(
    "SELECT * FROM event_levels WHERE event_id = ? ORDER BY `order`",
    [$eventId]
);

foreach ($levels as $level) {
    echo "Level: {$level['name']} (ID: {$level['id']}, Advance Count: " . ($level['advance_count'] ?? 'NULL') . ")\n";
    
    // Get rounds
    $rounds = $db->fetchAll(
        "SELECT * FROM rounds WHERE level_id = ? ORDER BY `order`",
        [$level['id']]
    );
    
    foreach ($rounds as $round) {
        echo "  Round: {$round['name']} (ID: {$round['id']})\n";
        
        // Get rankings
        $rankings = $db->fetchAll(
            "SELECT r.*, c.contestant_number, c.name 
             FROM rankings r
             JOIN contestants c ON r.contestant_id = c.id
             WHERE r.round_id = ?
             ORDER BY r.rank",
            [$round['id']]
        );
        
        echo "    Rankings: " . count($rankings) . " contestants\n";
        foreach (array_slice($rankings, 0, 5) as $ranking) {
            echo "      Rank {$ranking['rank']}: Contestant #{$ranking['contestant_number']} - Avg: " . number_format($ranking['average_score'], 3) . "\n";
        }
    }
    
    // Calculate level rankings
    $levelRankings = $scoringEngine->calculateLevelRankings($level['id']);
    echo "  Level Rankings: " . count($levelRankings) . " contestants\n";
    foreach (array_slice($levelRankings, 0, 5) as $ranking) {
        echo "    Contestant #{$ranking['contestant_number']} ({$ranking['name']}) - Avg: " . number_format($ranking['average'], 3) . "\n";
    }
    echo "\n";
}

// Check qualified contestants
echo "=== Qualified Contestants ===\n";
$qualified = $db->fetchAll(
    "SELECT c.contestant_number, c.name, el.name as level_name
     FROM contestants c
     LEFT JOIN event_levels el ON c.qualified_for_level_id = el.id
     WHERE c.event_id = ? AND c.status = 'Active'
     ORDER BY CAST(c.contestant_number AS UNSIGNED)",
    [$eventId]
);

foreach ($qualified as $q) {
    $status = $q['level_name'] ? "Qualified for: {$q['level_name']}" : "Not qualified";
    echo "Contestant #{$q['contestant_number']}: {$q['name']} - {$status}\n";
}

echo "\n=== Fixing Qualification ===\n";
// Get Semi-Finals level
$semiFinalsLevel = $db->fetchOne(
    "SELECT * FROM event_levels WHERE event_id = ? AND name LIKE '%Semi%'",
    [$eventId]
);

if ($semiFinalsLevel) {
    $levelRankings = $scoringEngine->calculateLevelRankings($semiFinalsLevel['id']);
    
    echo "Found " . count($levelRankings) . " contestants in Semi-Finals rankings\n";
    
    // Get top 5
    $advanceCount = 5;
    $qualifiedContestants = array_slice($levelRankings, 0, $advanceCount);
    
    // Get cutoff score
    $lastQualified = end($qualifiedContestants);
    $cutoffScore = $lastQualified['average'] ?? 0;
    
    echo "Cutoff Score: " . number_format($cutoffScore, 3) . "\n";
    
    // Include all tied contestants
    $allQualified = [];
    foreach ($levelRankings as $ranking) {
        $contestantScore = $ranking['average'] ?? 0;
        if (abs($contestantScore - $cutoffScore) <= 0.001 || $contestantScore >= $cutoffScore) {
            $allQualified[] = $ranking;
        }
    }
    
    // Get Finals level
    $finalsLevel = $db->fetchOne(
        "SELECT * FROM event_levels WHERE event_id = ? AND name LIKE '%Final%' AND `order` > ? ORDER BY `order` DESC LIMIT 1",
        [$eventId, $semiFinalsLevel['order']]
    );
    
    if ($finalsLevel) {
        // Clear previous qualifications
        $db->query(
            "UPDATE contestants SET qualified_for_level_id = NULL WHERE event_id = ? AND qualified_for_level_id = ?",
            [$eventId, $finalsLevel['id']]
        );
        
        // Mark as qualified
        foreach ($allQualified as $contestant) {
            $db->query(
                "UPDATE contestants SET qualified_for_level_id = ? WHERE id = ?",
                [$finalsLevel['id'], $contestant['contestant_id']]
            );
            echo "✓ Qualified: Contestant #{$contestant['contestant_number']} ({$contestant['name']}) - Score: " . number_format($contestant['average'], 3) . "\n";
        }
        
        echo "\nTotal Qualified: " . count($allQualified) . " contestants\n";
    }
}

echo "\n=== Done ===\n";
