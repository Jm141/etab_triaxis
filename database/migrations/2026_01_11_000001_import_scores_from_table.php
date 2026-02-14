<?php
/**
 * Migration: Import Scores from SCORE_INPUT_TABLE.txt
 * 
 * This migration reads scores from SCORE_INPUT_TABLE.txt and imports them into the database.
 * 
 * Usage: php database/migrations/2026_01_11_000001_import_scores_from_table.php
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../../core/ScoringEngine.php';

$db = Database::getInstance();
$scoringEngine = new ScoringEngine();

echo "=== Importing Scores from SCORE_INPUT_TABLE.txt ===\n\n";

// Read the file
$filePath = __DIR__ . '/../../SCORE_INPUT_TABLE.txt';
if (!file_exists($filePath)) {
    echo "ERROR: SCORE_INPUT_TABLE.txt not found at: {$filePath}\n";
    exit(1);
}

$fileContent = file_get_contents($filePath);
$lines = explode("\n", $fileContent);

// Parse the file
$currentRound = null;
$currentRoundId = null;
$currentCriteria = [];
$currentJudge = null;
$currentJudgeId = null;
$currentJudgeUsername = null;
$scores = [];

$lineNumber = 0;
foreach ($lines as $line) {
    $lineNumber++;
    $line = trim($line);
    
    // Skip empty lines and separators
    if (empty($line) || strpos($line, '=') === 0 || strpos($line, '-') === 0) {
        continue;
    }
    
    // Skip header lines
    if (strpos($line, 'SCORE INPUT TABLE') !== false || 
        strpos($line, 'Generated:') !== false ||
        strpos($line, '⚠') !== false) {
        continue;
    }
    
    // Parse Round
    if (preg_match('/^ROUND:\s*(.+?)\s*$/i', $line, $matches)) {
        $currentRound = trim($matches[1]);
        continue;
    }
    
    if (preg_match('/^Round ID:\s*(\d+)\s*$/i', $line, $matches)) {
        $currentRoundId = (int)$matches[1];
        $currentCriteria = [];
        echo "Processing Round: {$currentRound} (ID: {$currentRoundId})\n";
        continue;
    }
    
    // Parse Criteria Header (only once per round)
    if (strpos($line, 'Contestant') !== false && strpos($line, '|') !== false && empty($currentCriteria)) {
        // Extract criteria from header
        // Format: Contestant | Criteria1 (Max: X.XX) | Criteria2 (Max: X.XX) | ...
        $parts = explode('|', $line);
        array_shift($parts); // Remove "Contestant" part
        
        foreach ($parts as $part) {
            if (preg_match('/(.+?)\s*\(Max:\s*([\d.]+)\)/i', trim($part), $matches)) {
                $criteriaName = trim($matches[1]);
                $maxScore = (float)$matches[2];
                $currentCriteria[] = [
                    'name' => $criteriaName,
                    'max_score' => $maxScore
                ];
            }
        }
        
        if (!empty($currentCriteria)) {
            echo "  Found " . count($currentCriteria) . " criteria\n";
        }
        continue;
    }
    
    // Parse Judge
    if (preg_match('/^JUDGE:\s*(.+?)\s*\(ID:\s*(\d+),\s*Username:\s*(\w+)\)/i', $line, $matches)) {
        $currentJudge = trim($matches[1]);
        $currentJudgeId = (int)$matches[2];
        $currentJudgeUsername = trim($matches[3]);
        echo "  Processing Judge: {$currentJudge} (ID: {$currentJudgeId}, Username: {$currentJudgeUsername})\n";
        // Don't reset criteria when judge changes - criteria are per round
        continue;
    }
    
    // Parse Contestant Score Line
    // Format: #1 (Contestant C1) | 6.986 | 7.948 | 8.122
    if (preg_match('/^#(\d+)\s*\([^)]+\)\s*\|(.+)$/i', $line, $matches)) {
        $contestantNumber = trim($matches[1]);
        $scoreValues = explode('|', $matches[2]);
        
        // Clean and parse scores
        $scoresArray = [];
        foreach ($scoreValues as $scoreValue) {
            $scoreValue = trim($scoreValue);
            if (is_numeric($scoreValue)) {
                $scoresArray[] = (float)$scoreValue;
            }
        }
        
        // Validate we have the right number of scores and all required data
        if (count($scoresArray) === count($currentCriteria) && 
            !empty($currentRoundId) && 
            !empty($currentJudgeId) && 
            !empty($currentCriteria)) {
            $scores[] = [
                'round_id' => $currentRoundId,
                'round_name' => $currentRound,
                'judge_id' => $currentJudgeId,
                'judge_name' => $currentJudge,
                'judge_username' => $currentJudgeUsername,
                'contestant_number' => $contestantNumber,
                'criteria' => $currentCriteria,
                'scores' => $scoresArray
            ];
        } else {
            // Debug output
            if (empty($currentRoundId)) echo "  Debug: Missing round_id for line: {$line}\n";
            if (empty($currentJudgeId)) echo "  Debug: Missing judge_id for line: {$line}\n";
            if (empty($currentCriteria)) echo "  Debug: Missing criteria for line: {$line}\n";
            if (count($scoresArray) !== count($currentCriteria)) {
                echo "  Debug: Score count mismatch (got " . count($scoresArray) . ", expected " . count($currentCriteria) . ") for line: {$line}\n";
            }
        }
    }
}

echo "\n=== Parsing Complete ===\n";
echo "Total score entries found: " . count($scores) . "\n\n";

if (empty($scores)) {
    echo "ERROR: No scores found in file. Please check the file format.\n";
    exit(1);
}

// Import scores
echo "=== Importing Scores ===\n\n";

$importedCount = 0;
$skippedCount = 0;
$errorCount = 0;

foreach ($scores as $scoreData) {
    try {
        // Find round
        $round = $db->fetchOne("SELECT * FROM rounds WHERE id = ?", [$scoreData['round_id']]);
        if (!$round) {
            echo "⚠ Skipping: Round ID {$scoreData['round_id']} not found\n";
            $skippedCount++;
            continue;
        }
        
        // Find judge
        $judge = $db->fetchOne(
            "SELECT j.* FROM judges j 
             JOIN users u ON j.user_id = u.id 
             WHERE j.id = ? AND u.username = ?",
            [$scoreData['judge_id'], $scoreData['judge_username']]
        );
        
        if (!$judge) {
            // Try to find by ID only
            $judge = $db->fetchOne("SELECT * FROM judges WHERE id = ?", [$scoreData['judge_id']]);
        }
        
        if (!$judge) {
            echo "⚠ Skipping: Judge ID {$scoreData['judge_id']} (Username: {$scoreData['judge_username']}) not found\n";
            $skippedCount++;
            continue;
        }
        
        // Find contestant by number
        $eventId = $db->fetchOne(
            "SELECT el.event_id FROM event_levels el 
             JOIN rounds r ON el.id = r.level_id 
             WHERE r.id = ?",
            [$scoreData['round_id']]
        )['event_id'];
        
        $contestant = $db->fetchOne(
            "SELECT * FROM contestants 
             WHERE event_id = ? AND contestant_number = ? AND status = 'Active'",
            [$eventId, $scoreData['contestant_number']]
        );
        
        if (!$contestant) {
            echo "⚠ Skipping: Contestant #{$scoreData['contestant_number']} not found for event ID {$eventId}\n";
            $skippedCount++;
            continue;
        }
        
        // Get criteria IDs for this round
        $criteriaIds = [];
        foreach ($scoreData['criteria'] as $index => $criteriaInfo) {
            // Try exact match first
            $criteria = $db->fetchOne(
                "SELECT c.* FROM criteria c
                 JOIN criteria_weights cw ON c.id = cw.criteria_id
                 WHERE cw.round_id = ? AND c.name = ? AND c.max_score = ? AND cw.is_active = 1
                 LIMIT 1",
                [$scoreData['round_id'], $criteriaInfo['name'], $criteriaInfo['max_score']]
            );
            
            // Try case-insensitive match
            if (!$criteria) {
                $criteria = $db->fetchOne(
                    "SELECT c.* FROM criteria c
                     JOIN criteria_weights cw ON c.id = cw.criteria_id
                     WHERE cw.round_id = ? AND LOWER(TRIM(c.name)) = LOWER(TRIM(?)) AND c.max_score = ? AND cw.is_active = 1
                     LIMIT 1",
                    [$scoreData['round_id'], $criteriaInfo['name'], $criteriaInfo['max_score']]
                );
            }
            
            // Try matching by max_score only (if only one criteria with that max_score)
            if (!$criteria) {
                $allCriteria = $db->fetchAll(
                    "SELECT c.* FROM criteria c
                     JOIN criteria_weights cw ON c.id = cw.criteria_id
                     WHERE cw.round_id = ? AND c.max_score = ? AND cw.is_active = 1
                     ORDER BY c.id",
                    [$scoreData['round_id'], $criteriaInfo['max_score']]
                );
                
                if (count($allCriteria) == 1) {
                    $criteria = $allCriteria[0];
                }
            }
            
            if (!$criteria) {
                echo "⚠ Skipping: Criteria '{$criteriaInfo['name']}' (Max: {$criteriaInfo['max_score']}) not found for round ID {$scoreData['round_id']}\n";
                $skippedCount++;
                continue 2; // Skip this entire score entry
            }
            
            $criteriaIds[] = $criteria['id'];
        }
        
        // Check if score already exists
        $existingScore = $db->fetchOne(
            "SELECT id FROM scores 
             WHERE round_id = ? AND contestant_id = ? AND judge_id = ?",
            [$scoreData['round_id'], $contestant['id'], $judge['id']]
        );
        
        $scoreId = null;
        if ($existingScore) {
            $scoreId = $existingScore['id'];
            // Delete existing score details
            $db->query("DELETE FROM score_details WHERE score_id = ?", [$scoreId]);
            // Reset score
            $db->query(
                "UPDATE scores SET is_submitted = 0, is_draft = 0 WHERE id = ?",
                [$scoreId]
            );
        } else {
            // Create new score record
            $db->query(
                "INSERT INTO scores (judge_id, contestant_id, round_id, is_submitted, is_draft, submitted_at, ip_address)
                 VALUES (?, ?, ?, 1, 0, NOW(), ?)",
                [$judge['id'], $contestant['id'], $scoreData['round_id'], $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']
            );
            $scoreId = $db->lastInsertId();
        }
        
        // Insert score details
        foreach ($scoreData['scores'] as $index => $rawScore) {
            if (!isset($criteriaIds[$index])) {
                continue;
            }
            
            $db->query(
                "INSERT INTO score_details (score_id, criteria_id, raw_score, weighted_score)
                 VALUES (?, ?, ?, 0)",
                [$scoreId, $criteriaIds[$index], $rawScore]
            );
        }
        
        // Calculate weighted score
        $scoringEngine->calculateScore($scoreId);
        
        // Mark as submitted
        $db->query("UPDATE scores SET is_submitted = 1 WHERE id = ?", [$scoreId]);
        
        $importedCount++;
        
        if ($importedCount % 10 == 0) {
            echo "  Imported {$importedCount} scores...\n";
        }
        
    } catch (Exception $e) {
        echo "✗ Error importing score: " . $e->getMessage() . "\n";
        $errorCount++;
    }
}

echo "\n=== Import Complete ===\n";
echo "Imported: {$importedCount}\n";
echo "Skipped: {$skippedCount}\n";
echo "Errors: {$errorCount}\n";
echo "\n";

if ($importedCount > 0) {
    echo "✓ Successfully imported {$importedCount} scores!\n";
} else {
    echo "⚠ No scores were imported. Please check the errors above.\n";
}
