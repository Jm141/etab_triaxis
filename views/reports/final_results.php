<?php 
$title = 'Final Results';
require __DIR__ . '/../layout/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-trophy"></i> Final Results</h1>
    <div>
        <button onclick="window.print()" class="btn btn-info">
            <i class="fas fa-print"></i> Print
        </button>
        <a href="/tabulation/events/<?= $event['id'] ?>/reports" class="btn btn-secondary">Back</a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <h5><?= htmlspecialchars($event['name']) ?></h5>
        <p class="mb-0"><strong>Level:</strong> <?= htmlspecialchars($level['name']) ?></p>
    </div>
</div>

<?php if (empty($rankings)): ?>
    <div class="alert alert-warning">Final round not completed yet.</div>
<?php else: ?>
    <div class="card">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0"><i class="fas fa-trophy"></i> Final Rankings</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th class="text-center">Rank</th>
                            <th>Contestant #</th>
                            <th>Name</th>
                            <th class="text-center">Total Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rankings as $idx => $ranking): 
                            $rank = $idx + 1;
                        ?>
                        <tr class="<?= $rank <= 3 ? 'table-warning' : '' ?>">
                            <td class="text-center">
                                <?php if ($rank == 1): ?>
                                    <h3 class="mb-0"><span class="badge bg-warning text-dark"><i class="fas fa-trophy"></i> Winner</span></h3>
                                <?php elseif ($rank == 2): ?>
                                    <h4 class="mb-0"><span class="badge bg-secondary">1st Runner-up</span></h4>
                                <?php elseif ($rank == 3): ?>
                                    <h4 class="mb-0"><span class="badge" style="background-color: #CD7F32; color: white;">2nd Runner-up</span></h4>
                                <?php else: ?>
                                    <h5 class="mb-0"><?= $rank ?></h5>
                                <?php endif; ?>
                            </td>
                            <td><strong>#<?= htmlspecialchars($ranking['contestant_number'] ?? '-') ?></strong></td>
                            <td><strong><?= htmlspecialchars($ranking['name'] ?? '-') ?></strong></td>
                            <td class="text-center"><strong><?= number_format($ranking['average'] ?? 0, 3) ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
