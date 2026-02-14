<?php 
$title = 'Score Round: ' . $round['name'];
require __DIR__ . '/../layout/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-clipboard-check"></i> <?= htmlspecialchars($round['name']) ?></h1>
    <div>
        <a href="/tabulation/judge/rounds/<?= $round['id'] ?>/table" class="btn btn-primary">
            <i class="fas fa-table"></i> Table View
        </a>
        <a href="/tabulation/judge/rounds" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <h5><?= htmlspecialchars($round['event_name']) ?> - <?= htmlspecialchars($round['level_name']) ?></h5>
        <p class="text-muted mb-0"><?= htmlspecialchars($round['description'] ?: 'No description') ?></p>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-users"></i> Contestants</h5>
        <div class="card-tools">
            <a href="/tabulation/judge/rounds/<?= $round['id'] ?>/table" class="btn btn-sm btn-primary">
                <i class="fas fa-table"></i> Open Table View to Score
            </a>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($contestants)): ?>
            <p class="text-muted">No contestants registered for this event.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Team</th>
                            <th>Status</th>
                            <th>Score</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contestants as $contestant): ?>
                        <tr>
                            <td><?= htmlspecialchars($contestant['contestant_number']) ?></td>
                            <td><strong><?= htmlspecialchars($contestant['name']) ?></strong></td>
                            <td><?= htmlspecialchars($contestant['team_name'] ?: '-') ?></td>
                            <td>
                                <?php if (!empty($contestant['is_submitted'])): ?>
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle"></i> Submitted
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-warning">
                                        <i class="fas fa-clock"></i> Pending
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($contestant['total_score'])): ?>
                                    <strong><?= number_format($contestant['total_score'], 3) ?></strong>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="/tabulation/judge/rounds/<?= $round['id'] ?>/table" 
                                       class="btn btn-sm btn-primary">
                                        <i class="fas fa-table"></i> Score in Table
                                    </a>
                                    <?php if (!empty($contestant['is_submitted'])): ?>
                                        <span class="badge badge-success ml-2">
                                            <i class="fas fa-lock"></i> Locked
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>



