<?php 
$title = 'Live Lineup';
require __DIR__ . '/../layout/header.php'; 
?>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-users"></i> Live Contestant Lineup
                </h3>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h5><i class="icon fas fa-info"></i> Select an Event</h5>
                    <p>Choose an event to display the live lineup of contestants. Perfect for projection screens during events!</p>
                </div>
                
                <?php if (empty($events)): ?>
                    <div class="alert alert-warning">
                        <h5><i class="icon fas fa-exclamation-triangle"></i> No Events Found</h5>
                        <p>No events available. Please create an event first.</p>
                        <a href="/tabulation/events/create" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Create Event
                        </a>
                    </div>
                <?php else: ?>
                    <form method="GET" action="/tabulation/display/lineup">
                        <div class="form-group">
                            <label for="event_id">
                                <i class="fas fa-calendar-alt"></i> Select Event
                            </label>
                            <select name="event_id" id="event_id" class="form-control form-control-lg" required>
                                <option value="">-- Choose an Event --</option>
                                <?php foreach ($events as $event): ?>
                                    <option value="<?= $event['id'] ?>">
                                        <?= htmlspecialchars($event['name']) ?> 
                                        (<?= htmlspecialchars($event['event_date']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-lg btn-block">
                                <i class="fas fa-tv"></i> Display Lineup
                            </button>
                        </div>
                    </form>
                    
                    <div class="card card-info collapsed-card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-question-circle"></i> How to Use
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <h5><i class="fas fa-lightbulb"></i> Tips:</h5>
                            <ul>
                                <li>Select an event and click "Display Lineup"</li>
                                <li>Use the fullscreen button (F11) for projection</li>
                                <li>Toggle between Grid and List view</li>
                                <li>Page auto-refreshes every 30 seconds</li>
                                <li>Press <kbd>F</kbd> for fullscreen, <kbd>G</kbd> for grid, <kbd>L</kbd> for list</li>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>



