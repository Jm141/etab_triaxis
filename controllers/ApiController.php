<?php
/**
 * API Controller
 */

class ApiController extends Controller {
    
    public function getRankings($roundId) {
        $rankings = $this->db->fetchAll(
            "SELECT r.*, c.contestant_number, c.name, c.team_name
             FROM rankings r
             JOIN contestants c ON r.contestant_id = c.id
             WHERE r.round_id = ?
             ORDER BY r.rank, r.total_score DESC",
            [$roundId]
        );
        
        $this->json(['success' => true, 'data' => $rankings]);
    }
}



