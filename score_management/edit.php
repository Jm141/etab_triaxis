<?php 
$title = 'Edit Score - ' . $score['contestant_name'];
require __DIR__ . '/../layout/header.php'; 
require_once __DIR__ . '/../../core/ScoreFormatter.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-edit"></i> Edit Score</h1>
    <a href="/tabulation/score-management/round/<?= $score['round_id'] ?>" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-body text-center">
                <h4><?= htmlspecialchars($score['contestant_name']) ?></h4>
                <p class="text-muted mb-2">#<?= htmlspecialchars($score['contestant_number']) ?></p>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Score Information</h6>
            </div>
            <div class="card-body">
                <p class="mb-1"><strong>Round:</strong> <?= htmlspecialchars($score['round_name']) ?></p>
                <p class="mb-1"><strong>Judge:</strong> <?= htmlspecialchars($score['judge_name']) ?></p>
                <p class="mb-1"><strong>Judge #:</strong> <?= htmlspecialchars($score['judge_number'] ?: '-') ?></p>
                <p class="mb-0"><strong>Current Total:</strong> <strong><?= ScoreFormatter::format($score['total_score'], 2) ?></strong></p>
            </div>
        </div>
        
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Note:</strong> This score was submitted by the judge. 
            Changes will be logged and visible to the judge.
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-list-check"></i> Edit Scores</h5>
            </div>
            <div class="card-body">
                <form id="scoreForm" method="POST" action="/tabulation/score-management/<?= $score['id'] ?>/update">
                    <input type="hidden" name="csrf_token" value="<?= Session::getCSRFToken() ?>">
                    
                    <?php if (empty($criteria)): ?>
                        <div class="alert alert-warning">
                            No criteria configured for this round.
                        </div>
                    <?php else: ?>
                        <?php foreach ($criteria as $criterion): ?>
                            <?php 
                            $existingScore = null;
                            foreach ($scoreDetails as $detail) {
                                if ($detail['criteria_id'] == $criterion['id']) {
                                    $existingScore = $detail['raw_score'];
                                    break;
                                }
                            }
                            ?>
                            
                            <div class="mb-4 p-3 border rounded criterion-container">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-1 criterion-name"><?= htmlspecialchars($criterion['name']) ?></h6>
                                        <?php if ($criterion['description']): ?>
                                            <small class="text-muted"><?= htmlspecialchars($criterion['description']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <span class="badge badge-info">Weight: <?= $criterion['weight'] ?>%</span>
                                </div>
                                
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <label class="form-label">
                                            Score (0 - <?= $criterion['max_score'] ?>)
                                        </label>
                                        <input type="number" 
                                               class="form-control form-control-lg score-input" 
                                               name="criteria_<?= $criterion['id'] ?>" 
                                               value="<?= $existingScore ? ScoreFormatter::format($existingScore, 2) : '' ?>" 
                                               min="0" 
                                               max="<?= $criterion['max_score'] ?>" 
                                               step="0.01"
                                               required
                                               data-max="<?= $criterion['max_score'] ?>"
                                               data-weight="<?= $criterion['weight'] ?>"
                                               data-original-value="<?= $existingScore ? ScoreFormatter::format($existingScore, 2) : '0' ?>"
                                               placeholder="0.000">
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center">
                                            <small class="text-muted d-block">Max Score</small>
                                            <h4 class="mb-0"><?= $criterion['max_score'] ?></h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <hr class="my-4">
                        
                        <?php if ($needsPermission): ?>
                        <div class="alert alert-warning mb-3" id="permissionAlert">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Permission Required:</strong> You must request permission from the judge to edit this score.
                            <br><small class="text-muted">Once permission is granted, you can edit. After editing, permission will be reset and you must request again for future edits.</small>
                        </div>
                        
                        <!-- Success notification banner (hidden by default) -->
                        <div class="alert alert-success alert-dismissible fade mb-3" id="requestSuccessBanner" role="alert" style="display: none;">
                            <h5 class="alert-heading"><i class="fas fa-check-circle"></i> Request Sent Successfully!</h5>
                            <p class="mb-2">Your permission request has been sent to <strong><?= htmlspecialchars($score['judge_name']) ?></strong>.</p>
                            <p class="mb-0"><small><i class="fas fa-bell"></i> You will receive a notification when the judge responds.</small></p>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        
                        <div class="mb-3">
                            <button type="button" class="btn btn-primary btn-lg" id="requestPermissionBtn">
                                <i class="fas fa-hand-paper"></i> Request Permission from Judge
                            </button>
                            <small class="form-text text-muted d-block mt-2">
                                Click this button to send a permission request to the judge. You will be notified when they respond.
                            </small>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-success mb-3" id="permissionGrantedAlert">
                            <i class="fas fa-check-circle"></i>
                            <strong>Permission Granted:</strong> Judge has granted permission to edit this score.
                            <br><small class="text-muted">You have <strong id="permissionCountdown">30</strong> seconds to make changes. Permission will expire if no changes are submitted within 30 seconds.</small>
                            <div class="progress mt-2" style="height: 5px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-warning" 
                                     id="permissionProgressBar" 
                                     role="progressbar" 
                                     style="width: 100%"></div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-warning btn-lg" id="submitBtn" <?= $needsPermission ? 'disabled' : '' ?>>
                                <i class="fas fa-save"></i> Update Score
                            </button>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>

<script>
// Simple, direct approach - wait for page to fully load
console.log('[Permission Request] Script block loaded');

// Store PHP variables at script level - with error handling
<?php
// Safely get values with fallbacks
$judgeName = isset($score['judge_name']) ? (string)$score['judge_name'] : '';
$contestantNumber = isset($score['contestant_number']) ? (string)$score['contestant_number'] : '';
$contestantName = isset($score['contestant_name']) ? (string)$score['contestant_name'] : '';
$roundName = isset($score['round_name']) ? (string)$score['round_name'] : '';
$scoreId = isset($score['id']) ? (int)$score['id'] : 0;
$hasPermission = isset($score['admin_edit_allowed']) && $score['admin_edit_allowed'] == 1;
$permissionGrantedAt = isset($score['permission_granted_at']) && !empty($score['permission_granted_at']) 
    ? strtotime($score['permission_granted_at']) 
    : null;
$csrfToken = '';
try {
    if (class_exists('Session') && method_exists('Session', 'getCSRFToken')) {
        $csrfToken = Session::getCSRFToken();
    }
} catch (Exception $e) {
    // Ignore
}
if (empty($csrfToken) && isset($_SESSION['csrf_token'])) {
    $csrfToken = $_SESSION['csrf_token'];
}
?>
const permissionRequestData = {
    judgeName: <?php echo json_encode($judgeName, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>,
    contestantNumber: <?php echo json_encode($contestantNumber, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>,
    contestantName: <?php echo json_encode($contestantName, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>,
    roundName: <?php echo json_encode($roundName, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>,
    scoreId: <?php echo $scoreId; ?>,
    csrfToken: <?php echo json_encode($csrfToken, JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
    hasPermission: <?php echo $hasPermission ? 'true' : 'false'; ?>,
    permissionGrantedAt: <?php echo $permissionGrantedAt ? $permissionGrantedAt : 'null'; ?>
};

console.log('[Permission Request] Data loaded:', permissionRequestData);

// Simple jQuery ready handler
jQuery(document).ready(function($) {
    console.log('[Permission Request] jQuery ready - initializing');
    console.log('[Permission Request] Swal available:', typeof Swal !== 'undefined');
    
    // Find button
    const $requestBtn = $('#requestPermissionBtn');
    console.log('[Permission Request] Button found:', $requestBtn.length > 0);
    
    if ($requestBtn.length === 0) {
        console.warn('[Permission Request] Button not found - might not need permission');
        return;
    }
    
    // Attach click handler
    $requestBtn.on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('[Permission Request] ===== BUTTON CLICKED =====');
        
        const $btn = $(this);
        
        // Check if Swal is available
        if (typeof Swal === 'undefined') {
            console.error('[Permission Request] Swal is not available!');
            alert('SweetAlert2 is not loaded. Please refresh the page.');
            return;
        }
        
        console.log('[Permission Request] Showing confirmation modal...');
        
        // Show confirmation modal
        Swal.fire({
            title: 'Request Permission to Edit Score?',
            html: '<div class="text-left">' +
                '<p>You are requesting permission from the judge to edit this score.</p>' +
                '<div class="alert alert-info mt-3 mb-0">' +
                '<strong>Contestant:</strong> #' + permissionRequestData.contestantNumber + ' - ' + permissionRequestData.contestantName + '<br>' +
                '<strong>Round:</strong> ' + permissionRequestData.roundName + '<br>' +
                '<strong>Judge:</strong> ' + permissionRequestData.judgeName +
                '</div>' +
                '<p class="mt-3"><small class="text-muted">The judge will receive a notification and can grant or deny your request.</small></p>' +
                '</div>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-paper-plane"></i> Yes, send request',
            cancelButtonText: '<i class="fas fa-times"></i> Cancel',
            reverseButtons: true,
            width: '500px'
        }).then((result) => {
            console.log('[Permission Request] Swal result:', result);
            if (result.isConfirmed) {
                console.log('[Permission Request] User confirmed - sending request');
                
                // Show loading
                Swal.fire({
                    title: 'Sending Request...',
                    text: 'Please wait while we send the permission request to the judge.',
                    icon: 'info',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                $btn.prop('disabled', true).html('<i class="fas fa-hourglass"></i> Sending Request...');
                
                // Send AJAX request
                $.ajax({
                    url: '/tabulation/score-management/' + permissionRequestData.scoreId + '/request-edit-permission',
                    method: 'POST',
                    data: {
                        csrf_token: permissionRequestData.csrfToken
                    },
                    dataType: 'json',
                    success: function(response) {
                        console.log('[Permission Request] AJAX success:', response);
                        if (response.success) {
                            $('#requestSuccessBanner').fadeIn(500).addClass('show');
                            
                            Swal.fire({
                                title: '<strong>Request Pushed Successfully!</strong>',
                                html: '<div class="text-center">' +
                                    '<i class="fas fa-paper-plane fa-3x text-success mb-3"></i>' +
                                    '<p class="mb-2">Permission request has been sent to <strong>' + permissionRequestData.judgeName + '</strong>.</p>' +
                                    '<p class="text-muted"><small><i class="fas fa-bell"></i> You will be notified when they respond</small></p>' +
                                    '</div>',
                                icon: 'success',
                                confirmButtonText: '<i class="fas fa-check"></i> OK',
                                confirmButtonColor: '#28a745',
                                timer: 4000,
                                timerProgressBar: true
                            }).then(() => {
                                const Toast = Swal.mixin({
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 5000,
                                    timerProgressBar: true
                                });
                                
                                Toast.fire({
                                    icon: 'success',
                                    title: '<strong>Request Sent!</strong>',
                                    html: '<small>Waiting for judge response...</small>'
                                });
                                
                                $btn.prop('disabled', true).html('<i class="fas fa-check"></i> Request Sent - Waiting for Response');
                                $btn.removeClass('btn-primary').addClass('btn-success');
                                $('#permissionAlert').fadeOut(300);
                                
                                setTimeout(() => {
                                    location.reload();
                                }, 2000);
                            });
                        } else {
                            Swal.fire({
                                title: 'Request Failed',
                                text: response.message || 'Failed to send request. Please try again.',
                                icon: 'error',
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#dc3545'
                            });
                            $btn.prop('disabled', false).html('<i class="fas fa-hand-paper"></i> Request Permission from Judge');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('[Permission Request] AJAX error:', { xhr, status, error });
                        let errorMsg = 'Error sending request. Please try again.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            title: 'Error',
                            text: errorMsg,
                            icon: 'error',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#dc3545'
                        });
                        $btn.prop('disabled', false).html('<i class="fas fa-hand-paper"></i> Request Permission from Judge');
                    }
                });
            } else {
                console.log('[Permission Request] User cancelled');
            }
        });
    });
    
    console.log('[Permission Request] Event handler attached successfully');
    
    // Permission timeout countdown timer (30 seconds)
    if (permissionRequestData.hasPermission && permissionRequestData.permissionGrantedAt) {
        let permissionTimeout = null;
        let countdownInterval = null;
        
        function startPermissionCountdown() {
            const grantedTime = permissionRequestData.permissionGrantedAt;
            const timeoutSeconds = 30;
            
            function updateCountdown() {
                const now = Math.floor(Date.now() / 1000);
                const elapsed = now - grantedTime;
                const remaining = Math.max(0, timeoutSeconds - elapsed);
                
                const $countdown = $('#permissionCountdown');
                const $progressBar = $('#permissionProgressBar');
                const $alert = $('#permissionGrantedAlert');
                
                if (remaining > 0) {
                    $countdown.text(remaining);
                    const percentage = (remaining / timeoutSeconds) * 100;
                    $progressBar.css('width', percentage + '%');
                    
                    if (remaining <= 10) {
                        $progressBar.removeClass('bg-warning').addClass('bg-danger');
                        $alert.removeClass('alert-success').addClass('alert-danger');
                    } else if (remaining <= 15) {
                        $progressBar.removeClass('bg-success').addClass('bg-warning');
                    }
                } else {
                    // Timeout expired - void permission
                    clearInterval(countdownInterval);
                    $countdown.text('0');
                    $progressBar.css('width', '0%');
                    
                    Swal.fire({
                        title: 'Permission Expired',
                        html: '<div class="text-center">' +
                            '<i class="fas fa-clock fa-3x text-warning mb-3"></i>' +
                            '<p>Your permission to edit has expired (30 seconds).</p>' +
                            '<p class="text-muted"><small>Please request permission again to continue editing.</small></p>' +
                            '</div>',
                        icon: 'warning',
                        confirmButtonText: 'Request Permission Again',
                        confirmButtonColor: '#3085d6'
                    }).then(() => {
                        location.reload();
                    });
                }
            }
            
            // Update immediately
            updateCountdown();
            
            // Update every second
            countdownInterval = setInterval(updateCountdown, 1000);
        }
        
        // Start countdown when page loads
        startPermissionCountdown();
        
        // Track activity on score inputs
        let lastActivityTime = Math.floor(Date.now() / 1000);
        $('.score-input').on('input change', function() {
            lastActivityTime = Math.floor(Date.now() / 1000);
            console.log('[Permission] Activity detected at:', lastActivityTime);
        });
    }
});

// Score form handler (separate from permission request)
jQuery(document).ready(function($) {
    console.log('[Score Form] jQuery document ready fired');
    
    $('#scoreForm').on('submit', function(e) {
                e.preventDefault();
        
        // Check if permission has expired (30 seconds)
        if (permissionRequestData.hasPermission && permissionRequestData.permissionGrantedAt) {
            const now = Math.floor(Date.now() / 1000);
            const elapsed = now - permissionRequestData.permissionGrantedAt;
            
            if (elapsed > 30) {
                Swal.fire({
                    title: 'Permission Expired',
                    html: '<div class="text-center">' +
                        '<i class="fas fa-clock fa-3x text-warning mb-3"></i>' +
                        '<p>Your permission to edit has expired (30 seconds elapsed).</p>' +
                        '<p class="text-muted"><small>Please request permission again to continue editing.</small></p>' +
                        '</div>',
                    icon: 'warning',
                    confirmButtonText: 'Request Permission Again',
                    confirmButtonColor: '#3085d6'
                }).then(() => {
                    location.reload();
                });
                return false;
            }
        }
        
        // Get current score values to show in confirmation
        let scoreChanges = [];
        $('.score-input').each(function() {
            const $input = $(this);
            const currentValue = parseFloat($input.val()) || 0;
            const originalValue = parseFloat($input.data('original-value')) || 0;
            const $container = $input.closest('.criterion-container');
            const criterionName = $container.find('.criterion-name').text().trim() || 'Unknown Criterion';
            
            if (Math.abs(currentValue - originalValue) > 0.001) {
                    scoreChanges.push({
                        name: criterionName,
                        from: originalValue.toFixed(2),
                        to: currentValue.toFixed(2)
                    });
            }
        });
        
        let changesHtml = '';
        if (scoreChanges.length > 0) {
            changesHtml = '<div class="alert alert-info mt-3 mb-0 text-left"><strong>Score Changes:</strong><ul class="mb-0 mt-2">';
            scoreChanges.forEach(function(change) {
                   changesHtml += `<li><strong>${change.name}:</strong> ${parseFloat(change.from).toFixed(2)} → ${parseFloat(change.to).toFixed(2)}</li>`;
            });
            changesHtml += '</ul></div>';
        } else {
            changesHtml = '<div class="alert alert-warning mt-3 mb-0">No score changes detected.</div>';
        }
        
        Swal.fire({
            title: 'Update Score?',
            html: `
                <div class="text-left">
                    <p>Are you sure you want to update this score?</p>
                    <div class="alert alert-warning mb-0">
                        <strong>Note:</strong> The judge will be able to see this change. Permission will be reset after this edit, and you will need to request permission again for future edits.
                    </div>
                    ${changesHtml}
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-save"></i> Yes, update score',
            cancelButtonText: '<i class="fas fa-times"></i> Cancel',
            reverseButtons: true,
            width: '600px'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading state
                Swal.fire({
                    title: 'Updating Score...',
                    text: 'Please wait while we update the score.',
                    icon: 'info',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
            }
        });
        
        $('#submitBtn').prop('disabled', true).html('<i class="fas fa-hourglass"></i> Updating...');
                
                // Submit the form
                const form = document.getElementById('scoreForm');
                const formData = new FormData(form);
                
                $.ajax({
                    url: form.action,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Score Updated Successfully!',
                                html: `
                                    <div class="text-center">
                                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                        <p>${response.message || 'The score has been updated successfully.'}</p>
                                        <p class="text-muted"><small>Permission has been reset. You will need to request permission again for future edits.</small></p>
                                    </div>
                                `,
                                icon: 'success',
                                confirmButtonText: '<i class="fas fa-check"></i> OK',
                                confirmButtonColor: '#28a745',
                                timer: 3000,
                                timerProgressBar: true
                            }).then(() => {
                                window.location.href = '/tabulation/score-management/round/<?php echo isset($score['round_id']) ? (int)$score['round_id'] : 0; ?>';
                            });
                        } else {
                            Swal.fire({
                                title: 'Update Failed',
                                text: response.message || 'Failed to update score. Please try again.',
                                icon: 'error',
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#dc3545'
                            });
                            $('#submitBtn').prop('disabled', false).html('<i class="fas fa-save"></i> Update Score');
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = 'Error updating score. Please try again.';
                        let needsPermission = false;
                        let permissionExpired = false;
                        
                        if (xhr.responseJSON) {
                            if (xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                            if (xhr.responseJSON.needs_permission) {
                                needsPermission = true;
                            }
                            if (xhr.responseJSON.permission_expired) {
                                permissionExpired = true;
                            }
                        }
                        
                        if (permissionExpired) {
                            Swal.fire({
                                title: 'Permission Expired',
                                html: '<div class="text-center">' +
                                    '<i class="fas fa-clock fa-3x text-warning mb-3"></i>' +
                                    '<p>Your permission to edit has expired (30 seconds elapsed).</p>' +
                                    '<p class="text-muted"><small>Please request permission again to continue editing.</small></p>' +
                                    '</div>',
                                icon: 'warning',
                                confirmButtonText: 'Request Permission Again',
                                confirmButtonColor: '#3085d6'
                            }).then(() => {
                                location.reload();
                            });
                        } else if (needsPermission) {
                            Swal.fire({
                                title: 'Permission Required',
                                text: errorMsg,
                                icon: 'warning',
                                confirmButtonText: 'Request Permission',
                                confirmButtonColor: '#3085d6'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: errorMsg,
                                icon: 'error',
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#dc3545'
                            });
                        }
                        $('#submitBtn').prop('disabled', false).html('<i class="fas fa-save"></i> Update Score');
                    }
                });
            }
        });
    });
    
    // Real-time validation with decimal support (2 decimal places, rounding based on 3rd decimal)
    $('.score-input').on('input', function() {
        var max = parseFloat($(this).data('max'));
        var value = parseFloat($(this).val()) || 0;
        
        // Allow decimals up to 2 decimal places (but check 3rd for rounding)
        var inputValue = $(this).val();
        if (inputValue.includes('.')) {
            var decimalPlaces = inputValue.split('.')[1];
            if (decimalPlaces && decimalPlaces.length > 2) {
                // Round to 2 decimal places (checking 3rd decimal)
                value = Math.round(value * 100) / 100;
                $(this).val(value.toFixed(2));
            }
        }
        
        // Use epsilon for floating point comparison (allow values up to and including max)
        var epsilon = 0.01;
        if (value > max + epsilon) {
            $(this).val(max.toFixed(2));
        }
        if (value < 0) {
            $(this).val('0.00');
        }
    });
    
    // Format on blur to ensure 2 decimal places (rounding based on 3rd decimal)
    $('.score-input').on('blur', function() {
        var value = parseFloat($(this).val()) || 0;
        var max = parseFloat($(this).data('max'));
        
        // Use epsilon for floating point comparison (allow values up to and including max)
        var epsilon = 0.01;
        if (value > max + epsilon) {
            value = max;
        }
        if (value < 0) {
            value = 0;
        }
        
        // Round to 2 decimal places (PHP round() behavior - checks 3rd decimal)
        value = Math.round(value * 100) / 100;
        $(this).val(value.toFixed(2));
    });
    
    // Check if deduction requires organizer key
    $('#point_deduction').on('input', function() {
        var deductionValue = parseFloat($(this).val()) || 0;
        var deductionKey = $('#deduction_organizer_key').val() || '';
        var organizerKey = $('#organizer_key').val() || '';
        
        if (deductionValue > 0) {
            // Deduction always requires organizer key (deduction key or regular key)
            if (!deductionKey && !organizerKey) {
                $('#deductionKeyWarning').show();
            } else {
                $('#deductionKeyWarning').hide();
            }
        } else {
            $('#deductionKeyWarning').hide();
        }
    });
    
    // Also check when organizer keys are entered
    $('#deduction_organizer_key, #organizer_key').on('input', function() {
        var deductionValue = parseFloat($('#point_deduction').val()) || 0;
        var deductionKey = $('#deduction_organizer_key').val() || '';
        var organizerKey = $('#organizer_key').val() || '';
        
        if (deductionValue > 0 && (deductionKey || organizerKey)) {
            $('#deductionKeyWarning').hide();
        } else if (deductionValue > 0) {
            $('#deductionKeyWarning').show();
        }
    });
    
    // Validate on form submit
    $('#scoreForm').on('submit', function(e) {
        var deductionValue = parseFloat($('#point_deduction').val()) || 0;
        var deductionKey = $('#deduction_organizer_key').val() || '';
        var organizerKey = $('#organizer_key').val() || '';
        var needsKey = <?php echo (isset($needsOrganizerKey) && $needsOrganizerKey) ? 'true' : 'false'; ?>;
        
        // Check if scores are being changed
        var scoresChanged = false;
        $('.score-input').each(function() {
            var currentValue = parseFloat($(this).val()) || 0;
            var originalValue = parseFloat($(this).data('original-value')) || 0;
            if (Math.abs(currentValue - originalValue) > 0.001) {
                scoresChanged = true;
                return false; // break
            }
        });
        
        // If only applying deduction (no score changes)
        if (deductionValue > 0 && !scoresChanged) {
            // Only need deduction key
            if (!deductionKey && !organizerKey) {
                e.preventDefault();
                Swal.fire({
                    title: 'Organizer Key Required',
                    text: 'Point deduction requires the organizer key. Please enter the organizer key for deduction.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                $('#deduction_organizer_key').focus();
                return false;
            }
        } else if (deductionValue > 0 && scoresChanged) {
            // Editing scores AND applying deduction
            // Need organizer key for editing (if no permission) AND for deduction
            if (needsKey && !organizerKey) {
                e.preventDefault();
                Swal.fire({
                    title: 'Organizer Key Required',
                    text: 'Editing scores requires the organizer key. Please enter the organizer key.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                $('#organizer_key').focus();
                return false;
            }
            // For deduction, can use either key
            if (!deductionKey && !organizerKey) {
                e.preventDefault();
                Swal.fire({
                    title: 'Organizer Key Required',
                    text: 'Point deduction requires the organizer key. Please enter the organizer key for deduction.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                $('#deduction_organizer_key').focus();
                return false;
            }
        } else if (scoresChanged && needsKey && !organizerKey) {
            // Editing scores without permission - need organizer key
            e.preventDefault();
            Swal.fire({
                title: 'Organizer Key Required',
                text: 'Judge has not granted permission. Please enter the organizer key to edit scores.',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            $('#organizer_key').focus();
            return false;
        }
    });
});
</script>

