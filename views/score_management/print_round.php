<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Scores - <?= htmlspecialchars($round['name']) ?></title>
    <style>
        @media print {
            .no-print { display: none !important; }
            @page {
                size: A4 landscape;
                margin: 0.8cm;
            }
            body { margin: 0; padding: 0; font-size: 11pt; }
            .judge-section { 
                page-break-after: always;
                page-break-before: always;
                page-break-inside: avoid;
            }
            .judge-section:first-child {
                page-break-before: auto;
            }
            .judge-section:last-child {
                page-break-after: auto;
            }
            table {
                page-break-inside: avoid;
                font-size: 11pt !important;
            }
            table th, table td { padding: 8px 10px !important; }
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            margin: 15px;
            background: #fff;
            color: #000;
        }
        
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 20pt;
            font-weight: bold;
        }
        
        .header p {
            margin: 6px 0;
            font-size: 12pt;
        }
        
        .judge-section {
            margin-bottom: 0;
            min-height: 100%;
        }
        
        .judge-header {
            background: #f0f0f0;
            padding: 10px 12px;
            margin-bottom: 12px;
            border: 1px solid #000;
        }
        
        .judge-header h2 {
            margin: 0;
            font-size: 16pt;
            font-weight: bold;
        }
        
        .judge-header p {
            margin: 4px 0;
            font-size: 11pt;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 11pt;
        }
        
        table th {
            background: #e0e0e0;
            border: 1px solid #000;
            padding: 8px 10px;
            text-align: center;
            font-weight: bold;
            font-size: 10.5pt;
        }
        
        table th small {
            font-size: 9.5pt;
        }
        
        table td {
            border: 1px solid #000;
            padding: 8px 10px;
            text-align: center;
            font-size: 11pt;
        }
        
        table td.left-align {
            text-align: left;
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
            margin-top: 15px;
            text-align: center;
            font-size: 9pt;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 8px;
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
    
    <?php if (empty($judgeData)): ?>
        <div class="header">
            <h1><?= htmlspecialchars($round['event_name']) ?></h1>
            <p><strong><?= htmlspecialchars($round['name']) ?></strong></p>
            <!-- <p><strong><?= htmlspecialchars($round['level_name']) ?></strong></p> -->
        </div>
        <p style="text-align: center; font-style: italic; color: #666;">No scores recorded for this round.</p>
    <?php else: ?>
        <?php 
        $judgeCount = 0;
        $totalJudges = count($judgeData);
        ?>
        <?php foreach ($judgeData as $judgeDataItem): ?>
            <?php 
            $judgeCount++;
            $judge = $judgeDataItem['judge'];
            $scores = $judgeDataItem['scores'];
            ?>
            
            <div class="judge-section">
                <!-- Header on each page -->
                <div class="header">
                    <h1><?= htmlspecialchars($round['event_name']) ?></h1>
                    <p><strong><?= htmlspecialchars($round['name']) ?></strong></p>
                    <!-- <p><strong><?= htmlspecialchars($round['level_name']) ?></strong></p> -->
                </div>
                
                <div class="judge-header">
                    <h2>Judge #<?= htmlspecialchars($judge['judge_number'] ?: '-') ?> - <?= htmlspecialchars($judge['judge_name']) ?></h2>
                    <p>
                        <strong>Total Contestants Scored:</strong> <?= count($scores) ?>
                    </p>
                    <p style="margin-top: 6px;">
                        <!-- <small><strong>Note:</strong> This printout includes both <strong>Draft</strong> and <strong>Submitted</strong> scores.</small> -->
                    </p>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th style="width: 10%;">Contestant<br>Number</th>
                            <?php foreach ($criteria as $criterion): ?>
                                <th style="width: <?= 90 / count($criteria) ?>%;">
                                    <?= htmlspecialchars($criterion['name']) ?><br>
                                    <small>(Max: <?= number_format($criterion['max_score'], 3) ?>)</small>
                                </th>
                            <?php endforeach; ?>
                            <!-- <th style="width: 8%;">Status</th> -->
                             <th style="width: 100px;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($scores as $score): ?>
                            <tr>
                                <td style="font-weight: bold;">
                                    #<?= htmlspecialchars($score['contestant_number']) ?>
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
                                <!-- <td>
                                    <?= !empty($score['is_submitted']) ? 'Submitted' : 'Draft' ?>
                                </td> -->
                                <td>
                                    <?= number_format($score['total_score'], 3) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <!-- Footer on each page -->
                <div class="footer">
                    <p>Generated by TriAccess Group Tabulation System | <?= date('Y-m-d H:i:s') ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <script>
        // Auto-print when page loads (optional)
        // window.onload = function() {
        //     window.print();
        // };
    </script>
</body>
</html>

