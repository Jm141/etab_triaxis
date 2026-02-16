<?php 
$title = 'Score Round: ' . $round['name'];
require __DIR__ . '/../layout/header.php'; 
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
    <h1 class="mb-3 mb-md-0"><i class="fas fa-table"></i> <span class="d-none d-sm-inline">Score Round: </span><?= htmlspecialchars($round['name']) ?></h1>
    <div class="d-flex flex-column flex-sm-row gap-2 w-100 w-md-auto">
        <button type="button" class="btn btn-success btn-sm btn-block btn-md-inline" id="submitAllBtn" disabled>
            <i class="fas fa-check-circle"></i> <span class="d-none d-sm-inline">Submit All Scores</span><span class="d-sm-none">Submit All</span>
        </button>
        <a href="/tabulation/judge/rounds" class="btn btn-secondary btn-sm btn-block btn-md-inline">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<!-- <div class="card mb-4">
    <div class="card-body">
            <div class="row">
                <div class="col-12 <?= in_array(Session::get('role_name'), ['Super Admin', 'Event Technical Admin']) ? 'col-md-8' : 'col-12' ?> mb-3 mb-md-0">
                    <h5 class="h6 mb-1"><?= htmlspecialchars($round['event_name']) ?> - <?= htmlspecialchars($round['level_name']) ?></h5>
                    <p class="text-muted mb-2 small"><?= htmlspecialchars($round['description'] ?: 'No description') ?></p>
        <div class="mt-2">
            <div id="saveStatusContainer" class="d-inline-block">
                <span class="badge badge-info" id="autoSaveStatus" style="display: none;">
                                <i class="fas fa-spinner fa-spin"></i> <span class="d-none d-sm-inline">Auto-saving...</span>
                </span>
                <span class="badge badge-success" id="savedStatus" style="display: none;">
                    <i class="fas fa-check"></i> Saved <span id="saveTime"></span>
                </span>
                <span class="badge badge-secondary" id="draftStatus">
                                <i class="fas fa-file-alt"></i> <span class="d-none d-sm-inline">Draft - Scores are auto-saved and will be preserved if you leave</span><span class="d-sm-none">Draft</span>
                </span>
            </div>
                        <small class="text-muted ml-2 d-block d-sm-inline" id="saveCounter" style="display: none;">
                (<span id="saveCount">0</span> scores saved)
            </small>
                    </div>
                </div>
                <?php if (in_array(Session::get('role_name'), ['Super Admin', 'Event Technical Admin'])): ?>
                <div class="col-12 col-md-4">
                    <h6 class="mb-2"><i class="fas fa-chart-line"></i> Scoring Progress</h6>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between mb-1">
                            <small id="scoredLabel"><strong>Scored:</strong> <?= $progress['scored'] ?> / <?= $progress['total'] ?></small>
                            <small id="scoredPercent"><strong><?= $progress['scored_percentage'] ?>%</strong></small>
                        </div>
                        <div class="progress" style="height: 20px;">
                            <div id="scoredProgress" class="progress-bar bg-info" role="progressbar" 
                                 style="width: <?= $progress['scored_percentage'] ?>%"
                                 aria-valuenow="<?= $progress['scored'] ?>" 
                                 aria-valuemin="0" 
                                 aria-valuemax="<?= $progress['total'] ?>">
                                <?= $progress['scored'] ?> / <?= $progress['total'] ?>
                            </div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between mb-1">
                            <small id="fullyScoredLabel"><strong>Fully Scored:</strong> <?= $progress['fully_scored'] ?> / <?= $progress['total'] ?></small>
                            <small id="fullyScoredPercent"><strong><?= $progress['fully_scored_percentage'] ?>%</strong></small>
                        </div>
                        <div class="progress" style="height: 20px;">
                            <div id="fullyScoredProgress" class="progress-bar bg-warning" role="progressbar" 
                                 style="width: <?= $progress['fully_scored_percentage'] ?>%"
                                 aria-valuenow="<?= $progress['fully_scored'] ?>" 
                                 aria-valuemin="0" 
                                 aria-valuemax="<?= $progress['total'] ?>">
                                <?= $progress['fully_scored'] ?> / <?= $progress['total'] ?>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between mb-1">
                            <small id="submittedLabel"><strong>Submitted:</strong> <?= $progress['submitted'] ?> / <?= $progress['total'] ?></small>
                            <small id="submittedPercent"><strong><?= $progress['submitted_percentage'] ?>%</strong></small>
                        </div>
                        <div class="progress" style="height: 20px;">
                            <div id="submittedProgress" class="progress-bar bg-success" role="progressbar" 
                                 style="width: <?= $progress['submitted_percentage'] ?>%"
                                 aria-valuenow="<?= $progress['submitted'] ?>" 
                                 aria-valuemin="0" 
                                 aria-valuemax="<?= $progress['total'] ?>">
                                <?= $progress['submitted'] ?> / <?= $progress['total'] ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
        </div>
    </div>
</div> -->

<?php if (empty($contestants) || empty($criteria)): ?>
    <div class="alert alert-warning">
        <?php if (empty($contestants)): ?>
            No contestants registered for this event.
        <?php else: ?>
            No criteria configured for this round. Please contact the administrator.
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-clipboard-list"></i> Scoring Table</h5>
        </div>
        <div class="card-body p-0" >
            <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                <table class="table table-bordered table-hover table-sm" id="scoringTable">
                    <thead class="thead-dark sticky-top" style="background-color: #37474f; z-index: 10;">
                        <tr>
                            <th rowspan="2" class="align-middle text-center" style="min-width: 100px; position: sticky; left: 0; background-color: #37474f; z-index: 11;">
                                <strong>Contestant #</strong>
                            </th>
                            <?php foreach ($criteria as $criterion): ?>
                                <th class="text-center criteria-header-cell">
                                    <div class="criteria-header-inner">
                                        <strong><?= htmlspecialchars($criterion['name']) ?></strong>
                                        <br>
                                        <span class="badge badge-info">Max: <?= number_format($criterion['max_score'], 2) ?></span>
                                    </div>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <?php foreach ($criteria as $criterion): ?>
                                <th class="text-center small">
                                    <?php if ($criterion['description']): ?>
                                        <span title="<?= htmlspecialchars($criterion['description']) ?>">
                                            <i class="fas fa-info-circle text-info"></i>
                                        </span>
                                    <?php endif; ?>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalMaxScore = array_sum(array_column($criteria, 'max_score'));
                        foreach ($contestants as $contestant): 
                            // Get existing score for this contestant
                            $existingScore = null;
                            $scoreDetails = [];
                            $isSubmitted = false;
                            $isDraft = true;
                            $permissionStatus = 'none';
                            
                            if (!empty($allScores)) {
                                foreach ($allScores as $score) {
                                    if ($score['contestant_id'] == $contestant['id']) {
                                        $existingScore = $score;
                                        $isSubmitted = $score['is_submitted'] ?? false;
                                        $isDraft = $score['is_draft'] ?? true;
                                        $permissionStatus = $score['permission_request_status'] ?? 'none';
                                        
                                        // Get score details
                                        if (!empty($allScoreDetails[$score['id']])) {
                                            $scoreDetails = $allScoreDetails[$score['id']];
                                        }
                                        break;
                                    }
                                }
                            }
                            
                            $canEdit = !$isSubmitted || $permissionStatus === 'granted';
                            $rowClass = $isSubmitted ? 'table-success' : '';
                        ?>
                        <tr data-contestant-id="<?= $contestant['id'] ?>" class="<?= $rowClass ?>">
                            <td class="text-center font-weight-bold" style="position: sticky; left: 0; background-color: inherit; z-index: 1; font-size: 1.1em;">
                                <?= htmlspecialchars($contestant['contestant_number']) ?>
                            </td>
                            <?php foreach ($criteria as $criterion): 
                                $detail = null;
                                // $scoreDetails is indexed by criteria_id, so we can directly access it
                                if (!empty($scoreDetails) && isset($scoreDetails[$criterion['id']])) {
                                    $detail = $scoreDetails[$criterion['id']];
                                }
                                $value = $detail ? number_format($detail['raw_score'], 2, '.', '') : '';
                            ?>
                            <td class="text-center">
                                <input type="number" 
                                       class="form-control form-control-sm score-input text-center" 
                                       data-contestant-id="<?= $contestant['id'] ?>"
                                       data-criteria-id="<?= $criterion['id'] ?>"
                                       data-round-id="<?= $round['id'] ?>"
                                       data-max="<?= $criterion['max_score'] ?>"
                                       value="<?= $value ?>"
                                       min="0" 
                                       max="<?= $criterion['max_score'] ?>" 
                                       step="0.01"
                                       placeholder="0.00"
                                       <?= !$canEdit ? 'readonly' : '' ?>
                                       style="min-width: 80px; width: 100px; margin: 0 auto;">
                                <?php if (!$canEdit): ?>
                                    <small class="text-muted d-block mt-1">
                                        <i class="fas fa-lock"></i> Submitted
                                    </small>
                                <?php endif; ?>
                            </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<style>
