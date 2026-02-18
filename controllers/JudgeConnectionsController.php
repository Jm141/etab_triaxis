<?php
/**
 * Judge Connections Controller (Admin)
 * Shows online/offline status based on judge heartbeat (users.last_ping).
 */

class JudgeConnectionsController extends Controller {
    
    public function index() {
        // Admin-only page (judges blocked)
        $this->restrictJudges();
        
        // Ongoing events (for stats panel)
        $ongoingEvents = $this->db->fetchAll(
            "SELECT id, name, event_type 
             FROM events 
             WHERE status = 'Ongoing'
             ORDER BY name ASC"
        );
        
        // Online = heartbeat within last 8 seconds
        $rows = $this->db->fetchAll(
            "SELECT 
                j.id as judge_id,
                j.judge_number,
                u.full_name,
                u.username,
                u.email,
                u.last_ping,
                UNIX_TIMESTAMP(u.last_ping) as last_ping_unix,
                e.id as event_id,
                e.name as event_name,
                e.event_type,
                COUNT(DISTINCT CASE 
                    WHEN ja.is_active = 1 AND ja.is_preparation_only = 0 THEN ja.round_id 
                    ELSE NULL 
                END) as assigned_rounds,
                CASE 
                    WHEN u.last_ping IS NOT NULL AND u.last_ping >= (NOW() - INTERVAL 8 SECOND) THEN 1
                    ELSE 0
                END as is_active
             FROM judges j
             JOIN users u ON j.user_id = u.id
             JOIN events e ON j.event_id = e.id
             LEFT JOIN judge_assignments ja ON ja.judge_id = j.id
             WHERE j.is_active = 1 AND e.status = 'Ongoing'
             GROUP BY j.id, e.id, u.id
             ORDER BY e.name ASC, CAST(j.judge_number AS UNSIGNED), j.judge_number ASC, u.full_name ASC"
        );
        
        $judgeConnections = [];
        foreach ($rows as $row) {
            $judgeConnections[] = [
                'judge' => [
                    'id' => (int)$row['judge_id'],
                    'full_name' => $row['full_name'],
                    'username' => $row['username'],
                    'email' => $row['email'],
                    'judge_number' => $row['judge_number'],
                    'assigned_rounds' => (int)($row['assigned_rounds'] ?? 0),
                ],
                'event' => [
                    'id' => (int)$row['event_id'],
                    'name' => $row['event_name'],
                    'event_type' => $row['event_type'] ?? '',
                ],
                'is_active' => !empty($row['is_active']),
                'last_activity' => !empty($row['last_ping_unix']) ? (int)$row['last_ping_unix'] : null, // used by JS
                'last_heartbeat' => !empty($row['last_ping']) ? $row['last_ping'] : null,
                'session_info' => null,
                'response_time' => null
            ];
        }
        
        $this->view('dashboard/judge_connections', [
            'judgeConnections' => $judgeConnections,
            'ongoingEvents' => $ongoingEvents
        ]);
    }
}

