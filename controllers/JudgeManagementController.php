<?php
/**
 * Judge Management Controller - Global judge management
 */

class JudgeManagementController extends Controller {
    
    public function index() {
        $this->restrictJudges();
        
        // Get filter parameters
        $filterJudge = $_GET['filter_judge'] ?? '';
        $filterRound = $_GET['filter_round'] ?? '';
        
        // Check if user is Event Organizer and filter by their assignments
        $userId = Session::get('user_id');
        $roleName = Session::get('role_name');
        
        if ($roleName === 'Event Organizer') {
            // For Event Organizers, only show judges assigned to them
            $query = "SELECT j.*, u.full_name, u.username, u.email, u.is_active as user_active,
                     e.name as event_name, e.id as event_id,
                     joa.id as assignment_id,
                     (SELECT COUNT(*) FROM judge_assignments WHERE judge_id = j.id AND is_active = 1) as assigned_rounds
                     FROM judge_organizer_assignments joa
                     JOIN judges j ON joa.judge_id = j.id
                     JOIN users u ON j.user_id = u.id
                     JOIN events e ON joa.event_id = e.id
                     WHERE joa.organizer_id = ? AND joa.is_active = 1 AND j.is_active = 1";
            
            $params = [$userId];
            
            // Filter by judge name
            if (!empty($filterJudge)) {
                $query .= " AND (u.full_name LIKE ? OR u.username LIKE ? OR j.judge_number LIKE ?)";
                $searchTerm = '%' . $filterJudge . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            $query .= " ORDER BY e.name, j.judge_number";
            
            $judges = $this->db->fetchAll($query, $params);
            
            // Get accessible events for this organizer
            $events = $this->getAccessibleEvents();
            
            // Get rounds for filter (only from organizer's events)
            $rounds = [];
            if (!empty($events)) {
                $eventIds = array_column($events, 'id');
                $placeholders = implode(',', array_fill(0, count($eventIds), '?'));
                $rounds = $this->db->fetchAll(
                    "SELECT r.id, r.name, el.name as level_name, e.name as event_name, e.id as event_id
                         FROM rounds r
                         JOIN event_levels el ON r.level_id = el.id
                         JOIN events e ON el.event_id = e.id
                         WHERE r.is_active = 1 AND e.id IN ($placeholders)
                         ORDER BY e.name, el.`order`, r.`order`, r.name",
                        $eventIds
                );
            }
            
            // Get all judge users for organizer (for adding new judges)
            $allJudgeUsers = [];
            
        } else {
            // For other roles (Super Admin, Tech Admin), show all judges
            $query = "SELECT j.*, u.full_name, u.username, u.email, u.is_active as user_active,
                     e.name as event_name, e.id as event_id,
                     NULL as assignment_id,
                     (SELECT COUNT(*) FROM judge_assignments WHERE judge_id = j.id AND is_active = 1) as assigned_rounds
                     FROM judges j
                     JOIN users u ON j.user_id = u.id
                     JOIN events e ON j.event_id = e.id
                     WHERE j.is_active = 1";
            
            $params = [];
            
            // Filter by judge name
            if (!empty($filterJudge)) {
                $query .= " AND (u.full_name LIKE ? OR u.username LIKE ? OR j.judge_number LIKE ?)";
                $searchTerm = '%' . $filterJudge . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            // Filter by round
            if (!empty($filterRound)) {
                $query .= " AND EXISTS (
                    SELECT 1 FROM judge_assignments ja
                    WHERE ja.judge_id = j.id AND ja.round_id = ? AND ja.is_active = 1
                )";
                $params[] = $filterRound;
            }
            
            $query .= " ORDER BY e.name, j.judge_number";
            
            $judges = $this->db->fetchAll($query, $params);
            
            // Get accessible events for filter
            $events = $this->getAccessibleEvents();
            $accessibleEventIds = array_column($events, 'id');
            
            // Get all rounds for filter dropdown (only from accessible events)
            $rounds = [];
            if (!empty($accessibleEventIds)) {
                $placeholders = implode(',', array_fill(0, count($accessibleEventIds), '?'));
                $rounds = $this->db->fetchAll(
                    "SELECT r.id, r.name, el.name as level_name, e.name as event_name, e.id as event_id
                         FROM rounds r
                         JOIN event_levels el ON r.level_id = el.id
                         JOIN events e ON el.event_id = e.id
                         WHERE r.status = 'Active' AND e.id IN ($placeholders)
                         ORDER BY e.name, el.`order`, r.`order`, r.name",
                    $accessibleEventIds
                );
            }
            
            // Get all users with Judge role
            $allJudgeUsers = $this->db->fetchAll(
                "SELECT u.*, 
                 (SELECT COUNT(*) FROM judges WHERE user_id = u.id AND is_active = 1) as event_count
                 FROM users u
                 JOIN roles r ON u.role_id = r.id
                 WHERE r.name = 'Judge' AND u.is_active = 1
                 ORDER BY u.full_name"
            );
        }
        
        $this->view('judge_management/index', [
            'judges' => $judges,
            'events' => $events,
            'rounds' => $rounds,
            'allJudgeUsers' => $allJudgeUsers,
            'filterJudge' => $filterJudge,
            'filterRound' => $filterRound,
            'isOrganizer' => $roleName === 'Event Organizer'
        ]);
    }
    
    public function create() {
        $this->restrictJudges();
        
        // Get accessible events
        $events = $this->getAccessibleEvents();
        
        $this->view('judge_management/create', [
            'events' => $events
        ]);
    }
    
    public function store() {
        $this->restrictJudges();
        
        $errors = $this->validateInput($_POST, [
            'username' => 'required|min:3',
            'email' => 'required|email',
            'full_name' => 'required|min:2',
            'password' => 'required|min:6'
        ]);
        
        if (!empty($errors)) {
            $events = $this->db->fetchAll("SELECT * FROM events ORDER BY name");
            $this->view('judge_management/create', [
                'errors' => $errors,
                'events' => $events,
                'data' => $_POST
            ]);
            return;
        }
        
        // Check if username or email already exists
        $existing = $this->db->fetchOne(
            "SELECT * FROM users WHERE username = ? OR email = ?",
            [$_POST['username'], $_POST['email']]
        );
        
        if ($existing) {
            $events = $this->db->fetchAll("SELECT * FROM events ORDER BY name");
            $this->view('judge_management/create', [
                'errors' => ['username' => 'Username or email already exists'],
                'events' => $events,
                'data' => $_POST
            ]);
            return;
        }
        
        // Get Judge role ID
        $role = $this->db->fetchOne("SELECT id FROM roles WHERE name = 'Judge'");
        if (!$role) {
            die("Judge role not found. Please run database schema.");
        }
        
        // Create user
        $passwordHash = password_hash($_POST['password'], PASSWORD_BCRYPT);
        
        $this->db->query(
            "INSERT INTO users (username, email, password_hash, full_name, role_id, is_active)
             VALUES (?, ?, ?, ?, ?, 1)",
            [
                $_POST['username'],
                $_POST['email'],
                $passwordHash,
                $_POST['full_name'],
                $role['id']
            ]
        );
        
        $userId = $this->db->lastInsertId();
        
        // Assign to event if specified
        if (!empty($_POST['event_id'])) {
            $this->db->query(
                "INSERT INTO judges (user_id, event_id, judge_number, specialty)
                 VALUES (?, ?, ?, ?)",
                [
                    $userId,
                    $_POST['event_id'],
                    $_POST['judge_number'] ?? null,
                    $_POST['specialty'] ?? null
                ]
            );
        }
        
        $this->logAudit('CREATE_JUDGE', 'users', $userId, null, $_POST);
        
        Session::set('success_message', 'Judge created successfully');
        $this->redirect('/tabulation/judge-management');
    }
    
    public function assignToEvent() {
        $this->restrictJudges();
        
        $userId = $_POST['user_id'] ?? null;
        $eventId = $_POST['event_id'] ?? null;
        
        if (!$userId || !$eventId) {
            $this->json(['success' => false, 'message' => 'User and Event are required'], 400);
            return;
        }
        
        // Prevent modifications to finished events
        $this->preventFinishedEventModification($eventId);
        
        // Check if already assigned
        $existing = $this->db->fetchOne(
            "SELECT * FROM judges WHERE user_id = ? AND event_id = ?",
            [$userId, $eventId]
        );
        
        if ($existing) {
            if (!$existing['is_active']) {
                // Reactivate
                $this->db->query(
                    "UPDATE judges SET is_active = 1, judge_number = ?, specialty = ? WHERE id = ?",
                    [
                        $_POST['judge_number'] ?? $existing['judge_number'],
                        $_POST['specialty'] ?? $existing['specialty'],
                        $existing['id']
                    ]
                );
                $this->json(['success' => true, 'message' => 'Judge reactivated for this event']);
            } else {
                $this->json(['success' => false, 'message' => 'Judge is already assigned to this event'], 400);
            }
            return;
        }
        
        // Create assignment
        $this->db->query(
            "INSERT INTO judges (user_id, event_id, judge_number, specialty)
             VALUES (?, ?, ?, ?)",
            [
                $userId,
                $eventId,
                $_POST['judge_number'] ?? null,
                $_POST['specialty'] ?? null
            ]
        );
        
        // Auto-assign judge user to event (for event filtering)
        $this->db->query(
            "INSERT INTO user_event_assignments (user_id, event_id, assigned_by, is_active)
             VALUES (?, ?, ?, 1)
             ON DUPLICATE KEY UPDATE is_active = 1",
            [$userId, $eventId, Session::get('user_id')]
        );
        
        $this->logAudit('ASSIGN_JUDGE_EVENT', 'judges', $this->db->lastInsertId(), null, $_POST);
        
        $this->json(['success' => true, 'message' => 'Judge assigned to event successfully']);
    }
    
    public function removeFromEvent($judgeId) {
        $this->restrictJudges();
        
        // Get event_id from judge to check if event is finished
        $judge = $this->db->fetchOne("SELECT event_id FROM judges WHERE id = ?", [$judgeId]);
        
        if (!$judge) {
            Session::set('error_message', 'Judge not found');
            $this->redirect('/tabulation/judge-management');
            return;
        }
        
        // Prevent modifications to finished events
        $this->preventFinishedEventModification($judge['event_id']);
        
        $this->db->query("UPDATE judges SET is_active = 0 WHERE id = ?", [$judgeId]);
        $this->logAudit('REMOVE_JUDGE_EVENT', 'judges', $judgeId, null, null);
        
        Session::set('success_message', 'Judge removed from event successfully');
        $this->redirect('/tabulation/judge-management');
    }
    
    public function edit($id) {
        $this->restrictJudges();
        
        $judge = $this->db->fetchOne(
            "SELECT j.*, u.full_name, u.username, u.email, e.name as event_name
             FROM judges j
             JOIN users u ON j.user_id = u.id
             JOIN events e ON j.event_id = e.id
             WHERE j.id = ?",
            [$id]
        );
        
        if (!$judge) {
            die("Judge not found");
        }
        
        $events = $this->db->fetchAll("SELECT * FROM events ORDER BY name");
        
        $this->view('judge_management/edit', [
            'judge' => $judge,
            'events' => $events
        ]);
    }
    
    public function update($id) {
        $this->restrictJudges();
        
        $judge = $this->db->fetchOne("SELECT * FROM judges WHERE id = ?", [$id]);
        if (!$judge) {
            die("Judge not found");
        }
        
        // Prevent modifications to finished events
        $this->preventFinishedEventModification($judge['event_id']);
        
        $this->db->query(
            "UPDATE judges SET judge_number = ?, specialty = ? WHERE id = ?",
            [
                $_POST['judge_number'] ?? null,
                $_POST['specialty'] ?? null,
                $id
            ]
        );
        
        // Update user info if provided
        if (!empty($_POST['full_name'])) {
            $this->db->query(
                "UPDATE users SET full_name = ? WHERE id = ?",
                [$_POST['full_name'], $judge['user_id']]
            );
        }
        
        // Update password if provided
        if (!empty($_POST['password'])) {
            $passwordHash = password_hash($_POST['password'], PASSWORD_BCRYPT);
            $this->db->query(
                "UPDATE users SET password_hash = ? WHERE id = ?",
                [$passwordHash, $judge['user_id']]
            );
        }
        
        $this->logAudit('UPDATE_JUDGE', 'judges', $id, null, $_POST);
        
        Session::set('success_message', 'Judge updated successfully');
        $this->redirect('/tabulation/judge-management');
    }
    
    public function assignRounds($id) {
        $this->restrictJudges();
        
        $judge = $this->db->fetchOne(
            "SELECT j.*, u.full_name, u.username, u.email, e.id as event_id, e.name as event_name
             FROM judges j
             JOIN users u ON j.user_id = u.id
             JOIN events e ON j.event_id = e.id
             WHERE j.id = ?",
            [$id]
        );
        
        if (!$judge) {
            die("Judge not found");
        }
        
        // Get all rounds for this event with criteria
        $rounds = $this->db->fetchAll(
            "SELECT r.*, el.name as level_name,
             (SELECT COUNT(*) FROM judge_assignments WHERE judge_id = ? AND round_id = r.id AND is_active = 1) as is_assigned,
             (SELECT is_preparation_only FROM judge_assignments WHERE judge_id = ? AND round_id = r.id AND is_active = 1) as is_preparation_only
             FROM rounds r
             JOIN event_levels el ON r.level_id = el.id
             WHERE el.event_id = ?
             ORDER BY el.`order`, r.`order`",
            [$id, $id, $judge['event_id']]
        );
        
        // Load criteria for each round
        foreach ($rounds as &$round) {
            $round['criteria'] = $this->db->fetchAll(
                "SELECT c.*, 
                 (SELECT COUNT(*) FROM judge_criteria_assignments 
                  WHERE judge_id = ? AND round_id = ? AND criteria_id = c.id AND is_active = 1) as is_assigned
                 FROM criteria c
                 JOIN criteria_weights cw ON c.id = cw.criteria_id AND cw.round_id = ? AND cw.is_active = 1
                 WHERE c.event_id = ?
                 ORDER BY c.`name`",
                [$id, $round['id'], $round['id'], $judge['event_id']]
            );
        }
        
        $this->view('judge_management/assign_rounds', [
            'judge' => $judge,
            'rounds' => $rounds
        ]);
    }
    
    public function updateRoundAssignments($id) {
        $this->restrictJudges();
        
        $roundIds = $_POST['round_ids'] ?? [];
        $preparationOnly = $_POST['preparation_only'] ?? [];
        
        // Get judge to find event_id
        $judge = $this->db->fetchOne("SELECT event_id FROM judges WHERE id = ?", [$id]);
        
        if (!$judge) {
            $this->json(['success' => false, 'message' => 'Judge not found'], 404);
            return;
        }
        
        // Prevent modifications to finished events
        $this->preventFinishedEventModification($judge['event_id']);
        
        // Get current assignments
        $currentAssignments = $this->db->fetchAll(
            "SELECT round_id, is_preparation_only FROM judge_assignments WHERE judge_id = ? AND is_active = 1",
            [$id]
        );
        $currentRoundIds = array_column($currentAssignments, 'round_id');
        $currentPrepOnly = array_column($currentAssignments, 'is_preparation_only', 'round_id');
        
        // Deactivate removed assignments
        $toRemove = array_diff($currentRoundIds, $roundIds);
        if (!empty($toRemove)) {
            $placeholders = implode(',', array_fill(0, count($toRemove), '?'));
            $this->db->query(
                "UPDATE judge_assignments SET is_active = 0 WHERE judge_id = ? AND round_id IN ($placeholders)",
                array_merge([$id], $toRemove)
            );
        }
        
        // Activate/add new assignments
        $toAdd = array_diff($roundIds, $currentRoundIds);
        foreach ($toAdd as $roundId) {
            $existing = $this->db->fetchOne(
                "SELECT * FROM judge_assignments WHERE judge_id = ? AND round_id = ?",
                [$id, $roundId]
            );
            
            $isPrepOnly = isset($preparationOnly[$roundId]) ? 1 : 0;
            
            if ($existing) {
                $this->db->query(
                    "UPDATE judge_assignments SET is_active = 1, is_preparation_only = ? WHERE id = ?",
                    [$isPrepOnly, $existing['id']]
                );
            } else {
                $this->db->query(
                    "INSERT INTO judge_assignments (judge_id, round_id, is_preparation_only) VALUES (?, ?, ?)",
                    [$id, $roundId, $isPrepOnly]
                );
            }
        }
        
        // Update preparation-only status for existing assignments
        $toUpdate = array_intersect($currentRoundIds, $roundIds);
        foreach ($toUpdate as $roundId) {
            $newPrepOnly = isset($preparationOnly[$roundId]) ? 1 : 0;
            $currentPrepValue = $currentPrepOnly[$roundId] ?? 0;
            
            if ($newPrepOnly != $currentPrepValue) {
                $this->db->query(
                    "UPDATE judge_assignments SET is_preparation_only = ? WHERE judge_id = ? AND round_id = ? AND is_active = 1",
                    [$newPrepOnly, $id, $roundId]
                );
            }
        }
        
        // Handle criteria assignments
        $criteriaAssignments = $_POST['criteria'] ?? [];
        
        // Get current criteria assignments
        $currentCriteriaAssignments = $this->db->fetchAll(
            "SELECT round_id, criteria_id FROM judge_criteria_assignments WHERE judge_id = ? AND is_active = 1",
            [$id]
        );
        $currentCriteriaKeys = [];
        foreach ($currentCriteriaAssignments as $assignment) {
            $currentCriteriaKeys[] = $assignment['round_id'] . '_' . $assignment['criteria_id'];
        }
        
        // Process new criteria assignments
        foreach ($criteriaAssignments as $roundId => $criteriaIds) {
            if (!in_array($roundId, $roundIds)) {
                continue; // Skip criteria if round is not assigned
            }
            
            foreach ($criteriaIds as $criteriaId) {
                $key = $roundId . '_' . $criteriaId;
                
                if (!in_array($key, $currentCriteriaKeys)) {
                    // Add new criteria assignment
                    $this->db->query(
                        "INSERT INTO judge_criteria_assignments (judge_id, round_id, criteria_id) VALUES (?, ?, ?)",
                        [$id, $roundId, $criteriaId]
                    );
                }
                
                // Remove from current keys to avoid deactivating
                $index = array_search($key, $currentCriteriaKeys);
                if ($index !== false) {
                    unset($currentCriteriaKeys[$index]);
                }
            }
        }
        
        // Deactivate removed criteria assignments
        foreach ($currentCriteriaKeys as $key) {
            list($roundId, $criteriaId) = explode('_', $key);
            $this->db->query(
                "UPDATE judge_criteria_assignments SET is_active = 0 WHERE judge_id = ? AND round_id = ? AND criteria_id = ?",
                [$id, $roundId, $criteriaId]
            );
        }
        
        $this->logAudit('UPDATE_ROUND_ASSIGNMENTS', 'judge_assignments', $id, null, [
            'round_ids' => $roundIds,
            'preparation_only' => $preparationOnly,
            'criteria_assignments' => $criteriaAssignments
        ]);
        
        // Get judge info to determine redirect
        $judge = $this->db->fetchOne(
            "SELECT j.*, e.id as event_id FROM judges j JOIN events e ON j.event_id = e.id WHERE j.id = ?",
            [$id]
        );
        
        Session::set('success_message', 'Round assignments updated successfully');
        
        // Redirect back to event judge page if accessed from event, otherwise global judge management
        $redirectUrl = isset($_POST['return_to_event']) && $judge 
            ? "/tabulation/events/{$judge['event_id']}/judges"
            : '/tabulation/judge-management';
        $this->redirect($redirectUrl);
    }
    
    /**
     * Import judges from CSV file
     */
    public function import() {
        $this->restrictJudges();
        
        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            Session::set('error_message', 'File upload failed');
            $this->redirect('/tabulation/judge-management');
            return;
        }
        
        $file = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($file, 'r');
        
        if ($handle === false) {
            Session::set('error_message', 'Could not open file');
            $this->redirect('/tabulation/judge-management');
            return;
        }
        
        $imported = 0;
        $skipped = 0;
        $errors = [];
        
        // Get Judge role ID
        $role = $this->db->fetchOne("SELECT id FROM roles WHERE name = 'Judge'");
        if (!$role) {
            Session::set('error_message', 'Judge role not found. Please run database schema.');
            $this->redirect('/tabulation/judge-management');
            return;
        }
        
        // Skip header row
        fgetcsv($handle);
        
        while (($data = fgetcsv($handle)) !== false) {
            if (count($data) < 4) {
                $skipped++;
                continue;
            }
            
            $username = trim($data[0]);
            $email = trim($data[1]);
            $fullName = trim($data[2]);
            $password = trim($data[3]);
            $eventId = isset($data[4]) && !empty(trim($data[4])) ? trim($data[4]) : null;
            $judgeNumber = isset($data[5]) && !empty(trim($data[5])) ? trim($data[5]) : null;
            $specialty = isset($data[6]) && !empty(trim($data[6])) ? trim($data[6]) : null;
            
            // Validate required fields
            if (empty($username) || empty($email) || empty($fullName) || empty($password)) {
                $skipped++;
                $errors[] = "Row skipped: Missing required fields (username, email, full_name, or password)";
                continue;
            }
            
            // Validate email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $skipped++;
                $errors[] = "Row skipped: Invalid email format for {$username}";
                continue;
            }
            
            // Validate password length
            if (strlen($password) < 6) {
                $skipped++;
                $errors[] = "Row skipped: Password too short for {$username} (minimum 6 characters)";
                continue;
            }
            
            // Check if username or email already exists
            $existing = $this->db->fetchOne(
                "SELECT * FROM users WHERE username = ? OR email = ?",
                [$username, $email]
            );
            
            if ($existing) {
                $skipped++;
                $errors[] = "Row skipped: Username or email already exists for {$username}";
                continue;
            }
            
            // Validate event_id if provided
            if ($eventId) {
                $event = $this->db->fetchOne("SELECT * FROM events WHERE id = ?", [$eventId]);
                if (!$event) {
                    $skipped++;
                    $errors[] = "Row skipped: Invalid event_id for {$username}";
                    continue;
                }
                
                // Check event access
                if (!$this->hasEventAccess($eventId)) {
                    $skipped++;
                    $errors[] = "Row skipped: No access to event for {$username}";
                    continue;
                }
            }
            
            $this->db->getConnection()->beginTransaction();
            
            try {
                // Create user
                $passwordHash = password_hash($password, PASSWORD_BCRYPT);
                
                $this->db->query(
                    "INSERT INTO users (username, email, password_hash, full_name, role_id, is_active)
                     VALUES (?, ?, ?, ?, ?, 1)",
                    [$username, $email, $passwordHash, $fullName, $role['id']]
                );
                
                $userId = $this->db->lastInsertId();
                
                // Assign to event if specified
                if ($eventId) {
                    // Check if already assigned
                    $existingJudge = $this->db->fetchOne(
                        "SELECT * FROM judges WHERE user_id = ? AND event_id = ?",
                        [$userId, $eventId]
                    );
                    
                    if (!$existingJudge) {
                        $this->db->query(
                            "INSERT INTO judges (user_id, event_id, judge_number, specialty)
                             VALUES (?, ?, ?, ?)",
                            [$userId, $eventId, $judgeNumber, $specialty]
                        );
                        
                        // Auto-assign judge user to event (for event filtering)
                        $this->db->query(
                            "INSERT INTO user_event_assignments (user_id, event_id, assigned_by, is_active)
                             VALUES (?, ?, ?, 1)
                             ON DUPLICATE KEY UPDATE is_active = 1",
                            [$userId, $eventId, Session::get('user_id')]
                        );
                    }
                }
                
                $this->logAudit('IMPORT_JUDGE', 'users', $userId, null, [
                    'username' => $username,
                    'email' => $email,
                    'event_id' => $eventId
                ]);
                
                $this->db->getConnection()->commit();
                $imported++;
                
            } catch (Exception $e) {
                $this->db->getConnection()->rollBack();
                $skipped++;
                $errors[] = "Row skipped: Error creating user {$username} - " . $e->getMessage();
            }
        }
        