#scoringTable thead th {
    background-color: #37474f !important;
    color: #eceff1 !important;
    border-color: #546e7a !important;
}
#scoringTable thead th.criteria-header-cell {
    min-width: 150px !important;
    padding: 12px 10px;
}
#scoringTable thead th.criteria-header-cell .criteria-header-inner {
    font-size: 1rem;
    line-height: 1.4;
}
#scoringTable thead th.criteria-header-cell .criteria-header-inner strong {
    font-size: 1.05rem;
}
#scoringTable thead th.criteria-header-cell .badge {
    font-size: 0.8rem;
    padding: 4px 8px;
}

#scoringTable tbody tr.table-success {
    background-color: rgba(77, 182, 172, 0.08) !important;
}

.score-input:read-only {
    background-color: #eceff1;
    cursor: not-allowed;
}

.score-input:focus {
    border-color: #4db6ac;
    box-shadow: 0 0 0 0.2rem rgba(77, 182, 172, 0.25);
}
</style>

<script>
// Wait for jQuery to load before executing
(function() {
    'use strict';
    
    function initScoreHandlers() {
        // Check if jQuery is available
        if (typeof jQuery === 'undefined' || typeof $ === 'undefined') {
            console.error('jQuery not loaded yet, retrying...');
            setTimeout(initScoreHandlers, 100);
            return;
        }
        
        // Ensure document is ready
        $(document).ready(function() {
            console.log('[Score Handlers] Initializing...');
            console.log('[Score Handlers] Handlers will be attached in initAutoSave() where roundId is available');
        });
    }
    
    // Start initialization
    initScoreHandlers();
})();
</script>

<script>
// Original auto-save script - runs AFTER jQuery loads (from footer.php)
console.log('[Auto-Save] ===== SCRIPT BLOCK LOADED =====');

