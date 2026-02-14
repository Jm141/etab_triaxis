<?php 
$title = 'Scoring Formula Management - ' . $event['name'];
require __DIR__ . '/../layout/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-calculator"></i> Scoring Formula Management</h1>
    <a href="/tabulation/events/<?= $event['id'] ?>/reports" class="btn btn-secondary">Back to Reports</a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <h5><?= htmlspecialchars($event['name']) ?></h5>
        <p class="text-muted">Only Event Technical Admins can view and edit scoring formulas.</p>
        <div class="mt-2">
            <a href="/tabulation/docs/SCORING_FORMULAS.md" target="_blank" class="btn btn-sm btn-info">
                <i class="fas fa-book"></i> View Complete Formula Reference
            </a>
            <a href="/tabulation/docs/HOW_WINNERS_ARE_CALCULATED.md" target="_blank" class="btn btn-sm btn-success">
                <i class="fas fa-calculator"></i> How Winners Are Calculated
            </a>
        </div>
    </div>
</div>

<?php
// Variables passed from controller: $allLevels, $allRounds
// These are already available in the view
?>

<div class="card mb-4">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0"><i class="fas fa-question-circle"></i> Standard Pageant Formulas</h5>
    </div>
    <div class="card-body">
        <div class="accordion" id="formulaAccordion">
            <!-- Formula 1: Average Score (Basic) -->
            <div class="card mb-2">
                <div class="card-header" id="heading1">
                    <h6 class="mb-0">
                        <button class="btn btn-link btn-sm text-left w-100" type="button" data-toggle="collapse" data-target="#collapse1">
                            <strong>1. Average Score (Basic Pageant Formula)</strong>
                        </button>
                    </h6>
                </div>
                <div id="collapse1" class="collapse" data-parent="#formulaAccordion">
                    <div class="card-body">
                        <p><strong>Formula:</strong> <code>AVG(round_scores)</code></p>
                        <p class="small">Average of all judge scores across selected rounds.</p>
                        <p class="small"><strong>Mathematical:</strong> (Score₁ + Score₂ + ... + Scoreₙ) ÷ n</p>
                        <p class="small"><strong>Example:</strong> Scores: 90, 88, 92 → (90 + 88 + 92) ÷ 3 = 90</p>
                        <button class="btn btn-sm btn-primary" onclick="useFormula('AVG(round_scores)', 'Average of all judge scores across selected rounds. Mathematical: (Score₁ + Score₂ + ... + Scoreₙ) ÷ n. Example: Scores: 90, 88, 92 → (90 + 88 + 92) ÷ 3 = 90')">
                            <i class="fas fa-copy"></i> Use This Formula
                        </button>
                    </div>
                </div>
            </div>

            <!-- Formula 2: Weighted Average -->
            <div class="card mb-2">
                <div class="card-header" id="heading2">
                    <h6 class="mb-0">
                        <button class="btn btn-link btn-sm text-left w-100" type="button" data-toggle="collapse" data-target="#collapse2">
                            <strong>2. Weighted Average (Most Common in Pageants)</strong>
                        </button>
                    </h6>
                </div>
                <div id="collapse2" class="collapse" data-parent="#formulaAccordion">
                    <div class="card-body">
                        <p><strong>Formula:</strong> <code>(round_totals[0] * 0.4) + (round_totals[1] * 0.3) + (round_totals[2] * 0.3)</code></p>
                        <p class="small">Each round/criterion has a percentage weight. Adjust weights as needed.</p>
                        <p class="small"><strong>Mathematical:</strong> (S₁ × W₁) + (S₂ × W₂) + ... + (Sₙ × Wₙ)</p>
                        <p class="small"><strong>Example:</strong> Round 1 (40%): 90, Round 2 (30%): 85, Round 3 (30%): 88</p>
                        <p class="small">→ (90 × 0.40) + (85 × 0.30) + (88 × 0.30) = 88.9</p>
                        <button class="btn btn-sm btn-primary" onclick="useFormula('(round_totals[0] * 0.4) + (round_totals[1] * 0.3) + (round_totals[2] * 0.3)', 'Weighted Average - Most common pageant formula')">
                            <i class="fas fa-copy"></i> Use This Formula
                        </button>
                    </div>
                </div>
            </div>

            <!-- Formula 3: Average per Judge -->
            <div class="card mb-2">
                <div class="card-header" id="heading3">
                    <h6 class="mb-0">
                        <button class="btn btn-link btn-sm text-left w-100" type="button" data-toggle="collapse" data-target="#collapse3">
                            <strong>3. Average per Judge (Multiple Judges) - For Round Reports</strong>
                        </button>
                    </h6>
                </div>
                <div id="collapse3" class="collapse" data-parent="#formulaAccordion">
                    <div class="card-body">
                        <p><strong>Formula for Round Type:</strong> <code>AVG(judge_scores)</code> or <code>AVG(round_averages)</code></p>
                        <p class="small">Average of all judge scores for each contestant. Use this for Round formula type.</p>
                        <p class="small"><strong>Mathematical:</strong> Σ(Judge Scores) ÷ Number of Judges</p>
                        <p class="small"><strong>Example:</strong> Judges' scores: 89, 91, 90, 88, 92 → (89 + 91 + 90 + 88 + 92) ÷ 5 = 90</p>
                        <p class="small text-warning"><strong>Note:</strong> Select "Round" as Formula Type when using this formula.</p>
                        <button class="btn btn-sm btn-primary" onclick="useFormula('AVG(judge_scores)', 'Average per Judge - Multiple judges formula (for Round type)')">
                            <i class="fas fa-copy"></i> Use This Formula (Round Type)
                        </button>
                    </div>
                </div>
            </div>

            <!-- Formula 4: Final Pageant Score -->
            <div class="card mb-2">
                <div class="card-header" id="heading4">
                    <h6 class="mb-0">
                        <button class="btn btn-link btn-sm text-left w-100" type="button" data-toggle="collapse" data-target="#collapse4">
                            <strong>4. Final Pageant Score (Criteria + Judges) - Professional</strong>
                        </button>
                    </h6>
                </div>
                <div id="collapse4" class="collapse" data-parent="#formulaAccordion">
                    <div class="card-body">
                        <p><strong>Formula:</strong> <code>SUM(round_totals) / COUNT(round_totals)</code></p>
                        <p class="small">Sum of all weighted scores divided by number of rounds. Most professional pageant formula.</p>
                        <p class="small"><strong>Mathematical:</strong> Σ(Judge Weighted Scores) ÷ Number of Judges</p>
                        <p class="small">This combines weighted criteria scores across all judges.</p>
                        <button class="btn btn-sm btn-primary" onclick="useFormula('SUM(round_totals) / COUNT(round_totals)', 'Final Pageant Score - Professional formula (Criteria + Judges)')">
                            <i class="fas fa-copy"></i> Use This Formula
                        </button>
                    </div>
                </div>
            </div>

            <!-- Formula 5: Sum All Rounds -->
            <div class="card mb-2">
                <div class="card-header" id="heading5">
                    <h6 class="mb-0">
                        <button class="btn btn-link btn-sm text-left w-100" type="button" data-toggle="collapse" data-target="#collapse5">
                            <strong>5. Sum All Rounds (Simple Total)</strong>
                        </button>
                    </h6>
                </div>
                <div id="collapse5" class="collapse" data-parent="#formulaAccordion">
                    <div class="card-body">
                        <p><strong>Formula:</strong> <code>SUM(round_totals)</code></p>
                        <p class="small">Simple sum of all round totals. Used when all rounds have equal weight.</p>
                        <p class="small"><strong>Mathematical:</strong> Total₁ + Total₂ + ... + Totalₙ</p>
                        <button class="btn btn-sm btn-primary" onclick="useFormula('SUM(round_totals)', 'Sum All Rounds - Simple total formula')">
                            <i class="fas fa-copy"></i> Use This Formula
                        </button>
                    </div>
                </div>
            </div>

            <!-- Formula 6: Pre-Pageant + Final Combined -->
            <div class="card mb-2">
                <div class="card-header" id="heading6">
                    <h6 class="mb-0">
                        <button class="btn btn-link btn-sm text-left w-100" type="button" data-toggle="collapse" data-target="#collapse6">
                            <strong>6. Pre-Pageant + Final Combined (Pre-Pageant Scores Added to Final)</strong>
                        </button>
                    </h6>
                </div>
                <div id="collapse6" class="collapse" data-parent="#formulaAccordion">
                    <div class="card-body">
                        <p><strong>Formula:</strong> <code>SUM(level_totals)</code> or <code>level_totals[0] + level_totals[2]</code></p>
                        <p class="small">Adds pre-pageant scores to final scores. Select specific levels to combine.</p>
                        <p class="small"><strong>Mathematical:</strong> Pre-Pageant Total + Final Total</p>
                        <p class="small"><strong>Example:</strong> Pre-Pageant (Level 0): 900, Final (Level 2): 914 → Total: 1,814</p>
                        <p class="small"><strong>Setup:</strong> Select "Pre-Pageant" and "Final" levels in the level selection below.</p>
                        <button class="btn btn-sm btn-primary" onclick="useFormula('SUM(level_totals)', 'Pre-Pageant + Final - Combined scores from selected levels')">
                            <i class="fas fa-copy"></i> Use This Formula
                        </button>
                    </div>
                </div>
            </div>

            <!-- Formula 7: Tie-Breaker -->
            <div class="card mb-2">
                <div class="card-header" id="heading7">
                    <h6 class="mb-0">
                        <button class="btn btn-link btn-sm text-left w-100" type="button" data-toggle="collapse" data-target="#collapse7">
                            <strong>7. Tie-Breaker Formula (Highest Criterion Score)</strong>
                        </button>
                    </h6>
                </div>
                <div id="collapse7" class="collapse" data-parent="#formulaAccordion">
                    <div class="card-body">
                        <p><strong>Formula:</strong> <code>MAX(round_totals)</code> or <code>round_totals[0]</code> (for specific round)</p>
                        <p class="small">Uses highest criterion score (e.g., Q&A round) or specific round score to break ties.</p>
                        <p class="small"><strong>Mathematical:</strong> Highest Criterion Score or Judge Chairperson Score</p>
                        <p class="small"><strong>Example:</strong> If Q&A is round 0, use <code>round_totals[0]</code></p>
                        <button class="btn btn-sm btn-primary" onclick="useFormula('MAX(round_totals)', 'Tie-Breaker - Highest criterion score')">
                            <i class="fas fa-copy"></i> Use This Formula
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4">

        <h6>Available Variables:</h6>
        <ul class="small">
            <li><code>round_scores</code> - Array of all judge scores in selected rounds</li>
            <li><code>round_totals</code> - Array of total scores per round (sum of judge scores)</li>
            <li><code>round_averages</code> - Array of average scores per round (total ÷ judges)</li>
            <li><code>level_totals</code> - Array of total scores per level</li>
        </ul>

        <h6 class="mt-3">Available Functions:</h6>
        <div class="bg-light p-3 rounded small">
            <code>SUM(array)</code> - Sum all values<br>
            <code>AVG(array)</code> - Average of values<br>
            <code>COUNT(array)</code> - Count of items<br>
            <code>MAX(array)</code> - Maximum value<br>
            <code>MIN(array)</code> - Minimum value<br>
            <code>ROUND(value, decimals)</code> - Round to decimals
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0">Scoring Formulas</h5>
    </div>
    <div class="card-body">
        <div class="alert alert-info mb-3">
            <i class="fas fa-info-circle"></i> <strong>Default formulas (Tech Admin can change):</strong><br>
            <strong>Round:</strong> <code>AVG(round_scores)</code> — Average of all judge scores across selected rounds. Mathematical: (Score₁ + Score₂ + ... + Scoreₙ) ÷ n. Example: Scores: 90, 88, 92 → (90 + 88 + 92) ÷ 3 = 90.<br>
            <strong>Final:</strong> <code>SUM(round_totals)</code> — Sum of all round totals. You can edit or add formulas below.
        </div>
        <form method="POST" action="/tabulation/events/<?= $event['id'] ?>/reports/formula/save">
            <input type="hidden" name="csrf_token" value="<?= Session::get('csrf_token') ?>">
            <input type="hidden" name="formula_id" id="formula_id" value="">
            <input type="hidden" name="round_ids" id="round_ids" value="">
            <input type="hidden" name="level_ids" id="level_ids" value="">
            
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="formula_type" class="form-label">Formula Type</label>
                    <select class="form-select" id="formula_type" name="formula_type" required>
                        <option value="round">Round</option>
                        <option value="level">Level</option>
                        <option value="final">Final</option>
                    </select>
                </div>
                <div class="col-md-8">
                    <label for="formula_expression" class="form-label">Formula Expression</label>
                    <input type="text" class="form-control" id="formula_expression" name="formula_expression" 
                           placeholder="e.g., SUM(round_totals)" required>
                    <small class="form-text text-muted">
                        Use: SUM(), AVG(), COUNT(), MAX(), MIN() with round_totals, round_averages, level_totals
                    </small>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Select Rounds to Combine (Optional)</label>
                    <p class="small text-muted">Leave empty to include all rounds. Select specific rounds to combine.</p>
                    <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                        <?php 
                        $roundsByLevel = [];
                        foreach ($allRounds as $round) {
                            if (!isset($roundsByLevel[$round['level_id']])) {
                                $roundsByLevel[$round['level_id']] = [];
                            }
                            $roundsByLevel[$round['level_id']][] = $round;
                        }
                        foreach ($roundsByLevel as $levelId => $rounds): 
                            $level = array_filter($allLevels, function($l) use ($levelId) { return $l['id'] == $levelId; });
                            $level = reset($level);
                        ?>
                            <div class="mb-3">
                                <strong class="text-primary"><?= htmlspecialchars($level['name'] ?? 'Level') ?></strong>
                                <?php foreach ($rounds as $round): ?>
                                    <div class="form-check mt-2">
                                        <input class="form-check-input round-checkbox" type="checkbox" 
                                               value="<?= $round['id'] ?>" 
                                               id="round_<?= $round['id'] ?>"
                                               data-level="<?= $levelId ?>">
                                        <label class="form-check-label" for="round_<?= $round['id'] ?>">
                                            <?= htmlspecialchars($round['name']) ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" id="select_all_rounds">
                            <label class="form-check-label" for="select_all_rounds">
                                <strong>Select All Rounds</strong>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Select Levels to Combine (Optional)</label>
                    <p class="small text-muted">Only <strong>checked</strong> levels are used in the formula. To exclude Pre Pageant from the final score, check <strong>only</strong> the levels you want (e.g. Pageant only). Leave all unchecked to include all levels.</p>
                    <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                        <?php foreach ($allLevels as $level): ?>
                            <div class="form-check">
                                <input class="form-check-input level-checkbox" type="checkbox" 
                                       value="<?= $level['id'] ?>" 
                                       id="level_<?= $level['id'] ?>">
                                <label class="form-check-label" for="level_<?= $level['id'] ?>">
                                    <?= htmlspecialchars($level['name']) ?> 
                                    <small class="text-muted">(<?= $level['round_count'] ?> rounds)</small>
                                </label>
                            </div>
                        <?php endforeach; ?>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" id="select_all_levels">
                            <label class="form-check-label" for="select_all_levels">
                                <strong>Select All Levels</strong>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3" 
                          placeholder="Describe what this formula calculates"></textarea>
            </div>
            
            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                    <label class="form-check-label" for="is_active">
                        Active
                    </label>
                </div>
            </div>
            
            <button type="submit" class="btn btn-warning">
                <i class="fas fa-save"></i> Save Formula
            </button>
            <button type="button" class="btn btn-secondary" onclick="resetForm()">
                <i class="fas fa-times"></i> Cancel
            </button>
        </form>
    </div>
