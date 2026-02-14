<?php 
$title = 'Score Edit Log - ' . $round['name'];
require __DIR__ . '/../layout/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-history"></i> Score Change / Edit Log</h1>
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

<?php if (empty($editLogs)): ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> No score edits recorded for this round.
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0">Edit History</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Date & Time</th>
                            <th>Editor</th>
                            <th>Contestant</th>
                            <th>Judge</th>
                            <th>Before</th>
                            <th>After</th>
                            <th>Changes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($editLogs as $log): ?>
                        <tr>
                            <td>
                                <small><?= date('M d, Y H:i:s', strtotime($log['created_at'])) ?></small>
                            </td>
                            <td><?= htmlspecialchars($log['editor_name'] ?: 'System') ?></td>
                            <td>
                                #<?= htmlspecialchars($log['contestant_number'] ?: '-') ?>
                                <?php if ($log['contestant_name']): ?>
                                    <br><small><?= htmlspecialchars($log['contestant_name']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                Judge #<?= htmlspecialchars($log['judge_number'] ?: '-') ?>
                                <?php if ($log['judge_name']): ?>
                                    <br><small><?= htmlspecialchars($log['judge_name']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($log['old_values_parsed']): ?>
                                    <small class="text-muted">
                                        <?php 
                                        $oldScores = $log['old_values_parsed'];
                                        if (is_array($oldScores)) {
                                            foreach ($oldScores as $old) {
                                                if (isset($old['raw_score'])) {
                                                    echo number_format($old['raw_score'], 3) . '<br>';
                                                }
                                            }
                                        }
                                        ?>
                                    </small>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($log['new_values_parsed']): ?>
                                    <small>
                                        <?php 
                                        $newScores = $log['new_values_parsed'];
                                        if (is_array($newScores) && isset($newScores['new_scores'])) {
                                            foreach ($newScores['new_scores'] as $score) {
                                                echo number_format($score, 3) . '<br>';
                                            }
                                        }
                                        ?>
                                    </small>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-info">Edited</span>
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
