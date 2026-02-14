<?php
/**
 * Result Controller
 */

class ResultController extends Controller {
    
    public function index($eventId) {
        $this->restrictJudges();
        $this->requireEventAccess($eventId);
        
        $event = $this->db->fetchOne("SELECT * FROM events WHERE id = ?", [$eventId]);
        if (!$event) {
            die("Event not found");
        }
        
        // Get all rounds with rankings
        $rounds = $this->db->fetchAll(
            "SELECT r.*, el.name as level_name,
             (SELECT COUNT(*) FROM rankings WHERE round_id = r.id) as ranking_count
             FROM rounds r
             JOIN event_levels el ON r.level_id = el.id
             WHERE el.event_id = ?
             ORDER BY el.`order`, r.`order`",
            [$eventId]
        );
        
        // Auto-calculate rankings for rounds where all scores are complete
        require_once __DIR__ . '/../core/ScoringEngine.php';
        $scoringEngine = new ScoringEngine();
        
        foreach ($rounds as $round) {
            $scoringEngine->autoCalculateIfComplete($round['id']);
        }
        
        // Refresh rounds data after auto-calculation
        $rounds = $this->db->fetchAll(
            "SELECT r.*, el.name as level_name,
             (SELECT COUNT(*) FROM rankings WHERE round_id = r.id) as ranking_count
             FROM rounds r
             JOIN event_levels el ON r.level_id = el.id
             WHERE el.event_id = ?
             ORDER BY el.`order`, r.`order`",
            [$eventId]
        );
        
        $this->view('result/index', [
            'event' => $event,
            'rounds' => $rounds
        ]);
    }
    
    public function round($eventId, $roundId) {
        $this->restrictJudges();
        $this->requireEventAccess($eventId);
        
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
        
        // Auto-calculate rankings if all scores are complete
        require_once __DIR__ . '/../core/ScoringEngine.php';
        $scoringEngine = new ScoringEngine();
        $scoringEngine->autoCalculateIfComplete($roundId);
        
        // Get rankings
        $rankings = $this->db->fetchAll(
            "SELECT r.*, c.contestant_number, c.name, c.team_name
             FROM rankings r
             JOIN contestants c ON r.contestant_id = c.id
             WHERE r.round_id = ?
             ORDER BY r.rank, r.total_score DESC",
            [$roundId]
        );
        
        // Get judge breakdown (raw total per score for consistency with rankings)
        $judgeBreakdown = $this->db->fetchAll(
            "SELECT s.contestant_id, c.contestant_number, c.name, s.judge_id, j.judge_number, u.full_name as judge_name,
                    (SELECT COALESCE(SUM(sd.raw_score), 0) FROM score_details sd WHERE sd.score_id = s.id) as raw_total
             FROM scores s
             JOIN contestants c ON s.contestant_id = c.id
             JOIN judges j ON s.judge_id = j.id
             JOIN users u ON j.user_id = u.id
             WHERE s.round_id = ? AND s.is_submitted = 1
             ORDER BY c.contestant_number, j.judge_number",
            [$roundId]
        );
        
        $this->view('result/round', [
            'round' => $round,
            'rankings' => $rankings,
            'judgeBreakdown' => $judgeBreakdown
        ]);
    }
    
    public function calculate($eventId) {
        $this->restrictJudges();
        $this->requireEventAccess($eventId);
        
        $roundId = $_POST['round_id'] ?? null;
        if (!$roundId) {
            $this->json(['success' => false, 'message' => 'Round ID is required'], 400);
            return;
        }
        
        require_once __DIR__ . '/../core/ScoringEngine.php';
        $scoringEngine = new ScoringEngine();
        
        $result = $scoringEngine->recalculateRound($roundId);
        
        if ($result) {
            $this->logAudit('CALCULATE_RANKINGS', 'rankings', null, null, ['round_id' => $roundId]);
            $this->json(['success' => true, 'message' => 'Rankings calculated successfully']);
        } else {
            $this->json(['success' => false, 'message' => 'Calculation failed'], 500);
        }
    }
    
    public function release($eventId) {
        $this->restrictJudges();
        $this->requireEventAccess($eventId);
        
        $roundId = $_POST['round_id'] ?? null;
        if (!$roundId) {
            $this->json(['success' => false, 'message' => 'Round ID is required'], 400);
            return;
        }
        
        // Get rankings
        $rankings = $this->db->fetchAll(
            "SELECT * FROM rankings WHERE round_id = ? ORDER BY rank",
            [$roundId]
        );
        
        // Create snapshot
        $this->db->query(
            "INSERT INTO result_snapshots (event_id, round_id, snapshot_data, is_public, released_by, released_at)
             VALUES (?, ?, ?, 1, ?, NOW())",
            [
                $eventId,
                $roundId,
                json_encode($rankings),
                Session::get('user_id')
            ]
        );
        
        $this->logAudit('RELEASE_RESULTS', 'result_snapshots', $this->db->lastInsertId(), null, ['round_id' => $roundId]);
        
        $this->json(['success' => true, 'message' => 'Results released successfully']);
    }
    
