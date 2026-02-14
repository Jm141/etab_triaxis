<?php 
$title = 'Score: ' . $contestant['name'];
require __DIR__ . '/../layout/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-pencil"></i> Score Contestant</h1>
    <a href="/tabulation/judge/rounds/<?= $round['id'] ?>/table" class="btn btn-secondary">Back to Table</a>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-body text-center">
                <h4><?= htmlspecialchars($contestant['name']) ?></h4>
                <p class="text-muted mb-2">#<?= htmlspecialchars($contestant['contestant_number']) ?></p>
                <?php if ($contestant['team_name']): ?>
                    <p class="mb-0"><small class="text-muted">Team: <?= htmlspecialchars($contestant['team_name']) ?></small></p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Round Info</h6>
            </div>
            <div class="card-body">
                <p class="mb-1"><strong>Event:</strong> <?= htmlspecialchars($round['event_name']) ?></p>
                <p class="mb-1"><strong>Level:</strong> <?= htmlspecialchars($round['level_name']) ?></p>
                <p class="mb-0"><strong>Round:</strong> <?= htmlspecialchars($round['name']) ?></p>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-list-check"></i> Scoring Criteria</h5>
            </div>
            <div class="card-body">
                <form id="scoreForm" method="POST" action="/tabulation/judge/rounds/<?= $round['id'] ?>/contestants/<?= $contestant['id'] ?>/submit">
                    <input type="hidden" name="csrf_token" value="<?= Session::getCSRFToken() ?>">
                    
                    <?php if (empty($criteria)): ?>
                        <div class="alert alert-warning">
                            No criteria configured for this round. Please contact the administrator.
                        </div>
                    <?php else: ?>
                        <?php 
                        $totalWeight = 0;
                        $totalMaxScore = 0;
                        foreach ($criteria as $criterion) {
                            $totalWeight += $criterion['weight'];
                            $totalMaxScore += $criterion['max_score'];
                        }
                        ?>
                        
                        <div class="alert alert-info mb-4">
                            <h6><i class="fas fa-info-circle"></i> Scoring Information</h6>
                            <p class="mb-1">
                                <strong>Total Max Score:</strong> <?= number_format($totalMaxScore, 2) ?> points
                            </p>
                            <p class="mb-0">
                                <strong>Weights are auto-calculated</strong> based on each criterion's max score proportionally. 
                                Simply enter your scores - the system will calculate the final percentage automatically.
                            </p>
                        </div>
                        
                        <?php foreach ($criteria as $criterion): ?>
                            <?php 
                            $existingScore = null;
                            if (!empty($scoreDetails)) {
                                foreach ($scoreDetails as $detail) {
                                    if ($detail['criteria_id'] == $criterion['id']) {
                                        $existingScore = $detail['raw_score'];
                                        break;
                                    }
                                }
                            }
                            ?>
                            
                            <div class="mb-4 p-3 border rounded">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-1"><?= htmlspecialchars($criterion['name']) ?></h6>
                                        <?php if ($criterion['description']): ?>
                                            <small class="text-muted"><?= htmlspecialchars($criterion['description']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-right">
                                        <span class="badge badge-info">Auto Weight: <?= number_format($criterion['weight'], 2) ?>%</span>
                                        <small class="text-muted d-block">
                                            (<?= number_format($criterion['max_score'] / $totalMaxScore * 100, 2) ?>% of total)
                                        </small>
                                    </div>
                                </div>
                                
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <label class="form-label">
                                            Score (0 - <?= $criterion['max_score'] ?>)
                                        </label>
                                        <input type="number" 
                                               class="form-control form-control-lg score-input" 
                                               name="criteria_<?= $criterion['id'] ?>" 
                                               value="<?= $existingScore ? number_format($existingScore, 3, '.', '') : '' ?>" 
                                               min="0" 
                                               max="<?= $criterion['max_score'] ?>" 
                                               step="0.001"
                                               required
                                               data-max="<?= $criterion['max_score'] ?>"
                                               data-weight="<?= $criterion['weight'] ?>"
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
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                <i class="fas fa-check-circle"></i> Submit Scores
                            </button>
                        </div>
                        
                        <?php if ($score && $score['is_submitted']): ?>
                        <hr>
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" 
                                       class="custom-control-input" 
                                       id="adminEditPermission"
                                       <?= $score['admin_edit_allowed'] ? 'checked' : '' ?>
                                       data-score-id="<?= $score['id'] ?>">
                                <label class="custom-control-label" for="adminEditPermission">
                                    <strong>Allow Admin/Tabulator to Edit This Score</strong>
                                    <br><small class="text-muted">If enabled, administrators can modify your scores if needed (e.g., for corrections)</small>
                                </label>
                            </div>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Show loading on submit (after validation passes)
    $('#scoreForm').on('submit', function(e) {
        // Show loading
        $('#submitBtn').prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Submitting...');
    });
    
    // Prevent typing values greater than max using keydown
    $('.score-input').on('keydown', function(e) {
        var $input = $(this);
        // Ensure max is a proper number
        var max = Number(parseFloat($input.data('max')));
        var currentValue = $input.val();
        
        // Skip validation if max is invalid
        if (isNaN(max) || max <= 0) {
            return;
        }
        
        // Allow: backspace, delete, tab, escape, enter, decimal point, and arrow keys
        if ([8, 9, 27, 13, 46, 110, 190, 37, 38, 39, 40].indexOf(e.keyCode) !== -1 ||
            // Allow: Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
            (e.keyCode === 65 && e.ctrlKey === true) ||
            (e.keyCode === 67 && e.ctrlKey === true) ||
            (e.keyCode === 86 && e.ctrlKey === true) ||
            (e.keyCode === 88 && e.ctrlKey === true) ||
            // Allow: home, end, left, right, down, up
            (e.keyCode >= 35 && e.keyCode <= 40)) {
            return;
        }
        
        // Allow numbers and decimal point
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105) && e.keyCode !== 110 && e.keyCode !== 190) {
            e.preventDefault();
            return;
        }
        
        // Check if the new value would exceed max
        var char = String.fromCharCode(e.which);
        var newValue = currentValue.slice(0, $input[0].selectionStart) + char + currentValue.slice($input[0].selectionEnd);
        var numValue = parseFloat(newValue);
        
        // Convert both to numbers for proper comparison
        const numValueCheck = Number(numValue);
        const numMax = Number(max);
        
        // Check if value is less than max (valid) - accept decimals
        // Only reject if value >= max
        if (!isNaN(numValue) && numValueCheck >= numMax) {
            e.preventDefault();
            $input.val(numMax.toFixed(3));
            showMaxValueWarning($input, numMax);
            return false;
        }
    });
    
    // Real-time validation with decimal support
    $('.score-input').on('input', function() {
        var $input = $(this);
        // Ensure max is a proper number
        var max = Number(parseFloat($input.data('max')));
        var value = parseFloat($input.val());
        
        // Skip validation if max is invalid
        if (isNaN(max) || max <= 0) {
            return;
        }
        
        // Allow decimals up to 3 decimal places
        var inputValue = $input.val();
        if (inputValue.includes('.')) {
            var decimalPlaces = inputValue.split('.')[1];
            if (decimalPlaces && decimalPlaces.length > 3) {
                // Round to 3 decimal places
                value = Math.round(value * 1000) / 1000;
                $input.val(value.toFixed(3));
            }
        }
        
        // Remove existing warnings
        $input.next('.max-value-warning').remove();
        $input.removeClass('border-danger');
        
        if (!isNaN(value)) {
            // Convert both to numbers for proper comparison
            const numValue = Number(value);
            const numMax = Number(max);
            
            // Set to 0 if negative
            if (numValue < 0) {
                $input.val('0.000');
            }
            // Check if value is less than max (valid) - accept decimals
            else if (numValue < numMax) {
                // Value is valid - remove any warnings
                $input.next('.max-value-warning').remove();
                $input.removeClass('border-danger');
            }
            // Value equals or exceeds max - show warning and cap
            else {
                $input.val(numMax.toFixed(3));
                showMaxValueWarning($input, numMax);
            }
        }
    });
    
    // Handle paste event to prevent pasting values > max
    $('.score-input').on('paste', function(e) {
        var $input = $(this);
        // Ensure max is a proper number
        var max = Number(parseFloat($input.data('max')));
        
        // Skip validation if max is invalid
        if (isNaN(max) || max <= 0) {
            return;
        }
        
        // Get pasted data
        var pastedData = (e.originalEvent.clipboardData || window.clipboardData).getData('text');
        var pastedValue = parseFloat(pastedData);
        
        // If pasted value is >= max, prevent paste and set to max
        // Accept values less than max (including decimals)
        const numPastedValue = Number(pastedValue);
        const numMax = Number(max);
        if (!isNaN(pastedValue) && numPastedValue >= numMax) {
            e.preventDefault();
            $input.val(numMax.toFixed(3));
            showMaxValueWarning($input, numMax);
            return false;
        }
    });
    
    // Format on blur to ensure 3 decimal places
    $('.score-input').on('blur', function() {
        var $input = $(this);
        var value = parseFloat($input.val()) || 0;
        // Ensure max is a proper number
        var max = Number(parseFloat($input.data('max')));
        
        // Skip validation if max is invalid
        if (isNaN(max) || max <= 0) {
            return;
        }
        
        // Remove existing warnings
        $input.next('.max-value-warning').remove();
        $input.removeClass('border-danger');
        
        // Convert both to numbers for proper comparison
        const numValue = Number(value);
        const numMax = Number(max);
        
        // Set to 0 if negative
        if (numValue < 0) {
            value = 0;
        }
        // Check if value is less than max (valid) - accept decimals
        else if (numValue < numMax) {
            // Value is valid - remove any warnings
            $input.next('.max-value-warning').remove();
            $input.removeClass('border-danger');
        }
        // Value equals or exceeds max - show warning and cap
        else {
            value = numMax;
            showMaxValueWarning($input, numMax);
        }
        
        // Round to 3 decimal places and format
        value = Math.round(value * 1000) / 1000;
        $input.val(value.toFixed(3));
    });
    
    // Show warning for max value exceeded
    function showMaxValueWarning($input, max) {
        // Add warning message
        const $warning = $('<small class="text-danger d-block max-value-warning mt-1">')
            .html('<i class="fas fa-exclamation-triangle"></i> Maximum value is ' + max.toFixed(3));
        $input.after($warning);
        $input.addClass('border-danger');
        
        // Remove warning after 3 seconds
        setTimeout(function() {
            $warning.fadeOut(function() {
                $(this).remove();
            });
            $input.removeClass('border-danger');
        }, 3000);
    }
    
    // Validate on form submit
    $('#scoreForm').on('submit', function(e) {
        var isValid = true;
        var errorMessages = [];
        
        $('.score-input').each(function() {
            var $input = $(this);
            var value = parseFloat($input.val());
            var max = parseFloat($input.data('max'));
            var criterionName = $input.closest('.border').find('h6').text();
            
            if (isNaN(value) || value === '') {
                isValid = false;
                errorMessages.push('Please enter a score for: ' + criterionName);
                $input.addClass('border-danger');
            } else {
                // Use epsilon for floating point comparison (allow values up to and including max)
                var epsilon = 0.0001;
                if (value < 0 || value > max + epsilon) {
                    // Only show error if significantly over max
                    if (value > max + 0.01) {
                        isValid = false;
                        errorMessages.push('Score for "' + criterionName + '" must be between 0 and ' + max.toFixed(3) + '. You entered ' + value.toFixed(3));
                        $input.addClass('border-danger');
                    } else if (value > max) {
                        // Cap to max if just slightly over
                        value = max;
                        $input.val(max.toFixed(3));
                    }
                } else {
                    $input.removeClass('border-danger');
                }
            }
            
            if (value < 0) {
                isValid = false;
                errorMessages.push('Score for "' + criterionName + '" cannot be negative');
                $input.addClass('border-danger');
            } else {
                $input.removeClass('border-danger');
            }
            } else {
                $input.removeClass('border-danger');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            Swal.fire({
                title: 'Validation Error',
                html: 'Please correct the following errors:<br><br>' + errorMessages.join('<br>'),
                icon: 'error'
            });
            return false;
        }
        
        Swal.fire({
            title: 'Submit Scores?',
            text: 'Are you sure you want to submit these scores? This action cannot be undone.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, submit',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (!result.isConfirmed) {
                e.preventDefault();
                return false;
            }
        });
    });
});
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>



