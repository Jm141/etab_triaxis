<?php 
$title = 'Criteria';
require __DIR__ . '/../layout/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-list-check"></i> Criteria: <?= htmlspecialchars($event['name']) ?></h1>
    <a href="/tabulation/events/<?= $event['id'] ?>" class="btn btn-secondary">Back</a>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Add New Criteria</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="/tabulation/events/<?= $event['id'] ?>/criteria/store">
            <input type="hidden" name="csrf_token" value="<?= Session::getCSRFToken() ?>">
            <div class="row">
                <div class="col-md-4 mb-2">
                    <input type="text" class="form-control" name="name" placeholder="Criteria Name" required>
                </div>
                <div class="col-md-3 mb-2">
                    <input type="number" class="form-control" name="max_score" placeholder="Max Score" step="0.01" required>
                </div>
                <div class="col-md-3 mb-2">
                    <input type="text" class="form-control" name="category" placeholder="Category (optional)">
                </div>
                <div class="col-md-2 mb-2">
                    <button type="submit" class="btn btn-primary w-100">Add</button>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-12">
                    <textarea class="form-control" name="description" placeholder="Description (optional)" rows="2"></textarea>
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
                        <th>Max Score</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($criteria as $criterion): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($criterion['name']) ?></strong></td>
                        <td><?= htmlspecialchars($criterion['max_score']) ?></td>
                        <td><?= htmlspecialchars($criterion['category'] ?: '-') ?></td>
                        <td><?= htmlspecialchars($criterion['description'] ?: '-') ?></td>
                        <td>
                            <div class="btn-group">
                                <a href="/tabulation/criteria-management/edit/<?= $criterion['id'] ?>" 
                                   class="btn btn-sm btn-warning" title="Edit Criterion">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form method="POST" 
                                      action="/tabulation/events/<?= $event['id'] ?>/criteria/<?= $criterion['id'] ?>/delete" 
                                      style="display: inline;"
                                      onsubmit="confirmDelete(event, 'This cannot be undone if it\'s already being used in rounds.', 'Delete Criterion?'); return false;">
                                    <input type="hidden" name="csrf_token" value="<?= Session::getCSRFToken() ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete Criterion">
                                        <i class="fas fa-trash"></i>
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



