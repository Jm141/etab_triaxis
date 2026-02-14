<?php 
$title = 'Rounds';
require __DIR__ . '/../layout/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-list-ul"></i> Rounds: <?= htmlspecialchars($level['name']) ?></h1>
    <a href="/tabulation/events/<?= $level['event_id'] ?>" class="btn btn-secondary">Back</a>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Add New Round</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="/tabulation/levels/<?= $level['id'] ?>/rounds/store">
            <input type="hidden" name="csrf_token" value="<?= Session::getCSRFToken() ?>">
            <div class="row">
                <div class="col-md-5 mb-2">
                    <input type="text" class="form-control" name="name" placeholder="Round Name" required>
                </div>
                <div class="col-md-5 mb-2">
                    <input type="text" class="form-control" name="description" placeholder="Description">
                </div>
                <div class="col-md-2 mb-2">
                    <button type="submit" class="btn btn-primary w-100">Add</button>
                </div>
            </div>
            <div class="alert alert-info mt-2 mb-0">
                <small><i class="fas fa-info-circle"></i> <strong>Note:</strong> Elimination is based on the level's "Advance Count" setting, not individual rounds. All rounds in a level contribute to overall rankings.</small>
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
                        <th>Description</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rounds as $round): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($round['name']) ?></strong></td>
                        <td><?= htmlspecialchars($round['description'] ?: '-') ?></td>
                        <td>
                            <span class="badge bg-<?= $round['status'] === 'Active' ? 'success' : 'secondary' ?>">
                                <?= htmlspecialchars($round['status']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="/tabulation/levels/<?= $level['id'] ?>/rounds/<?= $round['id'] ?>/edit" class="btn btn-sm btn-warning" title="Edit Round">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <a href="/tabulation/rounds/<?= $round['id'] ?>/weights" class="btn btn-sm btn-primary">
                                    <i class="bi bi-sliders"></i> Set Weights
                                </a>
                                <form method="POST" action="/tabulation/levels/<?= $level['id'] ?>/rounds/<?= $round['id'] ?>/delete" 
                                      style="display: inline;" 
                                      onsubmit="confirmDelete(event, 'This will also delete all scores, rankings, and criteria weights associated with it. This action cannot be undone!', 'Delete Round?'); return false;">
                                    <input type="hidden" name="csrf_token" value="<?= Session::getCSRFToken() ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete Round">
                                        <i class="bi bi-trash"></i> Delete
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



