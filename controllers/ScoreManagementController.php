<?php
/**
 * Score Management Controller
 * Handles score editing, permission requests, and deductions
 */

class ScoreManagementController extends Controller {
    
    public function index($roundId, $judgeId = null) {
        $this->restrictJudges();
        
        $round = $this->db->fetchOne(
            "SELECT r.*, el.name as level_name, el.event_id, e.name as event_name
             FROM rounds r
             JOIN event_levels el ON r.level_id = el.id
             JOIN events e ON el.event_id = e.id
             WHERE r.id = ?",
            [$roundId]
        );
        
        if (!$round) {
            die("Round not found");
        }
        
        $this->requireEventAccess($round['event_id']);
        
        // Get criteria for this round
        $criteria = $this->db->fetchAll(
            "SELECT c.*, cw.weight
             FROM criteria_weights cw
             JOIN criteria c ON cw.criteria_id = c.id
             WHERE cw.round_id = ? AND cw.is_active = 1
             ORDER BY c.id, c.name",
            [$roundId]
        );
        
        // Get all judges for this round
        $judges = $this->db->fetchAll(
            "SELECT DISTINCT j.*, u.full_name as judge_name
             FROM judges j
             JOIN users u ON j.user_id = u.id
             JOIN judge_assignments ja ON j.id = ja.judge_id
             WHERE ja.round_id = ? AND ja.is_active = 1
             ORDER BY j.judge_number",
            [$roundId]
        );
        
        // Filter by judge if specified
        $judgeFilter = null;
        if ($judgeId) {
            $judgeFilter = $this->db->fetchOne(
                "SELECT j.*, u.full_name as judge_name
                 FROM judges j
                 JOIN users u ON j.user_id = u.id
                 WHERE j.id = ?",
                [$judgeId]
            );
        }
        
        // Get all scores for this round (both draft and submitted)
        // Admins should be able to see scores even before judges submit
        $scoresQuery = "SELECT s.*, c.contestant_number, c.name as contestant_name,
                               j.id as judge_id, j.judge_number, u.full_name as judge_name
             FROM scores s
             JOIN contestants c ON s.contestant_id = c.id
             JOIN judges j ON s.judge_id = j.id
             JOIN users u ON j.user_id = u.id
             WHERE s.round_id = ?";
        
        $scoresParams = [$roundId];
        if ($judgeId) {
            $scoresQuery .= " AND s.judge_id = ?";
            $scoresParams[] = $judgeId;
        }
        
        $scoresQuery .= " ORDER BY j.judge_number, CAST(c.contestant_number AS UNSIGNED)";
        
        $scores = $this->db->fetchAll($scoresQuery, $scoresParams);
        
        // Get score details
        $scoreIds = array_column($scores, 'id');
        $scoreDetails = [];
        if (!empty($scoreIds)) {
            $placeholders = implode(',', array_fill(0, count($scoreIds), '?'));
            $details = $this->db->fetchAll(
                "SELECT * FROM score_details WHERE score_id IN ($placeholders)",
                $scoreIds
            );
            
            foreach ($details as $detail) {
                $scoreDetails[$detail['score_id']][] = $detail;
            }
        }
        
        // Group scores by judge
        $judgeData = [];
        foreach ($scores as $score) {
            $judgeId = $score['judge_id'];
            if (!isset($judgeData[$judgeId])) {
                $judgeData[$judgeId] = [
                    'judge' => [
                        'id' => $judgeId,
                        'judge_number' => $score['judge_number'],
                        'judge_name' => $score['judge_name']
                    ],
                    'scores' => []
                ];
            }
            $score['details'] = $scoreDetails[$score['id']] ?? [];
            $judgeData[$judgeId]['scores'][] = $score;
        }
        
        $this->view('score_management/index', [
            'round' => $round,
            'criteria' => $criteria,
            'judges' => $judges,
            'judgeFilter' => $judgeFilter,
            'judgeData' => $judgeData
        ]);
    }
    
    public function generateOverwriteKey($scoreId) {
        $this->restrictJudges();
        
        $score = $this->db->fetchOne(
            "SELECT s.*, el.event_id FROM scores s
             JOIN rounds r ON s.round_id = r.id
             JOIN event_levels el ON r.level_id = el.id
             WHERE s.id = ?",
            [$scoreId]
        );
        
        if (!$score) {
            $this->json(['success' => false, 'message' => 'Score not found'], 404);
            return;
        }
        
        $this->requireEventAccess($score['event_id']);
        
        // Generate a temporary overwrite key
        $overwriteKey = bin2hex(random_bytes(16));
        
        // Store in session (temporary, expires on logout)
        if (!isset($_SESSION['overwrite_keys'])) {
            $_SESSION['overwrite_keys'] = [];
        }
        $_SESSION['overwrite_keys'][$scoreId] = $overwriteKey;
        
        $this->json([
            'success' => true, 
            'overwrite_key' => $overwriteKey
        ]);
    }
    
