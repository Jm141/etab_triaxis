<?php
/**
 * Criteria Controller
 */

class CriteriaController extends Controller {
    
    public function index($eventId) {
        $this->restrictJudges();
        $this->requireEventAccess($eventId);
        
        $event = $this->db->fetchOne("SELECT * FROM events WHERE id = ?", [$eventId]);
        if (!$event) {
            die("Event not found");
        }
        
        $criteria = $this->db->fetchAll(
            "SELECT * FROM criteria WHERE event_id = ? ORDER BY id",
            [$eventId]
        );
        
        $this->view('criteria/index', ['event' => $event, 'criteria' => $criteria]);
    }
    
    public function store($eventId) {
        $this->restrictJudges();
        $this->requireEventAccess($eventId);
        
        $errors = $this->validateInput($_POST, [
            'name' => 'required|min:2',
            'max_score' => 'required|numeric'
        ]);
        
        if (!empty($errors)) {
            Session::set('error_message', implode(', ', $errors));
            $this->redirect('/tabulation/events/' . $eventId . '/criteria');
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
        
        Session::set('success_message', 'Criteria created successfully');
        $this->redirect('/tabulation/events/' . $eventId . '/criteria');
    }
    
    public function update($eventId, $id) {
        $this->restrictJudges();
        $this->requireEventAccess($eventId);
        
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
        
        Session::set('success_message', 'Criteria updated successfully');
        $this->redirect('/tabulation/events/' . $eventId . '/criteria');
    }
    
    public function delete($eventId, $id) {
        $this->restrictJudges();
        $this->requireEventAccess($eventId);
        
        $this->db->query("DELETE FROM criteria WHERE id = ?", [$id]);
        $this->logAudit('DELETE', 'criteria', $id, null, null);
        
        Session::set('success_message', 'Criteria deleted successfully');
        $this->redirect('/tabulation/events/' . $eventId . '/criteria');
    }
}


