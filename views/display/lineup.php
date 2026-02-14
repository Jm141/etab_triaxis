<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Live Lineup - <?= htmlspecialchars($event['name']) ?></title>
    
    <!-- Inter Font -->
    <link rel="stylesheet" href="/tabulation/public/assets/css/inter-font.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="/tabulation/public/assets/css/font-awesome.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #0a0a0a;
            color: #ffffff;
            line-height: 1.6;
            padding: 0;
            margin: 0;
        }
        
        .container {
            max-width: 1600px;
            margin: 0 auto;
            padding: 60px 40px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 80px;
            padding-bottom: 40px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .header h1 {
            font-size: 3.5rem;
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 12px;
            letter-spacing: -0.04em;
        }
        
        .header .meta {
            font-size: 1rem;
            color: rgba(255,255,255,0.6);
            font-weight: 500;
        }
        
        .header .meta span {
            margin: 0 16px;
        }
        
        .stats {
            text-align: center;
            margin-bottom: 60px;
        }
        
        .stats-number {
            font-size: 4rem;
            font-weight: 800;
            color: #00d9ff;
            margin-bottom: 8px;
            letter-spacing: -0.03em;
        }
        
        .stats-label {
            font-size: 0.875rem;
            color: rgba(255,255,255,0.6);
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-weight: 600;
        }
        
        .controls {
            position: fixed;
            top: 24px;
            right: 24px;
            z-index: 1000;
            display: flex;
            gap: 8px;
        }
        
        .btn-control {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 0.875rem;
            color: rgba(255,255,255,0.9);
            cursor: pointer;
            transition: all 0.2s;
            font-family: inherit;
            display: flex;
            align-items: center;
            gap: 6px;
            font-weight: 500;
            backdrop-filter: blur(10px);
        }
        
        .btn-control:hover {
            background: rgba(255,255,255,0.15);
            border-color: rgba(255,255,255,0.3);
            color: #ffffff;
        }
        
        .btn-control.active {
            background: #00d9ff;
            color: #0a0a0a;
            border-color: #00d9ff;
            font-weight: 600;
        }
        
        .contestants-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 24px;
            margin-bottom: 40px;
        }
        
        .contestants-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .contestant-card {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            padding: 28px;
            transition: all 0.2s;
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(10px);
        }
        
        .contestants-grid .contestant-card {
            min-height: 160px;
        }
        
        .contestants-list .contestant-card {
            display: flex;
            align-items: center;
            padding: 24px 28px;
        }
        
        .contestant-card::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: #00d9ff;
            opacity: 0;
            transition: opacity 0.2s;
        }
        
        .contestant-card:hover {
            border-color: #00d9ff;
            background: rgba(0,217,255,0.1);
            transform: translateY(-2px);
        }
        
        .contestant-card:hover::before {
            opacity: 1;
        }
        
        .contestant-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 56px;
            height: 56px;
            background: rgba(0,217,255,0.2);
            border: 2px solid #00d9ff;
            border-radius: 12px;
            font-size: 1.5rem;
            font-weight: 700;
            color: #00d9ff;
            margin-right: 20px;
            flex-shrink: 0;
        }
        
        .contestants-list .contestant-number {
            width: 48px;
            height: 48px;
            font-size: 1.25rem;
        }
        
        .contestant-info {
            flex: 1;
        }
        
        .contestant-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 8px;
            letter-spacing: -0.02em;
        }
        
        .contestants-list .contestant-name {
            font-size: 1.25rem;
            margin-bottom: 6px;
        }
        
        .contestant-details {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            font-size: 0.875rem;
            color: rgba(255,255,255,0.7);
        }
        
        .contestants-list .contestant-details {
            gap: 20px;
        }
        
        .contestant-detail {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .contestant-detail i {
            font-size: 0.75rem;
            color: #00d9ff;
        }
        
        .empty-state {
            text-align: center;
            padding: 100px 20px;
            color: rgba(255,255,255,0.5);
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 40px 20px;
            }
            
            .header h1 {
                font-size: 2.25rem;
            }
            
            .contestants-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            
            .controls {
                top: 16px;
                right: 16px;
                flex-direction: column;
            }
            
            .btn-control {
                padding: 8px 12px;
                font-size: 0.8rem;
            }
        }
        
        @media print {
            .controls {
                display: none;
            }
            
            body {
                background: white;
                color: #0a0a0a;
            }
            
            .contestant-card {
                background: white;
                border: 1px solid #e5e5e5;
                break-inside: avoid;
            }
        }
        
        /* Fullscreen styles */
        body.fullscreen {
            background: #0a0a0a;
            padding: 0;
        }
        
        body.fullscreen .container {
            padding: 80px 60px;
        }
        
        body.fullscreen .header {
            margin-bottom: 100px;
        }
        
        body.fullscreen .header h1 {
            font-size: 4.5rem;
        }
        
        body.fullscreen .stats-number {
            font-size: 5rem;
        }
        
        body.fullscreen .contestants-grid {
            grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
            gap: 40px;
        }
        
        body.fullscreen .contestant-card {
            padding: 40px;
        }
        
        body.fullscreen .contestant-name {
            font-size: 1.75rem;
        }
    </style>
