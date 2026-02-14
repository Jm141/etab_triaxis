<?php 
$title = 'Judge Attendance Report - ' . $round['name'];
require __DIR__ . '/../layout/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-user-check"></i> Judge Attendance & Submission Report</h1>
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

<?php if (empty($judges)): ?>
    <div class="alert alert-warning">No judges assigned to this round.</div>
<?php else: ?>
    <div class="card">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0">Judge Submission Status</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Judge #</th>
                            <th>Judge Name</th>
                            <th class="text-center">Submitted</th>
                            <th class="text-center">Total Expected</th>
                            <th class="text-center">Completion %</th>
                            <th class="text-center">Last Submission</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($judges as $judge): 
                            $completion = $judge['total_contestants'] > 0 
                                ? ($judge['submitted_count'] / $judge['total_contestants']) * 100 
                                : 0;
                            $isComplete = $judge['submitted_count'] >= $judge['total_contestants'];
                        ?>
                        <tr class="<?= $isComplete ? 'table-success' : 'table-warning' ?>">
                            <td><strong>#<?= htmlspecialchars($judge['judge_number']) ?></strong></td>
                            <td><?= htmlspecialchars($judge['judge_name']) ?></td>
                            <td class="text-center">
                                <strong><?= $judge['submitted_count'] ?></strong>
                            </td>
                            <td class="text-center"><?= $judge['total_contestants'] ?></td>
                            <td class="text-center">
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar <?= $isComplete ? 'bg-success' : 'bg-warning' ?>" 
                                         role="progressbar" 
                                         style="width: <?= $completion ?>%">
                                        <?= number_format($completion, 1) ?>%
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <?php if ($judge['last_submission']): ?>
                                    <small><?= date('M d, Y H:i', strtotime($judge['last_submission'])) ?></small>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($isComplete): ?>
                                    <span class="badge bg-success">
                                        <i class="fas fa-check"></i> Complete
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-warning">
                                        <i class="fas fa-clock"></i> Pending
                                    </span>
                                <?php endif; ?>
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