(function() {
    'use strict';
    
    function initAutoSave() {
        let autoSaveTimer = null;
        let isSaving = false;
        let saveCount = 0;
        
        <?php 
        $roundId = isset($round['id']) ? (int)$round['id'] : 0;
        $judgeId = isset($judge['id']) ? (int)$judge['id'] : 0;
        if (!$roundId || !$judgeId) {
            echo "alert('ERROR: Round ID or Judge ID is missing! Round: " . $roundId . " Judge: " . $judgeId . "'); return;";
        }
        ?>
        const roundId = <?php echo $roundId; ?>;
        const judgeId = <?php echo $judgeId; ?>;
        
        // Make roundId available globally for permission handlers
        window.roundId = roundId;
        window.judgeId = judgeId;
        
        console.log('[Auto-Save] Round ID:', roundId, 'Judge ID:', judgeId);
        
        // LocalStorage key for offline saves
        const OFFLINE_STORAGE_KEY = 'tabulation_offline_scores_' + roundId + '_' + judgeId;
        const PENDING_SYNC_KEY = 'tabulation_pending_sync_' + roundId + '_' + judgeId;
        
        // Auto-save logging (enabled for debugging)
        const DEBUG_AUTOSAVE = true;
        
        function logAutoSave(message, data = null) {
            if (DEBUG_AUTOSAVE) {
                console.log('[Auto-Save]', message, data || '');
            }
        }
        
        // Load offline scores from localStorage
        function loadOfflineScores() {
            try {
                const offlineData = JSON.parse(localStorage.getItem(OFFLINE_STORAGE_KEY) || '{}');
                let loadedCount = 0;
                
                Object.keys(offlineData).forEach(function(key) {
                    const scoreData = offlineData[key];
                    const $input = $('.score-input[data-contestant-id="' + scoreData.contestantId + '"][data-criteria-id="' + scoreData.criteriaId + '"]');
                    
                    if ($input.length && (!$input.val() || $input.val() === '0.00')) {
                        $input.val(scoreData.rawScore.toFixed(2));
                        loadedCount++;
                    }
                });
                
                if (loadedCount > 0) {
                    logAutoSave('Loaded ' + loadedCount + ' scores from localStorage');
                }
            } catch (e) {
                console.error('Error loading offline scores:', e);
            }
        }
        
        // Sync pending saves from localStorage
        function syncPendingSaves() {
            try {
                const pendingSync = JSON.parse(localStorage.getItem(PENDING_SYNC_KEY) || '[]');
                if (pendingSync.length === 0) {
                    return;
                }
                
                logAutoSave('Syncing ' + pendingSync.length + ' pending saves');
                
                let syncedCount = 0;
                let failedItems = [];
                
                // Sync each pending item
                pendingSync.forEach(function(item, index) {
                    const saveUrl = '/tabulation/judge/rounds/' + roundId + '/contestants/' + item.contestantId + '/auto-save';
                    const saveData = {
                        csrf_token: <?php echo json_encode(Session::getCSRFToken() ?: '', JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
                        criteria_id: item.criteriaId,
                        raw_score: item.rawScore
                    };
                    
                    $.ajax({
                        url: saveUrl,
                        method: 'POST',
                        data: saveData,
                        success: function(response) {
                            if (response.success) {
                                syncedCount++;
                                logAutoSave('Synced pending save', item);
                            } else {
                                item.attempts = (item.attempts || 0) + 1;
                                if (item.attempts < 5) {
                                    failedItems.push(item);
                                }
                            }
                            
                            // If this is the last item, update localStorage
                            if (index === pendingSync.length - 1) {
                                if (failedItems.length > 0) {
                                    localStorage.setItem(PENDING_SYNC_KEY, JSON.stringify(failedItems));
                                } else {
                                    localStorage.removeItem(PENDING_SYNC_KEY);
                                    localStorage.removeItem(OFFLINE_STORAGE_KEY);
                                }
                                
                                if (syncedCount > 0) {
                                    const $syncMsg = $('<div class="alert alert-success alert-dismissible fade show mt-2" role="alert">')
                                        .html('<i class="fas fa-check-circle"></i> <strong>Synced ' + syncedCount + ' offline saves.</strong>')
                                        .append('<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>');
                                    $('.card-body').first().prepend($syncMsg);
                                    
                                    setTimeout(function() {
                                        $syncMsg.fadeOut(function() {
                                            $(this).remove();
                                        });
                                    }, 5000);
                                }
                            }
                        },
                        error: function() {
                            item.attempts = (item.attempts || 0) + 1;
                            if (item.attempts < 5) {
                                failedItems.push(item);
                            }
                            
                            if (index === pendingSync.length - 1) {
                                if (failedItems.length > 0) {
                                    localStorage.setItem(PENDING_SYNC_KEY, JSON.stringify(failedItems));
                                }
                            }
                        }
                    });
                });
            } catch (e) {
                console.error('Error syncing pending saves:', e);
            }
        }
        
        // Check if there are any saved scores on page load
        let hasSavedScores = false;
        $('.score-input').each(function() {
            if ($(this).val() && $(this).val() !== '0.000' && $(this).val() !== '') {
                hasSavedScores = true;
                return false; // break
            }
        });
        
        // Load offline scores first
        loadOfflineScores();
        
        // Try to sync pending saves
        syncPendingSaves();
        
        if (hasSavedScores) {
            // Show message that scores were loaded
            const $loadedMsg = $('<div class="alert alert-info alert-dismissible fade show mt-2" role="alert" style="margin-top: 10px;">')
                .html('<i class="fas fa-info-circle"></i> <strong>Your previous scores have been loaded.</strong> All scores are auto-saved and will be preserved if you leave this page.')
                .append('<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>');
            $('.card-body').first().prepend($loadedMsg);
            
            // Auto-dismiss after 5 seconds
            setTimeout(function() {
                $loadedMsg.fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
        }
        
        // Periodic sync every 30 seconds
        setInterval(function() {
            syncPendingSaves();
        }, 30000);
        
        // Auto-save functionality with validation
        // Use event delegation to ensure it works even if inputs are added dynamically
        $(document).on('input change', '.score-input', function() {
            const $input = $(this);
            
            // Skip if input is readonly or disabled
            if ($input.prop('readonly') || $input.prop('disabled')) {
                logAutoSave('Input is readonly/disabled, skipping auto-save');
                return;
            }
            
            const contestantId = $input.data('contestant-id');
            const criteriaId = $input.data('criteria-id');
            
            // Validate required data attributes
            if (!contestantId || !criteriaId) {
                logAutoSave('Missing contestantId or criteriaId, skipping auto-save', {
                    contestantId: contestantId,
                    criteriaId: criteriaId
                });
                return;
            }
            
            let inputValue = $input.val();
            if (typeof inputValue === 'string') {
                inputValue = inputValue.trim();
            }
            
            console.log('[Auto-Save] Input event triggered', {
                contestantId: contestantId,
                criteriaId: criteriaId,
                inputValue: inputValue
            });
            
            // Parse value for saving
            let value = parseFloat(inputValue);
            
            // Get max score from data attribute
            const maxScore = parseFloat($input.data('max')) || 0;
            
            // Format to 2 decimal places if it's a valid number with decimals
            if (!isNaN(value) && inputValue && inputValue.toString().includes('.')) {
                const decimalPlaces = inputValue.toString().split('.')[1];
                if (decimalPlaces && decimalPlaces.length > 2) {
                    value = Math.round(value * 100) / 100;
                    $input.val(value.toFixed(2));
                }
            }
            
            // Don't save if input is empty or invalid (but allow 0 as valid score)
            if (!inputValue || inputValue === '' || isNaN(value)) {
                logAutoSave('Input is empty or invalid, skipping auto-save', {
                    inputValue: inputValue,
                    value: value
                });
                // Still check submit button even if not saving
                checkSubmitButton();
                return;
            }
            
            // Validate max score - check if value exceeds max
            if (!isNaN(maxScore) && maxScore > 0 && value > maxScore) {
                // Show warning and prevent saving
                showMaxValueWarning($input, maxScore);
                $input.addClass('border-danger');
                logAutoSave('Value exceeds max score, preventing save', {
                    value: value,
                    maxScore: maxScore
                });
                // Clear any pending save timer
                if (autoSaveTimer) {
                    clearTimeout(autoSaveTimer);
                    autoSaveTimer = null;
                }
                // Hide saving status
                $('#autoSaveStatus').hide();
                $('#savedStatus').hide();
                $('#draftStatus').fadeIn();
                checkSubmitButton();
                return;
            }
            
            // Remove error styling if value is valid
            $input.removeClass('border-danger');
            $input.next('.max-value-warning').remove();
            
            // Allow saving 0 as a valid score
            
            // Show saving status
            $('#autoSaveStatus').fadeIn();
            $('#savedStatus').hide();
            $('#draftStatus').hide();
            logAutoSave('Starting auto-save timer for contestant ' + contestantId + ', criteria ' + criteriaId + ', value: ' + value);
            
            // Clear existing timer
            if (autoSaveTimer) {
                clearTimeout(autoSaveTimer);
                logAutoSave('Cleared previous auto-save timer');
            }
            
            // Set new timer (save after 1 second of no typing)
            // Use the validated/capped value
            const finalValue = value;
            const $inputRef = $input; // Store reference for closure
            autoSaveTimer = setTimeout(function() {
                logAutoSave('Auto-save timer triggered, calling saveScore');
                // Get the current value from input in case it changed
                const currentInputValue = parseFloat($inputRef.val()) || finalValue;
                if (!isNaN(currentInputValue)) {
                    saveScore(contestantId, criteriaId, currentInputValue, $inputRef);
                } else {
                    logAutoSave('Final value is invalid, skipping save', {
                        currentInputValue: currentInputValue,
                        finalValue: finalValue
                    });
                    checkSubmitButton();
                }
            }, 1000);
            
            logAutoSave('Auto-save timer set for 1 second');
            
            // Check submit button immediately when value changes
            checkSubmitButton();
        });
        
        // Format on blur and trigger immediate save
        $(document).on('blur', '.score-input', function() {
            const $input = $(this);
            
            // Skip if input is readonly or disabled
            if ($input.prop('readonly') || $input.prop('disabled')) {
                return;
            }
            
            const contestantId = $input.data('contestant-id');
            const criteriaId = $input.data('criteria-id');
            
            if (!contestantId || !criteriaId) {
                return;
            }
            
            let value = parseFloat($input.val()) || 0;
            let finalValue = value;
            
            // Get max score from data attribute
            const maxScore = parseFloat($input.data('max')) || 0;
            
            // Set to 0 if negative
            if (finalValue < 0) finalValue = 0;
            
            // Check if value exceeds max score
            if (!isNaN(maxScore) && maxScore > 0 && finalValue > maxScore) {
                // Cap value to max score
                finalValue = maxScore;
                $input.val(finalValue.toFixed(2));
                showMaxValueWarning($input, maxScore);
                $input.addClass('border-danger');
                logAutoSave('Value exceeded max score, capped to max', {
                    originalValue: value,
                    maxScore: maxScore,
                    finalValue: finalValue
                });
            } else {
                // Format to 2 decimal places
                finalValue = Math.round(finalValue * 100) / 100;
                $input.val(finalValue.toFixed(2));
                // Remove error styling if value is valid
                $input.removeClass('border-danger');
                $input.next('.max-value-warning').remove();
            }
            
            // Clear any pending timer and save immediately
            if (autoSaveTimer) {
                clearTimeout(autoSaveTimer);
                autoSaveTimer = null;
            }
            
            // Save immediately if value is valid
            if (!isNaN(finalValue) && finalValue >= 0) {
                const inputValue = $input.val().trim();
                if (inputValue && inputValue !== '') {
                    logAutoSave('Blur event - saving immediately', {
                        contestantId: contestantId,
                        criteriaId: criteriaId,
                        value: finalValue
                    });
                    saveScore(contestantId, criteriaId, finalValue, $input);
                }
            }
            
            // Check submit button on blur
            checkSubmitButton();
        });
        
        // Show warning for max value exceeded
        function showMaxValueWarning($input, max) {
        // Remove existing warning
        $input.next('.max-value-warning').remove();
        
        // Add warning tooltip
        const $warning = $('<small class="text-danger d-block max-value-warning mt-1">')
            .html('<i class="fas fa-exclamation-triangle"></i> Max: ' + max.toFixed(2));
        $input.after($warning);
        
            // Remove warning after 3 seconds
            setTimeout(function() {
                $warning.fadeOut(function() {
                    $(this).remove();
                });
            }, 3000);
        }
        
        // Save score function
        function saveScore(contestantId, criteriaId, value, $input) {
            if (isSaving) {
                logAutoSave('Already saving, skipping duplicate save');
                return;
            }
            
            // Validate before saving
            const numValue = parseFloat(value);
            
            logAutoSave('saveScore called', {
                contestantId: contestantId,
                criteriaId: criteriaId,
                value: value,
                numValue: numValue
            });
            
            if (isNaN(numValue)) {
                logAutoSave('Invalid value, skipping save');
                return; // Don't save invalid values
            }
            
            // Allow saving 0 as a valid score
            // Only check if value is negative
            if (numValue < 0) {
                logAutoSave('Value is negative, skipping save');
                return;
            }
            
            // Check max score validation before saving
            const maxScore = parseFloat($input.data('max')) || 0;
            if (!isNaN(maxScore) && maxScore > 0 && numValue > maxScore) {
                // Show warning and prevent save
                showMaxValueWarning($input, maxScore);
                $input.addClass('border-danger');
                logAutoSave('Value exceeds max score, preventing save', {
                    value: numValue,
                    maxScore: maxScore
                });
                // Hide saving status
                $('#autoSaveStatus').hide();
                $('#savedStatus').hide();
                $('#draftStatus').fadeIn();
                return; // Don't save if exceeds max
            }
            
            isSaving = true;
            logAutoSave('Setting isSaving = true, making AJAX call');
            
            logAutoSave('Sending auto-save request', {
                contestantId: contestantId,
                criteriaId: criteriaId,
                score: numValue,
                roundId: roundId
            });
            
            const saveUrl = '/tabulation/judge/rounds/' + roundId + '/contestants/' + contestantId + '/auto-save';
            const saveData = {
                csrf_token: <?php echo json_encode(Session::getCSRFToken() ?: '', JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
                criteria_id: criteriaId,
                raw_score: numValue
            };
            
            $.ajax({
                url: saveUrl,
                method: 'POST',
                dataType: 'json',
                data: saveData,
                success: function(response) {
                if (response.success) {
                    saveCount++;
                    const saveTime = new Date().toLocaleTimeString();
                    
                    logAutoSave('Auto-save successful', {
                        contestantId: contestantId,
                        criteriaId: criteriaId,
                        score: numValue,
                        totalSaves: saveCount
                    });
                    
                    // Show saved status
                    $('#autoSaveStatus').hide();
                    $('#savedStatus').fadeIn();
                    $('#saveTime').text('(' + saveTime + ')');
                    $('#saveCount').text(saveCount);
                    $('#saveCounter').fadeIn();
                    
                    // Show draft status after 2 seconds
                    setTimeout(function() {
                        $('#savedStatus').fadeOut(function() {
                            $('#draftStatus').fadeIn();
                        });
                    }, 2000);
                    
                    // Check if all scores are filled
                    checkSubmitButton();
                    
                    // Update progress indicators
                    updateProgress();
                } else {
                    logAutoSave('Auto-save failed', response);
                    Swal.fire('Error', 'Error saving score: ' + (response.message || 'Unknown error'), 'error');
                    // Highlight the input with error
                    $input.addClass('border-danger');
                    setTimeout(function() {
                        $input.removeClass('border-danger');
                    }, 3000);
                }
                isSaving = false;
            },
            error: function(xhr, status, error) {
                let errorMsg = 'Error saving score. Please try again.';
                let isOffline = false;
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.status === 0 || status === 'error') {
                    // Network error or server unreachable - save to localStorage
                    isOffline = true;
                    errorMsg = 'Server unreachable - saving locally. Will sync when connection is restored.';
                    
                    // Save to localStorage as backup
                    try {
                        let offlineScores = JSON.parse(localStorage.getItem(OFFLINE_STORAGE_KEY) || '{}');
                        offlineScores[contestantId + '_' + criteriaId] = {
                            contestantId: contestantId,
                            criteriaId: criteriaId,
                            rawScore: numValue,
                            timestamp: new Date().toISOString(),
                            roundId: roundId
                        };
                        localStorage.setItem(OFFLINE_STORAGE_KEY, JSON.stringify(offlineScores));
                        
                        // Add to pending sync queue
                        let pendingSync = JSON.parse(localStorage.getItem(PENDING_SYNC_KEY) || '[]');
                        const syncItem = {
                            contestantId: contestantId,
                            criteriaId: criteriaId,
                            rawScore: numValue,
                            timestamp: new Date().toISOString(),
                            attempts: 0
                        };
                        // Check if already in queue
                        const existingIndex = pendingSync.findIndex(item => 
                            item.contestantId === contestantId && item.criteriaId === criteriaId
                        );
                        if (existingIndex >= 0) {
                            pendingSync[existingIndex] = syncItem;
                        } else {
                            pendingSync.push(syncItem);
                        }
                        localStorage.setItem(PENDING_SYNC_KEY, JSON.stringify(pendingSync));
                        
                        logAutoSave('Saved to localStorage', syncItem);
                    } catch (e) {
                        console.error('Failed to save to localStorage:', e);
                        errorMsg = 'Failed to save locally. Please check your browser settings.';
                    }
                } else if (xhr.status === 404) {
                    errorMsg = 'Auto-save endpoint not found - check route configuration';
                } else if (xhr.status === 500) {
                    errorMsg = 'Server error - check server logs';
                }
                
                logAutoSave('Auto-save error', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    message: errorMsg,
                    response: xhr.responseJSON,
                    url: saveUrl,
                    isOffline: isOffline
                });
                
                // Show appropriate status
                $('#autoSaveStatus').hide();
                if (isOffline) {
                    const $offlineBadge = $('<span class="badge badge-warning" id="offlineStatus">')
                        .html('<i class="fas fa-save"></i> Saved locally (offline)');
                    $('#saveStatusContainer').append($offlineBadge);
                    
                    setTimeout(function() {
                        $offlineBadge.fadeOut(function() {
                            $(this).remove();
                            $('#draftStatus').fadeIn();
                        });
                    }, 3000);
                } else {
                    const $errorBadge = $('<span class="badge badge-danger" id="errorStatus">')
                        .html('<i class="fas fa-exclamation-triangle"></i> Save failed: ' + errorMsg);
                    $('#saveStatusContainer').append($errorBadge);
                    
                    setTimeout(function() {
                        $errorBadge.fadeOut(function() {
                            $(this).remove();
                            $('#draftStatus').fadeIn();
                        });
                    }, 5000);
                    
                    Swal.fire('Error', errorMsg, 'error');
                }
                
                $input.addClass('border-danger');
                setTimeout(function() {
                    $input.removeClass('border-danger');
                }, 3000);
                isSaving = false;
            }
            });
        }
        
        // Check if submit button should be enabled
        function checkSubmitButton() {
            let filledCount = 0;
            let totalEditable = 0;
            let emptyInputs = [];
            
            // Only check editable inputs (not readonly/disabled)
            $('.score-input:not([readonly]):not([disabled])').each(function() {
                const $input = $(this);
                totalEditable++;
                
                let value = $input.val();
                if (typeof value === 'string') {
                    value = value.trim();
                }
                
                // Check if input has a value (empty string, null, or undefined means not filled)
                if (!value || value === '') {
                    const contestantId = $input.data('contestant-id');
                    const criteriaId = $input.data('criteria-id');
                    emptyInputs.push('C' + contestantId + '-Cr' + criteriaId);
                    return true; // continue checking others
                }
                
                // Check if value is a valid number (0 is valid)
                const numValue = parseFloat(value);
                if (isNaN(numValue)) {
                    const contestantId = $input.data('contestant-id');
                    const criteriaId = $input.data('criteria-id');
                    emptyInputs.push('C' + contestantId + '-Cr' + criteriaId + '(invalid)');
                    return true; // continue checking others
                }
                
                // Check if value exceeds max score
                const maxScore = parseFloat($input.data('max')) || 0;
                if (!isNaN(maxScore) && maxScore > 0 && numValue > maxScore) {
                    const contestantId = $input.data('contestant-id');
                    const criteriaId = $input.data('criteria-id');
                    emptyInputs.push('C' + contestantId + '-Cr' + criteriaId + '(exceeds max: ' + numValue.toFixed(2) + ' > ' + maxScore.toFixed(2) + ')');
                    return true; // continue checking others
                }
                
                // Value is valid
                filledCount++;
            });
            
            // Enable button only if all editable inputs are filled AND no scores exceed max
            if (totalEditable > 0 && filledCount === totalEditable) {
                $('#submitAllBtn').prop('disabled', false);
                logAutoSave('Submit All button enabled - all ' + filledCount + ' of ' + totalEditable + ' scores filled');
            } else {
                $('#submitAllBtn').prop('disabled', true);
                if (totalEditable === 0) {
                    logAutoSave('Submit All button disabled - no editable inputs found');
                } else if (filledCount === 0) {
                    logAutoSave('Submit All button disabled - no scores entered (' + totalEditable + ' inputs need scores)');
                } else {
                    logAutoSave('Submit All button disabled - ' + filledCount + ' of ' + totalEditable + ' scores filled. Empty: ' + emptyInputs.slice(0, 5).join(', ') + (emptyInputs.length > 5 ? '...' : ''));
                }
            }
        }
        
        // Update progress indicators dynamically (only if progress section exists)
        function updateProgress() {
            // Check if progress section exists (only visible to Technical Admin)
            if ($('#scoredProgress').length === 0) {
                return; // Progress section not visible, skip update
            }
            
            const totalContestants = <?php echo isset($progress['total']) ? (int)$progress['total'] : 0; ?>;
            let scoredCount = 0;
            let fullyScoredCount = 0;
            let submittedCount = 0;
            
            // Get unique contestant IDs
            const contestantIds = new Set();
            $('.score-input').each(function() {
                const contestantId = $(this).data('contestant-id');
                contestantIds.add(contestantId);
            });
            
            // Count scored, fully scored, and submitted contestants
            contestantIds.forEach(function(contestantId) {
                let hasScore = false;
                let allCriteriaScored = true;
                let isSubmitted = false;
                
                // Check if contestant has any scores
                $(`.score-input[data-contestant-id="${contestantId}"]`).each(function() {
                    const value = $(this).val();
                    if (value && value !== '0.000' && value !== '') {
                        hasScore = true;
                    } else {
                        allCriteriaScored = false;
                    }
                });
                
                // Check if contestant row has submitted badge
                const $row = $(`tr[data-contestant-id="${contestantId}"]`);
                if ($row.find('.badge-success:contains("Submitted")').length > 0) {
                    isSubmitted = true;
                }
                
                if (hasScore) scoredCount++;
                if (allCriteriaScored) fullyScoredCount++;
                if (isSubmitted) submittedCount++;
            });
            
            // Calculate percentages
            const scoredPercentage = totalContestants > 0 ? (scoredCount / totalContestants * 100) : 0;
            const fullyScoredPercentage = totalContestants > 0 ? (fullyScoredCount / totalContestants * 100) : 0;
            const submittedPercentage = totalContestants > 0 ? (submittedCount / totalContestants * 100) : 0;
            
            // Update progress bars - Scored
            $('#scoredProgress').css('width', scoredPercentage + '%')
                .attr('aria-valuenow', scoredCount)
                .text(scoredCount + ' / ' + totalContestants);
            $('#scoredLabel').html('<strong>Scored:</strong> ' + scoredCount + ' / ' + totalContestants);
            $('#scoredPercent').html('<strong>' + scoredPercentage.toFixed(1) + '%</strong>');
            
            // Update progress bars - Fully Scored
            $('#fullyScoredProgress').css('width', fullyScoredPercentage + '%')
                .attr('aria-valuenow', fullyScoredCount)
                .text(fullyScoredCount + ' / ' + totalContestants);
            $('#fullyScoredLabel').html('<strong>Fully Scored:</strong> ' + fullyScoredCount + ' / ' + totalContestants);
            $('#fullyScoredPercent').html('<strong>' + fullyScoredPercentage.toFixed(1) + '%</strong>');
            
            // Update progress bars - Submitted
            $('#submittedProgress').css('width', submittedPercentage + '%')
                .attr('aria-valuenow', submittedCount)
                .text(submittedCount + ' / ' + totalContestants);
            $('#submittedLabel').html('<strong>Submitted:</strong> ' + submittedCount + ' / ' + totalContestants);
            $('#submittedPercent').html('<strong>' + submittedPercentage.toFixed(1) + '%</strong>');
        }
        
        // Submit all scores
        $('#submitAllBtn').on('click', function() {
            // First, validate all scores before showing confirmation
            let exceededMaxScores = [];
            let hasEmptyScores = false;
            
            $('.score-input').each(function() {
                const $input = $(this);
                
                // Skip if readonly (already submitted)
                if ($input.prop('readonly')) {
                    return;
                }
                
                const value = $input.val();
                const maxScore = parseFloat($input.data('max')) || 0;
                const contestantNum = $input.closest('tr').find('td:first').text().trim();
                const criteriaName = $input.closest('th').prevAll('th').length > 0 ? 
                    $input.closest('thead').find('th').eq($input.closest('td').index()).find('strong').text().trim() : 
                    'Unknown';
                
                // Check if empty
                if (!value || value === '' || value === '0' || value === '0.00' || value === '0.0') {
                    hasEmptyScores = true;
                    return;
                }
                
                // Check if exceeds max
                const numValue = parseFloat(value);
                if (!isNaN(numValue) && !isNaN(maxScore) && maxScore > 0 && numValue > maxScore) {
                    exceededMaxScores.push({
                        contestant: contestantNum,
                        criteria: criteriaName,
                        value: numValue.toFixed(2),
                        max: maxScore.toFixed(2)
                    });
                }
            });
            
            // Show error if there are scores exceeding max
            if (exceededMaxScores.length > 0) {
                let errorMsg = 'Cannot submit: ' + exceededMaxScores.length + ' score(s) exceed the maximum allowed:\n\n';
                exceededMaxScores.slice(0, 5).forEach(function(item) {
                    errorMsg += '• Contestant #' + item.contestant + ' - ' + item.criteria + ': ' + item.value + ' (Max: ' + item.max + ')\n';
                });
                if (exceededMaxScores.length > 5) {
                    errorMsg += '... and ' + (exceededMaxScores.length - 5) + ' more';
                }
                errorMsg += '\n\nPlease fix these scores before submitting.';
                
                Swal.fire({
                    title: 'Invalid Scores',
                    text: errorMsg,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return;
            }
            
            // Show error if there are empty scores
            if (hasEmptyScores) {
                Swal.fire({
                    title: 'Incomplete Scores',
                    text: 'Please fill in all scores before submitting.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                return;
            }
            
            // All validations passed, show confirmation
        Swal.fire({
            title: 'Submit All Scores?',
            text: 'Once submitted, you will need permission to edit them.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, submit all',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (!result.isConfirmed) {
                return;
            }
        
        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Submitting...');
        
        $.ajax({
            url: '/tabulation/judge/rounds/' + roundId + '/submit-all',
            method: 'POST',
            data: {
                csrf_token: <?php echo json_encode(Session::getCSRFToken() ?: '', JSON_HEX_APOS | JSON_HEX_QUOT); ?>
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success!', 'All scores submitted successfully!', 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', response.message || 'Unknown error', 'error');
                    $btn.prop('disabled', false).html('<i class="fas fa-check-circle"></i> Submit All Scores');
                }
            },
            error: function() {
                Swal.fire('Error', 'Error submitting scores. Please try again.', 'error');
                $btn.prop('disabled', false).html('<i class="fas fa-check-circle"></i> Submit All Scores');
            }
                });
            });
        });
        
        // Permission handlers are now attached outside initAutoSave() for better reliability
        // See handlers below after initAutoSave() function
        
        // Initial check
        checkSubmitButton();
        
        // Log initialization complete
        const inputCount = $('.score-input').length;
        const editableCount = $('.score-input:not([readonly])').length;
        console.log('[Auto-Save] Initialization complete', {
            totalInputs: inputCount,
            editableInputs: editableCount,
            roundId: roundId,
            judgeId: judgeId
        });
        
        // Test that event handlers are attached
        if (editableCount > 0) {
            console.log('[Auto-Save] Event handlers attached to ' + editableCount + ' editable inputs');
        } else {
            console.warn('[Auto-Save] No editable inputs found!');
        }
    }
    
    // Initialize when jQuery is ready
    // Since this script runs before footer, we need to wait for jQuery
    (function waitForJQuery() {
        if (typeof jQuery !== 'undefined' && typeof $ !== 'undefined') {
            console.log('[Auto-Save] jQuery found, initializing...');
            jQuery(document).ready(function() {
                console.log('[Auto-Save] Document ready, calling initAutoSave');
                initAutoSave();
            });
        } else {
            console.log('[Auto-Save] Waiting for jQuery...');
            setTimeout(waitForJQuery, 50);
        }
    })();
    
    // Permission handlers moved to second script block (after jQuery loads)
    // See handlers in the script block after initAutoSave()
</script>

<style>
#scoringTable thead th {
    background-color: #37474f !important;
    color: #eceff1 !important;
    border-color: #546e7a !important;
}

#scoringTable tbody tr.table-success {
    background-color: rgba(77, 182, 172, 0.08) !important;
}

.score-input:read-only {
    background-color: #eceff1;
    cursor: not-allowed;
}

.score-input:focus {
    border-color: #4db6ac;
    box-shadow: 0 0 0 0.2rem rgba(77, 182, 172, 0.25);
}

/* Mobile and Tablet Responsive Styles */
@media (max-width: 768px) {
    /* Header adjustments */
    h1 {
        font-size: 1.5rem;
    }
    
    /* Table responsive */
    #scoringTable {
        font-size: 0.85rem;
    }
    
    #scoringTable thead th {
        font-size: 0.75rem;
        padding: 8px 4px;
        white-space: nowrap;
    }
    
    #scoringTable tbody td {
        padding: 8px 4px;
    }
    
    /* Larger input fields for mobile */
    .score-input {
        font-size: 16px !important; /* Prevents zoom on iOS */
        min-width: 90px !important;
        width: 100% !important;
        padding: 10px 5px !important;
        height: 44px !important; /* Minimum touch target size */
    }
    
    /* Contestant number column */
    #scoringTable th:first-child,
    #scoringTable td:first-child {
        position: sticky;
        left: 0;
        background-color: inherit;
        z-index: 1;
        min-width: 80px;
        font-weight: bold;
    }
    
    /* Status column adjustments */
    #scoringTable th:last-child,
    #scoringTable td:last-child {
        min-width: 100px;
    }
    
    /* Progress section */
    .col-md-4 {
        margin-top: 15px;
    }
    
    /* Badge adjustments */
    .badge {
        font-size: 0.7rem;
        padding: 4px 6px;
    }
    
    /* Button adjustments */
    .btn {
        font-size: 0.875rem;
        padding: 8px 12px;
    }
    
    /* Card header */
    .card-header h5 {
        font-size: 1rem;
    }
}

