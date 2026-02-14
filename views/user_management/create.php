<?php 
$title = 'Add New User';
require __DIR__ . '/../layout/header.php'; 
?>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title" style="font-weight: 700;">
                    <i class="fas fa-user-plus"></i> Create New User
                </h3>
                <div class="card-tools">
                    <a href="/tabulation/user-management" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>
            <form method="POST" action="/tabulation/user-management/store">
                <input type="hidden" name="csrf_token" value="<?= Session::getCSRFToken() ?>">
                
                <div class="card-body">
                    <div class="alert alert-info">
                        <h5><i class="icon fas fa-info"></i> User Creation Rules</h5>
                        <?php if ($currentRole === 'Super Admin'): ?>
                            <p class="mb-0">You can create users with any role including <strong>Event Organizer</strong> and <strong>Event Admin</strong>.</p>
                        <?php elseif ($currentRole === 'Event Organizer'): ?>
                            <p class="mb-0">You can create: <strong>Event Technical Admin</strong>, <strong>Tabulator</strong>, <strong>Judge</strong>, <strong>Auditor</strong>, and <strong>Host</strong> users.</p>
                        <?php else: ?>
                            <p class="mb-0">You can create: <strong>Tabulator</strong>, <strong>Judge</strong>, <strong>Auditor</strong>, and <strong>Host</strong> users.</p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="full_name">
                            <i class="fas fa-user"></i> Full Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control form-control-lg" 
                               id="full_name" 
                               name="full_name" 
                               value="<?= htmlspecialchars($data['full_name'] ?? '') ?>"
                               placeholder="e.g., John Doe"
                               required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="username">
                                    <i class="fas fa-at"></i> Username <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="username" 
                                       name="username" 
                                       value="<?= htmlspecialchars($data['username'] ?? '') ?>"
                                       placeholder="e.g., johndoe"
                                       minlength="3"
                                       required>
                                <small class="form-text text-muted">Minimum 3 characters, used for login</small>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">
                                    <i class="fas fa-envelope"></i> Email <span class="text-danger">*</span>
                                </label>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       value="<?= htmlspecialchars($data['email'] ?? '') ?>"
                                       placeholder="e.g., john@example.com"
                                       required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password">
                                    <i class="fas fa-lock"></i> Password <span class="text-danger">*</span>
                                </label>
                                <input type="password" 
                                       class="form-control" 
                                       id="password" 
                                       name="password" 
                                       placeholder="Minimum 6 characters"
                                       minlength="6"
                                       required>
                                <small class="form-text text-muted">User will use this to login</small>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="role_id">
                                    <i class="fas fa-user-tag"></i> Role <span class="text-danger">*</span>
                                </label>
                                <select class="form-control" id="role_id" name="role_id" required>
                                    <option value="">-- Select Role --</option>
                                    <?php foreach ($availableRoles as $role): ?>
                                        <option value="<?= $role['id'] ?>" 
                                                <?= (isset($data['role_id']) && $data['role_id'] == $role['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($role['name']) ?>
                                            <?php if ($role['description']): ?>
                                                - <?= htmlspecialchars($role['description']) ?>
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="form-text text-muted" id="role-description"></small>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (isset($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Role Descriptions -->
                    <div class="card card-info collapsed-card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-question-circle"></i> Role Descriptions
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled">
                                <?php foreach ($availableRoles as $role): ?>
                                    <li class="mb-2">
                                        <strong><?= htmlspecialchars($role['name']) ?>:</strong>
                                        <?= htmlspecialchars($role['description'] ?? 'No description') ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save"></i> Create User
                    </button>
                    <a href="/tabulation/user-management" class="btn btn-secondary btn-lg">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Show role description when selected
document.getElementById('role_id').addEventListener('change', function() {
    const roleId = this.value;
    const selectedOption = this.options[this.selectedIndex];
    const description = selectedOption.text.split(' - ')[1] || '';
    document.getElementById('role-description').textContent = description || 'Select a role to see description';
});
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>

