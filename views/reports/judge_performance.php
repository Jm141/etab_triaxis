<?php 
$title = 'Judge Performance Report - ' . $round['name'];
require __DIR__ . '/../layout/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-chart-line"></i> Judge Performance Summary</h1>
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
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">Judge Statistics</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Judge #</th>
                            <th>Judge Name</th>
                            <th class="text-center">Total Scores</th>
                            <th class="text-center">Average Score</th>
                            <th class="text-center">Min Score</th>
                            <th class="text-center">Max Score</th>
                            <th class="text-center">Std Deviation</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($judges as $judge): ?>
                        <tr>
                            <td><strong>#<?= htmlspecialchars($judge['judge_number']) ?></strong></td>
                            <td><?= htmlspecialchars($judge['judge_name']) ?></td>
                            <td class="text-center"><?= $judge['total_scores'] ?></td>
                            <td class="text-center">
                                <strong><?= $judge['avg_score'] ? number_format($judge['avg_score'], 3) : '-' ?></strong>
                            </td>
                            <td class="text-center">
                                <?= $judge['min_score'] !== null ? number_format($judge['min_score'], 3) : '-' ?>
                            </td>
                            <td class="text-center">
                                <?= $judge['max_score'] !== null ? number_format($judge['max_score'], 3) : '-' ?>
                            </td>
                            <td class="text-center">
                                <?= $judge['std_dev'] !== null ? number_format($judge['std_dev'], 3) : '-' ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="card mt-4">
        <div class="card-body">
            <h6><i class="fas fa-info-circle"></i> Performance Analysis</h6>
            <p class="mb-0">
                <strong>Average Score:</strong> Shows the judge's average scoring tendency<br>
                <strong>Standard Deviation:</strong> Lower values indicate more consistent scoring<br>
                <strong>Min/Max:</strong> Shows the range of scores given by this judge
            </p>
        </div>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
