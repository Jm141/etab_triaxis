<?php
/**
 * Notification Controller
 * Handles permission requests and notifications
 */

class NotificationController extends Controller {
    
    public function index() {
        $this->requireAuth();
        
        $userId = Session::get('user_id');
        
        // Get all notifications for this user
        $notifications = $this->db->fetchAll(
            "SELECT n.*
             FROM notifications n
             WHERE n.user_id = ?
             ORDER BY n.is_read ASC, n.created_at DESC",
            [$userId]
        );
        
        // Enrich notifications with related data
        foreach ($notifications as &$notif) {
            if ($notif['related_type'] === 'score' && $notif['related_id']) {
                // Get score details
                $score = $this->db->fetchOne(
                    "SELECT s.*,
                     c.name as contestant_name, c.contestant_number, c.id as contestant_id,
                     r.id as round_id, r.name as round_name, r.level_id,
                     el.event_id, el.name as level_name,
                     e.name as event_name
                     FROM scores s
                     JOIN contestants c ON s.contestant_id = c.id
                     JOIN rounds r ON s.round_id = r.id
                     JOIN event_levels el ON r.level_id = el.id
                     JOIN events e ON el.event_id = e.id
                     WHERE s.id = ?",
                    [$notif['related_id']]
                );
                
                if ($score) {
                    $notif['event_name'] = $score['event_name'];
                    $notif['round_name'] = $score['round_name'];
                    $notif['round_id'] = $score['round_id'];
                    $notif['contestant_number'] = $score['contestant_number'];
                    $notif['contestant_name'] = $score['contestant_name'];
                }
            } elseif ($notif['related_type'] === 'report_deduction' && $notif['related_id']) {
                $deduction = $this->db->fetchOne(
                    "SELECT rd.*, e.name as event_name, c.contestant_number, c.name as contestant_name
                     FROM report_deductions rd
                     JOIN events e ON rd.event_id = e.id
                     JOIN contestants c ON rd.contestant_id = c.id
                     WHERE rd.id = ?",
                    [$notif['related_id']]
                );
                
                if ($deduction) {
                    $notif['event_name'] = $deduction['event_name'];
                    $notif['event_id'] = $deduction['event_id'];
                    $notif['contestant_number'] = $deduction['contestant_number'];
                    $notif['contestant_name'] = $deduction['contestant_name'];
                    $notif['scope_type'] = $deduction['scope_type'];
                    $notif['scope_id'] = $deduction['scope_id'];
                    
                    if ($deduction['scope_type'] === 'round') {
                        $round = $this->db->fetchOne("SELECT name FROM rounds WHERE id = ?", [$deduction['scope_id']]);
                        $notif['round_name'] = $round['name'] ?? null;
                    } elseif ($deduction['scope_type'] === 'level') {
                        $level = $this->db->fetchOne("SELECT name FROM event_levels WHERE id = ?", [$deduction['scope_id']]);
                        $notif['round_name'] = $level['name'] ?? null;
                    } else {
                        $notif['round_name'] = 'Final';
                    }
                }
            }
        }
        
        // Get unread count
        $unreadCount = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0",
            [$userId]
        )['count'] ?? 0;
        