</head>
<body>
    <div class="controls">
        <button class="btn-control" onclick="setView('grid')" id="btn-grid">
            <i class="fas fa-th"></i> Grid
        </button>
        <button class="btn-control" onclick="setView('list')" id="btn-list">
            <i class="fas fa-list"></i> List
        </button>
        <button class="btn-control" onclick="toggleFullscreen()" id="btn-fullscreen">
            <i class="fas fa-expand"></i> Fullscreen
        </button>
    </div>
    
    <div class="container">
        <div class="header">
            <h1><?= htmlspecialchars($event['name']) ?></h1>
            <div class="meta">
                <?php if ($event['event_date']): ?>
                    <span><i class="far fa-calendar"></i> <?= htmlspecialchars($event['event_date']) ?></span>
                <?php endif; ?>
                <?php if ($event['venue']): ?>
                    <span><i class="far fa-map-marker-alt"></i> <?= htmlspecialchars($event['venue']) ?></span>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="stats">
            <div class="stats-number"><?= count($contestants) ?></div>
            <div class="stats-label">Contestants</div>
        </div>
        
        <?php if (empty($contestants)): ?>
            <div class="empty-state">
                <i class="fas fa-users"></i>
                <div>No contestants registered</div>
            </div>
        <?php else: ?>
            <div id="contestants-container" class="contestants-grid">
                <?php foreach ($contestants as $contestant): ?>
                    <div class="contestant-card">
                        <div style="display: flex; align-items: flex-start;">
                            <div class="contestant-number">
                                <?= htmlspecialchars($contestant['contestant_number']) ?>
                            </div>
                            <div class="contestant-info">
                                <div class="contestant-name">
                                    <?= htmlspecialchars($contestant['name']) ?>
                                </div>
                                <div class="contestant-details">
                                    <?php if ($contestant['team_name']): ?>
                                        <div class="contestant-detail">
                                            <i class="fas fa-users"></i>
                                            <span><?= htmlspecialchars($contestant['team_name']) ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($contestant['category']): ?>
                                        <div class="contestant-detail">
                                            <i class="fas fa-tag"></i>
                                            <span><?= htmlspecialchars($contestant['category']) ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        // View toggle
        function setView(view) {
            const container = document.getElementById('contestants-container');
            const btnGrid = document.getElementById('btn-grid');
            const btnList = document.getElementById('btn-list');
            
            if (view === 'grid') {
                container.className = 'contestants-grid';
                btnGrid.classList.add('active');
                btnList.classList.remove('active');
            } else {
                container.className = 'contestants-list';
                btnList.classList.add('active');
                btnGrid.classList.remove('active');
            }
            
            localStorage.setItem('lineup-view', view);
        }
        
        // Load saved preference
        const savedView = localStorage.getItem('lineup-view') || 'grid';
        setView(savedView);
        
        // Fullscreen
        function toggleFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().then(() => {
                    document.body.classList.add('fullscreen');
                    document.getElementById('btn-fullscreen').innerHTML = '<i class="fas fa-compress"></i> Exit';
                });
            } else {
                document.exitFullscreen().then(() => {
                    document.body.classList.remove('fullscreen');
                    document.getElementById('btn-fullscreen').innerHTML = '<i class="fas fa-expand"></i> Fullscreen';
                });
            }
        }
        
        // Handle fullscreen change
        document.addEventListener('fullscreenchange', function() {
            if (!document.fullscreenElement) {
                document.body.classList.remove('fullscreen');
                document.getElementById('btn-fullscreen').innerHTML = '<i class="fas fa-expand"></i> Fullscreen';
            }
        });
        
        // Auto-refresh
        setInterval(() => location.reload(), 30000);
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.key === 'f' || e.key === 'F') {
                e.preventDefault();
                toggleFullscreen();
            } else if (e.key === 'g' || e.key === 'G') {
                e.preventDefault();
                setView('grid');
            } else if (e.key === 'l' || e.key === 'L') {
                e.preventDefault();
                setView('list');
            }
        });
    </script>
</body>
</html>
