<?php
/**
 * Round Controller
 */

class RoundController extends Controller {
    
    public function index($levelId) {
        $this->restrictJudges();
        
        $level = $this->db->fetchOne(
            "SELECT el.*, e.id as event_id, e.name as event_name
             FROM event_levels el
             JOIN events e ON el.event_id = e.id
             WHERE el.id = ?",
            [$levelId]
        );
        
        if (!$level) {
            die("Level not found");
        }
        
        $this->requireEventAccess($level['event_id']);
        
        // Show all rounds for this level (regardless of event status)
        // Admins need to see rounds even if event is Draft/Pending
        $rounds = $this->db->fetchAll(
            "SELECT r.* 
             FROM rounds r
             WHERE r.level_id = ?
             ORDER BY r.`order`",
            [$levelId]
        );
        
        $this->view('round/index', ['level' => $level, 'rounds' => $rounds]);
    }
    
    public function edit($levelId, $id) {
        $this->restrictJudges();
        
        $level = $this->db->fetchOne(
            "SELECT el.*, e.id as event_id, e.name as event_name
             FROM event_levels el
             JOIN events e ON el.event_id = e.id
             WHERE el.id = ?",
            [$levelId]
        );
        
        if (!$level) {
            die("Level not found");
        }
        
        $this->requireEventAccess($level['event_id']);
        
        $round = $this->db->fetchOne(
            "SELECT * FROM rounds WHERE id = ? AND level_id = ?",
            [$id, $levelId]
        );
        
        if (!$round) {
            Session::set('error_message', 'Round not found');
            $this->redirect('/tabulation/levels/' . $levelId . '/rounds');
            return;
        }
        
        $this->view('round/edit', ['level' => $level, 'round' => $round]);
    }
    
    public function store($levelId) {
        $this->restrictJudges();
        
        // Get event_id from level to check if event is finished
        $level = $this->db->fetchOne(
            "SELECT el.*, e.id as event_id FROM event_levels el JOIN events e ON el.event_id = e.id WHERE el.id = ?",
            [$levelId]
        );
        
        if (!$level) {
            Session::set('error_message', 'Level not found');
            $this->redirect('/tabulation/levels/' . $levelId . '/rounds');
            return;
        }
        
        $this->requireEventAccess($level['event_id']);
        
        // Prevent modifications to finished events
        $this->preventFinishedEventModification($level['event_id']);
        
        $errors = $this->validateInput($_POST, [
            'name' => 'required|min:2'
        ]);
        
        if (!empty($errors)) {
            Session::set('error_message', implode(', ', $errors));
            $this->redirect('/tabulation/levels/' . $levelId . '/rounds');
            return;
        }
        
        $order = $this->db->fetchOne(
            "SELECT MAX(`order`) as max_order FROM rounds WHERE level_id = ?",
            [$levelId]
        )['max_order'] ?? 0;
        
        $this->db->query(
            "INSERT INTO rounds (level_id, name, description, `order`)
             VALUES (?, ?, ?, ?)",
            [
                $levelId,
                $_POST['name'],
                $_POST['description'] ?? '',
                $order + 1
            ]
        );
        
        $this->logAudit('CREATE', 'rounds', $this->db->lastInsertId(), null, $_POST);
        
        Session::set('success_message', 'Round created successfully');
        $this->redirect('/tabulation/levels/' . $levelId . '/rounds');
    }
    
    public function update($levelId, $id) {
        $this->restrictJudges();
        
        // Get event_id from level to check if event is finished
        $level = $this->db->fetchOne(
            "SELECT el.*, e.id as event_id FROM event_levels el JOIN events e ON el.event_id = e.id WHERE el.id = ?",
            [$levelId]
        );
        
        if (!$level) {
            Session::set('error_message', 'Level not found');
            $this->redirect('/tabulation/levels/' . $levelId . '/rounds');
            return;
        }
        
        $this->requireEventAccess($level['event_id']);
        
        // Prevent modifications to finished events
        $this->preventFinishedEventModification($level['event_id']);
        
        $this->db->query(
            "UPDATE rounds SET name = ?, description = ?, status = ? WHERE id = ?",
            [
                $_POST['name'],
                $_POST['description'] ?? '',
                $_POST['status'] ?? 'Pending',
                $id
            ]
        );
        
        $this->logAudit('UPDATE', 'rounds', $id, null, $_POST);
        
        Session::set('success_message', 'Round updated successfully');
        $this->redirect('/tabulation/levels/' . $levelId . '/rounds');
    }
    
    public function delete($levelId, $id) {
        $this->restrictJudges();
        
        $round = $this->db->fetchOne("SELECT * FROM rounds WHERE id = ? AND level_id = ?", [$id, $levelId]);
        if (!$round) {
            $level = $this->db->fetchOne(
                "SELECT el.*, e.id as event_id FROM event_levels el JOIN events e ON el.event_id = e.id WHERE el.id = ?",
                [$levelId]
            );
            Session::set('error_message', 'Round not found');
            $this->redirect('/tabulation/levels/' . $levelId . '/rounds');
            return;
        }
        
        // Get level and event for access check
        $level = $this->db->fetchOne(
            "SELECT el.*, e.id as event_id FROM event_levels el JOIN events e ON el.event_id = e.id WHERE el.id = ?",
            [$levelId]
        );
        $this->requireEventAccess($level['event_id']);
        
        // Prevent modifications to finished events
        $this->preventFinishedEventModification($level['event_id']);
        
        // Check if round has scores
        $scoresCount = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM scores WHERE round_id = ?",
            [$id]
        );
        
        if ($scoresCount['count'] > 0) {
            Session::set('error_message', 'Cannot delete round that has scores. This round has ' . $scoresCount['count'] . ' score entries. Delete scores first or contact administrator.');
            $this->redirect('/tabulation/levels/' . $levelId . '/rounds');
            return;
        }
        
        $this->db->query("DELETE FROM rounds WHERE id = ?", [$id]);
        $this->logAudit('DELETE', 'rounds', $id, null, null);
        
        Session::set('success_message', 'Round deleted successfully');
        $this->redirect('/tabulation/levels/' . $levelId . '/rounds');
    }
}


