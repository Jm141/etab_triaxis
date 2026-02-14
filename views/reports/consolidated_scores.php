<?php 
$title = 'Consolidated Scores Report - ' . $round['name'];
require __DIR__ . '/../layout/header.php'; 
require __DIR__ . '/_print_styles.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <h1><i class="fas fa-table"></i> Consolidated Scores Report</h1>
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
        <?php if (!empty($event['venue'])): ?>
            <strong>Venue:</strong> <?= htmlspecialchars($event['venue']) ?><br>
        <?php endif; ?>
        <strong>Date:</strong> <?= !empty($event['event_date']) ? date('F d, Y', strtotime($event['event_date'])) : date('F d, Y') ?>
    </div>
</div>

<?php if (empty($contestantScores)): ?>
    <div class="alert alert-warning">No scores available yet.</div>
<?php else: ?>
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Consolidated Scores</h5>
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
                        <tr>
                            <td class="text-center"><?= $cs['contestant']['rank'] ?></td>
                            <td><strong>#<?= htmlspecialchars($cs['contestant']['contestant_number']) ?></strong></td>
                            <td><?= htmlspecialchars($cs['contestant']['name']) ?></td>
                            <?php foreach ($judges as $judge): ?>
                                <td class="text-center">
                                    <?= $cs['judge_scores'][$judge['id']] !== null ? number_format($cs['judge_scores'][$judge['id']], 3) : '-' ?>
                                </td>
                            <?php endforeach; ?>
                            <td class="text-center"><strong><?= number_format($cs['total'], 3) ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
