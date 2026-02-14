<?php
/**
 * Reports Controller
 * Handles simplified reporting functionality
 */

class ReportsController extends Controller {
    
    /**
     * Reports index - show available reports
     * Only Technical Admin and Super Admin can access reports
     */
    public function index($eventId) {
        $this->restrictJudges();
        $this->requireEventAccess($eventId);
        
        $currentRole = Session::get('role_name');
        // Only Event Technical Admin and Super Admin can view reports
        // Event Organizer CANNOT view reports (restricted)
        if (!in_array($currentRole, ['Super Admin', 'Event Technical Admin'])) {
            $this->accessDenied("Access denied. Only Technical Admin can view reports.");
        }
        
        $event = $this->db->fetchOne("SELECT * FROM events WHERE id = ?", [$eventId]);
        if (!$event) {
            die("Event not found");
        }
        
        // Get rounds and levels for this event
        $rounds = $this->db->fetchAll(
            "SELECT r.*, el.name as level_name, el.order as level_order
             FROM rounds r
             JOIN event_levels el ON r.level_id = el.id
             WHERE el.event_id = ?
             ORDER BY el.order, r.order",
            [$eventId]
        );
        
        $levels = $this->db->fetchAll(
            "SELECT * FROM event_levels WHERE event_id = ? ORDER BY `order`",
            [$eventId]
        );
        
        // Get all judges for this event
        $judges = $this->db->fetchAll(
            "SELECT j.id, j.judge_number, u.full_name as judge_name
             FROM judges j
             JOIN users u ON j.user_id = u.id
             WHERE j.event_id = ? AND j.is_active = 1
             ORDER BY j.judge_number",
            [$eventId]
        );
        
        $this->view('reports/index', [
            'event' => $event,
            'rounds' => $rounds,
            'levels' => $levels,
            'judges' => $judges
        ]);
    }
    
    /**
     * Report Per Round
     * Shows: Rank, Contestant #, Name, Raw scores per judge, Total
     * Only Technical Admin and Super Admin can access reports
     */
    public function roundReport($eventId, $roundId) {
        $this->restrictJudges();
        $this->requireEventAccess($eventId);
        
        $currentRole = Session::get('role_name');
        // Only Event Technical Admin and Super Admin can view reports
        // Event Organizer CANNOT view reports (restricted)
        if (!in_array($currentRole, ['Super Admin', 'Event Technical Admin'])) {
            $this->accessDenied("Access denied. Only Technical Admin can view reports.");
        }
        
        $event = $this->db->fetchOne("SELECT * FROM events WHERE id = ?", [$eventId]);
        $round = $this->db->fetchOne(
            "SELECT r.*, el.name as level_name, el.order as level_order
             FROM rounds r
             JOIN event_levels el ON r.level_id = el.id
             WHERE r.id = ? AND el.event_id = ?",
            [$roundId, $eventId]
        );
        
        if (!$event || !$round) {
            die("Event or round not found");
        }
        
        // Get all judges for this round
        $judges = $this->db->fetchAll(
            "SELECT j.id, 
                    j.judge_number, 
                    u.full_name as judge_name
             FROM judges j
             JOIN users u ON j.user_id = u.id
             JOIN judge_assignments ja ON j.id = ja.judge_id
             WHERE ja.round_id = ? AND ja.is_active = 1
             ORDER BY CAST(j.judge_number AS UNSIGNED), j.judge_number, j.id",
            [$roundId]
        );
        
        // Get active round formula (if exists) that applies to this round
        // Priority: 1) Formula specifically for this round, 2) Formula for all rounds, 3) Most recent active formula
        $allRoundFormulas = $this->db->fetchAll(
            "SELECT * FROM scoring_formula 
             WHERE event_id = ? AND formula_type = 'round' AND is_active = 1
             ORDER BY updated_at DESC, created_at DESC",
            [$eventId]
        );
        
        // Find the formula that applies to this round
        $roundFormula = null;
        $specificFormula = null; // Formula that specifically includes this round
        $generalFormula = null;  // Formula that applies to all rounds
        
        foreach ($allRoundFormulas as $formula) {
            $roundIds = !empty($formula['round_ids']) ? json_decode($formula['round_ids'], true) : null;
            
            // If round_ids is null/empty, formula applies to all rounds
            if ($roundIds === null || empty($roundIds)) {
                if (!$generalFormula) {
                    $generalFormula = $formula; // Use first general formula found
                }
            } elseif (is_array($roundIds) && in_array($roundId, $roundIds)) {
                // This round is specifically included - prioritize this
                if (!$specificFormula) {
                    $specificFormula = $formula;
                }
            }
        }
        
        // Use the most specific formula available
        $roundFormula = $specificFormula ?: $generalFormula;
        
        // Log for debugging (can be removed in production)
        if ($roundFormula) {
            error_log("Round Report - Using formula ID {$roundFormula['id']}: {$roundFormula['formula_expression']} for round $roundId");
        } else {
            error_log("Round Report - No active formula found, using default AVG(round_scores) for round $roundId");
        }
        
        // Load any deductions for this round report
        $roundDeductions = $this->getReportDeductions($eventId, 'round', $roundId);

        // Get all contestants for this round (with or without rankings)
        // First, get the current level for this round
        $currentLevel = $this->db->fetchOne(
            "SELECT * FROM event_levels WHERE id = ?",
            [$round['level_id']]
        );
        
        // Check if elimination has occurred for this level
        // If contestants have been qualified for this level, only show those who advanced
        $qualifiedCount = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM contestants 
             WHERE event_id = ? AND status = 'Active' AND qualified_for_level_id = ?",
            [$eventId, $currentLevel['id']]
        )['count'];
        
        // Get contestants - filter based on whether elimination has occurred
        if ($qualifiedCount > 0) {
            // Elimination has occurred - only show contestants who qualified for this level
            $allContestants = $this->db->fetchAll(
                "SELECT c.id, c.contestant_number, c.name
                 FROM contestants c
                 WHERE c.event_id = ? AND c.status = 'Active' AND c.qualified_for_level_id = ?
                 ORDER BY CAST(c.contestant_number AS UNSIGNED)",
                [$eventId, $currentLevel['id']]
            );
        } else {
            // No elimination has occurred - show all active contestants
            $allContestants = $this->db->fetchAll(
                "SELECT c.id, c.contestant_number, c.name
                 FROM contestants c
                 WHERE c.event_id = ? AND c.status = 'Active'
                 ORDER BY CAST(c.contestant_number AS UNSIGNED)",
                [$eventId]
            );
        }
        
