<?php 
$title = 'My Rounds';
require __DIR__ . '/../layout/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-clipboard-check"></i> My Assigned Rounds</h1>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($rounds)): ?>
            <div class="text-center py-5">
                <i class="fas fa-inbox" style="font-size: 3rem; color: #ccc;"></i>
                <h4 class="mt-3 text-muted">No Rounds Assigned</h4>
                <p class="text-muted">You don't have any rounds assigned to you yet.</p>
                <div class="alert alert-info mt-4" style="max-width: 600px; margin: 0 auto; text-align: left;">
                    <h5><i class="fas fa-info-circle"></i> What you need to do:</h5>
                    <ol class="mb-0">
                        <li>Contact your <strong>Event Admin</strong> or <strong>Super Admin</strong></li>
                        <li>Ask them to:
                            <ul>
                                <li>Assign you to an event (if not already done)</li>
                                <li>Assign you to specific rounds for that event</li>
                            </ul>
                        </li>
                        <li>Once assigned, your rounds will appear here</li>
                    </ol>
                    <hr>
                    <p class="mb-0"><small><strong>Note:</strong> Only administrators can assign judges to events and rounds. You cannot assign yourself.</small></p>
                </div>
            </div>
        <?php else: ?>
            <?php
            // Group rounds by event and level (one card per level; all scoring categories in one view)
            $groupedByLevel = [];
            foreach ($rounds as $round) {
                $levelId = $round['level_id'];
                if (!isset($groupedByLevel[$round['event_id']])) {
                    $groupedByLevel[$round['event_id']] = [];
                }
                if (!isset($groupedByLevel[$round['event_id']][$levelId])) {
                    $groupedByLevel[$round['event_id']][$levelId] = [
                        'event_name' => $round['event_name'],
                        'level_name' => $round['level_name'],
                        'rounds' => []
                    ];
                }
                $groupedByLevel[$round['event_id']][$levelId]['rounds'][] = $round;
            }
            ?>
            
            <?php foreach ($groupedByLevel as $eventId => $levels): ?>
                <?php foreach ($levels as $levelId => $levelData): ?>
                    <?php 
                    $levelRounds = $levelData['rounds'];
                    $firstRound = $levelRounds[0];
                    $totalSubmitted = 0;
                    $totalContestants = 0;
                    $allDone = true;
                    foreach ($levelRounds as $r) {
                        $sc = $r['submitted_count'] ?? 0;
                        $tc = $r['total_contestants'] ?? 0;
                        $totalSubmitted += $sc;
                        $totalContestants = max($totalContestants, $tc);
                        if ($tc > 0 && $sc < $tc) $allDone = false;
                    }
                    $percentage = $totalContestants > 0 ? min(100, ($totalSubmitted / (count($levelRounds) * max(1, $totalContestants))) * 100) : 0;
                    ?>
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0">
                                <i class="fas fa-trophy"></i> <?= htmlspecialchars($firstRound['event_name']) ?>
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="card mb-3">
                                <div class="card-header d-flex flex-wrap align-items-center justify-content-between" style="background-color: #37474f; color: #eceff1;">
                                    <h5 class="mb-0">
                                        <i class="fas fa-layer-group"></i> <?= htmlspecialchars($levelData['level_name']) ?>
                                    </h5>
                                    <a href="/tabulation/judge/level/<?= (int)$levelId ?>/table" class="btn btn-sm btn-primary">
                                        <i class="fas fa-table"></i> Score All Categories
                                    </a>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted small mb-2">
                                        <strong>Scoring categories in this level:</strong>
                                        <?= htmlspecialchars(implode(', ', array_map(function($r) { return $r['name']; }, $levelRounds))) ?>
                                    </p>
                                    <div class="mb-2">
                                        <small class="text-muted">Progress (all categories):</small>
                                        <div class="progress mt-1" style="height: 20px;">
                                            <div class="progress-bar <?= $allDone ? 'bg-success' : '' ?>" role="progressbar" style="width: <?= $percentage ?>%">
                                                <?= $totalSubmitted ?> / <?= count($levelRounds) * max(1, $totalContestants) ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <?php if ($allDone && $totalContestants > 0): ?>
                                            <span class="badge bg-success"><i class="fas fa-check-circle"></i> All categories done</span>
                                        <?php else: ?>
                                            <span class="badge bg-primary">In progress</span>
                                        <?php endif; ?>
                                    </div>
                                    <a href="/tabulation/judge/level/<?= (int)$levelId ?>/table" class="btn btn-primary w-100">
                                        <i class="fas fa-table"></i> Score All Categories in One View
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>