/* Tablet specific (768px - 1024px) */
@media (min-width: 768px) and (max-width: 1024px) {
    .score-input {
        font-size: 15px !important;
        min-width: 85px !important;
        padding: 8px 4px !important;
        height: 38px !important;
    }
    
    #scoringTable {
        font-size: 0.9rem;
    }
    
    #scoringTable thead th {
        font-size: 0.8rem;
        padding: 10px 6px;
    }
}

/* Small mobile devices (max-width: 576px) */
@media (max-width: 576px) {
    h1 {
        font-size: 1.25rem;
    }
    
    .card-body {
        padding: 10px !important;
    }
    
    #scoringTable {
        font-size: 0.75rem;
    }
    
    #scoringTable thead th {
        font-size: 0.7rem;
        padding: 6px 3px;
    }
    
    #scoringTable thead th .badge {
        font-size: 0.65rem;
        padding: 2px 4px;
    }
    
    .score-input {
        font-size: 16px !important;
        min-width: 80px !important;
        padding: 12px 4px !important;
        height: 48px !important;
    }
    
    /* Progress bars on mobile */
    .progress {
        height: 18px !important;
    }
    
    .progress-bar {
        font-size: 0.7rem;
        padding: 2px 4px;
    }
    
    /* Hide some text on very small screens */
    .draftStatus .d-sm-none {
        display: inline !important;
    }
}

