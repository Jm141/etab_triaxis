<?php 
$title = 'Add New Criteria';
require __DIR__ . '/../layout/header.php'; 
?>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-plus-circle"></i> Add New Scoring Criterion
                </h3>
                <div class="card-tools">
                    <a href="/tabulation/criteria-management?event_id=<?= $event['id'] ?>" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>
            <form method="POST" action="/tabulation/criteria-management/store">
                <input type="hidden" name="csrf_token" value="<?= Session::getCSRFToken() ?>">
                <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                
                <div class="card-body">
                    <div class="alert alert-info">
                        <h5><i class="icon fas fa-info"></i> Adding Criteria for: <strong><?= htmlspecialchars($event['name']) ?></strong></h5>
                        <p class="mb-0">A criterion is what judges will score contestants on. Examples: "Creativity", "Presentation", "Content Quality"</p>
                    </div>
                    
                    <div class="form-group">
                        <label for="name">
                            <i class="fas fa-tag"></i> Criterion Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control form-control-lg" 
                               id="name" 
                               name="name" 
                               placeholder="e.g., Creativity, Presentation, Technical Skills"
                               required>
                        <small class="form-text text-muted">
                            <i class="fas fa-lightbulb"></i> Use a clear, descriptive name that judges will understand
                        </small>
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
                                   placeholder="100" 
                                   min="1" 
                                   step="0.01"
                                   required>
                            <div class="input-group-append">
                                <span class="input-group-text">points</span>
                            </div>
                        </div>
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle"></i> This is the highest score a judge can give for this criterion. 
                            Common values: 10, 25, 50, or 100 points
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
                               placeholder="e.g., Performance, Appearance, Content"
                               list="category-suggestions">
                        <datalist id="category-suggestions">
                            <option value="Performance">
                            <option value="Appearance">
                            <option value="Content">
                            <option value="Technical">
                            <option value="Creativity">
                        </datalist>
                        <small class="form-text text-muted">
                            <i class="fas fa-lightbulb"></i> Group related criteria together for better organization
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">
                            <i class="fas fa-align-left"></i> Description <span class="text-muted">(Optional)</span>
                        </label>
                        <textarea class="form-control" 
                                  id="description" 
                                  name="description" 
                                  rows="4"
                                  placeholder="Describe what judges should look for when scoring this criterion..."></textarea>
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle"></i> Help judges understand what to evaluate. 
                            Example: "Assess the originality and innovation demonstrated in the presentation"
                        </small>
                    </div>
                    
                    <!-- Example Box -->
                    <div class="card card-info collapsed-card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-question-circle"></i> Need an Example?
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <p><strong>Example Criterion:</strong></p>
                            <ul>
                                <li><strong>Name:</strong> "Creativity"</li>
                                <li><strong>Max Score:</strong> 25</li>
                                <li><strong>Category:</strong> "Performance"</li>
                                <li><strong>Description:</strong> "Evaluate the originality, innovation, and creative approach demonstrated by the contestant"</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save"></i> Save Criterion
                    </button>
                    <a href="/tabulation/criteria-management?event_id=<?= $event['id'] ?>" class="btn btn-secondary btn-lg">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>



