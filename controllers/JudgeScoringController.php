<?php
/**
 * Judge Scoring Controller
 */

class JudgeScoringController extends Controller {
    
    public function rounds() {
        $this->requireRoles(['Judge']);
        
        $userId = Session::get('user_id');
        
        // Get assigned rounds with progress counts, ordered by level and round order (ascending)
        // Only show rounds from events with 'Ongoing' status
        // Exclude preparation-only assignments (only visible to tech admins)
        $rounds = $this->db->fetchAll(
            "SELECT r.*, el.name as level_name, el.`order` as level_order, e.name as event_name, e.id as event_id,
             COALESCE((SELECT COUNT(*) FROM scores s2 WHERE s2.round_id = r.id AND s2.judge_id = j.id AND s2.is_submitted = 1), 0) as submitted_count,
             COALESCE((SELECT COUNT(DISTINCT s3.contestant_id) FROM scores s3 WHERE s3.round_id = r.id), 0) as total_contestants
             FROM judge_assignments ja
             JOIN judges j ON ja.judge_id = j.id
             JOIN rounds r ON ja.round_id = r.id
             JOIN event_levels el ON r.level_id = el.id
             JOIN events e ON el.event_id = e.id
             WHERE j.user_id = ? AND ja.is_active = 1 AND ja.is_preparation_only = 0 AND e.status = 'Ongoing'
             ORDER BY COALESCE(el.`order`, 0) ASC, COALESCE(r.`order`, 0) ASC, r.name ASC",
            [$userId]
        );
        
        $this->view('judge/rounds', ['rounds' => $rounds]);
    }
    
    public function round($roundId) {
        $this->requireRoles(['Judge']);
        
        $userId = Session::get('user_id');
        
        // Verify judge is assigned to this round
        $judge = $this->db->fetchOne(
            "SELECT j.* FROM judges j
             JOIN judge_assignments ja ON j.id = ja.judge_id
             WHERE j.user_id = ? AND ja.round_id = ? AND ja.is_active = 1",
            [$userId, $roundId]
        );
        
        if (!$judge) {
            $this->accessDenied("You are not assigned to this round.");
        }
        
        // Get round info
        $round = $this->db->fetchOne(
            "SELECT r.*, el.name as level_name, el.event_id, e.name as event_name
             FROM rounds r
             JOIN event_levels el ON r.level_id = el.id
             JOIN events e ON el.event_id = e.id
             WHERE r.id = ?",
            [$roundId]
        );
        
        // Get contestants
        $contestants = $this->db->fetchAll(
            "SELECT c.*,
             (SELECT is_submitted FROM scores WHERE contestant_id = c.id AND round_id = ? AND judge_id = ?) as is_submitted,
             (SELECT total_score FROM scores WHERE contestant_id = c.id AND round_id = ? AND judge_id = ?) as total_score
             FROM contestants c
             WHERE c.event_id = ? AND c.status = 'Active'
             ORDER BY c.contestant_number",
            [$roundId, $judge['id'], $roundId, $judge['id'], $round['event_id']]
        );
        
        // Redirect to table view by default (better scoring experience)
        $this->redirect('/tabulation/judge/rounds/' . $roundId . '/table');
    }
    
