<?php 
$title = 'User Management';
require __DIR__ . '/../layout/header.php'; 
?>

<div class="row">
    <div class="col-md-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title" style="font-weight: 700;">
                    <i class="fas fa-users-cog mr-1"></i>
                    User Management
                </h3>
                <div class="card-tools">
                    <a href="/tabulation/user-management/create" class="btn btn-primary btn-sm">
                        <i class="fas fa-user-plus"></i> Add New User
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h5><i class="icon fas fa-info"></i> User Management Rules</h5>
                    <?php if ($currentRole === 'Super Admin'): ?>
                        <p class="mb-0">
                            <strong>Super Admin</strong> can create and manage all user types including Event Organizer and Event Admin.
                        </p>
                    <?php elseif ($currentRole === 'Event Organizer'): ?>
                        <p class="mb-0">
                            <strong>Event Organizer</strong> can create and manage: <strong>Event Technical Admin</strong>, <strong>Tabulator</strong>, <strong>Judge</strong>, <strong>Auditor</strong>, and <strong>Host</strong> users.
                            You cannot create or modify Super Admin, Event Admin, or Event Organizer accounts.
                        </p>
                    <?php else: ?>
                        <p class="mb-0">
                            <strong>Event Admin</strong> can create and manage: <strong>Tabulator</strong>, <strong>Judge</strong>, <strong>Auditor</strong>, and <strong>Host</strong> users.
                            You cannot create or modify Super Admin, Event Admin, or Event Organizer accounts.
                        </p>
                    <?php endif; ?>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Last Login</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted">No users found</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($user['full_name']) ?></strong></td>
                                <td><?= htmlspecialchars($user['username']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td>
                                    <span class="badge badge-info">
                                        <?= htmlspecialchars($user['role_name']) ?>
                                    </span>
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">
                                        <?= htmlspecialchars($user['role_description'] ?? '') ?>
                                    </small>
                                </td>
                                <td>
                                    <?php if ($user['id'] == Session::get('user_id')): ?>
                                        <span class="badge badge-success">You</span>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-<?= $user['is_active'] ? 'success' : 'secondary' ?> toggle-active" 
                                                data-user-id="<?= $user['id'] ?>"
                                                data-current-status="<?= $user['is_active'] ?>">
                                            <?= $user['is_active'] ? 'Active' : 'Inactive' ?>
                                        </button>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($user['last_login']): ?>
                                        <small><?= date('M d, Y H:i', strtotime($user['last_login'])) ?></small>
                                    <?php else: ?>
                                        <small class="text-muted">Never</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="/tabulation/user-management/edit/<?= $user['id'] ?>" 
                                           class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($user['id'] != Session::get('user_id')): ?>
                                            <?php if ($currentRole === 'Super Admin' || ($currentRole === 'Event Admin' && !in_array($user['role_name'], ['Super Admin', 'Event Admin']))): ?>
                                                <form method="POST" 
                                                      action="/tabulation/user-management/<?= $user['id'] ?>/delete" 
                                                      style="display: inline;"
                                                      onsubmit="confirmDelete(event, 'This action cannot be undone.', 'Delete User?'); return false;">
                                                    <input type="hidden" name="csrf_token" value="<?= Session::getCSRFToken() ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle user active status
document.querySelectorAll('.toggle-active').forEach(btn => {
    btn.addEventListener('click', function() {
        const userId = this.dataset.userId;
        const currentStatus = this.dataset.currentStatus === '1';
        
        Swal.fire({
            title: `${currentStatus ? 'Deactivate' : 'Activate'} User?`,
            text: `Are you sure you want to ${currentStatus ? 'deactivate' : 'activate'} this user?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (!result.isConfirmed) {
                return;
            }
        
        fetch(`/tabulation/user-management/${userId}/toggle-active`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `csrf_token=<?= Session::getCSRFToken() ?>`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire('Success!', 'User status updated successfully.', 'success').then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        })
        .catch(error => {
            Swal.fire('Error', 'Error updating user status', 'error');
        });
        });
    });
});
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>

