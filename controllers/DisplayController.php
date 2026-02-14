<?php
/**
 * Display/Scoreboard Controller (Public View)
 */

class DisplayController extends Controller {
    
    public function event($eventId) {
        $event = $this->db->fetchOne("SELECT * FROM events WHERE id = ?", [$eventId]);
        if (!$event) {
            die("Event not found");
        }
        
        // Get all rounds with public results
        $rounds = $this->db->fetchAll(
            "SELECT r.*, el.name as level_name
             FROM rounds r
             JOIN event_levels el ON r.level_id = el.id
             WHERE el.event_id = ? AND r.id IN (
                 SELECT round_id FROM result_snapshots WHERE is_public = 1
             )
             ORDER BY el.`order`, r.`order`",
            [$eventId]
        );
        
        $this->view('display/event', [
            'event' => $event,
            'rounds' => $rounds
        ]);
    }
    
    public function round($roundId) {
        // Get latest public snapshot
        $snapshot = $this->db->fetchOne(
            "SELECT * FROM result_snapshots 
             WHERE round_id = ? AND is_public = 1 
             ORDER BY released_at DESC LIMIT 1",
            [$roundId]
        );
        
        if (!$snapshot) {
            die("Results not available");
        }
        
        $round = $this->db->fetchOne(
            "SELECT r.*, el.name as level_name, el.event_id, e.name as event_name
             FROM rounds r
             JOIN event_levels el ON r.level_id = el.id
             JOIN events e ON el.event_id = e.id
             WHERE r.id = ?",
            [$roundId]
        );
        
        $rankings = json_decode($snapshot['snapshot_data'], true);
        
        // Enrich with contestant info
        foreach ($rankings as &$ranking) {
            $contestant = $this->db->fetchOne(
                "SELECT * FROM contestants WHERE id = ?",
                [$ranking['contestant_id']]
            );
            $ranking['contestant'] = $contestant;
        }
        
        $this->view('display/round', [
            'round' => $round,
            'rankings' => $rankings
        ]);
    }
    
    public function lineup($eventId = null) {
        // Update event statuses before displaying
        $this->updateEventStatuses();
        
        // Check for GET parameter (from form submission)
        $eventId = $eventId ?? ($_GET['event_id'] ?? null);
        
        // If no event ID, show selector
        if (!$eventId) {
            // Filter to show only ongoing events for public view
            // If logged in as admin, show all events
            if (Session::has('user_id')) {
                $events = $this->db->fetchAll(
                    "SELECT * FROM events ORDER BY event_date DESC, created_at DESC"
                );
            } else {
                // Public view - only show ongoing events
                $events = $this->db->fetchAll(
                    "SELECT * FROM events 
                     WHERE status = 'Ongoing' 
                     ORDER BY event_date DESC, created_at DESC"
                );
            }
            
            // If user is logged in, show admin interface
            if (Session::has('user_id')) {
                $this->view('display/lineup_selector', ['events' => $events]);
            } else {
                // Public view - just show selector
                $this->view('display/lineup_selector', ['events' => $events]);
            }
            return;
        }
        
        $event = $this->db->fetchOne("SELECT * FROM events WHERE id = ?", [$eventId]);
        if (!$event) {
            die("Event not found");
        }
        
        // Get all active contestants for this event
        $contestants = $this->db->fetchAll(
            "SELECT * FROM contestants 
             WHERE event_id = ? AND status = 'Active' 
             ORDER BY CAST(contestant_number AS UNSIGNED), contestant_number",
            [$eventId]
        );
        
        $this->view('display/lineup', [
            'event' => $event,
            'contestants' => $contestants
        ]);
    }
    
}