    public function roundTable($roundId) {
        $this->requireRoles(['Judge']);
        
        $userId = Session::get('user_id');
        
        // Verify judge is assigned to this round
        $judge = $this->db->fetchOne(
            "SELECT j.* FROM judges j
             JOIN judge_assignments ja ON j.id = ja.judge_id
             WHERE j.user_id = ? AND ja.round_id = ? AND ja.is_active = 1",
            [$userId, $roundId]
        );
        
        if (!$judge) {
            $this->accessDenied("You are not assigned to this round.");
        }
        
        // Get round info
        $round = $this->db->fetchOne(
            "SELECT r.*, el.name as level_name, el.event_id, e.name as event_name
             FROM rounds r
             JOIN event_levels el ON r.level_id = el.id
             JOIN events e ON el.event_id = e.id
             WHERE r.id = ?",
            [$roundId]
        );
        
        // Get contestants - filter by qualification if this is not the first level
        // Get current level info
        $currentLevel = $this->db->fetchOne(
            "SELECT el.* FROM event_levels el
             JOIN rounds r ON el.id = r.level_id
             WHERE r.id = ?",
            [$roundId]
        );
        
        // Check if there's a previous level
        $previousLevel = null;
        if ($currentLevel) {
            $previousLevel = $this->db->fetchOne(
                "SELECT * FROM event_levels 
                 WHERE event_id = ? AND `order` < ? 
                 ORDER BY `order` DESC LIMIT 1",
                [$round['event_id'], $currentLevel['order']]
            );
        }
        
        // If there's a previous level AND it had elimination (advance_count set),
        // show contestants who participated in this level. Otherwise, show all active contestants.
        if ($previousLevel && $previousLevel['advance_count'] !== null) {
            // Previous level had elimination - first try to get contestants with scores in this level
            $contestants = $this->db->fetchAll(
                "SELECT DISTINCT c.*
                 FROM contestants c
                 JOIN scores s ON c.id = s.contestant_id
                 JOIN rounds r ON s.round_id = r.id
                 WHERE c.event_id = ? 
                 AND c.status = 'Active'
                 AND r.level_id = ?
                 ORDER BY CAST(c.contestant_number AS UNSIGNED), c.contestant_number",
                [$round['event_id'], $currentLevel['id']]
            );
            
            // If no scores exist yet, show contestants qualified for this level
            if (empty($contestants)) {
                $contestants = $this->db->fetchAll(
                    "SELECT c.*
                     FROM contestants c
                     WHERE c.event_id = ? 
                     AND c.status = 'Active'
                     AND c.qualified_for_level_id = ?
                     ORDER BY CAST(c.contestant_number AS UNSIGNED), c.contestant_number",
                    [$round['event_id'], $currentLevel['id']]
                );
            }
        } else {
            // First level OR previous level had no elimination (advance_count = NULL) - show all active contestants
            $contestants = $this->db->fetchAll(
                "SELECT c.*
                 FROM contestants c
                 WHERE c.event_id = ? AND c.status = 'Active'
                 ORDER BY CAST(c.contestant_number AS UNSIGNED), c.contestant_number",
                [$round['event_id']]
            );
        }
        
        // Get criteria for this round that the judge is assigned to
        $criteria = $this->db->fetchAll(
            "SELECT c.*, cw.weight
             FROM criteria_weights cw
             JOIN criteria c ON cw.criteria_id = c.id
             LEFT JOIN judge_criteria_assignments jca ON jca.criteria_id = c.id AND jca.judge_id = ? AND jca.round_id = ? AND jca.is_active = 1
             WHERE cw.round_id = ? AND cw.is_active = 1 AND (jca.id IS NOT NULL OR ? = 1)
             ORDER BY c.id",
            [$judge['id'], $roundId, $roundId, in_array(Session::get('role_name'), ['Super Admin', 'Event Technical Admin']) ? 1 : 0]
        );
        
        // Get all existing scores for this round and judge
        $allScores = $this->db->fetchAll(
            "SELECT s.*, c.id as contestant_id
             FROM scores s
             JOIN contestants c ON s.contestant_id = c.id
             WHERE s.round_id = ? AND s.judge_id = ? AND c.event_id = ?",
            [$roundId, $judge['id'], $round['event_id']]
        );
        
        // Get all score details
        $allScoreDetails = [];
        if (!empty($allScores)) {
            $scoreIds = array_column($allScores, 'id');
            if (!empty($scoreIds)) {
                $placeholders = implode(',', array_fill(0, count($scoreIds), '?'));
                $details = $this->db->fetchAll(
                    "SELECT * FROM score_details WHERE score_id IN ($placeholders)",
                    $scoreIds
                );
                foreach ($details as $detail) {
                    if (!isset($allScoreDetails[$detail['score_id']])) {
                        $allScoreDetails[$detail['score_id']] = [];
                    }
                    $allScoreDetails[$detail['score_id']][$detail['criteria_id']] = $detail;
                }
            }
        }
        
        // Calculate scoring progress
        $totalContestants = count($contestants);
        $scoredCount = 0; // Contestants with at least one score entered
        $submittedCount = 0; // Contestants with submitted scores
        $fullyScoredCount = 0; // Contestants with all criteria scored
        
        foreach ($contestants as $contestant) {
            $hasScore = false;
            $isSubmitted = false;
            $hasAllCriteria = false;
            
            foreach ($allScores as $score) {
                if ($score['contestant_id'] == $contestant['id']) {
                    $hasScore = true;
                    $isSubmitted = $score['is_submitted'] ?? false;
                    
                    // Check if all criteria are scored
                    if (!empty($allScoreDetails[$score['id']])) {
                        $scoredCriteriaCount = count($allScoreDetails[$score['id']]);
                        $hasAllCriteria = ($scoredCriteriaCount == count($criteria));
                    }
                    break;
                }
            }
            
            if ($hasScore) {
                $scoredCount++;
            }
            if ($isSubmitted) {
                $submittedCount++;
            }
            if ($hasAllCriteria) {
                $fullyScoredCount++;
            }
        }
        
        $progressData = [
            'total' => $totalContestants,
            'scored' => $scoredCount,
            'submitted' => $submittedCount,
            'fully_scored' => $fullyScoredCount,
            'scored_percentage' => $totalContestants > 0 ? round(($scoredCount / $totalContestants) * 100, 1) : 0,
            'submitted_percentage' => $totalContestants > 0 ? round(($submittedCount / $totalContestants) * 100, 1) : 0,
            'fully_scored_percentage' => $totalContestants > 0 ? round(($fullyScoredCount / $totalContestants) * 100, 1) : 0
        ];
        
        $this->view('judge/score_table', [
            'round' => $round,
            'contestants' => $contestants,
            'criteria' => $criteria,
            'judge' => $judge,
            'allScores' => $allScores,
            'allScoreDetails' => $allScoreDetails,
            'progress' => $progressData
        ]);
    }
    
