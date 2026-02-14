<?php
/**
 * Assign Criteria to Pageant 2026 Rounds
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

// Get all rounds for this event
$rounds = $db->fetchAll(
    "SELECT r.*, el.name as level_name
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

// Define criteria for each round type
$criteriaDefinitions = [
    'Talent Competition' => [
        ['name' => 'Performance Quality', 'max_score' => 10.0, 'weight' => 40],
        ['name' => 'Stage Presence', 'max_score' => 10.0, 'weight' => 30],
        ['name' => 'Originality', 'max_score' => 10.0, 'weight' => 30]
    ],
    'Q&A Session' => [
        ['name' => 'Content & Clarity', 'max_score' => 10.0, 'weight' => 40],
        ['name' => 'Confidence', 'max_score' => 10.0, 'weight' => 30],
        ['name' => 'Communication Skills', 'max_score' => 10.0, 'weight' => 30]
    ],
    'Evening Gown' => [
        ['name' => 'Poise & Elegance', 'max_score' => 10.0, 'weight' => 35],
        ['name' => 'Stage Walk', 'max_score' => 10.0, 'weight' => 35],
        ['name' => 'Overall Presentation', 'max_score' => 10.0, 'weight' => 30]
    ],
    'Swimsuit' => [
        ['name' => 'Physical Fitness', 'max_score' => 10.0, 'weight' => 40],
        ['name' => 'Confidence', 'max_score' => 10.0, 'weight' => 30],
        ['name' => 'Stage Presence', 'max_score' => 10.0, 'weight' => 30]
    ],
    'Final Q&A' => [
        ['name' => 'Intelligence & Wit', 'max_score' => 15.0, 'weight' => 40],
        ['name' => 'Composure', 'max_score' => 10.0, 'weight' => 30],
        ['name' => 'Persuasiveness', 'max_score' => 10.0, 'weight' => 30]
    ],
    'Final Walk' => [
        ['name' => 'Grace & Poise', 'max_score' => 10.0, 'weight' => 35],
        ['name' => 'Confidence', 'max_score' => 10.0, 'weight' => 35],
        ['name' => 'Overall Impact', 'max_score' => 10.0, 'weight' => 30]
    ]
];

// Process each round
foreach ($rounds as $round) {
    $roundName = $round['name'];
    echo "=== Processing Round: {$round['level_name']} - {$roundName} (ID: {$round['id']}) ===\n";
    
    // Find matching criteria definition
    $criteriaDef = null;
    foreach ($criteriaDefinitions as $key => $def) {
        if (stripos($roundName, $key) !== false) {
            $criteriaDef = $def;
            break;
        }
    }
    
    if (!$criteriaDef) {
        // Use default criteria
        $criteriaDef = [
            ['name' => 'Criterion 1', 'max_score' => 10.0, 'weight' => 33.33],
            ['name' => 'Criterion 2', 'max_score' => 10.0, 'weight' => 33.33],
            ['name' => 'Criterion 3', 'max_score' => 10.0, 'weight' => 33.34]
        ];
        echo "  Using default criteria (no match found)\n";
    }
    
    foreach ($criteriaDef as $criterionData) {
        // Check if criterion exists
        $criterion = $db->fetchOne(
            "SELECT id FROM criteria WHERE name = ? AND max_score = ?",
            [$criterionData['name'], $criterionData['max_score']]
        );
        
        if (!$criterion) {
            // Create criterion
            $db->query(
                "INSERT INTO criteria (event_id, name, description, max_score, created_at)
                 VALUES (?, ?, ?, ?, NOW())",
                [$event['id'], $criterionData['name'], "Criterion for {$roundName}", $criterionData['max_score']]
            );
            $criterionId = $db->lastInsertId();
            echo "  Created criterion: {$criterionData['name']} (ID: {$criterionId}, Max: {$criterionData['max_score']})\n";
        } else {
            $criterionId = $criterion['id'];
            echo "  Found existing criterion: {$criterionData['name']} (ID: {$criterionId})\n";
        }
        
        // Check if criteria_weight already exists
        $existingWeight = $db->fetchOne(
            "SELECT id FROM criteria_weights WHERE round_id = ? AND criteria_id = ?",
            [$round['id'], $criterionId]
        );
        
        if (!$existingWeight) {
            // Create criteria_weight
            $db->query(
                "INSERT INTO criteria_weights (round_id, criteria_id, weight, is_active)
                 VALUES (?, ?, ?, 1)",
                [$round['id'], $criterionId, $criterionData['weight']]
            );
            echo "    Assigned to round with weight: {$criterionData['weight']}%\n";
        } else {
            // Update weight and activate
            $db->query(
                "UPDATE criteria_weights SET weight = ?, is_active = 1 WHERE id = ?",
                [$criterionData['weight'], $existingWeight['id']]
            );
            echo "    Updated weight to: {$criterionData['weight']}%\n";
        }
    }
    
    echo "\n";
}

echo "=== Criteria Assignment Complete ===\n";
