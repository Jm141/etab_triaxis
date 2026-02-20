<?php 
$title = 'Round Report - ' . $round['name'];
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

<!-- Centered Tabulation Header (Visible on Screen and Print) -->
<div class="tabulation-header">
    <div class="header-with-logos">
        <div class="logo-left">
            <img src="/tabulation/public/images/diwataLogo.png" alt="Left Logo" class="header-logo" onerror="this.style.display='none';">
        </div>
        <div class="header-center">
            <div class="org-info">
                <!-- Organization info can be added here if available -->
            </div>
            <div class="event-name">
                <?= htmlspecialchars($event['name']) ?>
            </div>
            <div class="event-details">
                <?php if (!empty($round['level_name'])): ?>
                    <!-- <strong><?= htmlspecialchars($round['level_name']) ?></strong>
                    <?php if (!empty($round['name'])): ?>
                        - <?= htmlspecialchars($round['name']) ?>
                    <?php endif; ?> -->
                    <!-- <br> -->
                <?php endif; ?>
                <?php if (!empty($event['venue'])): ?>
                    Venue: <?= htmlspecialchars($event['venue']) ?><br>
                <?php endif; ?>
                <?= !empty($event['event_date']) ? date('F d, Y', strtotime($event['event_date'])) : date('F d, Y') ?>
            </div>
            <div class="tabulation-title">SUMMARY TABULATION SHEET - <?= htmlspecialchars($round['name']) ?></div>
        </div>
        <!-- <div class="logo-right">
            <img src="/tabulation/public/assets/images/logo-right.svg" alt="Right Logo" class="header-logo" onerror="this.style.display='none';">
        </div> -->
    </div>
    <div class="divider"></div>
</div>

<?php if (empty($contestantScores)): ?>
    <div class="alert alert-warning">No scores available for this round yet.</div>
