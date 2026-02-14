<?php 
$title = 'Summary Report - ' . $round['name'];
require __DIR__ . '/../layout/header.php'; 
?>

<!-- Print-only title -->
<div class="print-only" style="display: none; text-align: center; margin-bottom: 16px;">
    <div style="font-size: 1.5em; font-weight: bold;">SUMMARY TABULATION SHEET</div>
</div>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-trophy"></i> Summary Report: <?= htmlspecialchars($round['name']) ?></h1>
    <div>
        <button onclick="window.print()" class="btn btn-info">
            <i class="fas fa-print"></i> Print Report
        </button>
        <a href="/tabulation/events/<?= $round['event_id'] ?>/results/round/<?= $round['id'] ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <h5><?= htmlspecialchars($round['event_name']) ?> - <?= htmlspecialchars($round['level_name']) ?></h5>
        <p class="text-muted mb-0">
            <strong>Round:</strong> <?= htmlspecialchars($round['name']) ?>
            <?php if ($round['description']): ?>
                <br><strong>Description:</strong> <?= htmlspecialchars($round['description']) ?>
            <?php endif; ?>
        </p>
        <p class="text-muted mb-0 mt-2 small"><strong>Summary:</strong> Judge columns and Total (average) scores.</p>
    </div>
</div>

<?php if (empty($rankings)): ?>
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i> No rankings calculated yet. Rankings will be calculated automatically once all judges have submitted their scores.
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-trophy"></i> Winners List (Ranked by Position)</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th class="text-center" style="width: 80px;">Rank</th>
                            <th style="width: 100px;">Contestant #</th>
                            <th>Name</th>
                            <?php foreach ($judges as $judge): ?>
                                <th class="text-center" style="min-width: 120px;">
                                    Judge #<?= htmlspecialchars($judge['judge_number']) ?>
                                    <br>
                                    <small class="text-muted"><?= htmlspecialchars($judge['judge_name']) ?></small>
                                </th>
                            <?php endforeach; ?>
                            <th class="text-center" style="width: 120px;">
                                <strong>Total</strong>
                                <br>
                                <small class="text-muted">(Average Score)</small>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rankings as $ranking): 
                            $contestantId = $ranking['contestant_id'];
                            $scores = $contestantScores[$contestantId]['judge_scores'] ?? [];
                        ?>
                        <tr class="<?= $ranking['rank'] <= 3 ? 'table-warning' : '' ?>">
                            <td class="text-center align-middle">
                                <?php if ($ranking['rank'] == 1): ?>
                                    <h3 class="mb-0">
                                        <span class="badge bg-warning text-dark">
                                            <i class="fas fa-trophy"></i> 1st
                                        </span>
                                    </h3>
                                <?php elseif ($ranking['rank'] == 2): ?>
                                    <h4 class="mb-0">
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-medal"></i> 2nd
                                        </span>
                                    </h4>
                                <?php elseif ($ranking['rank'] == 3): ?>
                                    <h4 class="mb-0">
                                        <span class="badge" style="background-color: #CD7F32; color: white;">
                                            <i class="fas fa-medal"></i> 3rd
                                        </span>
                                    </h4>
                                <?php else: ?>
                                    <h5 class="mb-0"><?= $ranking['rank'] ?></h5>
                                <?php endif; ?>
                            </td>
                            <td class="align-middle">
                                <strong>#<?= htmlspecialchars($ranking['contestant_number']) ?></strong>
                            </td>
                            <td class="align-middle">
                                <strong><?= htmlspecialchars($ranking['name']) ?></strong>
                            </td>
                            <?php foreach ($judges as $judge): 
                                $judgeScore = $scores[$judge['id']]['score'] ?? null;
                            ?>
                                <td class="text-center align-middle">
                                    <?php if ($judgeScore !== null): ?>
                                        <strong><?= number_format($judgeScore, 3) ?></strong>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                            <td class="text-center align-middle">
                                <strong class="text-primary"><?= number_format($ranking['raw_average'] ?? $ranking['average_score'], 3) ?></strong>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-info-circle"></i> Report Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Total Contestants:</strong> <?= count($rankings) ?></p>
                    <p><strong>Total Judges:</strong> <?= count($judges) ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Report Generated:</strong> <?= date('F d, Y h:i A') ?></p>
                    <p><strong>Generated By:</strong> <?= htmlspecialchars(Session::get('full_name')) ?></p>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<style>
@media print {
    .btn, .card-header .btn, .no-print {
        display: none !important;
    }
    .print-only {
        display: block !important;
    }
    .card {
        border: 1px solid #ddd;
        page-break-inside: avoid;
    }
    .table {
        font-size: 12px;
    }
    /* Highlight top 3 in print - ensure background prints */
    .table tr.table-warning,
    .table tr.top-three-print {
        background-color: #fff3cd !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    .table tr.table-warning td,
    .table tr.top-three-print td {
        background-color: #fff3cd !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    .print-summary-note {
        display: block !important;
        margin-top: 8px;
        font-size: 11px;
        color: #333;
    }
}
.print-summary-note {
    display: none;
}
</style>
<p class="print-summary-note"><strong>Summary Report:</strong> Judge scores and Total (average).</p>

<?php require __DIR__ . '/../layout/footer.php'; ?>
