<?php 
$title = 'All Judge Assignments';
require __DIR__ . '/../layout/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-user-shield"></i> All Judge Assignments (Tech Admin View)</h1>
</div>

<div class="alert alert-info">
    <i class="fas fa-info-circle"></i> <strong>Tech Admin View:</strong> This page shows all judge assignments including preparation-only assignments that are hidden from judges.
</div>

<?php if (empty($allAssignments)): ?>
    <div class="text-center py-5">
        <i class="fas fa-inbox" style="font-size: 3rem; color: #ccc;"></i>
        <h4 class="mt-3 text-muted">No Judge Assignments Found</h4>
        <p class="text-muted">No judges have been assigned to any rounds yet.</p>
    </div>
<?php else: ?>
    <?php
    // Group assignments by event, level, and judge
    $groupedAssignments = [];
    foreach ($allAssignments as $assignment) {
        $groupedAssignments[$assignment['event_id']][$assignment['level_name']][$assignment['judge_id']][] = $assignment;
    }
    ?>
    
    <?php foreach ($groupedAssignments as $eventId => $levels): ?>
        <?php foreach ($levels as $levelName => $judges): ?>
            <?php foreach ($judges as $judgeId => $judgeAssignments): ?>
                <?php $firstAssignment = $judgeAssignments[0]; ?>
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-trophy"></i> <?= htmlspecialchars($firstAssignment['event_name']) ?>
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
                                <div class="card mb-3">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="mb-0">
                                            <i class="fas fa-user"></i> <?= htmlspecialchars($firstAssignment['judge_name']) ?> 
                                            (<?= htmlspecialchars($firstAssignment['judge_number'] ?: 'No Number') ?>)
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <?php foreach ($judgeAssignments as $assignment): ?>
                                                <div class="col-md-6 col-lg-4 mb-3">
                                                    <div class="card h-100 <?= $assignment['is_preparation_only'] ? 'border-warning' : '' ?>">
                                                        <div class="card-body">
                                                            <h6 class="card-title">
                                                                <i class="fas fa-circle-notch"></i> <?= htmlspecialchars($assignment['round_name']) ?>
                                                                <?php if ($assignment['is_preparation_only']): ?>
                                                                    <span class="badge bg-warning ms-1" title="Preparation Only - Not visible to judge">
                                                                        <i class="fas fa-eye-slash"></i> Prep Only
                                                                    </span>
                                                                <?php endif; ?>
                                                            </h6>
                                                            <div class="mb-2">
                                                                <?php 
                                                                $submittedCount = $assignment['submitted_count'] ?? 0;
                                                                $totalContestants = $assignment['total_contestants'] ?? 0;
                                                                $percentage = $totalContestants > 0 ? ($submittedCount / $totalContestants * 100) : 0;
                                                                ?>
                                                                <small class="text-muted">Progress:</small>
                                                                <div class="progress mt-1" style="height: 20px;">
                                                                    <div class="progress-bar" role="progressbar" 
                                                                         style="width: <?= $percentage ?>%">
                                                                        <?= $submittedCount ?> / <?= $totalContestants ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="mb-2">
                                                                <span class="badge bg-<?= $assignment['status'] === 'Active' ? 'success' : 'secondary' ?>">
                                                                    <?= htmlspecialchars($assignment['status']) ?>
                                                                </span>
                                                            </div>
                                                            <div class="btn-group w-100">
                                                                <?php if ($assignment['is_preparation_only']): ?>
                                                                    <button class="btn btn-sm btn-warning" disabled title="Preparation Only - Not accessible to judge">
                                                                        <i class="fas fa-eye-slash"></i> Prep Only
                                                                    </button>
                                                                <?php else: ?>
                                                                    <a href="/tabulation/judge/rounds/<?= $assignment['round_id'] ?>/table" 
                                                                       class="btn btn-sm btn-primary" title="View scoring table">
                                                                        <i class="fas fa-table"></i> View
                                                                    </a>
                                                                <?php endif; ?>
                                                                <a href="/tabulation/judge-management/<?= $assignment['judge_id'] ?>/assign-rounds" 
                                                                   class="btn btn-sm btn-info" title="Edit assignments">
                                                                    <i class="fas fa-edit"></i> Edit
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    <?php endforeach; ?>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