<?php else: ?>
    <!-- Screen View Table -->
    <div class="table-responsive no-print">
        <table class="table table-bordered table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th class="text-center" style="width: 80px;">Rank</th>
                            <th style="width: 100px;">Contestant #</th>
                            <th style="width: 120px;">Name</th>
                            <?php foreach ($judges as $judge): ?>
                                <th class="text-center" style="min-width: 120px;">
                                    Judge #<?= htmlspecialchars($judge['judge_number'] ?: '') ?>
                                </th>
                            <?php endforeach; ?>
                            <th class="text-center" style="width: 120px;"><strong> Total Score</strong></th>
                            <th class="text-center no-print" style="width: 90px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($event['gender_mode'] === 'mr_miss'): ?>
                            <!-- MR & MISS Mode - Group by contestant number -->
                            <?php 
                            // Group contestants by number
                            $groupedContestants = [];
                            foreach ($contestantScores as $contestantId => $data) {
                                $number = $data['contestant']['contestant_number'];
                                if (!isset($groupedContestants[$number])) {
                                    $groupedContestants[$number] = [];
                                }
                                $groupedContestants[$number][] = [
                                    'id' => $contestantId,
                                    'data' => $data
                                ];
                            }
                            
                            foreach ($groupedContestants as $contestantNumber => $group):
                                foreach ($group as $contestant):
                                    $cs = $contestant['data'];
                            ?>
                        <tr>
                            <td class="text-center align-middle">
                                <?php if (isset($cs['contestant']['gender_rank'])): ?>
                                    <?php if ($cs['contestant']['gender_rank'] == 1): ?>
                                        <h3 class="mb-0"><span class="badge bg-warning text-dark">1</span></h3>
                                    <?php elseif ($cs['contestant']['gender_rank'] == 2): ?>
                                        <h4 class="mb-0"><span class="badge bg-secondary">2</span></h4>
                                    <?php elseif ($cs['contestant']['gender_rank'] >= 3 && $cs['contestant']['gender_rank'] < 4): ?>
                                        <h4 class="mb-0"><span class="badge" style="background-color: #CD7F32; color: white;"><?= $cs['contestant']['gender_rank'] ?></span></h4>
                                    <?php else: ?>
                                        <h5 class="mb-0"><?= $cs['contestant']['gender_rank'] ?></h5>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <h5 class="mb-0"><?= $cs['contestant']['rank'] ?></h5>
                                <?php endif; ?>
                            </td>
                            <td class="align-middle">
                                <strong>#<?= htmlspecialchars($cs['contestant']['contestant_number']) ?></strong>
                                <br>
                                <small class="text-muted"><?= htmlspecialchars($cs['contestant']['gender'] ?? 'Unknown') ?></small>
                            </td>
                            <td class="align-middle">
                                <?= htmlspecialchars($cs['contestant']['name']) ?>
                            </td>
                            <?php foreach ($judges as $judge): ?>
                                <td class="text-center align-middle">
                                    <strong><?= ScoreFormatter::format($cs['judge_raw_scores'][$judge['id']] ?? 0, 2) ?></strong>
                                </td>
                            <?php endforeach; ?>
                            <td class="text-center align-middle">
                                <strong class="text-success" style="font-size: 1.2em;">
                                    <?= ScoreFormatter::format($cs['adjusted_total'], 2) ?>
                                </strong>
                                <?php if (!empty($cs['deduction']) && $cs['deduction'] > 0): ?>
                                    <div class="text-danger small" title="<?= htmlspecialchars($cs['deduction_reason'] ?? '') ?>">
                                        <i class="fas fa-minus-circle"></i> -<?= ScoreFormatter::format($cs['deduction'], 2) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="text-center align-middle no-print">
                                <a href="/tabulation/score-management/edit/<?= $cs['contestant']['contestant_id'] ?>" 
                                   class="btn btn-sm btn-warning" 
                                   title="Edit Score">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            </td>
                        </tr>
                        <?php 
                                endforeach; // End contestant in group
                            endforeach; // End contestant number group
                        } else: ?>
                            <!-- Single Gender Mode - Original Layout -->
                            <?php foreach ($contestantScores as $cs): ?>
                        <tr>
                            <td class="text-center align-middle">
                                <?php if ($cs['contestant']['rank'] == 1): ?>
                                    <h3 class="mb-0"><span class="badge bg-warning text-dark">1</span></h3>
                                <?php elseif ($cs['contestant']['rank'] == 2): ?>
                                    <h4 class="mb-0"><span class="badge bg-secondary">2</span></h4>
                                <?php elseif ($cs['contestant']['rank'] >= 3 && $cs['contestant']['rank'] < 4): ?>
                                    <h4 class="mb-0"><span class="badge" style="background-color: #CD7F32; color: white;"><?= $cs['contestant']['rank'] ?></span></h4>
                                <?php else: ?>
                                    <h5 class="mb-0"><?= $cs['contestant']['rank'] ?></h5>
                                <?php endif; ?>
                            </td>
                            <td class="align-middle">
                                <strong>#<?= htmlspecialchars($cs['contestant']['contestant_number']) ?></strong>
                            </td>
                            <td class="align-middle">
                                <?= htmlspecialchars($cs['contestant']['name']) ?>
                            </td>
                            <?php foreach ($judges as $judge): ?>
                                <td class="text-center align-middle">
                                    <strong><?= ScoreFormatter::format($cs['judge_raw_scores'][$judge['id']] ?? 0, 2) ?></strong>
                                </td>
                            <?php endforeach; ?>
                            <td class="text-center align-middle">
                                <strong class="text-success" style="font-size: 1.2em;">
                                    <?= ScoreFormatter::format($cs['adjusted_total'], 2) ?>
                                </strong>
                                <?php if (!empty($cs['deduction']) && $cs['deduction'] > 0): ?>
                                    <div class="text-danger small" title="<?= htmlspecialchars($cs['deduction_reason'] ?? '') ?>">
                                        <i class="fas fa-minus-circle"></i> -<?= ScoreFormatter::format($cs['deduction'], 2) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="text-center align-middle no-print">
                                <a href="/tabulation/score-management/edit/<?= $cs['contestant']['contestant_id'] ?>" 
                                   class="btn btn-sm btn-warning" 
                                   title="Edit Score">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <?php if (empty($cs['deduction']) || $cs['deduction'] == 0): ?>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-danger mt-1 request-deduction"
                                            data-contestant-id="<?= $cs['contestant']['contestant_id'] ?>"
                                            data-round-id="<?= $round['id'] ?>"
                                            title="Request point deduction">
                                        <i class="fas fa-minus-circle"></i>
                                        <?= (($cs['deduction_status'] ?? '') === 'requested') ? 'Pending' : 'Request Deduction' ?>
                                    </button>
                                <?php else: ?>
                                    <div class="mt-1">
                                        <small class="text-muted">
                                            <i class="fas fa-minus-circle"></i> 
                                            <?= ScoreFormatter::format($cs['deduction'], 2) ?> 
                                            (<?= $cs['deduction_status'] ?? 'none' ?>)
                                        </small>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; // End single mode loop
                        } // End gender mode check ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Print View - Tabulation Sheet Format -->
    <table class="tabulation-table" style="display: none;">
        <thead>
            <tr>
                <th class="contestant-no">Contestant<br>No.</th>
                <th>Contestant Name</th>
                <?php foreach ($judges as $judge): ?>
                    <th class="judge-col">
                        Judge #<?= htmlspecialchars($judge['judge_number'] ?: '') ?>
                    </th>
                <?php endforeach; ?>
                <th class="total-col">Total Score</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($event['gender_mode'] === 'mr_miss'): ?>
                <!-- MR & MISS Mode - Group by contestant number -->
                <?php 
                // Group contestants by number for print view
                $groupedContestants = [];
                foreach ($contestantScores as $contestantId => $data) {
                    $number = $data['contestant']['contestant_number'];
                    if (!isset($groupedContestants[$number])) {
                        $groupedContestants[$number] = [];
                    }
                    $groupedContestants[$number][] = [
                        'id' => $contestantId,
                        'data' => $data
                    ];
                }
                
                foreach ($groupedContestants as $contestantNumber => $group):
                    foreach ($group as $contestant):
                        $cs = $contestant['data'];
                ?>
                <tr>
                    <td class="contestant-no"><?= $contestantNumber ?></td>
                    <td><?= htmlspecialchars($cs['contestant']['gender'] ?? 'Unknown') ?></td>
                    <?php foreach ($judges as $judge): ?>
                        <td class="judge-col">
                            <?= ScoreFormatter::format($cs['judge_raw_scores'][$judge['id']] ?? 0, 2) ?>
                        </td>
                    <?php endforeach; ?>
                    <td class="total-col">
                        <?= ScoreFormatter::format($cs['adjusted_total'], 2) ?>
                    </td>
                </tr>
                <?php 
                        endforeach; // End contestant in group
                    endforeach; // End contestant number group
                } else: ?>
                <!-- Single Gender Mode - Original Layout -->
                <?php foreach ($contestantScores as $cs): ?>
                <tr>
                    <td class="contestant-no"><?= $cs['contestant']['contestant_number'] ?></td>
                    <td><?= htmlspecialchars($cs['contestant']['name']) ?></td>
                    <?php foreach ($judges as $judge): ?>
                        <td class="judge-col">
                            <?= ScoreFormatter::format($cs['judge_raw_scores'][$judge['id']] ?? 0, 2) ?>
                        </td>
                    <?php endforeach; ?>
                    <td class="total-col">
                        <?= ScoreFormatter::format($cs['adjusted_total'], 2) ?>
                    </td>
                </tr>
                <?php endforeach; // End single mode loop
                } // End gender mode check ?>
        </tbody>
    </table>
<?php endif; ?>

<script>
// Copy functionality for tabulation sheet
document.addEventListener('DOMContentLoaded', function() {
    // Copy scores to clipboard functionality
    const copyButtons = document.querySelectorAll('.copy-scores');
    
    copyButtons.forEach(button => {
        button.addEventListener('click', function() {
            const contestantId = this.getAttribute('data-contestant-id');
            const scores = this.getAttribute('data-scores');
            
            // Create temporary textarea
            const textarea = document.createElement('textarea');
            textarea.value = scores;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            
            // Show feedback
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-check"></i> Copied!';
            setTimeout(() => {
                this.innerHTML = originalText;
            }, 2000);
        });
    });
});
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
