<?php 
$title = 'Edit Criteria';
require __DIR__ . '/../layout/header.php'; 
?>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-edit"></i> Edit Scoring Criterion
                </h3>
                <div class="card-tools">
                    <a href="/tabulation/criteria-management?event_id=<?= $criterion['event_id'] ?>" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>
            <form method="POST" action="/tabulation/criteria-management/update/<?= $criterion['id'] ?>">
                <input type="hidden" name="csrf_token" value="<?= Session::getCSRFToken() ?>">
                
                <div class="card-body">
                    <div class="alert alert-info">
                        <h5><i class="icon fas fa-info"></i> Editing Criteria for: <strong><?= htmlspecialchars($criterion['event_name']) ?></strong></h5>
                    </div>
                    
                    <div class="form-group">
                        <label for="name">
                            <i class="fas fa-tag"></i> Criterion Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control form-control-lg" 
                               id="name" 
                               name="name" 
                               value="<?= htmlspecialchars($criterion['name']) ?>"
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="max_score">
                            <i class="fas fa-star"></i> Maximum Score <span class="text-danger">*</span>
                        </label>
                        <div class="input-group input-group-lg">
                            <input type="number" 
                                   class="form-control" 
                                   id="max_score" 
                                   name="max_score" 
                                   value="<?= htmlspecialchars($criterion['max_score']) ?>"
                                   min="1" 
                                   step="0.01"
                                   required>
                            <div class="input-group-append">
                                <span class="input-group-text">points</span>
                            </div>
                        </div>
                        <small class="form-text text-warning">
                            <i class="fas fa-exclamation-triangle"></i> 
                            <strong>Warning:</strong> Changing the max score may affect existing scores. Use with caution!
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label for="category">
                            <i class="fas fa-folder"></i> Category <span class="text-muted">(Optional)</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="category" 
                               name="category" 
                               value="<?= htmlspecialchars($criterion['category'] ?? '') ?>"
                               placeholder="e.g., Performance, Appearance, Content">
                    </div>
                    
                    <div class="form-group">
                        <label for="description">
                            <i class="fas fa-align-left"></i> Description <span class="text-muted">(Optional)</span>
                        </label>
                        <textarea class="form-control" 
                                  id="description" 
                                  name="description" 
                                  rows="4"><?= htmlspecialchars($criterion['description'] ?? '') ?></textarea>
                    </div>
                </div>
                
                <div class="card-footer">
                    <button type="submit" class="btn btn-warning btn-lg">
                        <i class="fas fa-save"></i> Update Criterion
                    </button>
                    <a href="/tabulation/criteria-management?event_id=<?= $criterion['event_id'] ?>" class="btn btn-secondary btn-lg">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>