        $this->view('notifications/index', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount
        ]);
    }
    
    public function respond($notificationId) {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Invalid request method'], 405);
            return;
        }
        
        $userId = Session::get('user_id');
        $currentRole = Session::get('role_name');
        
        // Only admins, organizers, tech admins, and tabulators can respond to permission requests
        if (!in_array($currentRole, ['Super Admin', 'Event Admin', 'Event Organizer', 'Event Technical Admin', 'Tabulator'])) {
            $this->json(['success' => false, 'message' => 'You do not have permission to respond to permission requests'], 403);
            return;
        }
        
        $action = $_POST['action'] ?? ''; // 'grant' or 'deny'
        
        if (!in_array($action, ['grant', 'deny'])) {
            $this->json(['success' => false, 'message' => 'Invalid action'], 400);
            return;
        }
        
        // Get notification
        $notification = $this->db->fetchOne(
            "SELECT * FROM notifications WHERE id = ? AND user_id = ?",
            [$notificationId, $userId]
        );
        
        if (!$notification) {
            $this->json(['success' => false, 'message' => 'Notification not found'], 404);
            return;
        }
        
        // Get related score (from related_id when related_type is 'score')
        if ($notification['related_type'] !== 'score' || !$notification['related_id']) {
            $this->json(['success' => false, 'message' => 'Invalid notification type'], 400);
            return;
        }
        
        $score = $this->db->fetchOne(
            "SELECT s.*, j.user_id as judge_user_id
             FROM scores s
             JOIN judges j ON s.judge_id = j.id
             WHERE s.id = ?",
            [$notification['related_id']]
        );
        
        if (!$score) {
            $this->json(['success' => false, 'message' => 'Score not found'], 404);
            return;
        }
        
        $this->db->getConnection()->beginTransaction();
        
        try {
            // Update score permission status
            // Get requester ID from score
            $requesterId = $score['permission_requested_by'] ?? null;
            
            if ($action === 'grant') {
                // Check if this is a judge request (judge wants to edit their own score)
                // or admin request (admin wants to edit judge's score)
                $isJudgeRequest = ($score['permission_request_status'] ?? '') === 'judge_requested';
                
                if ($isJudgeRequest) {
                    // Judge requested to edit their own submitted score
                    // Grant permission for judge to edit (don't set admin_edit_allowed)
                    $this->db->query(
                        "UPDATE scores SET 
                         permission_request_status = 'granted',
                         permission_responded_by = ?,
                         permission_responded_at = NOW()
                         WHERE id = ?",
                        [$userId, $score['id']]
                    );
                } else {
                    // Admin requested to edit judge's score (shouldn't happen here, but handle it)
                    $this->db->query(
                        "UPDATE scores SET 
                         permission_request_status = 'granted',
                         permission_responded_by = ?,
                         permission_responded_at = NOW(),
                         admin_edit_allowed = 1
                         WHERE id = ?",
                        [$userId, $score['id']]
                    );
                }
                
                // Create notification for the requester
                if ($requesterId && $requesterId != $userId) {
                    $this->db->query(
                        "INSERT INTO notifications (user_id, type, title, message, related_type, related_id, created_at)
                         VALUES (?, 'permission_granted', 'Permission Granted', 
                         'Your request to edit scores has been granted.', 
                         'score', ?, NOW())",
                        [$requesterId, $score['id']]
                    );
                }
            } else {
                // Deny permission
                $this->db->query(
                    "UPDATE scores SET 
                     permission_request_status = 'denied',
                     permission_responded_by = ?,
                     permission_responded_at = NOW()
                     WHERE id = ?",
                    [$userId, $score['id']]
                );
                
                // Create notification for the requester
                if ($requesterId && $requesterId != $userId) {
                    $this->db->query(
                        "INSERT INTO notifications (user_id, type, title, message, related_type, related_id, created_at)
                         VALUES (?, 'permission_denied', 'Permission Denied', 
                         'Your request to edit scores has been denied.', 
                         'score', ?, NOW())",
                        [$requesterId, $score['id']]
                    );
                }
            }
            
            // Mark notification as read
            $this->db->query(
                "UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = ?",
                [$notificationId]
            );
            
            $this->db->getConnection()->commit();
            
            $this->json([
                'success' => true, 
                'message' => $action === 'grant' ? 'Permission granted successfully' : 'Permission denied'
            ]);
            
        } catch (Exception $e) {
            $this->db->getConnection()->rollBack();
            $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
    
    public function markRead($notificationId) {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Invalid request method'], 405);
            return;
        }
        
        $userId = Session::get('user_id');
        
        // Verify notification belongs to user
        $notification = $this->db->fetchOne(
            "SELECT * FROM notifications WHERE id = ? AND user_id = ?",
            [$notificationId, $userId]
        );
        
        if (!$notification) {
            $this->json(['success' => false, 'message' => 'Notification not found'], 404);
            return;
        }
        
        $this->db->query(
            "UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = ?",
            [$notificationId]
        );
        
        $this->json(['success' => true, 'message' => 'Notification marked as read']);
    }
}

