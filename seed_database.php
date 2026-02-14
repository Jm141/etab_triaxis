<?php
/**
 * Database Seeding Script
 * Auto-populates the database with test data from TEST_GUIDE.md
 * 
 * Usage: php seed_database.php
 */

require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/Session.php';

echo "========================================\n";
echo "Database Seeding Script\n";
echo "Using TEST_GUIDE.md data\n";
echo "========================================\n\n";

$db = Database::getInstance();

try {
    $db->getConnection()->beginTransaction();
    
    // Get Super Admin user (for created_by fields)
    $admin = $db->fetchOne("SELECT id FROM users WHERE username = 'admin'");
    if (!$admin) {
        die("❌ Error: Admin user not found. Please ensure admin user exists.\n");
    }
    $adminId = $admin['id'];
    
    echo "✓ Found admin user (ID: {$adminId})\n\n";
    
    // ============================================
    // STEP 1: Create Event
    // ============================================
    echo "Step 1: Creating Event...\n";
    
    $eventData = [
        'name' => 'Miss Universe 2024',
        'description' => 'The 73rd Miss Universe pageant showcasing beauty, talent, and intelligence',
        'event_type' => 'Pageant',
        'venue' => 'Grand Ballroom, International Convention Center',
        'event_date' => '2024-12-15',
        'start_time' => '19:00:00',
        'end_time' => '23:00:00',
        'timezone' => 'UTC',
        'status' => 'Draft',
        'created_by' => $adminId
    ];
    
    // Check if event already exists
    $existingEvent = $db->fetchOne("SELECT id FROM events WHERE name = ?", [$eventData['name']]);
    
    if ($existingEvent) {
        $eventId = $existingEvent['id'];
        echo "  → Event already exists (ID: {$eventId})\n";
    } else {
        $db->query(
            "INSERT INTO events (name, description, event_type, venue, event_date, start_time, end_time, timezone, status, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            array_values($eventData)
        );
        $eventId = $db->lastInsertId();
        echo "  ✓ Created event: {$eventData['name']} (ID: {$eventId})\n";
        
        // Auto-assign creator to event
        $db->query(
            "INSERT INTO user_event_assignments (user_id, event_id, assigned_by, is_active)
             VALUES (?, ?, ?, 1)
             ON DUPLICATE KEY UPDATE is_active = 1",
            [$adminId, $eventId, $adminId]
        );
    }
    
    // ============================================
    // STEP 2: Create Levels
    // ============================================
    echo "\nStep 2: Creating Levels...\n";
    
    $levels = [
        ['name' => 'Preliminaries', 'description' => 'Initial competition rounds to select semi-finalists', 'order' => 1],
        ['name' => 'Semi-Finals', 'description' => 'Top 20 contestants compete for final 10 spots', 'order' => 2],
        ['name' => 'Finals', 'description' => 'Final competition to determine the winner', 'order' => 3]
    ];
    
    $levelIds = [];
    foreach ($levels as $level) {
        $existing = $db->fetchOne(
            "SELECT id FROM event_levels WHERE event_id = ? AND name = ?",
            [$eventId, $level['name']]
        );
        
        if ($existing) {
            $levelIds[$level['name']] = $existing['id'];
            echo "  → Level '{$level['name']}' already exists (ID: {$existing['id']})\n";
        } else {
            $db->query(
                "INSERT INTO event_levels (event_id, name, description, `order`)
                 VALUES (?, ?, ?, ?)",
                [$eventId, $level['name'], $level['description'], $level['order']]
            );
            $levelIds[$level['name']] = $db->lastInsertId();
            echo "  ✓ Created level: {$level['name']} (ID: {$levelIds[$level['name']]})\n";
        }
    }
    
    // ============================================
    // STEP 3: Create Rounds
    // ============================================
    echo "\nStep 3: Creating Rounds...\n";
    
    $rounds = [
        ['level' => 'Preliminaries', 'name' => 'Talent Competition', 'description' => 'Contestants showcase their unique talents', 'order' => 1],
        ['level' => 'Preliminaries', 'name' => 'Swimsuit Competition', 'description' => 'Physical fitness and confidence on stage', 'order' => 2],
        ['level' => 'Preliminaries', 'name' => 'Evening Gown', 'description' => 'Elegance, poise, and style presentation', 'order' => 3],
        ['level' => 'Semi-Finals', 'name' => 'Top 20 Interview', 'description' => 'Personal interview with judges', 'order' => 1],
        ['level' => 'Semi-Finals', 'name' => 'Swimsuit (Semi-Finals)', 'description' => 'Semi-final swimsuit presentation', 'order' => 2],
        ['level' => 'Semi-Finals', 'name' => 'Evening Gown (Semi-Finals)', 'description' => 'Semi-final evening gown presentation', 'order' => 3],
        ['level' => 'Finals', 'name' => 'Final Q&A', 'description' => 'Final question and answer round for top 5', 'order' => 1]
    ];
    
    $roundIds = [];
    foreach ($rounds as $round) {
        $levelId = $levelIds[$round['level']];
        $existing = $db->fetchOne(
            "SELECT id FROM rounds WHERE level_id = ? AND name = ?",
            [$levelId, $round['name']]
        );
        
        if ($existing) {
            $roundIds[$round['name']] = $existing['id'];
            echo "  → Round '{$round['name']}' already exists (ID: {$existing['id']})\n";
        } else {
            $db->query(
                "INSERT INTO rounds (level_id, name, description, `order`, status)
                 VALUES (?, ?, ?, ?, 'Active')",
                [$levelId, $round['name'], $round['description'], $round['order']]
            );
            $roundIds[$round['name']] = $db->lastInsertId();
            echo "  ✓ Created round: {$round['name']} (ID: {$roundIds[$round['name']]})\n";
        }
    }
    
    // ============================================
    // STEP 4: Create Criteria
    // ============================================
    echo "\nStep 4: Creating Criteria...\n";
    
    $criteria = [
        // Talent Criteria
        ['name' => 'Performance Quality', 'description' => 'Technical skill, execution, and overall performance quality', 'max_score' => 100.000],
        ['name' => 'Originality', 'description' => 'Uniqueness and creativity of the talent', 'max_score' => 50.000],
        ['name' => 'Stage Presence', 'description' => 'Confidence, charisma, and audience engagement', 'max_score' => 50.000],
        
        // Swimsuit Criteria
        ['name' => 'Physical Fitness', 'description' => 'Overall physical condition and health', 'max_score' => 50.000],
        ['name' => 'Confidence', 'description' => 'Poise, confidence, and stage presence', 'max_score' => 50.000],
        ['name' => 'Overall Presentation', 'description' => 'Overall appearance and presentation', 'max_score' => 50.000],
        
        // Evening Gown Criteria (reuses Stage Presence from Talent)
        ['name' => 'Elegance', 'description' => 'Grace, poise, and elegant presentation', 'max_score' => 50.000],
        ['name' => 'Style & Fashion', 'description' => 'Fashion sense and style choices', 'max_score' => 50.000],
        
        // Interview Criteria
        ['name' => 'Communication Skills', 'description' => 'Clarity, articulation, and communication ability', 'max_score' => 50.000],
        ['name' => 'Intelligence', 'description' => 'Depth of thought and intellectual capacity', 'max_score' => 50.000],
        ['name' => 'Personality', 'description' => 'Authenticity, charisma, and personality', 'max_score' => 50.000],
        
        // Q&A Criteria
        ['name' => 'Answer Quality', 'description' => 'Relevance, depth, and quality of answer', 'max_score' => 50.000],
        ['name' => 'Poise Under Pressure', 'description' => 'Composure and confidence under pressure', 'max_score' => 50.000],
        ['name' => 'Communication', 'description' => 'Clarity and effectiveness of communication', 'max_score' => 50.000]
    ];
    
    $criteriaIds = [];
    foreach ($criteria as $criterion) {
        $existing = $db->fetchOne(
            "SELECT id FROM criteria WHERE name = ? AND event_id = ?",
            [$criterion['name'], $eventId]
        );
        
        if ($existing) {
            $criteriaIds[$criterion['name']] = $existing['id'];
            echo "  → Criterion '{$criterion['name']}' already exists (ID: {$existing['id']})\n";
        } else {
            $db->query(
                "INSERT INTO criteria (event_id, name, description, max_score)
                 VALUES (?, ?, ?, ?)",
                [$eventId, $criterion['name'], $criterion['description'], $criterion['max_score']]
            );
            $criteriaIds[$criterion['name']] = $db->lastInsertId();
            echo "  ✓ Created criterion: {$criterion['name']} (ID: {$criteriaIds[$criterion['name']]})\n";
        }
    }
    
    // ============================================
    // STEP 5: Assign Criteria to Rounds
    // ============================================
    echo "\nStep 5: Assigning Criteria to Rounds...\n";
    
    $roundCriteria = [
        'Talent Competition' => [
            'Performance Quality',
            'Originality',
            'Stage Presence'
        ],
        'Swimsuit Competition' => [
            'Physical Fitness',
            'Confidence',
            'Overall Presentation'
        ],
        'Evening Gown' => [
            'Elegance',
            'Style & Fashion',
            'Stage Presence'
        ],
        'Top 20 Interview' => [
            'Communication Skills',
            'Intelligence',
            'Personality'
        ],
        'Swimsuit (Semi-Finals)' => [
            'Physical Fitness',
            'Confidence',
            'Overall Presentation'
        ],
        'Evening Gown (Semi-Finals)' => [
            'Elegance',
            'Style & Fashion',
            'Stage Presence'
        ],
        'Final Q&A' => [
            'Answer Quality',
            'Poise Under Pressure',
            'Communication'
        ]
    ];
    
    foreach ($roundCriteria as $roundName => $criteriaNames) {
        $roundId = $roundIds[$roundName];
        
        // Get all criteria for this round to calculate total max score
        $roundCriteriaList = [];
        $totalMaxScore = 0;
        foreach ($criteriaNames as $criteriaName) {
            if (!isset($criteriaIds[$criteriaName])) {
                echo "  ⚠ Warning: Criterion '{$criteriaName}' not found, skipping...\n";
                continue;
            }
            $criteriaId = $criteriaIds[$criteriaName];
            $criterion = $db->fetchOne("SELECT max_score FROM criteria WHERE id = ?", [$criteriaId]);
            if ($criterion) {
                $roundCriteriaList[] = [
                    'id' => $criteriaId,
                    'name' => $criteriaName,
                    'max_score' => $criterion['max_score']
                ];
                $totalMaxScore += $criterion['max_score'];
            }
        }
        
        if ($totalMaxScore == 0) {
            echo "  ⚠ Warning: No valid criteria for round '{$roundName}', skipping...\n";
            continue;
        }
        
        // Calculate auto-weights based on max_score
        foreach ($roundCriteriaList as $criterion) {
            $autoWeight = round(($criterion['max_score'] / $totalMaxScore) * 100, 3);
            
            $existing = $db->fetchOne(
                "SELECT id FROM criteria_weights WHERE round_id = ? AND criteria_id = ?",
                [$roundId, $criterion['id']]
            );
            
            if ($existing) {
                // Update weight
                $db->query(
                    "UPDATE criteria_weights SET weight = ?, is_active = 1 WHERE id = ?",
                    [$autoWeight, $existing['id']]
                );
                echo "  → Updated weight for '{$criterion['name']}' in '{$roundName}': {$autoWeight}%\n";
            } else {
                $db->query(
                    "INSERT INTO criteria_weights (round_id, criteria_id, weight, is_active)
                     VALUES (?, ?, ?, 1)",
                    [$roundId, $criterion['id'], $autoWeight]
                );
                echo "  ✓ Assigned '{$criterion['name']}' to '{$roundName}' (Weight: {$autoWeight}%)\n";
            }
        }
    }
    
    // ============================================
    // STEP 6: Create Contestants
    // ============================================
    echo "\nStep 6: Creating Contestants...\n";
    
    $contestants = [
        ['number' => '1', 'name' => 'Emma Rodriguez', 'team' => 'United States', 'category' => 'North America'],
        ['number' => '2', 'name' => 'Sofia Martinez', 'team' => 'Mexico', 'category' => 'North America'],
        ['number' => '3', 'name' => 'Isabella Santos', 'team' => 'Brazil', 'category' => 'South America'],
        ['number' => '4', 'name' => 'Olivia Chen', 'team' => 'China', 'category' => 'Asia'],
        ['number' => '5', 'name' => 'Charlotte Dubois', 'team' => 'France', 'category' => 'Europe'],
        ['number' => '6', 'name' => 'Amelia Johnson', 'team' => 'Australia', 'category' => 'Oceania'],
        ['number' => '7', 'name' => 'Mia Williams', 'team' => 'Canada', 'category' => 'North America'],
        ['number' => '8', 'name' => 'Luna Garcia', 'team' => 'Spain', 'category' => 'Europe'],
        ['number' => '9', 'name' => 'Aria Patel', 'team' => 'India', 'category' => 'Asia'],
        ['number' => '10', 'name' => 'Zara Khan', 'team' => 'Pakistan', 'category' => 'Asia'],
        ['number' => '11', 'name' => 'Maya Thompson', 'team' => 'United Kingdom', 'category' => 'Europe'],
        ['number' => '12', 'name' => 'Layla Anderson', 'team' => 'South Africa', 'category' => 'Africa'],
        ['number' => '13', 'name' => 'Nova Brown', 'team' => 'Jamaica', 'category' => 'Caribbean'],
        ['number' => '14', 'name' => 'Stella Wilson', 'team' => 'Italy', 'category' => 'Europe'],
        ['number' => '15', 'name' => 'Aurora Lee', 'team' => 'South Korea', 'category' => 'Asia'],
        ['number' => '16', 'name' => 'Celeste Taylor', 'team' => 'Philippines', 'category' => 'Asia'],
        ['number' => '17', 'name' => 'Iris White', 'team' => 'Netherlands', 'category' => 'Europe'],
        ['number' => '18', 'name' => 'Rose Davis', 'team' => 'Argentina', 'category' => 'South America'],
        ['number' => '19', 'name' => 'Lily Miller', 'team' => 'Germany', 'category' => 'Europe'],
        ['number' => '20', 'name' => 'Violet Moore', 'team' => 'Thailand', 'category' => 'Asia'],
        ['number' => '21', 'name' => 'Jasmine Garcia', 'team' => 'Colombia', 'category' => 'South America'],
        ['number' => '22', 'name' => 'Daisy Martinez', 'team' => 'Venezuela', 'category' => 'South America'],
        ['number' => '23', 'name' => 'Poppy Anderson', 'team' => 'Sweden', 'category' => 'Europe'],
        ['number' => '24', 'name' => 'Tulip Johnson', 'team' => 'Japan', 'category' => 'Asia'],
        ['number' => '25', 'name' => 'Orchid Smith', 'team' => 'New Zealand', 'category' => 'Oceania']
    ];
    
    $contestantCount = 0;
    foreach ($contestants as $contestant) {
        $existing = $db->fetchOne(
            "SELECT id FROM contestants WHERE event_id = ? AND contestant_number = ?",
            [$eventId, $contestant['number']]
        );
        
        if ($existing) {
            echo "  → Contestant #{$contestant['number']} ({$contestant['name']}) already exists\n";
        } else {
            $db->query(
                "INSERT INTO contestants (event_id, contestant_number, name, team_name, category, status)
                 VALUES (?, ?, ?, ?, ?, 'Active')",
                [$eventId, $contestant['number'], $contestant['name'], $contestant['team'], $contestant['category']]
            );
            $contestantCount++;
            echo "  ✓ Created contestant: #{$contestant['number']} - {$contestant['name']}\n";
        }
    }
    echo "  → Created {$contestantCount} new contestants\n";
    
    // ============================================
    // STEP 7: Get/Create Judge Role
    // ============================================
    echo "\nStep 7: Getting Judge Role...\n";
    
    $judgeRole = $db->fetchOne("SELECT id FROM roles WHERE name = 'Judge'");
    if (!$judgeRole) {
        die("❌ Error: Judge role not found. Please run schema.sql first.\n");
    }
    $judgeRoleId = $judgeRole['id'];
    echo "  ✓ Judge role found (ID: {$judgeRoleId})\n";
    
    // ============================================
    // STEP 8: Create Judges
    // ============================================
    echo "\nStep 8: Creating Judges...\n";
    
    $judges = [
        ['username' => 'judge1', 'email' => 'judge1@pageant.local', 'full_name' => 'John Anderson', 'judge_number' => '1'],
        ['username' => 'judge2', 'email' => 'judge2@pageant.local', 'full_name' => 'Maria Garcia', 'judge_number' => '2'],
        ['username' => 'judge3', 'email' => 'judge3@pageant.local', 'full_name' => 'David Chen', 'judge_number' => '3'],
        ['username' => 'judge4', 'email' => 'judge4@pageant.local', 'full_name' => 'Sarah Williams', 'judge_number' => '4'],
        ['username' => 'judge5', 'email' => 'judge5@pageant.local', 'full_name' => 'Michael Brown', 'judge_number' => '5'],
        ['username' => 'judge6', 'email' => 'judge6@pageant.local', 'full_name' => 'Lisa Johnson', 'judge_number' => '6'],
        ['username' => 'judge7', 'email' => 'judge7@pageant.local', 'full_name' => 'Robert Taylor', 'judge_number' => '7']
    ];
    
    $judgeIds = [];
    $judgeUserIds = [];
    foreach ($judges as $judgeData) {
        // Check if user exists
        $existingUser = $db->fetchOne(
            "SELECT id FROM users WHERE username = ?",
            [$judgeData['username']]
        );
        
        if ($existingUser) {
            $userId = $existingUser['id'];
            echo "  → User '{$judgeData['username']}' already exists (ID: {$userId})\n";
        } else {
            $passwordHash = password_hash('judge123', PASSWORD_BCRYPT);
            $db->query(
                "INSERT INTO users (username, email, password_hash, full_name, role_id, is_active)
                 VALUES (?, ?, ?, ?, ?, 1)",
                [$judgeData['username'], $judgeData['email'], $passwordHash, $judgeData['full_name'], $judgeRoleId]
            );
            $userId = $db->lastInsertId();
            echo "  ✓ Created user: {$judgeData['username']} (ID: {$userId})\n";
        }
        
        $judgeUserIds[$judgeData['username']] = $userId;
        
        // Check if judge record exists
        $existingJudge = $db->fetchOne(
            "SELECT id FROM judges WHERE user_id = ? AND event_id = ?",
            [$userId, $eventId]
        );
        
        if ($existingJudge) {
            $judgeIds[$judgeData['username']] = $existingJudge['id'];
            echo "  → Judge '{$judgeData['username']}' already assigned to event (Judge ID: {$existingJudge['id']})\n";
        } else {
            $db->query(
                "INSERT INTO judges (user_id, event_id, judge_number, specialty)
                 VALUES (?, ?, ?, ?)",
                [$userId, $eventId, $judgeData['judge_number'], 'Pageant Judge']
            );
            $judgeIds[$judgeData['username']] = $db->lastInsertId();
            echo "  ✓ Assigned judge: {$judgeData['username']} to event (Judge ID: {$judgeIds[$judgeData['username']]})\n";
            
            // Auto-assign judge to event
            $db->query(
                "INSERT INTO user_event_assignments (user_id, event_id, assigned_by, is_active)
                 VALUES (?, ?, ?, 1)
                 ON DUPLICATE KEY UPDATE is_active = 1",
                [$userId, $eventId, $adminId]
            );
        }
    }
    
    // ============================================
    // STEP 9: Assign Judges to Rounds
    // ============================================
    echo "\nStep 9: Assigning Judges to Rounds...\n";
    
    foreach ($roundIds as $roundName => $roundId) {
        foreach ($judgeIds as $judgeUsername => $judgeId) {
            $existing = $db->fetchOne(
                "SELECT id FROM judge_assignments WHERE judge_id = ? AND round_id = ?",
                [$judgeId, $roundId]
            );
            
            if ($existing) {
                // Reactivate if inactive
                $db->query(
                    "UPDATE judge_assignments SET is_active = 1 WHERE id = ?",
                    [$existing['id']]
                );
            } else {
                $db->query(
                    "INSERT INTO judge_assignments (judge_id, round_id, is_active)
                     VALUES (?, ?, 1)",
                    [$judgeId, $roundId]
                );
            }
        }
        echo "  ✓ Assigned all 7 judges to round: {$roundName}\n";
    }
    
    // ============================================
    // STEP 10: Create Event Organizer and Technical Admin (Optional)
    // ============================================
    echo "\nStep 10: Creating Event Staff (Optional)...\n";
    
    // Get roles
    $organizerRole = $db->fetchOne("SELECT id FROM roles WHERE name = 'Event Organizer'");
    $techAdminRole = $db->fetchOne("SELECT id FROM roles WHERE name = 'Event Technical Admin'");
    
    if ($organizerRole && $techAdminRole) {
        $staff = [
            [
                'username' => 'organizer1',
                'email' => 'organizer@pageant.local',
                'full_name' => 'Patricia Thompson',
                'password' => 'organizer123',
                'role_id' => $organizerRole['id']
            ],
            [
                'username' => 'techadmin1',
                'email' => 'techadmin@pageant.local',
                'full_name' => 'James Wilson',
                'password' => 'techadmin123',
                'role_id' => $techAdminRole['id']
            ]
        ];
        
        foreach ($staff as $staffMember) {
            $existing = $db->fetchOne(
                "SELECT id FROM users WHERE username = ?",
                [$staffMember['username']]
            );
            
            if ($existing) {
                echo "  → User '{$staffMember['username']}' already exists\n";
            } else {
                $passwordHash = password_hash($staffMember['password'], PASSWORD_BCRYPT);
                $db->query(
                    "INSERT INTO users (username, email, password_hash, full_name, role_id, is_active)
                     VALUES (?, ?, ?, ?, ?, 1)",
                    [$staffMember['username'], $staffMember['email'], $passwordHash, $staffMember['full_name'], $staffMember['role_id']]
                );
                $staffUserId = $db->lastInsertId();
                
                // Assign to event
                $db->query(
                    "INSERT INTO user_event_assignments (user_id, event_id, assigned_by, is_active)
                     VALUES (?, ?, ?, 1)
                     ON DUPLICATE KEY UPDATE is_active = 1",
                    [$staffUserId, $eventId, $adminId]
                );
                
                echo "  ✓ Created and assigned: {$staffMember['username']}\n";
            }
        }
    } else {
        echo "  → Skipping (roles not found)\n";
    }
    
    $db->getConnection()->commit();
    
    echo "\n========================================\n";
    echo "✅ Seeding Complete!\n";
    echo "========================================\n\n";
    
    echo "Summary:\n";
    echo "  - Event: Miss Universe 2024 (ID: {$eventId})\n";
    echo "  - Levels: 3\n";
    echo "  - Rounds: 7\n";
    echo "  - Criteria: 15\n";
    echo "  - Contestants: 25\n";
    echo "  - Judges: 7\n";
    echo "  - All judges assigned to all rounds\n\n";
    
    echo "Login Credentials:\n";
    echo "  Admin: admin / admin123\n";
    echo "  Judges: judge1-judge7 / judge123\n";
    if ($organizerRole && $techAdminRole) {
        echo "  Organizer: organizer1 / organizer123\n";
        echo "  Tech Admin: techadmin1 / techadmin123\n";
    }
    echo "\n";
    
} catch (Exception $e) {
    $db->getConnection()->rollBack();
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

