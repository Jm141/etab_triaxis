<?php 
$title = 'Create Judge';
require __DIR__ . '/../layout/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-person-plus"></i> Create New Judge</h1>
    <a href="/tabulation/judge-management" class="btn btn-secondary">Back</a>
</div>

<div class="card">
    <div class="card-body">
        <?php if (isset($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $field => $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="/tabulation/judge-management/store">
            <input type="hidden" name="csrf_token" value="<?= Session::getCSRFToken() ?>">
            
            <h5 class="mb-3">Judge Account Information</h5>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="username" class="form-label">Username *</label>
                    <input type="text" class="form-control" id="username" name="username" 
                           value="<?= htmlspecialchars($data['username'] ?? '') ?>" required>
                    <small class="text-muted">Used for login</small>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email *</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?= htmlspecialchars($data['email'] ?? '') ?>" required>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="full_name" class="form-label">Full Name *</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" 
                           value="<?= htmlspecialchars($data['full_name'] ?? '') ?>" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label">Password *</label>
                    <input type="password" class="form-control" id="password" name="password" 
                           minlength="6" required>
                    <small class="text-muted">Minimum 6 characters</small>
                </div>
            </div>
            
            <hr class="my-4">
            
            <h5 class="mb-3">Event Assignment (Optional)</h5>
            <p class="text-muted">You can assign this judge to an event now, or do it later.</p>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="event_id" class="form-label">Assign to Event</label>
                    <select class="form-select" id="event_id" name="event_id">
                        <option value="">-- Select Event (Optional) --</option>
                        <?php foreach ($events as $event): ?>
                            <option value="<?= $event['id'] ?>" 
                                    <?= (isset($data['event_id']) && $data['event_id'] == $event['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($event['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-3 mb-3">
                    <label for="judge_number" class="form-label">Judge Number</label>
                    <input type="text" class="form-control" id="judge_number" name="judge_number" 
                           value="<?= htmlspecialchars($data['judge_number'] ?? '') ?>" placeholder="Optional">
                </div>
                
                <div class="col-md-3 mb-3">
                    <label for="specialty" class="form-label">Specialty</label>
                    <input type="text" class="form-control" id="specialty" name="specialty" 
                           value="<?= htmlspecialchars($data['specialty'] ?? '') ?>" placeholder="Optional">
                </div>
            </div>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                <a href="/tabulation/judge-management" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Create Judge
                </button>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>



