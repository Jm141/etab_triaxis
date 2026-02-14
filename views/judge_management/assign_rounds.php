<?php 
$title = 'Assign Rounds';
require __DIR__ . '/../layout/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-list-check"></i> Assign Rounds to Judge</h1>
    <?php 
    // Smart back button: if we have event_id, go back to event judge page, otherwise global judge management
    $backUrl = isset($judge['event_id']) 
        ? "/tabulation/events/{$judge['event_id']}/judges" 
        : "/tabulation/judge-management";
    ?>
    <a href="<?= $backUrl ?>" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <h5>Judge Information</h5>
        <table class="table table-borderless">
            <tr>
                <th width="150">Name:</th>
                <td><strong><?= htmlspecialchars($judge['full_name']) ?></strong></td>
            </tr>
            <tr>
                <th>Event:</th>
                <td><?= htmlspecialchars($judge['event_name']) ?></td>
            </tr>
            <tr>
                <th>Judge Number:</th>
                <td><?= htmlspecialchars($judge['judge_number'] ?: '-') ?></td>
            </tr>
            <tr>
                <th>Specialty:</th>
                <td><?= htmlspecialchars($judge['specialty'] ?: '-') ?></td>
            </tr>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-list"></i> Available Rounds</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="/tabulation/judge-management/<?= $judge['id'] ?>/update-round-assignments">
            <input type="hidden" name="csrf_token" value="<?= Session::getCSRFToken() ?>">
            <?php if (isset($judge['event_id'])): ?>
            <input type="hidden" name="return_to_event" value="1">
            <?php endif; ?>
            
            <?php if (empty($rounds)): ?>
                <p class="text-muted">No rounds available for this event yet.</p>
            <?php else: ?>
                <div class="mb-3">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-info" onclick="selectAllRounds()">
                            <i class="fas fa-check-square"></i> Select All
                        </button>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="deselectAllRounds()">
                            <i class="fas fa-square"></i> Deselect All
                        </button>
                        <button type="button" class="btn btn-sm btn-warning" onclick="toggleSelectedRounds()">
                            <i class="fas fa-exchange-alt"></i> Toggle Selection
                        </button>
                    </div>
                    <span class="ms-3 text-muted">
                        <span id="selectedCount">0</span> of <?= count($rounds) ?> rounds selected
                    </span>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="50">
                                    <input type="checkbox" id="selectAll" onchange="toggleAll(this)" title="Select/Deselect All">
                                    <label for="selectAll" class="ms-1" style="cursor: pointer; font-weight: normal;">All</label>
                                </th>
                                <th>Level</th>
                                <th>Round</th>
                                <th>Criteria</th>
                                <th>Status</th>
                                <?php if (in_array(Session::get('role_name'), ['Super Admin', 'Event Technical Admin'])): ?>
                                <th width="120">Prep Only</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rounds as $round): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" 
                                           name="round_ids[]" 
                                           value="<?= $round['id'] ?>"
                                           id="round_<?= $round['id'] ?>"
                                           <?= $round['is_assigned'] ? 'checked' : '' ?>
                                           class="round-checkbox"
                                           onchange="updateSelectedCount()">
                                </td>
                                <td><?= htmlspecialchars($round['level_name']) ?></td>
                                <td>
                                    <label for="round_<?= $round['id'] ?>" style="cursor: pointer; margin: 0;">
                                        <strong><?= htmlspecialchars($round['name']) ?></strong>
                                    </label>
                                </td>
                                <td>
                                    <?php if (!empty($round['criteria'])): ?>
                                        <div class="criteria-checkboxes" style="max-height: 100px; overflow-y: auto;">
                                            <?php foreach ($round['criteria'] as $criteria): ?>
                                                <div class="form-check">
                                                    <input type="checkbox" 
                                                           class="form-check-input criteria-checkbox" 
                                                           id="criteria_<?= $round['id'] ?>_<?= $criteria['id'] ?>"
                                                           name="criteria[<?= $round['id'] ?>][]"
                                                           value="<?= $criteria['id'] ?>"
                                                           <?= $criteria['is_assigned'] ? 'checked' : '' ?>
                                                           onchange="updateCriteriaSelection(<?= $round['id'] ?>)">
                                                    <label class="form-check-label" for="criteria_<?= $round['id'] ?>_<?= $criteria['id'] ?>" title="<?= htmlspecialchars($criteria['description'] ?? '') ?>">
                                                        <small><?= htmlspecialchars($criteria['name']) ?> (<?= htmlspecialchars($criteria['max_score']) ?>)</small>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <button type="button" class="btn btn-xs btn-info mt-1" onclick="toggleAllCriteria(<?= $round['id'] ?>)">
                                            <i class="fas fa-check-square"></i> All Criteria
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted">No criteria available</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($round['is_assigned']): ?>
                                        <span class="badge bg-success">Assigned</span>
                                        <?php if ($round['is_preparation_only']): ?>
                                            <span class="badge bg-warning ms-1" title="Preparation Only - Not visible to judge">
                                                <i class="fas fa-eye-slash"></i> Prep
                                            </span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Not Assigned</span>
                                    <?php endif; ?>
                                </td>
                                <?php if (in_array(Session::get('role_name'), ['Super Admin', 'Event Technical Admin'])): ?>
                                <td>
                                    <div class="form-check">
                                        <input type="checkbox" 
                                               class="form-check-input" 
                                               id="prep_<?= $round['id'] ?>"
                                               name="preparation_only[<?= $round['id'] ?>]"
                                               value="1"
                                               <?= $round['is_preparation_only'] ? 'checked' : '' ?>
                                               title="Check if this round is for preparation only (not visible to judge)">
                                        <label class="form-check-label" for="prep_<?= $round['id'] ?>" title="Preparation Only">
                                            <small>Prep Only</small>
                                        </label>
                                    </div>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-check-circle"></i> Save Assignments
                    </button>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<script>
