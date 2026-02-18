<?php
/**
 * Scoring Engine - Core calculation logic
 */

class ScoringEngine {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Calculate weighted score for a single score entry
     * Weights are auto-calculated based on max_score proportionally
     * Example: If criteria have max scores 10, 15, 5 (total 30), weights are 33.33%, 50%, 16.67%
     */
    public function calculateScore($scoreId) {
        $score = $this->db->fetchOne(
            "SELECT s.*, r.id as round_id, r.level_id, el.event_id 
             FROM scores s 
             JOIN rounds r ON s.round_id = r.id 
             JOIN event_levels el ON r.level_id = el.id
             WHERE s.id = ?",
            [$scoreId]
        );
        
        if (!$score) {
            return false;
        }
        
        // Get all criteria assigned to this round with their max scores
        $criteriaWeights = $this->db->fetchAll(
            "SELECT cw.*, c.max_score, c.name as criteria_name
             FROM criteria_weights cw 
             JOIN criteria c ON cw.criteria_id = c.id 
             WHERE cw.round_id = ? AND cw.is_active = 1
             ORDER BY c.id",
            [$score['round_id']]
        );
        
        if (empty($criteriaWeights)) {
            return false;
        }
        
        // Calculate total max score for all criteria in this round
        $totalMaxScore = array_sum(array_column($criteriaWeights, 'max_score'));
        
        if ($totalMaxScore == 0) {
            return false;
        }
        
        // Get score details
        $scoreDetails = $this->db->fetchAll(
            "SELECT * FROM score_details WHERE score_id = ?",
            [$scoreId]
        );
        
        $totalRawScore = 0;
        $totalWeightedScore = 0;
        $normalizedScores = [];
        $criteriaCount = 0;
        
        // Calculate scores: weight is automatically proportional to max_score
        foreach ($criteriaWeights as $cw) {
            $detail = array_filter($scoreDetails, function($sd) use ($cw) {
                return $sd['criteria_id'] == $cw['criteria_id'];
            });
            $detail = reset($detail);
            
            if ($detail) {
                $rawScore = $detail['raw_score'];
                $maxScore = $cw['max_score'];
                
                // Auto-calculate weight based on max_score proportion
                $autoWeight = ($maxScore / $totalMaxScore) * 100;
                
                // Normalize score to percentage
                $normalizedScore = ($rawScore / $maxScore) * 100;
                
                // Store normalized score for average calculation
                $normalizedScores[] = $normalizedScore;
                $criteriaCount++;
                
                // Calculate weighted score contribution
                $weightedScore = ($normalizedScore * $autoWeight) / 100;
                
                // Round to 3 decimal places (0.001 precision)
                $weightedScore = round($weightedScore, 3);
                
                $totalRawScore += $rawScore;
                $totalWeightedScore += $weightedScore;
                
                // Update score detail with auto-calculated weighted score
                $this->db->query(
                    "UPDATE score_details SET weighted_score = ? WHERE id = ?",
                    [$weightedScore, $detail['id']]
                );
                
                // Update criteria_weights table with auto-calculated weight (for display purposes)
                $this->db->query(
                    "UPDATE criteria_weights SET weight = ? WHERE id = ?",
                    [$autoWeight, $cw['id']]
                );
            }
        }
        
        // Final score uses total raw score (sum of criteria raw_score)
        // This keeps deductions based on raw totals.
        $finalScore = $totalRawScore;
        
        // Apply point deduction if any
        $pointDeduction = $score['point_deduction'] ?? 0;
        if ($pointDeduction > 0) {
            $finalScore = max(0, $finalScore - $pointDeduction); // Ensure score doesn't go below 0
        }
        
        // Round to 3 decimal places (0.001 precision)
        $finalScore = round($finalScore, 3);
        
        // Update total score
        $this->db->query(
            "UPDATE scores SET total_score = ? WHERE id = ?",
            [$finalScore, $scoreId]
        );
        
        return $finalScore;
    }
    