    /**
     * Summary report showing winners with judge scores
     */
    public function summary($eventId, $roundId) {
        $this->restrictJudges();
        $this->requireEventAccess($eventId);
        
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
        
        // Get rankings (winners list in ascending order by rank)
        $rankings = $this->db->fetchAll(
            "SELECT r.*, c.contestant_number, c.name, c.team_name
             FROM rankings r
             JOIN contestants c ON r.contestant_id = c.id
             WHERE r.round_id = ?
             ORDER BY r.rank ASC, r.average_score DESC",
            [$roundId]
        );
        
        // Get all judges for this round
        $judges = $this->db->fetchAll(
            "SELECT DISTINCT j.id, j.judge_number, u.full_name as judge_name
             FROM judges j
             JOIN users u ON j.user_id = u.id
             JOIN judge_assignments ja ON j.id = ja.judge_id
             WHERE ja.round_id = ? AND ja.is_active = 1
             ORDER BY j.judge_number",
            [$roundId]
        );
        
        // Get scores for each contestant from each judge (raw totals: sum of criteria raw_score, not percentage)
        $contestantScores = [];
        foreach ($rankings as &$ranking) {
            $contestantId = $ranking['contestant_id'];
            $contestantScores[$contestantId] = [
                'contestant' => $ranking,
                'judge_scores' => []
            ];
            $rawSum = 0;
            $rawCount = 0;
            
            foreach ($judges as $judge) {
                $score = $this->db->fetchOne(
                    "SELECT s.id,
                            (SELECT COALESCE(SUM(sd.raw_score), 0) FROM score_details sd WHERE sd.score_id = s.id) as raw_total
                     FROM scores s
                     WHERE s.round_id = ? AND s.contestant_id = ? AND s.judge_id = ? AND s.is_submitted = 1",
                    [$roundId, $contestantId, $judge['id']]
                );
                $raw = $score ? (float)$score['raw_total'] : null;
                $contestantScores[$contestantId]['judge_scores'][$judge['id']] = [
                    'judge' => $judge,
                    'score' => $raw
                ];
                if ($raw !== null) {
                    $rawSum += $raw;
                    $rawCount++;
                }
            }
            // Per-contestant raw total and average for display (Summary shows raw, not percentage)
            $ranking['raw_total'] = $rawSum;
            $ranking['raw_average'] = $rawCount > 0 ? $rawSum / $rawCount : 0;
        }
        unset($ranking);
        
        // Order by contestant number for display
        usort($rankings, function($a, $b) {
            return (int)$a['contestant_number'] <=> (int)$b['contestant_number'];
        });
        
        $this->view('result/summary', [
            'round' => $round,
            'rankings' => $rankings,
            'judges' => $judges,
            'contestantScores' => $contestantScores
        ]);
    }
    
    /**
     * Overwrite Keys Management (Organizer only)
     * Allows organizer to generate overwrite keys for scores
     */
    public function overwriteKeys($eventId) {
        $this->restrictJudges();
        $this->requireEventAccess($eventId);
        
        $currentRole = Session::get('role_name');
        
        // Only Super Admin and Event Organizer can access
        if (!in_array($currentRole, ['Super Admin', 'Event Organizer'])) {
            $this->accessDenied("Access denied. Only organizers can generate overwrite keys.");
        }
        
        $event = $this->db->fetchOne("SELECT * FROM events WHERE id = ?", [$eventId]);
        if (!$event) {
            die("Event not found");
        }
        
        // Get all rounds for this event
        $rounds = $this->db->fetchAll(
            "SELECT r.*, el.name as level_name
             FROM rounds r
             JOIN event_levels el ON r.level_id = el.id
             WHERE el.event_id = ?
             ORDER BY el.`order`, r.`order`",
            [$eventId]
        );
        
        // Get all submitted scores with their overwrite key status
        $scores = [];
        foreach ($rounds as $round) {
            $roundScores = $this->db->fetchAll(
                "SELECT s.id as score_id, s.overwrite_key, s.overwrite_key_set_at,
                 s.admin_edit_allowed, s.is_submitted,
                 c.contestant_number, c.name as contestant_name,
                 j.judge_number, u.full_name as judge_name,
                 r.name as round_name, r.id as round_id
                 FROM scores s
                 JOIN contestants c ON s.contestant_id = c.id
                 JOIN judges j ON s.judge_id = j.id
                 JOIN users u ON j.user_id = u.id
                 JOIN rounds r ON s.round_id = r.id
                 WHERE s.round_id = ? AND s.is_submitted = 1
                 ORDER BY c.contestant_number, j.judge_number",
                [$round['id']]
            );
            
            foreach ($roundScores as $score) {
                $scores[] = $score;
            }
        }
        
        $this->view('result/overwrite_keys', [
            'event' => $event,
            'rounds' => $rounds,
            'scores' => $scores
        ]);
    }
}


