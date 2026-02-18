<?php 
$title = 'Reports - ' . $event['name'];
require __DIR__ . '/../layout/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-file-alt"></i> Reports: <?= htmlspecialchars($event['name']) ?></h1>
    <a href="/tabulation/events/<?= $event['id'] ?>" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Event
    </a>
</div>

<?php if (Session::get('role_name') === 'Event Technical Admin' || Session::get('role_name') === 'Super Admin'): ?>
<div class="card mb-4">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0"><i class="fas fa-calculator"></i> Scoring Formula Management</h5>
    </div>
    <div class="card-body">
        <p class="text-muted">Manage scoring formulas for calculating winners. Only Technical Admins can edit formulas.</p>
        <a href="/tabulation/events/<?= $event['id'] ?>/reports/formula" class="btn btn-warning">
            <i class="fas fa-edit"></i> Manage Formulas
        </a>
    </div>
</div>
<?php endif; ?>

<div class="row">
    <!-- Level Completion Status -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-tasks"></i> Level Completion Status</h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">Check which levels are complete before processing advancement.</p>
                
                <?php 
                // Initialize scoring engine
                require_once __DIR__ . '/../../core/ScoringEngine.php';
                $scoringEngine = new ScoringEngine();
                
                $totalLevels = count($levels);
                $completeLevels = 0;
                $incompleteLevels = 0;
                $hasIncompleteRounds = false;
                ?>
                
                <div class="row">
                    <?php foreach ($levels as $level): ?>
                        <?php 
                        $levelResult = $scoringEngine->calculateLevelRankings($level['id']);
                        $isComplete = $levelResult['level_complete'] ?? false;
                        $incompleteRounds = $levelResult['incomplete_rounds'] ?? [];
                        $totalRounds = $levelResult['total_rounds'] ?? 0;
                        $completedRounds = $levelResult['completed_rounds'] ?? 0;
                        $completionPercentage = $totalRounds > 0 ? ($completedRounds / $totalRounds) * 100 : 0;
                        
                        if ($isComplete) {
                            $completeLevels++;
                        } else {
                            $incompleteLevels++;
                            $hasIncompleteRounds = true;
                        }
                        ?>
                        
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card <?= $isComplete ? 'border-success' : 'border-warning' ?> h-100">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0">
                                            <i class="fas fa-layer-group"></i> 
                                            <?= htmlspecialchars($level['name']) ?>
                                        </h6>
                                        <span class="badge <?= $isComplete ? 'bg-success' : 'bg-warning' ?> text-white">
                                            <?= $isComplete ? 'Complete' : 'Incomplete' ?>
                                        </span>
                                    </div>
                                    
                                    <!-- Progress Bar -->
                                    <div class="progress mb-2" style="height: 20px;">
                                        <div class="progress-bar <?= $isComplete ? 'bg-success' : 'bg-warning' ?>" 
                                             role="progressbar" 
                                             style="width: <?= $completionPercentage ?>%"
                                             aria-valuenow="<?= $completionPercentage ?>" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                            <?= number_format($completionPercentage, 0) ?>%
                                        </div>
                                    </div>
                                    
                                    <small class="text-muted">
                                        <?= $completedRounds ?>/<?= $totalRounds ?> rounds complete
                                        <?php if (!$isComplete && !empty($incompleteRounds)): ?>
                                            <br>
                                            <span class="text-warning">
                                                <i class="fas fa-exclamation-triangle"></i> 
                                                <?= count($incompleteRounds) ?> round(s) missing scores
                                            </span>
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Summary Alert -->
                <?php if ($hasIncompleteRounds): ?>
                    <div class="alert alert-warning mt-3">
                        <h6><i class="fas fa-exclamation-triangle"></i> ⚠️ Incomplete Levels Detected</h6>
                        <p class="mb-2">
                            <strong>Advancement cannot be processed</strong> until all rounds in incomplete levels are scored.
                            This ensures fair and accurate rankings for all contestants.
                        </p>
                        <a href="/tabulation/events/<?= $event['id'] ?>/levels" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Go to Scoring
                        </a>
                    </div>
                <?php else: ?>
                    <div class="alert alert-success mt-3">
                        <h6><i class="fas fa-check-circle"></i> ✅ All Levels Complete</h6>
                        <p class="mb-0">
                            All levels have been scored. Advancement can be processed safely.
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Report Per Round -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-list"></i> Report Per Round</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">View scores and rankings for a specific round.</p>
                <select class="form-control mb-3" id="roundSelect">
                    <option value="">Select Round...</option>
                    <?php foreach ($rounds as $round): ?>
                        <option value="<?= $round['id'] ?>">
                            <?= htmlspecialchars($round['level_name']) ?> - <?= htmlspecialchars($round['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="button" class="btn btn-primary btn-block" id="btnRoundReport" disabled>
                    <i class="fas fa-file-alt"></i> Generate Report
                </button>
            </div>
        </div>
    </div>
    
    <!-- Report Per Level -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-layer-group"></i> Report Per Level</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">View aggregated scores and rankings for a level (all rounds combined).</p>
                <select class="form-control mb-3" id="levelSelect">
                    <option value="">Select Level...</option>
                    <?php foreach ($levels as $level): ?>
                        <option value="<?= $level['id'] ?>">
                            <?= htmlspecialchars($level['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="button" class="btn btn-info btn-block" id="btnLevelReport" disabled>
                    <i class="fas fa-file-alt"></i> Generate Report
                </button>
            </div>
        </div>
    </div>
    
    <!-- Report Per Judge -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-user-tie"></i> Report Per Judge</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">View all scores submitted by a specific judge.</p>
                <select class="form-control mb-3" id="judgeSelect">
                    <option value="">Select Judge...</option>
                    <?php foreach ($judges as $judge): ?>
                        <option value="<?= $judge['id'] ?>">
                            Judge #<?= htmlspecialchars($judge['judge_number']) ?> - <?= htmlspecialchars($judge['judge_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="button" class="btn btn-success btn-block" id="btnJudgeReport" disabled>
                    <i class="fas fa-file-alt"></i> Generate Report
                </button>
            </div>
        </div>
    </div>
    
    <!-- Finals Report -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-trophy"></i> Finals Report</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">View final rankings with all round scores, totals, and winner calculation.</p>
                <a href="/tabulation/events/<?= $event['id'] ?>/reports/finals" class="btn btn-warning">
                    <i class="fas fa-file-alt"></i> Generate Finals Report
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Wait for jQuery to be available
(function() {
    function initReports() {
        if (typeof jQuery === 'undefined' || typeof $ === 'undefined') {
            setTimeout(initReports, 100);
            return;
        }
        
        $(document).ready(function() {
            const eventId = <?= $event['id'] ?>;
            
            console.log('Reports page initialized, eventId:', eventId);
            
            // Round Report Button
            $('#roundSelect').on('change', function() {
                const roundId = $(this).val();
                console.log('Round selected:', roundId);
                if (roundId) {
                    $('#btnRoundReport').prop('disabled', false).removeClass('disabled');
                } else {
                    $('#btnRoundReport').prop('disabled', true).addClass('disabled');
                }
            });
            
            $('#btnRoundReport').on('click', function(e) {
                e.preventDefault();
                const roundId = $('#roundSelect').val();
                console.log('Round report clicked, roundId:', roundId);
                if (roundId) {
                    window.location.href = '/tabulation/events/' + eventId + '/reports/round/' + roundId;
                } else {
                    alert('Please select a round first');
                }
            });
            
            // Level Report Button
            $('#levelSelect').on('change', function() {
                const levelId = $(this).val();
                console.log('Level selected:', levelId);
                if (levelId) {
                    $('#btnLevelReport').prop('disabled', false).removeClass('disabled');
                } else {
                    $('#btnLevelReport').prop('disabled', true).addClass('disabled');
                }
            });
            
            $('#btnLevelReport').on('click', function(e) {
                e.preventDefault();
                const levelId = $('#levelSelect').val();
                console.log('Level report clicked, levelId:', levelId);
                if (levelId) {
                    window.location.href = '/tabulation/events/' + eventId + '/reports/level/' + levelId;
                } else {
                    alert('Please select a level first');
                }
            });
            
            // Judge Report Button
            $('#judgeSelect').on('change', function() {
                const judgeId = $(this).val();
                console.log('Judge selected:', judgeId);
                if (judgeId) {
                    $('#btnJudgeReport').prop('disabled', false).removeClass('disabled');
                } else {
                    $('#btnJudgeReport').prop('disabled', true).addClass('disabled');
                }
            });
            
            $('#btnJudgeReport').on('click', function(e) {
                e.preventDefault();
                const judgeId = $('#judgeSelect').val();
                console.log('Judge report clicked, judgeId:', judgeId);
                if (judgeId) {
                    window.location.href = '/tabulation/events/' + eventId + '/reports/judge/' + judgeId;
                } else {
                    alert('Please select a judge first');
                }
            });
            
            // Debug: Log initial state
            console.log('Round select value:', $('#roundSelect').val());
            console.log('Level select value:', $('#levelSelect').val());
            console.log('Judge select value:', $('#judgeSelect').val());
        });
    }
    
    initReports();
})();
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