    /**
     * Calculate rankings for a round using raw score totals (sum of criteria raw_score per judge).
     */
    public function calculateRankings($roundId) {
        // Get all submitted scores with raw total (sum of score_details.raw_score) per score
        $scores = $this->db->fetchAll(
            "SELECT s.contestant_id, s.judge_id, c.contestant_number, c.name,
                    (SELECT COALESCE(SUM(sd.raw_score), 0) FROM score_details sd WHERE sd.score_id = s.id) as raw_total
             FROM scores s
             JOIN contestants c ON s.contestant_id = c.id
             WHERE s.round_id = ? AND s.is_submitted = 1 AND c.status = 'Active'
             ORDER BY s.contestant_id, s.judge_id",
            [$roundId]
        );
        
        if (empty($scores)) {
            return [];
        }
        
        // Group by contestant and sum/average raw totals
        $contestantScores = [];
        foreach ($scores as $score) {
            $cid = $score['contestant_id'];
            $raw = (float)($score['raw_total'] ?? 0);
            if (!isset($contestantScores[$cid])) {
                $contestantScores[$cid] = [
                    'contestant_id' => $cid,
                    'contestant_number' => $score['contestant_number'],
                    'name' => $score['name'],
                    'scores' => [],
                    'total' => 0,
                    'count' => 0
                ];
            }
            $contestantScores[$cid]['scores'][] = $raw;
            $contestantScores[$cid]['total'] += $raw;
            $contestantScores[$cid]['count']++;
        }
        
        // Calculate averages and round to 3 decimal places
        foreach ($contestantScores as &$cs) {
            $average = $cs['count'] > 0 ? $cs['total'] / $cs['count'] : 0;
            $cs['average'] = round($average, 3);
        }
        
        // Sort by average (descending)
        usort($contestantScores, function($a, $b) {
            if ($a['average'] == $b['average']) {
                return 0;
            }
            return ($a['average'] > $b['average']) ? -1 : 1;
        });
        
        // Fractional (Olympic) ranking: tie = same average (within 0.001). Tied get average of positions (e.g. 3.5); next rank skips.
        $n = count($contestantScores);
        $i = 0;
        while ($i < $n) {
            $j = $i;
            while ($j + 1 < $n && abs((float)$contestantScores[$j + 1]['average'] - (float)$contestantScores[$j]['average']) <= 0.001) {
                $j++;
            }
            $rank = (($i + 1) + ($j + 1)) / 2;
            for ($k = $i; $k <= $j; $k++) {
                $contestantScores[$k]['rank'] = $rank;
            }
            $i = $j + 1;
        }
        return $contestantScores;
    }
    
    /**
     * Save rankings to database
     */
    public function saveRankings($roundId, $rankings) {
        // Get round info
        $round = $this->db->fetchOne(
            "SELECT r.*, el.event_id, el.id as level_id, el.advance_count
             FROM rounds r 
             JOIN event_levels el ON r.level_id = el.id 
             WHERE r.id = ?",
            [$roundId]
        );
        
        if (!$round) {
            return false;
        }
        
        // Delete existing rankings for this round
        $this->db->query(
            "DELETE FROM rankings WHERE round_id = ?",
            [$roundId]
        );
        
        // Insert new rankings
        foreach ($rankings as $ranking) {
            $this->db->query(
                "INSERT INTO rankings (event_id, level_id, round_id, contestant_id, `rank`, total_score, average_score)
                 VALUES (?, ?, ?, ?, ?, ?, ?)",
                [
                    $round['event_id'],
                    $round['level_id'],
                    $roundId,
                    $ranking['contestant_id'],
                    $ranking['rank'],
                    round($ranking['total'], 3),
                    round($ranking['average'], 3)
                ]
            );
        }
        
        // Check if level is completed and process elimination based on overall level rankings
        $this->checkLevelCompletion($round['level_id']);
        
        return true;
    }
    
