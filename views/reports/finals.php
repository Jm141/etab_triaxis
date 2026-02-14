<?php 
$title = 'Final Report - ' . $event['name'];
require __DIR__ . '/../layout/header.php'; 
require __DIR__ . '/_print_styles.php';
require_once __DIR__ . '/../../core/ScoreFormatter.php';
?>

<!-- Action Buttons (Screen Only) -->
<div class="mb-3 no-print">
    <button onclick="window.print()" class="btn btn-sm btn-info">
        <i class="fas fa-print"></i> Print
    </button>
    <a href="/tabulation/events/<?= $event['id'] ?>/reports" class="btn btn-sm btn-secondary">Back</a>
</div>

<!-- Centered Tabulation Header (Screen View - Simplified) -->
<div class="tabulation-header no-print">
    <div class="event-name">
        <?= htmlspecialchars($event['name']) ?>
    </div>
    <div class="event-details">
        <strong>Final Report</strong>
    </div>
</div>

<!-- Centered Tabulation Header (Print View - Full) -->
<div class="tabulation-header" style="display: none;">
    <div class="org-info">
        <!-- Organization info can be added here if available -->
    </div>
    <div class="event-name">
        <?= htmlspecialchars($event['name']) ?>
    </div>
    <div class="event-details">
        <?php if (!empty($event['venue'])): ?>
            Venue: <?= htmlspecialchars($event['venue']) ?><br>
        <?php endif; ?>
        <?= !empty($event['event_date']) ? date('F d, Y', strtotime($event['event_date'])) : date('F d, Y') ?>
    </div>
    <div class="tabulation-title">FINAL REPORT</div>
    <div class="divider"></div>
</div>

<?php if (empty($contestantData)): ?>
    <div class="alert alert-warning">No scores available yet.</div>
