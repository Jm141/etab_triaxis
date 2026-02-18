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
            // Group rounds by event and level
            $groupedRounds = [];
            foreach ($rounds as $round) {
                $groupedRounds[$round['event_id']][$round['level_name']][] = $round;
            }
            ?>
            
            <?php foreach ($groupedRounds as $eventId => $levels): ?>
                <?php foreach ($levels as $levelName => $levelRounds): ?>
                    <?php $firstRound = $levelRounds[0]; ?>
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0">
                                <i class="fas fa-trophy"></i> <?= htmlspecialchars($firstRound['event_name']) ?>
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="card mb-3">
                                <div class="card-header bg-secondary text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-layer-group"></i> <?= htmlspecialchars($levelName) ?>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <?php foreach ($levelRounds as $round): ?>
                                            <div class="col-md-6 col-lg-4 mb-3">
                                                <div class="card h-100">
                                                    <div class="card-body">
                                                        <h6 class="card-title">
                                                            <i class="fas fa-circle-notch"></i> <?= htmlspecialchars($round['name']) ?>
                                                        </h6>
                                                        <div class="mb-2">
                                                            <?php 
                                                            $submittedCount = $round['submitted_count'] ?? 0;
                                                            $totalContestants = $round['total_contestants'] ?? 0;
                                                            $percentage = $totalContestants > 0 ? ($submittedCount / $totalContestants * 100) : 0;
                                                            
                                                            // Determine if round is done (all contestants scored and submitted)
                                                            $isDone = ($totalContestants > 0 && $submittedCount >= $totalContestants);
                                                            ?>
                                                            <small class="text-muted">Progress:</small>
                                                            <div class="progress mt-1" style="height: 20px;">
                                                                <div class="progress-bar <?= $isDone ? 'bg-success' : '' ?>" role="progressbar" 
                                                                     style="width: <?= $percentage ?>%">
                                                                    <?= $submittedCount ?> / <?= $totalContestants ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="mb-2">
                                                            <?php if ($isDone): ?>
                                                                <span class="badge bg-success">
                                                                    <i class="fas fa-check-circle"></i> Done
                                                                </span>
                                                            <?php else: ?>
                                                                <span class="badge bg-<?= $round['status'] === 'Active' ? 'primary' : 'secondary' ?>">
                                                                    <?= htmlspecialchars($round['status']) ?>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <a href="/tabulation/judge/rounds/<?= $round['id'] ?>/table" class="btn btn-sm btn-primary w-100">
                                                            <i class="fas fa-table"></i> Score Contestants
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
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


