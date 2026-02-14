<?php
/**
 * Generate Simple Score Table for Manual Input
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

// Get all rounds
$rounds = $db->fetchAll(
    "SELECT r.*, el.name as level_name
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
$output[] = "=" . str_repeat("=", 100);
$output[] = "SCORE INPUT TABLE - " . strtoupper($event['name']);
$output[] = "Generated: " . date('Y-m-d H:i:s');
$output[] = "=" . str_repeat("=", 100);
$output[] = "";

foreach ($rounds as $round) {
    $output[] = "";
    $output[] = str_repeat("=", 100);
    $output[] = "ROUND: {$round['level_name']} - {$round['name']}";
    $output[] = "Round ID: {$round['id']}";
    $output[] = str_repeat("=", 100);
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
        $output[] = "⚠ No criteria found!";
        $output[] = "";
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
        $output[] = "⚠ No judges assigned!";
        $output[] = "";
        continue;
    }
    
    // Create table header
    $header = sprintf("%-15s", "Contestant");
    foreach ($criteria as $criterion) {
        $header .= sprintf(" | %-20s (Max: %5.2f)", $criterion['name'], $criterion['max_score']);
    }
    $output[] = $header;
    $output[] = str_repeat("-", strlen($header));
    
    // Generate and display scores for each judge
    foreach ($roundJudges as $judge) {
        $output[] = "";
        $output[] = "JUDGE: {$judge['full_name']} (ID: {$judge['id']}, Username: {$judge['username']})";
        $output[] = str_repeat("-", strlen($header));
        
        foreach ($contestants as $contestantIndex => $contestant) {
            // Generate scores
            $baseScore = 7.0 + ($contestantIndex * 0.3) + (($judge['id'] % 3) * 0.2);
            $scores = [];
            
            foreach ($criteria as $criterionIndex => $criterion) {
                $maxScore = (float)$criterion['max_score'];
                $variation = sin($contestantIndex + $criterionIndex) * 0.8 + cos($judge['id'] + $criterionIndex) * 0.3;
                $randomDecimal = mt_rand(0, 999) / 1000;
                $rawScore = min($maxScore, max(0, $baseScore + $variation + ($randomDecimal * 0.5)));
                $rawScore = round($rawScore, 3);
                
                if ($rawScore == floor($rawScore)) {
                    $rawScore += mt_rand(1, 999) / 1000;
                    $rawScore = min($maxScore, $rawScore);
                    $rawScore = round($rawScore, 3);
                }
                
                $scores[] = $rawScore;
            }
            
            // Format row
            $row = sprintf("%-15s", "#{$contestant['contestant_number']} ({$contestant['name']})");
            foreach ($scores as $score) {
                $row .= sprintf(" | %20.3f", $score);
            }
            $output[] = $row;
        }
    }
}

$output[] = "";
$output[] = "=" . str_repeat("=", 100);
$output[] = "END OF SCORE INPUT TABLE";
$output[] = "=" . str_repeat("=", 100);

// Write to file
$filename = 'SCORE_INPUT_TABLE.txt';
file_put_contents($filename, implode("\n", $output));

echo "✓ Generated simple score table\n";
echo "File saved: {$filename}\n";
echo "\n";
echo "You can now use this file to manually input scores.\n";
echo "The format shows: Round → Judge → Contestant → Scores for each criteria\n";