    /**
     * Check if level is completed and process elimination based on overall level rankings
     */
    private function checkLevelCompletion($levelId) {
        // Get level info
        $level = $this->db->fetchOne(
            "SELECT * FROM event_levels WHERE id = ?",
            [$levelId]
        );
        
        if (!$level) {
            return false;
        }
        
        // If no advance_count is set, all contestants advance automatically
        if ($level['advance_count'] === null) {
            // Get all rounds in this level
            $rounds = $this->db->fetchAll(
                "SELECT id FROM rounds WHERE level_id = ?",
                [$levelId]
            );
            
            // Check if all rounds have completed rankings
            $allRoundsComplete = true;
            foreach ($rounds as $round) {
                $hasRankings = $this->db->fetchOne(
                    "SELECT COUNT(*) as count FROM rankings WHERE round_id = ?",
                    [$round['id']]
                )['count'];
                
                if ($hasRankings == 0) {
                    $allRoundsComplete = false;
                    break;
                }
            }
            
            // If not all rounds are complete, don't process advancement yet
            if (!$allRoundsComplete) {
                return false;
            }
            
            // Calculate overall level rankings (aggregate across all rounds)
            $levelResult = $this->calculateLevelRankings($levelId);
            
            // Check if level is complete
            if (!$levelResult['level_complete']) {
                return [
                    'success' => false,
                    'message' => $levelResult['message'],
                    'incomplete_rounds' => $levelResult['incomplete_rounds'],
                    'total_rounds' => $levelResult['total_rounds'],
                    'completed_rounds' => $levelResult['completed_rounds']
                ];
            }
            
            $levelRankings = $levelResult['rankings'];
            
            if (empty($levelRankings)) {
                return [
                    'success' => false,
                    'message' => 'No rankings available for this level'
                ];
            }
            
            // Get next level
            $nextLevel = $this->db->fetchOne(
                "SELECT * FROM event_levels 
                 WHERE event_id = ? AND `order` > ? 
                 ORDER BY `order` ASC LIMIT 1",
                [$level['event_id'], $level['order']]
            );
            
            if (!$nextLevel) {
                // No next level, nothing to do
                return false;
            }
            
            // When advance_count is NULL, ALL contestants advance
            $qualifiedContestants = $levelRankings;
            
            // Clear previous qualifications for this next level
            $this->db->query(
                "UPDATE contestants SET qualified_for_level_id = NULL 
                 WHERE event_id = ? AND qualified_for_level_id = ?",
                [$level['event_id'], $nextLevel['id']]
            );
            
            // Mark ALL contestants as qualified for next level
            foreach ($qualifiedContestants as $contestant) {
                $this->db->query(
                    "UPDATE contestants SET qualified_for_level_id = ? WHERE id = ?",
                    [$nextLevel['id'], $contestant['contestant_id']]
                );
            }
            
            return true;
        }
        
        // Get all rounds in this level
        $rounds = $this->db->fetchAll(
            "SELECT id FROM rounds WHERE level_id = ?",
            [$levelId]
        );
        
        if (empty($rounds)) {
            return false;
        }
        
        // Check if all rounds have completed rankings
        $allRoundsComplete = true;
        foreach ($rounds as $round) {
            $hasRankings = $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM rankings WHERE round_id = ?",
                [$round['id']]
            )['count'];
            
            if ($hasRankings == 0) {
                $allRoundsComplete = false;
                break;
            }
        }
        
        // If not all rounds are complete, don't process elimination yet
        if (!$allRoundsComplete) {
            return false;
        }
        
        // Calculate overall level rankings (aggregate across all rounds)
        $levelResult = $this->calculateLevelRankings($levelId);
        
        // Check if level is complete
        if (!$levelResult['level_complete']) {
            return [
                'success' => false,
                'message' => $levelResult['message'],
                'incomplete_rounds' => $levelResult['incomplete_rounds'],
                'total_rounds' => $levelResult['total_rounds'],
                'completed_rounds' => $levelResult['completed_rounds']
            ];
        }
        
        $levelRankings = $levelResult['rankings'];
        
        if (empty($levelRankings)) {
            return [
                'success' => false,
                'message' => 'No rankings available for this level'
            ];
        }
        
