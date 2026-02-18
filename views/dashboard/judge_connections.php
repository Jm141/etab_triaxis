<?php 
$title = 'Judge Connections Monitor';
require __DIR__ . '/../layout/header.php'; 
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
    <h1 class="mb-3 mb-md-0"><i class="fas fa-users-cog"></i> <span class="d-none d-sm-inline">Judge Connections Monitor</span></h1>
    <div class="d-flex flex-column flex-sm-row gap-2 w-100 w-md-auto">
        <button type="button" class="btn btn-success btn-sm btn-block btn-md-inline" onclick="refreshConnections()">
            <i class="fas fa-sync-alt"></i> <span class="d-none d-sm-inline">Refresh Now</span><span class="d-sm-none">Refresh</span>
        </button>
        <a href="/tabulation/dashboard" class="btn btn-secondary btn-sm btn-block btn-md-inline">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Auto Refresh Info -->
        <div class="alert alert-info py-2">
            <i class="fas fa-info-circle"></i> This page automatically refreshes every 4 seconds. Offline if no heartbeat for 8 seconds. Last updated: <span id="last-updated"><?= date('Y-m-d H:i:s') ?></span>
        </div>

        <!-- Excel-style sheet area: tabs above, one panel visible at a time -->
        <div class="level-score-sheet-wrapper card border">
            <!-- Category tabs above the sheet -->
            <div class="level-score-tabs">
                <div class="tabs-scroll-arrows tabs-left" title="Previous tabs"><i class="fas fa-chevron-left"></i></div>
                <div class="tabs-container">
                    <button type="button" class="score-sheet-tab cat-color-0 active" data-panel-id="panel-all" data-status="all">
                        All Judges
                    </button>
                    <button type="button" class="score-sheet-tab cat-color-1" data-panel-id="panel-online" data-status="online">
                        Online Only
                    </button>
                    <button type="button" class="score-sheet-tab cat-color-2" data-panel-id="panel-offline" data-status="offline">
                        Offline Only
                    </button>
                    <button type="button" class="score-sheet-tab cat-color-3" data-panel-id="panel-stats" data-status="stats">
                        Statistics
                    </button>
                </div>
                <div class="tabs-scroll-arrows tabs-right" title="Next tabs"><i class="fas fa-chevron-right"></i></div>
            </div>
            <div class="card-body p-0 position-relative" style="min-height: 320px;">
                <!-- All Judges Panel -->
                <div id="panel-all" class="score-sheet-panel cat-color-0" data-status="all">
                    <?php if (empty($judgeConnections)): ?>
                        <div class="p-4 text-muted">No judges assigned to ongoing events found.</div>
                    <?php else: ?>
                        <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                            <table class="table table-bordered table-hover table-sm scoring-table-level mb-0">
                                <thead class="thead-dark sticky-top thead-cat-accent" style="background-color: #37474f; z-index: 10;">
                                    <tr>
                                        <th class="align-middle text-center" style="min-width: 100px; position: sticky; left: 0; background-color: #37474f; z-index: 11;"><strong>Status</strong></th>
                                        <th class="text-center criteria-header-cell">
                                            <div class="criteria-header-inner">
                                                <strong>Judge Name</strong>
                                            </div>
                                        </th>
                                        <th class="text-center criteria-header-cell">
                                            <div class="criteria-header-inner">
                                                <strong>Username</strong>
                                            </div>
                                        </th>
                                        <th class="text-center criteria-header-cell">
                                            <div class="criteria-header-inner">
                                                <strong>Email</strong>
                                            </div>
                                        </th>
                                        <th class="text-center criteria-header-cell">
                                            <div class="criteria-header-inner">
                                                <strong>Event</strong>
                                            </div>
                                        </th>
                                        <th class="text-center criteria-header-cell">
                                            <div class="criteria-header-inner">
                                                <strong>Judge #</strong>
                                            </div>
                                        </th>
                                        <th class="text-center criteria-header-cell">
                                            <div class="criteria-header-inner">
                                                <strong>Assigned Rounds</strong>
                                            </div>
                                        </th>
                                        <th class="text-center criteria-header-cell">
                                            <div class="criteria-header-inner">
                                                <strong>Last Heartbeat</strong>
                                            </div>
                                        </th>
                                        <th class="align-middle text-center" style="min-width: 100px;"><strong>Actions</strong></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($judgeConnections as $connection): ?>
                                        <tr data-contestant-id="<?= $connection['judge']['id'] ?>" class="<?= $connection['is_active'] ? '' : 'table-secondary' ?>">
                                            <td class="text-center font-weight-bold" style="position: sticky; left: 0; background-color: inherit; z-index: 1;">
                                                <?php if ($connection['is_active']): ?>
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-circle"></i> Online
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-danger">
                                                        <i class="fas fa-circle"></i> Offline
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <strong><?= htmlspecialchars($connection['judge']['full_name']) ?></strong>
                                            </td>
                                            <td class="text-center"><?= htmlspecialchars($connection['judge']['username']) ?></td>
                                            <td class="text-center"><?= htmlspecialchars($connection['judge']['email']) ?></td>
                                            <td class="text-center">
                                                <a href="/tabulation/events/<?= $connection['event']['id'] ?>" class="text-primary">
                                                    <?= htmlspecialchars($connection['event']['name']) ?>
                                                </a>
                                                <br>
                                                <small class="text-muted"><?= htmlspecialchars($connection['event']['event_type']) ?></small>
                                            </td>
                                            <td class="text-center">
                                                <?= $connection['judge']['judge_number'] ? 'Judge #' . htmlspecialchars($connection['judge']['judge_number']) : '-' ?>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-info">
                                                    <?= $connection['judge']['assigned_rounds'] ?> rounds
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <?php if (!empty($connection['last_activity'])): ?>
                                                    <small class="text-muted">
                                                        <?= date('Y-m-d H:i:s', $connection['last_activity']) ?>
                                                    </small>
                                                    <br>
                                                    <?php if ($connection['is_active']): ?>
                                                        <small class="text-success">Online</small>
                                                    <?php else: ?>
                                                        <small class="text-danger">Offline</small>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <small class="text-muted">Never</small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">-</span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Online Only Panel -->
                <div id="panel-online" class="score-sheet-panel cat-color-1" data-status="online" style="display:none;">
                    <?php 
                    $onlineJudges = array_filter($judgeConnections, fn($jc) => $jc['is_active']);
                    ?>
                    <?php if (empty($onlineJudges)): ?>
                        <div class="p-4 text-muted">No online judges found.</div>
                    <?php else: ?>
                        <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                            <table class="table table-bordered table-hover table-sm scoring-table-level mb-0">
                                <thead class="thead-dark sticky-top thead-cat-accent" style="background-color: #37474f; z-index: 10;">
                                    <tr>
                                        <th class="align-middle text-center" style="min-width: 100px; position: sticky; left: 0; background-color: #37474f; z-index: 11;"><strong>Judge Name</strong></th>
                                        <th class="text-center criteria-header-cell">
                                            <div class="criteria-header-inner">
                                                <strong>Username</strong>
                                            </div>
                                        </th>
                                        <th class="text-center criteria-header-cell">
                                            <div class="criteria-header-inner">
                                                <strong>Event</strong>
                                            </div>
                                        </th>
                                        <th class="text-center criteria-header-cell">
                                            <div class="criteria-header-inner">
                                                <strong>Last Activity</strong>
                                            </div>
                                        </th>
                                        <th class="align-middle text-center" style="min-width: 100px;"><strong>Actions</strong></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($onlineJudges as $connection): ?>
                                        <tr data-contestant-id="<?= $connection['judge']['id'] ?>">
                                            <td class="text-center font-weight-bold" style="position: sticky; left: 0; background-color: inherit; z-index: 1;">
                                                <strong><?= htmlspecialchars($connection['judge']['full_name']) ?></strong>
                                            </td>
                                            <td class="text-center"><?= htmlspecialchars($connection['judge']['username']) ?></td>
                                            <td class="text-center">
                                                <a href="/tabulation/events/<?= $connection['event']['id'] ?>" class="text-primary">
                                                    <?= htmlspecialchars($connection['event']['name']) ?>
                                                </a>
                                            </td>
                                            <td class="text-center">
                                                <small class="text-muted">
                                                    <?= date('H:i:s', $connection['session_info']['last_activity']) ?>
                                                </small>
                                                <br>
                                                <small class="text-success">
                                                    Active now
                                                </small>
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-xs btn-info" onclick="showSessionDetails('<?= htmlspecialchars(json_encode($connection['session_info'])) ?>')">
                                                    <i class="fas fa-info-circle"></i> Details
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Offline Only Panel -->
                <div id="panel-offline" class="score-sheet-panel cat-color-2" data-status="offline" style="display:none;">
                    <?php 
                    $offlineJudges = array_filter($judgeConnections, fn($jc) => !$jc['is_active']);
                    ?>
                    <?php if (empty($offlineJudges)): ?>
                        <div class="p-4 text-muted">No offline judges found.</div>
                    <?php else: ?>
                        <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                            <table class="table table-bordered table-hover table-sm scoring-table-level mb-0">
                                <thead class="thead-dark sticky-top thead-cat-accent" style="background-color: #37474f; z-index: 10;">
                                    <tr>
                                        <th class="align-middle text-center" style="min-width: 100px; position: sticky; left: 0; background-color: #37474f; z-index: 11;"><strong>Judge Name</strong></th>
                                        <th class="text-center criteria-header-cell">
                                            <div class="criteria-header-inner">
                                                <strong>Username</strong>
                                            </div>
                                        </th>
                                        <th class="text-center criteria-header-cell">
                                            <div class="criteria-header-inner">
                                                <strong>Email</strong>
                                            </div>
                                        </th>
                                        <th class="text-center criteria-header-cell">
                                            <div class="criteria-header-inner">
                                                <strong>Event</strong>
                                            </div>
                                        </th>
                                        <th class="text-center criteria-header-cell">
                                            <div class="criteria-header-inner">
                                                <strong>Judge #</strong>
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($offlineJudges as $connection): ?>
                                        <tr data-contestant-id="<?= $connection['judge']['id'] ?>" class="table-secondary">
                                            <td class="text-center font-weight-bold" style="position: sticky; left: 0; background-color: inherit; z-index: 1;">
                                                <strong><?= htmlspecialchars($connection['judge']['full_name']) ?></strong>
                                            </td>
                                            <td class="text-center"><?= htmlspecialchars($connection['judge']['username']) ?></td>
                                            <td class="text-center"><?= htmlspecialchars($connection['judge']['email']) ?></td>
                                            <td class="text-center">
                                                <a href="/tabulation/events/<?= $connection['event']['id'] ?>" class="text-primary">
                                                    <?= htmlspecialchars($connection['event']['name']) ?>
                                                </a>
                                            </td>
                                            <td class="text-center">
                                                <?= $connection['judge']['judge_number'] ? 'Judge #' . htmlspecialchars($connection['judge']['judge_number']) : '-' ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Statistics Panel -->
                <div id="panel-stats" class="score-sheet-panel cat-color-3" data-status="stats" style="display:none;">
                    <div class="p-4">
                        <div class="row">
                            <div class="col-md-3 col-6 mb-3">
                                <div class="card border-success">
                                    <div class="card-body text-center">
                                        <h3 class="text-success"><?= count(array_filter($judgeConnections, fn($jc) => $jc['is_active'])) ?></h3>
                                        <p class="mb-0">Online Judges</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <div class="card border-danger">
                                    <div class="card-body text-center">
                                        <h3 class="text-danger"><?= count(array_filter($judgeConnections, fn($jc) => !$jc['is_active'])) ?></h3>
                                        <p class="mb-0">Offline Judges</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <div class="card border-info">
                                    <div class="card-body text-center">
                                        <h3 class="text-info"><?= count($ongoingEvents) ?></h3>
                                        <p class="mb-0">Ongoing Events</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <div class="card border-warning">
                                    <div class="card-body text-center">
                                        <h3 class="text-warning"><?= count($judgeConnections) ?></h3>
                                        <p class="mb-0">Total Assigned Judges</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Troubleshooting Guide -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-tools mr-2"></i>
                                    Troubleshooting Guide
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6><i class="fas fa-user-times text-danger mr-2"></i>Judge Shows Offline</h6>
                                        <ul class="small">
                                            <li>Judge may have closed their browser</li>
                                            <li>Session may have expired (1 hour timeout)</li>
                                            <li>Network connectivity issues</li>
                                            <li>Judge may have logged out</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <h6><i class="fas fa-exclamation-triangle text-warning mr-2"></i>Auto-Save Issues</h6>
                                        <ul class="small">
                                            <li>Check if judge is online (green status)</li>
                                            <li>Verify judge has stable internet connection</li>
                                            <li>Ensure judge's browser supports JavaScript</li>
                                            <li>Check browser console for errors</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Session Details Modal -->
