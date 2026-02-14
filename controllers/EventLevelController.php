<?php
/**
 * Event Level Controller
 */

class EventLevelController extends Controller {
    
    public function index($eventId) {
        $this->restrictJudges();
        $this->requireEventAccess($eventId);
        
        $event = $this->db->fetchOne("SELECT * FROM events WHERE id = ?", [$eventId]);
        if (!$event) {
            die("Event not found");
        }
        
        $levels = $this->db->fetchAll(
            "SELECT * FROM event_levels WHERE event_id = ? ORDER BY `order`",
            [$eventId]
        );
        
        $this->view('event/levels', ['event' => $event, 'levels' => $levels]);
    }

    public function edit($eventId, $id) {
        $this->restrictJudges();
        $this->requireEventAccess($eventId);
        
        $level = $this->db->fetchOne("SELECT * FROM event_levels WHERE id = ? AND event_id = ?", [$id, $eventId]);
        if (!$level) {
            die("Level not found");
        }

        $this->view('event/levels/edit', ['level' => $level]);
    }
  

    public function store($eventId) {
        $this->restrictJudges();
        $this->requireEventAccess($eventId);
        
        $errors = $this->validateInput($_POST, [
            'name' => 'required|min:2'
        ]);
        
        if (!empty($errors)) {
            Session::set('error_message', implode(', ', $errors));
            $this->redirect('/tabulation/events/' . $eventId . '/levels');
            return;
        }
        
        $order = $this->db->fetchOne(
            "SELECT MAX(`order`) as max_order FROM event_levels WHERE event_id = ?",
            [$eventId]
        )['max_order'] ?? 0;
        
        $this->db->query(
            "INSERT INTO event_levels (event_id, name, description, `order`, advance_count)
             VALUES (?, ?, ?, ?, ?)",
            [
                $eventId,
                $_POST['name'],
                $_POST['description'] ?? '',
                $order + 1,
                !empty($_POST['advance_count']) ? (int)$_POST['advance_count'] : null
            ]
        );
        
        $this->logAudit('CREATE', 'event_levels', $this->db->lastInsertId(), null, $_POST);
        
        Session::set('success_message', 'Level created successfully');
        $this->redirect('/tabulation/events/' . $eventId . '/levels');
    }
    
    public function update($eventId, $id) {
        $this->restrictJudges();
        $this->requireEventAccess($eventId);
        
        $this->db->query(
            "UPDATE event_levels SET name = ?, description = ?, status = ?, advance_count = ? WHERE id = ?",
            [
                $_POST['name'],
                $_POST['description'] ?? '',
                $_POST['status'] ?? 'Pending',
                !empty($_POST['advance_count']) ? (int)$_POST['advance_count'] : null,
                $id
            ]
        );
        
        $this->logAudit('UPDATE', 'event_levels', $id, null, $_POST);
        
        Session::set('success_message', 'Level updated successfully');
        $this->redirect('/tabulation/events/' . $eventId . '/levels');
    }
    
    public function delete($eventId, $id) {
        $this->restrictJudges();
        $this->requireEventAccess($eventId);
        
        $level = $this->db->fetchOne("SELECT * FROM event_levels WHERE id = ? AND event_id = ?", [$id, $eventId]);
        if (!$level) {
            Session::set('error_message', 'Level not found');
            $this->redirect('/tabulation/events/' . $eventId . '/levels');
            return;
        }
        
        // Check if level has rounds
        $roundsCount = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM rounds WHERE level_id = ?",
            [$id]
        );
        
        if ($roundsCount['count'] > 0) {
            Session::set('error_message', 'Cannot delete level that has rounds. Delete all rounds first.');
            $this->redirect('/tabulation/events/' . $eventId . '/levels');
            return;
        }
        
        $this->db->query("DELETE FROM event_levels WHERE id = ?", [$id]);
        $this->logAudit('DELETE', 'event_levels', $id, null, null);
        
        Session::set('success_message', 'Level deleted successfully');
        $this->redirect('/tabulation/events/' . $eventId . '/levels');
    }
}


