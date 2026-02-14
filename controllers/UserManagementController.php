<?php
/**
 * User Management Controller
 */

class UserManagementController extends Controller {
    
    public function index() {
        $this->restrictJudges();
        
        $currentRole = Session::get('role_name');
        
        // Determine which roles can be managed
        if ($currentRole === 'Super Admin') {
            // Super Admin can see all users
            $users = $this->db->fetchAll(
                "SELECT u.*, r.name as role_name, r.description as role_description
                 FROM users u
                 JOIN roles r ON u.role_id = r.id
                 ORDER BY r.name, u.full_name"
            );
            
            $availableRoles = $this->db->fetchAll(
                "SELECT * FROM roles ORDER BY name"
            );
        } elseif ($currentRole === 'Event Admin' || $currentRole === 'Event Organizer') {
            // Event Admin and Event Organizer can see and manage non-admin users
            $users = $this->db->fetchAll(
                "SELECT u.*, r.name as role_name, r.description as role_description
                 FROM users u
                 JOIN roles r ON u.role_id = r.id
                 WHERE r.name NOT IN ('Super Admin', 'Event Admin', 'Event Organizer')
                 ORDER BY r.name, u.full_name"
            );
            
            $availableRoles = $this->db->fetchAll(
                "SELECT * FROM roles WHERE name NOT IN ('Super Admin', 'Event Admin', 'Event Organizer') ORDER BY name"
            );
        } else {
            $this->accessDenied("Access denied. Only Super Admin and Event Admin can manage users.");
        }
        
        $this->view('user_management/index', [
            'users' => $users,
            'availableRoles' => $availableRoles,
            'currentRole' => $currentRole
        ]);
    }
    
    public function create() {
        $this->restrictJudges();
        
        $currentRole = Session::get('role_name');
        
        if ($currentRole !== 'Super Admin' && $currentRole !== 'Event Admin' && $currentRole !== 'Event Organizer') {
            $this->accessDenied();
        }
        
        // Get available roles based on current user's role
        if ($currentRole === 'Super Admin') {
            $availableRoles = $this->db->fetchAll(
                "SELECT * FROM roles ORDER BY name"
            );
        } elseif ($currentRole === 'Event Organizer') {
            // Event Organizer can create: Event Technical Admin, Tabulator, Judge, Auditor, Host
            $availableRoles = $this->db->fetchAll(
                "SELECT * FROM roles WHERE name IN ('Event Technical Admin', 'Tabulator', 'Judge', 'Auditor', 'Host') ORDER BY name"
            );
        } else {
            // Event Admin can create: Tabulator, Judge, Auditor, Host
            $availableRoles = $this->db->fetchAll(
                "SELECT * FROM roles WHERE name NOT IN ('Super Admin', 'Event Admin', 'Event Organizer') ORDER BY name"
            );
        }
        
        $this->view('user_management/create', [
            'availableRoles' => $availableRoles,
            'currentRole' => $currentRole
        ]);
    }
    
    public function store() {
        $this->restrictJudges();
        
        $currentRole = Session::get('role_name');
        
        // Validate permissions
        if ($currentRole === 'Super Admin') {
            // Super Admin can create any role
            $allowedRoles = $this->db->fetchAll("SELECT id FROM roles");
            $allowedRoleIds = array_column($allowedRoles, 'id');
        } elseif ($currentRole === 'Event Organizer') {
            // Event Organizer can create: Event Technical Admin, Tabulator, Judge, Auditor, Host
            $allowedRoles = $this->db->fetchAll(
                "SELECT id FROM roles WHERE name IN ('Event Technical Admin', 'Tabulator', 'Judge', 'Auditor', 'Host')"
            );
            $allowedRoleIds = array_column($allowedRoles, 'id');
        } elseif ($currentRole === 'Event Admin') {
            // Event Admin can only create: Tabulator, Judge, Auditor, Host
            $allowedRoles = $this->db->fetchAll(
                "SELECT id FROM roles WHERE name NOT IN ('Super Admin', 'Event Admin', 'Event Organizer')"
            );
            $allowedRoleIds = array_column($allowedRoles, 'id');
        } else {
            $this->accessDenied();
        }
        
        $errors = $this->validateInput($_POST, [
            'username' => 'required|min:3',
            'email' => 'required|email',
            'full_name' => 'required|min:2',
            'password' => 'required|min:6',
            'role_id' => 'required|numeric'
        ]);
        
        if (!empty($errors)) {
            $availableRoles = $this->db->fetchAll("SELECT * FROM roles ORDER BY name");
            $this->view('user_management/create', [
                'errors' => $errors,
                'availableRoles' => $availableRoles,
                'data' => $_POST,
                'currentRole' => $currentRole
            ]);
            return;
        }
        
        // Check if role is allowed
        if (!in_array($_POST['role_id'], $allowedRoleIds)) {
            Session::set('error_message', 'You do not have permission to create users with this role');
            $this->redirect('/tabulation/user-management/create');
            return;
        }
        
        // Check if username or email already exists
        $existing = $this->db->fetchOne(
            "SELECT * FROM users WHERE username = ? OR email = ?",
            [$_POST['username'], $_POST['email']]
        );
        
        if ($existing) {
            Session::set('error_message', 'Username or email already exists');
            $this->redirect('/tabulation/user-management/create');
            return;
        }
        
        // Create user
        $passwordHash = password_hash($_POST['password'], PASSWORD_BCRYPT);
        
        $this->db->query(
            "INSERT INTO users (username, email, password_hash, full_name, role_id, is_active)
             VALUES (?, ?, ?, ?, ?, 1)",
            [
                $_POST['username'],
                $_POST['email'],
                $passwordHash,
                $_POST['full_name'],
                $_POST['role_id']
            ]
        );
        
        $userId = $this->db->lastInsertId();
        
        $this->logAudit('CREATE_USER', 'users', $userId, null, [
            'username' => $_POST['username'],
            'role_id' => $_POST['role_id']
        ]);
        
        Session::set('success_message', 'User created successfully');
        $this->redirect('/tabulation/user-management');
    }
    
