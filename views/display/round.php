<?php 
$title = 'Results: ' . $round['name'];
require __DIR__ . '/../layout/header.php'; 
?>

<div class="container-fluid py-5">
    <div class="text-center mb-5">
        <h1 class="display-4"><i class="bi bi-trophy-fill"></i> <?= htmlspecialchars($round['event_name']) ?></h1>
        <h2 class="text-muted"><?= htmlspecialchars($round['level_name']) ?> - <?= htmlspecialchars($round['name']) ?></h2>
    </div>
    
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0 text-center"><i class="bi bi-trophy"></i> Final Rankings</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 80px;" class="text-center">Rank</th>
                                    <th style="width: 100px;">#</th>
                                    <th>Name</th>
                                    <th>Team</th>
                                    <th class="text-center" style="width: 150px;">Total Score</th>
                                    <th class="text-center" style="width: 150px;">Average</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rankings as $index => $ranking): ?>
                                <tr class="<?= $ranking['rank'] <= 3 ? 'table-warning' : '' ?>">
                                    <td class="text-center">
                                        <?php if ($ranking['rank'] == 1): ?>
                                            <h2 class="mb-0"><i class="bi bi-trophy-fill text-warning"></i></h2>
                                        <?php elseif ($ranking['rank'] == 2): ?>
                                            <h2 class="mb-0"><i class="bi bi-trophy-fill text-secondary"></i></h2>
                                        <?php elseif ($ranking['rank'] == 3): ?>
                                            <h2 class="mb-0"><i class="bi bi-trophy-fill" style="color: #CD7F32;"></i></h2>
                                        <?php else: ?>
                                            <h4 class="mb-0"><?= $ranking['rank'] ?></h4>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong>#<?= htmlspecialchars($ranking['contestant']['contestant_number']) ?></strong></td>
                                    <td><strong><?= htmlspecialchars($ranking['contestant']['name']) ?></strong></td>
                                    <td><?= htmlspecialchars($ranking['contestant']['team_name'] ?: '-') ?></td>
                                    <td class="text-center">
                                        <h4 class="mb-0"><strong><?= number_format($ranking['total_score'], 3) ?></strong></h4>
                                    </td>
                                    <td class="text-center">
                                        <strong><?= number_format($ranking['average_score'], 3) ?></strong>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
body {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
}
</style>

<script>
// Auto-refresh every 30 seconds
setInterval(function() {
    location.reload();
}, 30000);
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>



