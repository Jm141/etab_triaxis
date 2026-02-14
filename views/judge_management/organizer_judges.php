<?php 
$title = 'My Judges: ' . htmlspecialchars($event['name']);
require __DIR__ . '/../layout/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-users"></i> My Judges: <?= htmlspecialchars($event['name']) ?></h1>
    <a href="/tabulation/events/<?= $event['id'] ?>" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Event
    </a>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-user-plus"></i> Assign Judge to Event</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="/tabulation/judge-management/assign-to-organizer" class="mb-3">
            <input type="hidden" name="csrf_token" value="<?= Session::getCSRFToken() ?>">
            <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
            <div class="row">
                <div class="col-md-8 mb-2">
                    <select class="form-select" name="judge_id" required>
                        <option value="">Select Judge to Assign</option>
                        <?php foreach ($availableJudges as $judge): ?>
                            <option value="<?= $judge['id'] ?>">
                                <?= htmlspecialchars($judge['full_name']) ?> (<?= htmlspecialchars($judge['username']) ?>)
                                <?php if ($judge['judge_number']): ?>
                                    - Judge #<?= htmlspecialchars($judge['judge_number']) ?>
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 mb-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-plus"></i> Assign Judge
                    </button>
                </div>
            </div>
        </form>
        
        <?php if (empty($availableJudges)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> All judges for this event are already assigned to you.
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-list"></i> My Assigned Judges</h5>
    </div>
    <div class="card-body">
        <?php if (empty($judges)): ?>
            <div class="text-center py-5">
                <i class="fas fa-users" style="font-size: 3rem; color: #ccc;"></i>
                <h4 class="mt-3 text-muted">No Judges Assigned</h4>
                <p class="text-muted">You haven't assigned any judges to this event yet.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Judge #</th>
                            <th>Assigned Rounds</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($judges as $judge): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($judge['full_name']) ?></strong></td>
                            <td><?= htmlspecialchars($judge['username']) ?></td>
                            <td><?= htmlspecialchars($judge['judge_number'] ?: '-') ?></td>
                            <td><span class="badge bg-info"><?= $judge['assigned_rounds'] ?></span></td>
                            <td>
                                <div class="btn-group">
                                    <a href="/tabulation/judge-management/<?= $judge['id'] ?>/assign-rounds" 
                                       class="btn btn-sm btn-primary" title="Assign Rounds">
                                        <i class="fas fa-list-check"></i> Assign Rounds
                                    </a>
                                    <form method="POST" action="/tabulation/judge-management/remove-from-organizer" 
                                          style="display: inline;" onsubmit="confirmDelete(event, 'Remove this judge from your event?', 'Remove Judge?'); return false;">
                                        <input type="hidden" name="csrf_token" value="<?= Session::getCSRFToken() ?>">
                                        <input type="hidden" name="assignment_id" value="<?= $judge['assignment_id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" title="Remove Judge">
                                            <i class="fas fa-trash"></i> Remove
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function confirmDelete(event, message, title) {
    if (!confirm(message)) {
        event.preventDefault();
        return false;
    }
    return true;
}
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