    public function edit($scoreId) {
        $this->restrictJudges();
        
        $currentRole = Session::get('role_name');
        
        // Only Super Admin, Event Admin, Event Technical Admin, and Tabulator can access
        // Event Organizer CANNOT edit scores (restricted)
        if (!in_array($currentRole, ['Super Admin', 'Event Admin', 'Event Technical Admin', 'Tabulator'])) {
            $this->accessDenied("Access denied. Only Technical Admin and authorized personnel can edit scores.");
        }
        
        $score = $this->db->fetchOne(
            "SELECT s.*, 
             c.name as contestant_name, c.contestant_number, c.id as contestant_id,
             r.id as round_id, r.name as round_name, r.level_id,
             j.id as judge_id, j.judge_number,
             u.full_name as judge_name, u.id as judge_user_id,
             el.event_id,
             s.permission_granted_at
             FROM scores s
             JOIN contestants c ON s.contestant_id = c.id
             JOIN rounds r ON s.round_id = r.id
             JOIN event_levels el ON r.level_id = el.id
             JOIN judges j ON s.judge_id = j.id
             JOIN users u ON j.user_id = u.id
             WHERE s.id = ?",
            [$scoreId]
        );
        
        if (!$score) {
            die("Score not found");
        }
        
        $this->requireEventAccess($score['event_id']);
        
        // Check if permission is needed - MUST ASK PERMISSION EACH TIME
        // Permission is reset after each edit, so we always need to check
        $needsPermission = !$score['admin_edit_allowed'];
        
        // Get criteria for this round
        $criteria = $this->db->fetchAll(
            "SELECT c.*, cw.weight
             FROM criteria_weights cw
             JOIN criteria c ON cw.criteria_id = c.id
             WHERE cw.round_id = ? AND cw.is_active = 1
             ORDER BY c.id, c.name",
            [$score['round_id']]
        );
        
        // Get existing score details
        $scoreDetails = $this->db->fetchAll(
            "SELECT * FROM score_details WHERE score_id = ?",
            [$scoreId]
        );
        
        $this->view('score_management/edit', [
            'score' => $score,
            'criteria' => $criteria,
            'scoreDetails' => $scoreDetails,
            'needsPermission' => $needsPermission
        ]);
    }
    
    public function requestEditPermission($scoreId) {
        $this->restrictJudges();
        
        $currentRole = Session::get('role_name');
        if (!in_array($currentRole, ['Super Admin', 'Event Admin', 'Event Technical Admin', 'Tabulator'])) {
            $this->json(['success' => false, 'message' => 'Access denied'], 403);
            return;
        }
        
        $score = $this->db->fetchOne(
            "SELECT s.*, j.user_id as judge_user_id, c.contestant_number, c.name as contestant_name,
                    r.name as round_name, el.event_id
             FROM scores s
             JOIN judges j ON s.judge_id = j.id
             JOIN contestants c ON s.contestant_id = c.id
             JOIN rounds r ON s.round_id = r.id
             JOIN event_levels el ON r.level_id = el.id
             WHERE s.id = ?",
            [$scoreId]
        );
        
        if (!$score) {
            $this->json(['success' => false, 'message' => 'Score not found'], 404);
            return;
        }
        
        $this->requireEventAccess($score['event_id']);
        
        // Prevent modifications to finished events
        $this->preventFinishedEventModification($score['event_id']);
        
        // Update permission request status
        $this->db->query(
            "UPDATE scores SET 
             permission_request_status = 'admin_requested',
             permission_requested_by = ?,
             permission_requested_at = NOW()
             WHERE id = ?",
            [Session::get('user_id'), $scoreId]
        );
        
        // Create notification for judge
        $this->db->query(
            "INSERT INTO notifications (user_id, type, title, message, related_type, related_id)
             VALUES (?, 'permission_request', ?, ?, 'score', ?)",
            [
                $score['judge_user_id'],
                'Permission Request: Admin wants to edit submitted score',
                "Admin " . Session::get('full_name') . " is requesting permission to edit score for Contestant #{$score['contestant_number']} ({$score['contestant_name']}) in Round: {$score['round_name']}",
                $scoreId
            ]
        );
        
        $this->json(['success' => true, 'message' => 'Permission request sent to judge']);
    }
    
