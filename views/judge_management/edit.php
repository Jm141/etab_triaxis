<?php 
$title = 'Edit Judge';
require __DIR__ . '/../layout/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-pencil"></i> Edit Judge</h1>
    <a href="/tabulation/judge-management" class="btn btn-secondary">Back</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="/tabulation/judge-management/<?= $judge['id'] ?>/update">
            <input type="hidden" name="csrf_token" value="<?= Session::getCSRFToken() ?>">
            
            <h5 class="mb-3">Judge Information</h5>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="full_name" class="form-label">Full Name *</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" 
                           value="<?= htmlspecialchars($judge['full_name']) ?>" required>
                </div>
                
                <div class="col-md-3 mb-3">
                    <label for="judge_number" class="form-label">Judge Number</label>
                    <input type="text" class="form-control" id="judge_number" name="judge_number" 
                           value="<?= htmlspecialchars($judge['judge_number'] ?? '') ?>" placeholder="Optional">
                </div>
                
                <div class="col-md-3 mb-3">
                    <label for="specialty" class="form-label">Specialty</label>
                    <input type="text" class="form-control" id="specialty" name="specialty" 
                           value="<?= htmlspecialchars($judge['specialty'] ?? '') ?>" placeholder="Optional">
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Event</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($judge['event_name']) ?>" disabled>
                <small class="text-muted">Event cannot be changed. Remove and reassign if needed.</small>
            </div>
            
            <hr class="my-4">
            
            <h5 class="mb-3">Change Password (Optional)</h5>
            <p class="text-muted">Leave blank to keep current password</p>
            
            <div class="mb-3">
                <label for="password" class="form-label">New Password</label>
                <input type="password" class="form-control" id="password" name="password" 
                       minlength="6" placeholder="Leave blank to keep current">
                <small class="text-muted">Minimum 6 characters</small>
            </div>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                <a href="/tabulation/judge-management" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Update Judge
                </button>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>



