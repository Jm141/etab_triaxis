<?php 
$title = 'Add Contestants - MR & MISS Mode';
require __DIR__ . '/../layout/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-person-badge"></i> Add Contestants: <?= htmlspecialchars($event['name']) ?> (MR & MISS Mode)</h1>
    <a href="/tabulation/events/<?= $event['id'] ?>/contestants" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Standard View
    </a>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="bi bi-people"></i> 
            Add Contestants - MR & MISS Mode
            <small class="text-muted">(Create paired contestants)</small>
        </h5>
    </div>
    <div class="card-body">
        <?php if (isset($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="/tabulation/events/<?= $event['id'] ?>/contestants/store-mr-miss">
            <input type="hidden" name="csrf_token" value="<?= Session::getCSRFToken() ?>">
            
            <!-- Standard Contestant Fields -->
            <div class="row mb-3">
                <div class="col-md-2 mb-2">
                    <label for="contestant_number" class="form-label">Contestant Number *</label>
                    <input type="number" class="form-control" id="contestant_number" name="contestant_number" min="1" required>
                </div>
                <div class="col-md-3 mb-2">
                    <label for="team_name" class="form-label">Team</label>
                    <input type="text" class="form-control" id="team_name" name="team_name" placeholder="Optional">
                </div>
                <div class="col-md-2 mb-2">
                    <label for="category" class="form-label">Category</label>
                    <input type="text" class="form-control" id="category" name="category" placeholder="Optional">
                </div>
                <div class="col-md-3 mb-2">
                    <label for="bio" class="form-label">Bio</label>
                    <textarea class="form-control" id="bio" name="bio" rows="3" placeholder="Optional"></textarea>
                </div>
            </div>
            
            <!-- MR & MISS Specific Fields -->
            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0">
                                <i class="bi bi-people"></i> 
                                MR & MISS Contestant Pair
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="female_name" class="form-label">Female Name *</label>
                                    <input type="text" class="form-control" id="female_name" name="female_name" required>
                                    <small class="form-text text-muted">e.g., Miss Sarah Johnson</small>
                                </div>
                                <div class="col-md-6">
                                    <label for="male_name" class="form-label">Male Name *</label>
                                    <input type="text" class="form-control" id="male_name" name="male_name" required>
                                    <small class="form-text text-muted">e.g., Mr Michael Chen</small>
                                </div>
                            </div>
                            
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label for="team_female" class="form-label">Team (Female)</label>
                                    <input type="text" class="form-control" id="team_female" name="team_female" placeholder="Optional">
                                </div>
                                <div class="col-md-6">
                                    <label for="team_male" class="form-label">Team (Male)</label>
                                    <input type="text" class="form-control" id="team_male" name="team_male" placeholder="Optional">
                                </div>
                            </div>
                            
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label for="category_female" class="form-label">Category (Female)</label>
                                    <input type="text" class="form-control" id="category_female" name="category_female" placeholder="Optional">
                                </div>
                                <div class="col-md-6">
                                    <label for="category_male" class="form-label">Category (Male)</label>
                                    <input type="text" class="form-control" id="category_male" name="category_male" placeholder="Optional">
                                </div>
                            </div>
                            
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label for="bio_female" class="form-label">Bio (Female)</label>
                                    <textarea class="form-control" id="bio_female" name="bio_female" rows="3" placeholder="Optional"></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label for="bio_male" class="form-label">Bio (Male)</label>
                                    <textarea class="form-control" id="bio_male" name="bio_male" rows="3" placeholder="Optional"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                <a href="/tabulation/events/<?= $event['id'] ?>/contestants" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Standard View
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Add MR & MISS Pair
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="bi bi-download"></i> 
            Import Options
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6>Standard CSV Import</h6>
                <p class="text-muted">Use the standard template for individual contestants.</p>
                <a href="/tabulation/templates/contestants_import_template.csv" class="btn btn-outline-primary" download>
                    <i class="bi bi-download"></i> Download Standard Template
                </a>
            </div>
            
            <div class="col-md-6">
                <h6>MR & MISS CSV Import</h6>
                <p class="text-muted">Use the enhanced template for paired contestants.</p>
                <a href="/tabulation/templates/contestants_mr_miss_import_template.csv" class="btn btn-primary" download>
                    <i class="bi bi-download"></i> Download MR & MISS Template
                </a>
            </div>
        </div>
        
        <div class="mt-4">
            <h6>Instructions</h6>
            <div class="alert alert-info">
                <h6><i class="bi bi-info-circle"></i> MR & MISS Contestant Entry</h6>
                <ul class="mb-2">
                    <li><strong>Contestant Number:</strong> Same number for both Female and Male contestants</li>
                    <li><strong>Naming Convention:</strong> Use "Miss [Name]" for Female, "Mr [Name]" for Male</li>
                    <li><strong>Gender Detection:</strong> System automatically detects gender from names</li>
                    <li><strong>Scoring Interface:</strong> Shows grouped layout with gender-separated rankings</li>
                    <li><strong>Reporting:</strong> Provides dual winners (Male & Female) plus overall rankings</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
function addMRMissPair() {
    const femaleName = document.getElementById('female_name').value.trim();
    const maleName = document.getElementById('male_name').value.trim();
    const contestantNumber = document.getElementById('contestant_number').value.trim();
    
    if (!femaleName || !maleName || !contestantNumber) {
        alert('Please fill in all required fields for the MR & MISS pair.');
        return;
    }
    
    // Create two contestants with the same number
    const form = document.querySelector('form');
    const formData = new FormData(form);
    
    // Add female contestant
    const femaleData = new FormData();
    femaleData.append('contestant_number', contestantNumber);
    femaleData.append('name', femaleName);
    femaleData.append('team_name', document.getElementById('team_female').value);
    femaleData.append('category', document.getElementById('category_female').value);
    femaleData.append('bio', document.getElementById('bio_female').value);
    femaleData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);
    
    // Add male contestant
    const maleData = new FormData();
    maleData.append('contestant_number', contestantNumber);
    maleData.append('name', maleName);
    maleData.append('team_name', document.getElementById('team_male').value);
    maleData.append('category', document.getElementById('category_male').value);
    maleData.append('bio', document.getElementById('bio_male').value);
    maleData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);
    
    // Send both contestants
    Promise.all([
        fetch('/tabulation/events/<?= $event['id'] ?>/contestants/store', {
            method: 'POST',
            body: femaleData
        }),
        fetch('/tabulation/events/<?= $event['id'] ?>/contestants/store', {
            method: 'POST',
            body: maleData
        })
    ]).then(responses => Promise.all(responses.map(r => r.json())))
    .then(results => {
        if (results.every(r => r.success)) {
            alert('MR & MISS pair added successfully!');
            // Clear form fields
            document.getElementById('female_name').value = '';
            document.getElementById('male_name').value = '';
            document.getElementById('team_female').value = '';
            document.getElementById('team_male').value = '';
            document.getElementById('category_female').value = '';
            document.getElementById('category_male').value = '';
            document.getElementById('bio_female').value = '';
            document.getElementById('bio_male').value = '';
            document.getElementById('contestant_number').value = '';
        } else {
            const errors = results.map(r => r.success ? null : r.message).filter(Boolean);
            alert('Errors: ' + errors.join(', '));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while adding contestants.');
    });
}
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
