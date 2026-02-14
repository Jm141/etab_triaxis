<?php 
$title = 'Criteria Weights';
require __DIR__ . '/../layout/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-sliders-h"></i> Assign Criteria to Round: <?= htmlspecialchars($round['name']) ?></h1>
    <a href="/tabulation/levels/<?= $round['level_id'] ?>/rounds" class="btn btn-secondary">Back</a>
</div>

<div class="alert alert-info">
    <h5><i class="icon fas fa-info-circle"></i> Auto-Calculated Weights</h5>
    <p class="mb-0">
        <strong>Weights are automatically calculated</strong> based on each criterion's max score proportionally.
        For example, if criteria have max scores of 10, 15, and 5 (total 30), weights will be 33.33%, 50%, and 16.67% respectively.
        You only need to select which criteria to include - the system handles the rest!
    </p>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-plus-circle"></i> Assign Criteria to Round</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="/tabulation/rounds/<?= $round['id'] ?>/weights/store">
            <input type="hidden" name="csrf_token" value="<?= Session::getCSRFToken() ?>">
            <div class="row">
                <div class="col-md-10 mb-2">
                    <label for="criteria_id">Select Criteria</label>
                    <select class="form-control form-control-lg" name="criteria_id" id="criteria_id" required>
                        <option value="">-- Choose a Criterion --</option>
                        <?php foreach ($allCriteria as $criterion): ?>
                            <?php 
                            $assigned = false;
                            foreach ($weights as $w) {
                                if ($w['criteria_id'] == $criterion['id'] && $w['is_active']) {
                                    $assigned = true;
                                    break;
                                }
                            }
                            ?>
                            <?php if (!$assigned): ?>
                                <option value="<?= $criterion['id'] ?>">
                                    <?= htmlspecialchars($criterion['name']) ?> (Max Score: <?= $criterion['max_score'] ?>)
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-text text-muted">
                        Select a criterion to add to this round. Weight will be calculated automatically.
                    </small>
                </div>
                <div class="col-md-2 mb-2">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="fas fa-plus"></i> Add
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php 
        $totalWeight = 0;
        $totalMaxScore = 0;
        foreach ($weights as $w) {
            if ($w['is_active']) {
                $totalWeight += $w['weight'];
                $totalMaxScore += $w['max_score'];
            }
        }
        ?>
        
        <?php if (empty($weights)): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> 
                No criteria assigned yet. Add criteria above to get started.
            </div>
        <?php else: ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> 
                <strong>Total Max Score:</strong> <?= number_format($totalMaxScore, 2) ?> points | 
                <strong>Total Weight:</strong> <?= number_format($totalWeight, 2) ?>% 
                <?php if (abs($totalWeight - 100) < 0.01): ?>
                    ✓ Perfect!
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Criteria Name</th>
                        <th>Max Score</th>
                        <th>Auto-Calculated Weight</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($weights as $weight): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($weight['criteria_name']) ?></strong></td>
                        <td>
                            <span class="badge badge-primary badge-lg">
                                <?= number_format($weight['max_score'], 2) ?> pts
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-info badge-lg">
                                <?= number_format($weight['weight'], 2) ?>%
                            </span>
                            <small class="text-muted d-block">
                                (<?= number_format($weight['max_score'] / $totalMaxScore * 100, 2) ?>% of total)
                            </small>
                        </td>
                        <td>
                            <span class="badge badge-<?= $weight['is_active'] ? 'success' : 'secondary' ?>">
                                <?= $weight['is_active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                        <td>
                            <form method="POST" action="/tabulation/rounds/<?= $round['id'] ?>/weights/<?= $weight['id'] ?>/delete" 
                                  style="display: inline;" 
                                  onsubmit="confirmDelete(event, 'Weights will be recalculated automatically.', 'Remove Criterion?'); return false;">
                                <input type="hidden" name="csrf_token" value="<?= Session::getCSRFToken() ?>">
                                <button type="submit" class="btn btn-sm btn-danger" title="Remove Criterion">
                                    <i class="fas fa-trash"></i> Remove
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>



