<?php 
$title = 'Criteria Breakdown Report - ' . $round['name'];
require __DIR__ . '/../layout/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-chart-bar"></i> Criteria Breakdown Report</h1>
    <div>
        <button onclick="window.print()" class="btn btn-info">
            <i class="fas fa-print"></i> Print
        </button>
        <a href="/tabulation/events/<?= $event['id'] ?>/reports" class="btn btn-secondary">Back</a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <h5><?= htmlspecialchars($event['name']) ?> - <?= htmlspecialchars($round['level_name']) ?></h5>
        <p class="mb-0"><strong>Round:</strong> <?= htmlspecialchars($round['name']) ?></p>
    </div>
</div>

<?php if (empty($contestantCriteriaScores)): ?>
    <div class="alert alert-warning">No scores available yet.</div>
<?php else: ?>
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Scores Per Criterion</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Rank</th>
                            <th>Contestant #</th>
                            <th>Name</th>
                            <?php foreach ($criteria as $criterion): ?>
                                <th class="text-center">
                                    <?= htmlspecialchars($criterion['name']) ?>
                                    <br><small>Max: <?= number_format($criterion['max_score'], 2) ?></small>
                                </th>
                            <?php endforeach; ?>
                            <th class="text-center"><strong>Total</strong></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contestantCriteriaScores as $cs): ?>
                        <tr>
                            <td><?= $cs['contestant']['rank'] ?></td>
                            <td><strong>#<?= htmlspecialchars($cs['contestant']['contestant_number']) ?></strong></td>
                            <td><?= htmlspecialchars($cs['contestant']['name']) ?></td>
                            <?php foreach ($criteria as $criterion): 
                                $criteriaData = $cs['criteria_scores'][$criterion['id']] ?? null;
                            ?>
                                <td class="text-center">
                                    <?php if ($criteriaData): ?>
                                        <strong><?= number_format($criteriaData['average'], 3) ?></strong>
                                        <br><small class="text-muted">(<?= number_format($criteriaData['total'], 3) ?>)</small>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                            <td class="text-center">
                                <strong><?= number_format($cs['contestant']['total_score'], 3) ?></strong>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
