<?php 
$title = 'Elimination Summary - ' . $level['name'];
require __DIR__ . '/../layout/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-filter"></i> Elimination Summary: <?= htmlspecialchars($level['name']) ?></h1>
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
        <p class="mb-0">
            <strong>Level:</strong> <?= htmlspecialchars($level['name']) ?><br>
            <?php if ($nextLevel): ?>
                <strong>Next Level:</strong> <?= htmlspecialchars($nextLevel['name']) ?><br>
                <strong>Advance Count:</strong> <?= $level['advance_count'] ?: 'All' ?>
            <?php else: ?>
                <strong>Status:</strong> Final Level (No next level)
            <?php endif; ?>
        </p>
    </div>
</div>

<?php if (!$nextLevel): ?>
    <div class="alert alert-info">
        This is the final level. No elimination occurs.
    </div>
<?php elseif (empty($qualified)): ?>
    <div class="alert alert-warning">
        No contestants have been qualified yet. Level may not be complete.
    </div>
<?php else: ?>
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-check-circle"></i> Qualified (<?= count($qualified) ?>)
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Rank</th>
                                    <th>Contestant #</th>
                                    <th>Name</th>
                                    <th class="text-center">Score</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $qualifiedRankings = array_slice($levelRankings, 0, count($qualified));
                                foreach ($qualifiedRankings as $idx => $ranking): 
                                ?>
                                <tr class="table-success">
                                    <td><?= $idx + 1 ?></td>
                                    <td><strong>#<?= htmlspecialchars($ranking['contestant_number'] ?? '-') ?></strong></td>
                                    <td><?= htmlspecialchars($ranking['name'] ?? '-') ?></td>
                                    <td class="text-center"><strong><?= number_format($ranking['average'] ?? 0, 3) ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-times-circle"></i> Eliminated (<?= count($eliminated) ?>)
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Rank</th>
                                    <th>Contestant #</th>
                                    <th>Name</th>
                                    <th class="text-center">Score</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $eliminatedRankings = array_slice($levelRankings, count($qualified));
                                foreach ($eliminatedRankings as $idx => $ranking): 
                                ?>
                                <tr class="table-danger">
                                    <td><?= count($qualified) + $idx + 1 ?></td>
                                    <td><strong>#<?= htmlspecialchars($ranking['contestant_number'] ?? '-') ?></strong></td>
                                    <td><?= htmlspecialchars($ranking['name'] ?? '-') ?></td>
                                    <td class="text-center"><?= number_format($ranking['average'] ?? 0, 3) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php if ($cutoffScore !== null): ?>
    <div class="card">
        <div class="card-body">
            <h6><i class="fas fa-info-circle"></i> Cutoff Information</h6>
            <p class="mb-0">
                <strong>Cutoff Score:</strong> <?= number_format($cutoffScore, 3) ?><br>
                <strong>Contestants above this score qualified for next level.</strong>
            </p>
        </div>
    </div>
    <?php endif; ?>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
