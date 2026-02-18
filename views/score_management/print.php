<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Scores - <?= htmlspecialchars($event['name']) ?></title>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            @page {
                size: A4 landscape;
                margin: 1cm;
            }
            body {
                margin: 0;
                padding: 0;
            }
            .page-break {
                page-break-after: always;
                page-break-before: always;
            }
            .table-section {
                page-break-after: always;
                page-break-inside: avoid;
            }
            .table-section:last-child {
                page-break-after: auto;
            }
            table {
                page-break-inside: avoid;
            }
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            margin: 20px;
            background: #fff;
            color: #000;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 18pt;
            font-weight: bold;
        }
        
        .header p {
            margin: 5px 0;
            font-size: 10pt;
        }
        
        .round-section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        
        .table-section {
            page-break-after: always;
            page-break-inside: avoid;
        }
        
        .table-section:last-child {
            page-break-after: auto;
        }
        
        .round-header {
            background: #f0f0f0;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #000;
        }
        
        .round-header h2 {
            margin: 0;
            font-size: 14pt;
            font-weight: bold;
        }
        
        .round-header p {
            margin: 3px 0;
            font-size: 9pt;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 9pt;
        }
        
        table th {
            background: #e0e0e0;
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
            font-weight: bold;
        }
        
        table td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
        }
        
        table td.left-align {
            text-align: left;
        }
        
        .contestant-group {
            background: #f9f9f9;
        }
        
        .total-row {
            background: #e8e8e8;
            font-weight: bold;
        }
        
        .print-actions {
            margin-bottom: 20px;
            text-align: center;
        }
        
        .print-actions button {
            padding: 10px 20px;
            font-size: 14px;
            margin: 0 10px;
            cursor: pointer;
        }
        
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8pt;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="print-actions no-print">
        <button onclick="window.print()" style="background: #007bff; color: white; border: none; border-radius: 4px;">
            <i class="fas fa-print"></i> Print
        </button>
        <button onclick="window.close()" style="background: #6c757d; color: white; border: none; border-radius: 4px;">
            Close
        </button>
    </div>
    
    <div class="header">
        <h1><?= htmlspecialchars($event['name']) ?></h1>
        <p><strong>Score Report</strong></p>
        <p>Printed on: <?= date('F d, Y h:i A') ?></p>
    </div>
    
    <?php 
    $roundCount = 0;
    $totalRounds = count($scoresByRound);
    ?>
    <?php foreach ($scoresByRound as $roundData): ?>
        <?php 
        $roundCount++;
        $round = $roundData['round']; 
        $scores = $roundData['scores']; 
        $criteria = $roundData['criteria']; 
        ?>
        
        <div class="round-section table-section">
            <div class="round-header">
                <h2><?= htmlspecialchars($round['name']) ?></h2>
                <p>
                    <strong>Level:</strong> <?= htmlspecialchars($round['level_name']) ?> | 
                    <strong>Total Criteria:</strong> <?= count($criteria) ?> | 
                    <strong>Total Scores:</strong> <?= count($scores) ?>
                </p>
            </div>
            
            <?php if (empty($scores)): ?>
                <p style="text-align: center; font-style: italic; color: #666;">No scores recorded for this round.</p>
            <?php else: ?>
                <?php
                // Group scores by contestant
                $scoresByContestant = [];
                foreach ($scores as $score) {
                    $cid = $score['contestant_id'];
                    if (!isset($scoresByContestant[$cid])) {
                        $scoresByContestant[$cid] = [
                            'contestant_number' => $score['contestant_number'],
                            'contestant_name' => $score['contestant_name'],
                            'scores' => []
                        ];
                    }
                    $scoresByContestant[$cid]['scores'][] = $score;
                }
                ?>
                
                <table style="page-break-inside: avoid;">
                    <thead>
                        <tr>
                            <th rowspan="2" style="width: 5%;">#</th>
                            <th rowspan="2" style="width: 15%;" class="left-align">Contestant</th>
                            <th rowspan="2" style="width: 8%;">Judge</th>
                            <?php foreach ($criteria as $criterion): ?>
                                <th style="width: <?= 60 / count($criteria) ?>%;">
                                    <?= htmlspecialchars($criterion['name']) ?><br>
                                    <small>(Max: <?= $criterion['max_score'] ?>)</small>
                                </th>
                            <?php endforeach; ?>
                            <th rowspan="2" style="width: 8%;">Total<br>Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($scoresByContestant as $cid => $contestantData): ?>
                            <?php 
                            $firstRow = true;
                            $rowspan = count($contestantData['scores']);
                            ?>
                            <?php foreach ($contestantData['scores'] as $index => $score): ?>
                                <tr class="<?= $index === 0 ? 'contestant-group' : '' ?>">
                                    <?php if ($firstRow): ?>
                                        <td rowspan="<?= $rowspan ?>" style="font-weight: bold;">
                                            <?= htmlspecialchars($contestantData['contestant_number']) ?>
                                        </td>
                                        <td rowspan="<?= $rowspan ?>" class="left-align" style="font-weight: bold;">
                                            <?= htmlspecialchars($contestantData['contestant_name']) ?>
                                        </td>
                                        <?php $firstRow = false; ?>
                                    <?php endif; ?>
                                    
                                    <td>
                                        Judge #<?= htmlspecialchars($score['judge_number'] ?: '-') ?><br>
                                        <small><?= htmlspecialchars($score['judge_name']) ?></small>
                                        <?php if (empty($score['is_submitted'])): ?>
                                            <br><small style="color:#b45309;"><strong>(Draft)</strong></small>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <?php 
                                    // Create a map of criteria_id => score_detail for quick lookup
                                    $detailsMap = [];
                                    foreach ($score['details'] as $detail) {
                                        $detailsMap[$detail['criteria_id']] = $detail;
                                    }
                                    ?>
                                    
                                    <?php foreach ($criteria as $criterion): ?>
                                        <td>
                                            <?php 
                                            $detail = $detailsMap[$criterion['id']] ?? null;
                                            if ($detail) {
                                                echo number_format($detail['raw_score'], 3);
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>
                                    <?php endforeach; ?>
                                    
                                    <td style="font-weight: bold;">
                                        <?php 
                                        $finalScore = $score['total_score'];
                                        if (!empty($score['point_deduction']) && $score['point_deduction'] > 0) {
                                            $finalScore -= $score['point_deduction'];
                                            echo '<span style="text-decoration: line-through; color: #999;">' . number_format($score['total_score'], 3) . '</span><br>';
                                            echo '<span style="color: #d32f2f;">' . number_format($finalScore, 3) . '</span>';
                                            if (!empty($score['deduction_reason'])) {
                                                echo '<br><small style="color: #d32f2f;">(-' . number_format($score['point_deduction'], 3) . ': ' . htmlspecialchars($score['deduction_reason']) . ')</small>';
                                            }
                                        } else {
                                            echo number_format($finalScore, 3);
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            
                            <?php
                            // Calculate average for this contestant (accounting for deductions)
                            $contestantTotal = 0;
                            $contestantCount = 0;
                            foreach ($contestantData['scores'] as $score) {
                                $finalScore = $score['total_score'];
                                if (!empty($score['point_deduction']) && $score['point_deduction'] > 0) {
                                    $finalScore -= $score['point_deduction'];
                                }
                                $contestantTotal += $finalScore;
                                $contestantCount++;
                            }
                            $contestantAverage = $contestantCount > 0 ? $contestantTotal / $contestantCount : 0;
                            $contestantAverage = round($contestantAverage, 3);
                            ?>
                            
                            <tr class="total-row">
                                <td colspan="<?= 3 + count($criteria) ?>" style="text-align: right; padding-right: 10px;">
                                    <strong>Average for Contestant #<?= htmlspecialchars($contestantData['contestant_number']) ?>:</strong>
                                </td>
                                <td style="font-weight: bold; font-size: 10pt;">
                                    <?= number_format($contestantAverage, 3) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <?php if ($roundCount < $totalRounds): ?>
            <!-- Page break handled by .table-section CSS -->
        <?php endif; ?>
    <?php endforeach; ?>
    
    <div class="footer">
        <p>Generated by Tabulation System | <?= date('Y-m-d H:i:s') ?></p>
    </div>
    
    <script>
        // Auto-print when page loads (optional)
        // window.onload = function() {
        //     window.print();
        // };
    </script>
</body>
</html>