        fclose($handle);
        
        $message = "Imported {$imported} judges. {$skipped} skipped.";
        if (!empty($errors) && count($errors) <= 10) {
            $message .= "\n\nErrors:\n" . implode("\n", array_slice($errors, 0, 10));
            if (count($errors) > 10) {
                $message .= "\n... and " . (count($errors) - 10) . " more errors.";
            }
        }
        
        Session::set('success_message', $message);
        $this->redirect('/tabulation/judge-management');
    }
    
    /**
     * Show judge assignments filtered by organizer
     */
    public function organizerJudges() {
        $this->requireRoles(['Event Organizer', 'Super Admin', 'Event Technical Admin']);
        
        $organizerId = Session::get('user_id');
        $eventId = $_GET['event_id'] ?? null;
        
        if (!$eventId) {
            Session::set('error_message', 'Event ID is required');
            $this->redirect('/tabulation/events');
            return;
        }
        
        // Check event access
        if (!$this->hasEventAccess($eventId)) {
            $this->accessDenied("You don't have access to this event");
        }
        
        // Get judges assigned to this organizer for this event
        $judges = $this->db->fetchAll(
            "SELECT j.*, u.full_name, u.username, u.email, e.name as event_name,
             (SELECT COUNT(*) FROM judge_assignments WHERE judge_id = j.id AND is_active = 1) as assigned_rounds
             FROM judge_organizer_assignments joa
             JOIN judges j ON joa.judge_id = j.id
             JOIN users u ON j.user_id = u.id
             JOIN events e ON joa.event_id = e.id
             WHERE joa.organizer_id = ? AND joa.event_id = ? AND joa.is_active = 1 AND j.is_active = 1
             ORDER BY j.judge_number",
            [$organizerId, $eventId]
        );
        
        // Get available judges not assigned to this organizer
        $availableJudges = $this->db->fetchAll(
            "SELECT j.*, u.full_name, u.username, u.email
             FROM judges j
             JOIN users u ON j.user_id = u.id
             WHERE j.event_id = ? AND j.is_active = 1
             AND j.id NOT IN (
                 SELECT judge_id FROM judge_organizer_assignments 
                 WHERE organizer_id = ? AND event_id = ? AND is_active = 1
             )
             ORDER BY u.full_name",
            [$eventId, $organizerId, $eventId]
        );
        
        $event = $this->db->fetchOne("SELECT * FROM events WHERE id = ?", [$eventId]);
        
        $this->view('judge_management/organizer_judges', [
            'judges' => $judges,
            'availableJudges' => $availableJudges,
            'event' => $event,
            'eventId' => $eventId
        ]);
    }
    
    /**
     * Assign judge to organizer
     */
    public function assignToOrganizer() {
        $this->requireRoles(['Event Organizer', 'Super Admin', 'Event Technical Admin']);
        
        $organizerId = Session::get('user_id');
        $judgeId = $_POST['judge_id'] ?? null;
        $eventId = $_POST['event_id'] ?? null;
        
        if (!$judgeId || !$eventId) {
            $this->json(['success' => false, 'message' => 'Judge and Event are required'], 400);
            return;
        }
        
        // Check event access
        if (!$this->hasEventAccess($eventId)) {
            $this->json(['success' => false, 'message' => 'No access to this event'], 403);
            return;
        }
        
        // Check if already assigned
        $existing = $this->db->fetchOne(
            "SELECT * FROM judge_organizer_assignments 
             WHERE organizer_id = ? AND judge_id = ? AND event_id = ?",
            [$organizerId, $judgeId, $eventId]
        );
        
        if ($existing) {
            if (!$existing['is_active']) {
                // Reactivate
                $this->db->query(
                    "UPDATE judge_organizer_assignments SET is_active = 1 WHERE id = ?",
                    [$existing['id']]
                );
                $this->json(['success' => true, 'message' => 'Judge reactivated for this event']);
            } else {
                $this->json(['success' => false, 'message' => 'Judge already assigned to this event'], 400);
            }
            return;
        }
        
        // Create assignment
        $this->db->query(
            "INSERT INTO judge_organizer_assignments (organizer_id, judge_id, event_id)
             VALUES (?, ?, ?)",
            [$organizerId, $judgeId, $eventId]
        );
        
        $this->logAudit('ASSIGN_JUDGE_ORGANIZER', 'judge_organizer_assignments', $this->db->lastInsertId(), null, $_POST);
        
        $this->json(['success' => true, 'message' => 'Judge assigned to event successfully']);
    }
    
    /**
     * Remove judge from organizer
     */
    public function removeFromOrganizer() {
        $this->requireRoles(['Event Organizer', 'Super Admin', 'Event Technical Admin']);
        
        $organizerId = Session::get('user_id');
        $assignmentId = $_POST['assignment_id'] ?? null;
        
        if (!$assignmentId) {
            $this->json(['success' => false, 'message' => 'Assignment ID is required'], 400);
            return;
        }
        
        // Verify ownership
        $assignment = $this->db->fetchOne(
            "SELECT * FROM judge_organizer_assignments 
             WHERE id = ? AND organizer_id = ?",
            [$assignmentId, $organizerId]
        );
        
        if (!$assignment) {
            $this->json(['success' => false, 'message' => 'Assignment not found'], 404);
            return;
        }
        
        // Check event access
        if (!$this->hasEventAccess($assignment['event_id'])) {
            $this->json(['success' => false, 'message' => 'No access to this event'], 403);
            return;
        }
        
        $this->db->query(
            "UPDATE judge_organizer_assignments SET is_active = 0 WHERE id = ?",
            [$assignmentId]
        );
        
        $this->logAudit('REMOVE_JUDGE_ORGANIZER', 'judge_organizer_assignments', $assignmentId, null, null);
        
        $this->json(['success' => true, 'message' => 'Judge removed from event successfully']);
    }
    
    /**
     * Show all judge assignments including preparation-only ones (Tech Admin only)
     */
    public function allAssignments() {
        // Restrict to Tech Admins and Super Admins only
        $this->requireRoles(['Super Admin', 'Event Technical Admin']);
        
        // Get all assignments including preparation-only ones
        $allAssignments = $this->db->fetchAll(
            "SELECT 
                ja.is_preparation_only,
                r.id as round_id, r.name as round_name, r.status,
                el.name as level_name,
                e.id as event_id, e.name as event_name,
                j.id as judge_id, j.judge_number,
                u.full_name as judge_name,
                COALESCE((SELECT COUNT(*) FROM scores s2 WHERE s2.round_id = r.id AND s2.judge_id = j.id AND s2.is_submitted = 1), 0) as submitted_count,
                COALESCE((SELECT COUNT(*) FROM contestants c2 WHERE c2.event_id = e.id AND c2.status = 'Active'), 0) as total_contestants
             FROM judge_assignments ja
             JOIN judges j ON ja.judge_id = j.id
             JOIN users u ON j.user_id = u.id
             JOIN rounds r ON ja.round_id = r.id
             JOIN event_levels el ON r.level_id = el.id
             JOIN events e ON el.event_id = e.id
             WHERE ja.is_active = 1
             ORDER BY e.name, el.`order`, r.`order`, u.full_name"
        );
        
        $this->view('judge_management/all_assignments', [
            'allAssignments' => $allAssignments
        ]);
    }
    
    /**
     * Download CSV template for judge import
     */
    public function downloadTemplate() {
        $templateFile = __DIR__ . '/../templates/judges_import_template.csv';
        
        if (!file_exists($templateFile)) {
            die("Template file not found");
        }
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="judges_import_template.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        readfile($templateFile);
        exit;
    }
}


