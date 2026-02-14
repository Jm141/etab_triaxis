<?php 
$title = 'Candidate Summary - ' . $contestant['name'];
require __DIR__ . '/../layout/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-user"></i> Candidate Score Summary</h1>
    <div>
        <button onclick="window.print()" class="btn btn-info">
            <i class="fas fa-print"></i> Print
        </button>
        <a href="/tabulation/events/<?= $event['id'] ?>/reports" class="btn btn-secondary">Back</a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h5><?= htmlspecialchars($event['name']) ?></h5>
                <p class="mb-0">
                    <strong>Contestant #:</strong> <?= htmlspecialchars($contestant['contestant_number']) ?><br>
                    <strong>Name:</strong> <?= htmlspecialchars($contestant['name']) ?><br>
                    <strong>Status:</strong> 
                    <span class="badge bg-<?= $contestant['status'] === 'Active' ? 'success' : 'secondary' ?>">
                        <?= htmlspecialchars($contestant['status']) ?>
                    </span>
                </p>
            </div>
            <div class="col-md-6">
                <?php if ($finalRanking): ?>
                    <h5>Final Result</h5>
                    <p class="mb-0">
                        <strong>Final Rank:</strong> 
                        <span class="badge bg-<?= $finalRanking['rank'] <= 3 ? 'warning' : 'secondary' ?>" style="font-size: 1.2em;">
                            <?= $finalRanking['rank'] ?>
                        </span><br>
                        <strong>Final Average:</strong> <?= number_format($finalRanking['average_score'], 3) ?><br>
                        <strong>Final Total:</strong> <?= number_format($finalRanking['total_score'], 3) ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Scores Per Round</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Level</th>
                        <th>Round</th>
                        <th class="text-center">Rank</th>
                        <th class="text-center">Average Score</th>
                        <th class="text-center">Total Score</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rounds as $round): 
                        $roundScore = $roundScores[$round['id']] ?? null;
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($round['level_name']) ?></td>
                        <td><strong><?= htmlspecialchars($round['name']) ?></strong></td>
                        <td class="text-center">
                            <?php if ($roundScore && $roundScore['ranking']): ?>
                                <span class="badge bg-<?= $roundScore['ranking']['rank'] <= 3 ? 'warning' : 'secondary' ?>">
                                    <?= $roundScore['ranking']['rank'] ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($roundScore && $roundScore['ranking']): ?>
                                <strong><?= number_format($roundScore['ranking']['average_score'], 3) ?></strong>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($roundScore && $roundScore['ranking']): ?>
                                <strong><?= number_format($roundScore['ranking']['total_score'], 3) ?></strong>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
