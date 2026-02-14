<?php 
$title = 'Overall Ranking Report - ' . $round['name'];
require __DIR__ . '/../layout/header.php'; 
require __DIR__ . '/_print_styles.php';
require_once __DIR__ . '/../../core/ScoreFormatter.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <h1><i class="fas fa-trophy"></i> Overall Ranking / Final Results Report</h1>
    <div>
        <button onclick="window.print()" class="btn btn-info">
            <i class="fas fa-print"></i> Print
        </button>
        <a href="/tabulation/events/<?= $event['id'] ?>/reports" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<!-- Standardized Report Header -->
<div class="report-header">
    <h2><?= htmlspecialchars($event['name']) ?></h2>
    <div class="report-info">
        <strong>Level:</strong> <?= htmlspecialchars($round['level_name']) ?><br>
        <strong>Round:</strong> <?= htmlspecialchars($round['name']) ?><br>
        <?php if (!empty($event['venue'])): ?>
            <strong>Venue:</strong> <?= htmlspecialchars($event['venue']) ?><br>
        <?php endif; ?>
        <strong>Date:</strong> <?= !empty($event['event_date']) ? date('F d, Y', strtotime($event['event_date'])) : date('F d, Y') ?>
    </div>
</div>

<?php if (empty($contestantScores)): ?>
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i> No rankings available yet.
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-medal"></i> Final Rankings</h5>
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
                                    <br><small><?= htmlspecialchars($judge['judge_name']) ?></small>
                                </th>
                            <?php endforeach; ?>
                            <th class="text-center" style="width: 120px;">
                                <strong>Total</strong>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contestantScores as $cs): 
                            $ranking = $cs['contestant'];
                        ?>
                        <tr class="<?= $ranking['rank'] <= 3 ? 'table-warning' : '' ?>">
                            <td class="text-center align-middle">
                                <?php if ($ranking['rank'] == 1): ?>
                                    <h3 class="mb-0">
                                        <span class="badge bg-warning text-dark">
                                            <i class="fas fa-trophy"></i> 1
                                        </span>
                                    </h3>
                                <?php elseif ($ranking['rank'] == 2): ?>
                                    <h4 class="mb-0">
                                        <span class="badge bg-secondary">2</span>
                                    </h4>
                                <?php elseif ($ranking['rank'] == 3): ?>
                                    <h4 class="mb-0">
                                        <span class="badge" style="background-color: #CD7F32; color: white;">3</span>
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
                                $judgeScore = $cs['judge_scores'][$judge['id']] ?? 0;
                            ?>
                                <td class="text-center align-middle">
                                    <strong><?= ScoreFormatter::format($judgeScore, 2) ?></strong>
                                </td>
                            <?php endforeach; ?>
                            <td class="text-center align-middle">
                                <strong class="text-success" style="font-size: 1.2em;">
                                    <?= ScoreFormatter::format($cs['grand_total'], 2) ?>
                                </strong>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<style>
@media print {
    .btn, .card-header .btn {
        display: none;
    }
    .card {
        border: 1px solid #ddd;
        page-break-inside: avoid;
    }
    .table {
        font-size: 11px;
    }
}
</style>

<?php require __DIR__ . '/../layout/footer.php'; ?>
