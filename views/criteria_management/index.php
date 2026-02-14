<?php 
$title = 'Criteria Management';
require __DIR__ . '/../layout/header.php'; 
?>

<div class="row">
    <div class="col-md-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list-check mr-1"></i>
                    <strong>Criteria Management</strong>
                </h3>
                <div class="card-tools">
                    <?php if ($selectedEvent): ?>
                    <a href="/tabulation/criteria-management/create?event_id=<?= $selectedEvent['id'] ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add New Criteria
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <!-- Event Selection -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="info-box bg-info">
                            <span class="info-box-icon"><i class="fas fa-info-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Step 1: Select an Event</span>
                                <span class="info-box-number">Choose the event to manage criteria for</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <form method="GET" action="/tabulation/criteria-management" class="mb-4">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="event_id">
                                    <i class="fas fa-calendar-alt"></i> Select Event
                                </label>
                                <select name="event_id" id="event_id" class="form-control form-control-lg" onchange="this.form.submit()">
                                    <option value="">-- Choose an Event --</option>
                                    <?php foreach ($events as $event): ?>
                                        <option value="<?= $event['id'] ?>" 
                                                <?= ($selectedEvent && $selectedEvent['id'] == $event['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($event['name']) ?> 
                                            (<?= $event['criteria_count'] ?> criteria)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="form-text text-muted">
                                    <i class="fas fa-lightbulb"></i> Select an event to view and manage its scoring criteria
                                </small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary btn-lg btn-block">
                                        <i class="fas fa-search"></i> Load Criteria
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                
                <?php if ($selectedEvent): ?>
                <!-- Selected Event Info -->
                <div class="alert alert-info">
                    <h5><i class="icon fas fa-info"></i> Managing Criteria for: <strong><?= htmlspecialchars($selectedEvent['name']) ?></strong></h5>
                    <p class="mb-0">
                        <i class="fas fa-question-circle"></i> 
                        <strong>What are Criteria?</strong> Criteria are the different aspects judges will score contestants on. 
                        For example: "Creativity", "Presentation", "Content", etc. Each criterion has a maximum score.
                    </p>
                </div>
                
                <!-- Criteria List -->
                <?php if (empty($criteria)): ?>
                <div class="alert alert-warning">
                    <h5><i class="icon fas fa-exclamation-triangle"></i> No Criteria Found</h5>
                    <p>This event doesn't have any criteria yet. Click "Add New Criteria" to get started!</p>
                    <a href="/tabulation/criteria-management/create?event_id=<?= $selectedEvent['id'] ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Your First Criterion
                    </a>
                </div>
                <?php else: ?>
                <div class="card card-success card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-list"></i> Current Criteria (<?= count($criteria) ?>)
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 30%">Criterion Name</th>
                                    <th style="width: 15%">Max Score</th>
                                    <th style="width: 15%">Category</th>
                                    <th style="width: 30%">Description</th>
                                    <th style="width: 10%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $groupedCriteria = [];
                                foreach ($criteria as $c) {
                                    $cat = $c['category'] ?: 'General';
                                    if (!isset($groupedCriteria[$cat])) {
                                        $groupedCriteria[$cat] = [];
                                    }
                                    $groupedCriteria[$cat][] = $c;
                                }
                                ?>
                                <?php foreach ($groupedCriteria as $category => $items): ?>
                                    <?php if ($category !== 'General'): ?>
                                    <tr class="table-secondary">
                                        <td colspan="5">
                                            <strong><i class="fas fa-folder"></i> <?= htmlspecialchars($category) ?></strong>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php foreach ($items as $criterion): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($criterion['name']) ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge badge-primary badge-lg">
                                                <?= number_format($criterion['max_score'], 2) ?> pts
                                            </span>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($criterion['category'] ?: '-') ?>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?= htmlspecialchars($criterion['description'] ?: 'No description') ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="/tabulation/criteria-management/edit/<?= $criterion['id'] ?>" 
                                                   class="btn btn-sm btn-warning" title="Edit Criterion">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <form method="POST" 
                                                      action="/tabulation/criteria-management/delete/<?= $criterion['id'] ?>" 
                                                      style="display: inline;"
                                                      onsubmit="confirmDelete(event, 'This cannot be undone if it\'s already being used in rounds.', 'Delete Criterion?'); return false;">
                                                    <input type="hidden" name="csrf_token" value="<?= Session::getCSRFToken() ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete Criterion">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Help Box -->
                <div class="card card-info collapsed-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-question-circle"></i> Need Help?
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5><i class="fas fa-lightbulb"></i> Tips for Setting Up Criteria:</h5>
                        <ul>
                            <li><strong>Name:</strong> Use clear, descriptive names like "Creativity", "Presentation", or "Technical Skills"</li>
                            <li><strong>Max Score:</strong> This is the highest score a judge can give. Common values are 10, 25, 50, or 100</li>
                            <li><strong>Category:</strong> Group related criteria together (optional). Example: "Performance", "Appearance", "Content"</li>
                            <li><strong>Description:</strong> Explain what judges should look for when scoring this criterion</li>
                        </ul>
                        <h5 class="mt-3"><i class="fas fa-exclamation-triangle"></i> Important:</h5>
                        <ul>
                            <li>After creating criteria, you'll need to assign them to rounds and set weights</li>
                            <li>Don't delete criteria that are already being used in active rounds</li>
                            <li>You can edit criteria at any time, but be careful with max_score changes</li>
                        </ul>
                    </div>
                </div>
                <?php endif; ?>
                <?php else: ?>
                <div class="alert alert-warning">
                    <h5><i class="icon fas fa-exclamation-triangle"></i> No Event Selected</h5>
                    <p>Please select an event from the dropdown above to manage criteria.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>



