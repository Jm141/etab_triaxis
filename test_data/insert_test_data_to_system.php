<?php
/**
 * Insert Complete Test Data into System
 * 
 * This script:
 * 1. Creates a test event (or uses existing)
 * 2. Creates 5 judge users
 * 3. Creates 10 contestants
 * 4. Creates levels and rounds
 * 5. Creates criteria
 * 6. Assigns judges to rounds
 * 7. Inserts all test scores from CSV
 * 8. Calculates rankings
 * 
 * Usage: php test_data/insert_test_data_to_system.php [event_id]
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/ScoringEngine.php';

$db = Database::getInstance();
$scoringEngine = new ScoringEngine();

echo "========================================\n";
echo "Test Data Insertion Script\n";
echo "========================================\n\n";

// Get or create event
$eventId = $argv[1] ?? null;

if ($eventId) {
    $event = $db->fetchOne("SELECT * FROM events WHERE id = ?", [$eventId]);
    if (!$event) {
        die("❌ Event ID {$eventId} not found!\n");
    }
    echo "✓ Using existing event: {$event['name']} (ID: {$eventId})\n";
} else {
    // Create new test event
    $adminUser = $db->fetchOne("SELECT id FROM users WHERE username = 'admin'");
    if (!$adminUser) {
        die("❌ Admin user not found! Please create admin user first.\n");
    }
    
    $eventName = "Test Beauty Pageant - " . date('Y-m-d H:i:s');
    $db->query(
        "INSERT INTO events (name, description, event_type, status, created_by)
         VALUES (?, 'Test event for verification', 'Pageant', 'Ongoing', ?)",
        [$eventName, $adminUser['id']]
    );
    $eventId = $db->lastInsertId();
    echo "✓ Created new event: {$eventName} (ID: {$eventId})\n";
}

// Get Judge role
$judgeRole = $db->fetchOne("SELECT id FROM roles WHERE name = 'Judge'");
if (!$judgeRole) {
    die("❌ Judge role not found!\n");
}

// Create 5 judge users
echo "\n--- Creating Judges ---\n";
$judges = [];
for ($i = 1; $i <= 5; $i++) {
    $username = "test_judge{$i}";
    $email = "judge{$i}@test.local";
    $fullName = "Test Judge {$i}";
    
    // Check if user exists
    $existing = $db->fetchOne("SELECT id FROM users WHERE username = ?", [$username]);
    
    if ($existing) {
        $userId = $existing['id'];
        echo "  → Judge {$i} already exists (ID: {$userId})\n";
    } else {
        $passwordHash = password_hash('judge123', PASSWORD_BCRYPT);
        $db->query(
            "INSERT INTO users (username, email, password_hash, full_name, role_id, is_active)
             VALUES (?, ?, ?, ?, ?, 1)",
            [$username, $email, $passwordHash, $fullName, $judgeRole['id']]
        );
        $userId = $db->lastInsertId();
        echo "  ✓ Created judge {$i}: {$username} (ID: {$userId})\n";
    }
    
    // Create judge record
    $existingJudge = $db->fetchOne(
        "SELECT id FROM judges WHERE user_id = ? AND event_id = ?",
        [$userId, $eventId]
    );
    
    if ($existingJudge) {
        $judgeId = $existingJudge['id'];
        echo "    → Judge record already exists (ID: {$judgeId})\n";
    } else {
        $db->query(
            "INSERT INTO judges (user_id, event_id, judge_number, is_active)
             VALUES (?, ?, ?, 1)",
            [$userId, $eventId, "J{$i}"]
        );
        $judgeId = $db->lastInsertId();
        echo "    ✓ Created judge record (ID: {$judgeId})\n";
    }
    
    $judges["J{$i}"] = $judgeId;
}

// Create 10 contestants
echo "\n--- Creating Contestants ---\n";
$contestants = [];
for ($i = 1; $i <= 10; $i++) {
    $contestantNumber = str_pad($i, 2, '0', STR_PAD_LEFT);
    $name = "Contestant C{$i}";
    
    $existing = $db->fetchOne(
        "SELECT id FROM contestants WHERE event_id = ? AND contestant_number = ?",
        [$eventId, $contestantNumber]
    );
    
    if ($existing) {
        $contestantId = $existing['id'];
        echo "  → Contestant C{$i} already exists (ID: {$contestantId})\n";
    } else {
        $db->query(
            "INSERT INTO contestants (event_id, contestant_number, name, status)
             VALUES (?, ?, ?, 'Active')",
            [$eventId, $contestantNumber, $name]
        );
        $contestantId = $db->lastInsertId();
        echo "  ✓ Created contestant C{$i}: {$name} (ID: {$contestantId})\n";
    }
    
    $contestants["C{$i}"] = $contestantId;
}

// Create levels
echo "\n--- Creating Levels ---\n";
$levels = [
    'L1' => ['name' => 'Preliminary', 'order' => 1],
    'L2' => ['name' => 'Semi-Final', 'order' => 2],
    'L3' => ['name' => 'Final', 'order' => 3]
];

$levelIds = [];
foreach ($levels as $levelKey => $levelData) {
    $existing = $db->fetchOne(
        "SELECT id FROM event_levels WHERE event_id = ? AND name = ?",
        [$eventId, $levelData['name']]
    );
    
    if ($existing) {
        $levelId = $existing['id'];
        echo "  → Level '{$levelData['name']}' already exists (ID: {$levelId})\n";
    } else {
        $db->query(
            "INSERT INTO event_levels (event_id, name, `order`)
             VALUES (?, ?, ?)",
            [$eventId, $levelData['name'], $levelData['order']]
        );
        $levelId = $db->lastInsertId();
        echo "  ✓ Created level: {$levelData['name']} (ID: {$levelId})\n";
    }
    
    $levelIds[$levelKey] = $levelId;
}

// Create rounds
echo "\n--- Creating Rounds ---\n";
$rounds = [
    'R1' => ['name' => 'Talent Competition', 'level' => 'L1', 'order' => 1],
    'R2' => ['name' => 'Q&A Session', 'level' => 'L1', 'order' => 2],
    'R3' => ['name' => 'Evening Gown', 'level' => 'L2', 'order' => 1],
    'R4' => ['name' => 'Swimsuit', 'level' => 'L2', 'order' => 2],
    'R5' => ['name' => 'Final Q&A', 'level' => 'L3', 'order' => 1],
    'R6' => ['name' => 'Final Walk', 'level' => 'L3', 'order' => 2]
];

$roundIds = [];
foreach ($rounds as $roundKey => $roundData) {
    $levelId = $levelIds[$roundData['level']];
    
    $existing = $db->fetchOne(
        "SELECT id FROM rounds WHERE level_id = ? AND name = ?",
        [$levelId, $roundData['name']]
    );
    
    if ($existing) {
        $roundId = $existing['id'];
        echo "  → Round '{$roundData['name']}' already exists (ID: {$roundId})\n";
    } else {
        $db->query(
            "INSERT INTO rounds (level_id, name, `order`)
             VALUES (?, ?, ?)",
            [$levelId, $roundData['name'], $roundData['order']]
        );
        $roundId = $db->lastInsertId();
        echo "  ✓ Created round: {$roundData['name']} (ID: {$roundId})\n";
    }
    
    $roundIds[$roundKey] = $roundId;
}

// Create criteria
echo "\n--- Creating Criteria ---\n";
$criteriaMap = [
    'R1' => [
        ['name' => 'Performance Quality', 'max_score' => 10],
        ['name' => 'Originality', 'max_score' => 15],
        ['name' => 'Stage Presence', 'max_score' => 10]
    ],
    'R2' => [
        ['name' => 'Communication', 'max_score' => 10],
        ['name' => 'Intelligence', 'max_score' => 15],
        ['name' => 'Poise', 'max_score' => 10]
    ],
    'R3' => [
        ['name' => 'Elegance', 'max_score' => 10],
        ['name' => 'Confidence', 'max_score' => 15],
        ['name' => 'Presentation', 'max_score' => 10]
    ],
    'R4' => [
        ['name' => 'Fitness', 'max_score' => 10],
        ['name' => 'Confidence', 'max_score' => 15],
        ['name' => 'Poise', 'max_score' => 10]
    ],
    'R5' => [
        ['name' => 'Communication', 'max_score' => 10],
        ['name' => 'Intelligence', 'max_score' => 15],
        ['name' => 'Overall Impact', 'max_score' => 10]
    ],
    'R6' => [
        ['name' => 'Final Walk', 'max_score' => 10],
        ['name' => 'Stage Presence', 'max_score' => 15],
        ['name' => 'Overall Impact', 'max_score' => 10]
    ]
];

$criteriaIds = [];
foreach ($rounds as $roundKey => $roundData) {
    $roundId = $roundIds[$roundKey];
    $criteriaList = $criteriaMap[$roundKey];
    
    foreach ($criteriaList as $idx => $criteriaData) {
        $criteriaKey = "C" . ((array_search($roundKey, array_keys($rounds)) * 3) + $idx + 1);
        
        // Check if criteria exists
        $existing = $db->fetchOne(
            "SELECT id FROM criteria WHERE name = ? AND max_score = ?",
            [$criteriaData['name'], $criteriaData['max_score']]
        );
        
        if ($existing) {
            $criteriaId = $existing['id'];
        } else {
            $db->query(
                "INSERT INTO criteria (event_id, name, max_score)
                 VALUES (?, ?, ?)",
                [$eventId, $criteriaData['name'], $criteriaData['max_score']]
            );
            $criteriaId = $db->lastInsertId();
        }
        
        $criteriaIds[$criteriaKey] = $criteriaId;
        
        // Assign criteria to round (criteria_weights)
        $existingWeight = $db->fetchOne(
            "SELECT id FROM criteria_weights WHERE round_id = ? AND criteria_id = ?",
            [$roundId, $criteriaId]
        );
        
        if (!$existingWeight) {
            $db->query(
                "INSERT INTO criteria_weights (round_id, criteria_id, weight, is_active)
                 VALUES (?, ?, 0, 1)",
                [$roundId, $criteriaId]
            );
        }
    }
    
    echo "  ✓ Created criteria for {$roundData['name']}\n";
}

// Assign judges to all rounds
echo "\n--- Assigning Judges to Rounds ---\n";
foreach ($roundIds as $roundId) {
    foreach ($judges as $judgeId) {
        $existing = $db->fetchOne(
            "SELECT id FROM judge_assignments WHERE judge_id = ? AND round_id = ?",
            [$judgeId, $roundId]
        );
        
        if (!$existing) {
            $db->query(
                "INSERT INTO judge_assignments (judge_id, round_id, is_active)
                 VALUES (?, ?, 1)",
                [$judgeId, $roundId]
            );
        }
    }
    echo "  ✓ Assigned all judges to round ID {$roundId}\n";
}

// Read CSV and insert scores
echo "\n--- Inserting Scores from CSV ---\n";
$csvFile = __DIR__ . '/COMPLETE_TEST_DATA.csv';
if (!file_exists($csvFile)) {
    die("❌ CSV file not found: {$csvFile}\n");
}

$handle = fopen($csvFile, 'r');
if (!$handle) {
    die("❌ Cannot open CSV file\n");
}

// Skip header
fgetcsv($handle);

$scoreCount = 0;
$currentScore = null;
$scoreDetails = [];

while (($row = fgetcsv($handle)) !== false) {
    if (count($row) < 5) continue;
    
    $contestantKey = $row[0]; // C1, C2, etc.
    $judgeKey = $row[1];      // J1, J2, etc.
    $roundKey = $row[2];      // R1, R2, etc.
    $criteriaKey = $row[3];   // C1, C2, etc.
    $rawScore = floatval($row[4]);
    $maxScore = floatval($row[5]);
    
    if (!isset($contestants[$contestantKey]) || !isset($judges[$judgeKey]) || 
        !isset($roundIds[$roundKey]) || !isset($criteriaIds[$criteriaKey])) {
        continue;
    }
    
    $contestantId = $contestants[$contestantKey];
    $judgeId = $judges[$judgeKey];
    $roundId = $roundIds[$roundKey];
    $criteriaId = $criteriaIds[$criteriaKey];
    
    // Check if score exists
    $score = $db->fetchOne(
        "SELECT id FROM scores 
         WHERE judge_id = ? AND contestant_id = ? AND round_id = ?",
        [$judgeId, $contestantId, $roundId]
    );
    
    if (!$score) {
        // Create score record
        $db->query(
            "INSERT INTO scores (judge_id, contestant_id, round_id, total_score, is_submitted, submitted_at)
             VALUES (?, ?, ?, 0, 1, NOW())",
            [$judgeId, $contestantId, $roundId]
        );
        $scoreId = $db->lastInsertId();
    } else {
        $scoreId = $score['id'];
    }
    
    // Insert or update score detail
    $existingDetail = $db->fetchOne(
        "SELECT id FROM score_details WHERE score_id = ? AND criteria_id = ?",
        [$scoreId, $criteriaId]
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
            [$scoreId, $criteriaId, $rawScore]
        );
    }
    
    $scoreCount++;
    
    // Calculate score when all criteria are entered
    if ($scoreCount % 3 == 0) {
        $scoringEngine->calculateScore($scoreId);
    }
}

fclose($handle);

echo "  ✓ Inserted {$scoreCount} score entries\n";

// Calculate rankings for all rounds
echo "\n--- Calculating Rankings ---\n";
foreach ($roundIds as $roundKey => $roundId) {
    $scoringEngine->calculateRankings($roundId);
    echo "  ✓ Calculated rankings for {$rounds[$roundKey]['name']}\n";
}

echo "\n========================================\n";
echo "✅ Test Data Insertion Complete!\n";
echo "========================================\n\n";

echo "Summary:\n";
echo "  - Event ID: {$eventId}\n";
echo "  - Judges: 5 (test_judge1 to test_judge5, password: judge123)\n";
echo "  - Contestants: 10 (C1 to C10)\n";
echo "  - Levels: 3 (Preliminary, Semi-Final, Final)\n";
echo "  - Rounds: 6\n";
echo "  - Criteria: 18\n";
echo "  - Score entries: {$scoreCount}\n";
echo "\n";

echo "Login Credentials:\n";
echo "  Judges: test_judge1 to test_judge5 / judge123\n";
echo "\n";

echo "Next Steps:\n";
echo "  1. Login as a judge and verify scores\n";
echo "  2. Generate reports to verify calculations\n";
echo "  3. Compare with Excel calculations\n";
echo "\n";