<?php else: ?>
    <?php $usingSumRoundTotals = !empty($formula['formula_expression']) && (stripos($formula['formula_expression'], 'SUM(round_totals)') !== false); ?>
    <?php if ($usingSumRoundTotals): ?>
    <div class="alert alert-info no-print mb-2">
        <strong>Why is Total Average Score so large?</strong> Your event uses <code>SUM(round_totals)</code>: it adds each round’s <strong>sum of all judges’ raw scores</strong> (e.g. 5 judges × ~124 ≈ 620 per round). To get (Pre Pageant Total Avg + Pageant Total Avg) ÷ 2 (values ~12–25), go to <strong>Reports → Scoring Formula Management</strong>, edit the <strong>Final</strong> formula and set it to <code>AVG(level_totals)</code>.
    </div>
    <?php endif; ?>
    <p class="text-muted small no-print mb-2"><strong>Note:</strong> All scores are <strong>score totals</strong> (sum of criteria scores). Round columns = average of judges’ totals; Each level Total Avg Score = average of round averages. <strong>Total Average Score</strong> (final column) = average of level totals (e.g. Pre Pageant + Pageant then ÷ 2). Custom formulas (e.g. <code>AVG(level_totals)</code>) can be set in Scoring Formula Management.</p>
    <!-- Screen View Table -->
    <div class="table-responsive no-print">
        <table class="table table-bordered table-hover mb-0" style="font-size: 0.9em;">
            <thead class="thead-light">
                <tr>
                    <th class="text-center" style="width: 80px;">Rank</th>
                    <th style="width: 100px;">Contestant #</th>
                    <th>Name</th>
                    <?php foreach ($levels as $level): ?>
                        <?php if (isset($allRounds[$level['id']])): ?>
                            <?php foreach ($allRounds[$level['id']] as $round): ?>
                                <th class="text-center" style="min-width: 80px;" title="<?= htmlspecialchars($level['name']) ?> - <?= htmlspecialchars($round['name']) ?>">
                                    <?= htmlspecialchars($round['name']) ?><br>
                                    <small>Average</small>
                                </th>
                            <?php endforeach; ?>
                            <th class="text-center bg-light" style="min-width: 100px;">
                                <?= htmlspecialchars($level['name']) ?><br>
                                <small><strong> Avg Score</strong></small>
                            </th>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <th class="text-center bg-warning" style="min-width: 120px;">
                        <strong> Overall Average Score</strong>
                    </th>
                    <th class="text-center no-print" style="width: 90px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contestantData as $data): ?>
                <tr class="<?= $data['rank'] <= 3.5 ? 'table-warning' : '' ?>">
                    <td class="text-center align-middle">
                        <?php if ($data['rank'] == 1): ?>
                            <h3 class="mb-0"><span class="badge bg-warning text-dark">1</span></h3>
                        <?php elseif ($data['rank'] == 2): ?>
                            <h4 class="mb-0"><span class="badge bg-secondary">2</span></h4>
                        <?php elseif ($data['rank'] >= 3 && $data['rank'] < 4): ?>
                            <h4 class="mb-0"><span class="badge" style="background-color: #CD7F32; color: white;"><?= $data['rank'] ?></span></h4>
                        <?php else: ?>
                            <h5 class="mb-0"><?= $data['rank'] ?></h5>
                        <?php endif; ?>
                    </td>
                    <td class="align-middle">
                        <strong>#<?= htmlspecialchars($data['contestant']['contestant_number']) ?></strong>
                    </td>
                    <td class="align-middle">
                        <strong><?= htmlspecialchars($data['contestant']['name']) ?></strong>
                    </td>
                    <?php foreach ($levels as $level): ?>
                        <?php if (isset($allRounds[$level['id']])): ?>
                            <?php foreach ($allRounds[$level['id']] as $round): ?>
                                <td class="text-center align-middle">
                                    <strong><?= ScoreFormatter::format($data['round_averages'][$round['id']] ?? 0, 2) ?></strong>
                                </td>
                            <?php endforeach; ?>
                            <td class="text-center align-middle bg-light">
                                <strong><?= ScoreFormatter::format($data['level_totals'][$level['id']] ?? 0, 2) ?></strong>
                            </td>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <td class="text-center align-middle bg-warning">
                        <strong class="text-success" style="font-size: 1.2em;">
                            <?= ScoreFormatter::format($data['adjusted_total'], 2) ?>
                        </strong>
                        <?php if (!empty($data['deduction']) && $data['deduction'] > 0): ?>
                            <div class="text-danger small" title="<?= htmlspecialchars($data['deduction_reason'] ?? '') ?>">
                                <i class="fas fa-minus-circle"></i> -<?= ScoreFormatter::format($data['deduction'], 2) ?>
                            </div>
                            <?php if (($data['deduction_status'] ?? '') === 'requested'): ?>
                                <div class="text-warning small">Pending approval</div>
                            <?php elseif (($data['deduction_status'] ?? '') === 'denied'): ?>
                                <div class="text-muted small">Denied</div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                    <td class="text-center align-middle no-print">
                        <button type="button"
                                class="btn btn-sm btn-danger deduct-btn"
                                data-contestant-id="<?= (int)$data['contestant']['id'] ?>"
                                data-deduction="<?= htmlspecialchars((string)($data['deduction'] ?? 0)) ?>"
                                data-reason="<?= htmlspecialchars((string)($data['deduction_reason'] ?? '')) ?>"
                                data-status="<?= htmlspecialchars((string)($data['deduction_status'] ?? '')) ?>"
                                <?= (($data['deduction_status'] ?? '') === 'requested') ? 'disabled' : '' ?>>
                            <i class="fas fa-minus-circle"></i>
                            <?= (($data['deduction_status'] ?? '') === 'requested') ? 'Pending' : 'Request Deduction' ?>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Print View - Tabulation Sheet Format -->
    <table class="tabulation-table" style="display: none;">
        <thead>
            <tr>
                <th class="contestant-no">Contestant<br>No.</th>
                <th>Name</th>
                <?php foreach ($levels as $level): ?>
                    <?php if (isset($allRounds[$level['id']])): ?>
                        <?php foreach ($allRounds[$level['id']] as $round): ?>
                            <th class="judge-col">
                                <?= htmlspecialchars($round['name']) ?><br>
                                <small>Average</small>
                            </th>
                        <?php endforeach; ?>
                        <th class="judge-col">
                            <?= htmlspecialchars($level['name']) ?><br>
                            <small><strong>Total Avg Score</strong></small>
                        </th>
                    <?php endif; ?>
                <?php endforeach; ?>
                <th class="total-col">OVERALL AVERAGE<br>SCORE</th>
                <th class="rank-col">RANK</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($contestantData as $data): ?>
            <tr class="<?= $data['rank'] <= 3.5 ? 'table-warning top-three-row' : '' ?>">
                <td class="contestant-no">
                    <?= htmlspecialchars($data['contestant']['contestant_number']) ?>
                </td>
                <td style="text-align: left; padding-left: 8px;">
                    <?= htmlspecialchars($data['contestant']['name']) ?>
                </td>
                <?php foreach ($levels as $level): ?>
                    <?php if (isset($allRounds[$level['id']])): ?>
                        <?php foreach ($allRounds[$level['id']] as $round): ?>
                            <td class="judge-col">
                                <?= ScoreFormatter::format($data['round_averages'][$round['id']] ?? 0, 2) ?>
                            </td>
                        <?php endforeach; ?>
                        <td class="judge-col">
                            <strong><?= ScoreFormatter::format($data['level_totals'][$level['id']] ?? 0, 2) ?></strong>
                        </td>
                    <?php endif; ?>
                <?php endforeach; ?>
                <td class="total-col">
                    <?= ScoreFormatter::format($data['adjusted_total'], 2) ?>
                </td>
                <td class="rank-col">
                    <?= $data['rank'] ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<script>
