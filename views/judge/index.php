<?php 
$title = 'Judges';
require __DIR__ . '/../layout/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-people"></i> Judges: <?= htmlspecialchars($event['name']) ?></h1>
    <a href="/tabulation/events/<?= $event['id'] ?>" class="btn btn-secondary">Back</a>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Assign Judge</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="/tabulation/events/<?= $event['id'] ?>/judges/store">
            <input type="hidden" name="csrf_token" value="<?= Session::getCSRFToken() ?>">
            <div class="row">
                <div class="col-md-5 mb-2">
                    <select class="form-select" name="user_id" required>
                        <option value="">Select Judge</option>
                        <?php foreach ($availableUsers as $user): ?>
                            <option value="<?= $user['id'] ?>">
                                <?= htmlspecialchars($user['full_name']) ?> (<?= htmlspecialchars($user['username']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <input type="text" class="form-control" name="judge_number" placeholder="Judge Number">
                </div>
                <div class="col-md-3 mb-2">
                    <input type="text" class="form-control" name="specialty" placeholder="Specialty">
                </div>
                <div class="col-md-1 mb-2">
                    <button type="submit" class="btn btn-primary w-100">Add</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Judge #</th>
                        <th>Specialty</th>
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
                        <td><?= htmlspecialchars($judge['specialty'] ?: '-') ?></td>
                        <td><span class="badge bg-info"><?= $judge['assigned_rounds'] ?></span></td>
                        <td>
                            <div class="btn-group">
                                <a href="/tabulation/judge-management/<?= $judge['id'] ?>/assign-rounds" 
                                   class="btn btn-sm btn-primary" title="Assign Rounds">
                                    <i class="bi bi-list-check"></i> Assign Rounds
                                </a>
                                <form method="POST" action="/tabulation/events/<?= $event['id'] ?>/judges/<?= $judge['id'] ?>/delete" 
                                      style="display: inline;" onsubmit="confirmDelete(event, 'Remove this judge?', 'Remove Judge?'); return false;">
                                    <input type="hidden" name="csrf_token" value="<?= Session::getCSRFToken() ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" title="Remove Judge">
                                        <i class="bi bi-trash"></i> Remove
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>



