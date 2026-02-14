<?php
/**
 * Add Winners to Pageant 2026 Summary
 * 
 * This script calculates winners per round, per level, and overall,
 * then adds them to the summary document.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Database.php';

$db = Database::getInstance();

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

echo "=== Adding Winners to Summary ===\n";
echo "Event: {$event['name']} (ID: {$event['id']})\n\n";

// Get all rounds with their levels
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

$contestantMap = [];
foreach ($contestants as $c) {
    $contestantMap[$c['id']] = $c;
}

// Calculate winners per round
$roundWinners = [];
foreach ($rounds as $round) {
    echo "Calculating winners for Round: {$round['level_name']} - {$round['name']}\n";
    
    // Get average weighted score per contestant (across all judges)
    $roundScores = $db->fetchAll(
        "SELECT c.id as contestant_id, c.contestant_number, c.name,
                AVG(s.total_score) as avg_weighted_score,
                COUNT(s.id) as judge_count
         FROM contestants c
         INNER JOIN scores s ON c.id = s.contestant_id AND s.round_id = ?
         WHERE c.event_id = ? AND c.status = 'Active' AND s.is_submitted = 1
         GROUP BY c.id, c.contestant_number, c.name
         ORDER BY avg_weighted_score DESC",
        [$round['id'], $event['id']]
    );
    
    echo "  Found " . count($roundScores) . " contestants with scores\n";
    
    $roundWinners[$round['id']] = [
        'round_name' => $round['name'],
        'level_name' => $round['level_name'],
        'winners' => $roundScores
    ];
}

// Calculate winners per level
$levelWinners = [];
$levels = $db->fetchAll(
    "SELECT DISTINCT el.id, el.name, el.`order`
     FROM event_levels el
     JOIN rounds r ON el.id = r.level_id
     WHERE el.event_id = ?
     ORDER BY el.`order` ASC",
    [$event['id']]
);

foreach ($levels as $level) {
    echo "Calculating winners for Level: {$level['name']}\n";
    
    // Get average weighted score per contestant across all rounds in this level
    $levelScores = $db->fetchAll(
        "SELECT c.id as contestant_id, c.contestant_number, c.name,
                AVG(s.total_score) as avg_weighted_score,
                COUNT(DISTINCT s.round_id) as rounds_count,
                COUNT(s.id) as total_scores
         FROM contestants c
         INNER JOIN scores s ON c.id = s.contestant_id
         INNER JOIN rounds r ON s.round_id = r.id
         WHERE c.event_id = ? AND c.status = 'Active' 
               AND r.level_id = ? AND s.is_submitted = 1
         GROUP BY c.id, c.contestant_number, c.name
         ORDER BY avg_weighted_score DESC",
        [$event['id'], $level['id']]
    );
    
    echo "  Found " . count($levelScores) . " contestants with scores\n";
    
    $levelWinners[$level['id']] = [
        'level_name' => $level['name'],
        'winners' => $levelScores
    ];
}

// Calculate overall winners
echo "Calculating overall winners\n";
$overallWinners = $db->fetchAll(
    "SELECT c.id as contestant_id, c.contestant_number, c.name,
            AVG(s.total_score) as avg_weighted_score,
            COUNT(DISTINCT s.round_id) as rounds_count,
            COUNT(s.id) as total_scores
     FROM contestants c
     INNER JOIN scores s ON c.id = s.contestant_id
     INNER JOIN rounds r ON s.round_id = r.id
     INNER JOIN event_levels el ON r.level_id = el.id
     WHERE c.event_id = ? AND c.status = 'Active' 
           AND el.event_id = ? AND s.is_submitted = 1
     GROUP BY c.id, c.contestant_number, c.name
     ORDER BY avg_weighted_score DESC",
    [$event['id'], $event['id']]
);

// Read current summary file
$summaryFile = __DIR__ . '/PAGEANT_2026_SCORES_SUMMARY.md';
$summaryContent = file_get_contents($summaryFile);

// Remove existing winners section if it exists
$summaryContent = preg_replace('/\n\n---\n\n# Winners Summary.*$/s', '', $summaryContent);

// Add winners section at the end
$winnersSection = "\n\n---\n\n";
$winnersSection .= "# Winners Summary\n\n";
$winnersSection .= "**Generated:** " . date('Y-m-d H:i:s') . "\n\n";

// Winners per Round
$winnersSection .= "## Winners Per Round\n\n";
foreach ($roundWinners as $roundId => $roundData) {
    $winnersSection .= "### {$roundData['level_name']} - {$roundData['round_name']}\n\n";
    $winnersSection .= "| Rank | Contestant | Average Weighted Score |\n";
    $winnersSection .= "|------|------------|------------------------|\n";
    
    if (empty($roundData['winners'])) {
        $winnersSection .= "| *No scores available* | | |\n\n";
        continue;
    }
    
    $rank = 1;
    $prevScore = null;
    foreach ($roundData['winners'] as $index => $winner) {
        // Handle ties
        if ($prevScore !== null && abs($winner['avg_weighted_score'] - $prevScore) > 0.001) {
            $rank = $index + 1;
        }
        $prevScore = $winner['avg_weighted_score'];
        
        $winnersSection .= sprintf("| %d | #%s - %s | %.3f |\n", 
            $rank, 
            $winner['contestant_number'], 
            $winner['name'],
            $winner['avg_weighted_score']
        );
    }
    $winnersSection .= "\n";
}

// Winners per Level
$winnersSection .= "---\n\n";
$winnersSection .= "## Winners Per Level\n\n";
foreach ($levelWinners as $levelId => $levelData) {
    $winnersSection .= "### {$levelData['level_name']}\n\n";
    $winnersSection .= "| Rank | Contestant | Average Weighted Score | Rounds Completed |\n";
    $winnersSection .= "|------|------------|------------------------|------------------|\n";
    
    $rank = 1;
    $prevScore = null;
    foreach ($levelData['winners'] as $index => $winner) {
        // Handle ties
        if ($prevScore !== null && abs($winner['avg_weighted_score'] - $prevScore) > 0.001) {
            $rank = $index + 1;
        }
        $prevScore = $winner['avg_weighted_score'];
        
        $winnersSection .= sprintf("| %d | #%s - %s | %.3f | %d |\n", 
            $rank, 
            $winner['contestant_number'], 
            $winner['name'],
            $winner['avg_weighted_score'],
            $winner['rounds_count']
        );
    }
    $winnersSection .= "\n";
}

// Overall Winners
$winnersSection .= "---\n\n";
$winnersSection .= "## Overall Winners\n\n";
$winnersSection .= "| Rank | Contestant | Average Weighted Score | Rounds Completed |\n";
$winnersSection .= "|------|------------|------------------------|------------------|\n";

$rank = 1;
$prevScore = null;
foreach ($overallWinners as $index => $winner) {
    // Handle ties
    if ($prevScore !== null && abs($winner['avg_weighted_score'] - $prevScore) > 0.001) {
        $rank = $index + 1;
    }
    $prevScore = $winner['avg_weighted_score'];
    
    $winnersSection .= sprintf("| %d | #%s - %s | %.3f | %d |\n", 
        $rank, 
        $winner['contestant_number'], 
        $winner['name'],
        $winner['avg_weighted_score'],
        $winner['rounds_count']
    );
}

// Append winners section to summary
$summaryContent .= $winnersSection;

// Write updated summary
file_put_contents($summaryFile, $summaryContent);

echo "\n=== Winners Added to Summary ===\n";
echo "Summary file updated: {$summaryFile}\n";
echo "\nWinners calculated:\n";
echo "  - Per Round: " . count($roundWinners) . " rounds\n";
echo "  - Per Level: " . count($levelWinners) . " levels\n";
echo "  - Overall: " . count($overallWinners) . " contestants\n";