</div>

<?php if (!empty($formulas)): ?>
<div class="card mt-4">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0">Existing Formulas</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Type</th>
                        <th>Formula Expression</th>
                        <th>Description</th>
                        <th>Rounds/Levels</th>
                        <th>Status</th>
                        <th>Created By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($formulas as $formula): ?>
                    <tr>
                        <td><span class="badge bg-primary"><?= htmlspecialchars($formula['formula_type']) ?></span></td>
                        <td><code><?= htmlspecialchars($formula['formula_expression']) ?></code></td>
                        <td><?= htmlspecialchars($formula['description'] ?: '-') ?></td>
                        <td>
                            <?php 
                            $roundInfo = '';
                            if (!empty($formula['round_ids'])) {
                                try {
                                    $roundIds = json_decode($formula['round_ids'], true);
                                    if (is_array($roundIds) && !empty($roundIds)) {
                                        $roundInfo = count($roundIds) . ' round(s)';
                                    }
                                } catch (Exception $e) {}
                            }
                            if (!empty($formula['level_ids'])) {
                                try {
                                    $levelIds = json_decode($formula['level_ids'], true);
                                    if (is_array($levelIds) && !empty($levelIds)) {
                                        $roundInfo .= ($roundInfo ? ', ' : '') . count($levelIds) . ' level(s)';
                                    }
                                } catch (Exception $e) {}
                            }
                            if (empty($roundInfo)) {
                                $roundInfo = '<span class="text-muted">All rounds/levels</span>';
                            } else {
                                $roundInfo = '<span class="badge bg-info">' . htmlspecialchars($roundInfo) . '</span>';
                            }
                            echo $roundInfo;
                            ?>
                        </td>
                        <td>
                            <?php if ($formula['is_active']): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($formula['created_by_name'] ?? '-') ?></td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="editFormula(<?= htmlspecialchars(json_encode($formula)) ?>)">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <form method="POST" action="/tabulation/events/<?= (int)$event['id'] ?>/reports/formula/<?= (int)$formula['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Delete this formula? Reports will use the default calculation for this type until you add another formula.');">
                                <input type="hidden" name="csrf_token" value="<?= Session::getCSRFToken() ?>">
                                <button type="submit" class="btn btn-sm btn-danger" title="Delete formula">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
