<?php 
$title = 'Event Assignments - ' . $event['name'];
require __DIR__ . '/../layout/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-users"></i> Event Assignments: <?= htmlspecialchars($event['name']) ?></h1>
    <a href="/tabulation/events/<?= $event['id'] ?>" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Event
    </a>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user-plus"></i> Assign Users to Event</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    Assign users to this event so they can access and manage it. 
                    Judges are automatically assigned when assigned to rounds.
                </div>
                
                <?php if (empty($users)): ?>
                    <p class="text-muted">No users available to assign.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= htmlspecialchars($user['full_name']) ?></td>
                                    <td>
                                        <span class="badge badge-info"><?= htmlspecialchars($user['role_name']) ?></span>
                                    </td>
                                    <td>
                                        <?php if ($user['is_assigned']): ?>
                                            <span class="badge badge-success">Assigned</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Not Assigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($user['is_assigned']): ?>
                                            <form method="POST" action="/tabulation/events/<?= $event['id'] ?>/assignments/unassign" class="d-inline">
                                                <input type="hidden" name="csrf_token" value="<?= Session::getCSRFToken() ?>">
                                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" 
                                                        onclick="event.preventDefault(); Swal.fire({title: 'Unassign User?', text: 'Unassign this user from the event?', icon: 'question', showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#6c757d', confirmButtonText: 'Yes, unassign', cancelButtonText: 'Cancel'}).then((result) => { if (result.isConfirmed) { event.target.closest('form').submit(); } }); return false;">
                                                    <i class="fas fa-times"></i> Unassign
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" action="/tabulation/events/<?= $event['id'] ?>/assignments/assign" class="d-inline">
                                                <input type="hidden" name="csrf_token" value="<?= Session::getCSRFToken() ?>">
                                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    <i class="fas fa-plus"></i> Assign
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-list"></i> Currently Assigned Users</h5>
            </div>
            <div class="card-body">
                <?php if (empty($assignedUsers)): ?>
                    <p class="text-muted">No users assigned to this event yet.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Role</th>
                                    <th>Assigned</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($assignedUsers as $user): ?>
                                <tr>
                                    <td><?= htmlspecialchars($user['full_name']) ?></td>
                                    <td>
                                        <span class="badge badge-info"><?= htmlspecialchars($user['role_name']) ?></span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= date('M d, Y', strtotime($user['assigned_at'])) ?>
                                        </small>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>

