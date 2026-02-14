<?php 
$title = 'Edit User';
require __DIR__ . '/../layout/header.php'; 
?>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title" style="font-weight: 700;">
                    <i class="fas fa-user-edit"></i> Edit User
                </h3>
                <div class="card-tools">
                    <a href="/tabulation/user-management" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>
            <form method="POST" action="/tabulation/user-management/<?= $user['id'] ?>/update">
                <input type="hidden" name="csrf_token" value="<?= Session::getCSRFToken() ?>">
                
                <div class="card-body">
                    <div class="alert alert-info">
                        <h5><i class="icon fas fa-info"></i> Editing: <strong><?= htmlspecialchars($user['full_name']) ?></strong></h5>
                        <p class="mb-0">Current Role: <strong><?= htmlspecialchars($user['role_name']) ?></strong></p>
                    </div>
                    
                    <div class="form-group">
                        <label for="full_name">
                            <i class="fas fa-user"></i> Full Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control form-control-lg" 
                               id="full_name" 
                               name="full_name" 
                               value="<?= htmlspecialchars($user['full_name']) ?>"
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
                                       value="<?= htmlspecialchars($user['username']) ?>"
                                       minlength="3"
                                       required>
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
                                       value="<?= htmlspecialchars($user['email']) ?>"
                                       required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="role_id">
                                    <i class="fas fa-user-tag"></i> Role <span class="text-danger">*</span>
                                </label>
                                <select class="form-control" id="role_id" name="role_id" required>
                                    <?php foreach ($availableRoles as $role): ?>
                                        <option value="<?= $role['id'] ?>" 
                                                <?= $user['role_id'] == $role['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($role['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if ($currentRole === 'Event Admin'): ?>
                                    <small class="form-text text-warning">
                                        <i class="fas fa-exclamation-triangle"></i> 
                                        You cannot change users to Super Admin or Event Admin roles
                                    </small>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="is_active">
                                    <i class="fas fa-toggle-on"></i> Status
                                </label>
                                <select class="form-control" id="is_active" name="is_active">
                                    <option value="1" <?= $user['is_active'] ? 'selected' : '' ?>>Active</option>
                                    <option value="0" <?= !$user['is_active'] ? 'selected' : '' ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock"></i> New Password <span class="text-muted">(Optional)</span>
                        </label>
                        <input type="password" 
                               class="form-control" 
                               id="password" 
                               name="password" 
                               placeholder="Leave blank to keep current password"
                               minlength="6">
                        <small class="form-text text-muted">
                            Only fill this if you want to change the user's password
                        </small>
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
                </div>
                
                <div class="card-footer">
                    <button type="submit" class="btn btn-warning btn-lg">
                        <i class="fas fa-save"></i> Update User
                    </button>
                    <a href="/tabulation/user-management" class="btn btn-secondary btn-lg">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>

