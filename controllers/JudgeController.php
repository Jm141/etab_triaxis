<?php
/**
 * Judge Controller
 */

class JudgeController extends Controller {
    
    public function index($eventId) {
        $this->restrictJudges();
        $this->requireEventAccess($eventId);
        
        $event = $this->db->fetchOne("SELECT * FROM events WHERE id = ?", [$eventId]);
        if (!$event) {
            die("Event not found");
        }
        
        $judges = $this->db->fetchAll(
            "SELECT j.*, u.full_name, u.username, u.email,
             (SELECT COUNT(*) FROM judge_assignments WHERE judge_id = j.id AND is_active = 1) as assigned_rounds
             FROM judges j
             JOIN users u ON j.user_id = u.id
             WHERE j.event_id = ? AND j.is_active = 1
             ORDER BY j.judge_number",
            [$eventId]
        );
        
        // Get all users with Judge role
        $availableUsers = $this->db->fetchAll(
            "SELECT u.* FROM users u
             JOIN roles r ON u.role_id = r.id
             WHERE r.name = 'Judge' AND u.is_active = 1
             AND u.id NOT IN (SELECT user_id FROM judges WHERE event_id = ? AND is_active = 1)
             ORDER BY u.full_name",
            [$eventId]
        );
        
        $this->view('judge/index', [
            'event' => $event,
            'judges' => $judges,
            'availableUsers' => $availableUsers
        ]);
    }
    
    public function store($eventId) {
        $this->restrictJudges();
        $this->requireEventAccess($eventId);
        
        // Prevent modifications to finished events
        $this->preventFinishedEventModification($eventId);
        
        $userId = $_POST['user_id'] ?? null;
        if (!$userId) {
            Session::set('error_message', 'User is required');
            $this->redirect('/tabulation/events/' . $eventId . '/judges');
            return;
        }
        
        // Check if already assigned
        $existing = $this->db->fetchOne(
            "SELECT * FROM judges WHERE user_id = ? AND event_id = ?",
            [$userId, $eventId]
        );
        
        if ($existing) {
            // Reactivate
            $this->db->query(
                "UPDATE judges SET is_active = 1, judge_number = ?, specialty = ? WHERE id = ?",
                [
                    $_POST['judge_number'] ?? null,
                    $_POST['specialty'] ?? null,
                    $existing['id']
                ]
            );
        } else {
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
        }
        
        $this->logAudit('CREATE', 'judges', $this->db->lastInsertId(), null, $_POST);
        
        Session::set('success_message', 'Judge assigned successfully');
        $this->redirect('/tabulation/events/' . $eventId . '/judges');
    }
    
    public function delete($eventId, $id) {
        $this->restrictJudges();
        $this->requireEventAccess($eventId);
        
        // Prevent modifications to finished events
        $this->preventFinishedEventModification($eventId);
        
        $this->db->query("UPDATE judges SET is_active = 0 WHERE id = ?", [$id]);
        $this->logAudit('DELETE', 'judges', $id, null, null);
        
        Session::set('success_message', 'Judge removed successfully');
        $this->redirect('/tabulation/events/' . $eventId . '/judges');
    }
    
    public function assignRound($id) {
        $this->restrictJudges();
        
        $roundId = $_POST['round_id'] ?? null;
        if (!$roundId) {
            $this->json(['success' => false, 'message' => 'Round is required'], 400);
            return;
        }
        
        // Get event_id from round to check if event is finished
        $round = $this->db->fetchOne(
            "SELECT r.*, el.event_id FROM rounds r
             JOIN event_levels el ON r.level_id = el.id
             WHERE r.id = ?",
            [$roundId]
        );
        
        if (!$round) {
            $this->json(['success' => false, 'message' => 'Round not found'], 404);
            return;
        }
        
        // Prevent modifications to finished events
        $this->preventFinishedEventModification($round['event_id']);
        
        // Check if already assigned
        $existing = $this->db->fetchOne(
            "SELECT * FROM judge_assignments WHERE judge_id = ? AND round_id = ?",
            [$id, $roundId]
        );
        
        if ($existing) {
            $this->db->query(
                "UPDATE judge_assignments SET is_active = 1 WHERE id = ?",
                [$existing['id']]
            );
        } else {
            $this->db->query(
                "INSERT INTO judge_assignments (judge_id, round_id)
                 VALUES (?, ?)",
                [$id, $roundId]
            );
        }
        
        $this->logAudit('ASSIGN_ROUND', 'judge_assignments', $this->db->lastInsertId(), null, $_POST);
        
        $this->json(['success' => true, 'message' => 'Round assigned successfully']);
    }
}


