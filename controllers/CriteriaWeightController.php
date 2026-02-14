<?php
/**
 * Criteria Weight Controller
 */

class CriteriaWeightController extends Controller {
    
    public function index($roundId) {
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
        
        // Get criteria weights for this round
        $weights = $this->db->fetchAll(
            "SELECT cw.*, c.name as criteria_name, c.max_score, c.description
             FROM criteria_weights cw
             JOIN criteria c ON cw.criteria_id = c.id
             WHERE cw.round_id = ? AND c.event_id = ?
             ORDER BY c.id",
            [$roundId, $round['event_id']]
        );
        
        // Get all criteria for this event
        $allCriteria = $this->db->fetchAll(
            "SELECT * FROM criteria WHERE event_id = ? ORDER BY id",
            [$round['event_id']]
        );
        
        $this->view('criteria/weights', [
            'round' => $round,
            'weights' => $weights,
            'allCriteria' => $allCriteria
        ]);
    }
    
    public function store($roundId) {
        $this->restrictJudges();
        
        // Get round with event_id from event_levels
        $round = $this->db->fetchOne(
            "SELECT r.*, el.event_id 
             FROM rounds r 
             JOIN event_levels el ON r.level_id = el.id 
             WHERE r.id = ?",
            [$roundId]
        );
        if (!$round) {
            die("Round not found");
        }
        
        $criteriaId = $_POST['criteria_id'] ?? null;
        
        if (!$criteriaId) {
            Session::set('error_message', 'Criteria is required');
            $this->redirect('/tabulation/rounds/' . $roundId . '/weights');
            return;
        }
        
        // Check if already exists first
        $existing = $this->db->fetchOne(
            "SELECT * FROM criteria_weights WHERE round_id = ? AND criteria_id = ?",
            [$roundId, $criteriaId]
        );
        
        // If it exists and is being reactivated, just update is_active and recalculate
        if ($existing) {
            $this->db->query(
                "UPDATE criteria_weights SET is_active = 1 WHERE id = ?",
                [$existing['id']]
            );
            // Recalculate all weights
            $this->recalculateWeights($roundId);
            Session::set('success_message', 'Criteria reactivated. Weights recalculated automatically.');
            $this->redirect('/tabulation/rounds/' . $roundId . '/weights');
            return;
        }
        
        // Get the criteria to get its max_score
        $criterion = $this->db->fetchOne(
            "SELECT * FROM criteria WHERE id = ? AND event_id = ?",
            [$criteriaId, $round['event_id']]
        );
        
        if (!$criterion) {
            Session::set('error_message', 'Criteria not found');
            $this->redirect('/tabulation/rounds/' . $roundId . '/weights');
            return;
        }
        
        // Insert new criteria (weight will be calculated in recalculateWeights)
        $this->db->query(
            "INSERT INTO criteria_weights (round_id, criteria_id, weight)
             VALUES (?, ?, 0)",
            [$roundId, $criteriaId]
        );
        
        // Recalculate all weights for this round to ensure they sum to 100%
        $this->recalculateWeights($roundId);
        
        $this->logAudit('CREATE', 'criteria_weights', $this->db->lastInsertId(), null, $_POST);
        
        Session::set('success_message', 'Criteria assigned successfully. Weight auto-calculated based on max score.');
        $this->redirect('/tabulation/rounds/' . $roundId . '/weights');
    }
    
    /**
     * Recalculate all weights for a round based on max_score proportions
     */
    private function recalculateWeights($roundId) {
        $criteriaWeights = $this->db->fetchAll(
            "SELECT cw.id, c.max_score
             FROM criteria_weights cw
             JOIN criteria c ON cw.criteria_id = c.id
             WHERE cw.round_id = ? AND cw.is_active = 1",
            [$roundId]
        );
        
        if (empty($criteriaWeights)) {
            return;
        }
        
        $totalMaxScore = array_sum(array_column($criteriaWeights, 'max_score'));
        
        if ($totalMaxScore == 0) {
            return;
        }
        
        // Update all weights proportionally
        foreach ($criteriaWeights as $cw) {
            $autoWeight = ($cw['max_score'] / $totalMaxScore) * 100;
            $this->db->query(
                "UPDATE criteria_weights SET weight = ? WHERE id = ?",
                [$autoWeight, $cw['id']]
            );
        }
    }
    
    public function update($roundId, $id) {
        $this->restrictJudges();
        
        // Only allow toggling is_active, weights are auto-calculated
        $this->db->query(
            "UPDATE criteria_weights SET is_active = ? WHERE id = ?",
            [
                $_POST['is_active'] ?? 1,
                $id
            ]
        );
        
        // Recalculate all weights after toggling
        $this->recalculateWeights($roundId);
        
        $this->logAudit('UPDATE', 'criteria_weights', $id, null, $_POST);
        
        Session::set('success_message', 'Criteria status updated. Weights recalculated automatically.');
        $this->redirect('/tabulation/rounds/' . $roundId . '/weights');
    }
    
    public function delete($roundId, $id) {
        $this->restrictJudges();
        
        $this->db->query("DELETE FROM criteria_weights WHERE id = ?", [$id]);
        
        // Recalculate remaining weights
        $this->recalculateWeights($roundId);
        
        $this->logAudit('DELETE', 'criteria_weights', $id, null, null);
        
        Session::set('success_message', 'Criteria removed. Remaining weights recalculated automatically.');
        $this->redirect('/tabulation/rounds/' . $roundId . '/weights');
    }
}


