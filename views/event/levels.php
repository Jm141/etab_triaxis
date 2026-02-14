<?php 
$title = 'Event Levels';
require __DIR__ . '/../layout/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-list-ol"></i> Levels: <?= htmlspecialchars($event['name']) ?></h1>
    <a href="/tabulation/events/<?= $event['id'] ?>" class="btn btn-secondary">Back</a>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Add New Level</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="/tabulation/events/<?= $event['id'] ?>/levels/store">
            <input type="hidden" name="csrf_token" value="<?= Session::getCSRFToken() ?>">
            <div class="row">
                <div class="col-md-4 mb-2">
                    <input type="text" class="form-control" name="name" placeholder="Level Name" required>
                </div>
                <div class="col-md-3 mb-2">
                    <input type="text" class="form-control" name="description" placeholder="Description">
                </div>
                <div class="col-md-3 mb-2">
                    <input type="number" class="form-control" name="advance_count" placeholder="Advance Count (e.g., 10)" min="1" title="Number of contestants that advance to next level (leave empty for all)">
                    <small class="text-muted">Leave empty if all contestants advance</small>
                </div>
                <div class="col-md-2 mb-2">
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
                        <th>Description</th>
                        <th>Advance Count</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($levels as $level): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($level['name']) ?></strong></td>
                        <td><?= htmlspecialchars($level['description'] ?: '-') ?></td>
                        <td>
                            <?php if ($level['advance_count']): ?>
                                <span class="badge bg-info">Top <?= $level['advance_count'] ?></span>
                            <?php else: ?>
                                <span class="badge bg-secondary">All Advance</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-<?= $level['status'] === 'Active' ? 'success' : 'secondary' ?>">
                                <?= htmlspecialchars($level['status']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="/tabulation/levels/<?= $level['id'] ?>/rounds" class="btn btn-sm btn-primary">
                                    <i class="bi bi-arrow-right"></i> Manage Rounds
                                </a>
                                <form method="POST" action="/tabulation/events/<?= $event['id'] ?>/levels/<?= $level['id'] ?>/delete" 
                                      style="display: inline;" 
                                      onsubmit="confirmDelete(event, 'This will also delete all rounds, scores, and rankings associated with it. This action cannot be undone!', 'Delete Level?'); return false;">
                                    <input type="hidden" name="csrf_token" value="<?= Session::getCSRFToken() ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete Level">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                <!-- </form> <form method="POST" action="/tabulation/events/<?= $event['id'] ?>/levels/<?= $level['id'] ?>/edit"   
                                      style="display: inline;" 
                                      onsubmit="confirmDelete(event, 'This will also delete all rounds, scores, and rankings associated with it. This action cannot be undone!', 'Delete Level?'); return false;">
                                    <input type="hidden" name="csrf_token" value="<?= Session::getCSRFToken() ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete Level">
                                        <i class="bi bi-pencil"></i> edit 
                                    </button>
                                </form>  -->
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



