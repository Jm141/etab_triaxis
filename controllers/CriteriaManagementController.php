<?php
/**
 * Criteria Management Controller - User-friendly global criteria management
 */

class CriteriaManagementController extends Controller {
    
    public function index() {
        $this->requireRoles(['Super Admin', 'Event Admin', 'Event Organizer', 'Event Technical Admin']);
        
        // Get accessible events (filtered by user assignment)
        $accessibleEvents = $this->getAccessibleEvents();
        
        // Enrich with criteria counts
        $events = [];
        foreach ($accessibleEvents as $event) {
            $event['criteria_count'] = $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM criteria WHERE event_id = ?",
                [$event['id']]
            )['count'];
            $events[] = $event;
        }
        
        // Get selected event or first event
        $selectedEventId = $_GET['event_id'] ?? ($events[0]['id'] ?? null);
        
        $selectedEvent = null;
        $criteria = [];
        
        if ($selectedEventId) {
            $selectedEvent = $this->db->fetchOne("SELECT * FROM events WHERE id = ?", [$selectedEventId]);
            
            if ($selectedEvent) {
                $criteria = $this->db->fetchAll(
                    "SELECT * FROM criteria WHERE event_id = ? ORDER BY category, name",
                    [$selectedEventId]
                );
            }
        }
        
        $this->view('criteria_management/index', [
            'events' => $events,
            'selectedEvent' => $selectedEvent,
            'criteria' => $criteria
        ]);
    }
    
    public function create() {
        $this->requireRoles(['Super Admin', 'Event Admin', 'Event Organizer', 'Event Technical Admin']);
        
        $eventId = $_GET['event_id'] ?? null;
        if (!$eventId) {
            Session::set('error_message', 'Please select an event first');
            $this->redirect('/tabulation/criteria-management');
            return;
        }
        
        $this->requireEventAccess($eventId);
        
        $event = $this->db->fetchOne("SELECT * FROM events WHERE id = ?", [$eventId]);
        if (!$event) {
            Session::set('error_message', 'Event not found');
            $this->redirect('/tabulation/criteria-management');
            return;
        }
        
        $this->view('criteria_management/create', ['event' => $event]);
    }
    
    public function store() {
        $this->requireRoles(['Super Admin', 'Event Admin', 'Event Organizer', 'Event Technical Admin']);
        
        $eventId = $_POST['event_id'] ?? null;
        if (!$eventId) {
            Session::set('error_message', 'Event is required');
            $this->redirect('/tabulation/criteria-management');
            return;
        }
        
        $errors = $this->validateInput($_POST, [
            'name' => 'required|min:2',
            'max_score' => 'required|numeric'
        ]);
        
        if (!empty($errors)) {
            Session::set('error_message', implode(', ', $errors));
            $this->redirect('/tabulation/criteria-management?event_id=' . $eventId);
            return;
        }
        
        // Validate max_score is positive
        if ($_POST['max_score'] <= 0) {
            Session::set('error_message', 'Maximum score must be greater than 0');
            $this->redirect('/tabulation/criteria-management?event_id=' . $eventId);
            return;
        }
        
        $this->db->query(
            "INSERT INTO criteria (event_id, name, description, max_score, category)
             VALUES (?, ?, ?, ?, ?)",
            [
                $eventId,
                $_POST['name'],
                $_POST['description'] ?? '',
                $_POST['max_score'],
                $_POST['category'] ?? null
            ]
        );
        
        $this->logAudit('CREATE', 'criteria', $this->db->lastInsertId(), null, $_POST);
        
        Session::set('success_message', 'Criteria "' . htmlspecialchars($_POST['name']) . '" added successfully!');
        $this->redirect('/tabulation/criteria-management?event_id=' . $eventId);
    }
    
    public function edit($id) {
        $this->requireRoles(['Super Admin', 'Event Admin', 'Event Organizer', 'Event Technical Admin']);
        
        $criterion = $this->db->fetchOne(
            "SELECT c.*, e.name as event_name, e.id as event_id
             FROM criteria c
             JOIN events e ON c.event_id = e.id
             WHERE c.id = ?",
            [$id]
        );
        
        if (!$criterion) {
            Session::set('error_message', 'Criteria not found');
            $this->redirect('/tabulation/criteria-management');
            return;
        }
        
        // Check event access
        $this->requireEventAccess($criterion['event_id']);
        
        $this->view('criteria_management/edit', ['criterion' => $criterion]);
    }
    
    public function update($id) {
        $this->requireRoles(['Super Admin', 'Event Admin', 'Event Organizer', 'Event Technical Admin']);
        
        $criterion = $this->db->fetchOne("SELECT * FROM criteria WHERE id = ?", [$id]);
        if (!$criterion) {
            Session::set('error_message', 'Criteria not found');
            $this->redirect('/tabulation/criteria-management');
            return;
        }
        
        // Check event access
        $this->requireEventAccess($criterion['event_id']);
        
        $errors = $this->validateInput($_POST, [
            'name' => 'required|min:2',
            'max_score' => 'required|numeric'
        ]);
        
        if (!empty($errors)) {
            Session::set('error_message', implode(', ', $errors));
            $this->redirect('/tabulation/criteria-management/edit/' . $id);
            return;
        }
        
        if ($_POST['max_score'] <= 0) {
            Session::set('error_message', 'Maximum score must be greater than 0');
            $this->redirect('/tabulation/criteria-management/edit/' . $id);
            return;
        }
        
        $this->db->query(
            "UPDATE criteria SET name = ?, description = ?, max_score = ?, category = ?
             WHERE id = ?",
            [
                $_POST['name'],
                $_POST['description'] ?? '',
                $_POST['max_score'],
                $_POST['category'] ?? null,
                $id
            ]
        );
        
        $this->logAudit('UPDATE', 'criteria', $id, null, $_POST);
        
        Session::set('success_message', 'Criteria updated successfully!');
        $this->redirect('/tabulation/criteria-management?event_id=' . $criterion['event_id']);
    }
    
    public function delete($id) {
        $this->requireRoles(['Super Admin', 'Event Admin', 'Event Organizer', 'Event Technical Admin']);
        
        $criterion = $this->db->fetchOne("SELECT * FROM criteria WHERE id = ?", [$id]);
        if (!$criterion) {
            Session::set('error_message', 'Criteria not found');
            $this->redirect('/tabulation/criteria-management');
            return;
        }
        
        // Check if criteria is used in any rounds
        $inUse = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM criteria_weights WHERE criteria_id = ?",
            [$id]
        );
        
        if ($inUse['count'] > 0) {
            Session::set('error_message', 'Cannot delete criteria that is assigned to rounds. Remove it from rounds first.');
            $this->redirect('/tabulation/criteria-management?event_id=' . $criterion['event_id']);
            return;
        }
        
        $this->db->query("DELETE FROM criteria WHERE id = ?", [$id]);
        $this->logAudit('DELETE', 'criteria', $id, null, null);
        
        Session::set('success_message', 'Criteria deleted successfully');
        $this->redirect('/tabulation/criteria-management?event_id=' . $criterion['event_id']);
    }
}


