<?php 
$title = 'Edit Event';
require __DIR__ . '/../layout/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-pencil"></i> Edit Event</h1>
    <a href="/tabulation/events/<?= $event['id'] ?>" class="btn btn-secondary">Back</a>
</div>

<div class="card">
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
        
        <form method="POST" action="/tabulation/events/<?= $event['id'] ?>/update">
            <input type="hidden" name="csrf_token" value="<?= Session::getCSRFToken() ?>">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Event Name *</label>
                    <input type="text" class="form-control" id="name" name="name" 
                           value="<?= htmlspecialchars($event['name']) ?>" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="event_type" class="form-label">Event Type *</label>
                    <select class="form-select" id="event_type" name="event_type" required>
                        <option value="Pageant" <?= $event['event_type'] === 'Pageant' ? 'selected' : '' ?>>Pageant</option>
                        <option value="Quiz Bee" <?= $event['event_type'] === 'Quiz Bee' ? 'selected' : '' ?>>Quiz Bee</option>
                        <option value="Hackathon" <?= $event['event_type'] === 'Hackathon' ? 'selected' : '' ?>>Hackathon</option>
                        <option value="Sports" <?= $event['event_type'] === 'Sports' ? 'selected' : '' ?>>Sports</option>
                        <option value="Talent Show" <?= $event['event_type'] === 'Talent Show' ? 'selected' : '' ?>>Talent Show</option>
                        <option value="Other" <?= $event['event_type'] === 'Other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($event['description'] ?? '') ?></textarea>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="event_date" class="form-label">Event Date *</label>
                    <input type="date" class="form-control" id="event_date" name="event_date" 
                           value="<?= htmlspecialchars($event['event_date']) ?>" required>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="start_time" class="form-label">Start Time</label>
                    <input type="time" class="form-control" id="start_time" name="start_time" 
                           value="<?= htmlspecialchars($event['start_time'] ?? '') ?>">
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="end_time" class="form-label">End Time</label>
                    <input type="time" class="form-control" id="end_time" name="end_time" 
                           value="<?= htmlspecialchars($event['end_time'] ?? '') ?>">
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="venue" class="form-label">Venue</label>
                    <input type="text" class="form-control" id="venue" name="venue" 
                           value="<?= htmlspecialchars($event['venue'] ?? '') ?>">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="Draft" <?= $event['status'] === 'Draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="Ongoing" <?= $event['status'] === 'Ongoing' ? 'selected' : '' ?>>Ongoing</option>
                        <option value="Finished" <?= $event['status'] === 'Finished' ? 'selected' : '' ?>>Finished</option>
                    </select>
                </div>
            </div>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="/tabulation/events/<?= $event['id'] ?>" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Update Event
                </button>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>



