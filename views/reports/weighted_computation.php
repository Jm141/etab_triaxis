<?php 
$title = 'Weighted Criteria Computation - ' . $round['name'];
require __DIR__ . '/../layout/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-calculator"></i> Weighted Criteria Computation Report</h1>
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

<?php if (empty($criteria)): ?>
    <div class="alert alert-warning">No criteria configured for this round.</div>
<?php else: ?>
    <div class="card mb-4">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0">Weight Calculation Method</h5>
        </div>
        <div class="card-body">
            <p><strong>Formula:</strong> Weight = (Criterion Max Score / Total Max Score) × 100</p>
            <p><strong>Total Max Score:</strong> <?= number_format($totalMaxScore, 2) ?></p>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Criteria Weights</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Criterion</th>
                            <th class="text-center">Max Score</th>
                            <th class="text-center">Calculated Weight</th>
                            <th class="text-center">Stored Weight</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($criteria as $criterion): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($criterion['name']) ?></strong>
                                <?php if ($criterion['description']): ?>
                                    <br><small class="text-muted"><?= htmlspecialchars($criterion['description']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="text-center"><?= number_format($criterion['max_score'], 2) ?></td>
                            <td class="text-center">
                                <strong><?= number_format($criterion['calculated_weight'], 2) ?>%</strong>
                            </td>
                            <td class="text-center">
                                <?= number_format($criterion['weight'], 2) ?>%
                            </td>
                            <td class="text-center">
                                <?php if (abs($criterion['calculated_weight'] - $criterion['weight']) < 0.01): ?>
                                    <span class="badge bg-success"><i class="fas fa-check"></i> Match</span>
                                <?php else: ?>
                                    <span class="badge bg-warning"><i class="fas fa-exclamation"></i> Different</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="thead-light">
                        <tr>
                            <th>Total</th>
                            <th class="text-center"><?= number_format($totalMaxScore, 2) ?></th>
                            <th class="text-center">
                                <strong><?= number_format(array_sum(array_column($criteria, 'calculated_weight')), 2) ?>%</strong>
                            </th>
                            <th class="text-center">
                                <?= number_format(array_sum(array_column($criteria, 'weight')), 2) ?>%
                            </th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    
    <div class="card mt-4">
        <div class="card-body">
            <h6><i class="fas fa-info-circle"></i> How Weights Are Applied</h6>
            <p class="mb-0">
                Weights are automatically calculated based on each criterion's maximum score proportionally.
                This ensures fair scoring without manual weight configuration.
            </p>
        </div>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