        // Get next level
        $nextLevel = $this->db->fetchOne(
            "SELECT * FROM event_levels 
             WHERE event_id = ? AND `order` > ? 
             ORDER BY `order` ASC LIMIT 1",
            [$level['event_id'], $level['order']]
        );
        
        if (!$nextLevel) {
            // No next level, nothing to do
            return false;
        }
        
        // Get top N contestants (rank 1 to advance_count)
        // IMPORTANT: If there's a tie at the cutoff point, include ALL tied contestants
        $qualifiedContestants = [];
        $advanceCount = (int)$level['advance_count'];
        
        if ($advanceCount > 0 && count($levelRankings) >= $advanceCount) {
            // Get the first N contestants
            $qualifiedContestants = array_slice($levelRankings, 0, $advanceCount);
            
            // Get the score of the last qualified contestant (cutoff score)
            $lastQualified = end($qualifiedContestants);
            $cutoffScore = $lastQualified['average'] ?? 0;
            
            // Include ALL contestants tied with the cutoff score (even if beyond advance_count)
            // This ensures fairness - if multiple contestants have the same score at cutoff, all advance
            foreach ($levelRankings as $ranking) {
                $contestantScore = $ranking['average'] ?? 0;
                // Check if this contestant is tied with cutoff (within 0.001 tolerance)
                if (abs($contestantScore - $cutoffScore) <= 0.001) {
                    // Check if not already in qualified list
                    $alreadyIncluded = false;
                    foreach ($qualifiedContestants as $q) {
                        if ($q['contestant_id'] == $ranking['contestant_id']) {
                            $alreadyIncluded = true;
                            break;
                        }
                    }
                    if (!$alreadyIncluded) {
                        $qualifiedContestants[] = $ranking;
                    }
                }
            }
        } else {
            // If advance_count is 0 or not enough contestants, take all
            $qualifiedContestants = $levelRankings;
        }
        
        // Clear previous qualifications for this next level
        $this->db->query(
            "UPDATE contestants SET qualified_for_level_id = NULL 
             WHERE event_id = ? AND qualified_for_level_id = ?",
            [$level['event_id'], $nextLevel['id']]
        );
        
        // Mark all qualified contestants (including tied ones) as qualified for next level
        foreach ($qualifiedContestants as $contestant) {
            $this->db->query(
                "UPDATE contestants SET qualified_for_level_id = ? WHERE id = ?",
                [$nextLevel['id'], $contestant['contestant_id']]
            );
        }
        
