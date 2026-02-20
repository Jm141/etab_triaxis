<?php
/**
 * Event Controller
 */

class EventController extends Controller {
    
    public function index() {
        $this->restrictJudges();
        
        // Update event statuses based on dates
        $this->updateEventStatuses();
        
        // Get accessible events (filtered by user assignment)
        $events = $this->getAccessibleEvents();
        
        // Enrich with counts
        foreach ($events as &$event) {
            $event['contestant_count'] = $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM contestants WHERE event_id = ?",
                [$event['id']]
            )['count'];
            
            $event['judge_count'] = $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM judges WHERE event_id = ? AND is_active = 1",
                [$event['id']]
            )['count'];
        }
        
        $this->view('event/index', ['events' => $events]);
    }
    
    /**
     * Update event statuses based on dates
     * Called automatically on page loads
     * Events close 1 hour after end_time (if end_time is 9pm, closes at 10pm)
     */
    public function updateEventStatuses() {
        // Update events that should be 'Ongoing'
        // Event starts if: event_date + start_time <= NOW()
        $this->db->query(
            "UPDATE events 
             SET status = 'Ongoing' 
             WHERE status IN ('Draft', 'Pending')
             AND (
                 (start_time IS NULL AND event_date <= CURDATE())
                 OR (start_time IS NOT NULL AND CONCAT(event_date, ' ', start_time) <= NOW())
                 OR (event_date < CURDATE())
             )
             AND (end_time IS NULL OR DATE_ADD(CONCAT(event_date, ' ', end_time), INTERVAL 1 HOUR) > NOW())"
        );
        
        // Get events that are about to be marked as 'Finished' (before updating)
        $eventsToFinish = $this->db->fetchAll(
            "SELECT id FROM events 
             WHERE status = 'Ongoing'
             AND (
                 (end_time IS NOT NULL AND DATE_ADD(CONCAT(event_date, ' ', end_time), INTERVAL 1 HOUR) < NOW())
                 OR (end_time IS NULL AND event_date < CURDATE())
             )"
        );
        
        // Update events that should be 'Finished'
        // Event ends 1 hour after end_time: if end_time is 9pm, closes at 10pm
        // Event ends if: event_date + end_time + 1 hour < NOW()
        $this->db->query(
            "UPDATE events 
             SET status = 'Finished' 
             WHERE status = 'Ongoing'
             AND end_time IS NOT NULL
             AND DATE_ADD(CONCAT(event_date, ' ', end_time), INTERVAL 1 HOUR) < NOW()"
        );
        
        // Also handle events without end_time - mark as finished if event_date is past
        $this->db->query(
            "UPDATE events 
             SET status = 'Finished' 
             WHERE status = 'Ongoing'
             AND end_time IS NULL
             AND event_date < CURDATE()"
        );
        
        // Create backups for events that just finished
        if (!empty($eventsToFinish)) {
            require_once __DIR__ . '/../core/DatabaseBackup.php';
            $backup = new DatabaseBackup();
            
            foreach ($eventsToFinish as $event) {
                try {
                    $backup->createEventBackup($event['id']);
                } catch (Exception $e) {
                    error_log("Failed to create backup for event ID {$event['id']}: " . $e->getMessage());
                }
            }
        }
    }
    
    public function create() {
        $this->restrictJudges();
        $this->view('event/create');
    }
    
    public function store() {
        $this->restrictJudges();
        
        $errors = $this->validateInput($_POST, [
            'name' => 'required|min:3',
            'event_type' => 'required',
            'gender_mode' => 'required|in:single,mr_miss',
            'event_date' => 'required'
        ]);
        
        if (!empty($errors)) {
            $this->view('event/create', ['errors' => $errors, 'data' => $_POST]);
            return;
        }
        
        $data = [
            'name' => $_POST['name'],
            'description' => $_POST['description'] ?? '',
            'event_type' => $_POST['event_type'],
            'gender_mode' => $_POST['gender_mode'],
            'venue' => $_POST['venue'] ?? '',
            'event_date' => $_POST['event_date'],
            'start_time' => $_POST['start_time'] ?? null,
            'end_time' => $_POST['end_time'] ?? null,
            'timezone' => $_POST['timezone'] ?? 'UTC',
            'status' => $_POST['status'] ?? 'Draft',
            'created_by' => Session::get('user_id')
        ];
        
        $this->db->getConnection()->beginTransaction();
        
        try {
            $this->db->query(
                "INSERT INTO events (name, description, event_type, gender_mode, venue, event_date, start_time, end_time, timezone, status, created_by)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                array_values($data)
            );
            
            $eventId = $this->db->lastInsertId();
            
            // Auto-assign creator to the event
            $this->db->query(
                "INSERT INTO user_event_assignments (user_id, event_id, assigned_by, is_active)
                 VALUES (?, ?, ?, 1)
                 ON DUPLICATE KEY UPDATE is_active = 1",
                [Session::get('user_id'), $eventId, Session::get('user_id')]
            );
            
            $this->logAudit('CREATE', 'events', $eventId, null, $data);
            
            $this->db->getConnection()->commit();
            
            Session::set('success_message', 'Event created successfully');
            $this->redirect('/tabulation/events');
            
        } catch (Exception $e) {
            $this->db->getConnection()->rollBack();
            Session::set('error_message', 'Error creating event: ' . $e->getMessage());
            $this->view('event/create', ['errors' => [], 'data' => $_POST]);
        }
    }
    
    public function show($id) {
        $this->restrictJudges();
        $this->requireEventAccess($id);
        
        $event = $this->db->fetchOne(
            "SELECT e.*, u.full_name as created_by_name
             FROM events e
             LEFT JOIN users u ON e.created_by = u.id
             WHERE e.id = ?",
            [$id]
        );
        
        if (!$event) {
            die("Event not found");
        }
        
        // Security: Only Super Admin and Event Organizer can see the organizer_key
        $currentRole = Session::get('role_name');
        if (!in_array($currentRole, ['Super Admin', 'Event Organizer'])) {
            // Remove organizer_key from event data for non-organizers
            unset($event['organizer_key']);
            unset($event['organizer_key_set_by']);
            unset($event['organizer_key_set_at']);
        }
        
        // Get levels
        $levels = $this->db->fetchAll(
            "SELECT * FROM event_levels WHERE event_id = ? ORDER BY `order`",
            [$id]
        );
        
        // Get contestants
        $contestants = $this->db->fetchAll(
            "SELECT * FROM contestants WHERE event_id = ? ORDER BY contestant_number",
            [$id]
        );
        
        // Get judges
        $judges = $this->db->fetchAll(
            "SELECT j.*, u.full_name, u.username
             FROM judges j
             JOIN users u ON j.user_id = u.id
             WHERE j.event_id = ? AND j.is_active = 1",
            [$id]
        );
        
        // Get all rounds for this event (for Summary button)
        $allRounds = [];
        foreach ($levels as $level) {
            $rounds = $this->db->fetchAll(
                "SELECT r.*, el.name as level_name, el.id as level_id
                 FROM rounds r
                 JOIN event_levels el ON r.level_id = el.id
                 WHERE r.level_id = ? 
                 ORDER BY r.order",
                [$level['id']]
            );
            foreach ($rounds as $round) {
                $allRounds[] = $round;
            }
        }
        
        $this->view('event/show', [
            'event' => $event,
            'levels' => $levels,
            'contestants' => $contestants,
            'judges' => $judges,
            'allRounds' => $allRounds
        ]);
    }
    
    /**
     * Generate organizer key for event (Organizer only)
     */
    public function generateOrganizerKey($id) {
        $this->restrictJudges();
        $this->requireEventAccess($id);
        
        $currentRole = Session::get('role_name');
        
        // Only Super Admin and Event Organizer can generate keys
        if (!in_array($currentRole, ['Super Admin', 'Event Organizer'])) {
            Session::set('error_message', 'Access denied. Only organizers can generate keys.');
            $this->redirect('/tabulation/events/' . $id);
            return;
        }
        
        $event = $this->db->fetchOne("SELECT * FROM events WHERE id = ?", [$id]);
        if (!$event) {
            Session::set('error_message', 'Event not found');
            $this->redirect('/tabulation/events');
            return;
        }
        
        // Generate a secure random key
        $organizerKey = bin2hex(random_bytes(16)); // 32 character hex string
        
        // Update event with organizer key
        $this->db->query(
            "UPDATE events SET 
             organizer_key = ?,
             organizer_key_set_by = ?,
             organizer_key_set_at = NOW()
             WHERE id = ?",
            [$organizerKey, Session::get('user_id'), $id]
        );
        
        $this->logAudit('GENERATE_ORGANIZER_KEY', 'events', $id, null, ['key_prefix' => substr($organizerKey, 0, 8) . '...']);
        
        Session::set('success_message', 'Organizer key generated successfully. Share this key with Technical Admins when needed.');
        $this->redirect('/tabulation/events/' . $id);
    }
    
    /**
     * Regenerate organizer key for event (Organizer only)
     */
    public function regenerateOrganizerKey($id) {
        $this->restrictJudges();
        $this->requireEventAccess($id);
        
        $currentRole = Session::get('role_name');
        
        // Only Super Admin and Event Organizer can regenerate keys
        if (!in_array($currentRole, ['Super Admin', 'Event Organizer'])) {
            Session::set('error_message', 'Access denied. Only organizers can regenerate keys.');
            $this->redirect('/tabulation/events/' . $id);
            return;
        }
        
        $event = $this->db->fetchOne("SELECT * FROM events WHERE id = ?", [$id]);
        if (!$event) {
            Session::set('error_message', 'Event not found');
            $this->redirect('/tabulation/events');
            return;
        }
        
        // Generate a new secure random key
        $organizerKey = bin2hex(random_bytes(16)); // 32 character hex string
        
        // Update event with new organizer key
        $this->db->query(
            "UPDATE events SET 
             organizer_key = ?,
             organizer_key_set_by = ?,
             organizer_key_set_at = NOW()
             WHERE id = ?",
            [$organizerKey, Session::get('user_id'), $id]
        );
        
        $this->logAudit('REGENERATE_ORGANIZER_KEY', 'events', $id, null, ['key_prefix' => substr($organizerKey, 0, 8) . '...']);
        
        Session::set('success_message', 'Organizer key regenerated successfully. The old key will no longer work.');
        $this->redirect('/tabulation/events/' . $id);
    }
    
    public function edit($id) {
        $this->restrictJudges();
        $this->requireEventAccess($id);
        
        $event = $this->db->fetchOne("SELECT * FROM events WHERE id = ?", [$id]);
        
        if (!$event) {
            die("Event not found");
        }
        
        $this->view('event/edit', ['event' => $event]);
    }
    
    public function update($id) {
        $this->restrictJudges();
        $this->requireEventAccess($id);
        
        // Prevent modifications to finished events
        $this->preventFinishedEventModification($id);
        
        $event = $this->db->fetchOne("SELECT * FROM events WHERE id = ?", [$id]);
        if (!$event) {
            die("Event not found");
        }
        
        $oldValues = $event;
        
        $errors = $this->validateInput($_POST, [
            'name' => 'required|min:3',
            'event_type' => 'required',
            'gender_mode' => 'required|in:single,mr_miss',
            'event_date' => 'required'
        ]);
        
        if (!empty($errors)) {
            $this->view('event/edit', ['errors' => $errors, 'event' => array_merge($event, $_POST)]);
            return;
        }
        
        $this->db->query(
            "UPDATE events SET name = ?, description = ?, event_type = ?, gender_mode = ?, venue = ?, 
             event_date = ?, start_time = ?, end_time = ?, timezone = ?, status = ?
             WHERE id = ?",
            [
                $_POST['name'],
                $_POST['description'] ?? '',
                $_POST['event_type'],
                $_POST['gender_mode'],
                $_POST['venue'] ?? '',
                $_POST['event_date'],
                $_POST['start_time'] ?? null,
                $_POST['end_time'] ?? null,
                $_POST['timezone'] ?? 'UTC',
                $_POST['status'] ?? 'Draft',
                $id
            ]
        );
        
        $newValues = array_merge($event, $_POST);
        $this->logAudit('UPDATE', 'events', $id, $oldValues, $newValues);
        
        Session::set('success_message', 'Event updated successfully');
        $this->redirect('/tabulation/events/' . $id);
    }
    
    public function delete($id) {
        $this->restrictJudges();
        $this->requireEventAccess($id);
        
        $event = $this->db->fetchOne("SELECT * FROM events WHERE id = ?", [$id]);
        if ($event) {
            $this->logAudit('DELETE', 'events', $id, $event, null);
            $this->db->query("DELETE FROM events WHERE id = ?", [$id]);
        }
        
        Session::set('success_message', 'Event deleted successfully');
        $this->redirect('/tabulation/events');
    }
    
    public function select($id) {
        $this->restrictJudges();
        Session::set('current_event_id', $id);
        $this->redirect('/tabulation/events/' . $id);
    }
}


