<?php 
$title = 'Score Management - ' . $round['name'];
require __DIR__ . '/../layout/header.php'; 
?>

<style>
/* Custom styling for active tabs */
.nav-tabs .nav-link.active {
    background-color: #2c3e50 !important;
    color: #ffffff !important;
    border-color: #2c3e50 !important;
    font-weight: bold;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.nav-tabs .nav-link.active:hover {
    background-color: #34495e !important;
}

/* Ensure tab colors are properly applied */
.nav-tabs .nav-link {
    transition: all 0.3s ease;
}
</style>

<!-- Get database connection for contestant count -->
$db = Database::getInstance();
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

<?php if (empty($judgeData)): ?>
    <div class="card">
        <div class="card-body">
            <div class="alert alert-info">
                <h5><i class="icon fas fa-info"></i> Admin Score Editing</h5>
                <p class="mb-0">
                    You can only edit scores where the judge has granted permission. 
                    Judges can toggle this permission in their scoring interface.
                </p>
            </div>
            <p class="text-muted text-center">No scores have been recorded yet for this round.</p>
        </div>
    </div>
<?php else: ?>
    <!-- Judge Navigation Tabs -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-users"></i> Judge Score Sheets</h5>
        </div>
        <div class="card-body p-0">
            <!-- Tab Navigation -->
            <ul class="nav nav-tabs nav-fill" id="judgeTabs" role="tablist">
                <?php $index = 0; foreach ($judgeData as $judgeId => $judge): ?>
                    <?php 
                    // Calculate completion status for this judge
                    $totalRequired = count($judge['scores']) * count($criteria);
                    $totalFilled = 0;
                    $totalSubmitted = 0;
                    $isCompleted = false;
                    
                    foreach ($judge['scores'] as $score) {
                        if (!empty($score['details'])) {
                            $totalFilled += count($score['details']);
                        }
                        // Check if this contestant's scores are submitted
                        if (!empty($score['is_submitted'])) {
                            $totalSubmitted++;
                        }
                        // Debug: log each score status
                        error_log("Judge {$judgeId} - Contestant {$score['contestant_number']}: submitted=" . (!empty($score['is_submitted']) ? 'YES' : 'NO'));
                    }
                    
                    // Debug: log totals
                    error_log("Judge {$judgeId} - Total submitted: {$totalSubmitted}, Total scores: " . count($judge['scores']));
                    
                    // Get total contestants for this judge (not all event contestants)
                    $totalContestantsForJudge = count($judge['scores']);
                    
                    // Calculate expected total: total contestants for this judge × number of criteria
                    $expectedTotal = $totalContestantsForJudge * count($criteria);
                    
                    if ($expectedTotal > 0) {
                        $completionPercentage = ($totalFilled / $expectedTotal) * 100;
                        $submissionPercentage = ($totalSubmitted / $totalContestantsForJudge) * 100;
                        $isCompleted = $submissionPercentage >= 100; // Only complete when all submitted
                        
                        // Debug: log final calculation
                        error_log("Judge {$judgeId} - Submission: {$totalSubmitted}/{$totalContestantsForJudge} = {$submissionPercentage}%, Completed: " . ($isCompleted ? 'YES' : 'NO'));
                    } else {
                        $completionPercentage = 0;
                        $submissionPercentage = 0;
                        $isCompleted = false;
                    }
                    
                    // Determine tab color and status
                    $tabClass = '';
                    $tabStatus = '';
                    $badgeClass = '';
                    
                    if ($isCompleted) {
                        // All submitted - Green
                        $tabClass = 'bg-success text-white';
                        $badgeClass = 'bg-light text-success';
                        $tabStatus = 'Complete: All contestants submitted';
                        error_log("Judge {$judgeId} - COLOR: GREEN (all submitted)");
                    } elseif ($totalFilled > 0) {
                        // Has scores but not all submitted - Blue for scored, not submitted
                        if ($totalSubmitted > 0) {
                            $tabClass = 'bg-primary text-white'; // Blue for partially submitted
                            $badgeClass = 'bg-light text-primary';
                            $tabStatus = 'In Progress: Some contestants submitted (' . $totalSubmitted . '/' . $totalContestantsForJudge . ')';
                            error_log("Judge {$judgeId} - COLOR: BLUE (partially submitted)");
                        } else {
                            $tabClass = 'bg-info text-white'; // Light blue for scored but not submitted
                            $badgeClass = 'bg-light text-info';
                            $tabStatus = 'Scored but not submitted: ' . $totalFilled . ' scores entered';
                            error_log("Judge {$judgeId} - COLOR: LIGHT BLUE (scored but not submitted)");
                        }
                    } else {
                        // No scores - Gray
                        $tabClass = 'bg-secondary text-white';
                        $badgeClass = 'bg-light text-secondary';
                        $tabStatus = 'Not Started: No contestants scored';
                        error_log("Judge {$judgeId} - COLOR: GRAY (no scores)");
                    }
                    ?>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?= $index === 0 ? 'active' : '' ?> <?= $tabClass ?>" 
                                id="judge-tab-<?= $judgeId ?>" 
                                data-bs-toggle="tab" 
                                data-bs-target="#judge-content-<?= $judgeId ?>" 
                                type="button" 
                                role="tab" 
                                aria-controls="judge-content-<?= $judgeId ?>" 
                                aria-selected="<?= $index === 0 ? 'true' : 'false' ?>"
                                title="<?= $tabStatus ?>">
                            <i class="fas fa-user-tie"></i> 
                            Judge #<?= htmlspecialchars($judge['judge']['judge_number']) ?> 
                            - <?= htmlspecialchars($judge['judge']['judge_name']) ?>
                            <span class="badge ms-2 <?= $badgeClass ?>">
                                <?= number_format($submissionPercentage, 0) ?>%
                            </span>
                        </button>
                    </li>
                    <?php $index++; ?>
                <?php endforeach; ?>
            </ul>
            
            <!-- Tab Content -->
            <div class="tab-content p-3" id="judgeTabContent">
                <?php $index = 0; foreach ($judgeData as $judgeId => $judge): ?>
                    <div class="tab-pane fade <?= $index === 0 ? 'show active' : '' ?>" 
                         id="judge-content-<?= $judgeId ?>" 
                         role="tabpanel" 
                         aria-labelledby="judge-tab-<?= $judgeId ?>">
                        
                        <!-- Judge Info and Print Button -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="mb-1">
                                    <i class="fas fa-user-tie"></i> Judge #<?= htmlspecialchars($judge['judge']['judge_number']) ?> - 
                                    <?= htmlspecialchars($judge['judge']['judge_name']) ?>
                                </h6>
                                <small class="text-muted">
                                    <?= count($judge['scores']) ?> contestants • <?= count($criteria) ?> criteria
                                </small>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" 
                                        class="btn btn-sm btn-info" 
                                        onclick="printJudgeSheet(<?= $judge['judge']['id'] ?>)">
                                    <i class="fas fa-print"></i> Print Sheet
                                </button>
                            </div>
                        </div>
                        
                        <!-- Spreadsheet-style Score Table -->
                        <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                            <table class="table table-bordered table-hover table-sm spreadsheet-table">
                                <thead class="thead-dark sticky-top">
                                    <tr>
                                        <th class="text-center" style="min-width: 80px; position: sticky; left: 0; background-color: #343a40; z-index: 10;">
                                            <strong>Contestant #</strong>
                                        </th>
                                        <th class="text-center" style="min-width: 150px; position: sticky; left: 80px; background-color: #343a40; z-index: 10;">
                                            <strong>Contestant Name</strong>
                                        </th>
                                        <?php foreach ($criteria as $criterion): ?>
                                            <th class="text-center" style="min-width: 120px;">
                                                <div>
                                                    <strong><?= htmlspecialchars($criterion['name']) ?></strong>
                                                    <br>
                                                    <small class="text-light">Max: <?= number_format($criterion['max_score'], 2) ?></small>
                                                </div>
                                            </th>
                                        <?php endforeach; ?>
                                        <th class="text-center" style="min-width: 120px;">
                                            <strong>Total Score</strong>
                                            <br>
                                            <small class="text-light">Sum of criteria</small>
                                        </th>
                                        <th class="text-center" style="min-width: 100px; position: sticky; right: 0; background-color: #343a40; z-index: 10;">
                                            <strong>Actions</strong>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($judge['scores'] as $score): ?>
                                        <tr>
                                            <td class="text-center font-weight-bold" style="position: sticky; left: 0; background-color: inherit; z-index: 1;">
                                                #<?= htmlspecialchars($score['contestant_number']) ?>
                                            </td>
                                            <td class="font-weight-bold" style="position: sticky; left: 80px; background-color: inherit; z-index: 1;">
                                                <?= htmlspecialchars($score['contestant_name']) ?>
                                                <br>
                                                <?php if (!empty($score['is_submitted'])): ?>
                                                    <span class="badge badge-success badge-sm">
                                                        <i class="fas fa-check-circle"></i> Submitted
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-warning badge-sm">
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
                                                            (<?= number_format(($detail['raw_score'] / $criterion['max_score']) * 100, 1) ?>%)
                                                        </small>
                                                    <?php else: ?>
                                                        <span class="text-muted font-italic">No score</span>
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
                                            <td class="text-center" style="position: sticky; right: 0; background-color: inherit; z-index: 1;">
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
                        
                        <!-- Summary Statistics -->
                        <div class="row mt-3">
                            <div class="col-md-3">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <h6 class="card-title mb-1">Total Contestants</h6>
                                        <h3 class="text-primary mb-0"><?= count($judge['scores']) ?></h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <h6 class="card-title mb-1">Submitted Scores</h6>
                                        <h3 class="text-success mb-0">
                                            <?= count(array_filter($judge['scores'], fn($s) => !empty($s['is_submitted']))) ?>
                                        </h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <h6 class="card-title mb-1">Draft Scores</h6>
                                        <h3 class="text-warning mb-0">
                                            <?= count(array_filter($judge['scores'], fn($s) => empty($s['is_submitted']))) ?>
                                        </h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <h6 class="card-title mb-1">Completion</h6>
                                        <h3 class="text-info mb-0">
                                            <?php 
                                            $totalRequired = count($judge['scores']) * count($criteria);
                                            $totalFilled = 0;
                                            foreach ($judge['scores'] as $score) {
                                                if (!empty($score['details'])) {
                                                    $totalFilled += count($score['details']);
                                                }
                                            }
                                            $completionPercentage = $totalRequired > 0 ? ($totalFilled / $totalRequired) * 100 : 0;
                                            echo number_format($completionPercentage, 1) . '%';
                                            ?>
                                        </h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php $index++; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Info Alert -->
    <div class="alert alert-info">
        <h5><i class="icon fas fa-info"></i> Judge Completion Status</h5>
        <p class="mb-0">
            <strong>Tab Colors:</strong><br>
            <i class="fas fa-circle text-success"></i> <strong>Green:</strong> All contestants submitted scores (100% submitted)<br>
            <i class="fas fa-circle text-primary"></i> <strong>Blue:</strong> Some contestants submitted scores (partially submitted)<br>
            <i class="fas fa-circle text-info"></i> <strong>Light Blue:</strong> Scores entered but not submitted (draft mode)<br>
            <i class="fas fa-circle text-secondary"></i> <strong>Gray:</strong> No contestants scored yet (0% complete)<br>
            <small class="text-muted">Hover over judge tabs to see detailed completion status. Badge shows submission percentage.</small>
        </p>
    </div>
<?php endif; ?>

<script>
// Print functionality for individual judge sheets
function printJudgeSheet(judgeId) {
    const baseUrl = '/tabulation/score-management/print-round/<?= $round['id'] ?>';
    const printUrl = baseUrl + '/' + judgeId;
    window.open(printUrl, '_blank');
}

// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM ready, initializing tabs...');
    
    // Get all tab buttons
    const tabButtons = document.querySelectorAll('#judgeTabs button');
    console.log('Found tabs:', tabButtons.length);
    
    // Add click event listeners to each tab
    tabButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            console.log('Tab clicked!');
            e.preventDefault();
            
            // Get the judge ID from the clicked tab
            const judgeId = this.id.replace('judge-tab-', '');
            console.log('Judge ID extracted:', judgeId);
            
            // Remove active classes from all tabs and panes
            document.querySelectorAll('#judgeTabs button').forEach(function(btn) {
                btn.classList.remove('active', 'show');
            });
            
            document.querySelectorAll('#judgeTabContent .tab-pane').forEach(function(pane) {
                pane.classList.remove('show', 'active');
            });
            
            // Add active class to clicked tab
            this.classList.add('active');
            
            // Show the corresponding content pane
            const targetPane = document.getElementById('judge-content-' + judgeId);
            console.log('Target pane:', targetPane ? 'found' : 'not found');
            
            if (targetPane) {
                targetPane.classList.add('show', 'active');
                console.log('Pane shown successfully');
            } else {
                console.error('Target pane not found:', 'judge-content-' + judgeId);
            }
        });
    });
    
    // Check if Bootstrap 5 is available
    if (typeof bootstrap !== 'undefined') {
        console.log('Bootstrap 5 available, initializing tabs...');
        tabButtons.forEach(function(button) {
            new bootstrap.Tab(button);
        });
    } else {
        console.log('Bootstrap 5 not available, using vanilla JavaScript');
    }
});
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>