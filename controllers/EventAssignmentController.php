<?php
/**
 * Event Assignment Controller
 * Manages user-event assignments
 */

class EventAssignmentController extends Controller {
    
    public function index($eventId) {
        $this->requireRoles(['Super Admin', 'Event Organizer']);
        $this->requireEventAccess($eventId);
        
        $event = $this->db->fetchOne("SELECT * FROM events WHERE id = ?", [$eventId]);
        if (!$event) {
            die("Event not found");
        }
        
        // Get all users with their assignment status
        $users = $this->db->fetchAll(
            "SELECT u.*, r.name as role_name,
             (SELECT id FROM user_event_assignments WHERE user_id = u.id AND event_id = ? AND is_active = 1) as is_assigned
             FROM users u
             JOIN roles r ON u.role_id = r.id
             WHERE u.is_active = 1 AND r.name != 'Judge'
             ORDER BY r.name, u.full_name",
            [$eventId]
        );
        
        // Get currently assigned users
        $assignedUsers = $this->db->fetchAll(
            "SELECT u.*, r.name as role_name, uea.assigned_at, uea.assigned_by
             FROM user_event_assignments uea
             JOIN users u ON uea.user_id = u.id
             JOIN roles r ON u.role_id = r.id
             WHERE uea.event_id = ? AND uea.is_active = 1
             ORDER BY r.name, u.full_name",
            [$eventId]
        );
        
        $this->view('event_assignment/index', [
            'event' => $event,
            'users' => $users,
            'assignedUsers' => $assignedUsers
        ]);
    }
    
    public function assign($eventId) {
        $this->requireRoles(['Super Admin', 'Event Organizer']);
        $this->requireEventAccess($eventId);
        
        $userId = $_POST['user_id'] ?? null;
        if (!$userId) {
            Session::set('error_message', 'User ID required');
            $this->redirect('/tabulation/events/' . $eventId . '/assignments');
            return;
        }
        
        $assignedBy = Session::get('user_id');
        
        $this->db->query(
            "INSERT INTO user_event_assignments (user_id, event_id, assigned_by, is_active)
             VALUES (?, ?, ?, 1)
             ON DUPLICATE KEY UPDATE is_active = 1, assigned_by = ?, assigned_at = NOW()",
            [$userId, $eventId, $assignedBy, $assignedBy]
        );
        
        $this->logAudit('ASSIGN_USER_TO_EVENT', 'user_event_assignments', null, null, [
            'user_id' => $userId,
            'event_id' => $eventId
        ]);
        
        Session::set('success_message', 'User assigned to event successfully');
        $this->redirect('/tabulation/events/' . $eventId . '/assignments');
    }
    
    public function unassign($eventId) {
        $this->requireRoles(['Super Admin', 'Event Organizer']);
        $this->requireEventAccess($eventId);
        
        $userId = $_POST['user_id'] ?? null;
        if (!$userId) {
            Session::set('error_message', 'User ID required');
            $this->redirect('/tabulation/events/' . $eventId . '/assignments');
            return;
        }
        
        $this->db->query(
            "UPDATE user_event_assignments SET is_active = 0 WHERE user_id = ? AND event_id = ?",
            [$userId, $eventId]
        );
        
        $this->logAudit('UNASSIGN_USER_FROM_EVENT', 'user_event_assignments', null, null, [
            'user_id' => $userId,
            'event_id' => $eventId
        ]);
        
        Session::set('success_message', 'User unassigned from event successfully');
        $this->redirect('/tabulation/events/' . $eventId . '/assignments');
    }
}

