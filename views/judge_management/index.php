<?php 
$title = 'Judge Management';
require __DIR__ . '/../layout/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-people-fill"></i> 
        <?php if ($isOrganizer ?? false): ?>
            My Judges
        <?php else: ?>
            Judge Management
        <?php endif; ?>
    </h1>
    <div>
        <?php if (!$isOrganizer): ?>
            <a href="/tabulation/judge-management/download-template" class="btn btn-info">
                <i class="fas fa-download"></i> Download CSV Template
            </a>
            <a href="/tabulation/judge-management/create" class="btn btn-primary">
                <i class="bi bi-person-plus"></i> Create New Judge
            </a>
        <?php endif; ?>
    </div>
</div>

<?php if ($isOrganizer ?? false): ?>
    <!-- Event Organizer View -->
    <div class="alert alert-info mb-4">
        <i class="fas fa-info-circle"></i> 
        <strong>Event Organizer View:</strong> You can only see and manage judges assigned to your events. 
        <a href="/tabulation/events" class="alert-link">Click here to view your events and manage judges for each event.</a>
    </div>
    
    <?php if (empty($judges)): ?>
        <div class="text-center py-5">
            <i class="fas fa-users" style="font-size: 3rem; color: #ccc;"></i>
            <h4 class="mt-3 text-muted">No Judges Assigned</h4>
            <p class="text-muted">You haven't assigned any judges to your events yet.</p>
            <a href="/tabulation/events" class="btn btn-primary">
                <i class="fas fa-calendar-alt"></i> View My Events
            </a>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-list"></i> My Assigned Judges</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Judge #</th>
                                <th>Specialty</th>
                                <th>Events</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($judges as $judge): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($judge['event_name']) ?></strong></td>
                                <td><strong><?= htmlspecialchars($judge['full_name']) ?></strong></td>
                                <td><?= htmlspecialchars($judge['username']) ?></td>
                                <td><?= htmlspecialchars($judge['judge_number'] ?: '-') ?></td>
                                <td><?= htmlspecialchars($judge['specialty'] ?: '-') ?></td>
                                <td><span class="badge bg-info"><?= $judge['assigned_rounds'] ?> rounds</span></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="/tabulation/judge-management/<?= $judge['id'] ?>/assign-rounds" 
                                           class="btn btn-sm btn-primary" title="Assign Rounds">
                                            <i class="fas fa-list-check"></i> Assign Rounds
                                        </a>
                                        <form method="POST" action="/tabulation/judge-management/remove-from-organizer" 
                                              style="display: inline;" onsubmit="confirmDelete(event, 'Remove this judge from your event?', 'Remove Judge?'); return false;">
                                            <input type="hidden" name="csrf_token" value="<?= Session::getCSRFToken() ?>">
                                            <input type="hidden" name="assignment_id" value="<?= $judge['assignment_id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" title="Remove Judge">
                                                <i class="fas fa-trash"></i> Remove
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
    <?php endif; ?>
