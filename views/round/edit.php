<?php 
$title = 'Edit Round - ' . htmlspecialchars($round['name']);
require __DIR__ . '/../layout/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-pencil"></i> Edit Round</h1>
    <a href="/tabulation/levels/<?= $level['id'] ?>/rounds" class="btn btn-secondary">Back to Rounds</a>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><?= htmlspecialchars($level['event_name']) ?> — <?= htmlspecialchars($level['name']) ?></h5>
    </div>
    <div class="card-body">
        <form method="POST" action="/tabulation/levels/<?= $level['id'] ?>/rounds/<?= $round['id'] ?>/update">
            <input type="hidden" name="csrf_token" value="<?= Session::getCSRFToken() ?>">
            <div class="mb-3">
                <label for="name" class="form-label">Round Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($round['name']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <input type="text" class="form-control" id="description" name="description" value="<?= htmlspecialchars($round['description'] ?? '') ?>" placeholder="Optional">
            </div>
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="Pending" <?= ($round['status'] ?? '') === 'Pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="Active" <?= ($round['status'] ?? '') === 'Active' ? 'selected' : '' ?>>Active</option>
                    <option value="Completed" <?= ($round['status'] ?? '') === 'Completed' ? 'selected' : '' ?>>Completed</option>
                </select>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check2"></i> Save Changes
                </button>
                <a href="/tabulation/levels/<?= $level['id'] ?>/rounds" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
