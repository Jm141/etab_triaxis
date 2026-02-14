<?php
/**
 * Reports Testing Script
 * This script checks if reports are properly configured and accessible
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== REPORTS SYSTEM TEST ===\n\n";

// 1. Check if ReportsController exists
echo "1. Checking ReportsController...\n";
$controllerFile = __DIR__ . '/controllers/ReportsController.php';
if (file_exists($controllerFile)) {
    echo "   ✓ ReportsController.php exists\n";
    
    // Check for syntax errors
    $output = [];
    $return = 0;
    exec("php -l " . escapeshellarg($controllerFile) . " 2>&1", $output, $return);
    if ($return === 0) {
        echo "   ✓ ReportsController.php has no syntax errors\n";
    } else {
        echo "   ✗ ReportsController.php has syntax errors:\n";
        foreach ($output as $line) {
            echo "     " . $line . "\n";
        }
    }
} else {
    echo "   ✗ ReportsController.php NOT FOUND\n";
}

// 2. Check if all report view files exist
echo "\n2. Checking report view files...\n";
$requiredViews = [
    'index',
    'overall_ranking',
    'per_judge_score_sheet',
    'consolidated_scores',
    'criteria_breakdown',
    'category_winners',
    'top_n_finalists',
    'preliminary_results',
    'semi_final_results',
    'final_results',
    'elimination_summary',
    'judge_attendance',
    'score_edit_log',
    'tie_breaker',
    'weighted_computation',
    'judge_performance',
    'candidate_summary',
    'event_summary'
];

$missingViews = [];
foreach ($requiredViews as $view) {
    $viewFile = __DIR__ . '/views/reports/' . $view . '.php';
    if (file_exists($viewFile)) {
        echo "   ✓ {$view}.php exists\n";
    } else {
        echo "   ✗ {$view}.php MISSING\n";
        $missingViews[] = $view;
    }
}

// 3. Check routes
echo "\n3. Checking routes...\n";
$routesFile = __DIR__ . '/routes/web.php';
if (file_exists($routesFile)) {
    $routesContent = file_get_contents($routesFile);
    $reportRoutes = [
        'events/{eventId}/reports',
        'events/{eventId}/reports/overall-ranking',
        'events/{eventId}/reports/per-judge/{roundId}',
        'events/{eventId}/reports/consolidated/{roundId}',
        'events/{eventId}/reports/preliminary',
        'events/{eventId}/reports/event-summary',
        'events/{eventId}/reports/judge-performance/{roundId}'
    ];
    
    foreach ($reportRoutes as $route) {
        if (strpos($routesContent, $route) !== false) {
            echo "   ✓ Route '{$route}' found\n";
        } else {
            echo "   ✗ Route '{$route}' NOT FOUND\n";
        }
    }
} else {
    echo "   ✗ routes/web.php NOT FOUND\n";
}

// 4. Check ScoringEngine (used by level reports)
echo "\n4. Checking ScoringEngine...\n";
$scoringEngineFile = __DIR__ . '/core/ScoringEngine.php';
if (file_exists($scoringEngineFile)) {
    echo "   ✓ ScoringEngine.php exists\n";
    
    // Check for syntax errors
    $output = [];
    $return = 0;
    exec("php -l " . escapeshellarg($scoringEngineFile) . " 2>&1", $output, $return);
    if ($return === 0) {
        echo "   ✓ ScoringEngine.php has no syntax errors\n";
    } else {
        echo "   ✗ ScoringEngine.php has syntax errors:\n";
        foreach ($output as $line) {
            echo "     " . $line . "\n";
        }
    }
    
    // Check if calculateLevelRankings includes team_name
    $content = file_get_contents($scoringEngineFile);
    if (strpos($content, 'team_name') !== false) {
        echo "   ✓ calculateLevelRankings includes team_name\n";
    } else {
        echo "   ⚠ calculateLevelRankings may not include team_name\n";
    }
} else {
    echo "   ✗ ScoringEngine.php NOT FOUND\n";
}

// 5. Check Controller base class
echo "\n5. Checking Controller base class...\n";
$controllerBaseFile = __DIR__ . '/core/Controller.php';
if (file_exists($controllerBaseFile)) {
    echo "   ✓ Controller.php exists\n";
    
    // Check for required methods
    $content = file_get_contents($controllerBaseFile);
    $requiredMethods = ['restrictJudges', 'requireEventAccess', 'view'];
    foreach ($requiredMethods as $method) {
        if (strpos($content, "function {$method}") !== false || strpos($content, "protected function {$method}") !== false) {
            echo "   ✓ Method '{$method}' exists\n";
        } else {
            echo "   ✗ Method '{$method}' NOT FOUND\n";
        }
    }
} else {
    echo "   ✗ Controller.php NOT FOUND\n";
}

// 6. Summary
echo "\n=== TEST SUMMARY ===\n";
if (empty($missingViews)) {
    echo "✓ All report views exist\n";
} else {
    echo "✗ Missing views: " . implode(', ', $missingViews) . "\n";
}

echo "\n=== TEST COMPLETE ===\n";
echo "If all checks passed, reports should be working.\n";
echo "To test manually:\n";
echo "1. Log in as Admin/Tabulator (not Judge)\n";
echo "2. Go to an event page\n";
echo "3. Click 'Reports' button\n";
echo "4. Try generating Report #1 (Overall Ranking) with a round selected\n";
echo "5. Try Report #18 (Event Summary) - no round needed\n";