<?php else: ?>
    <!-- Admin/Tech Admin View -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-file-upload"></i> Import Judges from CSV</h5>
        </div>
        <div class="card-body">
            <div class="alert alert-info mb-3">
                <h6><i class="fas fa-info-circle"></i> CSV Import Instructions</h6>
                <p class="mb-2">
                    <strong>CSV Format:</strong> username, email, full_name, password, event_id, judge_number, specialty
                </p>
                <ul class="mb-0">
                    <li><strong>Required fields:</strong> username, email, full_name, password</li>
                    <li><strong>Optional fields:</strong> event_id, judge_number, specialty</li>
                    <li>First row must be the header row</li>
                    <li>Duplicate usernames/emails will be skipped</li>
                    <li>Password must be at least 6 characters</li>
                    <li>If event_id is provided, judge will be automatically assigned to that event</li>
                </ul>
            </div>
            
            <form method="POST" action="/tabulation/judge-management/import" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= Session::getCSRFToken() ?>">
                <div class="row">
                    <div class="col-md-8">
                        <label for="csv_file" class="form-label">Select CSV File</label>
                        <input type="file" class="form-control" name="csv_file" id="csv_file" accept=".csv" required>
                        <small class="form-text text-muted">
                            <i class="fas fa-file-csv"></i> Upload a CSV file with judge data
                        </small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-success w-100 btn-lg">
                            <i class="fas fa-upload"></i> Import Judges
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-list"></i> All Judges</h5>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearFilters()">
                            <i class="fas fa-times"></i> Clear Filters
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filter Form -->
                    <form method="GET" action="/tabulation/judge-management" id="filterForm" class="mb-3">
                        <div class="row g-3">
                            <div class="col-md-5">
                                <label for="filter_judge" class="form-label">
                                    <i class="fas fa-search"></i> Filter by Judge
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="filter_judge" 
                                       name="filter_judge" 
                                       placeholder="Search by name, username, or judge number..."
                                       value="<?= htmlspecialchars($filterJudge ?? '') ?>">
                            </div>
                            <div class="col-md-5">
                                <label for="filter_round" class="form-label">
                                    <i class="fas fa-clipboard-list"></i> Filter by Round
                                </label>
                                <select class="form-select" id="filter_round" name="filter_round">
                                    <option value="">All Rounds</option>
                                    <?php if (!empty($rounds)): ?>
                                        <?php foreach ($rounds as $round): ?>
                                            <option value="<?= $round['id'] ?>" 
                                                    <?= (isset($filterRound) && $filterRound == $round['id']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($round['event_name']) ?> - 
                                                    <?= htmlspecialchars($round['level_name']) ?> - 
                                                    <?= htmlspecialchars($round['name']) ?>
                                                </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                            </div>
                        </div>
                    </form>
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Event</th>
                                    <th>Judge #</th>
                                    <th>Specialty</th>
                                    <th>Rounds</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($judges)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">No judges assigned yet</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($judges as $judge): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($judge['full_name']) ?></strong></td>
                                        <td><?= htmlspecialchars($judge['username']) ?></td>
                                        <td><?= htmlspecialchars($judge['email']) ?></td>
                                        <td>
                                            <a href="/tabulation/events/<?= $judge['event_id'] ?>">
                                                <?= htmlspecialchars($judge['event_name']) ?>
                                            </a>
                                        </td>
                                        <td><?= htmlspecialchars($judge['judge_number'] ?: '-') ?></td>
                                        <td><?= htmlspecialchars($judge['specialty'] ?: '-') ?></td>
                                        <td><span class="badge bg-info"><?= $judge['assigned_rounds'] ?> rounds</span></td>
                                        <td>
                                            <?php if ($judge['user_active']): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="/tabulation/judge-management/<?= $judge['id'] ?>/assign-rounds" 
                                                   class="btn btn-primary" title="Assign Rounds">
                                                    <i class="bi bi-list-check"></i>
                                                </a>
                                                <a href="/tabulation/judge-management/<?= $judge['id'] ?>/edit" 
                                                   class="btn btn-secondary" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form method="POST" 
                                                      action="/tabulation/judge-management/<?= $judge['id'] ?>/remove-from-event" 
                                                      style="display: inline;" 
                                                      onsubmit="confirmDelete(event, 'Remove this judge from event?', 'Remove Judge?'); return false;">
                                                    <input type="hidden" name="csrf_token" value="<?= Session::getCSRFToken() ?>">
                                                    <button type="submit" class="btn btn-danger" title="Remove Judge">
                                                        <i class="bi bi-x-circle"></i>
                                                    </button>
                                                </form>
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
</div>

<!-- Assign to Event Modal -->
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Judge to Event</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="assignForm" method="POST" action="/tabulation/judge-management/assign-to-event">
                <input type="hidden" name="csrf_token" value="<?= Session::getCSRFToken() ?>">
                <input type="hidden" name="user_id" id="assign_user_id">
                <div class="modal-body">
                    <p>Assigning: <strong id="assign_judge_name"></strong></p>
                    <div class="mb-3">
                        <label for="assign_event_id" class="form-label">Event *</label>
                        <select class="form-select" name="event_id" id="assign_event_id" required>
                            <option value="">Select Event</option>
                            <?php foreach ($events as $event): ?>
                                <option value="<?= $event['id'] ?>">
                                    <?= htmlspecialchars($event['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="assign_judge_number" class="form-label">Judge Number</label>
                        <input type="text" class="form-control" name="judge_number" id="assign_judge_number" placeholder="Optional">
                    </div>
                    <div class="mb-3">
                        <label for="assign_specialty" class="form-label">Specialty</label>
                        <input type="text" class="form-control" name="specialty" id="assign_specialty" placeholder="Optional">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showAssignModal(userId, judgeName) {
    document.getElementById('assign_user_id').value = userId;
    document.getElementById('assign_judge_name').textContent = judgeName;
    new bootstrap.Modal(document.getElementById('assignModal')).show();
}

function clearFilters() {
    document.getElementById('filter_judge').value = '';
    document.getElementById('filter_round').value = '';
    document.getElementById('filterForm').submit();
}

// Auto-submit on Enter key in filter inputs
document.addEventListener('DOMContentLoaded', function() {
    const filterJudge = document.getElementById('filter_judge');
    if (filterJudge) {
        filterJudge.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('filterForm').submit();
            }
        });
    }
});
</script>
<?php endif; ?>
<?php require __DIR__ . '/../layout/footer.php'; ?>
