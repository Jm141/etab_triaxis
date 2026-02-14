<?php
/**
 * Assign Judges to Pageant 2026 Event
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

echo "Found Event: {$event['name']} (ID: {$event['id']})\n\n";

// Get all available judges (users with Judge role)
$judges = $db->fetchAll(
    "SELECT u.id as user_id, u.username, u.full_name, r.name as role_name
     FROM users u
     JOIN roles r ON u.role_id = r.id
     WHERE r.name = 'Judge' AND u.is_active = 1
     ORDER BY u.id"
);

if (empty($judges)) {
    echo "ERROR: No judge users found. Creating judge users...\n";
    
    // Create 5 judge users
    for ($i = 1; $i <= 5; $i++) {
        $username = "judge{$i}";
        $email = "judge{$i}@pageant2026.com";
        $fullName = "Judge {$i}";
        $passwordHash = password_hash('judge123', PASSWORD_DEFAULT);
        
        // Get Judge role ID
        $judgeRole = $db->fetchOne("SELECT id FROM roles WHERE name = 'Judge'");
        if (!$judgeRole) {
            echo "ERROR: Judge role not found.\n";
            exit(1);
        }
        
        // Check if user exists
        $existing = $db->fetchOne("SELECT id FROM users WHERE username = ?", [$username]);
        if ($existing) {
            echo "  Judge user '{$username}' already exists.\n";
            continue;
        }
        
        $db->query(
            "INSERT INTO users (username, email, password_hash, full_name, role_id, is_active)
             VALUES (?, ?, ?, ?, ?, 1)",
            [$username, $email, $passwordHash, $fullName, $judgeRole['id']]
        );
        echo "  Created judge user: {$username}\n";
    }
    
    // Refresh judges list
    $judges = $db->fetchAll(
        "SELECT u.id as user_id, u.username, u.full_name, r.name as role_name
         FROM users u
         JOIN roles r ON u.role_id = r.id
         WHERE r.name = 'Judge' AND u.is_active = 1
         ORDER BY u.id"
    );
}

echo "=== Available Judges ===\n";
foreach ($judges as $judge) {
    echo "  - {$judge['full_name']} ({$judge['username']}) - User ID: {$judge['user_id']}\n";
}
echo "\n";

// Get all rounds for this event
$rounds = $db->fetchAll(
    "SELECT r.id, r.name, el.name as level_name
     FROM rounds r
     JOIN event_levels el ON r.level_id = el.id
     WHERE el.event_id = ?
     ORDER BY el.`order` ASC, r.`order` ASC",
    [$event['id']]
);

echo "=== Rounds ===\n";
foreach ($rounds as $round) {
    echo "  - Round ID {$round['id']}: {$round['level_name']} - {$round['name']}\n";
}
echo "\n";

// Assign judges to event and rounds
foreach ($judges as $judge) {
    // Check if judge already assigned to event
    $existingJudge = $db->fetchOne(
        "SELECT id FROM judges WHERE event_id = ? AND user_id = ?",
        [$event['id'], $judge['user_id']]
    );
    
    if (!$existingJudge) {
        // Create judge record for this event
        $db->query(
            "INSERT INTO judges (event_id, user_id, is_active)
             VALUES (?, ?, 1)",
            [$event['id'], $judge['user_id']]
        );
        $judgeId = $db->lastInsertId();
        echo "Created judge record for {$judge['full_name']} (Judge ID: {$judgeId})\n";
    } else {
        $judgeId = $existingJudge['id'];
        // Activate if inactive
        $db->query(
            "UPDATE judges SET is_active = 1 WHERE id = ?",
            [$judgeId]
        );
        echo "Found existing judge record for {$judge['full_name']} (Judge ID: {$judgeId})\n";
    }
    
    // Assign judge to all rounds
    foreach ($rounds as $round) {
        $existingAssignment = $db->fetchOne(
            "SELECT id FROM judge_assignments WHERE judge_id = ? AND round_id = ?",
            [$judgeId, $round['id']]
        );
        
        if (!$existingAssignment) {
            $db->query(
                "INSERT INTO judge_assignments (judge_id, round_id, is_active)
                 VALUES (?, ?, 1)",
                [$judgeId, $round['id']]
            );
            echo "  Assigned to Round {$round['id']} ({$round['name']})\n";
        } else {
            // Activate if inactive
            $db->query(
                "UPDATE judge_assignments SET is_active = 1 WHERE id = ?",
                [$existingAssignment['id']]
            );
            echo "  Already assigned to Round {$round['id']} ({$round['name']}) - activated\n";
        }
    }
    
    echo "\n";
}

echo "=== Assignment Complete ===\n";
echo "All judges have been assigned to the event and all rounds.\n";
