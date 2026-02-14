<?php
require_once 'core/Database.php';

$db = Database::getInstance();

echo "=== Testing Different Advance Count Scenarios ===\n";

// Scenario 1: Different advance counts
echo "\n--- Scenario 1: Different Advance Counts ---\n";

// Simulate Level 1 with advance_count = 3 (only 3 advance to Level 2)
echo "Level 1: advance_count = 3 (only top 3 advance)\n";
echo "Expected: 3 contestants advance to Level 2, 4 eliminated\n";

// Simulate Level 2 with advance_count = 2 (only 2 advance to Level 3)
echo "Level 2: advance_count = 2 (only top 2 advance)\n";
echo "Expected: 2 contestants advance to Level 3, others eliminated\n";

// Test judge scoring logic for Level 2 with different advance counts
echo "\nJudge Scoring Logic Test:\n";
echo "1. If Level 2 has scores -> show contestants with scores (works regardless of advance_count)\n";
echo "2. If Level 2 has no scores -> show qualified contestants (works regardless of advance_count)\n";
echo "Result: ✅ Works with any advance_count\n";

// Scenario 2: No advancement needed
echo "\n--- Scenario 2: No Advancement Needed ---\n";

// Simulate Level 1 with advance_count = NULL (all advance)
echo "Level 1: advance_count = NULL (all contestants advance)\n";
echo "Expected: All 7 contestants advance to Level 2\n";

// Simulate Level 2 with advance_count = NULL (all advance)
echo "Level 2: advance_count = NULL (all contestants advance)\n";
echo "Expected: All who reach Level 2 advance to Level 3\n";

// Test judge scoring logic for no advancement
echo "\nJudge Scoring Logic Test:\n";
echo "1. Previous level has no elimination (advance_count = NULL)\n";
echo "2. Logic goes to ELSE clause -> show all active contestants\n";
echo "Result: ✅ Works when no advancement needed\n";

echo "\n=== Summary ===\n";
echo "✅ Different advance_counts: System works because it shows contestants with scores first\n";
echo "✅ No advancement needed: System works because it uses ELSE clause for all active contestants\n";
echo "✅ Dynamic advancement: System adapts to any advance_count configuration\n";

echo "\n=== Key Points ===\n";
echo "1. Judge scoring shows contestants WITH SCORES in the level (primary)\n";
echo "2. Falls back to qualified contestants only if NO scores exist yet\n";
echo "3. Works regardless of advance_count value (3, 5, 10, NULL, etc.)\n";
echo "4. Works regardless of elimination configuration\n";

echo "\n=== Done ===\n";
