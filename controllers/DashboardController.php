<?php
/**
 * Dashboard Controller
 */

class DashboardController extends Controller {
    
    public function index() {
        $this->requireAuth();
        
        $userId = Session::get('user_id');
        $roleName = Session::get('role_name');
        
        $data = [
            'user' => [
                'name' => Session::get('full_name'),
                'role' => $roleName
            ]
        ];
        
        // Get statistics based on role
        if ($roleName === 'Super Admin') {
            // Super Admin sees everything
            $data['total_events'] = $this->db->fetchOne("SELECT COUNT(*) as count FROM events")['count'];
            $data['active_events'] = $this->db->fetchOne("SELECT COUNT(*) as count FROM events WHERE status = 'Ongoing'")['count'];
            $data['total_judges'] = $this->db->fetchOne("SELECT COUNT(*) as count FROM judges WHERE is_active = 1")['count'];
            $data['total_contestants'] = $this->db->fetchOne("SELECT COUNT(*) as count FROM contestants WHERE status = 'Active'")['count'];
            
            // Recent events
            $data['recent_events'] = $this->db->fetchAll(
                "SELECT * FROM events ORDER BY created_at DESC LIMIT 5"
            );
        } elseif (in_array($roleName, ['Event Admin', 'Event Organizer', 'Event Technical Admin', 'Tabulator', 'Auditor', 'Host'])) {
            // Get accessible events for this user
            $accessibleEvents = $this->getAccessibleEvents();
            $eventIds = array_column($accessibleEvents, 'id');
            
            if (!empty($eventIds)) {
                $placeholders = str_repeat('?,', count($eventIds) - 1) . '?';
                
                $data['total_events'] = count($accessibleEvents);
                $data['active_events'] = $this->db->fetchOne(
                    "SELECT COUNT(*) as count FROM events WHERE id IN ($placeholders) AND status = 'Ongoing'",
                    $eventIds
                )['count'];
                $data['total_judges'] = $this->db->fetchOne(
                    "SELECT COUNT(*) as count FROM judges WHERE event_id IN ($placeholders) AND is_active = 1",
                    $eventIds
                )['count'];
                $data['total_contestants'] = $this->db->fetchOne(
                    "SELECT COUNT(*) as count FROM contestants WHERE event_id IN ($placeholders) AND status = 'Active'",
                    $eventIds
                )['count'];
                
                // Recent events (from accessible events)
                $data['recent_events'] = array_slice($accessibleEvents, 0, 5);
            } else {
                $data['total_events'] = 0;
                $data['active_events'] = 0;
                $data['total_judges'] = 0;
                $data['total_contestants'] = 0;
                $data['recent_events'] = [];
            }
        } elseif ($roleName === 'Judge') {
            // Update event statuses first
            $this->updateEventStatuses();
            
            // Get judge's assigned events and rounds with progress counts
            // Only show events with 'Ongoing' status
            // Exclude preparation-only assignments (only visible to tech admins)
            $data['assigned_rounds'] = $this->db->fetchAll(
                "SELECT r.*, el.name as level_name, el.`order` as level_order, e.name as event_name, e.id as event_id,
                 COALESCE((SELECT COUNT(*) FROM scores s2 
                  WHERE s2.round_id = r.id AND s2.judge_id = j.id AND s2.is_submitted = 1), 0) as submitted_count,
                 COALESCE((SELECT COUNT(DISTINCT s3.contestant_id) FROM scores s3 WHERE s3.round_id = r.id), 0) as total_contestants
                 FROM judge_assignments ja
                 JOIN judges j ON ja.judge_id = j.id
                 JOIN rounds r ON ja.round_id = r.id
                 JOIN event_levels el ON r.level_id = el.id
                 JOIN events e ON el.event_id = e.id
                 WHERE j.user_id = ? AND ja.is_active = 1 AND ja.is_preparation_only = 0 AND r.status != 'Completed' AND e.status = 'Ongoing'
                 ORDER BY COALESCE(el.`order`, 0) ASC, COALESCE(r.`order`, 0) ASC, r.name ASC",
                [$userId]
            );
        }
        
        $this->view('dashboard/index', $data);
    }
    
    /**
     * Update event statuses based on dates
     * Events close 1 hour after end_time
     */
    protected function updateEventStatuses() {
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
        
        // Update events that should be 'Finished' (1 hour after end_time)
        $this->db->query(
            "UPDATE events 
             SET status = 'Finished' 
             WHERE status = 'Ongoing'
             AND end_time IS NOT NULL
             AND DATE_ADD(CONCAT(event_date, ' ', end_time), INTERVAL 1 HOUR) < NOW()"
        );
        
        // Handle events without end_time
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
}



