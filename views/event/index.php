<?php 
$title = 'Events';
require __DIR__ . '/../layout/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-calendar-event"></i> Events</h1>
    <a href="/tabulation/events/create" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Create Event
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Date</th>
                        <th>Contestants</th>
                        <th>Judges</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($events)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted">No events found</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($events as $event): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($event['name']) ?></strong></td>
                        <td><?= htmlspecialchars($event['event_type']) ?></td>
                        <td><?= htmlspecialchars($event['event_date']) ?></td>
                        <td><span class="badge bg-info"><?= $event['contestant_count'] ?></span></td>
                        <td><span class="badge bg-info"><?= $event['judge_count'] ?></span></td>
                        <td>
                            <span class="badge bg-<?= $event['status'] === 'Ongoing' ? 'success' : ($event['status'] === 'Finished' ? 'secondary' : 'warning') ?>">
                                <?= htmlspecialchars($event['status']) ?>
                            </span>
                        </td>
                        <td>
                            <a href="/tabulation/events/<?= $event['id'] ?>" class="btn btn-sm btn-primary">
                                <i class="bi bi-eye"></i> View
                            </a>
                            <a href="/tabulation/events/<?= $event['id'] ?>/edit" class="btn btn-sm btn-secondary">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>



