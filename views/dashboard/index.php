<?php 
$title = 'Dashboard';
require __DIR__ . '/../layout/header.php'; 
?>

<?php if (Session::get('role_name') !== 'Judge'): ?>
<!-- Small boxes (Stat box) -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box">
            <div class="inner">
                <h3><?= $total_events ?? 0 ?></h3>
                <p>Total Events</p>
            </div>
            <div class="icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <a href="/tabulation/events" class="small-box-footer">
                More info <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box">
            <div class="inner">
                <h3><?= $active_events ?? 0 ?></h3>
                <p>Active Events</p>
            </div>
            <div class="icon">
                <i class="fas fa-play-circle"></i>
            </div>
            <a href="/tabulation/events" class="small-box-footer">
                More info <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box">
            <div class="inner">
                <h3><?= $total_judges ?? 0 ?></h3>
                <p>Active Judges</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
            <a href="/tabulation/judge-management" class="small-box-footer">
                More info <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box">
            <div class="inner">
                <h3><?= $total_contestants ?? 0 ?></h3>
                <p>Active Contestants</p>
            </div>
            <div class="icon">
                <i class="fas fa-user-friends"></i>
            </div>
            <a href="/tabulation/events" class="small-box-footer">
                More info <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (Session::get('role_name') !== 'Judge' && isset($recent_events) && !empty($recent_events)): ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title" style="font-weight: 700;"><i class="fas fa-clock mr-1"></i> Recent Events</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_events as $event): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($event['name']) ?></strong></td>
                            <td><?= htmlspecialchars($event['event_type']) ?></td>
                            <td><?= htmlspecialchars($event['event_date']) ?></td>
                            <td>
                                <?php
                                $badgeClass = 'secondary';
                                if ($event['status'] === 'Ongoing') $badgeClass = 'success';
                                elseif ($event['status'] === 'Draft') $badgeClass = 'warning';
                                ?>
                                <span class="badge badge-<?= $badgeClass ?>">
                                    <?= htmlspecialchars($event['status']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="/tabulation/events/<?= $event['id'] ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (isset($assigned_rounds) && !empty($assigned_rounds)): ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title" style="font-weight: 700;"><i class="fas fa-clipboard-check mr-1"></i> My Assigned Rounds</h3>
            </div>
            <div class="card-body">
                <?php
                // Group rounds by event and level
                $groupedRounds = [];
                foreach ($assigned_rounds as $round) {
                    $groupedRounds[$round['event_id']][$round['level_name']][] = $round;
                }
                ?>
                
                <?php foreach ($groupedRounds as $eventId => $levels): ?>
                    <?php foreach ($levels as $levelName => $levelRounds): ?>
                        <?php $firstRound = $levelRounds[0]; ?>
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-trophy"></i> <?= htmlspecialchars($firstRound['event_name']) ?>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="card mb-3">
                                    <div class="card-header bg-secondary text-white">
                                        <h6 class="mb-0">
                                            <i class="fas fa-layer-group"></i> <?= htmlspecialchars($levelName) ?>
                                        </h6>
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
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
