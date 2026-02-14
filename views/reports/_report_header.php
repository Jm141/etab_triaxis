<?php
/**
 * Standardized Report Header Component
 * 
 * Usage:
 * require __DIR__ . '/_report_header.php';
 * echo renderReportHeader($event, [
 *     'level' => $level['name'] ?? null,
 *     'round' => $round['name'] ?? null,
 *     'judge' => $judge['judge_name'] ?? null,
 *     'judge_number' => $judge['judge_number'] ?? null
 * ]);
 */

function renderReportHeader($event, $options = []) {
    $level = $options['level'] ?? null;
    $round = $options['round'] ?? null;
    $judge = $options['judge'] ?? null;
    $judgeNumber = $options['judge_number'] ?? null;
    
    $html = '<div class="report-header">';
    $html .= '<h2>' . htmlspecialchars($event['name']) . '</h2>';
    $html .= '<div class="report-info">';
    
    if ($level) {
        $html .= '<strong>Level:</strong> ' . htmlspecialchars($level) . '<br>';
    }
    
    if ($round) {
        $html .= '<strong>Round:</strong> ' . htmlspecialchars($round) . '<br>';
    }
    
    if ($judge) {
        $judgeText = 'Judge #' . htmlspecialchars($judgeNumber ?? '') . ' - ' . htmlspecialchars($judge);
        $html .= '<strong>Judge:</strong> ' . $judgeText . '<br>';
    }
    
    if (!empty($event['venue'])) {
        $html .= '<strong>Venue:</strong> ' . htmlspecialchars($event['venue']) . '<br>';
    }
    
    $date = !empty($event['event_date']) ? date('F d, Y', strtotime($event['event_date'])) : date('F d, Y');
    $html .= '<strong>Date:</strong> ' . $date;
    
    $html .= '</div>';
    $html .= '</div>';
    
    return $html;
}
?>
