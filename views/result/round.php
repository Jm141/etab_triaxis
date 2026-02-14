<?php 
$title = 'Round Results';
require __DIR__ . '/../layout/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-trophy"></i> Results: <?= htmlspecialchars($round['name']) ?></h1>
    <div>
        <a href="/tabulation/events/<?= $round['event_id'] ?>/results/round/<?= $round['id'] ?>/summary" 
           class="btn btn-info">
            <i class="fas fa-file-alt"></i> Summary Report
        </a>
        <a href="/tabulation/events/<?= $round['event_id'] ?>/results" class="btn btn-secondary">Back</a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-trophy"></i> Final Rankings</h5>
    </div>
    <div class="card-body">
        <?php if (empty($rankings)): ?>
            <p class="text-muted">No rankings calculated yet. Rankings will be calculated automatically once all judges have submitted their scores.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>#</th>
                            <th>Name</th>
                            <th>Team</th>
                            <th>Total Score</th>
                            <th>Average</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rankings as $ranking): ?>
                        <tr>
                            <td>
                                <h4 class="mb-0">
                                    <span class="badge bg-<?= $ranking['rank'] <= 3 ? 'warning' : 'secondary' ?>">
                                        <?= $ranking['rank'] ?>
                                    </span>
                                </h4>
                            </td>
                            <td>#<?= htmlspecialchars($ranking['contestant_number']) ?></td>
                            <td><strong><?= htmlspecialchars($ranking['name']) ?></strong></td>
                            <td><?= htmlspecialchars($ranking['team_name'] ?: '-') ?></td>
                            <td><strong><?= number_format($ranking['total_score'], 3) ?></strong></td>
                            <td><?= number_format($ranking['average_score'], 3) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($judgeBreakdown)): ?>
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-people"></i> Judge Breakdown</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead>
                    <tr>
                        <th>Contestant</th>
                        <th>Judge</th>
                        <th>Score</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($judgeBreakdown as $breakdown): ?>
                    <tr>
                        <td>#<?= htmlspecialchars($breakdown['contestant_number']) ?> - <?= htmlspecialchars($breakdown['name']) ?></td>
                        <td><?= htmlspecialchars($breakdown['judge_name']) ?></td>
                        <td><strong><?= number_format($breakdown['raw_total'] ?? $breakdown['total_score'], 3) ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>