    /**
     * One view per level: all rounds (scoring categories) merged with section titles.
     * Judge scores all categories in one page.
     */
    public function levelTable($levelId) {
        $this->requireRoles(['Judge']);
        
        $userId = Session::get('user_id');
        
        // Get level and event
        $level = $this->db->fetchOne(
            "SELECT el.*, e.name as event_name, e.id as event_id
             FROM event_levels el
             JOIN events e ON el.event_id = e.id
             WHERE el.id = ?",
            [$levelId]
        );
        
        if (!$level) {
            $this->accessDenied("Level not found.");
        }
        
        // Get all rounds in this level that the judge is assigned to (ordered)
        $judge = $this->db->fetchOne(
            "SELECT j.* FROM judges j WHERE j.user_id = ? AND j.event_id = ?",
            [$userId, $level['event_id']]
        );
        
        if (!$judge) {
            $this->accessDenied("You are not a judge for this event.");
        }
        
        $roundsInLevel = $this->db->fetchAll(
            "SELECT r.*
             FROM rounds r
             JOIN judge_assignments ja ON ja.round_id = r.id AND ja.judge_id = ? AND ja.is_active = 1 AND ja.is_preparation_only = 0
             WHERE r.level_id = ?
             ORDER BY COALESCE(r.`order`, 0) ASC, r.name ASC",
            [$judge['id'], $levelId]
        );
        
        if (empty($roundsInLevel)) {
            $this->accessDenied("You are not assigned to any round in this level.");
        }
        
        // Contestants for this level (same logic as roundTable, by level)
        $previousLevel = $this->db->fetchOne(
            "SELECT * FROM event_levels 
             WHERE event_id = ? AND `order` < ? 
             ORDER BY `order` DESC LIMIT 1",
            [$level['event_id'], $level['order']]
        );
        
        if ($previousLevel && $previousLevel['advance_count'] !== null) {
            $contestants = $this->db->fetchAll(
                "SELECT DISTINCT c.*
                 FROM contestants c
                 JOIN scores s ON c.id = s.contestant_id
                 JOIN rounds r ON s.round_id = r.id
                 WHERE c.event_id = ? AND c.status = 'Active' AND r.level_id = ?
                 ORDER BY CAST(c.contestant_number AS UNSIGNED), c.contestant_number",
                [$level['event_id'], $levelId]
            );
            if (empty($contestants)) {
                $contestants = $this->db->fetchAll(
                    "SELECT c.* FROM contestants c
                     WHERE c.event_id = ? AND c.status = 'Active' AND c.qualified_for_level_id = ?
                     ORDER BY CAST(c.contestant_number AS UNSIGNED), c.contestant_number",
                    [$level['event_id'], $levelId]
                );
            }
        } else {
            $contestants = $this->db->fetchAll(
                "SELECT c.* FROM contestants c
                 WHERE c.event_id = ? AND c.status = 'Active'
                 ORDER BY CAST(c.contestant_number AS UNSIGNED), c.contestant_number",
                [$level['event_id']]
            );
        }
        
        $roundsData = [];
        foreach ($roundsInLevel as $round) {
            $roundId = $round['id'];
            $criteria = $this->db->fetchAll(
                "SELECT c.*, cw.weight
                 FROM criteria_weights cw
                 JOIN criteria c ON cw.criteria_id = c.id
                 LEFT JOIN judge_criteria_assignments jca ON jca.criteria_id = c.id AND jca.judge_id = ? AND jca.round_id = ? AND jca.is_active = 1
                 WHERE cw.round_id = ? AND cw.is_active = 1 AND (jca.id IS NOT NULL OR ? = 1)
                 ORDER BY c.id",
                [$judge['id'], $roundId, $roundId, in_array(Session::get('role_name'), ['Super Admin', 'Event Technical Admin']) ? 1 : 0]
            );
            
            $allScores = $this->db->fetchAll(
                "SELECT s.*, c.id as contestant_id
                 FROM scores s
                 JOIN contestants c ON s.contestant_id = c.id
                 WHERE s.round_id = ? AND s.judge_id = ? AND c.event_id = ?",
                [$roundId, $judge['id'], $level['event_id']]
            );
            
            $allScoreDetails = [];
            if (!empty($allScores)) {
                $scoreIds = array_column($allScores, 'id');
                $placeholders = implode(',', array_fill(0, count($scoreIds), '?'));
                $details = $this->db->fetchAll(
                    "SELECT * FROM score_details WHERE score_id IN ($placeholders)",
                    $scoreIds
                );
                foreach ($details as $detail) {
                    if (!isset($allScoreDetails[$detail['score_id']])) {
                        $allScoreDetails[$detail['score_id']] = [];
                    }
                    $allScoreDetails[$detail['score_id']][$detail['criteria_id']] = $detail;
                }
            }
            
            $totalContestants = count($contestants);
            $scoredCount = $submittedCount = $fullyScoredCount = 0;
            foreach ($contestants as $contestant) {
                foreach ($allScores as $score) {
                    if ($score['contestant_id'] == $contestant['id']) {
                        $scoredCount++;
                        if ($score['is_submitted'] ?? false) $submittedCount++;
                        if (!empty($allScoreDetails[$score['id']]) && count($allScoreDetails[$score['id']]) == count($criteria)) {
                            $fullyScoredCount++;
                        }
                        break;
                    }
                }
            }
            
            $roundsData[] = [
                'round' => array_merge($round, ['event_name' => $level['event_name'], 'level_name' => $level['name']]),
                'contestants' => $contestants,
                'criteria' => $criteria,
                'allScores' => $allScores,
                'allScoreDetails' => $allScoreDetails,
                'progress' => [
                    'total' => $totalContestants,
                    'scored' => $scoredCount,
                    'submitted' => $submittedCount,
                    'fully_scored' => $fullyScoredCount,
                    'scored_percentage' => $totalContestants > 0 ? round(($scoredCount / $totalContestants) * 100, 1) : 0,
                    'submitted_percentage' => $totalContestants > 0 ? round(($submittedCount / $totalContestants) * 100, 1) : 0,
                    'fully_scored_percentage' => $totalContestants > 0 ? round(($fullyScoredCount / $totalContestants) * 100, 1) : 0
                ]
            ];
        }
        
        $this->view('judge/level_score_table', [
            'level' => $level,
            'event' => ['name' => $level['event_name'], 'id' => $level['event_id']],
            'judge' => $judge,
            'roundsData' => $roundsData
        ]);
    }
    