window.addEventListener('load', function() {
    document.addEventListener('click', function(event) {
        const btn = event.target.closest('.deduct-btn');
        if (!btn) return;
        
        if (typeof Swal === 'undefined') {
            alert('SweetAlert2 is not loaded.');
            return;
        }
        
        const contestantId = btn.getAttribute('data-contestant-id');
        const currentDeduction = parseFloat(btn.getAttribute('data-deduction')) || 0;
        const currentReason = btn.getAttribute('data-reason') || '';
        const url = '/tabulation/events/<?= (int)$event['id'] ?>/reports/finals/deduct';
        const csrfToken = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
        
        Swal.fire({
            title: 'Request Deduction (Final Average)',
            html:
                '<div class="text-left">' +
                '<label class="form-label mb-1">Deduction Amount</label>' +
                '<input id="deductionAmount" type="number" class="form-control" min="0" step="0.01">' +
                '<label class="form-label mt-2 mb-1">Reason (required)</label>' +
                '<input id="deductionReason" type="text" class="form-control">' +
                '<small class="text-muted d-block mt-2">This request will be sent to the event organizer.</small>' +
                '</div>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Send Request',
            cancelButtonText: 'Cancel',
            didOpen: () => {
                const amountInput = document.getElementById('deductionAmount');
                const reasonInput = document.getElementById('deductionReason');
                if (amountInput) amountInput.value = currentDeduction;
                if (reasonInput) reasonInput.value = currentReason;
            },
            preConfirm: () => {
                const amount = parseFloat(document.getElementById('deductionAmount').value) || 0;
                const reason = document.getElementById('deductionReason').value || '';
                if (amount <= 0) {
                    Swal.showValidationMessage('Deduction amount must be greater than 0.');
                    return false;
                }
                if (!reason.trim()) {
                    Swal.showValidationMessage('Reason is required.');
                    return false;
                }
                return { amount, reason };
            }
        }).then((result) => {
            if (!result.isConfirmed) return;
            
            const body = new URLSearchParams();
            body.set('csrf_token', csrfToken);
            body.set('contestant_id', contestantId);
            body.set('deduction', result.value.amount);
            body.set('reason', result.value.reason);
            
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: body.toString()
            })
                .then((response) => response.json().catch(() => null))
                .then((response) => {
                    if (response && response.success) {
                        location.reload();
                    } else {
                        Swal.fire('Error', (response && response.message) || 'Failed to apply deduction.', 'error');
                    }
                })
                .catch(() => {
                    Swal.fire('Error', 'Failed to apply deduction.', 'error');
                });
        });
    });
});
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
