<?php 
$title = 'Apply Deduction - ' . $score['contestant_name'];
require __DIR__ . '/../layout/header.php'; 
require_once __DIR__ . '/../../core/ScoreFormatter.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-minus-circle"></i> Apply Point Deduction</h1>
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
                <p class="mb-0">
                    <strong>Current Total Score:</strong> 
                    <strong class="text-primary"><?= ScoreFormatter::format($score['total_score'], 2) ?></strong>
                </p>
                <?php if (isset($score['point_deduction']) && $score['point_deduction'] > 0): ?>
                    <p class="mb-0 mt-2">
                        <small class="text-muted">
                            <del>Original: <?= ScoreFormatter::format($score['total_score'] + $score['point_deduction'], 2) ?></del>
                            <br>
                            <span class="text-danger">After Deduction: <?= ScoreFormatter::format($score['total_score'], 2) ?></span>
                            <br>
                            <strong>Deduction: <?= ScoreFormatter::format($score['point_deduction'], 2) ?> points</strong>
                        </small>
                    </p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <strong>Note:</strong> Deductions are applied to the <strong>total score</strong> and require the organizer key.
            The judge will be notified of the deduction.
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-minus-circle"></i> Apply Point Deduction</h5>
            </div>
            <div class="card-body">
                <?php 
                // Check if deduction permission has been requested
                $deductionRequested = !empty($score['deduction_request_status']) && $score['deduction_request_status'] !== 'none';
                $deductionGranted = $score['deduction_request_status'] === 'granted';
                ?>
                
                <?php if ($deductionGranted): ?>
                    <div class="alert alert-success mb-3">
                        <i class="fas fa-check-circle"></i>
                        <strong>Permission Granted:</strong> Organizer has granted permission to apply deduction. You can now apply the deduction.
                    </div>
                <?php elseif ($deductionRequested): ?>
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-clock"></i>
                        <strong>Permission Requested:</strong> A request has been sent to the event organizer. Waiting for approval...
                        <br><small>You will be notified when the organizer responds.</small>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle"></i>
                        <strong>Request Organizer Permission:</strong> 
                        To apply a deduction, you need to request permission from the event organizer. 
                        Once granted, the deduction will be applied automatically.
                    </div>
                <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="point_deduction" class="form-label">
                            Deduction Amount <span class="text-danger">*</span>
                        </label>
                        <input type="number" 
                               class="form-control form-control-lg" 
                               name="point_deduction" 
                               id="point_deduction"
                               value="<?= isset($score['point_deduction']) && $score['point_deduction'] > 0 ? ScoreFormatter::format($score['point_deduction'], 2) : '' ?>"
                               min="0.01" 
                               step="0.01"
                               placeholder="0.00"
                               required>
                        <small class="form-text text-muted">
                            Enter the number of points to deduct from the <strong>total score</strong>.
                            This will be subtracted from the current total score of <strong><?= ScoreFormatter::format($score['total_score'], 2) ?></strong>.
                        </small>
                        <div class="mt-2">
                            <strong>New Total Score After Deduction:</strong>
                            <span id="newTotalScore" class="text-danger font-weight-bold"><?= ScoreFormatter::format($score['total_score'], 2) ?></span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="deduction_reason" class="form-label">
                            Reason for Deduction <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" 
                                  name="deduction_reason" 
                                  id="deduction_reason"
                                  rows="3"
                                  placeholder="Enter reason for point deduction (e.g., Rule violation, Time penalty, Late arrival, etc.)"
                                  required><?= htmlspecialchars($score['deduction_reason'] ?? '') ?></textarea>
                        <small class="form-text text-muted">
                            Provide a clear reason for the deduction. This will be visible to the judge.
                        </small>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <?php if ($deductionGranted): ?>
                            <button type="button" class="btn btn-danger btn-lg" id="applyDeductionBtn">
                                <i class="fas fa-minus-circle"></i> Apply Deduction
                            </button>
                        <?php elseif (!$deductionRequested): ?>
                            <button type="button" class="btn btn-primary btn-lg" id="requestDeductionBtn">
                                <i class="fas fa-paper-plane"></i> Request Permission from Organizer
                            </button>
                        <?php else: ?>
                            <button type="button" class="btn btn-secondary btn-lg" disabled>
                                <i class="fas fa-hourglass"></i> Waiting for Organizer Response...
                            </button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>

<script>
// Simple, direct approach - wait for page to fully load
console.log('[Deduction Request] Script block loaded');

// Store PHP variables at script level - with error handling
<?php
// Safely get values with fallbacks
$currentTotal = isset($score['total_score']) ? (float)$score['total_score'] : 0;
$scoreId = isset($score['id']) ? (int)$score['id'] : 0;
$roundId = isset($score['round_id']) ? (int)$score['round_id'] : 0;
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
const deductionData = {
    currentTotal: <?php echo $currentTotal; ?>,
    scoreId: <?php echo $scoreId; ?>,
    roundId: <?php echo $roundId; ?>,
    csrfToken: <?php echo json_encode($csrfToken, JSON_HEX_APOS | JSON_HEX_QUOT); ?>
};

console.log('[Deduction Request] Data loaded:', deductionData);