    public function update($scoreId) {
        $this->restrictJudges();
        
        $currentRole = Session::get('role_name');
        if (!in_array($currentRole, ['Super Admin', 'Event Admin', 'Event Technical Admin', 'Tabulator'])) {
            $this->json(['success' => false, 'message' => 'Access denied'], 403);
                return;
            }
            
        $score = $this->db->fetchOne(
            "SELECT s.*, el.event_id FROM scores s
             JOIN rounds r ON s.round_id = r.id
             JOIN event_levels el ON r.level_id = el.id
             WHERE s.id = ?",
            [$scoreId]
        );
        
        if (!$score) {
            $this->json(['success' => false, 'message' => 'Score not found'], 404);
                return;
            }
            
        $this->requireEventAccess($score['event_id']);
        
        // Prevent modifications to finished events
        $this->preventFinishedEventModification($score['event_id']);
        
        // Check permission - MUST HAVE PERMISSION TO EDIT
        if (!$score['admin_edit_allowed']) {
            $this->json(['success' => false, 'message' => 'Judge permission required. Please request permission from the judge first.', 'needs_permission' => true], 403);
            return;
        }
        
        // Check 30-second timeout: Permission expires after 30 seconds of inactivity or if no changes are submitted
        if (!empty($score['permission_granted_at'])) {
            $grantedTime = strtotime($score['permission_granted_at']);
            $currentTime = time();
            $secondsElapsed = $currentTime - $grantedTime;
            
            if ($secondsElapsed > 30) {
                // Permission expired - void it
                $this->db->query(
                    "UPDATE scores SET 
                     admin_edit_allowed = 0,
                     permission_request_status = 'none',
                     permission_granted_at = NULL
                     WHERE id = ?",
                    [$scoreId]
                );
                
                $this->json([
                    'success' => false, 
                    'message' => 'Permission has expired. You have 30 seconds after permission is granted to make changes. Please request permission again.', 
                    'needs_permission' => true,
                    'permission_expired' => true
                ], 403);
                return;
            }
        }
        
        // Get criteria
        $criteria = $this->db->fetchAll(
            "SELECT c.*, cw.weight
             FROM criteria_weights cw
             JOIN criteria c ON cw.criteria_id = c.id
             WHERE cw.round_id = ? AND cw.is_active = 1",
            [$score['round_id']]
        );
        
        // Validate scores
        $scores = [];
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
        
        $this->db->getConnection()->beginTransaction();
        
        try {
            // Store old values for audit
            $oldScoreDetails = $this->db->fetchAll(
                "SELECT * FROM score_details WHERE score_id = ?",
                [$scoreId]
            );
            
            // Delete existing score details
            $this->db->query("DELETE FROM score_details WHERE score_id = ?", [$scoreId]);
            
            // Insert new score details
            foreach ($scores as $criteriaId => $rawScore) {
                $this->db->query(
                    "INSERT INTO score_details (score_id, criteria_id, raw_score)
                     VALUES (?, ?, ?)",
                    [$scoreId, $criteriaId, $rawScore]
                );
            }
            
            // Recalculate weighted score
            require_once __DIR__ . '/../core/ScoringEngine.php';
            $scoringEngine = new ScoringEngine();
            $scoringEngine->calculateScore($scoreId);
            
            // Get score info for notification
            $scoreInfo = $this->db->fetchOne(
                "SELECT s.*, j.user_id as judge_user_id, c.contestant_number, c.name as contestant_name
                 FROM scores s
                 JOIN judges j ON s.judge_id = j.id
                 JOIN contestants c ON s.contestant_id = c.id
                 WHERE s.id = ?",
                [$scoreId]
            );
            
            // Update score record - RESET PERMISSION after edit (must ask again next time)
            // Also mark as submitted since admin has completed the edit
            $this->db->query(
                "UPDATE scores SET 
                 admin_edited_by = ?,
                 admin_edited_at = NOW(),
                 admin_edit_allowed = 0,
                 permission_request_status = 'none',
                 permission_granted_at = NULL,
                 is_submitted = 1,
                 submitted_at = NOW()
                 WHERE id = ?",
                [Session::get('user_id'), $scoreId]
            );
            
            // Create notification for judge
            $this->db->query(
                "INSERT INTO notifications (user_id, type, title, message, related_type, related_id)
                 VALUES (?, 'score_edited', ?, ?, 'score', ?)",
                [
                    $scoreInfo['judge_user_id'],
                    'Score Updated by Admin',
                    "Your score for Contestant #{$scoreInfo['contestant_number']} ({$scoreInfo['contestant_name']}) has been updated by an admin.",
                    $scoreId
                ]
            );
            
            // Log audit
            $this->logAudit('UPDATE_SCORE', 'scores', $scoreId, $oldScoreDetails, $scores);
            
            $this->db->getConnection()->commit();
            
            $this->json([
                'success' => true, 
                'message' => 'Score updated successfully. Permission has been reset - you must request permission again for future edits.',
                'redirect' => '/tabulation/score-management/round/' . $score['round_id']
            ]);
            
        } catch (Exception $e) {
            $this->db->getConnection()->rollBack();
            $this->json(['success' => false, 'message' => 'Error updating score: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Deduct points from total score (separate page)
     * Requires organizer key
     */
    public function deduct($scoreId) {
        $this->restrictJudges();
        
        $currentRole = Session::get('role_name');
        if (!in_array($currentRole, ['Super Admin', 'Event Admin', 'Event Technical Admin', 'Tabulator'])) {
            $this->accessDenied("Access denied. Only Technical Admin and authorized personnel can apply deductions.");
        }
        
        $score = $this->db->fetchOne(
            "SELECT s.*, 
             c.name as contestant_name, c.contestant_number, c.id as contestant_id,
             r.id as round_id, r.name as round_name, r.level_id,
             j.id as judge_id, j.judge_number,
             u.full_name as judge_name, u.id as judge_user_id,
             el.event_id
             FROM scores s
             JOIN contestants c ON s.contestant_id = c.id
             JOIN rounds r ON s.round_id = r.id
             JOIN event_levels el ON r.level_id = el.id
             JOIN judges j ON s.judge_id = j.id
             JOIN users u ON j.user_id = u.id
             WHERE s.id = ?",
            [$scoreId]
        );
        
        if (!$score) {
            die("Score not found");
        }
        
        $this->requireEventAccess($score['event_id']);
        
        // Prevent modifications to finished events
        $this->preventFinishedEventModification($score['event_id']);
        
        // Get deduction request status
        $score['deduction_request_status'] = $score['deduction_request_status'] ?? 'none';
        
        $this->view('score_management/deduct', [
            'score' => $score
        ]);
    }
    
    /**
     * Request deduction permission from organizer
     */
    public function requestDeduction($scoreId) {
        $this->restrictJudges();
        
        $currentRole = Session::get('role_name');
        if (!in_array($currentRole, ['Super Admin', 'Event Admin', 'Event Technical Admin', 'Tabulator'])) {
            $this->json(['success' => false, 'message' => 'Access denied'], 403);
            return;
        }
        
        $score = $this->db->fetchOne(
            "SELECT s.*, 
             c.contestant_number, c.name as contestant_name,
             r.name as round_name, el.event_id, e.name as event_name
             FROM scores s
             JOIN contestants c ON s.contestant_id = c.id
             JOIN rounds r ON s.round_id = r.id
             JOIN event_levels el ON r.level_id = el.id
             JOIN events e ON el.event_id = e.id
             WHERE s.id = ?",
            [$scoreId]
        );
        
        if (!$score) {
            $this->json(['success' => false, 'message' => 'Score not found'], 404);
            return;
        }
        
        $this->requireEventAccess($score['event_id']);
        
        // Prevent modifications to finished events
        $this->preventFinishedEventModification($score['event_id']);
        
            $pointDeduction = isset($_POST['point_deduction']) ? (float)$_POST['point_deduction'] : 0;
            $deductionReason = $_POST['deduction_reason'] ?? null;
            
        // Validate
        if ($pointDeduction <= 0) {
            $this->json(['success' => false, 'message' => 'Deduction amount must be greater than 0.'], 400);
            return;
        }
        
        if (empty($deductionReason)) {
            $this->json(['success' => false, 'message' => 'Deduction reason is required.'], 400);
            return;
        }
        
        $this->db->getConnection()->beginTransaction();
        
        try {
            // Update score with deduction request
            $this->db->query(
                "UPDATE scores SET 
                 deduction_request_status = 'requested',
                 deduction_requested_by = ?,
                 deduction_requested_at = NOW(),
                 deduction_pending_amount = ?,
                 deduction_pending_reason = ?
                 WHERE id = ?",
                [Session::get('user_id'), $pointDeduction, $deductionReason, $scoreId]
            );
            
            // Get event organizers
            $organizers = $this->db->fetchAll(
                "SELECT u.id, u.full_name
                 FROM users u
                 JOIN roles r ON u.role_id = r.id
                 JOIN user_event_assignments uea ON u.id = uea.user_id
                 WHERE r.name = 'Event Organizer' 
                 AND uea.event_id = ? 
                 AND uea.is_active = 1
                 AND u.is_active = 1",
                [$score['event_id']]
            );
            
            // If no specific organizers, get all organizers for the event
            if (empty($organizers)) {
                $organizers = $this->db->fetchAll(
                    "SELECT u.id, u.full_name
                     FROM users u
                     JOIN roles r ON u.role_id = r.id
                     WHERE r.name = 'Event Organizer' 
                     AND u.is_active = 1"
                );
            }
            
            // Send notification to organizers
            foreach ($organizers as $organizer) {
                $this->db->query(
                    "INSERT INTO notifications (user_id, type, title, message, related_type, related_id)
                     VALUES (?, 'deduction_request', ?, ?, 'score', ?)",
                    [
                        $organizer['id'],
                        'Deduction Permission Request',
                        "Admin " . Session::get('full_name') . " is requesting permission to apply a deduction of {$pointDeduction} points to Contestant #{$score['contestant_number']} ({$score['contestant_name']}) in Round: {$score['round_name']}. Reason: {$deductionReason}",
                        $scoreId
                    ]
                );
            }
            
            $this->db->getConnection()->commit();
            
            $this->json(['success' => true, 'message' => 'Deduction permission request sent to event organizer.']);
            
        } catch (Exception $e) {
            $this->db->getConnection()->rollBack();
            $this->json(['success' => false, 'message' => 'Error sending request: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Respond to deduction request (Organizer only)
     */
    public function respondToDeductionRequest($scoreId) {
        $this->requireRoles(['Event Organizer', 'Super Admin']);
        
        $userId = Session::get('user_id');
        $action = $_POST['action'] ?? ''; // 'grant' or 'deny'
        
        if (!in_array($action, ['grant', 'deny'])) {
            $this->json(['success' => false, 'message' => 'Invalid action'], 400);
            return;
        }
        
        $score = $this->db->fetchOne(
            "SELECT s.*, el.event_id FROM scores s
             JOIN rounds r ON s.round_id = r.id
             JOIN event_levels el ON r.level_id = el.id
             WHERE s.id = ?",
            [$scoreId]
        );
        
        if (!$score) {
            $this->json(['success' => false, 'message' => 'Score not found'], 404);
            return;
        }
        
        // Verify user has access to this event
        $hasAccess = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM event_assignments 
             WHERE user_id = ? AND event_id = ?",
            [$userId, $score['event_id']]
        )['count'] > 0;
        
        $currentRole = Session::get('role_name');
        if (!$hasAccess && $currentRole !== 'Super Admin') {
            $this->json(['success' => false, 'message' => 'You do not have access to this event'], 403);
            return;
        }
        
        // Prevent modifications to finished events
        $this->preventFinishedEventModification($score['event_id']);
        
        $this->db->getConnection()->beginTransaction();
        
        try {
            if ($action === 'grant') {
                // Update status to granted
                $this->db->query(
                    "UPDATE scores SET 
                     deduction_request_status = 'granted',
                     deduction_responded_by = ?,
                     deduction_responded_at = NOW()
                     WHERE id = ?",
                    [$userId, $scoreId]
                );
                
                // Get event organizer key and apply deduction automatically
                $event = $this->db->fetchOne("SELECT organizer_key FROM events WHERE id = ?", [$score['event_id']]);
                
                if (empty($event['organizer_key'])) {
                    $this->db->getConnection()->rollBack();
                    $this->json(['success' => false, 'message' => 'No organizer key has been set for this event.'], 400);
                    return;
                }
                
                // Get pending deduction details
                $scoreWithPending = $this->db->fetchOne(
                    "SELECT deduction_pending_amount, deduction_pending_reason FROM scores WHERE id = ?",
                    [$scoreId]
                );
                
                $pointDeduction = $scoreWithPending['deduction_pending_amount'] ?? 0;
                $deductionReason = $scoreWithPending['deduction_pending_reason'] ?? '';
                
                // Apply deduction
                $this->db->query(
                    "UPDATE scores SET 
                     point_deduction = ?,
                     deduction_reason = ?,
                     deduction_applied_by = ?,
                     deduction_applied_at = NOW(),
                     admin_edited_by = ?,
                     admin_edited_at = NOW(),
                     deduction_pending_amount = NULL,
                     deduction_pending_reason = NULL
                     WHERE id = ?",
                    [$pointDeduction, $deductionReason, $score['deduction_requested_by'], $score['deduction_requested_by'], $scoreId]
                );
                
                // Recalculate total score
                require_once __DIR__ . '/../core/ScoringEngine.php';
                $scoringEngine = new ScoringEngine();
                $scoringEngine->calculateScore($scoreId);
                
                // Get score info for notification
                $scoreInfo = $this->db->fetchOne(
                    "SELECT s.*, j.user_id as judge_user_id, c.contestant_number, c.name as contestant_name
                     FROM scores s
                     JOIN judges j ON s.judge_id = j.id
                     JOIN contestants c ON s.contestant_id = c.id
                     WHERE s.id = ?",
                    [$scoreId]
                );
                
                // Notify requester
                if ($score['deduction_requested_by']) {
                    $this->db->query(
                        "INSERT INTO notifications (user_id, type, title, message, related_type, related_id)
                         VALUES (?, 'deduction_granted', ?, ?, 'score', ?)",
                        [
                            $score['deduction_requested_by'],
                            'Deduction Permission Granted',
                            "Your request to apply a deduction of {$pointDeduction} points has been granted. The deduction has been applied automatically.",
                            $scoreId
                        ]
                    );
                }
                
                // Notify judge
                $this->db->query(
                    "INSERT INTO notifications (user_id, type, title, message, related_type, related_id)
                     VALUES (?, 'deduction_applied', ?, ?, 'score', ?)",
                    [
                        $scoreInfo['judge_user_id'],
                        'Point Deduction Applied',
                        "A deduction of {$pointDeduction} points has been applied to your score for Contestant #{$scoreInfo['contestant_number']} ({$scoreInfo['contestant_name']}). Reason: {$deductionReason}",
                        $scoreId
                    ]
                );
                
                $this->db->getConnection()->commit();
                
                $this->json(['success' => true, 'message' => 'Permission granted and deduction applied automatically.']);
                
            } else {
                // Deny permission
                $this->db->query(
                    "UPDATE scores SET 
                     deduction_request_status = 'denied',
                     deduction_responded_by = ?,
                     deduction_responded_at = NOW(),
                     deduction_pending_amount = NULL,
                     deduction_pending_reason = NULL
                     WHERE id = ?",
                    [$userId, $scoreId]
                );
                
                // Notify requester
                if ($score['deduction_requested_by']) {
                    $this->db->query(
                        "INSERT INTO notifications (user_id, type, title, message, related_type, related_id)
                         VALUES (?, 'deduction_denied', ?, ?, 'score', ?)",
                        [
                            $score['deduction_requested_by'],
                            'Deduction Permission Denied',
                            "Your request to apply a deduction has been denied by the event organizer.",
                            $scoreId
                        ]
                    );
                }
                
                $this->db->getConnection()->commit();
                
                $this->json(['success' => true, 'message' => 'Permission denied.']);
            }
            
        } catch (Exception $e) {
            $this->db->getConnection()->rollBack();
            $this->json(['success' => false, 'message' => 'Error processing request: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Apply deduction to total score
     * Requires organizer key
     */
    public function applyDeduction($scoreId) {
        $this->restrictJudges();
        
        $currentRole = Session::get('role_name');
        if (!in_array($currentRole, ['Super Admin', 'Event Admin', 'Event Technical Admin', 'Tabulator'])) {
            $this->json(['success' => false, 'message' => 'Access denied'], 403);
            return;
        }
        
        $score = $this->db->fetchOne(
            "SELECT s.*, el.event_id FROM scores s
             JOIN rounds r ON s.round_id = r.id
             JOIN event_levels el ON r.level_id = el.id
             WHERE s.id = ?",
            [$scoreId]
        );
        
        if (!$score) {
            $this->json(['success' => false, 'message' => 'Score not found'], 404);
            return;
        }
        
        $this->requireEventAccess($score['event_id']);
        
        // Prevent modifications to finished events
        $this->preventFinishedEventModification($score['event_id']);
        
        // Check if deduction permission has been granted
        if (empty($score['deduction_request_status']) || $score['deduction_request_status'] !== 'granted') {
            $this->json(['success' => false, 'message' => 'Deduction permission has not been granted yet. Please request permission from the organizer first.'], 403);
            return;
        }
        
        // Get deduction details from pending fields or POST
        $pointDeduction = isset($_POST['point_deduction']) ? (float)$_POST['point_deduction'] : ($score['deduction_pending_amount'] ?? 0);
        $deductionReason = $_POST['deduction_reason'] ?? ($score['deduction_pending_reason'] ?? null);
        
        // Validate deduction amount
        if ($pointDeduction <= 0) {
            $this->json(['success' => false, 'message' => 'Deduction amount must be greater than 0.'], 400);
            return;
        }
        
        if (empty($deductionReason)) {
            $this->json(['success' => false, 'message' => 'Deduction reason is required.'], 400);
            return;
        }
        
        $this->db->getConnection()->beginTransaction();
        
        try {
            // Update score with deduction (clear pending fields)
            $this->db->query(
                "UPDATE scores SET 
                 point_deduction = ?,
                 deduction_reason = ?,
                 deduction_applied_by = ?,
                 deduction_applied_at = NOW(),
                 admin_edited_by = ?,
                 admin_edited_at = NOW(),
                 deduction_pending_amount = NULL,
                 deduction_pending_reason = NULL,
                 deduction_request_status = 'applied'
                 WHERE id = ?",
                [$pointDeduction, $deductionReason, Session::get('user_id'), Session::get('user_id'), $scoreId]
            );
            
            // Recalculate total score (applies deduction)
            require_once __DIR__ . '/../core/ScoringEngine.php';
            $scoringEngine = new ScoringEngine();
            $scoringEngine->calculateScore($scoreId);
            
            // Get score info for notification
            $scoreInfo = $this->db->fetchOne(
                "SELECT s.*, j.user_id as judge_user_id, c.contestant_number, c.name as contestant_name
                 FROM scores s
                 JOIN judges j ON s.judge_id = j.id
                 JOIN contestants c ON s.contestant_id = c.id
                 WHERE s.id = ?",
                [$scoreId]
            );
            
            // Create notification for judge
            $this->db->query(
                "INSERT INTO notifications (user_id, type, title, message, related_type, related_id)
                 VALUES (?, 'deduction_applied', ?, ?, 'score', ?)",
                [
                    $scoreInfo['judge_user_id'],
                    'Point Deduction Applied',
                    "A deduction of {$pointDeduction} points has been applied to your score for Contestant #{$scoreInfo['contestant_number']} ({$scoreInfo['contestant_name']}). Reason: {$deductionReason}",
                    $scoreId
                ]
            );
            
            // Log audit
            $this->logAudit('APPLY_DEDUCTION', 'scores', $scoreId, null, [
                'deduction' => $pointDeduction,
                'reason' => $deductionReason
            ]);
            
            $this->db->getConnection()->commit();
            
            $this->json(['success' => true, 'message' => 'Deduction applied successfully.']);
            
        } catch (Exception $e) {
            $this->db->getConnection()->rollBack();
            $this->json(['success' => false, 'message' => 'Error applying deduction: ' . $e->getMessage()], 500);
        }
    }
    
    public function toggleEditPermission($scoreId) {
        $this->requireRoles(['Judge']);
        
        $userId = Session::get('user_id');
        
        // Verify this is the judge's score
        $score = $this->db->fetchOne(
            "SELECT s.*, j.user_id as judge_user_id
             FROM scores s
             JOIN judges j ON s.judge_id = j.id
             WHERE s.id = ?",
            [$scoreId]
        );
        
        if (!$score) {
            $this->json(['success' => false, 'message' => 'Score not found'], 404);
            return;
        }
        
        if ($score['judge_user_id'] != $userId) {
            $this->json(['success' => false, 'message' => 'You can only modify permissions for your own scores'], 403);
            return;
        }
        
        $newStatus = $score['admin_edit_allowed'] ? 0 : 1;
        $this->db->query(
            "UPDATE scores SET admin_edit_allowed = ? WHERE id = ?",
            [$newStatus, $scoreId]
        );
        
        $this->json([
            'success' => true, 
            'message' => $newStatus ? 'Admin edit permission granted' : 'Admin edit permission revoked',
            'admin_edit_allowed' => $newStatus
        ]);
    }
    
    public function respondToPermissionRequest($scoreId) {
        $this->requireRoles(['Judge']);
        
        $userId = Session::get('user_id');
        $action = $_POST['action'] ?? ''; // 'grant' or 'deny'
        
        // Verify this is the judge's score
        $score = $this->db->fetchOne(
            "SELECT s.*, j.user_id as judge_user_id
             FROM scores s
             JOIN judges j ON s.judge_id = j.id
             WHERE s.id = ?",
            [$scoreId]
        );
        
        if (!$score) {
            $this->json(['success' => false, 'message' => 'Score not found'], 404);
            return;
        }
        
        if ($score['judge_user_id'] != $userId) {
            $this->json(['success' => false, 'message' => 'You can only respond to requests for your own scores'], 403);
            return;
        }
        
        if ($action === 'grant') {
            // Judge is granting admin's request to edit
            // Set both admin_edit_allowed and permission status
            $this->db->query(
                "UPDATE scores SET 
                 admin_edit_allowed = 1,
                 permission_request_status = 'granted',
                 permission_granted_at = NOW(),
                 permission_responded_by = ?,
                 permission_responded_at = NOW()
                 WHERE id = ?",
                [$userId, $scoreId]
            );
            
            // Notify admin who requested
            if ($score['permission_requested_by']) {
                $this->db->query(
                    "INSERT INTO notifications (user_id, type, title, message, related_type, related_id)
                     VALUES (?, 'permission_granted', ?, ?, 'score', ?)",
                    [
                        $score['permission_requested_by'],
                        'Permission Granted',
                        "Judge has granted permission to edit score ID {$scoreId}",
                        $scoreId
                    ]
                );
            }
            
            $this->json(['success' => true, 'message' => 'Permission granted']);
        } else {
            // Judge is denying admin's request
            $this->db->query(
                "UPDATE scores SET 
                 permission_request_status = 'denied',
                 permission_denied_at = NOW(),
                 permission_responded_by = ?,
                 permission_responded_at = NOW()
                 WHERE id = ?",
                [$userId, $scoreId]
            );
            
            // Notify admin who requested
            if ($score['permission_requested_by']) {
                $this->db->query(
                    "INSERT INTO notifications (user_id, type, title, message, related_type, related_id)
                     VALUES (?, 'permission_denied', ?, ?, 'score', ?)",
                    [
                        $score['permission_requested_by'],
                        'Permission Denied',
                        "Judge has denied permission to edit score ID {$scoreId}",
                        $scoreId
                    ]
                );
            }
            
            $this->json(['success' => true, 'message' => 'Permission denied']);
        }
    }
    
    /**
     * Print scores for a specific round - grouped by judge
     * Shows: Contestant Number, Raw Scores (per criterion)
     */
    public function printRoundScores($roundId, $judgeId = null) {
        $this->restrictJudges();
        
        $round = $this->db->fetchOne(
            "SELECT r.*, el.name as level_name, el.event_id
             FROM rounds r
             JOIN event_levels el ON r.level_id = el.id
             WHERE r.id = ?",
            [$roundId]
        );
        
        if (!$round) {
            die("Round not found");
        }
        
        $this->requireEventAccess($round['event_id']);
        
        // Get criteria for this round
        $criteria = $this->db->fetchAll(
            "SELECT c.*, cw.weight
             FROM criteria_weights cw
             JOIN criteria c ON cw.criteria_id = c.id
             WHERE cw.round_id = ? AND cw.is_active = 1
             ORDER BY c.id, c.name",
            [$roundId]
        );
        
        // Get all judges for this round
        $judges = $this->db->fetchAll(
            "SELECT DISTINCT j.*, u.full_name as judge_name
             FROM judges j
             JOIN users u ON j.user_id = u.id
             JOIN judge_assignments ja ON j.id = ja.judge_id
             WHERE ja.round_id = ? AND ja.is_active = 1
             ORDER BY j.judge_number",
            [$roundId]
        );
        
        // Filter by judge if specified
        if ($judgeId) {
            $judges = array_filter($judges, function($j) use ($judgeId) {
                return $j['id'] == $judgeId;
            });
        }
        
        // Get all scores for this round (draft and submitted)
        $scoresQuery = "SELECT s.*, c.contestant_number, c.name as contestant_name,
                               j.id as judge_id, j.judge_number, u.full_name as judge_name
             FROM scores s
             JOIN contestants c ON s.contestant_id = c.id
             JOIN judges j ON s.judge_id = j.id
             JOIN users u ON j.user_id = u.id
             WHERE s.round_id = ?";
        
        $scoresParams = [$roundId];
        if ($judgeId) {
            $scoresQuery .= " AND s.judge_id = ?";
            $scoresParams[] = $judgeId;
        }
        
        $scoresQuery .= " ORDER BY j.judge_number, CAST(c.contestant_number AS UNSIGNED)";
        
        $scores = $this->db->fetchAll($scoresQuery, $scoresParams);
        
        // Get score details
        $scoreIds = array_column($scores, 'id');
        $scoreDetails = [];
        if (!empty($scoreIds)) {
            $placeholders = implode(',', array_fill(0, count($scoreIds), '?'));
            $details = $this->db->fetchAll(
                "SELECT * FROM score_details WHERE score_id IN ($placeholders)",
                $scoreIds
            );
            
            foreach ($details as $detail) {
                $scoreDetails[$detail['score_id']][] = $detail;
            }
        }
        
        // Group scores by judge
        $judgeData = [];
        foreach ($scores as $score) {
            $judgeId = $score['judge_id'];
            if (!isset($judgeData[$judgeId])) {
                $judgeData[$judgeId] = [
                    'judge' => [
                        'id' => $judgeId,
                        'judge_number' => $score['judge_number'],
                        'judge_name' => $score['judge_name']
                    ],
                    'scores' => []
                ];
            }
            $score['details'] = $scoreDetails[$score['id']] ?? [];
            $judgeData[$judgeId]['scores'][] = $score;
        }
        
        // Get event name
        $event = $this->db->fetchOne("SELECT name as event_name FROM events WHERE id = ?", [$round['event_id']]);
        $round['event_name'] = $event['event_name'] ?? '';
        
        $this->view('score_management/print_round', [
            'round' => $round,
            'criteria' => $criteria,
            'judges' => $judges,
            'judgeData' => $judgeData
        ]);
    }
    
    /**
     * Print all scores for an event
     */
    public function printScores($eventId) {
        $this->restrictJudges();
        $this->requireEventAccess($eventId);
        
        $event = $this->db->fetchOne("SELECT * FROM events WHERE id = ?", [$eventId]);
        if (!$event) {
            die("Event not found");
        }
        
        // Get all rounds for this event
        $rounds = $this->db->fetchAll(
            "SELECT r.*, el.name as level_name, el.order as level_order
             FROM rounds r
             JOIN event_levels el ON r.level_id = el.id
             WHERE el.event_id = ?
             ORDER BY el.order, r.order",
            [$eventId]
        );
        
        $roundData = [];
        foreach ($rounds as $round) {
            // Get criteria
            $criteria = $this->db->fetchAll(
                "SELECT c.*, cw.weight
                 FROM criteria_weights cw
                 JOIN criteria c ON cw.criteria_id = c.id
                 WHERE cw.round_id = ? AND cw.is_active = 1
                 ORDER BY c.id, c.name",
                [$round['id']]
            );
            
        // Get scores (draft and submitted)
            $scores = $this->db->fetchAll(
                "SELECT s.*, c.contestant_number, c.name as contestant_name,
                        j.id as judge_id, j.judge_number, u.full_name as judge_name
                 FROM scores s
                 JOIN contestants c ON s.contestant_id = c.id
                 JOIN judges j ON s.judge_id = j.id
                 JOIN users u ON j.user_id = u.id
             WHERE s.round_id = ?
                 ORDER BY j.judge_number, CAST(c.contestant_number AS UNSIGNED)",
                [$round['id']]
            );
            
            // Get score details
            $scoreIds = array_column($scores, 'id');
            $scoreDetails = [];
            if (!empty($scoreIds)) {
                $placeholders = implode(',', array_fill(0, count($scoreIds), '?'));
                $details = $this->db->fetchAll(
                    "SELECT * FROM score_details WHERE score_id IN ($placeholders)",
                    $scoreIds
                );
                
                foreach ($details as $detail) {
                    $scoreDetails[$detail['score_id']][] = $detail;
                }
            }
            
            // Group by judge
            $judgeData = [];
            foreach ($scores as $score) {
                $judgeId = $score['judge_id'];
                if (!isset($judgeData[$judgeId])) {
                    $judgeData[$judgeId] = [
                        'judge' => [
                            'id' => $judgeId,
                            'judge_number' => $score['judge_number'],
                            'judge_name' => $score['judge_name']
                        ],
                        'scores' => []
                    ];
                }
                $score['details'] = $scoreDetails[$score['id']] ?? [];
                $judgeData[$judgeId]['scores'][] = $score;
            }
            
            $roundData[] = [
                'round' => $round,
                'criteria' => $criteria,
                'judgeData' => $judgeData
            ];
        }
        
        $this->view('score_management/print_all', [
            'event' => $event,
            'roundData' => $roundData
        ]);
    }
    
}
