<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Notifications</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/tabulation/dashboard">Home</a></li>
                        <li class="breadcrumb-item active">Notifications</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <?php if (Session::has('success_message')): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= Session::get('success_message') ?>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
                <?php Session::remove('success_message'); ?>
            <?php endif; ?>

            <?php if (Session::has('error_message')): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= Session::get('error_message') ?>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
                <?php Session::remove('error_message'); ?>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">
                            <i class="fas fa-bell"></i> Permission Requests
                            <?php if ($unreadCount > 0): ?>
                                <span class="badge badge-danger"><?= $unreadCount ?> unread</span>
                            <?php endif; ?>
                        </h3>
                        <?php 
                        // Count pending permission requests
                        $pendingCount = 0;
                        foreach ($notifications as $notif) {
                            if (($notif['type'] === 'permission_request' || $notif['type'] === 'deduction_request' || $notif['type'] === 'report_deduction_request') && !$notif['is_read']) {
                                $pendingCount++;
                            }
                        }
                        
                        if ($pendingCount > 0): 
                        ?>
                            <button type="button" class="btn btn-success btn-sm" onclick="grantAllRequests()" title="Grant All Pending Requests">
                                <i class="fas fa-check-double"></i> Grant All
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($notifications)): ?>
                        <div class="p-3 text-center text-muted">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p>No notifications</p>
                        </div>
                    <?php else: ?>
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 5%;">Status</th>
                                    <th style="width: 20%;">Type</th>
                                    <th style="width: 35%;">Message</th>
                                    <th style="width: 20%;">Details</th>
                                    <th style="width: 15%;">Date</th>
                                    <th style="width: 5%;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($notifications as $notif): ?>
                                    <tr class="<?= !$notif['is_read'] ? 'table-warning' : '' ?>">
                                        <td>
                                            <?php if (!$notif['is_read']): ?>
                                                <span class="badge badge-danger">New</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Read</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $badgeClass = 'badge-secondary';
                                            $icon = 'fa-info-circle';
                                            if ($notif['type'] === 'permission_request') {
                                                $badgeClass = 'badge-warning';
                                                $icon = 'fa-hand-paper';
                                            } elseif ($notif['type'] === 'deduction_request' || $notif['type'] === 'report_deduction_request') {
                                                $badgeClass = 'badge-warning';
                                                $icon = 'fa-minus-circle';
                                            } elseif ($notif['type'] === 'permission_granted' || $notif['type'] === 'deduction_granted') {
                                                $badgeClass = 'badge-success';
                                                $icon = 'fa-check-circle';
                                            } elseif ($notif['type'] === 'permission_denied' || $notif['type'] === 'deduction_denied') {
                                                $badgeClass = 'badge-danger';
                                                $icon = 'fa-times-circle';
                                            }
                                            ?>
                                            <span class="badge <?= $badgeClass ?>">
                                                <i class="fas <?= $icon ?>"></i> <?= ucfirst(str_replace('_', ' ', $notif['type'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($notif['title']) ?></strong><br>
                                            <small class="text-muted"><?= htmlspecialchars($notif['message']) ?></small>
                                        </td>
                                        <td>
                                            <?php if ($notif['event_name']): ?>
                                                <strong>Event:</strong> <?= htmlspecialchars($notif['event_name']) ?><br>
                                            <?php endif; ?>
                                            <?php if ($notif['round_name']): ?>
                                                <strong>Round:</strong> <?= htmlspecialchars($notif['round_name']) ?><br>
                                            <?php endif; ?>
                                            <?php if ($notif['contestant_number']): ?>
                                                <strong>Contestant:</strong> #<?= htmlspecialchars($notif['contestant_number']) ?>
                                                <?php if ($notif['contestant_name']): ?>
                                                    (<?= htmlspecialchars($notif['contestant_name']) ?>)
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small><?= date('M d, Y H:i', strtotime($notif['created_at'])) ?></small>
                                        </td>
                                        <td>
                                            <?php if (($notif['type'] === 'permission_request' || $notif['type'] === 'deduction_request' || $notif['type'] === 'report_deduction_request') && !$notif['is_read']): ?>
                                                <?php 
                                                if ($notif['type'] === 'deduction_request') {
                                                    $grantBtnClass = 'grant-deduction-btn';
                                                    $denyBtnClass = 'deny-deduction-btn';
                                                    $grantTitle = 'Grant Deduction Permission';
                                                    $denyTitle = 'Deny Deduction Permission';
                                                } elseif ($notif['type'] === 'report_deduction_request') {
                                                    $grantBtnClass = 'grant-report-deduction-btn';
                                                    $denyBtnClass = 'deny-report-deduction-btn';
                                                    $grantTitle = 'Grant Report Deduction';
                                                    $denyTitle = 'Deny Report Deduction';
                                                } else {
                                                    $grantBtnClass = 'grant-permission-btn';
                                                    $denyBtnClass = 'deny-permission-btn';
                                                    $grantTitle = 'Grant Permission';
                                                    $denyTitle = 'Deny Permission';
                                                }
                                                ?>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-success <?= htmlspecialchars($grantBtnClass) ?>" 
                                                            data-notification-id="<?= $notif['id'] ?>"
                                                            data-score-id="<?= $notif['related_id'] ?? '' ?>"
                                                            data-report-deduction-id="<?= $notif['related_id'] ?? '' ?>"
                                                            data-event-id="<?= $notif['event_id'] ?? '' ?>"
                                                            data-type="<?= htmlspecialchars($notif['type']) ?>"
                                                            title="<?= htmlspecialchars($grantTitle) ?>">
                                                        <i class="fas fa-check"></i> Grant
                                                    </button>
                                                    <button type="button" class="btn btn-danger <?= htmlspecialchars($denyBtnClass) ?>" 
                                                            data-notification-id="<?= $notif['id'] ?>"
                                                            data-score-id="<?= $notif['related_id'] ?? '' ?>"
                                                            data-report-deduction-id="<?= $notif['related_id'] ?? '' ?>"
                                                            data-event-id="<?= $notif['event_id'] ?? '' ?>"
                                                            data-type="<?= htmlspecialchars($notif['type']) ?>"
                                                            title="<?= htmlspecialchars($denyTitle) ?>">
                                                        <i class="fas fa-times"></i> Deny
                                                    </button>
                                                </div>
                                            <?php elseif ($notif['type'] === 'permission_request' || $notif['type'] === 'deduction_request'): ?>
                                                <a href="/tabulation/score-management/round/<?= $notif['round_id'] ?? '' ?>" 
                                                   class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-secondary mark-read-btn" 
                                                        data-notification-id="<?= $notif['id'] ?>"
                                                        title="Mark as Read">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
// Wait for jQuery to be available
(function() {
    function initNotifications() {
        if (typeof jQuery === 'undefined') {
            console.error('jQuery is not loaded!');
            setTimeout(initNotifications, 100);
            return;
        }
        
        console.log('Notifications page loaded, jQuery version:', jQuery.fn.jquery);
        
        // Test: Count buttons
        const grantButtons = jQuery('.grant-permission-btn');
        console.log('Found', grantButtons.length, 'grant buttons on page');
        
        // Grant permission handler
        jQuery(document).on('click', '.grant-permission-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const notificationId = jQuery(this).data('notification-id');
            const $btn = jQuery(this);
            
            if (!notificationId) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error', 'Notification ID not found.', 'error');
                }
                return;
            }
            
            if (typeof Swal === 'undefined') {
                alert('Error: SweetAlert2 is not loaded.');
                return;
            }
            
            Swal.fire({
                title: 'Grant Permission?',
                text: 'Are you sure you want to grant permission to edit this score?',
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
            
                const url = '/tabulation/notifications/' + notificationId + '/respond';
                const csrfToken = jQuery('meta[name="csrf-token"]').attr('content') || jQuery('input[name="csrf_token"]').val() || <?php echo json_encode(Session::getCSRFToken() ?: '', JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
            
            jQuery.ajax({
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
                                text: 'Permission granted successfully!',
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
        
        // Grant deduction permission handler
        jQuery(document).on('click', '.grant-deduction-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const notificationId = jQuery(this).data('notification-id');
            const scoreId = jQuery(this).data('score-id');
            const $btn = jQuery(this);
            
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
                title: 'Grant Deduction Permission?',
                text: 'This will automatically apply the deduction using the organizer key. The deduction will be applied immediately.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, grant and apply',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }
                
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
                
                const url = '/tabulation/score-management/' + scoreId + '/respond-deduction';
                const csrfToken = jQuery('meta[name="csrf-token"]').attr('content') || jQuery('input[name="csrf_token"]').val() || <?php echo json_encode(Session::getCSRFToken() ?: '', JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
                    
                    jQuery.ajax({
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
                                text: 'Deduction permission granted and applied automatically!',
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
        
        // Deny deduction permission handler
        jQuery(document).on('click', '.deny-deduction-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const notificationId = jQuery(this).data('notification-id');
            const scoreId = jQuery(this).data('score-id');
            const $btn = jQuery(this);
            
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
                title: 'Deny Deduction Permission?',
                text: 'Are you sure you want to deny the deduction request?',
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
                
                const url = '/tabulation/score-management/' + scoreId + '/respond-deduction';
                const csrfToken = jQuery('meta[name="csrf-token"]').attr('content') || jQuery('input[name="csrf_token"]').val() || <?php echo json_encode(Session::getCSRFToken() ?: '', JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
                    
                    jQuery.ajax({
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
                                text: 'Deduction permission denied.',
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

        // Grant report deduction handler
        jQuery(document).on('click', '.grant-report-deduction-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const notificationId = jQuery(this).data('notification-id');
            const deductionId = jQuery(this).data('report-deduction-id');
            const eventId = jQuery(this).data('event-id');
            const $btn = jQuery(this);
            
            if (!deductionId || !eventId) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error', 'Deduction request not found.', 'error');
                }
                return;
            }
            
            if (typeof Swal === 'undefined') {
                alert('Error: SweetAlert2 is not loaded.');
                return;
            }
            
            Swal.fire({
                title: 'Grant Report Deduction?',
                text: 'Are you sure you want to grant this report deduction request?',
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
                
                const url = '/tabulation/events/' + eventId + '/reports/deductions/' + deductionId + '/respond';
                const csrfToken = jQuery('meta[name="csrf-token"]').attr('content') || jQuery('input[name="csrf_token"]').val() || <?php echo json_encode(Session::getCSRFToken() ?: '', JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
                    
                jQuery.ajax({
                    url: url,
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    dataType: 'json',
                    data: {
                        csrf_token: csrfToken,
                        notification_id: notificationId,
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
                                text: 'Report deduction granted.',
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
                    error: function(xhr) {
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
        
        // Deny report deduction handler
        jQuery(document).on('click', '.deny-report-deduction-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const notificationId = jQuery(this).data('notification-id');
            const deductionId = jQuery(this).data('report-deduction-id');
            const eventId = jQuery(this).data('event-id');
            const $btn = jQuery(this);
            
            if (!deductionId || !eventId) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error', 'Deduction request not found.', 'error');
                }
                return;
            }
            
            if (typeof Swal === 'undefined') {
                alert('Error: SweetAlert2 is not loaded.');
                return;
            }
            
            Swal.fire({
                title: 'Deny Report Deduction?',
                text: 'Are you sure you want to deny this report deduction request?',
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
                
                const url = '/tabulation/events/' + eventId + '/reports/deductions/' + deductionId + '/respond';
                const csrfToken = jQuery('meta[name="csrf-token"]').attr('content') || jQuery('input[name="csrf_token"]').val() || <?php echo json_encode(Session::getCSRFToken() ?: '', JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
                    
                jQuery.ajax({
                    url: url,
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    dataType: 'json',
                    data: {
                        csrf_token: csrfToken,
                        notification_id: notificationId,
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
                                text: 'Report deduction denied.',
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
                    error: function(xhr) {
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
    
        // Deny permission handler
        jQuery(document).on('click', '.deny-permission-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const notificationId = jQuery(this).data('notification-id');
            const $btn = jQuery(this);
            
            if (!notificationId) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error', 'Notification ID not found.', 'error');
                }
                return;
            }
            
            if (typeof Swal === 'undefined') {
                alert('Error: SweetAlert2 is not loaded.');
                return;
            }
            
            Swal.fire({
                title: 'Deny Permission?',
                text: 'Are you sure you want to deny permission to edit this score?',
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
            
                const url = '/tabulation/notifications/' + notificationId + '/respond';
                const csrfToken = jQuery('meta[name="csrf-token"]').attr('content') || jQuery('input[name="csrf_token"]').val() || <?php echo json_encode(Session::getCSRFToken() ?: '', JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
            
            jQuery.ajax({
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
        
        // Grant all pending requests
        window.grantAllRequests = function() {
            if (!confirm('Are you sure you want to grant ALL pending permission requests? This action cannot be undone.')) {
                return;
            }
            
            const $grantButtons = jQuery('.grant-permission-btn, .grant-deduction-btn, .grant-report-deduction-btn');
            const totalRequests = $grantButtons.length;
            
            if (totalRequests === 0) {
                alert('No pending requests to grant.');
                return;
            }
            
            let processed = 0;
            let errors = 0;
            
            // Disable all buttons during processing
            $grantButtons.prop('disabled', true).each(function() {
                const $btn = jQuery(this);
                const originalHtml = $btn.html();
                $btn.html('<i class="fas fa-spinner fa-spin"></i> Processing...');
            });
            
            // Process each request
            $grantButtons.each(function() {
                const $btn = jQuery(this);
                const notificationId = $btn.data('notification-id');
                const scoreId = $btn.data('score-id');
                const reportDeductionId = $btn.data('report-deduction-id');
                const eventId = $btn.data('event-id');
                const type = $btn.data('type');
                
                // Determine the correct endpoint based on type
                let url = '/tabulation/notifications/' + notificationId + '/grant';
                let data = {
                    csrf_token: '<?= Session::getCSRFToken() ?>',
                    action: 'grant'
                };
                
                if (type === 'deduction_request') {
                    url = '/tabulation/score-management/' + scoreId + '/apply-deduction';
                    data = {
                        csrf_token: '<?= Session::getCSRFToken() ?>',
                        score_id: scoreId
                    };
                } else if (type === 'report_deduction_request') {
                    url = '/tabulation/reports/grant-deduction';
                    data = {
                        csrf_token: '<?= Session::getCSRFToken() ?>',
                        id: reportDeductionId,
                        event_id: eventId
                    };
                }
                
                jQuery.ajax({
                    url: url,
                    method: 'POST',
                    data: data,
                    dataType: 'json',
                    success: function(response) {
                        processed++;
                        console.log('Grant response for notification ' + notificationId + ':', response);
                        if (response.success) {
                            $btn.closest('tr').addClass('table-success');
                        } else {
                            errors++;
                            console.error('Error granting request:', response);
                            console.error('Error details:', response.message || 'Unknown error');
                        }
                        
                        // Check if all requests are processed
                        if (processed === totalRequests) {
                            if (errors === 0) {
                                alert('All requests granted successfully!');
                                location.reload();
                            } else {
                                alert('Completed with ' + errors + ' errors. Please check the console for details.');
                                location.reload();
                            }
                        }
                    },
                    error: function(xhr) {
                        processed++;
                        errors++;
                        console.error('AJAX error for notification ' + notificationId + ':', xhr);
                        console.error('Status:', xhr.status);
                        console.error('Response:', xhr.responseText);
                        
                        if (processed === totalRequests) {
                            alert('Completed with ' + errors + ' errors. Please check the console for details.');
                            location.reload();
                        }
                    }
                });
            });
        };
        
        // Mark as read
        jQuery(document).on('click', '.mark-read-btn', function(e) {
            e.preventDefault();
            const notificationId = jQuery(this).data('notification-id');
            const $btn = jQuery(this);
            
            jQuery.ajax({
                url: '/tabulation/notifications/' + notificationId + '/mark-read',
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                dataType: 'json',
                data: {
                    csrf_token: '<?= Session::getCSRFToken() ?>'
                },
                success: function(response) {
                    if (response && response.success) {
                        location.reload();
                    }
                },
                error: function(xhr) {
                    console.error('Error marking notification as read:', xhr);
                }
            });
        });
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initNotifications);
    } else {
        initNotifications();
    }
})();
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>

