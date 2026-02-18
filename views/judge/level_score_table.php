<?php 
$title = 'Score Level: ' . htmlspecialchars($level['name']);
require __DIR__ . '/../layout/header.php'; 
?>
<!-- 
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
    <h1 class="mb-3 mb-md-0"><i class="fas fa-table"></i> <span class="d-none d-sm-inline"><?= htmlspecialchars($level['name']) ?></h1>
</div> -->

<?php if (empty($roundsData)): ?>
    <div class="alert alert-warning">No rounds assigned for this level.</div>
<?php else: ?>
    <div class="alert alert-info py-2">
        <i class="fas fa-info-circle"></i> Scores are <strong>saved to the database automatically</strong> as you enter or update them. Switch categories using the tabs. When finished, click <strong>Submit All Scores</strong> to confirm all scores are entered and to finalize (you will be notified if any are missing).
    </div>

    <!-- Excel-style sheet area: tabs above, one scoring matrix visible at a time -->
    <div class="level-score-sheet-wrapper card border">
        <!-- Category tabs above the sheet -->
        <div class="level-score-tabs">
            <div class="tabs-scroll-arrows tabs-left" title="Previous tabs"><i class="fas fa-chevron-left"></i></div>
            <div class="tabs-container">
                <?php foreach ($roundsData as $index => $rd):
                    $round = $rd['round'];
                    $roundId = (int)$round['id'];
                    $panelId = 'panel-' . $roundId;
                    $isFirst = ($index === 0);
                    $catIndex = $index % 6;
                ?>
                <button type="button" class="score-sheet-tab cat-color-<?= $catIndex ?> <?= $isFirst ? 'active' : '' ?>" data-panel-id="<?= $panelId ?>" data-round-id="<?= $roundId ?>">
                    <?= htmlspecialchars($round['name']) ?>
                </button>
                <?php endforeach; ?>
            </div>
            <div class="tabs-scroll-arrows tabs-right" title="Next tabs"><i class="fas fa-chevron-right"></i></div>
             
        </div>
        <div class="card-body p-0 position-relative" style="min-height: 320px;">
            <?php foreach ($roundsData as $index => $rd): 
                $round = $rd['round'];
                $contestants = $rd['contestants'];
                $criteria = $rd['criteria'];
                $allScores = $rd['allScores'];
                $allScoreDetails = $rd['allScoreDetails'];
                $roundId = (int)$round['id'];
                $panelId = 'panel-' . $roundId;
                $isFirst = ($index === 0);
                $catIndex = $index % 6;
            ?>
            <div id="<?= $panelId ?>" class="score-sheet-panel cat-color-<?= $catIndex ?>" data-round-id="<?= $roundId ?>" style="<?= $isFirst ? '' : 'display:none;' ?>">
                <?php 
                // Check if any contestant in this category has submitted scores
                $anySubmitted = false;
                if (!empty($allScores)) {
                    foreach ($allScores as $score) {
                        if ($score['is_submitted'] ?? false) {
                            $anySubmitted = true;
                            break;
                        }
                    }
                }
                $canEditCategory = !$anySubmitted;
                ?>
                <!-- Debug: canEditCategory = <?php var_dump($canEditCategory); ?>, anySubmitted = <?php var_dump($anySubmitted); ?> -->
                 
                <?php if (empty($contestants) || empty($criteria)): ?>
                    <div class="p-4 text-muted">No contestants or criteria for this category.</div>
                <?php else: 
                    $totalMax = array_sum(array_column($criteria, 'max_score'));
                ?>
                <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch; position: relative;">
                    <!-- Submit button positioned outside table -->
                    <div style="position: absolute; top: 10px; right: 10px; z-index: 25; background: white; padding: 5px; border-radius: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.15); border: 1px solid #dee2e6;">
                        <?php if ($canEditCategory): ?>
                            <button type="button" 
                                    class="btn btn-success btn-sm submit-category-btn" 
                                    data-round-id="<?= $roundId ?>" 
                                    data-category-name="<?= htmlspecialchars($round['name']) ?>"
                                    style="font-size: 11px; padding: 4px 8px;">
                                <i class="fas fa-check-circle"></i> 
                                Submit All
                            </button>
                        <?php else: ?>
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-muted" style="font-size: 10px;">
                                    <i class="fas fa-lock"></i> Submitted
                                </span>
                                <button type="button" 
                                        class="btn btn-warning btn-xs request-edit-permission" 
                                        data-round-id="<?= $roundId ?>" 
                                        data-category-name="<?= htmlspecialchars($round['name']) ?>"
                                        title="Request permission to edit submitted scores"
                                        style="font-size: 9px; padding: 2px 6px;">
                                    <i class="fas fa-key"></i> Request
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <table class="table table-bordered table-hover table-sm scoring-table-level mb-0" data-round-id="<?= $roundId ?>" data-max-total="<?= (float)$totalMax ?>" data-category-name="<?= htmlspecialchars($round['name']) ?>">
                        <thead class="thead-dark sticky-top thead-cat-accent" style="background-color: #37474f; z-index: 10;">
                            <tr>
                                <th class="align-middle text-center" style="min-width: 100px; position: sticky; left: 0; background-color: #37474f; z-index: 11; font-size: 14px;"><strong>Contestant #</strong></th>
                                <?php foreach ($criteria as $criterion): ?>
                                <th class="text-center criteria-header-cell" style="font-size: 13px;">
                                    <div class="criteria-header-inner">
                                        <strong style="font-size: 14px;"><?= htmlspecialchars($criterion['name']) ?></strong>
                                        <br><span class="badge badge-info" style="font-size: 11px;">Max <?= number_format($criterion['max_score'], 0) ?></span>
                                    </div>
                                </th>
                                <?php endforeach; ?>
                                <th class="align-middle text-center" style="min-width: 100px; font-size: 14px;"><strong>Total Score <?= number_format($totalMax, 0) ?></strong></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($contestants as $contestant): 
                                $existingScore = null;
                                $scoreDetails = [];
                                $isSubmitted = false;
                                $permissionStatus = 'none';
                                if (!empty($allScores)) {
                                    foreach ($allScores as $score) {
                                        if ($score['contestant_id'] == $contestant['id']) {
                                            $existingScore = $score;
                                            $isSubmitted = $score['is_submitted'] ?? false;
                                            $permissionStatus = $score['permission_request_status'] ?? 'none';
                                            if (!empty($allScoreDetails[$score['id']])) $scoreDetails = $allScoreDetails[$score['id']];
                                            break;
                                        }
                                    }
                                }
                                $canEdit = !$isSubmitted || $permissionStatus === 'granted';
                                $rowClass = $isSubmitted ? 'table-success' : '';
                                $rowTotal = 0;
                                foreach ($criteria as $c) {
                                    if (isset($scoreDetails[$c['id']])) $rowTotal += (float)$scoreDetails[$c['id']]['raw_score'];
                                }
                            ?>
                            <tr data-contestant-id="<?= $contestant['id'] ?>" class="<?= $rowClass ?>">
                                <td class="text-center font-weight-bold" style="position: sticky; left: 0; background-color: inherit; z-index: 1;"><?= htmlspecialchars($contestant['contestant_number']) ?></td>
                                <?php foreach ($criteria as $criterion): 
                                    $detail = isset($scoreDetails[$criterion['id']]) ? $scoreDetails[$criterion['id']] : null;
                                    $value = $detail ? number_format($detail['raw_score'], 2, '.', '') : '';
                                ?>
                                <td class="text-center">
                                    <input type="number" 
                                           class="form-control form-control-sm score-input text-center" 
                                           data-contestant-id="<?= $contestant['id'] ?>"
                                           data-criteria-id="<?= $criterion['id'] ?>"
                                           data-round-id="<?= $roundId ?>"
                                           data-max="<?= $criterion['max_score'] ?>"
                                           value="<?= $value ?>"
                                           min="0" max="<?= $criterion['max_score'] ?>" step="0.01" placeholder="0"
                                           <?= !$canEdit ? 'readonly' : '' ?>
                                           style="min-width: 80px; width: 100px; margin: 0 auto;">
                                    <?php if (!$canEdit): ?><small class="text-muted d-block mt-1"><i class="fas fa-lock"></i></small><?php endif; ?>
                                </td>
                                <?php endforeach; ?>
                                <td class="text-center align-middle raw-total-cell" data-contestant-id="<?= $contestant['id'] ?>" data-round-id="<?= $roundId ?>" style="font-size: 13px;"><?php
                                    if ($rowTotal > 0 && $totalMax > 0) {
                                        $pct = ($rowTotal / $totalMax) * 100;
                                        echo number_format($rowTotal, 2) . ' <span class="raw-total-pct text-muted small"></span>';
                                    } else {
                                        echo '—';
                                    }
                                ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Tab switching + Raw Total + Auto-save to DB: vanilla JS, no jQuery, runs immediately -->
    <script>
    (function() {
        var _csrf = <?= json_encode(Session::getCSRFToken() ?: '') ?>;
        var _baseUrl = '/tabulation';
        function initScoreSheetTabs() {
            var wrapper = document.querySelector('.level-score-sheet-wrapper');
            if (!wrapper) return;
            wrapper.addEventListener('click', function(e) {
                var tab = e.target.closest('.score-sheet-tab');
                if (!tab) return;
                e.preventDefault();
                e.stopPropagation();
                var panelId = tab.getAttribute('data-panel-id');
                if (!panelId) return;
                var panels = wrapper.querySelectorAll('.score-sheet-panel');
                for (var i = 0; i < panels.length; i++) {
                    panels[i].style.display = 'none';
                }
                var panel = document.getElementById(panelId);
                if (panel) {
                    panel.style.display = 'block';
                }
                var tabs = wrapper.querySelectorAll('.score-sheet-tab');
                for (var j = 0; j < tabs.length; j++) {
                    tabs[j].classList.remove('active');
                }
                tab.classList.add('active');
            });
            var tabsContainer = wrapper.querySelector('.tabs-container');
            if (tabsContainer) {
                var left = wrapper.querySelector('.tabs-left');
                var right = wrapper.querySelector('.tabs-right');
                if (left) left.addEventListener('click', function() { tabsContainer.scrollBy({ left: -120, behavior: 'smooth' }); });
                if (right) right.addEventListener('click', function() { tabsContainer.scrollBy({ left: 120, behavior: 'smooth' }); });
            }
        }
        function updateRawTotalForRow(row) {
            var table = row.closest('table');
            var inputs = row.querySelectorAll('.score-input');
            var total = 0;
            for (var i = 0; i < inputs.length; i++) {
                var v = parseFloat(inputs[i].value);
                if (!isNaN(v)) total += v;
            }
            var cell = row.querySelector('.raw-total-cell');
            if (!cell) return;
            
            // Get the actual max total for this category from the table data attribute
            var maxTotal = 100;
            if (table && table.getAttribute('data-max-total')) {
                maxTotal = parseFloat(table.getAttribute('data-max-total')) || 100;
            }
            
            if (total > 0 && maxTotal > 0) {
                var pct = (total / maxTotal * 100).toFixed(1);
                cell.innerHTML = total.toFixed(2) + ' <span class="raw-total-pct text-muted small">(' + pct + '%)</span>';
            } else {
                cell.innerHTML = '—';
            }
        }
        function initRawTotalUpdates() {
            var wrapper = document.querySelector('.level-score-sheet-wrapper');
            if (!wrapper) return;
            wrapper.addEventListener('input', function(e) {
                if (!e.target.classList.contains('score-input')) return;
                var row = e.target.closest('tr');
                if (row) updateRawTotalForRow(row);
            });
            wrapper.addEventListener('change', function(e) {
                if (!e.target.classList.contains('score-input')) return;
                var row = e.target.closest('tr');
                if (row) updateRawTotalForRow(row);
            });
        }
        var autoSaveTimer = null, autoSaveInFlight = false;
        function saveScoreToDb(input, callback) {
            var roundId = input.getAttribute('data-round-id');
            var contestantId = input.getAttribute('data-contestant-id');
            var criteriaId = input.getAttribute('data-criteria-id');
            var rawScore = parseFloat(input.value);
            if (!roundId || !contestantId || !criteriaId || isNaN(rawScore) || rawScore < 0) return;
            var max = parseFloat(input.getAttribute('data-max')) || 0;
            if (max > 0 && rawScore > max) rawScore = max;
            autoSaveInFlight = true;
            var body = new FormData();
            body.append('csrf_token', _csrf);
            body.append('criteria_id', criteriaId);
            body.append('raw_score', rawScore);
            fetch(_baseUrl + '/judge/rounds/' + roundId + '/contestants/' + contestantId + '/auto-save', {
                method: 'POST',
                body: body,
                credentials: 'same-origin'
            }).then(function(r) { return r.json(); }).then(function(data) {
                autoSaveInFlight = false;
                if (callback) callback(data);
            }).catch(function() {
                autoSaveInFlight = false;
                if (callback) callback({ success: false });
            });
        }
        function initAutoSaveToDb() {
            var wrapper = document.querySelector('.level-score-sheet-wrapper');
            if (!wrapper) return;
            wrapper.addEventListener('input', function(e) {
                if (!e.target.classList.contains('score-input') || e.target.readOnly) return;
                var val = parseFloat(e.target.value);
                if (isNaN(val) || val < 0) return;
                var max = parseFloat(e.target.getAttribute('data-max')) || 0;
                if (max > 0 && val > max) return;
                if (autoSaveTimer) clearTimeout(autoSaveTimer);
                autoSaveTimer = setTimeout(function() {
                    if (autoSaveInFlight) return;
                    saveScoreToDb(e.target);
                }, 700);
            });
            wrapper.addEventListener('change', function(e) {
                if (!e.target.classList.contains('score-input') || e.target.readOnly) return;
                var val = parseFloat(e.target.value);
                if (isNaN(val) || val < 0) return;
                var max = parseFloat(e.target.getAttribute('data-max')) || 0;
                if (max > 0 && val > max) { e.target.value = max.toFixed(2); val = max; }
                if (autoSaveTimer) clearTimeout(autoSaveTimer);
                autoSaveTimer = null;
                saveScoreToDb(e.target);
            });
        }
        var _levelId = <?= (int)$level['id'] ?>;
        function initSubmitAllButton() {
            var btn = document.getElementById('submitAllBtn');
            if (!btn) return;
            btn.addEventListener('click', function() {
                var inputs = document.querySelectorAll('.level-score-sheet-wrapper .score-input:not([readonly])');
                var missing = [], exceeded = [];
                inputs.forEach(function(inp) {
                    var v = (inp.value || '').trim();
                    var num = parseFloat(v);
                    var max = parseFloat(inp.getAttribute('data-max')) || 0;
                    if (!v || v === '' || v === '0' || v === '0.00') {
                        var row = inp.closest('tr');
                        var table = inp.closest('table');
                        var contestantNum = row && row.querySelector('td') ? row.querySelector('td').textContent.trim() : '?';
                        var catName = table && table.getAttribute('data-category-name') ? table.getAttribute('data-category-name') : 'Category';
                        missing.push('Contestant #' + contestantNum + ' — ' + catName);
                    } else if (!isNaN(num) && max > 0 && num > max) {
                        exceeded.push(num.toFixed(2) + ' (max ' + max + ')');
                    }
                });
                if (exceeded.length > 0) {
                    Swal.fire('Warning', 'Some scores exceed the maximum allowed. Please correct them.\n\nScores you entered are already saved.', 'warning');
                    return;
                }
                if (missing.length > 0) {
                    var msg = 'Your entered scores are already saved in the database.\n\nThe following have not been entered yet. Please fill them in, then click Submit All again:\n\n' + missing.slice(0, 20).join('\n');
                    if (missing.length > 20) msg += '\n... and ' + (missing.length - 20) + ' more';
                    Swal.fire('Missing Scores', msg, 'warning');
                    return;
                }
                if (!confirm('All scores are saved. Submit all to finalize? You will need permission to edit after.')) return;
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
                var body = new FormData();
                body.append('csrf_token', _csrf);
                fetch(_baseUrl + '/judge/rounds/' + roundId + '/submit-category', {
                    method: 'POST',
                    body: body,
                    credentials: 'same-origin'
                }).then(function(r) { return r.json(); }).then(function(data) {
                    if (data.success) {
                        Swal.fire('Success', 'All scores have been saved and submitted.', 'success').then(function() {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Error', data.message || 'Something went wrong.', 'error');
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-check-circle"></i> <span class="d-none d-sm-inline">Submit All Scores (All Categories)</span><span class="d-sm-none">Submit All</span>';
                    }
                }).catch(function() {
                    Swal.fire('Error', 'Error submitting. Please try again.', 'error');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-check-circle"></i> <span class="d-none d-sm-inline">Submit All Scores (All Categories)</span><span class="d-sm-none">Submit All</span>';
                });
            });
        }
        function init() {
            initScoreSheetTabs();
            initRawTotalUpdates();
            initAutoSaveToDb();
            initSubmitAllButton();
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }
    })();
    </script>
<?php endif; ?>

<style>
.level-score-sheet-wrapper { border-color: #cfd8dc !important; }
.level-score-sheet-wrapper .card-body { background: #fafafa; }
.scoring-table-level thead th { background-color: #37474f !important; color: #eceff1 !important; border-color: #546e7a !important; }
.scoring-table-level thead th.criteria-header-cell { min-width: 150px; padding: 12px 10px; }
.scoring-table-level thead th.criteria-header-cell .criteria-header-inner { font-size: 1rem; line-height: 1.4; }
.scoring-table-level thead th.criteria-header-cell .criteria-header-inner strong { font-size: 1.05rem; }
.scoring-table-level thead th.criteria-header-cell .badge { font-size: 0.8rem; padding: 4px 8px; }
.scoring-table-level tbody tr.table-success { background-color: rgba(77, 182, 172, 0.08) !important; }
.scoring-table-level td, .scoring-table-level th { vertical-align: middle !important; }
.score-input:read-only { background-color: #eceff1; cursor: not-allowed; }
.score-input:focus { border-color: #4db6ac; box-shadow: 0 0 0 0.2rem rgba(77, 182, 172, 0.25); }
.raw-total-cell { font-weight: 600; color: #263238; }
.raw-total-cell .raw-total-pct { font-weight: 400; font-size: 0.85em; }

/* Excel-style tabs above the sheet */
.level-score-tabs {
    display: flex;
    align-items: stretch;
    background: #455a64;
    border-bottom: 1px solid #546e7a;
    padding: 0 28px 0 28px;
    min-height: 38px;
}
.tabs-scroll-arrows {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    color: #888;
    cursor: pointer;
    flex-shrink: 0;
    margin: 0 -28px 0 0;
}
.tabs-scroll-arrows:hover { color: #80cbc4; }
.tabs-right { margin: 0 0 0 -28px; }
.tabs-container {
    display: flex;
    flex-wrap: nowrap;
    overflow-x: auto;
    overflow-y: hidden;
    gap: 0;
    flex: 1;
    min-width: 0;
    -webkit-overflow-scrolling: touch;
}
.tabs-container::-webkit-scrollbar { height: 6px; }
.tabs-container::-webkit-scrollbar-thumb { background: #555; border-radius: 3px; }
.score-sheet-tab {
    flex-shrink: 0;
    padding: 8px 14px;
    border: 1px solid #546e7a;
    border-top: none;
    border-radius: 0 0 4px 4px;
    margin-bottom: 4px;
    background: #546e7a;
    color: rgba(255,255,255,0.9);
    font-size: 0.9rem;
    cursor: pointer;
    white-space: nowrap;
    transition: background .15s, color .15s;
}
.score-sheet-tab:hover { color: #fff; }
/* Category 0: Teal (e.g. Production) */
.score-sheet-tab.cat-color-0 { border-left: 3px solid #00897b; }
.score-sheet-tab.cat-color-0:hover { background: #00695c; }
.score-sheet-tab.cat-color-0.active { background: #e0f2f1; color: #00695c; border-color: #00897b; border-bottom-color: #e0f2f1; }
.score-sheet-panel.cat-color-0 .thead-cat-accent th { border-top: 4px solid #00897b; }
/* Category 1: Blue (e.g. Swimsuit) */
.score-sheet-tab.cat-color-1 { border-left: 3px solid #1976d2; }
.score-sheet-tab.cat-color-1:hover { background: #1565c0; }
.score-sheet-tab.cat-color-1.active { background: #e3f2fd; color: #0d47a1; border-color: #1976d2; border-bottom-color: #e3f2fd; }
.score-sheet-panel.cat-color-1 .thead-cat-accent th { border-top: 4px solid #1976d2; }
/* Category 2: Amber (e.g. Casual Wear) */
.score-sheet-tab.cat-color-2 { border-left: 3px solid #f57c00; }
.score-sheet-tab.cat-color-2:hover { background: #ef6c00; }
.score-sheet-tab.cat-color-2.active { background: #fff3e0; color: #e65100; border-color: #f57c00; border-bottom-color: #fff3e0; }
.score-sheet-panel.cat-color-2 .thead-cat-accent th { border-top: 4px solid #f57c00; }
/* Category 3: Purple (e.g. Evening Gown) */
.score-sheet-tab.cat-color-3 { border-left: 3px solid #7b1fa2; }
.score-sheet-tab.cat-color-3:hover { background: #6a1b9a; }
.score-sheet-tab.cat-color-3.active { background: #f3e5f5; color: #4a148c; border-color: #7b1fa2; border-bottom-color: #f3e5f5; }
.score-sheet-panel.cat-color-3 .thead-cat-accent th { border-top: 4px solid #7b1fa2; }
/* Category 4: Rose (e.g. 5th category) */
.score-sheet-tab.cat-color-4 { border-left: 3px solid #c2185b; }
.score-sheet-tab.cat-color-4:hover { background: #ad1457; }
.score-sheet-tab.cat-color-4.active { background: #fce4ec; color: #880e4f; border-color: #c2185b; border-bottom-color: #fce4ec; }
.score-sheet-panel.cat-color-4 .thead-cat-accent th { border-top: 4px solid #c2185b; }
/* Category 5: Indigo (e.g. 6th category) */
.score-sheet-tab.cat-color-5 { border-left: 3px solid #3949ab; }
.score-sheet-tab.cat-color-5:hover { background: #303f9f; }
.score-sheet-tab.cat-color-5.active { background: #e8eaf6; color: #1a237e; border-color: #3949ab; border-bottom-color: #e8eaf6; }
.score-sheet-panel.cat-color-5 .thead-cat-accent th { border-top: 4px solid #3949ab; }
</style>

<script>
(function(){
    
    // Handle per-category submission buttons
    $(document).on('click', '.request-edit-permission', function() {
        var roundId = $(this).data('round-id'), scoreId = $(this).data('score-id'), contestantId = $(this).data('contestant-id');
        if (!roundId || !scoreId || !contestantId) return;
        // New code to handle request-edit-permission click event
        console.log('Request edit permission clicked');
        // Add your logic here
            url: '/tabulation/judge/rounds/' + roundId + '/request-edit-permission',
            method: 'POST',
            dataType: 'json',
            data: { csrf_token: csrfToken, score_id: scoreId, contestant_id: contestantId },
            success: function(r) { if (r.success) Swal.fire('Sent', r.message || 'Permission request sent.', 'success'); else Swal.fire('Error', r.message, 'error'); },
            error: function() { Swal.fire('Error', 'Request failed.', 'error'); }
        });
    });

    // Tab switching is in a separate script block above (no jQuery) so it always runs

    // Auto-calculate Raw Total and percentage (on 100% scale) when judge inputs scores
    $(document).on('input change', '.score-input', function() {
        var $row = $(this).closest('tr');
        var $table = $row.closest('table');
        var maxTotal = parseFloat($table.data('max-total')) || 100;
        var total = 0;
        $row.find('.score-input').each(function() { var v = parseFloat($(this).val()); if (!isNaN(v)) total += v; });
        var $cell = $row.find('.raw-total-cell');
        if (total > 0 && maxTotal > 0) {
            var pct = (total / maxTotal * 100).toFixed(1);
            $cell.html(total.toFixed(2) + ' <span class="raw-total-pct text-muted small">(' + pct + '%)</span>');
        } else {
            $cell.html('—');
        }
    });
    
    $(document).ready(function() { checkSubmitButton(); });
    
    // Handle per-category submission buttons
    $(document).on('click', '.submit-category-btn', function() {
        const $btn = $(this);
        const roundId = $btn.data('round-id');
        const categoryName = $btn.data('category-name');
        
        if ($btn.prop('disabled')) return;
        
        // Check all inputs in this category
        const $panel = $btn.closest('.score-sheet-panel');
        const $inputs = $panel.find('.score-input:not([readonly])');
        let missing = [], exceeded = [], incomplete = [];
        
        $inputs.each(function() {
            const $input = $(this);
            const v = $input.val() ? $input.val().trim() : '';
            const num = parseFloat(v);
            const max = parseFloat($input.data('max')) || 0;
            
            if (!v || v === '' || v === '0' || v === '0.00') {
                const $row = $input.closest('tr');
                const contestantNum = $row.find('td:first').text().trim();
                incomplete.push('Contestant #' + contestantNum);
            } else if (!isNaN(num) && max > 0 && num > max) {
                exceeded.push(num.toFixed(2) + ' (max ' + max + ')');
            }
        });
        
        if (exceeded.length > 0) {
            Swal.fire('Warning', 'Some scores exceed the maximum allowed for ' + categoryName + '. Please correct them.', 'warning');
            return;
        }
        
        if (incomplete.length > 0) {
            Swal.fire({
                title: 'Incomplete Scores',
                html: 'The following contestants in ' + categoryName + ' have incomplete scores:<br><br><strong>' + incomplete.join(', ') + '</strong><br><br>Please enter all scores before submitting.',
                icon: 'warning',
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'OK'
            });
            return;
        }
        
        Swal.fire({
            title: 'Submit Category',
            text: 'Are you sure you want to submit all scores for ' + categoryName + '? You will need permission to edit after.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Submit ' + categoryName,
            confirmButtonColor: '#28a745'
        }).then(function(result) {
            if (result.isConfirmed) {
                $btn.prop('disabled', true);
                $btn.html('<i class="fas fa-spinner fa-spin"></i> Submitting...');
                
                const body = new FormData();
                body.append('csrf_token', csrfToken);
                body.append('round_id', roundId);
                
                fetch(_baseUrl + '/judge/rounds/' + roundId + '/submit-all', {
                    method: 'POST',
                    body: body,
                    credentials: 'same-origin'
                }).then(function(r) { return r.json(); }).then(function(data) {
                    if (data.success) {
                        Swal.fire('Success', categoryName + ' scores have been submitted successfully.', 'success').then(function() {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Error', data.message || 'Something went wrong.', 'error');
                        $btn.prop('disabled', false);
                        $btn.html('<i class="fas fa-check-circle"></i> Submit All');
                    }
                }).catch(function() {
                    Swal.fire('Error', 'Error submitting ' + categoryName + '. Please try again.', 'error');
                    $btn.prop('disabled', false);
                    $btn.html('<i class="fas fa-check-circle"></i> Submit All');
                });
            }
        });
    });

    // Handle request edit permission button for categories
    $(document).on('click', '.request-edit-permission', function() {
        const $btn = $(this);
        const roundId = $btn.data('round-id');
        const categoryName = $btn.data('category-name');
        
        Swal.fire({
            title: 'Request Edit Permission',
            text: 'Are you sure you want to request permission to edit submitted scores for ' + categoryName + '?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Request Permission',
            confirmButtonColor: '#ffc107'
        }).then(function(result) {
            if (result.isConfirmed) {
                $btn.prop('disabled', true);
                $btn.html('<i class="fas fa-spinner fa-spin"></i> Requesting...');
                
                const body = new FormData();
                body.append('csrf_token', csrfToken);
                body.append('round_id', roundId);
                
                fetch(_baseUrl + '/judge/rounds/' + roundId + '/request-edit-permission', {
                    method: 'POST',
                    body: body,
                    credentials: 'same-origin'
                }).then(function(r) { return r.json(); }).then(function(data) {
                    if (data.success) {
                        Swal.fire('Request Sent', data.message || 'Permission request sent successfully.', 'success');
                        $btn.removeClass('btn-warning').addClass('btn-info');
                        $btn.html('<i class="fas fa-clock"></i> Pending');
                        $btn.prop('disabled', true);
                    } else {
                        Swal.fire('Error', data.message || 'Failed to send permission request.', 'error');
                        $btn.prop('disabled', false);
                        $btn.html('<i class="fas fa-key"></i> Request');
                    }
                }).catch(function() {
                    Swal.fire('Error', 'Error sending permission request. Please try again.', 'error');
                    $btn.prop('disabled', false);
                    $btn.html('<i class="fas fa-key"></i> Request');
                });
            }
        });
    });
    
    // Add heartbeat to keep session active and update last_activity
    setInterval(function() {
        $.ajax({
            url: '/tabulation/judge/heartbeat',
            method: 'POST',
            data: { csrf_token: csrfToken },
            success: function() {
                // Heartbeat successful - session remains active
            },
            error: function() {
                // Heartbeat failed - may indicate connection issues
            }
        });
    }, 8000); // Send heartbeat every 8 seconds (faster than 10-second ping check)
    
    // Handle browser close/tab close to mark session as inactive
    window.addEventListener('beforeunload', function(e) {
        // Send synchronous request to mark session as inactive
        navigator.sendBeacon('/tabulation/judge/disconnect', new FormData().append('csrf_token', csrfToken));
    });
    
    // Handle page visibility changes (tab switching)
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            // Page is hidden, send disconnect signal
            navigator.sendBeacon('/tabulation/judge/disconnect', new FormData().append('csrf_token', csrfToken));
        } else {
            // Page is visible again, send heartbeat immediately
            $.ajax({
                url: '/tabulation/judge/heartbeat',
                method: 'POST',
                data: { csrf_token: csrfToken },
                success: function() {
                    // Session reactivated
                }
            });
        }
    });
})();
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