        // Then filter to only those with submitted scores in this round
        $contestants = [];
        foreach ($allContestants as $contestant) {
            $hasScore = $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM scores
                 WHERE round_id = ? AND contestant_id = ? AND is_submitted = 1",
                [$roundId, $contestant['id']]
            );
            if ($hasScore['count'] > 0) {
                $contestants[] = $contestant;
            }
        }
        
        // Get scores per judge for each contestant: raw total (sum of criterion raw scores) for display and formula
        // Total Score is computed from the SAME numbers shown in the judge columns (raw totals) so the total matches the row
        $contestantScores = [];
        foreach ($contestants as $contestant) {
            $contestantId = $contestant['id'];
            $judgeScores = [];      // Percentage (0-100) kept for any future use
            $judgeRawScores = [];   // Raw score (sum of criterion raw_score) for display
            $judgeRawValues = [];   // Raw scores in judge order - used for formula (so Total = avg of displayed numbers)
            
            foreach ($judges as $judge) {
                // Get total_score (percentage) and raw total (sum of score_details.raw_score)
                $score = $this->db->fetchOne(
                    "SELECT s.total_score,
                            (SELECT COALESCE(SUM(sd.raw_score), 0) FROM score_details sd WHERE sd.score_id = s.id) as raw_total
                     FROM scores s
                     WHERE s.round_id = ? AND s.contestant_id = ? AND s.judge_id = ? AND s.is_submitted = 1",
                    [$roundId, $contestantId, $judge['id']]
                );
                
                $judgeScore = $score ? (float)$score['total_score'] : 0;
                $judgeRaw = $score ? (float)$score['raw_total'] : 0;
                $judgeScores[$judge['id']] = $judgeScore;
                $judgeRawScores[$judge['id']] = $judgeRaw;
                $judgeRawValues[] = $judgeRaw;
            }
            
            // Calculate round total from the SAME numbers shown in the table (raw scores), so AVG(round_scores) = average of 9, 22, 27, ...
            if ($roundFormula && !empty($roundFormula['formula_expression'])) {
                $total = $this->evaluateRoundFormula($roundFormula['formula_expression'], $judgeRawValues);
            } else {
                $total = count($judgeRawValues) > 0 ? array_sum($judgeRawValues) / count($judgeRawValues) : 0;
            }
            
            $deductionInfo = $roundDeductions[$contestantId] ?? null;
            $deduction = $deductionInfo ? (float)$deductionInfo['deduction'] : 0;
            $deductionStatus = $deductionInfo['status'] ?? 'none';
            $appliedDeduction = $deductionStatus === 'granted' ? $deduction : 0;
            $adjustedTotal = max(0, $total - $appliedDeduction);
            
            $contestantScores[$contestantId] = [
                'contestant' => [
                    'contestant_id' => $contestantId,
                    'contestant_number' => $contestant['contestant_number'],
                    'name' => $contestant['name'],
                    'rank' => 0 // Will be calculated
                ],
                'judge_scores' => $judgeScores,
                'judge_raw_scores' => $judgeRawScores,
                'total' => $total,
                'deduction' => $deduction,
                'deduction_status' => $deductionStatus,
                'adjusted_total' => $adjustedTotal,
                'deduction_reason' => $deductionInfo['reason'] ?? null
            ];
        }
        
        // Sort by adjusted total descending
        uasort($contestantScores, function($a, $b) {
            return $b['adjusted_total'] <=> $a['adjusted_total'];
        });
        $sorted = array_values($contestantScores);
        $n = count($sorted);
        
        // Fractional (Olympic) ranking: tie = exact same adjusted total (within 0.001). Tied get average of positions (e.g. 3.5); next rank skips (e.g. 5).
        $i = 0;
        while ($i < $n) {
            $j = $i;
            while ($j + 1 < $n && abs((float)$sorted[$j + 1]['adjusted_total'] - (float)$sorted[$j]['adjusted_total']) <= 0.001) {
                $j++;
            }
            $rank = (($i + 1) + ($j + 1)) / 2;
            for ($k = $i; $k <= $j; $k++) {
                $sorted[$k]['contestant']['rank'] = $rank;
            }
            $i = $j + 1;
        }
        $rankedScores = $sorted;
        
        // Sort by contestant number (after ranking)
        usort($rankedScores, function($a, $b) {
            $numA = (int)$a['contestant']['contestant_number'];
            $numB = (int)$b['contestant']['contestant_number'];
            return $numA <=> $numB;
        });
        
        $contestantScores = $rankedScores;
            
        $this->view('reports/round', [
            'event' => $event,
            'round' => $round,
            'judges' => $judges,
            'contestantScores' => $contestantScores,
            'formula' => $roundFormula
        ]);
    }
    
    /**
     * Report Per Level
     * Shows: Rank, Contestant #, Name, Total per round in level, Average per round, Total per level
     * Only Technical Admin and Super Admin can access reports
     */
    public function levelReport($eventId, $levelId) {
        $this->restrictJudges();
        $this->requireEventAccess($eventId);
        
        $currentRole = Session::get('role_name');
        // Only Event Technical Admin and Super Admin can view reports
        // Event Organizer CANNOT view reports (restricted)
        if (!in_array($currentRole, ['Super Admin', 'Event Technical Admin'])) {
            $this->accessDenied("Access denied. Only Technical Admin can view reports.");
        }
        
        $event = $this->db->fetchOne("SELECT * FROM events WHERE id = ?", [$eventId]);
        $level = $this->db->fetchOne(
            "SELECT * FROM event_levels WHERE id = ? AND event_id = ?",
            [$levelId, $eventId]
        );
        
        if (!$event || !$level) {
            die("Event or level not found");
        }
        
        // Get all rounds in this level
        $rounds = $this->db->fetchAll(
            "SELECT * FROM rounds WHERE level_id = ? ORDER BY `order`",
            [$levelId]
        );
        
        // Get active level formula (if exists) that applies to this level
        $allLevelFormulas = $this->db->fetchAll(
            "SELECT * FROM scoring_formula 
             WHERE event_id = ? AND formula_type = 'level' AND is_active = 1
             ORDER BY updated_at DESC, created_at DESC",
            [$eventId]
        );
        
        // Find the formula that applies to this level
        $levelFormula = null;
        $specificFormula = null; // Formula that specifically includes this level
        $generalFormula = null;  // Formula that applies to all levels
        
        foreach ($allLevelFormulas as $formula) {
            $levelIds = !empty($formula['level_ids']) ? json_decode($formula['level_ids'], true) : null;
            
            // If level_ids is null/empty, formula applies to all levels
            if ($levelIds === null || empty($levelIds)) {
                if (!$generalFormula) {
                    $generalFormula = $formula; // Use first general formula found
                }
            } elseif (is_array($levelIds) && in_array($levelId, $levelIds)) {
                // This level is specifically included - prioritize this
                if (!$specificFormula) {
                    $specificFormula = $formula;
                }
            }
        }
        
        // Use the most specific formula available
        $levelFormula = $specificFormula ?: $generalFormula;
        
        // Get contestants - filter based on elimination from previous levels
        // Find the previous level (if any)
        $previousLevel = $this->db->fetchOne(
            "SELECT * FROM event_levels 
             WHERE event_id = ? AND `order` < ? 
             ORDER BY `order` DESC LIMIT 1",
            [$eventId, $level['order']]
        );
        
        // Determine which contestants to show
        if ($previousLevel && $previousLevel['advance_count'] !== null) {
            // Previous level had elimination - show contestants who participated in this level (have scores)
            $contestants = $this->db->fetchAll(
                "SELECT DISTINCT c.* 
                 FROM contestants c
                 JOIN scores s ON c.id = s.contestant_id
                 JOIN rounds r ON s.round_id = r.id
                 WHERE c.event_id = ? AND c.status = 'Active' AND r.level_id = ?
                 ORDER BY CAST(c.contestant_number AS UNSIGNED)",
                [$eventId, $levelId]
            );
        } else {
            // First level OR previous level had no elimination (advance_count = NULL) - show all active contestants
            $contestants = $this->db->fetchAll(
                "SELECT * FROM contestants WHERE event_id = ? AND status = 'Active' ORDER BY CAST(contestant_number AS UNSIGNED)",
                [$eventId]
            );
        }
        
        // Load any deductions for this level report
        $levelDeductions = $this->getReportDeductions($eventId, 'level', $levelId);

        // Calculate scores per round - display average per round, calculate level total using formula
        $contestantData = [];
        foreach ($contestants as $contestant) {
            $contestantData[$contestant['id']] = [
                'contestant' => $contestant,
                'round_averages' => [], // Display average per round
                'round_totals' => [],  // Keep totals for formula calculation if needed
                'level_total' => 0
            ];
            
            foreach ($rounds as $round) {
                // Use raw score totals (sum of criteria raw_score) per judge - display raw, not percentage
                $scores = $this->db->fetchAll(
                    "SELECT s.id,
                            (SELECT COALESCE(SUM(sd.raw_score), 0) FROM score_details sd WHERE sd.score_id = s.id) as raw_total
                     FROM scores s
                     JOIN judges j ON s.judge_id = j.id
                     WHERE s.round_id = ? AND s.contestant_id = ? AND s.is_submitted = 1",
                    [$round['id'], $contestant['id']]
                );
                
                $roundTotal = 0;
                $roundRawScores = [];
                foreach ($scores as $score) {
                    $raw = (float)($score['raw_total'] ?? 0);
                    $roundRawScores[] = $raw;
                    $roundTotal += $raw;
                }
                // Round average = average of judges' raw totals (raw score, not percentage)
                $roundAverage = count($roundRawScores) > 0 ? $roundTotal / count($roundRawScores) : 0;
                
                $contestantData[$contestant['id']]['round_averages'][$round['id']] = $roundAverage;
                $contestantData[$contestant['id']]['round_totals'][$round['id']] = $roundTotal;
            }
            
            // Calculate level total using formula or default (sum of averages)
            if ($levelFormula && !empty($levelFormula['formula_expression'])) {
                // Formula can work with round_averages or round_totals
                $contestantData[$contestant['id']]['level_total'] = $this->evaluateLevelFormula(
                    $levelFormula['formula_expression'],
                    $contestantData[$contestant['id']]['round_averages'], // Use averages for formula
                    $rounds
                );
            } else {
                // Default: Average of round scores — add all round averages, then divide by number of rounds
                $roundAverages = $contestantData[$contestant['id']]['round_averages'];
                $nRounds = count($roundAverages);
                $contestantData[$contestant['id']]['level_total'] = $nRounds > 0
                    ? array_sum($roundAverages) / $nRounds
                    : 0;
            }

            $deductionInfo = $levelDeductions[$contestant['id']] ?? null;
            $deduction = $deductionInfo ? (float)$deductionInfo['deduction'] : 0;
            $deductionStatus = $deductionInfo['status'] ?? 'none';
            $appliedDeduction = $deductionStatus === 'granted' ? $deduction : 0;
            
            $contestantData[$contestant['id']]['deduction'] = $deduction;
            $contestantData[$contestant['id']]['deduction_status'] = $deductionStatus;
            $contestantData[$contestant['id']]['deduction_reason'] = $deductionInfo['reason'] ?? null;
            $contestantData[$contestant['id']]['adjusted_total'] = max(0, $contestantData[$contestant['id']]['level_total'] - $appliedDeduction);
        }
        
        // Sort by adjusted total descending
        uasort($contestantData, function($a, $b) {
            return $b['adjusted_total'] <=> $a['adjusted_total'];
        });
        $sorted = array_values($contestantData);
        $n = count($sorted);
        
        // Fractional (Olympic) ranking: tie = same adjusted total (within 0.001). Tied get average of positions (e.g. 3.5); next rank skips.
        $i = 0;
        while ($i < $n) {
            $j = $i;
            while ($j + 1 < $n && abs((float)$sorted[$j + 1]['adjusted_total'] - (float)$sorted[$j]['adjusted_total']) <= 0.001) {
                $j++;
            }
            $rank = (($i + 1) + ($j + 1)) / 2;
            for ($k = $i; $k <= $j; $k++) {
                $sorted[$k]['rank'] = $rank;
            }
            $i = $j + 1;
        }
        $rankedData = $sorted;
        // Order by contestant number for display
        usort($rankedData, function($a, $b) {
            return (int)$a['contestant']['contestant_number'] <=> (int)$b['contestant']['contestant_number'];
        });
        
        $this->view('reports/level', [
            'event' => $event,
            'level' => $level,
            'rounds' => $rounds,
            'contestantData' => $rankedData,
            'formula' => $levelFormula
        ]);
    }
    
    /**
     * Report Per Judge
     * Shows: All scores submitted by a specific judge across all rounds
     * Only Technical Admin and Super Admin can access reports
     */
    public function judgeReport($eventId, $judgeId) {
        $this->restrictJudges();
        $this->requireEventAccess($eventId);
        
        $currentRole = Session::get('role_name');
        // Only Event Technical Admin and Super Admin can view reports
        // Event Organizer CANNOT view reports (restricted)
        if (!in_array($currentRole, ['Super Admin', 'Event Technical Admin'])) {
            $this->accessDenied("Access denied. Only Technical Admin can view reports.");
        }
        
        $event = $this->db->fetchOne("SELECT * FROM events WHERE id = ?", [$eventId]);
        $judge = $this->db->fetchOne(
            "SELECT j.*, u.full_name as judge_name
             FROM judges j
             JOIN users u ON j.user_id = u.id
             WHERE j.id = ? AND j.event_id = ?",
            [$judgeId, $eventId]
        );
        
        if (!$event || !$judge) {
            die("Event or judge not found");
        }
        
        // Get all rounds this judge is assigned to
        $rounds = $this->db->fetchAll(
            "SELECT r.*, el.name as level_name, el.order as level_order
             FROM rounds r
             JOIN event_levels el ON r.level_id = el.id
             JOIN judge_assignments ja ON r.id = ja.round_id
             WHERE ja.judge_id = ? AND ja.is_active = 1 AND el.event_id = ?
             ORDER BY el.order, r.order",
            [$judgeId, $eventId]
        );
        
        // Get all scores from this judge
        $scores = $this->db->fetchAll(
            "SELECT s.*, c.contestant_number, c.name, r.name as round_name, 
                    el.name as level_name, el.order as level_order
             FROM scores s
             JOIN contestants c ON s.contestant_id = c.id
             JOIN rounds r ON s.round_id = r.id
             JOIN event_levels el ON r.level_id = el.id
             WHERE s.judge_id = ? AND s.is_submitted = 1 AND el.event_id = ?
             ORDER BY el.order, r.order, CAST(c.contestant_number AS UNSIGNED)",
            [$judgeId, $eventId]
        );
        
        // Get criteria for each round
        $roundCriteria = [];
        foreach ($rounds as $round) {
            $criteria = $this->db->fetchAll(
                "SELECT c.*, cw.weight 
                 FROM criteria c
                 JOIN criteria_weights cw ON c.id = cw.criteria_id
                 WHERE cw.round_id = ?
                 ORDER BY c.id, c.name",
                [$round['id']]
            );
            $roundCriteria[$round['id']] = $criteria;
        }
        
        // Get score details (raw scores per criteria) for all scores
        $scoreIds = array_column($scores, 'id');
        $scoreDetails = [];
        if (!empty($scoreIds)) {
            $placeholders = implode(',', array_fill(0, count($scoreIds), '?'));
            $details = $this->db->fetchAll(
                "SELECT sd.*, c.name as criteria_name, c.max_score
                 FROM score_details sd
                 JOIN criteria c ON sd.criteria_id = c.id
                 WHERE sd.score_id IN ($placeholders)
                 ORDER BY sd.score_id, c.name",
                $scoreIds
            );
            
            // Group by score_id
            foreach ($details as $detail) {
                $scoreDetails[$detail['score_id']][] = $detail;
            }
        }
        
        // Group by round and attach score details
        $roundScores = [];
        foreach ($scores as $score) {
            $roundId = $score['round_id'];
            if (!isset($roundScores[$roundId])) {
                $roundScores[$roundId] = [
                    'round' => [
                        'id' => $roundId,
                        'name' => $score['round_name'],
                        'level_name' => $score['level_name']
                    ],
                    'criteria' => $roundCriteria[$roundId] ?? [],
                    'scores' => []
                ];
            }
            // Attach score details to each score
            $score['details'] = $scoreDetails[$score['id']] ?? [];
            $roundScores[$roundId]['scores'][] = $score;
        }
        
        $this->view('reports/judge', [
            'event' => $event,
            'judge' => $judge,
            'roundScores' => $roundScores
        ]);
    }
    
    /**
     * Finals Report
     * Shows: All total raw scores per round, ranking, winner calculation
     * Calculation: All scores per round → Total → Average per round → Total per level
     * Only Technical Admin and Super Admin can access reports
     */
    public function finalsReport($eventId) {
        $this->restrictJudges();
        $this->requireEventAccess($eventId);
        
        $currentRole = Session::get('role_name');
        // Only Event Technical Admin and Super Admin can view reports
        // Event Organizer CANNOT view reports (restricted)
        if (!in_array($currentRole, ['Super Admin', 'Event Technical Admin'])) {
            $this->accessDenied("Access denied. Only Technical Admin can view reports.");
        }
        
        $event = $this->db->fetchOne("SELECT * FROM events WHERE id = ?", [$eventId]);
        if (!$event) {
            die("Event not found");
        }
        
        // Get all levels and rounds
        $levels = $this->db->fetchAll(
            "SELECT * FROM event_levels WHERE event_id = ? ORDER BY `order`",
            [$eventId]
        );
        
        $allRounds = [];
        foreach ($levels as $level) {
        $rounds = $this->db->fetchAll(
            "SELECT * FROM rounds WHERE level_id = ? ORDER BY `order`",
                [$level['id']]
            );
            $allRounds[$level['id']] = $rounds;
        }
        
        // Determine the final level (last level in order)
        $finalLevel = null;
        if (!empty($levels)) {
            $finalLevel = end($levels); // Get the last level (highest order)
        }
        
        // Get contestants - filter by qualification if elimination occurred
        // IMPORTANT: If there was an elimination round (e.g., semi-final where only top 5 advance),
        // only those who qualified for the final level should appear in the final report.
        // Example: If semi-final had advance_count=5, only those 5 contestants should be in the final report.
        if ($finalLevel) {
            // Check if any contestants have qualified for the final level
            // This happens when a previous level had elimination (advance_count set)
            $qualifiedCount = $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM contestants 
                 WHERE event_id = ? AND status = 'Active' AND qualified_for_level_id = ?",
                [$eventId, $finalLevel['id']]
            )['count'];
            
            if ($qualifiedCount > 0) {
                // Only show contestants who qualified for the final level
                // This ensures only those who passed elimination rounds appear in the final report
                $contestants = $this->db->fetchAll(
                    "SELECT * FROM contestants 
                     WHERE event_id = ? AND status = 'Active' AND qualified_for_level_id = ? 
                     ORDER BY CAST(contestant_number AS UNSIGNED)",
                    [$eventId, $finalLevel['id']]
                );
            } else {
                // No one qualified yet, or no elimination occurred (all contestants proceed)
                // Show all active contestants (fallback for events without elimination)
                $contestants = $this->db->fetchAll(
                    "SELECT * FROM contestants WHERE event_id = ? AND status = 'Active' ORDER BY CAST(contestant_number AS UNSIGNED)",
                    [$eventId]
                );
            }
        } else {
            // No levels defined, show all active contestants
            $contestants = $this->db->fetchAll(
                "SELECT * FROM contestants WHERE event_id = ? AND status = 'Active' ORDER BY CAST(contestant_number AS UNSIGNED)",
                [$eventId]
            );
        }
        
        // Get active final formula (if exists)
        $finalFormula = $this->db->fetchOne(
            "SELECT * FROM scoring_formula 
             WHERE event_id = ? AND formula_type = 'final' AND is_active = 1
             ORDER BY created_at DESC LIMIT 1",
            [$eventId]
        );
        
        // Get all level formulas for this event
        $allLevelFormulas = $this->db->fetchAll(
            "SELECT * FROM scoring_formula 
             WHERE event_id = ? AND formula_type = 'level' AND is_active = 1
             ORDER BY updated_at DESC, created_at DESC",
            [$eventId]
        );
        
        // Build a map of level_id => formula for quick lookup
        $levelFormulaMap = [];
        foreach ($levels as $level) {
            $levelId = $level['id'];
            $specificFormula = null;
            $generalFormula = null;
            
            foreach ($allLevelFormulas as $formula) {
                $levelIds = !empty($formula['level_ids']) ? json_decode($formula['level_ids'], true) : null;
                
                // If level_ids is null/empty, formula applies to all levels
                if ($levelIds === null || empty($levelIds)) {
                    if (!$generalFormula) {
                        $generalFormula = $formula;
                    }
                } elseif (is_array($levelIds) && in_array($levelId, $levelIds)) {
                    // This level is specifically included - prioritize this
                    if (!$specificFormula) {
                        $specificFormula = $formula;
                    }
                }
            }
            
            // Use the most specific formula available
            $levelFormulaMap[$levelId] = $specificFormula ?: $generalFormula;
        }
        
        // Load any deductions for final report
        $finalDeductions = $this->getReportDeductions($eventId, 'final', 0);

        // Calculate: All scores per round → Total → Average per round → Total per level (using formula)
        $contestantData = [];
        foreach ($contestants as $contestant) {
            $contestantData[$contestant['id']] = [
                'contestant' => $contestant,
                'round_totals' => [],
                'round_averages' => [],
                'level_totals' => [],
                'final_total' => 0
            ];
            
            foreach ($levels as $level) {
                $roundAverages = [];
                $roundTotals = [];
                
                if (isset($allRounds[$level['id']])) {
                    $rounds = $allRounds[$level['id']];
                    
                    foreach ($rounds as $round) {
                        // Use raw score totals (sum of criteria raw_score) per judge, same as round/level reports
                        $scores = $this->db->fetchAll(
                            "SELECT s.id,
                                    (SELECT COALESCE(SUM(sd.raw_score), 0) FROM score_details sd WHERE sd.score_id = s.id) as raw_total
                             FROM scores s
                             WHERE s.round_id = ? AND s.contestant_id = ? AND s.is_submitted = 1",
                            [$round['id'], $contestant['id']]
                        );
                        $roundTotal = 0;
                        foreach ($scores as $score) {
                            $roundTotal += (float)($score['raw_total'] ?? 0);
                        }
                        $roundAverage = count($scores) > 0 ? $roundTotal / count($scores) : 0;
                        
                        $roundAverages[$round['id']] = $roundAverage;
                        $roundTotals[$round['id']] = $roundTotal;
                        
                        $contestantData[$contestant['id']]['round_totals'][$round['id']] = $roundTotal;
                        $contestantData[$contestant['id']]['round_averages'][$round['id']] = $roundAverage;
                    }
                }
                
                // Calculate level total using formula or default (Total Average Score = average of round averages)
                $levelFormula = $levelFormulaMap[$level['id']] ?? null;
                if ($levelFormula && !empty($levelFormula['formula_expression'])) {
                    // Use the level formula to calculate level total
                    $levelTotal = $this->evaluateLevelFormula(
                        $levelFormula['formula_expression'],
                        $roundAverages, // Use averages for formula
                        $allRounds[$level['id']] ?? []
                    );
                } else {
                    // Default: Total Average Score = average of round averages (same as level report)
                    $nRounds = count($roundAverages);
                    $levelTotal = $nRounds > 0 ? array_sum($roundAverages) / $nRounds : 0;
                }
                
                $contestantData[$contestant['id']]['level_totals'][$level['id']] = $levelTotal;
            }
            
            // Calculate final total using custom formula or default
            if ($finalFormula) {
                $finalTotal = $this->evaluateFormula(
                    $finalFormula,
                    $contestantData[$contestant['id']],
                    $allRounds,
                    $levels
                );
                $contestantData[$contestant['id']]['final_total'] = $finalTotal;
            } else {
                // Default: Average of level totals (so combining two levels = (Pre Pageant Total Avg + Pageant Total Avg) / 2)
                $levelTotals = $contestantData[$contestant['id']]['level_totals'];
                $nLevels = count($levelTotals);
                $contestantData[$contestant['id']]['final_total'] = $nLevels > 0
                    ? array_sum($levelTotals) / $nLevels
                    : 0;
            }
            
            $deductionInfo = $finalDeductions[$contestant['id']] ?? null;
            $deduction = $deductionInfo ? (float)$deductionInfo['deduction'] : 0;
            $deductionStatus = $deductionInfo['status'] ?? 'none';
            $appliedDeduction = $deductionStatus === 'granted' ? $deduction : 0;
            
            $contestantData[$contestant['id']]['deduction'] = $deduction;
            $contestantData[$contestant['id']]['deduction_status'] = $deductionStatus;
            $contestantData[$contestant['id']]['deduction_reason'] = $deductionInfo['reason'] ?? null;
            $contestantData[$contestant['id']]['adjusted_total'] = max(0, $contestantData[$contestant['id']]['final_total'] - $appliedDeduction);
        }
        
        // Sort by adjusted total descending
        uasort($contestantData, function($a, $b) {
            return $b['adjusted_total'] <=> $a['adjusted_total'];
        });
        $sorted = array_values($contestantData);
        $n = count($sorted);
        
        // Fractional (Olympic) ranking: tie = same adjusted total (within 0.001). Tied get average of positions (e.g. 3.5); next rank skips.
        $i = 0;
        while ($i < $n) {
            $j = $i;
            while ($j + 1 < $n && abs((float)$sorted[$j + 1]['adjusted_total'] - (float)$sorted[$j]['adjusted_total']) <= 0.001) {
                $j++;
            }
            $rank = (($i + 1) + ($j + 1)) / 2;
            for ($k = $i; $k <= $j; $k++) {
                $sorted[$k]['rank'] = $rank;
            }
            $i = $j + 1;
        }
        $rankedData = $sorted;
        // Order by contestant number for display
        usort($rankedData, function($a, $b) {
            return (int)$a['contestant']['contestant_number'] <=> (int)$b['contestant']['contestant_number'];
        });
        
        // Debug: Log ranking calculation (remove in production)
        // error_log("Finals Ranking: " . json_encode(array_map(function($d) { 
        //     return ['rank' => $d['rank'], 'total' => $d['final_total'], 'name' => $d['contestant']['name']]; 
        // }, $rankedData)));
        
        $this->view('reports/finals', [
            'event' => $event,
            'levels' => $levels,
            'allRounds' => $allRounds,
            'contestantData' => $rankedData,
            'formula' => $finalFormula
        ]);
    }

    /**
     * Request deduction to round report totals (Tech Admin only)
     */
    public function applyRoundDeduction($eventId, $roundId) {
        $this->restrictJudges();
        $this->requireEventAccess($eventId);
        $this->checkCSRF();
        
        $currentRole = Session::get('role_name');
        if (!in_array($currentRole, ['Super Admin', 'Event Technical Admin'])) {
            $this->json(['success' => false, 'message' => 'Access denied'], 403);
            return;
        }
        
        $this->preventFinishedEventModification($eventId);
        
        $round = $this->db->fetchOne(
            "SELECT r.*, el.event_id FROM rounds r JOIN event_levels el ON r.level_id = el.id WHERE r.id = ?",
            [$roundId]
        );
        if (!$round || (int)$round['event_id'] !== (int)$eventId) {
            $this->json(['success' => false, 'message' => 'Round not found'], 404);
            return;
        }
        
        $contestantId = (int)($_POST['contestant_id'] ?? 0);
        $deduction = isset($_POST['deduction']) ? (float)$_POST['deduction'] : 0;
        $reason = trim((string)($_POST['reason'] ?? ''));
        
        if ($contestantId <= 0) {
            $this->json(['success' => false, 'message' => 'Contestant not found'], 400);
            return;
        }
        if ($deduction <= 0) {
            $this->json(['success' => false, 'message' => 'Deduction amount must be greater than 0.'], 400);
            return;
        }
        if (empty($reason)) {
            $this->json(['success' => false, 'message' => 'Reason is required.'], 400);
            return;
        }
        
        $requestId = $this->requestReportDeduction($eventId, 'round', (int)$roundId, $contestantId, $deduction, $reason);
        $this->notifyReportDeductionRequest($eventId, 'round', (int)$roundId, $contestantId, $deduction, $reason, $requestId);
        $this->json(['success' => true, 'message' => 'Deduction request sent to organizer']);
    }

    /**
     * Request deduction to level report totals (Tech Admin only)
     */
    public function applyLevelDeduction($eventId, $levelId) {
        $this->restrictJudges();
        $this->requireEventAccess($eventId);
        $this->checkCSRF();
        
        $currentRole = Session::get('role_name');
        if (!in_array($currentRole, ['Super Admin', 'Event Technical Admin'])) {
            $this->json(['success' => false, 'message' => 'Access denied'], 403);
            return;
        }
        
        $this->preventFinishedEventModification($eventId);
        
        $level = $this->db->fetchOne(
            "SELECT * FROM event_levels WHERE id = ? AND event_id = ?",
            [$levelId, $eventId]
        );
        if (!$level) {
            $this->json(['success' => false, 'message' => 'Level not found'], 404);
            return;
        }
        
        $contestantId = (int)($_POST['contestant_id'] ?? 0);
        $deduction = isset($_POST['deduction']) ? (float)$_POST['deduction'] : 0;
        $reason = trim((string)($_POST['reason'] ?? ''));
        
        if ($contestantId <= 0) {
            $this->json(['success' => false, 'message' => 'Contestant not found'], 400);
            return;
        }
        if ($deduction <= 0) {
            $this->json(['success' => false, 'message' => 'Deduction amount must be greater than 0.'], 400);
            return;
        }
        if (empty($reason)) {
            $this->json(['success' => false, 'message' => 'Reason is required.'], 400);
            return;
        }
        
        $requestId = $this->requestReportDeduction($eventId, 'level', (int)$levelId, $contestantId, $deduction, $reason);
        $this->notifyReportDeductionRequest($eventId, 'level', (int)$levelId, $contestantId, $deduction, $reason, $requestId);
        $this->json(['success' => true, 'message' => 'Deduction request sent to organizer']);
    }

    /**
     * Request deduction to final report totals (Tech Admin only)
     */
    public function applyFinalDeduction($eventId) {
        $this->restrictJudges();
        $this->requireEventAccess($eventId);
        $this->checkCSRF();
        
        $currentRole = Session::get('role_name');
        if (!in_array($currentRole, ['Super Admin', 'Event Technical Admin'])) {
            $this->json(['success' => false, 'message' => 'Access denied'], 403);
            return;
        }
        
        $this->preventFinishedEventModification($eventId);
        
        $contestantId = (int)($_POST['contestant_id'] ?? 0);
        $deduction = isset($_POST['deduction']) ? (float)$_POST['deduction'] : 0;
        $reason = trim((string)($_POST['reason'] ?? ''));
        
        if ($contestantId <= 0) {
            $this->json(['success' => false, 'message' => 'Contestant not found'], 400);
            return;
        }
        if ($deduction <= 0) {
            $this->json(['success' => false, 'message' => 'Deduction amount must be greater than 0.'], 400);
            return;
        }
        if (empty($reason)) {
            $this->json(['success' => false, 'message' => 'Reason is required.'], 400);
            return;
        }
        
        $requestId = $this->requestReportDeduction($eventId, 'final', 0, $contestantId, $deduction, $reason);
        $this->notifyReportDeductionRequest($eventId, 'final', 0, $contestantId, $deduction, $reason, $requestId);
        $this->json(['success' => true, 'message' => 'Deduction request sent to organizer']);
    }

    /**
     * Respond to report deduction request (Organizer only)
     */
    public function respondReportDeduction($eventId, $deductionId) {
        $this->requireRoles(['Event Organizer']);
        
        $userId = Session::get('user_id');
        $action = $_POST['action'] ?? '';
        $notificationId = (int)($_POST['notification_id'] ?? 0);
        
        if (!in_array($action, ['grant', 'deny'])) {
            $this->json(['success' => false, 'message' => 'Invalid action'], 400);
            return;
        }
        
        $deduction = $this->db->fetchOne(
            "SELECT * FROM report_deductions WHERE id = ? AND event_id = ?",
            [$deductionId, $eventId]
        );
        
        if (!$deduction) {
            $this->json(['success' => false, 'message' => 'Request not found'], 404);
            return;
        }
        
        // Verify organizer has access to this event (Super Admin allowed by requireRoles)
        $hasAccess = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM user_event_assignments 
             WHERE user_id = ? AND event_id = ? AND is_active = 1",
            [$userId, $eventId]
        )['count'] > 0;
        
        $currentRole = Session::get('role_name');
        if (!$hasAccess && $currentRole !== 'Super Admin') {
            $this->json(['success' => false, 'message' => 'You do not have access to this event'], 403);
            return;
        }
        
        $this->preventFinishedEventModification($eventId);
        
        $newStatus = $action === 'grant' ? 'granted' : 'denied';
        $this->db->query(
            "UPDATE report_deductions SET 
             status = ?,
             responded_by = ?,
             responded_at = NOW(),
             updated_by = ?
             WHERE id = ?",
            [$newStatus, $userId, $userId, $deductionId]
        );
        
        // Notify requester
        if (!empty($deduction['requested_by'])) {
            $type = $action === 'grant' ? 'report_deduction_granted' : 'report_deduction_denied';
            $title = $action === 'grant' ? 'Report Deduction Granted' : 'Report Deduction Denied';
            $message = $action === 'grant'
                ? 'Your report deduction request has been granted.'
                : 'Your report deduction request has been denied.';
            
            $this->db->query(
                "INSERT INTO notifications (user_id, type, title, message, related_type, related_id)
                 VALUES (?, ?, ?, ?, 'report_deduction', ?)",
                [$deduction['requested_by'], $type, $title, $message, $deductionId]
            );
        }
        
        if ($notificationId > 0) {
            $this->db->query(
                "UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = ? AND user_id = ?",
                [$notificationId, $userId]
            );
        }
        
        $this->json(['success' => true, 'message' => $action === 'grant' ? 'Request granted' : 'Request denied']);
    }

    /**
     * Load deductions for a report scope (round/level/final)
     */
    private function getReportDeductions($eventId, $scopeType, $scopeId) {
        $rows = $this->db->fetchAll(
            "SELECT contestant_id, deduction, reason, status
             FROM report_deductions
             WHERE event_id = ? AND scope_type = ? AND scope_id = ?",
            [$eventId, $scopeType, $scopeId]
        );
        
        $map = [];
        foreach ($rows as $row) {
            $map[$row['contestant_id']] = [
                'deduction' => (float)($row['deduction'] ?? 0),
                'reason' => $row['reason'] ?? null,
                'status' => $row['status'] ?? 'requested'
            ];
        }
        return $map;
    }

    /**
     * Save or update a report deduction request (pending organizer approval)
     */
    private function requestReportDeduction($eventId, $scopeType, $scopeId, $contestantId, $deduction, $reason) {
        $userId = Session::get('user_id');
        
        $this->db->query(
            "INSERT INTO report_deductions 
             (event_id, scope_type, scope_id, contestant_id, deduction, reason, status, requested_by, requested_at, created_by, updated_by)
             VALUES (?, ?, ?, ?, ?, ?, 'requested', ?, NOW(), ?, ?)
             ON DUPLICATE KEY UPDATE 
                deduction = VALUES(deduction),
                reason = VALUES(reason),
                status = 'requested',
                requested_by = VALUES(requested_by),
                requested_at = NOW(),
                updated_by = VALUES(updated_by)",
            [$eventId, $scopeType, $scopeId, $contestantId, $deduction, $reason, $userId, $userId, $userId]
        );
        
        $row = $this->db->fetchOne(
            "SELECT id FROM report_deductions WHERE event_id = ? AND scope_type = ? AND scope_id = ? AND contestant_id = ?",
            [$eventId, $scopeType, $scopeId, $contestantId]
        );
        
        return $row['id'] ?? null;
    }

    /**
     * Notify event organizers about a report deduction request
     */
    private function notifyReportDeductionRequest($eventId, $scopeType, $scopeId, $contestantId, $deduction, $reason, $requestId) {
        if (empty($requestId)) {
            return;
        }
        
        $contestant = $this->db->fetchOne(
            "SELECT contestant_number, name FROM contestants WHERE id = ?",
            [$contestantId]
        );
        
        $scopeLabel = 'Final';
        if ($scopeType === 'round') {
            $round = $this->db->fetchOne("SELECT name FROM rounds WHERE id = ?", [$scopeId]);
            $scopeLabel = $round['name'] ?? 'Round';
        } elseif ($scopeType === 'level') {
            $level = $this->db->fetchOne("SELECT name FROM event_levels WHERE id = ?", [$scopeId]);
            $scopeLabel = $level['name'] ?? 'Level';
        }
        
        $title = 'Report Deduction Request';
        $message = "Admin " . Session::get('full_name') . " is requesting a deduction of {$deduction} points for Contestant #{$contestant['contestant_number']} ({$contestant['name']}) in {$scopeLabel}. Reason: {$reason}";
        
        $organizers = $this->getEventOrganizers($eventId);
        foreach ($organizers as $organizer) {
            $this->db->query(
                "INSERT INTO notifications (user_id, type, title, message, related_type, related_id)
                 VALUES (?, 'report_deduction_request', ?, ?, 'report_deduction', ?)",
                [$organizer['id'], $title, $message, $requestId]
            );
        }
    }

    /**
     * Get event organizers for notifications
     */
    private function getEventOrganizers($eventId) {
        $organizers = $this->db->fetchAll(
            "SELECT u.id, u.full_name
             FROM users u
             JOIN roles r ON u.role_id = r.id
             JOIN user_event_assignments uea ON u.id = uea.user_id
             WHERE r.name = 'Event Organizer'
             AND uea.event_id = ?
             AND uea.is_active = 1
             AND u.is_active = 1",
            [$eventId]
        );
        
        if (empty($organizers)) {
            $organizers = $this->db->fetchAll(
                "SELECT u.id, u.full_name
                 FROM users u
                 JOIN roles r ON u.role_id = r.id
                 WHERE r.name = 'Event Organizer'
                 AND u.is_active = 1"
            );
        }
        
        return $organizers;
    }
    
    /**
     * Evaluate a level formula for round averages or totals
     * For level formulas, we work with round averages (display) or totals
     */
    private function evaluateLevelFormula($formulaExpression, $roundData, $rounds) {
        if (empty($roundData)) {
            return 0;
        }
        
        $expression = $formulaExpression;
        
        // Convert round data array to indexed array for formula evaluation
        $roundDataArray = array_values($roundData);
        
        // Replace formula functions with PHP equivalents using regex
        // Support both round_totals and round_averages (they work the same way)
        // Replace SUM(round_totals) or SUM(round_averages)
        $expression = preg_replace('/SUM\s*\(\s*round_totals\s*\)/i', 'array_sum($roundDataArray)', $expression);
        $expression = preg_replace('/SUM\s*\(\s*round_averages\s*\)/i', 'array_sum($roundDataArray)', $expression);
        
        // Replace AVG(round_totals) or AVG(round_averages) or AVG(round_scores)
        $avgReplacement = 'count($roundDataArray) > 0 ? array_sum($roundDataArray) / count($roundDataArray) : 0';
        $expression = preg_replace('/AVG\s*\(\s*round_totals\s*\)/i', $avgReplacement, $expression);
        $expression = preg_replace('/AVG\s*\(\s*round_averages\s*\)/i', $avgReplacement, $expression);
        $expression = preg_replace('/AVG\s*\(\s*round_scores\s*\)/i', $avgReplacement, $expression);
        
        // Replace COUNT(round_totals) or COUNT(round_averages) or COUNT(round_scores)
        $expression = preg_replace('/COUNT\s*\(\s*round_totals\s*\)/i', 'count($roundDataArray)', $expression);
        $expression = preg_replace('/COUNT\s*\(\s*round_averages\s*\)/i', 'count($roundDataArray)', $expression);
        $expression = preg_replace('/COUNT\s*\(\s*round_scores\s*\)/i', 'count($roundDataArray)', $expression);
        
        // Replace MAX(round_totals) or MAX(round_averages) or MAX(round_scores)
        $maxReplacement = 'count($roundDataArray) > 0 ? max($roundDataArray) : 0';
        $expression = preg_replace('/MAX\s*\(\s*round_totals\s*\)/i', $maxReplacement, $expression);
        $expression = preg_replace('/MAX\s*\(\s*round_averages\s*\)/i', $maxReplacement, $expression);
        $expression = preg_replace('/MAX\s*\(\s*round_scores\s*\)/i', $maxReplacement, $expression);
        
        // Replace MIN(round_totals) or MIN(round_averages) or MIN(round_scores)
        $minReplacement = 'count($roundDataArray) > 0 ? min($roundDataArray) : 0';
        $expression = preg_replace('/MIN\s*\(\s*round_totals\s*\)/i', $minReplacement, $expression);
        $expression = preg_replace('/MIN\s*\(\s*round_averages\s*\)/i', $minReplacement, $expression);
        $expression = preg_replace('/MIN\s*\(\s*round_scores\s*\)/i', $minReplacement, $expression);
        
        // Handle array access like round_totals[0] or round_averages[0] or round_scores[0]
        $expression = preg_replace_callback('/(round_totals|round_averages|round_scores)\s*\[\s*(\d+)\s*\]/i', function($matches) use ($roundDataArray) {
            $index = (int)$matches[2];
            return isset($roundDataArray[$index]) ? $roundDataArray[$index] : 0;
        }, $expression);
        
        // Replace any standalone round_totals, round_averages, or round_scores (without function or array access)
        // This must be done AFTER handling function calls and array access
        $expression = preg_replace('/(?<!\$)\bround_totals\b(?![\[\w\(])/', 'array_sum($roundDataArray)', $expression);
        $expression = preg_replace('/(?<!\$)\bround_averages\b(?![\[\w\(])/', 'array_sum($roundDataArray)', $expression);
        $expression = preg_replace('/(?<!\$)\bround_scores\b(?![\[\w\(])/', 'array_sum($roundDataArray)', $expression);
        
        // Catch-all: Replace any remaining AVG() calls that weren't caught (fallback)
        $expression = preg_replace_callback('/AVG\s*\(\s*([^)]+)\s*\)/i', function($matches) use ($roundDataArray) {
            // If it's any round-related variable, use the average replacement
            if (preg_match('/round_(totals|averages|scores)/i', $matches[1])) {
                return 'count($roundDataArray) > 0 ? array_sum($roundDataArray) / count($roundDataArray) : 0';
            }
            // Otherwise, try to evaluate the parameter
            return '0'; // Safe fallback
        }, $expression);
        
        // Log the expression for debugging
        error_log("Level formula - Original: $formulaExpression, Converted: $expression");
        
        // Evaluate the expression safely
        try {
            $result = eval("return $expression;");
            return round($result, 2); // Round to 2 decimal places
        } catch (Exception $e) {
            error_log("Level formula evaluation error: " . $e->getMessage() . " Original: $formulaExpression, Converted: $expression");
            // Fallback to average
            return count($roundDataArray) > 0 ? round(array_sum($roundDataArray) / count($roundDataArray), 2) : 0;
        } catch (Error $e) {
            error_log("Level formula evaluation error: " . $e->getMessage() . " Original: $formulaExpression, Converted: $expression");
            // Fallback to average
            return count($roundDataArray) > 0 ? round(array_sum($roundDataArray) / count($roundDataArray), 2) : 0;
        }
    }
    
    /**
     * Evaluate a round formula for judge scores
     * For round formulas, we work with judge scores directly
     */
    private function evaluateRoundFormula($formulaExpression, $judgeScores) {
        if (empty($judgeScores)) {
            return 0;
        }
        
        $expression = $formulaExpression;
        
        // Replace formula functions with PHP equivalents using regex for case-insensitive matching
        // For round formulas, judge_scores is the array of judge scores
        
        // Calculate round total (sum of judge scores) for this round
        $roundTotal = array_sum($judgeScores);
        $roundAverage = count($judgeScores) > 0 ? $roundTotal / count($judgeScores) : 0;
        
        // Replace SUM(judge_scores) or SUM(round_scores) or SUM(round_averages) or SUM(round_totals)
        $expression = preg_replace('/SUM\s*\(\s*judge_scores\s*\)/i', 'array_sum($judgeScores)', $expression);
        $expression = preg_replace('/SUM\s*\(\s*round_scores\s*\)/i', 'array_sum($judgeScores)', $expression);
        $expression = preg_replace('/SUM\s*\(\s*round_averages\s*\)/i', 'array_sum($judgeScores)', $expression);
        $expression = preg_replace('/SUM\s*\(\s*round_totals\s*\)/i', '$roundTotal', $expression);
        
        // Replace AVG(judge_scores) or AVG(round_scores) or AVG(round_averages) or AVG(round_totals)
        $avgReplacement = 'count($judgeScores) > 0 ? array_sum($judgeScores) / count($judgeScores) : 0';
        $expression = preg_replace('/AVG\s*\(\s*judge_scores\s*\)/i', $avgReplacement, $expression);
        $expression = preg_replace('/AVG\s*\(\s*round_scores\s*\)/i', $avgReplacement, $expression);
        $expression = preg_replace('/AVG\s*\(\s*round_averages\s*\)/i', $avgReplacement, $expression);
        $expression = preg_replace('/AVG\s*\(\s*round_totals\s*\)/i', '$roundAverage', $expression);
        
        // Replace COUNT functions
        $expression = preg_replace('/COUNT\s*\(\s*judge_scores\s*\)/i', 'count($judgeScores)', $expression);
        $expression = preg_replace('/COUNT\s*\(\s*round_scores\s*\)/i', 'count($judgeScores)', $expression);
        $expression = preg_replace('/COUNT\s*\(\s*round_averages\s*\)/i', 'count($judgeScores)', $expression);
        $expression = preg_replace('/COUNT\s*\(\s*round_totals\s*\)/i', '1', $expression); // For a single round, round_totals count is 1
        
        // Replace MAX functions
        $expression = preg_replace('/MAX\s*\(\s*judge_scores\s*\)/i', 'count($judgeScores) > 0 ? max($judgeScores) : 0', $expression);
        $expression = preg_replace('/MAX\s*\(\s*round_scores\s*\)/i', 'count($judgeScores) > 0 ? max($judgeScores) : 0', $expression);
        $expression = preg_replace('/MAX\s*\(\s*round_averages\s*\)/i', 'count($judgeScores) > 0 ? max($judgeScores) : 0', $expression);
        $expression = preg_replace('/MAX\s*\(\s*round_totals\s*\)/i', '$roundTotal', $expression);
        
        // Replace MIN functions
        $expression = preg_replace('/MIN\s*\(\s*judge_scores\s*\)/i', 'count($judgeScores) > 0 ? min($judgeScores) : 0', $expression);
        $expression = preg_replace('/MIN\s*\(\s*round_scores\s*\)/i', 'count($judgeScores) > 0 ? min($judgeScores) : 0', $expression);
        $expression = preg_replace('/MIN\s*\(\s*round_averages\s*\)/i', 'count($judgeScores) > 0 ? min($judgeScores) : 0', $expression);
        $expression = preg_replace('/MIN\s*\(\s*round_totals\s*\)/i', '$roundTotal', $expression);
        
        // Handle array access FIRST (before standalone references)
        // Handle round_totals[0] - for a single round, round_totals[0] is the round total
        $expression = preg_replace_callback('/round_totals\s*\[\s*(\d+)\s*\]/i', function($matches) use ($roundTotal) {
            $index = (int)$matches[1];
            // For a single round, only index 0 makes sense (the round total)
            return $index === 0 ? $roundTotal : 0;
        }, $expression);
        
        // Handle array access like judge_scores[0] or round_scores[0] or round_averages[0]
        $expression = preg_replace_callback('/(judge_scores|round_scores|round_averages)\s*\[\s*(\d+)\s*\]/i', function($matches) use ($judgeScores) {
            $index = (int)$matches[2];
            return isset($judgeScores[$index]) ? $judgeScores[$index] : 0;
        }, $expression);
        
        // Handle direct reference to round_totals (without function or array access)
        // Replace standalone round_totals with $roundTotal
        // This must be done AFTER handling array access and function calls
        // Match round_totals that is not part of a function call or array access
        // Pattern: round_totals not followed by [ or ( and not preceded by $ (already a variable)
        // Use a more aggressive pattern to catch all remaining instances
        $expression = preg_replace('/(?<!\$)\bround_totals\b(?![\[\w])/', '$roundTotal', $expression);
        
        // Log the expression for debugging
        error_log("Round formula - Original: $formulaExpression, Converted: $expression");
        
        // Evaluate the expression safely
        // Variables are available in eval scope from the function parameters and local variables
        try {
            $result = eval("return $expression;");
            return round($result, 3);
        } catch (Exception $e) {
            error_log("Round formula evaluation error: " . $e->getMessage() . " Original: $formulaExpression, Converted: $expression");
            // Fallback to average
            return count($judgeScores) > 0 ? round(array_sum($judgeScores) / count($judgeScores), 3) : 0;
        } catch (Error $e) {
            error_log("Round formula evaluation error: " . $e->getMessage() . " Original: $formulaExpression, Converted: $expression");
            // Fallback to average
            return count($judgeScores) > 0 ? round(array_sum($judgeScores) / count($judgeScores), 3) : 0;
        }
    }
    
    /**
     * Evaluate a custom formula
     */
    private function evaluateFormula($formula, $contestantData, $allRounds, $levels) {
        $expression = $formula['formula_expression'];
        $roundIds = !empty($formula['round_ids']) ? json_decode($formula['round_ids'], true) : null;
        $levelIds = !empty($formula['level_ids']) ? json_decode($formula['level_ids'], true) : null;
        
        // Filter rounds/levels if specified
        $filteredRoundTotals = [];
        $filteredRoundAverages = [];
        $filteredLevelTotals = [];
        
        if ($roundIds) {
            // Only include specified rounds
            foreach ($roundIds as $roundId) {
                if (isset($contestantData['round_totals'][$roundId])) {
                    $filteredRoundTotals[] = $contestantData['round_totals'][$roundId];
                }
                if (isset($contestantData['round_averages'][$roundId])) {
                    $filteredRoundAverages[] = $contestantData['round_averages'][$roundId];
                }
            }
        } else {
            // Include all rounds
            $filteredRoundTotals = array_values($contestantData['round_totals']);
            $filteredRoundAverages = array_values($contestantData['round_averages']);
        }
        
        if ($levelIds) {
            // Only include specified levels
            foreach ($levelIds as $levelId) {
                if (isset($contestantData['level_totals'][$levelId])) {
                    $filteredLevelTotals[] = $contestantData['level_totals'][$levelId];
                }
            }
        } else {
            // Include all levels
            $filteredLevelTotals = array_values($contestantData['level_totals']);
        }
        
        // Create variables for formula evaluation
        $round_totals = $filteredRoundTotals;
        $round_averages = $filteredRoundAverages;
        $level_totals = $filteredLevelTotals;
        
        // Replace formula functions with PHP equivalents (case-insensitive, allow spaces)
        $avgRoundTotals = 'count($round_totals) > 0 ? array_sum($round_totals) / count($round_totals) : 0';
        $avgRoundAverages = 'count($round_averages) > 0 ? array_sum($round_averages) / count($round_averages) : 0';
        $avgLevelTotals = 'count($level_totals) > 0 ? array_sum($level_totals) / count($level_totals) : 0';
        
        $expression = preg_replace('/SUM\s*\(\s*round_totals\s*\)/i', 'array_sum($round_totals)', $expression);
        $expression = preg_replace('/SUM\s*\(\s*round_averages\s*\)/i', 'array_sum($round_averages)', $expression);
        $expression = preg_replace('/SUM\s*\(\s*level_totals\s*\)/i', 'array_sum($level_totals)', $expression);
        $expression = preg_replace('/AVG\s*\(\s*round_totals\s*\)/i', $avgRoundTotals, $expression);
        $expression = preg_replace('/AVG\s*\(\s*round_averages\s*\)/i', $avgRoundAverages, $expression);
        $expression = preg_replace('/AVG\s*\(\s*level_totals\s*\)/i', $avgLevelTotals, $expression);
        $expression = preg_replace('/COUNT\s*\(\s*round_totals\s*\)/i', 'count($round_totals)', $expression);
        $expression = preg_replace('/COUNT\s*\(\s*round_averages\s*\)/i', 'count($round_averages)', $expression);
        $expression = preg_replace('/COUNT\s*\(\s*level_totals\s*\)/i', 'count($level_totals)', $expression);
        $expression = preg_replace('/MAX\s*\(\s*round_totals\s*\)/i', 'count($round_totals) > 0 ? max($round_totals) : 0', $expression);
        $expression = preg_replace('/MAX\s*\(\s*level_totals\s*\)/i', 'count($level_totals) > 0 ? max($level_totals) : 0', $expression);
        $expression = preg_replace('/MIN\s*\(\s*round_totals\s*\)/i', 'count($round_totals) > 0 ? min($round_totals) : 0', $expression);
        $expression = preg_replace('/MIN\s*\(\s*level_totals\s*\)/i', 'count($level_totals) > 0 ? min($level_totals) : 0', $expression);
        
        // Handle array access like round_totals[0]
        $expression = preg_replace_callback('/round_totals\[(\d+)\]/', function($matches) use ($round_totals) {
            $index = (int)$matches[1];
            return isset($round_totals[$index]) ? $round_totals[$index] : 0;
        }, $expression);
        
        $expression = preg_replace_callback('/round_averages\[(\d+)\]/', function($matches) use ($round_averages) {
            $index = (int)$matches[1];
            return isset($round_averages[$index]) ? $round_averages[$index] : 0;
        }, $expression);
        
        $expression = preg_replace_callback('/level_totals\[(\d+)\]/', function($matches) use ($level_totals) {
            $index = (int)$matches[1];
            return isset($level_totals[$index]) ? $level_totals[$index] : 0;
        }, $expression);
        
        // Evaluate the expression safely
        try {
            $result = eval("return $expression;");
            return round($result, 3);
        } catch (Exception $e) {
            error_log("Formula evaluation error: " . $e->getMessage() . " Expression: " . $expression);
            // Fallback to default
            return array_sum($filteredLevelTotals);
        } catch (Error $e) {
            error_log("Formula evaluation error: " . $e->getMessage() . " Expression: " . $expression);
            // Fallback to default
            return array_sum($filteredLevelTotals);
        }
    }
    
    /**
     * Formula Management (Tech Admin only)
     */
    public function formula($eventId) {
        $roleName = Session::get('role_name');
        
        // Ensure default formula exists
        $this->ensureDefaultFormula($eventId);
        if ($roleName !== 'Event Technical Admin' && $roleName !== 'Super Admin') {
            $this->accessDenied("Only Technical Admins can manage formulas.");
        }
        
        $this->requireEventAccess($eventId);
        
        $event = $this->db->fetchOne("SELECT * FROM events WHERE id = ?", [$eventId]);
        if (!$event) {
            die("Event not found");
        }
        
        // Get existing formulas
        $formulas = $this->db->fetchAll(
            "SELECT sf.*, u1.full_name as created_by_name, u2.full_name as updated_by_name
             FROM scoring_formula sf
             LEFT JOIN users u1 ON sf.created_by = u1.id
             LEFT JOIN users u2 ON sf.updated_by = u2.id
             WHERE sf.event_id = ?
             ORDER BY sf.formula_type, sf.created_at",
            [$eventId]
        );
        
        // Get all levels and rounds for selection
        $allLevels = $this->db->fetchAll(
            "SELECT el.*, 
             (SELECT COUNT(*) FROM rounds r WHERE r.level_id = el.id) as round_count
             FROM event_levels el
             WHERE el.event_id = ?
             ORDER BY el.`order`",
            [$eventId]
        );
        
        $allRounds = $this->db->fetchAll(
            "SELECT r.*, el.name as level_name, el.`order` as level_order
             FROM rounds r
             JOIN event_levels el ON r.level_id = el.id
             WHERE el.event_id = ?
             ORDER BY el.`order`, r.`order`",
            [$eventId]
        );
        
        $this->view('reports/formula', [
            'event' => $event,
            'formulas' => $formulas,
            'allLevels' => $allLevels,
            'allRounds' => $allRounds
        ]);
    }
    
    /**
     * Save Formula (Tech Admin only)
     */
    public function saveFormula($eventId) {
        $roleName = Session::get('role_name');
        if ($roleName !== 'Event Technical Admin' && $roleName !== 'Super Admin') {
            $this->accessDenied("Only Technical Admins can manage formulas.");
        }
        
        $this->requireEventAccess($eventId);
        $this->checkCSRF();
        
        $formulaId = $_POST['formula_id'] ?? null;
        $formulaType = $_POST['formula_type'] ?? 'round';
        $formulaExpression = $_POST['formula_expression'] ?? '';
        $description = $_POST['description'] ?? '';
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $roundIds = $_POST['round_ids'] ?? null; // JSON array
        $levelIds = $_POST['level_ids'] ?? null; // JSON array
        
        $userId = Session::get('user_id');
        
        if ($formulaId) {
            // Update existing
            $this->db->query(
                "UPDATE scoring_formula 
                 SET formula_type = ?, formula_expression = ?, description = ?, is_active = ?, 
                     round_ids = ?, level_ids = ?, updated_by = ?
                 WHERE id = ? AND event_id = ?",
                [$formulaType, $formulaExpression, $description, $isActive, $roundIds, $levelIds, $userId, $formulaId, $eventId]
            );
        } else {
            // Create new
            $this->db->query(
                "INSERT INTO scoring_formula (event_id, formula_type, formula_expression, description, is_active, round_ids, level_ids, created_by)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                [$eventId, $formulaType, $formulaExpression, $description, $isActive, $roundIds, $levelIds, $userId]
            );
        }
        
        Session::set('success_message', 'Formula saved successfully.');
        $this->redirect("/tabulation/events/{$eventId}/reports/formula");
    }
    
    /**
     * Delete Formula (Tech Admin only)
     */
    public function deleteFormula($eventId, $id) {
        $roleName = Session::get('role_name');
        if ($roleName !== 'Event Technical Admin' && $roleName !== 'Super Admin') {
            $this->accessDenied("Only Technical Admins can manage formulas.");
        }
        
        $this->requireEventAccess($eventId);
        $this->checkCSRF();
        
        $formula = $this->db->fetchOne(
            "SELECT id FROM scoring_formula WHERE id = ? AND event_id = ?",
            [$id, $eventId]
        );
        
        if (!$formula) {
            Session::set('error_message', 'Formula not found.');
            $this->redirect("/tabulation/events/{$eventId}/reports/formula");
            return;
        }
        
        $this->db->query("DELETE FROM scoring_formula WHERE id = ? AND event_id = ?", [$id, $eventId]);
        Session::set('success_message', 'Formula deleted successfully.');
        $this->redirect("/tabulation/events/{$eventId}/reports/formula");
    }
    
    /**
     * Ensure default formulas exist for event.
     * Round default: AVG(round_scores) - Average of all judge scores across selected rounds.
     * Final default: SUM(round_totals) - Sum of all round totals.
     */
    private function ensureDefaultFormula($eventId) {
        $userId = Session::get('user_id') ?? null;

        // Default round formula: AVG(round_scores) - (Score₁ + Score₂ + ... + Scoreₙ) ÷ n
        $existingRound = $this->db->fetchOne(
            "SELECT id FROM scoring_formula 
             WHERE event_id = ? AND formula_type = 'round' AND is_active = 1",
            [$eventId]
        );
        if (!$existingRound) {
            $this->db->query(
                "INSERT INTO scoring_formula (event_id, formula_type, formula_expression, description, is_active, created_by)
                 VALUES (?, 'round', 'AVG(round_scores)', 'Average of all judge scores across selected rounds. Mathematical: (Score₁ + Score₂ + ... + Scoreₙ) ÷ n. Example: Scores: 90, 88, 92 → (90 + 88 + 92) ÷ 3 = 90', 1, ?)",
                [$eventId, $userId]
            );
        }

        // Default final formula: AVG(level_totals) so Total Average Score = (Pre Pageant Total Avg + Pageant Total Avg) / 2 (small scale)
        $existingFinal = $this->db->fetchOne(
            "SELECT id FROM scoring_formula 
             WHERE event_id = ? AND formula_type = 'final' AND is_active = 1",
            [$eventId]
        );
        if (!$existingFinal) {
            $this->db->query(
                "INSERT INTO scoring_formula (event_id, formula_type, formula_expression, description, is_active, created_by)
                 VALUES (?, 'final', 'AVG(level_totals)', 'Default: Average of level Total Average Scores — e.g. (Pre Pageant Total Avg + Pageant Total Avg) ÷ 2', 1, ?)",
                [$eventId, $userId]
            );
        }
    }
}