// Simple jQuery ready handler
jQuery(document).ready(function($) {
    console.log('[Deduction Request] jQuery ready - initializing');
    console.log('[Deduction Request] Swal available:', typeof Swal !== 'undefined');
    
    // Calculate new total score as user types
    $('#point_deduction').on('input', function() {
        const deduction = parseFloat($(this).val()) || 0;
        const newTotal = Math.max(0, deductionData.currentTotal - deduction);
        $('#newTotalScore').text(newTotal.toFixed(2));
        
        if (newTotal < 0) {
            $('#newTotalScore').addClass('text-danger').removeClass('text-success');
        } else {
            $('#newTotalScore').addClass('text-danger').removeClass('text-success');
        }
    });
    
    // Request deduction permission
    const $requestBtn = $('#requestDeductionBtn');
    console.log('[Deduction Request] Request button found:', $requestBtn.length > 0);
    
    if ($requestBtn.length > 0) {
        $requestBtn.on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('[Deduction Request] ===== BUTTON CLICKED =====');
            
            const $btn = $(this);
            const deduction = parseFloat($('#point_deduction').val()) || 0;
            const reason = $('#deduction_reason').val().trim();
            
            // Check if Swal is available
            if (typeof Swal === 'undefined') {
                console.error('[Deduction Request] Swal is not available!');
                alert('SweetAlert2 is not loaded. Please refresh the page.');
                return;
            }
            
            if (deduction <= 0) {
                Swal.fire('Error', 'Deduction amount must be greater than 0.', 'error');
                return;
            }
            
            if (!reason) {
                Swal.fire('Error', 'Deduction reason is required.', 'error');
                return;
            }
            
            console.log('[Deduction Request] Showing confirmation modal...');
            
            Swal.fire({
                title: 'Request Deduction Permission?',
                html: 'You are requesting permission to deduct <strong>' + deduction.toFixed(2) + '</strong> points.<br><br>' +
                      '<strong>Current Total:</strong> ' + deductionData.currentTotal.toFixed(2) + '<br>' +
                      '<strong>New Total:</strong> ' + (deductionData.currentTotal - deduction).toFixed(2) + '<br><br>' +
                      '<strong>Reason:</strong> ' + reason + '<br><br>' +
                      'A notification will be sent to the event organizer.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, send request',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                console.log('[Deduction Request] Swal result:', result);
                if (result.isConfirmed) {
                    console.log('[Deduction Request] User confirmed - sending request');
                    
                    $btn.prop('disabled', true).html('<i class="fas fa-hourglass"></i> Sending Request...');
                    
                    // Show loading
                    Swal.fire({
                        title: 'Sending Request...',
                        text: 'Please wait while we send the permission request to the organizer.',
                        icon: 'info',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    $.ajax({
                        url: '/tabulation/score-management/' + deductionData.scoreId + '/request-deduction',
                        method: 'POST',
                        data: {
                            csrf_token: deductionData.csrfToken,
                            point_deduction: deduction,
                            deduction_reason: reason
                        },
                        dataType: 'json',
                        success: function(response) {
                            console.log('[Deduction Request] AJAX success:', response);
                            if (response.success) {
                                Swal.fire({
                                    title: '<strong>Request Pushed Successfully!</strong>',
                                    html: '<div class="text-center">' +
                                        '<i class="fas fa-paper-plane fa-3x text-success mb-3"></i>' +
                                        '<p class="mb-2">Permission request has been sent to the event organizer.</p>' +
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
                                        html: '<small>Waiting for organizer response...</small>'
                                    });
                                    
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
                                $btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Request Permission from Organizer');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('[Deduction Request] AJAX error:', { xhr, status, error });
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
                            $btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Request Permission from Organizer');
                        }
                    });
                } else {
                    console.log('[Deduction Request] User cancelled');
                }
            });
        });
        
        console.log('[Deduction Request] Request button handler attached');
    }
    
    // Apply deduction (when permission is granted)
    const $applyBtn = $('#applyDeductionBtn');
    console.log('[Deduction Request] Apply button found:', $applyBtn.length > 0);
    
    if ($applyBtn.length > 0) {
        $applyBtn.on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('[Deduction Request] Apply button clicked');
            
            const $btn = $(this);
            const deduction = parseFloat($('#point_deduction').val()) || 0;
            const reason = $('#deduction_reason').val().trim();
            
            Swal.fire({
                title: 'Apply Deduction?',
                html: 'Are you sure you want to deduct <strong>' + deduction.toFixed(2) + '</strong> points?<br><br>' +
                      '<strong>Current Total:</strong> ' + deductionData.currentTotal.toFixed(2) + '<br>' +
                      '<strong>New Total:</strong> ' + (deductionData.currentTotal - deduction).toFixed(2) + '<br><br>' +
                      '<strong>Reason:</strong> ' + reason,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, apply deduction',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $btn.prop('disabled', true).html('<i class="fas fa-hourglass"></i> Applying...');
                    
                    $.ajax({
                        url: '/tabulation/score-management/' + deductionData.scoreId + '/apply-deduction',
                        method: 'POST',
                        data: {
                            csrf_token: deductionData.csrfToken,
                            point_deduction: deduction,
                            deduction_reason: reason
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    title: 'Success!',
                                    text: response.message || 'Deduction applied successfully.',
                                    icon: 'success',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    window.location.href = '/tabulation/score-management/round/' + deductionData.roundId;
                                });
                            } else {
                                Swal.fire('Error', response.message || 'Failed to apply deduction.', 'error');
                                $btn.prop('disabled', false).html('<i class="fas fa-minus-circle"></i> Apply Deduction');
                            }
                        },
                        error: function(xhr) {
                            let errorMsg = 'Error applying deduction. Please try again.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                            Swal.fire('Error', errorMsg, 'error');
                            $btn.prop('disabled', false).html('<i class="fas fa-minus-circle"></i> Apply Deduction');
                        }
                    });
                }
            });
        });
        
        console.log('[Deduction Request] Apply button handler attached');
    }
    
    console.log('[Deduction Request] All handlers attached successfully');
});
</script>
