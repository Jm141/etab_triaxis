<?php 
$title = 'Judge Report - ' . $judge['judge_name'];
require __DIR__ . '/../layout/header.php'; 
require __DIR__ . '/_print_styles.php';
require_once __DIR__ . '/../../core/ScoreFormatter.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <h1><i class="fas fa-user-tie"></i> Report Per Judge</h1>
    <div>
        <button onclick="window.print()" class="btn btn-info">
            <i class="fas fa-print"></i> Print
        </button>
        <a href="/tabulation/events/<?= $event['id'] ?>/reports" class="btn btn-secondary">Back</a>
    </div>
</div>

<!-- Standardized Report Header -->
<div class="report-header">
    <h2><?= htmlspecialchars($event['name']) ?></h2>
    <div class="report-info">
        <strong>Judge:</strong> Judge #<?= htmlspecialchars($judge['judge_number']) ?> - <?= htmlspecialchars($judge['judge_name']) ?><br>
        <?php if (!empty($event['venue'])): ?>
            <strong>Venue:</strong> <?= htmlspecialchars($event['venue']) ?><br>
        <?php endif; ?>
        <strong>Date:</strong> <?= !empty($event['event_date']) ? date('F d, Y', strtotime($event['event_date'])) : date('F d, Y') ?>
    </div>
</div>

<?php if (empty($roundScores)): ?>
    <div class="alert alert-warning">No scores submitted by this judge yet.</div>
<?php else: ?>
    <?php 
    $roundCount = 0;
    foreach ($roundScores as $roundData): 
        $roundCount++;
    ?>
    <div class="card mb-4 <?= $roundCount > 1 ? 'page-break' : '' ?>">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">
                <?= htmlspecialchars($roundData['round']['name']) ?>
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th class="text-center" style="width: 100px;">Contestant #</th>
                            <th style="min-width: 150px;">Name</th>
                            <?php if (!empty($roundData['criteria'])): ?>
                                <?php foreach ($roundData['criteria'] as $criterion): ?>
                                    <th class="text-center" style="min-width: 120px;">
                                        <div>
                                            <strong><?= htmlspecialchars($criterion['name']) ?></strong>
                                            <br>
                                            <small class="text-muted">Max: <?= number_format($criterion['max_score'], 2) ?></small>
                                        </div>
                                    </th>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <th class="text-center" style="width: 120px;"><strong>Total Score</strong></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($roundData['scores'] as $score): ?>
                        <tr>
                            <td class="text-center">
                                <strong>#<?= htmlspecialchars($score['contestant_number']) ?></strong>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($score['name']) ?></strong>
                            </td>
                            <?php if (!empty($roundData['criteria'])): ?>
                                <?php 
                                // Create a map of criteria_id => score_detail for easy lookup
                                $detailsMap = [];
                                foreach ($score['details'] as $detail) {
                                    $detailsMap[$detail['criteria_id']] = $detail;
                                }
                                ?>
                                <?php foreach ($roundData['criteria'] as $criterion): ?>
                                    <?php $detail = $detailsMap[$criterion['id']] ?? null; ?>
                                    <td class="text-center">
                                        <?php if ($detail): ?>
                                            <strong><?= ScoreFormatter::format($detail['raw_score'], 2) ?></strong>
                                            <br>
                                            <small class="text-muted">
                                                (<?= ScoreFormatter::format($detail['weighted_score'], 2) ?>)
                                            </small>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <td class="text-center">
                                <strong><?= ScoreFormatter::format($score['total_score'], 2) ?></strong>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

<style>
@media print {
    /* One judge per page - ensure each round starts on new page after first */
    .card.mb-4.page-break {
        page-break-before: always;
        margin-top: 20px;
    }
    
    /* Ensure table fits on page */
    .table {
        font-size: 8pt;
    }
    
    .table th, .table td {
        padding: 4px;
    }
}
</style>

<?php require __DIR__ . '/../layout/footer.php'; ?>