    /**
     * Submit all draft scores for all rounds in a level (one efficient flow).
     */
    public function submitLevelAll($levelId) {
        $this->requireRoles(['Judge']);
        
        $userId = Session::get('user_id');
        
        $level = $this->db->fetchOne(
            "SELECT el.*, e.id as event_id FROM event_levels el JOIN events e ON el.event_id = e.id WHERE el.id = ?",
            [$levelId]
        );
        if (!$level) {
            $this->json(['success' => false, 'message' => 'Level not found'], 404);
            return;
        }
        
        $this->preventFinishedEventModification($level['event_id']);
        
        $judge = $this->db->fetchOne(
            "SELECT j.* FROM judges j WHERE j.user_id = ? AND j.event_id = ?",
            [$userId, $level['event_id']]
        );
        if (!$judge) {
            $this->json(['success' => false, 'message' => 'Access denied'], 403);
            return;
        }
        
        $roundIds = $this->db->fetchAll(
            "SELECT r.id FROM rounds r
             JOIN judge_assignments ja ON ja.round_id = r.id AND ja.judge_id = ? AND ja.is_active = 1 AND ja.is_preparation_only = 0
             WHERE r.level_id = ?",
            [$judge['id'], $levelId]
        );
        
        $totalSubmitted = 0;
        $errors = [];
        $validationFailed = false;
        
        foreach ($roundIds as $row) {
            $roundId = $row['id'];
            $scores = $this->db->fetchAll(
                "SELECT * FROM scores WHERE judge_id = ? AND round_id = ? AND (is_draft = 1 OR is_submitted = 0)",
                [$judge['id'], $roundId]
            );
            if (empty($scores)) continue;
            
            $criteria = $this->db->fetchAll(
                "SELECT c.* FROM criteria_weights cw JOIN criteria c ON cw.criteria_id = c.id
                 LEFT JOIN judge_criteria_assignments jca ON jca.criteria_id = c.id AND jca.judge_id = ? AND jca.round_id = ? AND jca.is_active = 1
                 WHERE cw.round_id = ? AND cw.is_active = 1 AND (jca.id IS NOT NULL OR ? = 1)",
                [$judge['id'], $roundId, $roundId, in_array(Session::get('role_name'), ['Super Admin', 'Event Technical Admin']) ? 1 : 0]
            );
            
            foreach ($scores as $score) {
                $details = $this->db->fetchAll(
                    "SELECT sd.*, c.max_score, c.name as criterion_name FROM score_details sd JOIN criteria c ON sd.criteria_id = c.id WHERE sd.score_id = ?",
                    [$score['id']]
                );
                if (count($details) < count($criteria)) {
                    $errors[] = "Complete all scores in each category before submitting.";
                    $validationFailed = true;
                    break 2;
                }
                foreach ($details as $detail) {
                    $raw = (float)$detail['raw_score'];
                    $max = (float)$detail['max_score'];
                    if ($raw < 0 || $raw > $max + 0.01) {
                        $errors[] = "Invalid score for {$detail['criterion_name']}.";
                        $validationFailed = true;
                        break 3;
                    }
                }
            }
            
            if ($validationFailed) break;
            
            foreach ($scores as $score) {
                $this->db->query(
                    "UPDATE scores SET is_submitted = 1, is_draft = 0, submitted_at = NOW() WHERE id = ?",
                    [$score['id']]
                );
                $totalSubmitted++;
            }
            
            require_once __DIR__ . '/../core/ScoringEngine.php';
            $eng = new ScoringEngine();
            $eng->autoCalculateIfComplete($roundId);
        }
        
        if ($validationFailed && !empty($errors)) {
            $this->json(['success' => false, 'message' => implode(' ', array_slice($errors, 0, 3))], 400);
            return;
        }
        
        $this->json([
            'success' => true,
            'message' => $totalSubmitted > 0 ? "All scores submitted successfully." : "No draft scores to submit.",
            'count' => $totalSubmitted
        ]);
    }
    