        return true;
    }
    
    /**
     * Calculate overall rankings for a level (aggregate across all rounds)
     * Returns contestants ranked by their average score across all rounds in the level
     */
    public function calculateLevelRankings($levelId) {
        // Get level info for formula lookup
        $level = $this->db->fetchOne(
            "SELECT * FROM event_levels WHERE id = ?",
            [$levelId]
        );
        
        if (!$level) {
            return [];
        }
        
        // Get all rounds in this level
        $rounds = $this->db->fetchAll(
            "SELECT id, name FROM rounds WHERE level_id = ? ORDER BY `order`",
            [$levelId]
        );
        
        if (empty($rounds)) {
            return [];
        }
        
        $roundIds = array_column($rounds, 'id');
        $totalExpectedRounds = count($rounds);
        $roundIndexMap = [];
        foreach ($roundIds as $index => $roundId) {
            $roundIndexMap[$roundId] = $index;
        }
        $placeholders = str_repeat('?,', count($roundIds) - 1) . '?';
        
        // Get all rankings for all rounds in this level
        $allRankings = $this->db->fetchAll(
            "SELECT r.contestant_id, r.round_id, r.average_score, c.contestant_number, c.name, c.team_name
             FROM rankings r
             JOIN contestants c ON r.contestant_id = c.id
             WHERE r.level_id = ? AND r.round_id IN ($placeholders)
             ORDER BY r.contestant_id, r.round_id",
            array_merge([$levelId], $roundIds)
        );
        
        if (empty($allRankings)) {
            return [];
        }
        
        // Check which rounds have scores (are complete)
        $roundsWithScores = [];
        foreach ($allRankings as $ranking) {
            $roundsWithScores[$ranking['round_id']] = true;
        }
        
        $incompleteRounds = [];
        foreach ($rounds as $round) {
            if (!isset($roundsWithScores[$round['id']])) {
                $incompleteRounds[] = [
                    'id' => $round['id'],
                    'name' => $round['name'],
                    'status' => 'incomplete'
                ];
            }
        }
        
        // CRITICAL: Check if level is complete before allowing advancement
        if (!empty($incompleteRounds)) {
            return [
                'level_complete' => false,
                'incomplete_rounds' => $incompleteRounds,
                'total_rounds' => $totalExpectedRounds,
                'completed_rounds' => count($roundsWithScores),
                'message' => 'Level cannot be ranked until all rounds are complete',
                'rankings' => [] // Empty rankings until level is complete
            ];
        }
        
        // Group by contestant and calculate overall score using formula or average
        $contestantScores = [];
        foreach ($allRankings as $ranking) {
            $cid = $ranking['contestant_id'];
            if (!isset($contestantScores[$cid])) {
                $contestantScores[$cid] = [
                    'contestant_id' => $cid,
                    'contestant_number' => $ranking['contestant_number'],
                    'name' => $ranking['name'],
                    'team_name' => $ranking['team_name'] ?? null,
                    'round_averages' => array_fill(0, $totalExpectedRounds, 0),
                    'total' => 0,
                    'count' => 0,
                    'average' => 0
                ];
            }
            
            $roundIndex = $roundIndexMap[$ranking['round_id']] ?? null;
            if ($roundIndex !== null) {
                $contestantScores[$cid]['round_averages'][$roundIndex] = (float)$ranking['average_score'];
            }
        }
        
        // Get level formula for this round (if any)
        $levelFormula = $this->getLevelFormula($level['event_id'], $levelId);
        
        // Calculate overall averages or formula-based totals
        foreach ($contestantScores as &$cs) {
            $roundAverages = $cs['round_averages'];
            $cs['count'] = $totalExpectedRounds; // Use TOTAL expected rounds, not just scored rounds
            $cs['total'] = array_sum($roundAverages);
            $cs['completed_rounds'] = count(array_filter($roundAverages, fn($r) => $r > 0));
            
            if ($levelFormula && !empty($levelFormula['formula_expression'])) {
                $average = $this->evaluateLevelFormula($levelFormula['formula_expression'], $roundAverages);
            } else {
                $average = $cs['count'] > 0 ? $cs['total'] / $cs['count'] : 0;
            }
            
            $cs['average'] = round($average, 3);
        }
        
        // Sort by average (descending) - higher is better
        usort($contestantScores, function($a, $b) {
            if ($a['average'] == $b['average']) {
                return 0;
            }
            return ($a['average'] > $b['average']) ? -1 : 1;
        });
        
        // Fractional (Olympic) ranking: tie = same average (within 0.001). Tied get average of positions (e.g. 3.5); next rank skips.
        $n = count($contestantScores);
        $i = 0;
        while ($i < $n) {
            $j = $i;
            while ($j + 1 < $n && abs((float)$contestantScores[$j + 1]['average'] - (float)$contestantScores[$j]['average']) <= 0.001) {
                $j++;
            }
            $rank = (($i + 1) + ($j + 1)) / 2;
            for ($k = $i; $k <= $j; $k++) {
                $contestantScores[$k]['rank'] = $rank;
            }
            $i = $j + 1;
        }
        
        return [
            'level_complete' => true,
            'incomplete_rounds' => [],
            'total_rounds' => $totalExpectedRounds,
            'completed_rounds' => $totalExpectedRounds,
            'message' => 'Level ranking calculated successfully',
            'rankings' => $contestantScores
        ];
    }

    /**
     * Get the active level formula for a level (if any).
     * Priority: specific formula for this level, then general formula for all levels.
     */
    private function getLevelFormula($eventId, $levelId) {
        $allLevelFormulas = $this->db->fetchAll(
            "SELECT * FROM scoring_formula 
             WHERE event_id = ? AND formula_type = 'level' AND is_active = 1
             ORDER BY updated_at DESC, created_at DESC",
            [$eventId]
        );
        
        $specificFormula = null;
        $generalFormula = null;
        
        foreach ($allLevelFormulas as $formula) {
            $levelIds = !empty($formula['level_ids']) ? json_decode($formula['level_ids'], true) : null;
            
            if ($levelIds === null || empty($levelIds)) {
                if (!$generalFormula) {
                    $generalFormula = $formula;
                }
            } elseif (is_array($levelIds) && in_array($levelId, $levelIds)) {
                if (!$specificFormula) {
                    $specificFormula = $formula;
                }
            }
        }
        
        return $specificFormula ?: $generalFormula;
    }
    
    /**
     * Evaluate a level formula for round averages (used for elimination ranking).
     * Supports round_totals, round_averages, and round_scores.
     */
    private function evaluateLevelFormula($formulaExpression, $roundAverages) {
        if (empty($roundAverages)) {
            return 0;
        }
        
        $expression = $formulaExpression;
        $roundDataArray = array_values($roundAverages);
        
        // Replace SUM
        $expression = preg_replace('/SUM\s*\(\s*round_totals\s*\)/i', 'array_sum($roundDataArray)', $expression);
        $expression = preg_replace('/SUM\s*\(\s*round_averages\s*\)/i', 'array_sum($roundDataArray)', $expression);
        $expression = preg_replace('/SUM\s*\(\s*round_scores\s*\)/i', 'array_sum($roundDataArray)', $expression);
        
        // Replace AVG
        $avgReplacement = 'count($roundDataArray) > 0 ? array_sum($roundDataArray) / count($roundDataArray) : 0';
        $expression = preg_replace('/AVG\s*\(\s*round_totals\s*\)/i', $avgReplacement, $expression);
        $expression = preg_replace('/AVG\s*\(\s*round_averages\s*\)/i', $avgReplacement, $expression);
        $expression = preg_replace('/AVG\s*\(\s*round_scores\s*\)/i', $avgReplacement, $expression);
        
        // Replace COUNT
        $expression = preg_replace('/COUNT\s*\(\s*round_totals\s*\)/i', 'count($roundDataArray)', $expression);
        $expression = preg_replace('/COUNT\s*\(\s*round_averages\s*\)/i', 'count($roundDataArray)', $expression);
        $expression = preg_replace('/COUNT\s*\(\s*round_scores\s*\)/i', 'count($roundDataArray)', $expression);
        
        // Replace MAX
        $maxReplacement = 'count($roundDataArray) > 0 ? max($roundDataArray) : 0';
        $expression = preg_replace('/MAX\s*\(\s*round_totals\s*\)/i', $maxReplacement, $expression);
        $expression = preg_replace('/MAX\s*\(\s*round_averages\s*\)/i', $maxReplacement, $expression);
        $expression = preg_replace('/MAX\s*\(\s*round_scores\s*\)/i', $maxReplacement, $expression);
        
        // Replace MIN
        $minReplacement = 'count($roundDataArray) > 0 ? min($roundDataArray) : 0';
        $expression = preg_replace('/MIN\s*\(\s*round_totals\s*\)/i', $minReplacement, $expression);
        $expression = preg_replace('/MIN\s*\(\s*round_averages\s*\)/i', $minReplacement, $expression);
        $expression = preg_replace('/MIN\s*\(\s*round_scores\s*\)/i', $minReplacement, $expression);
        
        // Handle array access
        $expression = preg_replace_callback('/(round_totals|round_averages|round_scores)\s*\[\s*(\d+)\s*\]/i', function($matches) use ($roundDataArray) {
            $index = (int)$matches[2];
            return isset($roundDataArray[$index]) ? $roundDataArray[$index] : 0;
        }, $expression);
        
        // Replace standalone variables
        $expression = preg_replace('/(?<!\$)\bround_totals\b(?![\[\w\(])/', 'array_sum($roundDataArray)', $expression);
        $expression = preg_replace('/(?<!\$)\bround_averages\b(?![\[\w\(])/', 'array_sum($roundDataArray)', $expression);
        $expression = preg_replace('/(?<!\$)\bround_scores\b(?![\[\w\(])/', 'array_sum($roundDataArray)', $expression);
        
        // Evaluate expression
        try {
            $result = eval("return $expression;");
            return round($result, 3);
        } catch (Exception $e) {
            error_log("Level formula evaluation error: " . $e->getMessage() . " Original: $formulaExpression, Converted: $expression");
            return count($roundDataArray) > 0 ? round(array_sum($roundDataArray) / count($roundDataArray), 3) : 0;
        } catch (Error $e) {
            error_log("Level formula evaluation error: " . $e->getMessage() . " Original: $formulaExpression, Converted: $expression");
            return count($roundDataArray) > 0 ? round(array_sum($roundDataArray) / count($roundDataArray), 3) : 0;
        }
    }
    
    /**
     * Recalculate all scores for a round
     */
    public function recalculateRound($roundId) {
        // Get all scores for this round
        $scores = $this->db->fetchAll(
            "SELECT id FROM scores WHERE round_id = ?",
            [$roundId]
        );
        
        foreach ($scores as $score) {
            $this->calculateScore($score['id']);
        }
        
        // Recalculate rankings
        $rankings = $this->calculateRankings($roundId);
        $this->saveRankings($roundId, $rankings);
        
        return true;
    }
    
    /**
     * Check if all scores are complete for a round
     * Returns true if all judges have submitted scores for all contestants
     */
    public function areAllScoresComplete($roundId) {
        // Get all active contestants for this round's event
        $round = $this->db->fetchOne(
            "SELECT r.*, el.event_id 
             FROM rounds r 
             JOIN event_levels el ON r.level_id = el.id 
             WHERE r.id = ?",
            [$roundId]
        );
        
        if (!$round) {
            return false;
        }
        
        $totalContestants = $this->db->fetchOne(
            "SELECT COUNT(DISTINCT s.contestant_id) as count 
             FROM scores s 
             WHERE s.round_id = ?",
            [$roundId]
        )['count'];
        
        if ($totalContestants == 0) {
            return false;
        }
        
        // Get all assigned judges for this round
        $assignedJudges = $this->db->fetchAll(
            "SELECT DISTINCT j.id 
             FROM judge_assignments ja
             JOIN judges j ON ja.judge_id = j.id
             WHERE ja.round_id = ? AND ja.is_active = 1",
            [$roundId]
        );
        
        $totalJudges = count($assignedJudges);
        
        if ($totalJudges == 0) {
            return false;
        }
        
        // Check if all judge-contestant combinations have submitted scores
        $expectedScores = $totalContestants * $totalJudges;
        
        $submittedScores = $this->db->fetchOne(
            "SELECT COUNT(*) as count 
             FROM scores s
             JOIN contestants c ON s.contestant_id = c.id
             WHERE s.round_id = ? AND s.is_submitted = 1 AND c.status = 'Active'",
            [$roundId]
        )['count'];
        
        return $submittedScores >= $expectedScores;
    }
    
    /**
     * Automatically calculate rankings if all scores are complete
     * Returns true if calculation was performed, false otherwise
     */
    public function autoCalculateIfComplete($roundId) {
        if ($this->areAllScoresComplete($roundId)) {
            // Check if rankings already exist
            $existingRankings = $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM rankings WHERE round_id = ?",
                [$roundId]
            )['count'];
            
            // Only calculate if rankings don't exist or need recalculation
            // For now, always recalculate to ensure accuracy
            $this->recalculateRound($roundId);
            return true;
        }
        
        return false;
    }
    
    /**
     * Get tie-breaking method from config
     */
    private function getTieBreakingMethod() {
        $config = $this->db->fetchOne(
            "SELECT config_value FROM system_config WHERE config_key = 'tie_breaking_method'"
        );
        return $config['config_value'] ?? 'average';
    }
}



