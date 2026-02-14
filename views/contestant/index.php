<?php 
$title = 'Contestants';
require __DIR__ . '/../layout/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-person-badge"></i> Contestants: <?= htmlspecialchars($event['name']) ?></h1>
    <a href="/tabulation/events/<?= $event['id'] ?>" class="btn btn-secondary">Back</a>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Add Contestant</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="/tabulation/events/<?= $event['id'] ?>/contestants/store">
            <input type="hidden" name="csrf_token" value="<?= Session::getCSRFToken() ?>">
            <div class="row">
                <div class="col-md-2 mb-2">
                    <input type="text" class="form-control" name="contestant_number" placeholder="Number *" required>
                </div>
                <div class="col-md-3 mb-2">
                    <input type="text" class="form-control" name="name" placeholder="Name *" required>
                </div>
                <div class="col-md-2 mb-2">
                    <input type="text" class="form-control" name="team_name" placeholder="Team">
                </div>
                <div class="col-md-2 mb-2">
                    <input type="text" class="form-control" name="category" placeholder="Category">
                </div>
                <div class="col-md-3 mb-2">
                    <button type="submit" class="btn btn-primary w-100">Add</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-file-upload"></i> Import from CSV</h5>
    </div>
    <div class="card-body">
        <div class="alert alert-info mb-3">
            <h6><i class="fas fa-info-circle"></i> CSV Import Instructions</h6>
            <p class="mb-2">
                <strong>CSV Format:</strong> contestant_number, name, team_name, category, bio
            </p>
            <ul class="mb-0">
                <li><strong>Required fields:</strong> contestant_number, name</li>
                <li><strong>Optional fields:</strong> team_name, category, bio</li>
                <li>First row must be the header row</li>
                <li>Duplicate contestant numbers will be skipped</li>
            </ul>
        </div>
        
        <div class="mb-3">
            <a href="/tabulation/contestants/download-template" class="btn btn-info">
                <i class="fas fa-download"></i> Download CSV Template
            </a>
        </div>
        
        <form method="POST" action="/tabulation/events/<?= $event['id'] ?>/contestants/import" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= Session::getCSRFToken() ?>">
            <div class="row">
                <div class="col-md-8">
                    <label for="csv_file" class="form-label">Select CSV File</label>
                    <input type="file" class="form-control" name="csv_file" id="csv_file" accept=".csv" required>
                    <small class="form-text text-muted">
                        <i class="fas fa-file-csv"></i> Upload a CSV file with contestant data
                    </small>
                </div>
                <div class="col-md-4">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-success w-100 btn-lg">
                        <i class="fas fa-upload"></i> Import Contestants
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Team</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contestants as $contestant): ?>
                    <tr>
                        <td><strong>#<?= htmlspecialchars($contestant['contestant_number']) ?></strong></td>
                        <td><?= htmlspecialchars($contestant['name']) ?></td>
                        <td><?= htmlspecialchars($contestant['team_name'] ?: '-') ?></td>
                        <td><?= htmlspecialchars($contestant['category'] ?: '-') ?></td>
                        <td>
                            <span class="badge bg-<?= $contestant['status'] === 'Active' ? 'success' : 'secondary' ?>">
                                <?= htmlspecialchars($contestant['status']) ?>
                            </span>
                        </td>
                        <td>
                            <a href="/tabulation/events/<?= $event['id'] ?>/contestants/<?= $contestant['id'] ?>/edit" 
                               class="btn btn-sm btn-secondary">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>



