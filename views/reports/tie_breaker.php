<?php 
$title = 'Tie-Breaker Report - ' . $round['name'];
require __DIR__ . '/../layout/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-equals"></i> Tie-Breaker Computation Report</h1>
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

<?php if (empty($ties)): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> No ties found. All contestants have unique scores.
    </div>
<?php else: ?>
    <?php foreach ($ties as $rank => $tiedContestants): ?>
    <div class="card mb-3">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0">Tie at Rank <?= $rank ?></h5>
        </div>
        <div class="card-body">
            <p><strong>Number of Tied Contestants:</strong> <?= count($tiedContestants) ?></p>
            <p><strong>Tie-Breaking Method:</strong> Average Score (Higher average wins)</p>
            
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>Contestant #</th>
                            <th>Name</th>
                            <th class="text-center">Average Score</th>
                            <th class="text-center">Total Score</th>
                            <th class="text-center">Final Rank</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tiedContestants as $contestant): ?>
                        <tr>
                            <td><strong>#<?= htmlspecialchars($contestant['contestant_number']) ?></strong></td>
                            <td><?= htmlspecialchars($contestant['name']) ?></td>
                            <td class="text-center"><strong><?= number_format($contestant['average_score'], 3) ?></strong></td>
                            <td class="text-center"><?= number_format($contestant['total_score'], 3) ?></td>
                            <td class="text-center">
                                <span class="badge bg-primary"><?= $contestant['rank'] ?></span>
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

<?php require __DIR__ . '/../layout/footer.php'; ?>
