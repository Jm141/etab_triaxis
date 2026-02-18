<?php 
$title = 'Score Management - ' . $round['name'];
require __DIR__ . '/../layout/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-edit"></i> Score Management: <?= htmlspecialchars($round['name']) ?></h1>
    <a href="/tabulation/events/<?= $round['event_id'] ?>/results" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Results
    </a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <h5><?= htmlspecialchars($round['event_name'] ?? 'Event') ?> - <?= htmlspecialchars($round['level_name']) ?></h5>
        <p class="text-muted mb-0"><?= htmlspecialchars($round['description'] ?? 'No description') ?></p>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-list"></i> Scores (Draft &amp; Submitted)</h5>
        <div class="d-flex gap-2">
            <?php if (!empty($judges)): ?>
            <select class="form-control form-control-sm" id="judgeFilter" style="width: auto; min-width: 200px;">
                <option value="">All Judges</option>
                <?php foreach ($judges as $judge): ?>
                    <option value="<?= $judge['id'] ?>" <?= ($judgeFilter && $judgeFilter['id'] == $judge['id']) ? 'selected' : '' ?>>
                        Judge #<?= htmlspecialchars($judge['judge_number']) ?> - <?= htmlspecialchars($judge['judge_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php endif; ?>
            <a href="/tabulation/score-management/print-round/<?= $round['id'] ?><?= ($judgeFilter ? '/' . $judgeFilter['id'] : '') ?>" 
               class="btn btn-sm btn-info" target="_blank" id="printBtn">
            <i class="fas fa-print"></i> Print Scores
        </a>
        </div>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <h5><i class="icon fas fa-info"></i> Admin Score Editing</h5>
            <p class="mb-0">
                You can only edit scores where the judge has granted permission. 
                Judges can toggle this permission in their scoring interface.
            </p>
        </div>
        
        <?php if (empty($judgeData)): ?>
            <p class="text-muted">No scores have been recorded yet for this round.</p>
        <?php else: ?>
            <?php foreach ($judgeData as $judgeData): ?>
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user-tie"></i> Judge #<?= htmlspecialchars($judgeData['judge']['judge_number']) ?> - 
                        <?= htmlspecialchars($judgeData['judge']['judge_name']) ?>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 100px;">Contestant #</th>
                                    <?php foreach ($criteria as $criterion): ?>
                                        <th class="text-center" style="min-width: 120px;">
                                            <div>
                                                <strong><?= htmlspecialchars($criterion['name']) ?></strong>
                                                <br>
                                                <small class="text-muted">Max: <?= number_format($criterion['max_score'], 2) ?></small>
                                            </div>
                                        </th>
                                    <?php endforeach; ?>
                                    <th class="text-center" style="width: 120px;">
                                        <strong>Total Score</strong>
                                        <br>
                                        <small class="text-muted">Sum of criteria</small>
                                    </th>
                                    <th class="text-center" style="width: 100px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($judgeData['scores'] as $score): ?>
                                <tr>
                                    <td>
                                        <strong>#<?= htmlspecialchars($score['contestant_number']) ?></strong>
                                        <br>
                                        <small class="text-muted"><?= htmlspecialchars($score['contestant_name']) ?></small>
                                        <br>
                                        <?php if (!empty($score['is_submitted'])): ?>
                                            <span class="badge badge-success badge-sm mt-1">
                                                <i class="fas fa-check-circle"></i> Submitted
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-warning badge-sm mt-1">
                                                <i class="fas fa-clock"></i> Draft
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <?php 
                                    // Create a map of criteria_id => score_detail for easy lookup
                                    $detailsMap = [];
                                    foreach ($score['details'] as $detail) {
                                        $detailsMap[$detail['criteria_id']] = $detail;
                                    }
                                    
                                    foreach ($criteria as $criterion): 
                                        $detail = $detailsMap[$criterion['id']] ?? null;
                                    ?>
                                        <td class="text-center">
                                            <?php if ($detail): ?>
                                                <strong><?= number_format($detail['raw_score'], 2) ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    (<?= number_format(($detail['raw_score'] / $criterion['max_score']) * 100, 2) ?>%)
                                                </small>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                    <td class="text-center">
                                        <?php
                                        $rawTotal = 0;
                                        foreach ($score['details'] as $d) { $rawTotal += (float)($d['raw_score'] ?? 0); }
                                        ?>
                                        <strong class="text-primary" style="font-size: 1.1em;">
                                            <?= number_format($rawTotal, 2) ?>
                                        </strong>
                                        <?php if (isset($score['point_deduction']) && $score['point_deduction'] > 0): ?>
                                            <br>
                                            <small class="text-danger">
                                                <i class="fas fa-minus-circle"></i> -<?= number_format($score['point_deduction'], 2) ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="/tabulation/score-management/edit/<?= $score['id'] ?>" 
                                           class="btn btn-sm btn-warning" 
                                           title="Edit Score">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
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
    </div>
</div>


<script>
$(document).ready(function() {
    $('#judgeFilter').on('change', function() {
        const judgeId = $(this).val();
        const baseUrl = '/tabulation/score-management/round/<?= $round['id'] ?>';
        const url = judgeId ? baseUrl + '/' + judgeId : baseUrl;
        window.location.href = url;
    });
    
    // Update print button URL when judge filter changes
    $('#judgeFilter').on('change', function() {
        const judgeId = $(this).val();
        const baseUrl = '/tabulation/score-management/print-round/<?= $round['id'] ?>';
        const printUrl = judgeId ? baseUrl + '/' + judgeId : baseUrl;
        $('#printBtn').attr('href', printUrl);
    });
});
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>

