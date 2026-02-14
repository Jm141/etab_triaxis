<?php 
$title = 'Edit Contestant';
require __DIR__ . '/../layout/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-pencil"></i> Edit Contestant</h1>
    <a href="/tabulation/events/<?= $event['id'] ?>/contestants" class="btn btn-secondary">Back</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="/tabulation/events/<?= $event['id'] ?>/contestants/<?= $contestant['id'] ?>/update">
            <input type="hidden" name="csrf_token" value="<?= Session::getCSRFToken() ?>">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="contestant_number" class="form-label">Contestant Number *</label>
                    <input type="text" class="form-control" id="contestant_number" name="contestant_number" 
                           value="<?= htmlspecialchars($contestant['contestant_number']) ?>" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Name *</label>
                    <input type="text" class="form-control" id="name" name="name" 
                           value="<?= htmlspecialchars($contestant['name']) ?>" required>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="team_name" class="form-label">Team Name</label>
                    <input type="text" class="form-control" id="team_name" name="team_name" 
                           value="<?= htmlspecialchars($contestant['team_name'] ?? '') ?>">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="category" class="form-label">Category</label>
                    <input type="text" class="form-control" id="category" name="category" 
                           value="<?= htmlspecialchars($contestant['category'] ?? '') ?>">
                </div>
            </div>
            
            <div class="mb-3">
                <label for="bio" class="form-label">Bio</label>
                <textarea class="form-control" id="bio" name="bio" rows="3"><?= htmlspecialchars($contestant['bio'] ?? '') ?></textarea>
            </div>
            
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="Active" <?= $contestant['status'] === 'Active' ? 'selected' : '' ?>>Active</option>
                    <option value="Disqualified" <?= $contestant['status'] === 'Disqualified' ? 'selected' : '' ?>>Disqualified</option>
                    <option value="Withdrawn" <?= $contestant['status'] === 'Withdrawn' ? 'selected' : '' ?>>Withdrawn</option>
                </select>
            </div>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="/tabulation/events/<?= $event['id'] ?>/contestants" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Update Contestant
                </button>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>