    public function score($roundId, $contestantId) {
        $this->requireAuth();
        
        $userId = Session::get('user_id');
        
        // Verify judge is assigned
        $judge = $this->db->fetchOne(
            "SELECT j.* FROM judges j
             JOIN judge_assignments ja ON j.id = ja.judge_id
             WHERE j.user_id = ? AND ja.round_id = ? AND ja.is_active = 1",
            [$userId, $roundId]
        );
        
        if (!$judge) {
            $this->accessDenied("You are not assigned to this round.");
        }
        
        // Get round and contestant info
        $round = $this->db->fetchOne(
            "SELECT r.*, el.name as level_name, el.event_id, e.name as event_name
             FROM rounds r
             JOIN event_levels el ON r.level_id = el.id
             JOIN events e ON el.event_id = e.id
             WHERE r.id = ?",
            [$roundId]
        );
        
        $contestant = $this->db->fetchOne(
            "SELECT * FROM contestants WHERE id = ? AND event_id = ?",
            [$contestantId, $round['event_id']]
        );
        
        if (!$contestant) {
            die("Contestant not found");
        }
        
        // Get criteria for this round that judge is assigned to
        $criteria = $this->db->fetchAll(
            "SELECT c.*, cw.weight
             FROM criteria_weights cw
             JOIN criteria c ON cw.criteria_id = c.id
             LEFT JOIN judge_criteria_assignments jca ON jca.criteria_id = c.id AND jca.judge_id = ? AND jca.round_id = ? AND jca.is_active = 1
             WHERE cw.round_id = ? AND cw.is_active = 1 AND (jca.id IS NOT NULL OR ? = 1)
             ORDER BY c.id",
            [$judge['id'], $roundId, $roundId, in_array(Session::get('role_name'), ['Super Admin', 'Event Technical Admin']) ? 1 : 0]
        );
        
        // Get existing score
        $score = $this->db->fetchOne(
            "SELECT * FROM scores 
             WHERE judge_id = ? AND contestant_id = ? AND round_id = ?",
            [$judge['id'], $contestantId, $roundId]
        );
        
        $scoreDetails = [];
        if ($score) {
            $scoreDetails = $this->db->fetchAll(
                "SELECT * FROM score_details WHERE score_id = ?",
                [$score['id']]
            );
        }
        
        $this->view('judge/score', [
            'round' => $round,
            'contestant' => $contestant,
            'criteria' => $criteria,
            'judge' => $judge,
            'score' => $score,
            'scoreDetails' => $scoreDetails
        ]);
    }
    
