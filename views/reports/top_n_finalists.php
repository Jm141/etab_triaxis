<?php 
$title = 'Top ' . $topN . ' Finalists - ' . $round['name'];
require __DIR__ . '/../layout/header.php'; 
require __DIR__ . '/_print_styles.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <h1><i class="fas fa-list-ol"></i> Top <?= $topN ?> Finalists</h1>
    <div>
        <button onclick="window.print()" class="btn btn-info">
            <i class="fas fa-print"></i> Print
        </button>
        <a href="/tabulation/events/<?= $event['id'] ?>/reports" class="btn btn-secondary">Back</a>
    </div>
</div>

<!-- Standardized Report Header -->
<div class="report-header">
    <h2><?= htmlspecialchars($event['name']) ?></h2>
    <div class="report-info">
        <strong>Level:</strong> <?= htmlspecialchars($round['level_name']) ?><br>
        <strong>Round:</strong> <?= htmlspecialchars($round['name']) ?><br>
        <strong>Showing:</strong> Top <?= $topN ?> contestants<br>
        <?php if (!empty($event['venue'])): ?>
            <strong>Venue:</strong> <?= htmlspecialchars($event['venue']) ?><br>
        <?php endif; ?>
        <strong>Date:</strong> <?= !empty($event['event_date']) ? date('F d, Y', strtotime($event['event_date'])) : date('F d, Y') ?>
    </div>
</div>

<?php if (empty($contestantScores)): ?>
    <div class="alert alert-warning">No rankings available yet.</div>
<?php else: ?>
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Top <?= $topN ?> Finalists</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th class="text-center">Rank</th>
                            <th>Contestant #</th>
                            <th>Name</th>
                            <?php foreach ($judges as $judge): ?>
                                <th class="text-center">Judge #<?= htmlspecialchars($judge['judge_number']) ?></th>
                            <?php endforeach; ?>
                            <th class="text-center"><strong>Total</strong></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contestantScores as $cs): ?>
                        <tr class="<?= $cs['contestant']['rank'] <= 3 ? 'table-warning' : '' ?>">
                            <td class="text-center">
                                <?php if ($cs['contestant']['rank'] == 1): ?>
                                    <h3 class="mb-0"><span class="badge bg-warning text-dark"><i class="fas fa-trophy"></i> 1</span></h3>
                                <?php elseif ($cs['contestant']['rank'] == 2): ?>
                                    <h4 class="mb-0"><span class="badge bg-secondary">2</span></h4>
                                <?php elseif ($cs['contestant']['rank'] == 3): ?>
                                    <h4 class="mb-0"><span class="badge" style="background-color: #CD7F32; color: white;">3</span></h4>
                                <?php else: ?>
                                    <h5 class="mb-0"><?= $cs['contestant']['rank'] ?></h5>
                                <?php endif; ?>
                            </td>
                            <td><strong>#<?= htmlspecialchars($cs['contestant']['contestant_number']) ?></strong></td>
                            <td><strong><?= htmlspecialchars($cs['contestant']['name']) ?></strong></td>
                            <?php foreach ($judges as $judge): ?>
                                <td class="text-center"><?= number_format($cs['judge_scores'][$judge['id']], 3) ?></td>
                            <?php endforeach; ?>
                            <td class="text-center"><strong class="text-success"><?= number_format($cs['total'], 3) ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