    public function edit($id) {
        $this->restrictJudges();
        
        $currentRole = Session::get('role_name');
        
        $user = $this->db->fetchOne(
            "SELECT u.*, r.name as role_name
             FROM users u
             JOIN roles r ON u.role_id = r.id
             WHERE u.id = ?",
            [$id]
        );
        
        if (!$user) {
            die("User not found");
        }
        
        // Check permissions
        if (($currentRole === 'Event Admin' || $currentRole === 'Event Organizer') && 
            in_array($user['role_name'], ['Super Admin', 'Event Admin', 'Event Organizer'])) {
            $this->accessDenied("You cannot edit admin users.");
        }
        
        // Get available roles
        if ($currentRole === 'Super Admin') {
            $availableRoles = $this->db->fetchAll("SELECT * FROM roles ORDER BY name");
        } elseif ($currentRole === 'Event Organizer') {
            $availableRoles = $this->db->fetchAll(
                "SELECT * FROM roles WHERE name IN ('Event Technical Admin', 'Tabulator', 'Judge', 'Auditor', 'Host') ORDER BY name"
            );
        } else {
            $availableRoles = $this->db->fetchAll(
                "SELECT * FROM roles WHERE name NOT IN ('Super Admin', 'Event Admin', 'Event Organizer') ORDER BY name"
            );
        }
        
        $this->view('user_management/edit', [
            'user' => $user,
            'availableRoles' => $availableRoles,
            'currentRole' => $currentRole
        ]);
    }
    
    public function update($id) {
        $this->restrictJudges();
        
        $currentRole = Session::get('role_name');
        
        $user = $this->db->fetchOne("SELECT * FROM users WHERE id = ?", [$id]);
        if (!$user) {
            die("User not found");
        }
        
        // Get user's current role
        $userRole = $this->db->fetchOne(
            "SELECT r.name FROM roles r JOIN users u ON r.id = u.role_id WHERE u.id = ?",
            [$id]
        );
        
        // Check permissions
        if (($currentRole === 'Event Admin' || $currentRole === 'Event Organizer') && 
            in_array($userRole['name'], ['Super Admin', 'Event Admin', 'Event Organizer'])) {
            $this->accessDenied();
        }
        
        $errors = $this->validateInput($_POST, [
            'username' => 'required|min:3',
            'email' => 'required|email',
            'full_name' => 'required|min:2'
        ]);
        
        if (!empty($errors)) {
            $availableRoles = $this->db->fetchAll("SELECT * FROM roles ORDER BY name");
            $this->view('user_management/edit', [
                'errors' => $errors,
                'user' => array_merge($user, $_POST),
                'availableRoles' => $availableRoles,
                'currentRole' => $currentRole
            ]);
            return;
        }
        
        // Check role permission if changing role
        if (isset($_POST['role_id']) && $_POST['role_id'] != $user['role_id']) {
            if ($currentRole === 'Event Organizer') {
                $allowedRoles = $this->db->fetchAll(
                    "SELECT id FROM roles WHERE name IN ('Event Technical Admin', 'Tabulator', 'Judge', 'Auditor', 'Host')"
                );
                $allowedRoleIds = array_column($allowedRoles, 'id');
                
                if (!in_array($_POST['role_id'], $allowedRoleIds)) {
                    Session::set('error_message', 'You do not have permission to assign this role');
                    $this->redirect('/tabulation/user-management/edit/' . $id);
                    return;
                }
            } elseif ($currentRole === 'Event Admin') {
                $allowedRoles = $this->db->fetchAll(
                    "SELECT id FROM roles WHERE name NOT IN ('Super Admin', 'Event Admin', 'Event Organizer')"
                );
                $allowedRoleIds = array_column($allowedRoles, 'id');
                
                if (!in_array($_POST['role_id'], $allowedRoleIds)) {
                    Session::set('error_message', 'You do not have permission to assign this role');
                    $this->redirect('/tabulation/user-management/edit/' . $id);
                    return;
                }
            }
        }
        
        // Update user
        $updateData = [
            $_POST['username'],
            $_POST['email'],
            $_POST['full_name'],
            $_POST['role_id'] ?? $user['role_id'],
            $_POST['is_active'] ?? $user['is_active'],
            $id
        ];
        
        $this->db->query(
            "UPDATE users SET username = ?, email = ?, full_name = ?, role_id = ?, is_active = ? WHERE id = ?",
            $updateData
        );
        
        // Update password if provided
        if (!empty($_POST['password'])) {
            $passwordHash = password_hash($_POST['password'], PASSWORD_BCRYPT);
            $this->db->query(
                "UPDATE users SET password_hash = ? WHERE id = ?",
                [$passwordHash, $id]
            );
        }
        
        $this->logAudit('UPDATE_USER', 'users', $id, $user, $_POST);
        
        Session::set('success_message', 'User updated successfully');
        $this->redirect('/tabulation/user-management');
    }
    