    public function submit($roundId, $contestantId) {
        $this->requireRoles(['Judge']);
        
        $userId = Session::get('user_id');
        
        // Verify judge
        $judge = $this->db->fetchOne(
            "SELECT j.* FROM judges j
             JOIN judge_assignments ja ON j.id = ja.judge_id
             WHERE j.user_id = ? AND ja.round_id = ? AND ja.is_active = 1",
            [$userId, $roundId]
        );
        
        if (!$judge) {
            $this->json(['success' => false, 'message' => 'Access denied'], 403);
            return;
        }
        
        // Get round info
        $round = $this->db->fetchOne(
            "SELECT r.*, el.event_id FROM rounds r
             JOIN event_levels el ON r.level_id = el.id
             WHERE r.id = ?",
            [$roundId]
        );
        
        // Prevent modifications to finished events
        $this->preventFinishedEventModification($round['event_id']);
        
        // Get criteria for this round that judge is assigned to
        $criteria = $this->db->fetchAll(
            "SELECT c.*, cw.weight
             FROM criteria_weights cw
             JOIN criteria c ON cw.criteria_id = c.id
             LEFT JOIN judge_criteria_assignments jca ON jca.criteria_id = c.id AND jca.judge_id = ? AND jca.round_id = ? AND jca.is_active = 1
             WHERE cw.round_id = ? AND cw.is_active = 1 AND (jca.id IS NOT NULL OR ? = 1)
             ORDER BY c.id",
            [$judge['id'], $roundId, $roundId, in_array(Session::get('role_name'), ['Super Admin', 'Event Technical Admin']) ? 1 : 0]
        );
        
        // Validate scores
        $scores = [];
        $totalScore = 0;
        
        foreach ($criteria as $criterion) {
            $fieldName = 'criteria_' . $criterion['id'];
            $rawScore = $_POST[$fieldName] ?? null;
            
            if ($rawScore === null) {
                $this->json(['success' => false, 'message' => "Score required for: {$criterion['name']}"], 400);
                return;
            }
            
            $rawScore = (float)$rawScore;
            $maxScore = (float)$criterion['max_score'];
            
            // Use epsilon for floating point comparison (allow values up to and including max)
            $epsilon = 0.0001;
            
            if ($rawScore < 0 || $rawScore > $maxScore + $epsilon) {
                // Only show error if significantly over max (not just floating point precision issue)
                if ($rawScore > $maxScore + 0.01) {
                    $this->json(['success' => false, 'message' => "Score for {$criterion['name']} must be between 0 and {$maxScore}. You entered {$rawScore}."], 400);
                    return;
                }
                // If just slightly over due to floating point, cap it
                if ($rawScore > $maxScore) {
                    $rawScore = $maxScore;
                }
            }
            
            $scores[$criterion['id']] = $rawScore;
        }
        
        // Get or create score record
        $score = $this->db->fetchOne(
            "SELECT * FROM scores 
             WHERE judge_id = ? AND contestant_id = ? AND round_id = ?",
            [$judge['id'], $contestantId, $roundId]
        );
        
        if ($score && $score['is_locked']) {
            $this->json(['success' => false, 'message' => 'Score is locked and cannot be modified'], 403);
            return;
        }
        
        $this->db->getConnection()->beginTransaction();
        
        try {
            if ($score) {
                $scoreId = $score['id'];
            } else {
                // Create score record (admin_edit_allowed defaults to 0)
                $this->db->query(
                    "INSERT INTO scores (judge_id, contestant_id, round_id, ip_address, admin_edit_allowed)
                     VALUES (?, ?, ?, ?, 0)",
                    [$judge['id'], $contestantId, $roundId, $_SERVER['REMOTE_ADDR'] ?? null]
                );
                $scoreId = $this->db->lastInsertId();
            }
            
            // Delete existing score details
            $this->db->query("DELETE FROM score_details WHERE score_id = ?", [$scoreId]);
            
            // Insert score details
            foreach ($scores as $criteriaId => $rawScore) {
                $this->db->query(
                    "INSERT INTO score_details (score_id, criteria_id, raw_score)
                     VALUES (?, ?, ?)",
                    [$scoreId, $criteriaId, $rawScore]
                );
            }
            
            // Calculate weighted score using ScoringEngine
            require_once __DIR__ . '/../core/ScoringEngine.php';
            $scoringEngine = new ScoringEngine();
            $scoringEngine->calculateScore($scoreId);
            
            // Mark as submitted
            $this->db->query(
                "UPDATE scores SET is_submitted = 1, submitted_at = NOW() WHERE id = ?",
                [$scoreId]
            );
            
            // Log audit
            $this->logAudit('SUBMIT_SCORE', 'scores', $scoreId, null, [
                'round_id' => $roundId,
                'contestant_id' => $contestantId,
                'scores' => $scores
            ]);
            
            $this->db->getConnection()->commit();
            
            // Auto-calculate rankings if all scores are complete
            require_once __DIR__ . '/../core/ScoringEngine.php';
            $scoringEngine = new ScoringEngine();
            $autoCalculated = $scoringEngine->autoCalculateIfComplete($roundId);
            
            $message = 'Scores submitted successfully';
            if ($autoCalculated) {
                $message .= '. Rankings calculated automatically.';
            }
            
            $this->json(['success' => true, 'message' => $message, 'auto_calculated' => $autoCalculated]);
            
        } catch (Exception $e) {
            $this->db->getConnection()->rollBack();
            $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
    
    public function autoSave($roundId, $contestantId) {
        $this->requireRoles(['Judge']);
        
        $userId = Session::get('user_id');
        
        // Verify judge
        $judge = $this->db->fetchOne(
            "SELECT j.* FROM judges j
             JOIN judge_assignments ja ON j.id = ja.judge_id
             WHERE j.user_id = ? AND ja.round_id = ? AND ja.is_active = 1",
            [$userId, $roundId]
        );
        
        if (!$judge) {
            $this->json(['success' => false, 'message' => 'Access denied'], 403);
            return;
        }
        
        // Get round info to check event status
        $round = $this->db->fetchOne(
            "SELECT r.*, el.event_id FROM rounds r
             JOIN event_levels el ON r.level_id = el.id
             WHERE r.id = ?",
            [$roundId]
        );
        
        // Prevent modifications to finished events
        $this->preventFinishedEventModification($round['event_id']);
        
        $criteriaId = $_POST['criteria_id'] ?? null;
        $rawScore = $_POST['raw_score'] ?? null;
        
        if ($criteriaId === null || $rawScore === null) {
            $this->json(['success' => false, 'message' => 'Missing required fields'], 400);
            return;
        }
        
        $rawScore = (float)$rawScore;
        
        // Get criterion to validate max score
        $criterion = $this->db->fetchOne(
            "SELECT c.* FROM criteria c
             JOIN criteria_weights cw ON c.id = cw.criteria_id
             LEFT JOIN judge_criteria_assignments jca ON jca.criteria_id = c.id AND jca.judge_id = ? AND jca.round_id = ? AND jca.is_active = 1
             WHERE cw.round_id = ? AND c.id = ? AND cw.is_active = 1 AND (jca.id IS NOT NULL OR ? = 1)",
            [$judge['id'], $roundId, $roundId, $criteriaId, in_array(Session::get('role_name'), ['Super Admin', 'Event Technical Admin']) ? 1 : 0]
        );
        
        if (!$criterion) {
            $this->json(['success' => false, 'message' => 'Invalid criterion'], 400);
            return;
        }
        
        $maxScore = (float)$criterion['max_score'];
        
        // Use epsilon for floating point comparison (allow values up to and including max)
        $epsilon = 0.0001;
        
        if ($rawScore < 0 || $rawScore > $maxScore + $epsilon) {
            // Only show error if significantly over max (not just floating point precision issue)
            if ($rawScore > $maxScore + 0.01) {
                $this->json(['success' => false, 'message' => "Score must be between 0 and {$maxScore}. You entered {$rawScore}."], 400);
                return;
            }
            // If just slightly over due to floating point, cap it
            if ($rawScore > $maxScore) {
                $rawScore = $maxScore;
            }
        }
        
        $this->db->getConnection()->beginTransaction();
        
        try {
            // Get or create score record (as draft)
            $score = $this->db->fetchOne(
                "SELECT * FROM scores 
                 WHERE judge_id = ? AND contestant_id = ? AND round_id = ?",
                [$judge['id'], $contestantId, $roundId]
            );
            
            if ($score && $score['is_submitted'] && ($score['permission_request_status'] ?? 'none') !== 'granted') {
                $this->db->getConnection()->rollBack();
                $this->json(['success' => false, 'message' => 'Score is submitted and cannot be edited without permission'], 403);
                return;
            }
            
            if (!$score) {
                $this->db->query(
                    "INSERT INTO scores (judge_id, contestant_id, round_id, ip_address, is_draft, is_submitted)
                     VALUES (?, ?, ?, ?, 1, 0)",
                    [$judge['id'], $contestantId, $roundId, $_SERVER['REMOTE_ADDR'] ?? null]
                );
                $scoreId = $this->db->lastInsertId();
            } else {
                $scoreId = $score['id'];
                // Update to draft if it was submitted but permission granted
                if ($score['is_submitted'] && ($score['permission_request_status'] ?? 'none') === 'granted') {
                    $this->db->query(
                        "UPDATE scores SET is_draft = 1 WHERE id = ?",
                        [$scoreId]
                    );
                }
            }
            
            // Update or insert score detail
            $existingDetail = $this->db->fetchOne(
                "SELECT * FROM score_details WHERE score_id = ? AND criteria_id = ?",
                [$scoreId, $criteriaId]
            );
            
            if ($existingDetail) {
                $this->db->query(
                    "UPDATE score_details SET raw_score = ? WHERE id = ?",
                    [$rawScore, $existingDetail['id']]
                );
            } else {
                $this->db->query(
                    "INSERT INTO score_details (score_id, criteria_id, raw_score)
                     VALUES (?, ?, ?)",
                    [$scoreId, $criteriaId, $rawScore]
                );
            }
            
            // Recalculate total score
            require_once __DIR__ . '/../core/ScoringEngine.php';
            $scoringEngine = new ScoringEngine();
            $scoringEngine->calculateScore($scoreId);
            
            $this->db->getConnection()->commit();
            
            $this->json(['success' => true, 'message' => 'Score saved']);
            
        } catch (Exception $e) {
            $this->db->getConnection()->rollBack();
            $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
    
    public function submitAll($roundId) {
        $this->requireRoles(['Judge']);
        
        $userId = Session::get('user_id');
        
        // Verify judge
        $judge = $this->db->fetchOne(
            "SELECT j.* FROM judges j
             JOIN judge_assignments ja ON j.id = ja.judge_id
             WHERE j.user_id = ? AND ja.round_id = ? AND ja.is_active = 1",
            [$userId, $roundId]
        );
        
        if (!$judge) {
            $this->json(['success' => false, 'message' => 'Access denied'], 403);
            return;
        }
        
        // Get round info to check event status
        $round = $this->db->fetchOne(
            "SELECT r.*, el.event_id FROM rounds r
             JOIN event_levels el ON r.level_id = el.id
             WHERE r.id = ?",
            [$roundId]
        );
        
        // Prevent modifications to finished events
        $this->preventFinishedEventModification($round['event_id']);
        
        // Get all draft scores for this round
        $scores = $this->db->fetchAll(
            "SELECT * FROM scores 
             WHERE judge_id = ? AND round_id = ? AND (is_draft = 1 OR is_submitted = 0)",
            [$judge['id'], $roundId]
        );
        
        if (empty($scores)) {
            $this->json(['success' => false, 'message' => 'No scores to submit'], 400);
            return;
        }
        
        // Get criteria to verify all scores are complete (only criteria assigned to judge)
        $criteria = $this->db->fetchAll(
            "SELECT c.* 
             FROM criteria_weights cw
             JOIN criteria c ON cw.criteria_id = c.id
             LEFT JOIN judge_criteria_assignments jca ON jca.criteria_id = c.id AND jca.judge_id = ? AND jca.round_id = ? AND jca.is_active = 1
             WHERE cw.round_id = ? AND cw.is_active = 1 AND (jca.id IS NOT NULL OR ? = 1)
             ORDER BY c.id",
            [$judge['id'], $roundId, $roundId, in_array(Session::get('role_name'), ['Super Admin', 'Event Technical Admin']) ? 1 : 0]
        );
        
        $this->db->getConnection()->beginTransaction();
        
        try {
            foreach ($scores as $score) {
                // Verify all criteria have scores
                $details = $this->db->fetchAll(
                    "SELECT sd.*, c.max_score, c.name as criterion_name
                     FROM score_details sd
                     JOIN criteria c ON sd.criteria_id = c.id
                     WHERE sd.score_id = ?",
                    [$score['id']]
                );
                
                if (count($details) < count($criteria)) {
                    $this->db->getConnection()->rollBack();
                    $this->json(['success' => false, 'message' => 'Please complete all scores before submitting'], 400);
                    return;
                }
                
                // Validate each score doesn't exceed max
                foreach ($details as $detail) {
                    $rawScore = (float)$detail['raw_score'];
                    $maxScore = (float)$detail['max_score'];
                    
                    // Use epsilon for floating point comparison (allow values up to and including max)
                    $epsilon = 0.0001;
                    
                    if ($rawScore < 0 || $rawScore > $maxScore + $epsilon) {
                        // Only show error if significantly over max (not just floating point precision issue)
                        if ($rawScore > $maxScore + 0.01) {
                            $this->db->getConnection()->rollBack();
                            $this->json([
                                'success' => false, 
                                'message' => "Score for '{$detail['criterion_name']}' (Contestant #{$score['contestant_id']}) must be between 0 and {$maxScore}. You entered {$rawScore}."
                            ], 400);
                            return;
                        }
                        // If just slightly over due to floating point, it's acceptable (will be capped in display)
                    }
                }
                
                // Mark as submitted (not draft)
                $this->db->query(
                    "UPDATE scores SET is_submitted = 1, is_draft = 0, submitted_at = NOW() WHERE id = ?",
                    [$score['id']]
                );
            }
            
            // Log audit
            $this->logAudit('SUBMIT_ALL_SCORES', 'scores', null, null, [
                'round_id' => $roundId,
                'count' => count($scores)
            ]);
            
            $this->db->getConnection()->commit();
            
            // Auto-calculate rankings if all scores are complete
            require_once __DIR__ . '/../core/ScoringEngine.php';
            $scoringEngine = new ScoringEngine();
            $autoCalculated = $scoringEngine->autoCalculateIfComplete($roundId);
            
            $message = 'All scores submitted successfully';
            if ($autoCalculated) {
                $message .= '. Rankings calculated automatically.';
            }
            
            $this->json(['success' => true, 'message' => $message, 'count' => count($scores), 'auto_calculated' => $autoCalculated]);
            
        } catch (Exception $e) {
            $this->db->getConnection()->rollBack();
            $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
    
    public function requestEditPermission($roundId) {
        $this->requireRoles(['Judge']);
        
        $userId = Session::get('user_id');
        $scoreId = $_POST['score_id'] ?? null;
        $contestantId = $_POST['contestant_id'] ?? null;
        
        if (!$scoreId || !$contestantId) {
            $this->json(['success' => false, 'message' => 'Missing required fields'], 400);
            return;
        }
        
        // Verify judge owns this score
        $judge = $this->db->fetchOne(
            "SELECT j.* FROM judges j
             JOIN scores s ON j.id = s.judge_id
             WHERE j.user_id = ? AND s.id = ? AND s.round_id = ?",
            [$userId, $scoreId, $roundId]
        );
        
        if (!$judge) {
            $this->json(['success' => false, 'message' => 'Access denied'], 403);
            return;
        }
        
        // Check if score is submitted
        $score = $this->db->fetchOne("SELECT * FROM scores WHERE id = ?", [$scoreId]);
        if (!$score || !$score['is_submitted']) {
            $this->json(['success' => false, 'message' => 'Score is not submitted'], 400);
            return;
        }
        
        // Get contestant and round info
        $contestant = $this->db->fetchOne("SELECT * FROM contestants WHERE id = ?", [$contestantId]);
        $round = $this->db->fetchOne(
            "SELECT r.*, e.name as event_name FROM rounds r
             JOIN event_levels el ON r.level_id = el.id
             JOIN events e ON el.event_id = e.id
             WHERE r.id = ?",
            [$roundId]
        );
        
        $this->db->getConnection()->beginTransaction();
        
        try {
            // Update score permission status
            $this->db->query(
                "UPDATE scores SET 
                 permission_request_status = 'judge_requested',
                 permission_requested_by = ?,
                 permission_requested_at = NOW()
                 WHERE id = ?",
                [$userId, $scoreId]
            );
            
            // Create notification for admins/tabulators/tech admins
            $admins = $this->db->fetchAll(
                "SELECT u.id FROM users u
                 JOIN roles r ON u.role_id = r.id
                 WHERE r.name IN ('Super Admin', 'Event Admin', 'Event Organizer', 'Event Technical Admin', 'Tabulator')
                 AND u.is_active = 1"
            );
            
            foreach ($admins as $admin) {
                $this->db->query(
                    "INSERT INTO notifications (user_id, type, title, message, related_type, related_id)
                     VALUES (?, 'permission_request', ?, ?, 'score', ?)",
                    [
                        $admin['id'],
                        'Permission Request: Judge wants to edit submitted score',
                        "Judge " . Session::get('full_name') . " is requesting permission to edit score for Contestant #{$contestant['contestant_number']} ({$contestant['name']}) in Round: {$round['name']}",
                        $scoreId
                    ]
                );
            }
            
            $this->db->getConnection()->commit();
            
            $this->json(['success' => true, 'message' => 'Permission request sent']);
            
        } catch (Exception $e) {
            $this->db->getConnection()->rollBack();
            $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}

