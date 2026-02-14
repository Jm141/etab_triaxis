<?php
/**
 * Authentication Controller
 */

class AuthController extends Controller {
    
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            
            $errors = $this->validateInput($_POST, [
                'username' => 'required',
                'password' => 'required'
            ]);
            
            if (!empty($errors)) {
                $this->view('auth/login', ['errors' => $errors]);
                return;
            }
            
            // Find user
            $user = $this->db->fetchOne(
                "SELECT u.*, r.name as role_name, r.permissions 
                 FROM users u 
                 JOIN roles r ON u.role_id = r.id 
                 WHERE u.username = ? AND u.is_active = 1",
                [$username]
            );
            
            // Debug: Check if user exists (remove in production)
            if (!$user) {
                $this->view('auth/login', ['error' => 'User not found or account is inactive']);
                return;
            }
            
            if (password_verify($password, $user['password_hash'])) {
                // Set session
                Session::set('user_id', $user['id']);
                Session::set('username', $user['username']);
                Session::set('full_name', $user['full_name']);
                Session::set('role_id', $user['role_id']);
                Session::set('role_name', $user['role_name']);
                Session::set('permissions', $user['permissions']);
                
                // Update last login
                $this->db->query(
                    "UPDATE users SET last_login = NOW() WHERE id = ?",
                    [$user['id']]
                );
                
                // Log successful login
                $this->db->query(
                    "INSERT INTO login_logs (user_id, username, ip_address, user_agent, status)
                     VALUES (?, ?, ?, ?, 'Success')",
                    [
                        $user['id'],
                        $username,
                        $_SERVER['REMOTE_ADDR'] ?? null,
                        $_SERVER['HTTP_USER_AGENT'] ?? null
                    ]
                );
                
                $this->redirect('/tabulation/dashboard');
            } else {
                // Log failed login
                $this->db->query(
                    "INSERT INTO login_logs (username, ip_address, user_agent, status, failure_reason)
                     VALUES (?, ?, ?, 'Failed', 'Invalid credentials')",
                    [
                        $username,
                        $_SERVER['REMOTE_ADDR'] ?? null,
                        $_SERVER['HTTP_USER_AGENT'] ?? null
                    ]
                );
                
                $this->view('auth/login', ['error' => 'Invalid username or password']);
            }
        } else {
            // Show login form
            if (Session::has('user_id')) {
                $this->redirect('/tabulation/dashboard');
            }
            $this->view('auth/login');
        }
    }
    
    public function logout() {
        Session::destroy();
        $this->redirect('/tabulation/login');
    }
}

