<?php 
$title = $event['name'];
require __DIR__ . '/../layout/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-calendar-event"></i> <?= htmlspecialchars($event['name']) ?></h1>
    <div>
        <?php if (in_array(Session::get('role_name'), ['Super Admin', 'Event Technical Admin'])): ?>
        <a href="/tabulation/events/<?= $event['id'] ?>/reports" class="btn btn-info">
            <i class="bi bi-file-text"></i> Reports
        </a>
        <?php endif; ?>
        <a href="/tabulation/events/<?= $event['id'] ?>/edit" class="btn btn-secondary">
            <i class="bi bi-pencil"></i> Edit
        </a>
        <a href="/tabulation/events" class="btn btn-outline-secondary">Back</a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Event Details</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="150">Type:</th>
                        <td><?= htmlspecialchars($event['event_type']) ?></td>
                    </tr>
                    <tr>
                        <th>Date:</th>
                        <td><?= htmlspecialchars($event['event_date']) ?></td>
                    </tr>
                    <tr>
                        <th>Venue:</th>
                        <td><?= htmlspecialchars($event['venue'] ?: 'N/A') ?></td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td>
                            <span class="badge bg-<?= $event['status'] === 'Ongoing' ? 'success' : ($event['status'] === 'Finished' ? 'secondary' : 'warning') ?>">
                                <?= htmlspecialchars($event['status']) ?>
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-list-ol"></i> Competition Levels</h5>
                <a href="/tabulation/events/<?= $event['id'] ?>/levels" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus"></i> Add Level
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($levels)): ?>
                    <p class="text-muted">No levels created yet.</p>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($levels as $level): ?>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1"><?= htmlspecialchars($level['name']) ?></h6>
                                    <small class="text-muted"><?= htmlspecialchars($level['description'] ?: 'No description') ?></small>
                                </div>
                                <div>
                                    <span class="badge bg-<?= $level['status'] === 'Active' ? 'success' : 'secondary' ?> me-2">
                                        <?= htmlspecialchars($level['status']) ?>
                                    </span>
                                    <a href="/tabulation/levels/<?= $level['id'] ?>/rounds" class="btn btn-sm btn-primary">
                                        <i class="bi bi-arrow-right"></i> Manage Rounds
                                    </a>
                                    <a href="/tabulation/events/<?= $event['id'] ?>/levels/<?= $level['id'] ?>/edit" class="btn btn-sm btn-primary">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-person-badge"></i> Contestants (<?= count($contestants) ?>)</h5>
            </div>
            <div class="card-body">
                <a href="/tabulation/events/<?= $event['id'] ?>/contestants" class="btn btn-primary w-100 mb-2">
                    <i class="bi bi-people"></i> Manage Contestants
                </a>
                <a href="/tabulation/events/<?= $event['id'] ?>/criteria" class="btn btn-outline-primary w-100 mb-2">
                    <i class="bi bi-list-check"></i> Manage Criteria
                </a>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-people"></i> Judges (<?= count($judges) ?>)</h5>
            </div>
            <div class="card-body">
                <a href="/tabulation/events/<?= $event['id'] ?>/judges" class="btn btn-primary w-100">
                    <i class="bi bi-person-check"></i> Manage Judges for This Event
                </a>
            </div>
        </div>
        
        <?php if (Session::get('role_name') === 'Super Admin' || Session::get('role_name') === 'Event Organizer'): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-person-check"></i> User Assignments</h5>
            </div>
            <div class="card-body">
                <a href="/tabulation/events/<?= $event['id'] ?>/assignments" class="btn btn-info w-100">
                    <i class="bi bi-person-plus"></i> Assign Users to Event
                </a>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-key"></i> Organizer Key</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($event['organizer_key'])): ?>
                    <div class="alert alert-success">
                        <strong>Your Organizer Key:</strong><br>
                        <code id="organizer-key" style="font-size: 1.1em; padding: 8px; background: #f0f0f0; display: block; margin: 10px 0; word-break: break-all;">
                            <?= htmlspecialchars($event['organizer_key']) ?>
                        </code>
                        <button class="btn btn-sm btn-primary" onclick="copyToClipboard('<?= htmlspecialchars($event['organizer_key']) ?>', 'organizer-key')">
                            <i class="fas fa-copy"></i> Copy Key
                        </button>
                        <small class="d-block text-muted mt-2">
                            Share this key with Technical Admins when they need to edit scores or apply deductions.
                        </small>
                    </div>
                    <form method="POST" action="/tabulation/events/<?= $event['id'] ?>/regenerate-organizer-key" 
                          onsubmit="return confirm('Are you sure you want to regenerate the key? The old key will no longer work.');">
                        <input type="hidden" name="csrf_token" value="<?= Session::getCSRFToken() ?>">
                        <button type="submit" class="btn btn-warning w-100">
                            <i class="fas fa-sync"></i> Regenerate Key
                        </button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-info">
                        <p class="mb-2">Generate a master key that Technical Admins can use to edit scores and apply deductions.</p>
                    </div>
                    <form method="POST" action="/tabulation/events/<?= $event['id'] ?>/generate-organizer-key">
                        <input type="hidden" name="csrf_token" value="<?= Session::getCSRFToken() ?>">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-key"></i> Generate Organizer Key
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-trophy"></i> Results & Reports</h5>
            </div>
            <div class="card-body">
                <a href="/tabulation/events/<?= $event['id'] ?>/results" class="btn btn-success w-100 mb-2">
                    <i class="bi bi-graph-up"></i> View Results
                </a>
                <?php if (in_array(Session::get('role_name'), ['Super Admin', 'Event Technical Admin'])): ?>
                <!-- Summary Button with Level and Round Selection -->
                <div class="dropdown mb-2">
                    <button class="btn btn-info w-100 dropdown-toggle" type="button" id="summaryDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="bi bi-file-text"></i> Summary
                    </button>
                    <div class="dropdown-menu w-100" aria-labelledby="summaryDropdown" style="max-height: 400px; overflow-y: auto;">
                        <?php if (empty($levels) && empty($allRounds)): ?>
                            <span class="dropdown-item-text text-muted">No levels or rounds available</span>
                        <?php else: ?>
                            <?php foreach ($levels as $level): ?>
                                <h6 class="dropdown-header">
                                    <i class="bi bi-layers"></i> <?= htmlspecialchars($level['name']) ?>
                                </h6>
                                <!-- Level Report Link -->
                                <a class="dropdown-item" href="/tabulation/events/<?= $event['id'] ?>/reports/level/<?= $level['id'] ?>" style="font-weight: 600; color: #0d6efd;">
                                    <i class="bi bi-file-text"></i> <?= htmlspecialchars($level['name']) ?> Report
                                </a>
                                <!-- Rounds in this level -->
                                <?php 
                                $levelRounds = array_filter($allRounds, function($round) use ($level) {
                                    return isset($round['level_id']) && $round['level_id'] == $level['id'];
                                });
                                if (!empty($levelRounds)): 
                                ?>
                                    <?php foreach ($levelRounds as $round): ?>
                                        <a class="dropdown-item pl-4" href="/tabulation/events/<?= $event['id'] ?>/reports/round/<?= $round['id'] ?>">
                                            <i class="bi bi-circle"></i> <?= htmlspecialchars($round['name']) ?>
                                        </a>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="dropdown-item-text text-muted pl-4" style="font-size: 0.875rem;">No rounds in this level</span>
                                <?php endif; ?>
                                <div class="dropdown-divider"></div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <a href="/tabulation/events/<?= $event['id'] ?>/reports" class="btn btn-outline-info w-100 mb-2">
                    <i class="bi bi-file-text"></i> All Reports
                </a>
                <?php if (in_array(Session::get('role_name'), ['Super Admin', 'Event Admin', 'Event Technical Admin', 'Tabulator']) && (!empty($levels) || !empty($allRounds))): ?>
                <div class="dropdown">
                    <button class="btn btn-warning w-100 dropdown-toggle" type="button" id="scoreMgmtDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="bi bi-pencil-square"></i> View Scores (Score Management)
                    </button>
                    <div class="dropdown-menu w-100" aria-labelledby="scoreMgmtDropdown" style="max-height: 400px; overflow-y: auto;">
                        <?php foreach ($levels as $level): ?>
                            <?php 
                            $levelRounds = array_filter($allRounds, function($r) use ($level) {
                                return isset($r['level_id']) && $r['level_id'] == $level['id'];
                            });
                            if (empty($levelRounds)) continue;
                            ?>
                            <h6 class="dropdown-header"><i class="bi bi-layers"></i> <?= htmlspecialchars($level['name']) ?></h6>
                            <?php foreach ($levelRounds as $round): ?>
                                <a class="dropdown-item" href="/tabulation/score-management/round/<?= $round['id'] ?>">
                                    <i class="bi bi-circle"></i> <?= htmlspecialchars($round['name']) ?>
                                </a>
                            <?php endforeach; ?>
                            <div class="dropdown-divider"></div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>