function toggleAll(checkbox) {
    const checkboxes = document.querySelectorAll('.round-checkbox');
    checkboxes.forEach(cb => cb.checked = checkbox.checked);
    updateSelectedCount();
}

function selectAllRounds() {
    const checkboxes = document.querySelectorAll('.round-checkbox');
    const selectAllCheckbox = document.getElementById('selectAll');
    checkboxes.forEach(cb => cb.checked = true);
    selectAllCheckbox.checked = true;
    updateSelectedCount();
}

function deselectAllRounds() {
    const checkboxes = document.querySelectorAll('.round-checkbox');
    const selectAllCheckbox = document.getElementById('selectAll');
    checkboxes.forEach(cb => cb.checked = false);
    selectAllCheckbox.checked = false;
    updateSelectedCount();
}

function toggleSelectedRounds() {
    const checkboxes = document.querySelectorAll('.round-checkbox');
    const selectAllCheckbox = document.getElementById('selectAll');
    let allChecked = true;
    
    checkboxes.forEach(cb => {
        cb.checked = !cb.checked;
        if (!cb.checked) allChecked = false;
    });
    
    selectAllCheckbox.checked = allChecked;
    updateSelectedCount();
}

function updateSelectedCount() {
    const checkboxes = document.querySelectorAll('.round-checkbox');
    const checked = document.querySelectorAll('.round-checkbox:checked');
    const countElement = document.getElementById('selectedCount');
    
    if (countElement) {
        countElement.textContent = checked.length;
        countElement.className = checked.length > 0 ? 'text-primary fw-bold' : 'text-muted';
    }
    
    // Update select all checkbox state
    const selectAllCheckbox = document.getElementById('selectAll');
    if (selectAllCheckbox) {
        selectAllCheckbox.checked = checkboxes.length > 0 && checked.length === checkboxes.length;
        selectAllCheckbox.indeterminate = checked.length > 0 && checked.length < checkboxes.length;
    }
}

function toggleAllCriteria(roundId) {
    const criteriaCheckboxes = document.querySelectorAll(`.criteria-checkbox[id^="criteria_${roundId}_"]`);
    const allChecked = Array.from(criteriaCheckboxes).every(cb => cb.checked);
    
    criteriaCheckboxes.forEach(cb => {
        cb.checked = !allChecked;
    });
    
    updateCriteriaSelection(roundId);
}

function updateCriteriaSelection(roundId) {
    const roundCheckbox = document.getElementById(`round_${roundId}`);
    const criteriaCheckboxes = document.querySelectorAll(`.criteria-checkbox[id^="criteria_${roundId}_"]`);
    const checkedCriteria = Array.from(criteriaCheckboxes).filter(cb => cb.checked);
    
    // Auto-select round if any criteria is selected
    if (checkedCriteria.length > 0 && !roundCheckbox.checked) {
        roundCheckbox.checked = true;
        updateSelectedCount();
    }
    
    // Auto-deselect round if no criteria is selected
    if (checkedCriteria.length === 0 && roundCheckbox.checked) {
        roundCheckbox.checked = false;
        updateSelectedCount();
    }
}

// Initialize count on page load
document.addEventListener('DOMContentLoaded', function() {
    updateSelectedCount();
    
    // Update count when any checkbox changes
    const checkboxes = document.querySelectorAll('.round-checkbox');
    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateSelectedCount);
    });
    
    // Initialize criteria selection for each round
    const rounds = document.querySelectorAll('[id^="round_"]');
    rounds.forEach(roundCheckbox => {
        const roundId = roundCheckbox.id.replace('round_', '');
        updateCriteriaSelection(roundId);
    });
});
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>



