<?php
/**
 * Generate Complete Test Data for Excel Verification
 * 
 * This script generates a complete CSV file with all test data:
 * - 10 Contestants (C1 to C10)
 * - 5 Judges (J1 to J5)
 * - 6 Rounds (R1 to R6)
 * - 3 Criteria per round (18 total criteria)
 * - Max scores: 10, 15, 10 (low values for easy testing)
 * 
 * Usage: php generate_excel_test_data.php
 */

// Contestant performance levels (for varied scores)
$contestantProfiles = [
    'C1' => ['high' => 9, 'mid' => 14, 'low' => 9],    // High performer (expected winner)
    'C2' => ['high' => 7, 'mid' => 11, 'low' => 7],  // Above average
    'C3' => ['high' => 6, 'mid' => 10, 'low' => 6],  // Average
    'C4' => ['high' => 7, 'mid' => 12, 'low' => 7],  // Above average
    'C5' => ['high' => 5, 'mid' => 9, 'low' => 5],   // Below average
    'C6' => ['high' => 6, 'mid' => 11, 'low' => 6],  // Average
    'C7' => ['high' => 8, 'mid' => 13, 'low' => 8],  // Good performer
    'C8' => ['high' => 5, 'mid' => 8, 'low' => 5],  // Below average
    'C9' => ['high' => 4, 'mid' => 7, 'low' => 4],  // Low performer
    'C10' => ['high' => 6, 'mid' => 10, 'low' => 6] // Average
];

// Rounds configuration
$rounds = [
    'R1' => ['name' => 'Talent Competition', 'level' => 'L1', 'criteria' => ['C1', 'C2', 'C3']],
    'R2' => ['name' => 'Q&A Session', 'level' => 'L1', 'criteria' => ['C4', 'C5', 'C6']],
    'R3' => ['name' => 'Evening Gown', 'level' => 'L2', 'criteria' => ['C7', 'C8', 'C9']],
    'R4' => ['name' => 'Swimsuit', 'level' => 'L2', 'criteria' => ['C10', 'C11', 'C12']],
    'R5' => ['name' => 'Final Q&A', 'level' => 'L3', 'criteria' => ['C13', 'C14', 'C15']],
    'R6' => ['name' => 'Final Walk', 'level' => 'L3', 'criteria' => ['C16', 'C17', 'C18']]
];

// Criteria max scores (10, 15, 10 pattern)
$criteriaMaxScores = [
    'C1' => 10, 'C2' => 15, 'C3' => 10,
    'C4' => 10, 'C5' => 15, 'C6' => 10,
    'C7' => 10, 'C8' => 15, 'C9' => 10,
    'C10' => 10, 'C11' => 15, 'C12' => 10,
    'C13' => 10, 'C14' => 15, 'C15' => 10,
    'C16' => 10, 'C17' => 15, 'C18' => 10
];

// Judges
$judges = ['J1', 'J2', 'J3', 'J4', 'J5'];

// Generate CSV data
$csvData = [];
$csvData[] = ['Contestant', 'Judge', 'Round', 'Criteria', 'Raw_Score', 'Max_Score', 'Calculated_Score'];

foreach ($contestantProfiles as $contestant => $profile) {
    foreach ($judges as $judge) {
        foreach ($rounds as $roundId => $round) {
            foreach ($round['criteria'] as $idx => $criteriaId) {
                // Determine score based on criteria position (high/mid/low)
                if ($idx == 0) {
                    $rawScore = $profile['high'];
                } elseif ($idx == 1) {
                    $rawScore = $profile['mid'];
                } else {
                    $rawScore = $profile['low'];
                }
                
                // Add slight variation per judge (to make it realistic)
                $variation = rand(-1, 1);
                $rawScore = max(0, min($rawScore + $variation, $criteriaMaxScores[$criteriaId]));
                
                $maxScore = $criteriaMaxScores[$criteriaId];
                
                $csvData[] = [
                    $contestant,
                    $judge,
                    $roundId,
                    $criteriaId,
                    $rawScore,
                    $maxScore,
                    '' // Will be calculated in Excel
                ];
            }
        }
    }
}

// Write CSV file
$filename = __DIR__ . '/COMPLETE_TEST_DATA.csv';
$fp = fopen($filename, 'w');

foreach ($csvData as $row) {
    fputcsv($fp, $row);
}

fclose($fp);

echo "✅ Test data generated successfully!\n";
echo "📁 File: $filename\n";
echo "📊 Total rows: " . count($csvData) . "\n";
echo "\n";

// Generate summary
echo "📋 Summary:\n";
echo "   - Contestants: 10 (C1 to C10)\n";
echo "   - Judges: 5 (J1 to J5)\n";
echo "   - Rounds: 6 (R1 to R6)\n";
echo "   - Criteria per round: 3\n";
echo "   - Total score entries: " . (10 * 5 * 6 * 3) . "\n";
echo "\n";

// Generate expected results
echo "🎯 Expected Results (Manual Calculation):\n";
echo "\n";

// Calculate expected scores for C1 (high performer)
$c1Scores = [];
foreach ($rounds as $roundId => $round) {
    $roundTotal = 0;
    foreach ($judges as $judge) {
        $rawTotal = 0;
        $maxTotal = 0;
        foreach ($round['criteria'] as $idx => $criteriaId) {
            if ($idx == 0) {
                $rawScore = $contestantProfiles['C1']['high'];
            } elseif ($idx == 1) {
                $rawScore = $contestantProfiles['C1']['mid'];
            } else {
                $rawScore = $contestantProfiles['C1']['low'];
            }
            $rawTotal += $rawScore;
            $maxTotal += $criteriaMaxScores[$criteriaId];
        }
        $judgeScore = ($rawTotal / $maxTotal) * 100;
        $roundTotal += $judgeScore;
    }
    $c1Scores[$roundId] = round($roundTotal, 2);
}

$c1Preliminary = $c1Scores['R1'] + $c1Scores['R2'];
$c1SemiFinal = $c1Scores['R3'] + $c1Scores['R4'];
$c1Final = $c1Scores['R5'] + $c1Scores['R6'];
$c1GrandTotal = $c1Preliminary + $c1SemiFinal + $c1Final;

echo "   Contestant C1 (Expected Winner):\n";
echo "   - Round 1 Total: " . $c1Scores['R1'] . "\n";
echo "   - Round 2 Total: " . $c1Scores['R2'] . "\n";
echo "   - Preliminary Total: " . $c1Preliminary . "\n";
echo "   - Round 3 Total: " . $c1Scores['R3'] . "\n";
echo "   - Round 4 Total: " . $c1Scores['R4'] . "\n";
echo "   - Semi-Final Total: " . $c1SemiFinal . "\n";
echo "   - Round 5 Total: " . $c1Scores['R5'] . "\n";
echo "   - Round 6 Total: " . $c1Scores['R6'] . "\n";
echo "   - Final Total: " . $c1Final . "\n";
echo "   - 🏆 Grand Total: " . $c1GrandTotal . " (Rank 1)\n";
echo "\n";

echo "✅ Use this data to verify system calculations in Excel!\n";
echo "📖 See EXCEL_TEST_TEMPLATE.md for Excel formulas.\n";