    public function delete($id) {
        $this->restrictJudges();
        
        $currentRole = Session::get('role_name');
        
        $user = $this->db->fetchOne("SELECT * FROM users WHERE id = ?", [$id]);
        if (!$user) {
            die("User not found");
        }
        
        // Prevent deleting yourself
        if ($user['id'] == Session::get('user_id')) {
            Session::set('error_message', 'You cannot delete your own account');
            $this->redirect('/tabulation/user-management');
            return;
        }
        
        // Get user's role
        $userRole = $this->db->fetchOne(
            "SELECT r.name FROM roles r JOIN users u ON r.id = u.role_id WHERE u.id = ?",
            [$id]
        );
        
        // Check permissions
        if (($currentRole === 'Event Admin' || $currentRole === 'Event Organizer') && 
            in_array($userRole['name'], ['Super Admin', 'Event Admin', 'Event Organizer'])) {
            Session::set('error_message', 'You cannot delete admin users');
            $this->redirect('/tabulation/user-management');
            return;
        }
        
        // Check if user is assigned as judge
        $judgeAssignments = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM judges WHERE user_id = ?",
            [$id]
        );
        
        if ($judgeAssignments['count'] > 0) {
            Session::set('error_message', 'Cannot delete user. User is assigned as a judge. Remove judge assignments first.');
            $this->redirect('/tabulation/user-management');
            return;
        }
        
        $this->db->query("DELETE FROM users WHERE id = ?", [$id]);
        $this->logAudit('DELETE_USER', 'users', $id, $user, null);
        
        Session::set('success_message', 'User deleted successfully');
        $this->redirect('/tabulation/user-management');
    }
    
    public function toggleActive($id) {
        $this->restrictJudges();
        
        $currentRole = Session::get('role_name');
        
        $user = $this->db->fetchOne("SELECT * FROM users WHERE id = ?", [$id]);
        if (!$user) {
            $this->json(['success' => false, 'message' => 'User not found'], 404);
            return;
        }
        
        // Prevent deactivating yourself
        if ($user['id'] == Session::get('user_id')) {
            $this->json(['success' => false, 'message' => 'You cannot deactivate your own account'], 403);
            return;
        }
        
        // Get user's role
        $userRole = $this->db->fetchOne(
            "SELECT r.name FROM roles r JOIN users u ON r.id = u.role_id WHERE u.id = ?",
            [$id]
        );
        
        // Check permissions
        if (($currentRole === 'Event Admin' || $currentRole === 'Event Organizer') && 
            in_array($userRole['name'], ['Super Admin', 'Event Admin', 'Event Organizer'])) {
            $this->json(['success' => false, 'message' => 'You cannot modify admin users'], 403);
            return;
        }
        
        $newStatus = $user['is_active'] ? 0 : 1;
        $this->db->query("UPDATE users SET is_active = ? WHERE id = ?", [$newStatus, $id]);
        
        $this->logAudit('TOGGLE_USER_ACTIVE', 'users', $id, ['is_active' => $user['is_active']], ['is_active' => $newStatus]);
        
        $this->json(['success' => true, 'message' => 'User status updated', 'is_active' => $newStatus]);
    }
}