/* Landscape mobile orientation */
@media (max-width: 896px) and (orientation: landscape) {
    .table-responsive {
        max-height: 60vh !important;
    }
    
    .score-input {
        height: 40px !important;
        padding: 8px 4px !important;
    }
}

/* Touch device optimizations */
@media (hover: none) and (pointer: coarse) {
    .score-input {
        font-size: 16px !important; /* Prevents iOS zoom */
        -webkit-appearance: none;
        -moz-appearance: textfield;
    }
    
    /* Larger touch targets */
    .btn {
        min-height: 44px;
        min-width: 44px;
    }
    
    /* Better scrolling on touch */
    .table-responsive {
        -webkit-overflow-scrolling: touch;
        overscroll-behavior: contain;
    }
}
</style>

<?php 
// Load footer (which includes jQuery)
require __DIR__ . '/../layout/footer.php'; 
?>

<script>
// Auto-save script - runs AFTER jQuery loads (from footer.php)
console.log('[Auto-Save] ===== SCRIPT BLOCK LOADED =====');

(function() {
    'use strict';
    
    function initAutoSave() {
        let autoSaveTimer = null;
        let isSaving = false;
        let saveCount = 0;
        
        <?php 
        $roundId = isset($round['id']) ? (int)$round['id'] : 0;
        $judgeId = isset($judge['id']) ? (int)$judge['id'] : 0;
        if (!$roundId || !$judgeId) {
            echo "alert('ERROR: Round ID or Judge ID is missing! Round: " . $roundId . " Judge: " . $judgeId . "'); return;";
        }
        ?>
        const roundId = <?php echo $roundId; ?>;
        const judgeId = <?php echo $judgeId; ?>;
        
        // Make roundId available globally for permission handlers
        window.roundId = roundId;
        window.judgeId = judgeId;
        
        console.log('[Auto-Save] Round ID:', roundId, 'Judge ID:', judgeId);
        
        // LocalStorage key for offline saves
        const OFFLINE_STORAGE_KEY = 'tabulation_offline_scores_' + roundId + '_' + judgeId;
        const PENDING_SYNC_KEY = 'tabulation_pending_sync_' + roundId + '_' + judgeId;
        
        // Auto-save logging (enabled for debugging)
        const DEBUG_AUTOSAVE = true;
        
        function logAutoSave(message, data = null) {
            if (DEBUG_AUTOSAVE) {
                console.log('[Auto-Save] ' + message, data || '');
            }
        }
        
        // Initial log to verify script is loading
        console.log('[Auto-Save] initAutoSave initialized. Round ID:', roundId, 'Judge ID:', judgeId);
        
        // Auto-save functionality with validation
        $(document).on('input change', '.score-input', function() {
            const $input = $(this);
            
            // Skip if input is readonly or disabled
            if ($input.prop('readonly') || $input.prop('disabled')) {
                return;
            }
            
            const contestantId = $input.data('contestant-id');
            const criteriaId = $input.data('criteria-id');
            
            if (!contestantId || !criteriaId) {
                return;
            }
            
            let inputValue = $input.val();
            if (typeof inputValue === 'string') {
                inputValue = inputValue.trim();
            }
            
            console.log('[Auto-Save] Input event triggered', {
                contestantId: contestantId,
                criteriaId: criteriaId,
                inputValue: inputValue
            });
            
            // Parse value for saving
            let value = parseFloat(inputValue);
            
            // Get max score from data attribute
            const maxScore = parseFloat($input.data('max')) || 0;
            
            // Format to 2 decimal places if it's a valid number with decimals
            if (!isNaN(value) && inputValue && inputValue.toString().includes('.')) {
                const decimalPlaces = inputValue.toString().split('.')[1];
                if (decimalPlaces && decimalPlaces.length > 2) {
                    value = Math.round(value * 100) / 100;
                    $input.val(value.toFixed(2));
                }
            }
            
            // Don't save if input is empty or invalid (but allow 0 as valid score)
            if (!inputValue || inputValue === '' || isNaN(value)) {
                checkSubmitButton();
                return;
            }
            
            // Validate max score - check if value exceeds max
            if (!isNaN(maxScore) && maxScore > 0 && value > maxScore) {
                // Show warning and prevent saving
                showMaxValueWarning($input, maxScore);
                $input.addClass('border-danger');
                logAutoSave('Value exceeds max score, preventing save', {
                    value: value,
                    maxScore: maxScore
                });
                // Clear any pending save timer
                if (autoSaveTimer) {
                    clearTimeout(autoSaveTimer);
                    autoSaveTimer = null;
                }
                // Hide saving status
                $('#autoSaveStatus').hide();
                $('#savedStatus').hide();
                $('#draftStatus').fadeIn();
                checkSubmitButton();
                return;
            }
            
            // Remove error styling if value is valid
            $input.removeClass('border-danger');
            $input.next('.max-value-warning').remove();
            
            // Show saving status
            $('#autoSaveStatus').fadeIn();
            $('#savedStatus').hide();
            $('#draftStatus').hide();
            logAutoSave('Starting auto-save timer for contestant ' + contestantId + ', criteria ' + criteriaId + ', value: ' + value);
            
            // Clear existing timer
            if (autoSaveTimer) {
                clearTimeout(autoSaveTimer);
            }
            
            // Set new timer (save after 1 second of no typing)
            const finalValue = value;
            const $inputRef = $input;
            autoSaveTimer = setTimeout(function() {
                logAutoSave('Auto-save timer triggered, calling saveScore');
                const currentInputValue = parseFloat($inputRef.val()) || finalValue;
                if (!isNaN(currentInputValue)) {
                    saveScore(contestantId, criteriaId, currentInputValue, $inputRef);
                } else {
                    checkSubmitButton();
                }
            }, 1000);
            
            // Check submit button immediately when value changes
            checkSubmitButton();
        });
        
        // Format on blur and trigger immediate save
        $(document).on('blur', '.score-input', function() {
            const $input = $(this);
            
            if ($input.prop('readonly') || $input.prop('disabled')) {
                return;
            }
            
            const contestantId = $input.data('contestant-id');
            const criteriaId = $input.data('criteria-id');
            
            if (!contestantId || !criteriaId) {
                return;
            }
            
            let value = parseFloat($input.val()) || 0;
            let finalValue = value;
            
            // Get max score from data attribute
            const maxScore = parseFloat($input.data('max')) || 0;
            
            // Set to 0 if negative
            if (finalValue < 0) finalValue = 0;
            
            // Check if value exceeds max score
            if (!isNaN(maxScore) && maxScore > 0 && finalValue > maxScore) {
                // Cap value to max score
                finalValue = maxScore;
                $input.val(finalValue.toFixed(2));
                showMaxValueWarning($input, maxScore);
                $input.addClass('border-danger');
                logAutoSave('Value exceeded max score, capped to max', {
                    originalValue: value,
                    maxScore: maxScore,
                    finalValue: finalValue
                });
            } else {
                // Format to 2 decimal places
                finalValue = Math.round(finalValue * 100) / 100;
                $input.val(finalValue.toFixed(2));
                // Remove error styling if value is valid
                $input.removeClass('border-danger');
                $input.next('.max-value-warning').remove();
            }
            
            // Clear any pending timer and save immediately
            if (autoSaveTimer) {
                clearTimeout(autoSaveTimer);
                autoSaveTimer = null;
            }
            
            // Save immediately if value is valid
            if (!isNaN(finalValue) && finalValue >= 0) {
                const inputValue = $input.val().trim();
                if (inputValue && inputValue !== '') {
                    logAutoSave('Blur event - saving immediately', {
                        contestantId: contestantId,
                        criteriaId: criteriaId,
                        value: finalValue
                    });
                    saveScore(contestantId, criteriaId, finalValue, $input);
                }
            }
            
            checkSubmitButton();
        });
        
        // Show warning for max value exceeded
        function showMaxValueWarning($input, max) {
            $input.next('.max-value-warning').remove();
            const $warning = $('<small class="text-danger d-block max-value-warning mt-1">')
                .html('<i class="fas fa-exclamation-triangle"></i> Max: ' + max.toFixed(2));
            $input.after($warning);
            setTimeout(function() {
                $warning.fadeOut(function() {
                    $(this).remove();
                });
            }, 3000);
        }
        
        // Save score function
        function saveScore(contestantId, criteriaId, value, $input) {
            if (isSaving) {
                return;
            }
            
            const numValue = parseFloat(value);
            
            logAutoSave('saveScore called', {
                contestantId: contestantId,
                criteriaId: criteriaId,
                value: value,
                numValue: numValue
            });
            
            if (isNaN(numValue)) {
                logAutoSave('Invalid value, skipping save');
                return; // Don't save invalid values
            }
            
            // Allow saving 0 as a valid score
            // Only check if value is negative
            if (numValue < 0) {
                logAutoSave('Value is negative, skipping save');
                return;
            }
            
            // Check max score validation before saving
            const maxScore = parseFloat($input.data('max')) || 0;
            if (!isNaN(maxScore) && maxScore > 0 && numValue > maxScore) {
                // Show warning and prevent save
                showMaxValueWarning($input, maxScore);
                $input.addClass('border-danger');
                logAutoSave('Value exceeds max score, preventing save', {
                    value: numValue,
                    maxScore: maxScore
                });
                // Hide saving status
                $('#autoSaveStatus').hide();
                $('#savedStatus').hide();
                $('#draftStatus').fadeIn();
                return; // Don't save if exceeds max
            }
            
            isSaving = true;
            
            const saveUrl = '/tabulation/judge/rounds/' + roundId + '/contestants/' + contestantId + '/auto-save';
            const saveData = {
                csrf_token: <?php echo json_encode(Session::getCSRFToken() ?: '', JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
                criteria_id: criteriaId,
                raw_score: numValue
            };
            
            $.ajax({
                url: saveUrl,
                method: 'POST',
                dataType: 'json',
                data: saveData,
                success: function(response) {
                    if (response.success) {
                        saveCount++;
                        const saveTime = new Date().toLocaleTimeString();
                        logAutoSave('Auto-save successful', {
                            contestantId: contestantId,
                            criteriaId: criteriaId,
                            score: numValue,
                            totalSaves: saveCount
                        });
                        
                        $('#autoSaveStatus').hide();
                        $('#savedStatus').fadeIn();
                        $('#saveTime').text('(' + saveTime + ')');
                        $('#saveCount').text(saveCount);
                        $('#saveCounter').fadeIn();
                        
                        setTimeout(function() {
                            $('#savedStatus').fadeOut(function() {
                                $('#draftStatus').fadeIn();
                            });
                        }, 2000);
                        
                        checkSubmitButton();
                    } else {
                        logAutoSave('Auto-save failed', response);
                    }
                    isSaving = false;
                },
                error: function(xhr, status, error) {
                    logAutoSave('Auto-save error', {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        url: saveUrl
                    });
                    isSaving = false;
                }
            });
        }
        
        // Check if submit button should be enabled
        function checkSubmitButton() {
            let filledCount = 0;
            let totalEditable = 0;
            let exceededMaxScores = [];
            
            $('.score-input:not([readonly]):not([disabled])').each(function() {
                const $input = $(this);
                totalEditable++;
                
                let value = $input.val();
                if (typeof value === 'string') {
                    value = value.trim();
                }
                
                if (!value || value === '') {
                    return true;
                }
                
                const numValue = parseFloat(value);
                if (isNaN(numValue)) {
                    return true;
                }
                
                // Check if value exceeds max score
                const maxScore = parseFloat($input.data('max')) || 0;
                if (!isNaN(maxScore) && maxScore > 0 && numValue > maxScore) {
                    exceededMaxScores.push({
                        value: numValue,
                        max: maxScore
                    });
                    return true;
                }
                
                filledCount++;
            });
            
            // Enable button only if all editable inputs are filled AND no scores exceed max
            if (totalEditable > 0 && filledCount === totalEditable && exceededMaxScores.length === 0) {
                $('#submitAllBtn').prop('disabled', false);
                logAutoSave('Submit All button enabled - all ' + filledCount + ' of ' + totalEditable + ' scores filled');
            } else {
                $('#submitAllBtn').prop('disabled', true);
                if (exceededMaxScores.length > 0) {
                    logAutoSave('Submit All button disabled - ' + exceededMaxScores.length + ' score(s) exceed maximum');
                } else {
                logAutoSave('Submit All button disabled - ' + filledCount + ' of ' + totalEditable + ' scores filled');
                }
            }
        }
        
        // Submit all scores
        $('#submitAllBtn').on('click', function() {
            // First, validate all scores before showing confirmation
            let exceededMaxScores = [];
            let hasEmptyScores = false;
            
            $('.score-input:not([readonly]):not([disabled])').each(function() {
                const $input = $(this);
                const value = $input.val();
                const maxScore = parseFloat($input.data('max')) || 0;
                
                // Check if empty
                if (!value || value === '' || value === '0' || value === '0.00' || value === '0.0') {
                    hasEmptyScores = true;
                    return;
                }
                
                // Check if exceeds max
                const numValue = parseFloat(value);
                if (!isNaN(numValue) && !isNaN(maxScore) && maxScore > 0 && numValue > maxScore) {
                    exceededMaxScores.push({
                        value: numValue.toFixed(2),
                        max: maxScore.toFixed(2)
                    });
                }
            });
            
            // Show error if there are scores exceeding max
            if (exceededMaxScores.length > 0) {
                Swal.fire({
                    title: 'Invalid Scores',
                    text: 'Cannot submit: ' + exceededMaxScores.length + ' score(s) exceed the maximum allowed. Please fix these scores before submitting.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return;
            }
            
            // Show error if there are empty scores
            if (hasEmptyScores) {
                Swal.fire({
                    title: 'Incomplete Scores',
                    text: 'Please fill in all scores before submitting.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                return;
            }
            
            // All validations passed, show confirmation
            Swal.fire({
                title: 'Submit All Scores?',
                text: 'Once submitted, you will need permission to edit them.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, submit all',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }
                
                const $btn = $(this);
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Submitting...');
                
                $.ajax({
                    url: '/tabulation/judge/rounds/' + roundId + '/submit-all',
                    method: 'POST',
                    data: {
                        csrf_token: <?php echo json_encode(Session::getCSRFToken() ?: '', JSON_HEX_APOS | JSON_HEX_QUOT); ?>
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Success!', 'All scores submitted successfully!', 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error', response.message || 'Unknown error', 'error');
                            $btn.prop('disabled', false).html('<i class="fas fa-check-circle"></i> Submit All Scores');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Error submitting scores. Please try again.', 'error');
                        $btn.prop('disabled', false).html('<i class="fas fa-check-circle"></i> Submit All Scores');
                    }
                });
            });
        });
        
        // Initial check
        checkSubmitButton();
        
        const inputCount = $('.score-input').length;
        const editableCount = $('.score-input:not([readonly])').length;
        console.log('[Auto-Save] Initialization complete', {
            totalInputs: inputCount,
            editableInputs: editableCount,
            roundId: roundId,
            judgeId: judgeId
        });
    }
    
    // Initialize when jQuery is ready (jQuery should already be loaded from footer)
    if (typeof jQuery !== 'undefined' && typeof $ !== 'undefined') {
        console.log('[Auto-Save] jQuery found, initializing...');
        jQuery(document).ready(function() {
            console.log('[Auto-Save] Document ready, calling initAutoSave');
            initAutoSave();
        });
    } else {
        console.error('[Auto-Save] jQuery not found! Script must run after footer loads jQuery.');
    }
})();
console.log('[Auto-Save] Script initialization complete');

// Attach permission handlers - moved here to ensure jQuery is loaded
console.log('[Permission] ===== ATTACHING PERMISSION HANDLERS =====');
(function attachPermissionHandlers() {
    console.log('[Permission] Function attachPermissionHandlers called');
    if (typeof jQuery !== 'undefined' && typeof $ !== 'undefined') {
        console.log('[Permission] jQuery found, attaching permission handlers...');
        jQuery(document).ready(function() {
            console.log('[Permission] Document ready, attaching handlers...');
            
            // Request edit permission handler
            $(document).on('click', '.request-edit-permission', function(e) {
                console.log('[Permission] ===== REQUEST BUTTON CLICKED =====');
                console.log('[Permission] Handler is working!');
                
                e.preventDefault();
                e.stopPropagation();
                
                // Get roundId from multiple sources (priority order)
                let roundId = null;
                
                // 1. Try from window (set by initAutoSave)
                if (typeof window.roundId !== 'undefined' && window.roundId) {
                    roundId = window.roundId;
                }
                
                // 2. Try from input element data attribute
                if (!roundId) {
                    const $firstInput = $('.score-input').first();
                    if ($firstInput.length) {
                        roundId = $firstInput.data('round-id');
                    }
                }
                
                // 3. Try to extract from URL
                if (!roundId) {
                    const urlMatch = window.location.pathname.match(/\/rounds\/(\d+)/);
                    if (urlMatch && urlMatch[1]) {
                        roundId = parseInt(urlMatch[1]);
                    }
                }
                
                const contestantId = $(this).data('contestant-id');
                const scoreId = $(this).data('score-id');
                const $btn = $(this);
                
                console.log('[Permission] Round ID:', roundId);
                console.log('[Permission] Contestant ID:', contestantId);
                console.log('[Permission] Score ID:', scoreId);
                
                if (!roundId) {
                    console.error('[Permission] Round ID not found');
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Error', 'Round ID not found. Please refresh the page.', 'error');
                    } else {
                        alert('Error: Round ID not found. Please refresh the page.');
                    }
                    return;
                }
                
                if (!contestantId || contestantId === '' || contestantId === '0') {
                    console.error('[Permission] Contestant ID is missing or invalid:', contestantId);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Error', 'Contestant ID not found. Please refresh the page.', 'error');
                    } else {
                        alert('Error: Contestant ID not found.');
                    }
                    return;
                }
                
                if (!scoreId || scoreId === '' || scoreId === '0') {
                    console.error('[Permission] Score ID is missing or invalid:', scoreId);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Error', 'Score ID not found. Please refresh the page.', 'error');
                    } else {
                        alert('Error: Score ID not found.');
                    }
                    return;
                }
                
                // Check if SweetAlert is available
                if (typeof Swal === 'undefined') {
                    console.error('[Permission] SweetAlert2 is not loaded!');
                    alert('Error: SweetAlert2 is not loaded. Please refresh the page.');
                    return;
                }
                
                console.log('[Permission] All IDs validated. Showing confirmation modal...');
                
                Swal.fire({
                    title: 'Request Edit Permission?',
                    text: 'Request permission from Admin/Tabulator to edit this submitted score?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, request',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (!result.isConfirmed) {
                        return;
                    }
                    
                    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Requesting...');
                    
                    const url = '/tabulation/judge/rounds/' + roundId + '/request-edit-permission';
                    const csrfToken = <?php echo json_encode(Session::getCSRFToken() ?: '', JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
                    
                    console.log('[Permission] Sending AJAX request to:', url);
                    console.log('[Permission] CSRF Token:', csrfToken ? 'Present' : 'Missing');
                    
                    $.ajax({
                        url: url,
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        dataType: 'json',
                        data: {
                            csrf_token: csrfToken,
                            score_id: scoreId,
                            contestant_id: contestantId
                        },
                        success: function(response) {
                            if (typeof response === 'string') {
                                try {
                                    response = JSON.parse(response);
                                } catch (e) {
                                    Swal.fire('Error', 'Invalid response from server', 'error');
                                    $btn.prop('disabled', false).html('<i class="fas fa-edit"></i> Request Edit');
                                    return;
                                }
                            }
                            
                            if (response && response.success) {
                                Swal.fire({
                                    title: '<strong>Request Sent!</strong>',
                                    html: '<p>Your permission request has been sent to Admin/Tabulator.</p>',
                                    icon: 'success',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Error', response.message || 'Unknown error', 'error');
                                $btn.prop('disabled', false).html('<i class="fas fa-edit"></i> Request Edit');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('[Permission] AJAX Error:', {
                                status: status,
                                error: error,
                                responseText: xhr.responseText,
                                statusCode: xhr.status
                            });
                            
                            let errorMessage = 'Error sending request. Please try again.';
                            if (xhr.responseText) {
                                try {
                                    const response = JSON.parse(xhr.responseText);
                                    if (response.message) {
                                        errorMessage = response.message;
                                    } else if (response.error) {
                                        errorMessage = response.error;
                                    }
                                } catch (e) {
                                    if (xhr.responseText.includes('CSRF token')) {
                                        errorMessage = 'CSRF token validation failed. Please refresh the page and try again.';
                                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                                }
                            }
                            
                            Swal.fire('Error', errorMessage, 'error');
                            $btn.prop('disabled', false).html('<i class="fas fa-edit"></i> Request Edit');
                        }
                    });
                });
            });
            
            // Grant permission handler
            $(document).on('click', '.grant-permission-btn', function(e) {
                console.log('[Permission] ===== GRANT BUTTON CLICKED =====');
                console.log('[Permission] Handler is working!');
                
                e.preventDefault();
                e.stopPropagation();
                
                const scoreId = $(this).data('score-id');
                const $btn = $(this);
                
                if (!scoreId) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Error', 'Score ID not found.', 'error');
                    }
                    return;
                }
                
                if (typeof Swal === 'undefined') {
                    alert('Error: SweetAlert2 is not loaded.');
                    return;
                }
                
                Swal.fire({
                    title: 'Grant Permission?',
                    text: 'Are you sure you want to grant permission to Admin/Tabulator to edit this score?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, grant',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (!result.isConfirmed) {
                        return;
                    }
                    
                    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
                    
                    const url = '/tabulation/score-management/' + scoreId + '/respond-permission';
                    const csrfToken = $('meta[name="csrf-token"]').attr('content') || $('input[name="csrf_token"]').val() || '';
                    
                    $.ajax({
                        url: url,
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        dataType: 'json',
                        data: {
                            csrf_token: csrfToken,
                            action: 'grant'
                        },
                        success: function(response) {
                            if (typeof response === 'string') {
                                try {
                                    response = JSON.parse(response);
                                } catch (e) {
                                    Swal.fire('Error', 'Invalid response from server', 'error');
                                    $btn.prop('disabled', false).html('<i class="fas fa-check"></i> Grant');
                                    return;
                                }
                            }
                            
                            if (response && response.success) {
                                Swal.fire({
                                    title: 'Success!',
                                    text: 'Permission granted. Admin can now edit this score.',
                                    icon: 'success',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Error', (response && response.message) || 'Unknown error', 'error');
                                $btn.prop('disabled', false).html('<i class="fas fa-check"></i> Grant');
                            }
                        },
                        error: function(xhr, status, error) {
                            let errorMessage = 'Error processing request. Please try again.';
                            if (xhr.responseText) {
                                try {
                                    const response = JSON.parse(xhr.responseText);
                                    if (response.message) {
                                        errorMessage = response.message;
                                    } else if (response.error) {
                                        errorMessage = response.error;
                                    }
                                } catch (e) {
                                    if (xhr.responseText.includes('CSRF token')) {
                                        errorMessage = 'CSRF token validation failed. Please refresh the page and try again.';
                                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                                    }
                                }
                            }
                            Swal.fire('Error', errorMessage, 'error');
                            $btn.prop('disabled', false).html('<i class="fas fa-check"></i> Grant');
                        }
                    });
                });
            });
            
            // Deny permission handler
            $(document).on('click', '.deny-permission-btn', function(e) {
                console.log('[Permission] ===== DENY BUTTON CLICKED =====');
                console.log('[Permission] Handler is working!');
                
                e.preventDefault();
                e.stopPropagation();
                
                const scoreId = $(this).data('score-id');
                const $btn = $(this);
                
                if (!scoreId) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Error', 'Score ID not found.', 'error');
                    }
                    return;
                }
                
                if (typeof Swal === 'undefined') {
                    alert('Error: SweetAlert2 is not loaded.');
                    return;
                }
                
                Swal.fire({
                    title: 'Deny Permission?',
                    text: 'Are you sure you want to deny permission to Admin/Tabulator to edit this score?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, deny',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (!result.isConfirmed) {
                        return;
                    }
                    
                    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
                    
                    const url = '/tabulation/score-management/' + scoreId + '/respond-permission';
                    const csrfToken = $('meta[name="csrf-token"]').attr('content') || $('input[name="csrf_token"]').val() || '';
                    
                    $.ajax({
                        url: url,
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        dataType: 'json',
                        data: {
                            csrf_token: csrfToken,
                            action: 'deny'
                        },
                        success: function(response) {
                            if (typeof response === 'string') {
                                try {
                                    response = JSON.parse(response);
                                } catch (e) {
                                    Swal.fire('Error', 'Invalid response from server', 'error');
                                    $btn.prop('disabled', false).html('<i class="fas fa-times"></i> Deny');
                                    return;
                                }
                            }
                            
                            if (response && response.success) {
                                Swal.fire({
                                    title: 'Success!',
                                    text: 'Permission denied.',
                                    icon: 'success',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Error', (response && response.message) || 'Unknown error', 'error');
                                $btn.prop('disabled', false).html('<i class="fas fa-times"></i> Deny');
                            }
                        },
                        error: function(xhr, status, error) {
                            let errorMessage = 'Error processing request. Please try again.';
                            if (xhr.responseText) {
                                try {
                                    const response = JSON.parse(xhr.responseText);
                                    if (response.message) {
                                        errorMessage = response.message;
                                    } else if (response.error) {
                                        errorMessage = response.error;
                                    }
                                } catch (e) {
                                    if (xhr.responseText.includes('CSRF token')) {
                                        errorMessage = 'CSRF token validation failed. Please refresh the page and try again.';
                                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                                    }
                                }
                            }
                            Swal.fire('Error', errorMessage, 'error');
                            $btn.prop('disabled', false).html('<i class="fas fa-times"></i> Deny');
                        }
                    });
                });
            });
            
            console.log('[Permission] All permission handlers attached successfully!');
        });
    } else {
        console.log('[Permission] Waiting for jQuery...');
        setTimeout(attachPermissionHandlers, 50);
    }
})(); // Self-execute immediately
</script>