// Update hidden fields when checkboxes change
function updateRoundIds() {
    const selected = Array.from(document.querySelectorAll('.round-checkbox:checked')).map(cb => cb.value);
    document.getElementById('round_ids').value = JSON.stringify(selected);
}

function updateLevelIds() {
    const selected = Array.from(document.querySelectorAll('.level-checkbox:checked')).map(cb => cb.value);
    document.getElementById('level_ids').value = JSON.stringify(selected);
}

// Select all rounds
document.getElementById('select_all_rounds')?.addEventListener('change', function() {
    document.querySelectorAll('.round-checkbox').forEach(cb => {
        cb.checked = this.checked;
    });
    updateRoundIds();
});

// Select all levels
document.getElementById('select_all_levels')?.addEventListener('change', function() {
    document.querySelectorAll('.level-checkbox').forEach(cb => {
        cb.checked = this.checked;
    });
    updateLevelIds();
});

// Update on individual checkbox change
document.querySelectorAll('.round-checkbox').forEach(cb => {
    cb.addEventListener('change', updateRoundIds);
});

document.querySelectorAll('.level-checkbox').forEach(cb => {
    cb.addEventListener('change', updateLevelIds);
});

function editFormula(formula) {
    document.getElementById('formula_id').value = formula.id;
    document.getElementById('formula_type').value = formula.formula_type;
    document.getElementById('formula_expression').value = formula.formula_expression;
    document.getElementById('description').value = formula.description || '';
    document.getElementById('is_active').checked = formula.is_active == 1;
    
    // Restore round selections
    if (formula.round_ids) {
        try {
            const roundIds = JSON.parse(formula.round_ids);
            roundIds.forEach(roundId => {
                const cb = document.getElementById('round_' + roundId);
                if (cb) cb.checked = true;
            });
            updateRoundIds();
        } catch(e) {}
    }
    
    // Restore level selections
    if (formula.level_ids) {
        try {
            const levelIds = JSON.parse(formula.level_ids);
            levelIds.forEach(levelId => {
                const cb = document.getElementById('level_' + levelId);
                if (cb) cb.checked = true;
            });
            updateLevelIds();
        } catch(e) {}
    }
    
    // Scroll to form
    document.querySelector('form').scrollIntoView({ behavior: 'smooth' });
}

function resetForm() {
    document.getElementById('formula_id').value = '';
    document.getElementById('formula_type').value = 'round';
    document.getElementById('formula_expression').value = '';
    document.getElementById('description').value = '';
    document.getElementById('is_active').checked = true;
    document.querySelectorAll('.round-checkbox').forEach(cb => cb.checked = false);
    document.querySelectorAll('.level-checkbox').forEach(cb => cb.checked = false);
    document.getElementById('select_all_rounds').checked = false;
    document.getElementById('select_all_levels').checked = false;
    updateRoundIds();
    updateLevelIds();
}

// Use formula from accordion
function useFormula(formula, description) {
    document.getElementById('formula_expression').value = formula;
    document.getElementById('description').value = description;
    document.querySelector('form').scrollIntoView({ behavior: 'smooth' });
}

// Initialize
updateRoundIds();
updateLevelIds();
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