<div class="modal fade" id="sessionDetailsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Session Details</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="session-details-content">
                <!-- Session details will be inserted here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
.level-score-sheet-wrapper { border-color: #cfd8dc !important; }
.level-score-sheet-wrapper .card-body { background: #fafafa; }
.scoring-table-level thead th { background-color: #37474f !important; color: #eceff1 !important; border-color: #546e7a !important; }
.scoring-table-level thead th.criteria-header-cell { min-width: 150px; padding: 12px 10px; }
.scoring-table-level thead th.criteria-header-cell .criteria-header-inner { font-size: 1rem; line-height: 1.4; }
.scoring-table-level thead th.criteria-header-cell .criteria-header-inner strong { font-size: 1.05rem; }
.scoring-table-level thead th.criteria-header-cell .badge { font-size: 0.8rem; padding: 4px 8px; }
.scoring-table-level tbody tr.table-success { background-color: rgba(77, 182, 172, 0.08) !important; }
.scoring-table-level tbody tr.table-secondary { background-color: rgba(108, 117, 125, 0.08) !important; }
.scoring-table-level td, .scoring-table-level th { vertical-align: middle !important; }

/* Excel-style tabs above the sheet */
.level-score-tabs {
    display: flex;
    align-items: stretch;
    background: #455a64;
    border-bottom: 1px solid #546e7a;
    padding: 0 28px 0 28px;
    min-height: 38px;
}
.tabs-scroll-arrows {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    color: #888;
    cursor: pointer;
    flex-shrink: 0;
    margin: 0 -28px 0 0;
}
.tabs-scroll-arrows:hover { color: #80cbc4; }
.tabs-right { margin: 0 0 0 -28px; }
.tabs-container {
    display: flex;
    flex-wrap: nowrap;
    overflow-x: auto;
    overflow-y: hidden;
    gap: 0;
    flex: 1;
    min-width: 0;
    -webkit-overflow-scrolling: touch;
}
.tabs-container::-webkit-scrollbar { height: 6px; }
.tabs-container::-webkit-scrollbar-thumb { background: #555; border-radius: 3px; }
.score-sheet-tab {
    flex-shrink: 0;
    padding: 8px 14px;
    border: 1px solid #546e7a;
    border-top: none;
    border-radius: 0 0 4px 4px;
    margin-bottom: 4px;
    background: #546e7a;
    color: rgba(255,255,255,0.9);
    font-size: 0.9rem;
    cursor: pointer;
    white-space: nowrap;
    transition: background .15s, color .15s;
}
.score-sheet-tab:hover { color: #fff; }
/* Category 0: Teal (All Judges) */
.score-sheet-tab.cat-color-0 { border-left: 3px solid #00897b; }
.score-sheet-tab.cat-color-0:hover { background: #00695c; }
.score-sheet-tab.cat-color-0.active { background: #e0f2f1; color: #00695c; border-color: #00897b; border-bottom-color: #e0f2f1; }
.score-sheet-panel.cat-color-0 .thead-cat-accent th { border-top: 4px solid #00897b; }
/* Category 1: Blue (Online Only) */
.score-sheet-tab.cat-color-1 { border-left: 3px solid #1976d2; }
.score-sheet-tab.cat-color-1:hover { background: #1565c0; }
.score-sheet-tab.cat-color-1.active { background: #e3f2fd; color: #0d47a1; border-color: #1976d2; border-bottom-color: #e3f2fd; }
.score-sheet-panel.cat-color-1 .thead-cat-accent th { border-top: 4px solid #1976d2; }
/* Category 2: Amber (Offline Only) */
.score-sheet-tab.cat-color-2 { border-left: 3px solid #f57c00; }
.score-sheet-tab.cat-color-2:hover { background: #ef6c00; }
.score-sheet-tab.cat-color-2.active { background: #fff3e0; color: #e65100; border-color: #f57c00; border-bottom-color: #fff3e0; }
.score-sheet-panel.cat-color-2 .thead-cat-accent th { border-top: 4px solid #f57c00; }
/* Category 3: Purple (Statistics) */
.score-sheet-tab.cat-color-3 { border-left: 3px solid #7b1fa2; }
.score-sheet-tab.cat-color-3:hover { background: #6a1b9a; }
.score-sheet-tab.cat-color-3.active { background: #f3e5f5; color: #4a148c; border-color: #7b1fa2; border-bottom-color: #f3e5f5; }
.score-sheet-panel.cat-color-3 .thead-cat-accent th { border-top: 4px solid #7b1fa2; }
</style>

<script>
// Tab switching functionality from level_score_table.php
(function() {
    function initScoreSheetTabs() {
        var wrapper = document.querySelector('.level-score-sheet-wrapper');
        if (!wrapper) return;
        wrapper.addEventListener('click', function(e) {
            var tab = e.target.closest('.score-sheet-tab');
            if (!tab) return;
            e.preventDefault();
            e.stopPropagation();
            var panelId = tab.getAttribute('data-panel-id');
            if (!panelId) return;
            var panels = wrapper.querySelectorAll('.score-sheet-panel');
            for (var i = 0; i < panels.length; i++) {
                panels[i].style.display = 'none';
            }
            var panel = document.getElementById(panelId);
            if (panel) {
                panel.style.display = 'block';
            }
            var tabs = wrapper.querySelectorAll('.score-sheet-tab');
            for (var j = 0; j < tabs.length; j++) {
                tabs[j].classList.remove('active');
            }
            tab.classList.add('active');
        });
        var tabsContainer = wrapper.querySelector('.tabs-container');
        if (tabsContainer) {
            var left = wrapper.querySelector('.tabs-left');
            var right = wrapper.querySelector('.tabs-right');
            if (left) left.addEventListener('click', function() { tabsContainer.scrollBy({ left: -120, behavior: 'smooth' }); });
            if (right) right.addEventListener('click', function() { tabsContainer.scrollBy({ left: 120, behavior: 'smooth' }); });
        }
    }

    function init() {
        initScoreSheetTabs();
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();

// Auto-refresh functionality using ping API
let refreshInterval;
let pingInterval;

function startAutoRefresh() {
    // Initial ping check
    pingJudges();
    
    // Set up ping interval every 4 seconds
    pingInterval = setInterval(pingJudges, 4000);
}

function pingJudges() {
    $.ajax({
        url: '/tabulation/api/ping-judges',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                updateJudgeConnections(response.data);
                $('#last-updated').text(response.timestamp);
                showNotification('Judge status updated', 'success');
            } else {
                showNotification('Error checking judge connections', 'error');
            }
        },
        error: function() {
            showNotification('Error checking judge connections', 'error');
        }
    });
}

function updateJudgeConnections(judgeData) {
    // Update All Judges Panel
    updatePanel('panel-all', judgeData, false);
    
    // Update Online Only Panel
    const onlineJudges = judgeData.filter(jd => jd.is_active);
    updatePanel('panel-online', onlineJudges, true);
    
    // Update Offline Only Panel
    const offlineJudges = judgeData.filter(jd => !jd.is_active);
    updatePanel('panel-offline', offlineJudges, true);
    
    // Update Statistics Panel
    updateStatistics(judgeData);
}

function updatePanel(panelId, data, isFiltered) {
    const panel = $(`#${panelId}`);
    const tbody = panel.find('tbody');
    
    if (data.length === 0) {
        if (isFiltered) {
            tbody.html('<tr><td colspan="' + (panelId === 'panel-online' ? '5' : '6') + '" class="text-center text-muted p-4">No judges found.</td></tr>');
        } else {
            tbody.html('<tr><td colspan="9" class="text-center text-muted p-4">No judges assigned to ongoing events found.</td></tr>');
        }
        return;
    }
    
    let html = '';
    data.forEach(function(connection) {
        const judge = connection.judge;
        const event = connection.event;
        const isActive = connection.is_active;
        const responseTime = connection.response_time;
        
        if (panelId === 'panel-all') {
            html += `
                <tr data-contestant-id="${judge.id}" class="${isActive ? '' : 'table-secondary'}">
                    <td class="text-center font-weight-bold" style="position: sticky; left: 0; background-color: inherit; z-index: 1;">
                        ${isActive ? 
                            '<span class="badge badge-success"><i class="fas fa-circle"></i> Online</span>' : 
                            '<span class="badge badge-danger"><i class="fas fa-circle"></i> Offline</span>'
                        }
                    </td>
                    <td class="text-center"><strong>${htmlspecialchars(judge.full_name)}</strong></td>
                    <td class="text-center">${htmlspecialchars(judge.username)}</td>
                    <td class="text-center">${htmlspecialchars(judge.email)}</td>
                    <td class="text-center">
                        <a href="/tabulation/events/${event.id}" class="text-primary">${htmlspecialchars(event.name)}</a>
                        <br><small class="text-muted">${htmlspecialchars(event.event_type)}</small>
                    </td>
                    <td class="text-center">${judge.judge_number ? 'Judge #' + htmlspecialchars(judge.judge_number) : '-'}</td>
                    <td class="text-center">
                        <span class="badge badge-info">${judge.assigned_rounds} rounds</span>
                    </td>
                    <td class="text-center">
                        ${connection.last_activity ? 
                            `<small class="text-muted">${new Date(connection.last_activity * 1000).toLocaleString()}</small>
                             <br><small class="${isActive ? 'text-success' : 'text-danger'}">${isActive ? 'Online' : 'Offline'}</small>` :
                            '<small class="text-muted">Never</small>'
                        }
                    </td>
                    <td class="text-center"><span class="text-muted">-</span></td>
                </tr>
            `;
        } else if (panelId === 'panel-online') {
            html += `
                <tr data-contestant-id="${judge.id}">
                    <td class="text-center font-weight-bold" style="position: sticky; left: 0; background-color: inherit; z-index: 1;">
                        <strong>${htmlspecialchars(judge.full_name)}</strong>
                    </td>
                    <td class="text-center">${htmlspecialchars(judge.username)}</td>
                    <td class="text-center">
                        <a href="/tabulation/events/${event.id}" class="text-primary">${htmlspecialchars(event.name)}</a>
                    </td>
                    <td class="text-center">
                        ${connection.last_activity ? `<small class="text-muted">${new Date(connection.last_activity * 1000).toLocaleString()}</small>` : '<small class="text-muted">Never</small>'}
                        <br><small class="text-success">Online</small>
                    </td>
                    <td class="text-center"><span class="text-muted">-</span></td>
                </tr>
            `;
        } else if (panelId === 'panel-offline') {
            html += `
                <tr data-contestant-id="${judge.id}" class="table-secondary">
                    <td class="text-center font-weight-bold" style="position: sticky; left: 0; background-color: inherit; z-index: 1;">
                        <strong>${htmlspecialchars(judge.full_name)}</strong>
                    </td>
                    <td class="text-center">${htmlspecialchars(judge.username)}</td>
                    <td class="text-center">${htmlspecialchars(judge.email)}</td>
                    <td class="text-center">
                        <a href="/tabulation/events/${event.id}" class="text-primary">${htmlspecialchars(event.name)}</a>
                    </td>
                    <td class="text-center">${judge.judge_number ? 'Judge #' + htmlspecialchars(judge.judge_number) : '-'}</td>
                </tr>
            `;
        }
    });
    
    tbody.html(html);
}

function updateStatistics(judgeData) {
    const onlineCount = judgeData.filter(jd => jd.is_active).length;
    const offlineCount = judgeData.filter(jd => !jd.is_active).length;
    const uniqueEvents = [...new Set(judgeData.map(jd => jd.event.id))].length;
    
    $('#panel-stats h3.text-success').text(onlineCount);
    $('#panel-stats h3.text-danger').text(offlineCount);
    $('#panel-stats h3.text-info').text(uniqueEvents);
    $('#panel-stats h3.text-warning').text(judgeData.length);
}

// Manual refresh function (now uses ping API)
function refreshConnections() {
    pingJudges();
}

// Show session details
function showSessionDetails(sessionInfo) {
    const session = JSON.parse(sessionInfo);
    
    const details = `
        <table class="table table-sm">
            <tr><td><strong>Session ID:</strong></td><td>${session.session_id}</td></tr>
            <tr><td><strong>Last Activity:</strong></td><td>${new Date(session.last_activity * 1000).toLocaleString()}</td></tr>
            <tr><td><strong>Role:</strong></td><td>${session.role_name}</td></tr>
            <tr><td><strong>Full Name:</strong></td><td>${session.full_name}</td></tr>
            <tr><td><strong>Login Time:</strong></td><td>${session.login_time || 'Not recorded'}</td></tr>
        </table>
    `;
    
    $('#session-details-content').html(details);
    $('#sessionDetailsModal').modal('show');
}

// Show notification
function showNotification(message, type) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const notification = `
        <div class="alert ${alertClass} alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999;">
            ${message}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    `;
    
    $('body').append(notification);
    
    // Auto-remove after 3 seconds
    setTimeout(() => {
        $('.alert').alert('close');
    }, 3000);
}

// Start auto-refresh when page loads
$(document).ready(function() {
    startAutoRefresh();
});

// Clean up intervals when page unloads
$(window).on('beforeunload', function() {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
    if (pingInterval) {
        clearInterval(pingInterval);
    }
});
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
