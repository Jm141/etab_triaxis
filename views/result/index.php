<?php 
$title = 'Results';
require __DIR__ . '/../layout/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-trophy"></i> Results: <?= htmlspecialchars($event['name']) ?></h1>
    <div>
        <?php if (in_array(Session::get('role_name'), ['Super Admin', 'Event Technical Admin'])): ?>
        <a href="/tabulation/events/<?= $event['id'] ?>/reports" class="btn btn-info">
            <i class="fas fa-file-alt"></i> Reports
        </a>
        <?php endif; ?>
        <?php if (in_array(Session::get('role_name'), ['Super Admin', 'Event Admin', 'Event Technical Admin', 'Tabulator'])): ?>
        <a href="/tabulation/score-management/print/<?= $event['id'] ?>" 
           class="btn btn-info" target="_blank">
            <i class="fas fa-print"></i> Print Scores
        </a>
        <?php endif; ?>
        <a href="/tabulation/events/<?= $event['id'] ?>" class="btn btn-secondary">Back</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Level</th>
                        <th>Round</th>
                        <th>Rankings</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rounds as $round): ?>
                    <tr>
                        <td><?= htmlspecialchars($round['level_name']) ?></td>
                        <td><strong><?= htmlspecialchars($round['name']) ?></strong></td>
                        <td>
                            <?php if ($round['ranking_count'] > 0): ?>
                                <span class="badge bg-success"><?= $round['ranking_count'] ?> Rankings</span>
                            <?php else: ?>
                                <span class="badge bg-warning">Not Calculated</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-<?= $round['status'] === 'Active' ? 'success' : 'secondary' ?>">
                                <?= htmlspecialchars($round['status']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="/tabulation/events/<?= $event['id'] ?>/results/round/<?= $round['id'] ?>" 
                                   class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <?php if ($round['ranking_count'] > 0): ?>
                                <a href="/tabulation/events/<?= $event['id'] ?>/results/round/<?= $round['id'] ?>/summary" 
                                   class="btn btn-sm btn-info">
                                    <i class="fas fa-file-alt"></i> Summary
                                </a>
                                <?php endif; ?>
                                <?php if (in_array(Session::get('role_name'), ['Super Admin', 'Event Admin', 'Event Technical Admin', 'Tabulator'])): ?>
                                <a href="/tabulation/score-management/round/<?= $round['id'] ?>" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i> Manage Scores
                                </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<?php require __DIR__ . '/../layout/footer.php'; ?>



