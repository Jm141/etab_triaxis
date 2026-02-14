<?php 
$title = 'Edit Level';
require __DIR__ . '/../../layout/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-pencil"></i> Edit Level</h1>
    <a href="/tabulation/events/<?= $level['event_id'] ?>/levels" class="btn btn-secondary">Back</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="/tabulation/events/<?= $level['event_id'] ?>/levels/<?= $level['id'] ?>/update">
            <input type="hidden" name="csrf_token" value="<?= Session::getCSRFToken() ?>">
            <div class="mb-3">
                <label for="name" class="form-label">Level Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($level['name']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <input type="text" class="form-control" id="description" name="description" value="<?= htmlspecialchars($level['description'] ?? '') ?>" placeholder="Optional">
            </div>
            <div class="mb-3">
                <label for="advance_count" class="form-label">Advance Count</label>
                <input type="number" class="form-control" id="advance_count" name="advance_count" value="<?= htmlspecialchars($level['advance_count'] ?? '') ?>" placeholder="Optional">
            </div>
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="Pending" <?= $level['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="Active" <?= $level['status'] === 'Active' ? 'selected' : '' ?>>Active</option>
                    <option value="Completed" <?= $level['status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update Level</button>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../../layout/footer.php'; ?>