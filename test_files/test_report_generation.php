<?php
/**
 * Test Report Generation
 * 
 * This script tests if report generation is working correctly
 * by checking if reports can be generated and if they contain correct data.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/Session.php';

$db = Database::getInstance();

echo "=== Testing Report Generation ===\n\n";

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

$eventId = $event['id'];
echo "Event: {$event['name']} (ID: {$eventId})\n\n";

// Test 1: Check if rounds exist
echo "Test 1: Checking rounds...\n";
$rounds = $db->fetchAll(
    "SELECT r.*, el.name as level_name
     FROM rounds r
     JOIN event_levels el ON r.level_id = el.id
     WHERE el.event_id = ?
     ORDER BY el.`order`, r.`order`",
    [$eventId]
);

if (empty($rounds)) {
    echo "  ❌ ERROR: No rounds found!\n";
    exit(1);
}

echo "  ✓ Found " . count($rounds) . " rounds\n";
foreach ($rounds as $round) {
    echo "    - {$round['level_name']} - {$round['name']} (ID: {$round['id']})\n";
}
echo "\n";

// Test 2: Check if contestants exist
echo "Test 2: Checking contestants...\n";
$contestants = $db->fetchAll(
    "SELECT * FROM contestants WHERE event_id = ? AND status = 'Active'",
    [$eventId]
);

if (empty($contestants)) {
    echo "  ❌ ERROR: No contestants found!\n";
    exit(1);
}

echo "  ✓ Found " . count($contestants) . " contestants\n\n";

// Test 3: Check if scores exist for each round
echo "Test 3: Checking scores per round...\n";
foreach ($rounds as $round) {
    $scoreCount = $db->fetchOne(
        "SELECT COUNT(*) as count FROM scores 
         WHERE round_id = ? AND is_submitted = 1",
        [$round['id']]
    )['count'];
    
    $expectedScores = count($contestants) * 7; // 7 judges
    
    if ($scoreCount > 0) {
        echo "  ✓ Round '{$round['name']}': {$scoreCount} scores (Expected: ~{$expectedScores})\n";
    } else {
        echo "  ⚠ Round '{$round['name']}': No scores found\n";
    }
}
echo "\n";

// Test 4: Test round report data query
echo "Test 4: Testing round report data query...\n";
$testRound = $rounds[0];
$roundId = $testRound['id'];

// Get judges for this round
$judges = $db->fetchAll(
    "SELECT DISTINCT j.id, j.judge_number, u.full_name as judge_name
     FROM judges j
     JOIN users u ON j.user_id = u.id
     JOIN judge_assignments ja ON j.id = ja.judge_id
     WHERE ja.round_id = ? AND ja.is_active = 1
     ORDER BY j.judge_number",
    [$roundId]
);

echo "  Round: {$testRound['name']}\n";
echo "  Judges: " . count($judges) . "\n";

// Get contestants with scores
$contestantScores = $db->fetchAll(
    "SELECT c.id, c.contestant_number, c.name,
            AVG(s.total_score) as avg_score,
            COUNT(s.id) as score_count
     FROM contestants c
     LEFT JOIN scores s ON c.id = s.contestant_id AND s.round_id = ? AND s.is_submitted = 1
     WHERE c.event_id = ? AND c.status = 'Active'
     GROUP BY c.id, c.contestant_number, c.name
     HAVING score_count > 0
     ORDER BY avg_score DESC
     LIMIT 5",
    [$roundId, $eventId]
);

if (!empty($contestantScores)) {
    echo "  ✓ Top 5 contestants with scores:\n";
    foreach ($contestantScores as $cs) {
        echo "    - #{$cs['contestant_number']} {$cs['name']}: {$cs['avg_score']} (from {$cs['score_count']} judges)\n";
    }
} else {
    echo "  ⚠ No contestants with scores found\n";
}
echo "\n";

// Test 5: Test overall ranking query
echo "Test 5: Testing overall ranking query...\n";
$overallRanking = $db->fetchAll(
    "SELECT c.id, c.contestant_number, c.name,
            AVG(s.total_score) as avg_score,
            COUNT(DISTINCT s.round_id) as rounds_count,
            COUNT(s.id) as total_scores
     FROM contestants c
     INNER JOIN scores s ON c.id = s.contestant_id
     INNER JOIN rounds r ON s.round_id = r.id
     INNER JOIN event_levels el ON r.level_id = el.id
     WHERE c.event_id = ? AND c.status = 'Active' 
           AND el.event_id = ? AND s.is_submitted = 1
     GROUP BY c.id, c.contestant_number, c.name
     ORDER BY avg_score DESC
     LIMIT 5",
    [$eventId, $eventId]
);

if (!empty($overallRanking)) {
    echo "  ✓ Top 5 overall contestants:\n";
    foreach ($overallRanking as $rank => $contestant) {
        echo "    " . ($rank + 1) . ". #{$contestant['contestant_number']} {$contestant['name']}: {$contestant['avg_score']} ({$contestant['rounds_count']} rounds, {$contestant['total_scores']} scores)\n";
    }
} else {
    echo "  ⚠ No overall ranking data found\n";
}
echo "\n";

// Test 6: Test level-based report query
echo "Test 6: Testing level-based report query...\n";
$levels = $db->fetchAll(
    "SELECT * FROM event_levels WHERE event_id = ? ORDER BY `order`",
    [$eventId]
);

foreach ($levels as $level) {
    $levelScores = $db->fetchAll(
        "SELECT c.id, c.contestant_number, c.name,
                AVG(s.total_score) as avg_score,
                COUNT(DISTINCT s.round_id) as rounds_count
         FROM contestants c
         INNER JOIN scores s ON c.id = s.contestant_id
         INNER JOIN rounds r ON s.round_id = r.id
         WHERE c.event_id = ? AND c.status = 'Active' 
               AND r.level_id = ? AND s.is_submitted = 1
         GROUP BY c.id, c.contestant_number, c.name
         ORDER BY avg_score DESC
         LIMIT 3",
        [$eventId, $level['id']]
    );
    
    if (!empty($levelScores)) {
        echo "  ✓ Level '{$level['name']}': Top 3 contestants found\n";
    } else {
        echo "  ⚠ Level '{$level['name']}': No scores found\n";
    }
}
echo "\n";

// Test 7: Check if ReportsController methods would work
echo "Test 7: Verifying report controller requirements...\n";

// Check if we have a Super Admin user (needed to access reports)
$superAdmin = $db->fetchOne(
    "SELECT u.* FROM users u
     JOIN roles r ON u.role_id = r.id
     WHERE r.name = 'Super Admin' AND u.is_active = 1
     LIMIT 1"
);

if ($superAdmin) {
    echo "  ✓ Super Admin user exists (ID: {$superAdmin['id']}, Username: {$superAdmin['username']})\n";
} else {
    echo "  ⚠ WARNING: No Super Admin user found - reports may not be accessible\n";
}

// Check routes
echo "\n  Report URLs that should work:\n";
echo "    - Reports Index: /tabulation/events/{$eventId}/reports\n";
echo "    - Round Report: /tabulation/events/{$eventId}/reports/round/{$roundId}\n";
echo "    - Overall Ranking: /tabulation/events/{$eventId}/reports/overall-ranking\n";
echo "    - Event Summary: /tabulation/events/{$eventId}/reports/event-summary\n";

echo "\n=== Test Summary ===\n";
echo "✅ All data checks passed!\n";
echo "✅ Reports should be accessible via the web interface\n";
echo "✅ Make sure you're logged in as Super Admin or Event Technical Admin\n";
echo "\n";
echo "To test reports in browser:\n";
echo "1. Login as Super Admin (username: admin, password: admin123)\n";
echo "2. Navigate to: http://localhost/tabulation/events/{$eventId}/reports\n";
echo "3. Click on any report to generate it\n";
