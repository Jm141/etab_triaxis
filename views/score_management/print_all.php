<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Scores - <?= htmlspecialchars($event['name']) ?></title>
    <style>
        @media print {
            .no-print { display: none !important; }
            @page { size: A4 landscape; margin: 0.8cm; }
            body { margin: 0; padding: 0; font-size: 10.5pt; }
            .round-section { page-break-after: always; page-break-inside: avoid; }
            .round-section:last-child { page-break-after: auto; }
            .judge-section { page-break-inside: avoid; }
            table { page-break-inside: avoid; }
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10.5pt;
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

        .header h1 { margin: 0; font-size: 18pt; font-weight: bold; }
        .header p { margin: 6px 0; font-size: 11pt; }

        .round-header {
            background: #f0f0f0;
            padding: 10px 12px;
            margin: 18px 0 12px 0;
            border: 1px solid #000;
        }

        .round-header h2 { margin: 0; font-size: 15pt; font-weight: bold; }
        .round-header p { margin: 4px 0; font-size: 10.5pt; }

        .judge-header {
            background: #fafafa;
            padding: 8px 10px;
            margin: 10px 0;
            border: 1px solid #000;
        }

        .judge-header h3 { margin: 0; font-size: 13pt; font-weight: bold; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 10.5pt;
        }

        table th {
            background: #e0e0e0;
            border: 1px solid #000;
            padding: 8px 10px;
            text-align: center;
            font-weight: bold;
            font-size: 10pt;
        }

        table td {
            border: 1px solid #000;
            padding: 8px 10px;
            text-align: center;
            font-size: 10.5pt;
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
            Print
        </button>
        <button onclick="window.close()" style="background: #6c757d; color: white; border: none; border-radius: 4px;">
            Close
        </button>
    </div>

    <div class="header">
        <h1><?= htmlspecialchars($event['name']) ?></h1>
        <p><strong>All Rounds Score Printout</strong></p>
        <!-- <p><small>Includes both <strong>Draft</strong> and <strong>Submitted</strong> scores | Generated: <?= date('Y-m-d H:i:s') ?></small></p> -->
    </div>

    <?php if (empty($roundData)): ?>
        <p style="text-align:center; font-style: italic; color:#666;">No rounds found for this event.</p>
    <?php else: ?>
        <?php foreach ($roundData as $roundItem): ?>
            <?php
                $round = $roundItem['round'];
                $criteria = $roundItem['criteria'] ?? [];
                $judgeData = $roundItem['judgeData'] ?? [];
            ?>
            <div class="round-section">
                <div class="round-header">
                    <h2><?= htmlspecialchars($round['name']) ?></h2>
                    <p>
                        <strong>Level:</strong> <?= htmlspecialchars($round['level_name'] ?? '') ?> |
                        <strong>Total Criteria:</strong> <?= count($criteria) ?> |
                        <strong>Judges with scores:</strong> <?= count($judgeData) ?>
                    </p>
                </div>

                <?php if (empty($judgeData)): ?>
                    <p style="text-align:center; font-style: italic; color:#666;">No scores recorded for this round.</p>
                <?php else: ?>
                    <?php foreach ($judgeData as $judgeBlock): ?>
                        <?php
                            $judge = $judgeBlock['judge'];
                            $scores = $judgeBlock['scores'] ?? [];
                        ?>
                        <div class="judge-section">
                            <div class="judge-header">
                                <h3>Judge #<?= htmlspecialchars($judge['judge_number'] ?? '-') ?> - <?= htmlspecialchars($judge['judge_name'] ?? '') ?></h3>
                                <p style="margin:4px 0 0 0;"><small><strong>Total Contestants Scored:</strong> <?= count($scores) ?></small></p>
                            </div>

                            <table>
                                <thead>
                                    <tr>
                                        <th style="width: 10%;">Contestant<br>Number</th>
                                        <?php foreach ($criteria as $criterion): ?>
                                            <th style="width: <?= count($criteria) > 0 ? (82 / count($criteria)) : 82 ?>%;">
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
                                            <td style="font-weight:bold;">#<?= htmlspecialchars($score['contestant_number']) ?></td>
                                            <?php
                                                $detailsMap = [];
                                                foreach (($score['details'] ?? []) as $detail) {
                                                    $detailsMap[$detail['criteria_id']] = $detail;
                                                }
                                            ?>
                                            <?php foreach ($criteria as $criterion): ?>
                                                <td>
                                                    <?php
                                                        $detail = $detailsMap[$criterion['id']] ?? null;
                                                        echo $detail ? number_format($detail['raw_score'], 3) : '-';
                                                    ?>
                                                </td>
                                            <?php endforeach; ?>
                                            <!-- <td><?= !empty($score['is_submitted']) ? 'Submitted' : 'Draft' ?></td> -->
                                            <td><?= number_format($score['total_score'], 3) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <div class="footer">
                    <p>Generated by Tabulation System | <?= date('Y-m-d H:i:s') ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>

