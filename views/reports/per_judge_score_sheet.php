<?php 
$title = 'Per-Judge Score Sheet - ' . $round['name'];
require __DIR__ . '/../layout/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-user-tie"></i> Per-Judge Score Sheet</h1>
    <div>
        <button onclick="window.print()" class="btn btn-info">
            <i class="fas fa-print"></i> Print
        </button>
        <a href="/tabulation/events/<?= $event['id'] ?>/reports" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <h5><?= htmlspecialchars($event['name']) ?></h5>
        <p class="mb-0">
            <strong>Level:</strong> <?= htmlspecialchars($round['level_name']) ?><br>
            <strong>Round:</strong> <?= htmlspecialchars($round['name']) ?>
        </p>
    </div>
</div>

<?php if (empty($judgeScores)): ?>
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i> No scores submitted yet.
    </div>
<?php else: ?>
    <?php foreach ($judgeScores as $judgeData): ?>
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-user-tie"></i> Judge #<?= htmlspecialchars($judgeData['judge']['judge_number']) ?> - 
                <?= htmlspecialchars($judgeData['judge']['judge_name']) ?>
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 80px;">Contestant #</th>
                            <th>Name</th>
                            <?php foreach ($criteria as $criterion): ?>
                                <th class="text-center" style="min-width: 120px;">
                                    <?= htmlspecialchars($criterion['name']) ?>
                                    <br><small class="text-muted">Max: <?= number_format($criterion['max_score'], 2) ?></small>
                                </th>
                            <?php endforeach; ?>
                            <th class="text-center" style="width: 120px;">
                                <strong>Total Score</strong>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($judgeData['scores'] as $score): ?>
                        <tr>
                            <td>
                                <strong>#<?= htmlspecialchars($score['contestant_number']) ?></strong>
                            </td>
                            <td>
                                <?= htmlspecialchars($score['name'] ?? '-') ?>
                            </td>
                            <?php 
                            $detailsMap = [];
                            foreach ($score['details'] as $detail) {
                                $detailsMap[$detail['criteria_id']] = $detail;
                            }
                            
                            foreach ($criteria as $criterion): 
                                $detail = $detailsMap[$criterion['id']] ?? null;
                            ?>
                                <td class="text-center">
                                    <?php if ($detail): ?>
                                        <strong><?= number_format($detail['raw_score'], 3) ?></strong>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                            <td class="text-center">
                                <strong class="text-primary"><?= number_format($score['total_score'], 3) ?></strong>
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
    .btn, .card-header .btn {
        display: none;
    }
    .card {
        border: 1px solid #ddd;
        page-break-inside: avoid;
        margin-bottom: 20px;
    }
}
</style>

<?php require __DIR__ . '/../layout/footer.php'; ?>
