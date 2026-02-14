<?php
/**
 * Base Controller Class
 */

abstract class Controller {
    protected $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->checkCSRF();
    }
    
    protected function view($view, $data = []) {
        extract($data);
        $viewFile = __DIR__ . '/../views/' . $view . '.php';
        
        if (!file_exists($viewFile)) {
            die("View not found: {$view}");
        }
        
        require $viewFile;
    }
    
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    protected function redirect($url) {
        header("Location: {$url}");
        exit;
    }
    
    protected function checkCSRF() {
        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
                // Check if this is an AJAX request
                $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                         strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
                
                // Check if request expects JSON response
                $acceptsJson = !empty($_SERVER['HTTP_ACCEPT']) && 
                              strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
                
                // If it's an AJAX request or expects JSON, return JSON error
                if ($isAjax || $acceptsJson) {
                    $this->json(['success' => false, 'message' => 'CSRF token validation failed'], 403);
                    return;
                }
                
                // Otherwise, return plain text error
                die("CSRF token validation failed");
            }
        }
    }
    
    protected function requireAuth($requiredRole = null) {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/tabulation/login');
        }
        
        if ($requiredRole && !$this->hasRole($requiredRole)) {
            $this->accessDenied("Access denied. Required role: {$requiredRole}");
        }
    }
    
    protected function requireRoles($allowedRoles = []) {
        $this->requireAuth();
        
        $currentRole = Session::get('role_name');
        
        if (!in_array($currentRole, $allowedRoles) && $currentRole !== 'Super Admin') {
            $this->accessDenied("Access denied. This page is restricted to: " . implode(', ', $allowedRoles));
        }
    }
    
    protected function restrictToRoles($allowedRoles = []) {
        $this->requireAuth();
        
        $currentRole = Session::get('role_name');
        
        if (!in_array($currentRole, $allowedRoles) && $currentRole !== 'Super Admin') {
            $this->accessDenied("Access denied. This page is restricted to: " . implode(', ', $allowedRoles));
        }
    }
    
    protected function restrictJudges() {
        $this->requireAuth();
        
        $currentRole = Session::get('role_name');
        
        if ($currentRole === 'Judge') {
            $this->accessDenied("Access denied. Judges can only access scoring pages.");
        }
    }
    
    protected function accessDenied($message = "Access Denied") {
        http_response_code(403);
        die("
        <!DOCTYPE html>
        <html>
        <head>
            <title>Access Denied</title>
            <style>
                body { font-family: 'Inter', sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; background: #0a0a0a; color: #fff; }
                .error-box { text-align: center; padding: 40px; }
                h1 { color: #00d9ff; font-size: 2rem; margin-bottom: 1rem; }
                p { color: rgba(255,255,255,0.7); }
                a { color: #00d9ff; text-decoration: none; }
            </style>
        </head>
        <body>
            <div class='error-box'>
                <h1>403 - Access Denied</h1>
                <p>{$message}</p>
                <p><a href='/tabulation/dashboard'>← Back to Dashboard</a></p>
            </div>
        </body>
        </html>
        ");
    }
    
    protected function hasRole($roleName) {
        if (!isset($_SESSION['role_name'])) {
            return false;
        }
        return $_SESSION['role_name'] === $roleName || $_SESSION['role_name'] === 'Super Admin';
    }
    
    protected function hasPermission($permission) {
        if (!isset($_SESSION['permissions'])) {
            return false;
        }
        
        $permissions = json_decode($_SESSION['permissions'], true) ?? [];
        
        // Super Admin has all permissions
        if (in_array('*', $permissions)) {
            return true;
        }
        
        return in_array($permission, $permissions);
    }
    
    protected function validateInput($data, $rules) {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            $ruleParts = explode('|', $rule);
            
            foreach ($ruleParts as $rulePart) {
                if ($rulePart === 'required' && empty($value)) {
                    $errors[$field] = ucfirst($field) . " is required";
                } elseif ($rulePart === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = ucfirst($field) . " must be a valid email";
                } elseif (strpos($rulePart, 'min:') === 0) {
                    $min = (int)substr($rulePart, 4);
                    if (strlen($value) < $min) {
                        $errors[$field] = ucfirst($field) . " must be at least {$min} characters";
                    }
                } elseif (strpos($rulePart, 'max:') === 0) {
                    $max = (int)substr($rulePart, 4);
                    if (strlen($value) > $max) {
                        $errors[$field] = ucfirst($field) . " must not exceed {$max} characters";
                    }
                } elseif (strpos($rulePart, 'numeric') === 0 && !is_numeric($value)) {
                    $errors[$field] = ucfirst($field) . " must be a number";
                }
            }
        }
        
        return $errors;
    }
    
    protected function logAudit($action, $tableName = null, $recordId = null, $oldValues = null, $newValues = null) {
        $sql = "INSERT INTO audit_logs (user_id, event_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $_SESSION['user_id'] ?? null,
            $_SESSION['current_event_id'] ?? null,
            $action,
            $tableName,
            $recordId,
            $oldValues ? json_encode($oldValues) : null,
            $newValues ? json_encode($newValues) : null,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ];
        
        $this->db->query($sql, $params);
    }
    
    /**
     * Get events accessible by the current user
     * Super Admin sees all events, others see only assigned events
     */
    protected function getAccessibleEvents() {
        $userId = Session::get('user_id');
        $roleName = Session::get('role_name');
        
        // Update event statuses before filtering
        $this->updateEventStatuses();
        
        // Super Admin sees everything
        if ($roleName === 'Super Admin') {
            return $this->db->fetchAll(
                "SELECT e.*, u.full_name as created_by_name
                 FROM events e
                 LEFT JOIN users u ON e.created_by = u.id
                 ORDER BY e.created_at DESC"
            );
        }
        
        // Judges see only events with 'Ongoing' status
        if ($roleName === 'Judge') {
            return $this->db->fetchAll(
                "SELECT DISTINCT e.*, u.full_name as created_by_name
                 FROM events e
                 LEFT JOIN users u ON e.created_by = u.id
                 INNER JOIN user_event_assignments uea ON e.id = uea.event_id
                 WHERE uea.user_id = ? AND uea.is_active = 1 AND e.status = 'Ongoing'
                 ORDER BY e.created_at DESC",
                [$userId]
            );
        }
        
        // Others see only assigned events
        return $this->db->fetchAll(
            "SELECT DISTINCT e.*, u.full_name as created_by_name
             FROM events e
             LEFT JOIN users u ON e.created_by = u.id
             INNER JOIN user_event_assignments uea ON e.id = uea.event_id
             WHERE uea.user_id = ? AND uea.is_active = 1
             ORDER BY e.created_at DESC",
            [$userId]
        );
    }
    
    /**
     * Update event statuses based on dates
     * Events close 1 hour after end_time
     */
    protected function updateEventStatuses() {
        // Update events that should be 'Ongoing'
        // Event starts if: event_date + start_time <= NOW()
        $this->db->query(
            "UPDATE events 
             SET status = 'Ongoing' 
             WHERE status IN ('Draft', 'Pending')
             AND (
                 (start_time IS NULL AND event_date <= CURDATE())
                 OR (start_time IS NOT NULL AND CONCAT(event_date, ' ', start_time) <= NOW())
                 OR (event_date < CURDATE())
             )
             AND (end_time IS NULL OR DATE_ADD(CONCAT(event_date, ' ', end_time), INTERVAL 1 HOUR) > NOW())"
        );
        
        // Get events that are about to be marked as 'Finished' (before updating)
        $eventsToFinish = $this->db->fetchAll(
            "SELECT id FROM events 
             WHERE status = 'Ongoing'
             AND (
                 (end_time IS NOT NULL AND DATE_ADD(CONCAT(event_date, ' ', end_time), INTERVAL 1 HOUR) < NOW())
                 OR (end_time IS NULL AND event_date < CURDATE())
             )"
        );
        
        // Update events that should be 'Finished' (1 hour after end_time)
        $this->db->query(
            "UPDATE events 
             SET status = 'Finished' 
             WHERE status = 'Ongoing'
             AND end_time IS NOT NULL
             AND DATE_ADD(CONCAT(event_date, ' ', end_time), INTERVAL 1 HOUR) < NOW()"
        );
        
        // Handle events without end_time
        $this->db->query(
            "UPDATE events 
             SET status = 'Finished' 
             WHERE status = 'Ongoing'
             AND end_time IS NULL
             AND event_date < CURDATE()"
        );
        
        // Create backups for events that just finished
        if (!empty($eventsToFinish)) {
            require_once __DIR__ . '/DatabaseBackup.php';
            $backup = new DatabaseBackup();
            
            foreach ($eventsToFinish as $event) {
                try {
                    $backup->createEventBackup($event['id']);
                } catch (Exception $e) {
                    error_log("Failed to create backup for event ID {$event['id']}: " . $e->getMessage());
                }
            }
        }
    }
    
    /**
     * Check if user has access to a specific event
     */
    protected function hasEventAccess($eventId) {
        $userId = Session::get('user_id');
        $roleName = Session::get('role_name');
        
        // Super Admin has access to everything
        if ($roleName === 'Super Admin') {
            return true;
        }
        
        // Check if user is assigned to this event
        $assignment = $this->db->fetchOne(
            "SELECT id FROM user_event_assignments 
             WHERE user_id = ? AND event_id = ? AND is_active = 1",
            [$userId, $eventId]
        );
        
        return $assignment !== false;
    }
    
    /**
     * Require event access or show 403
     */
    protected function requireEventAccess($eventId) {
        if (!$this->hasEventAccess($eventId)) {
            $this->accessDenied("You do not have access to this event.");
        }
    }
    
    /**
     * Check if an event is finished
     * @param int $eventId The event ID
     * @return bool True if event is finished, false otherwise
     */
    protected function isEventFinished($eventId) {
        $event = $this->db->fetchOne(
            "SELECT status FROM events WHERE id = ?",
            [$eventId]
        );
        
        return $event && $event['status'] === 'Finished';
    }
    
    /**
     * Prevent modifications to finished events
     * @param int $eventId The event ID
     * @param string $message Optional custom error message
     */
    protected function preventFinishedEventModification($eventId, $message = null) {
        if ($this->isEventFinished($eventId)) {
            $errorMessage = $message ?? "Cannot modify data for a finished event. The event has been completed and data is locked.";
            
            // Check if this is an AJAX request
            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                     strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
            
            // Check if request expects JSON response
            $acceptsJson = !empty($_SERVER['HTTP_ACCEPT']) && 
                          strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
            
            if ($isAjax || $acceptsJson) {
                $this->json(['success' => false, 'message' => $errorMessage], 403);
            } else {
                Session::set('error_message', $errorMessage);
                // Try to redirect back, or to events list
                $referer = $_SERVER['HTTP_REFERER'] ?? '/tabulation/events';
                $this->redirect($referer);
            }
        }
    }
}


