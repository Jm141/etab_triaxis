<?php 
$title = 'Level Completion Status';
require __DIR__ . '/../layout/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-tasks"></i> Level Completion Status</h1>
    <a href="/tabulation/events/<?= $eventId ?>/results" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Event Results
    </a>
</div>

<?php if (empty($levels)): ?>
    <div class="alert alert-info">
        No levels found for this event.
    </div>
<?php else: ?>
    <div class="row">
        <?php foreach ($levels as $level): ?>
            <?php 
            // Check level completion status
            $levelResult = $scoringEngine->calculateLevelRankings($level['id']);
            $isComplete = $levelResult['level_complete'] ?? false;
            $incompleteRounds = $levelResult['incomplete_rounds'] ?? [];
            $totalRounds = $levelResult['total_rounds'] ?? 0;
            $completedRounds = $levelResult['completed_rounds'] ?? 0;
            $completionPercentage = $totalRounds > 0 ? ($completedRounds / $totalRounds) * 100 : 0;
            ?>
            
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card <?= $isComplete ? 'border-success' : 'border-warning' ?>">
                    <div class="card-header <?= $isComplete ? 'bg-success text-white' : 'bg-warning text-dark' ?>">
                        <h5 class="mb-0">
                            <i class="fas fa-layer-group"></i> 
                            <?= htmlspecialchars($level['name']) ?>
                            <?php if ($isComplete): ?>
                                <i class="fas fa-check-circle float-end"></i>
                            <?php else: ?>
                                <i class="fas fa-exclamation-triangle float-end"></i>
                            <?php endif; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Progress Overview -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold">Progress:</span>
                                    <span class="badge <?= $isComplete ? 'bg-success' : 'bg-warning' ?> text-white">
                                        <?= $completedRounds ?>/<?= $totalRounds ?> rounds (<?= number_format($completionPercentage, 1) ?>%)
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Progress Bar -->
                        <div class="mb-3">
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar <?= $isComplete ? 'bg-success' : 'bg-warning' ?>" 
                                     role="progressbar" 
                                     style="width: <?= $completionPercentage ?>%"
                                     aria-valuenow="<?= $completionPercentage ?>" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                    <?= number_format($completionPercentage, 1) ?>%
                                </div>
                            </div>
                        </div>
                        
                        <?php if (!$isComplete): ?>
                            <!-- Incomplete Rounds Details -->
                            <div class="alert alert-warning">
                                <h6><i class="fas fa-exclamation-triangle"></i> Incomplete Rounds</h6>
                                <p class="mb-2">
                                    The following rounds need to be completed before this level can be finalized and advancement processed:
                                </p>
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($incompleteRounds as $incompleteRound): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="fas fa-clock text-warning"></i>
                                                <strong><?= htmlspecialchars($incompleteRound['name']) ?></strong>
                                                <span class="badge bg-danger ms-2">No Scores</span>
                                            </div>
                                            <div>
                                                <small class="text-muted">Round ID: <?= $incompleteRound['id'] ?></small>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="d-grid gap-2">
                                <a href="/tabulation/judge/rounds/level/<?= $level['id'] ?>" 
                                   class="btn btn-primary">
                                    <i class="fas fa-edit"></i> Go to Scoring
                                </a>
                                <a href="/tabulation/reports/level/<?= $level['id'] ?>" 
                                   class="btn btn-outline-info">
                                    <i class="fas fa-chart-bar"></i> View Current Rankings
                                </a>
                            </div>
                        <?php else: ?>
                            <!-- Complete Level Details -->
                            <div class="alert alert-success">
                                <h6><i class="fas fa-check-circle"></i> Level Complete!</h6>
                                <p class="mb-2">
                                    All rounds have been scored. This level is ready for advancement processing.
                                </p>
                                <div class="d-grid gap-2">
                                    <a href="/tabulation/reports/level/<?= $level['id'] ?>" 
                                       class="btn btn-success">
                                        <i class="fas fa-trophy"></i> View Final Rankings
                                    </a>
                                    <a href="/tabulation/events/<?= $eventId ?>/levels" 
                                       class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left"></i> Back to All Levels
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Summary Card -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-info-circle"></i> Event Summary</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <?php 
                $totalLevels = count($levels);
                $completeLevels = 0;
                $incompleteLevels = 0;
                
                foreach ($levels as $level) {
                    $result = $scoringEngine->calculateLevelRankings($level['id']);
                    if (($result['level_complete'] ?? false)) {
                        $completeLevels++;
                    } else {
                        $incompleteLevels++;
                    }
                }
                ?>
                <div class="col-md-4">
                    <div class="text-center">
                        <h4 class="text-success"><?= $completeLevels ?></h4>
                        <small class="text-muted">Complete Levels</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <h4 class="text-warning"><?= $incompleteLevels ?></h4>
                        <small class="text-muted">Incomplete Levels</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <h4 class="text-info"><?= $totalLevels ?></h4>
                        <small class="text-muted">Total Levels</small>
                    </div>
                </div>
            </div>
            
            <?php if ($incompleteLevels > 0): ?>
                <div class="alert alert-info mt-3">
                    <i class="fas fa-lightbulb"></i>
                    <strong>Recommendation:</strong> Complete all incomplete rounds before processing advancement to ensure fair and accurate rankings.
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<style>
.card.border-success {
    border-left: 5px solid #28a745 !important;
}

.card.border-warning {
    border-left: 5px solid #ffc107 !important;
}

.progress {
    background-color: #e9ecef;
}

.progress-bar {
    transition: width 0.6s ease;
}

.list-group-item {
    border-left: 4px solid #ffc107;
    margin-bottom: 5px;
}

.badge {
    font-size: 0.8em;
}
</style>

<?php require __DIR__ . '/../layout/footer.php'; ?>
