<?php
// controllers/AuthController.php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../../../core/Security.php';
require_once __DIR__ . '/../../../core/SessionManager.php';

class AuthController extends BaseController {

    public function showLoginForm() {
        // Ensure session is started so CSRF token can be stored
        SessionManager::startSecureSession();
        $csrfToken = Security::generateCsrfToken();
        $viewContent = $this->render('login', ['csrfToken' => $csrfToken]);
        $this->loadLayout($viewContent, 'Login');
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Security::redirect(BASE_URL . '/index.php?action=login'); // Use action
        }
        // Ensure session is started before validating CSRF and setting session vars
        SessionManager::startSecureSession();

        $username = Security::sanitizeInput($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $submittedToken = $_POST[CSRF_TOKEN_NAME] ?? '';

        if (!Security::validateCsrfToken($submittedToken)) {
            $_SESSION['error'] = "Invalid request. Please try again.";
            Security::redirect(BASE_URL . '/index.php?action=login'); // Use action
        }

        if (empty($username) || empty($password)) {
            $_SESSION['error'] = "Username and password are required.";
            Security::redirect(BASE_URL . '/index.php?action=login'); // Use action
        }

        $userModel = new User();
        $user = $userModel->findByUsername($username);

        if ($user && Security::verifyPassword($password, $user['password_hash'])) {
            SessionManager::startSecureSession();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            // Persist normalized user_type in session for easier checks in views/layout
            if (isset($user['user_type']) && !empty($user['user_type'])) {
                $ut = $user['user_type'];
                // Normalize common DB variants to canonical types used in controllers
                if (stripos($ut, 'branch') !== false) {
                    $_SESSION['user_type'] = 'branch';
                } elseif (stripos($ut, 'admin') !== false) {
                    $_SESSION['user_type'] = 'admin';
                } elseif (stripos($ut, 'operation') !== false || stripos($ut, 'op') !== false) {
                    $_SESSION['user_type'] = 'staff';
                } else {
                    $_SESSION['user_type'] = $ut; // fallback
                }
            } else {
                // Derive user_type from role slugs when user_type DB field is missing or inconsistent
                try {
                    $roleSlugs = $userModel->getRoleSlugs($user['id']);
                    if (in_array('branch', $roleSlugs) || in_array('um_branch', $roleSlugs)) {
                        $_SESSION['user_type'] = 'branch';
                    } elseif (in_array('superadmin', $roleSlugs) || in_array('sysadmin', $roleSlugs) || in_array('administrator', $roleSlugs)) {
                        $_SESSION['user_type'] = 'admin';
                    } elseif (in_array('operation', $roleSlugs) || in_array('um_operation', $roleSlugs)) {
                        $_SESSION['user_type'] = 'staff';
                    }
                } catch (Exception $e) {
                    // ignore and proceed without setting
                }
            }
            
            session_write_close(); // Ensure session is written before redirect
            
            Security::redirect(BASE_URL . '/index.php?action=dashboard'); // Use action
        } else {
            // Fallback for legacy MD5 password storage: if stored hash is 32 hex chars, compare md5
            $legacyMatch = false;
            if ($user && isset($user['password_hash']) && preg_match('/^[a-f0-9]{32}$/i', $user['password_hash'])) {
                if (md5($password) === $user['password_hash']) {
                    $legacyMatch = true;
                    // Re-hash with secure algorithm and update user record
                    $newHash = Security::hashPassword($password);
                    $userModel->updatePassword($user['id'], $newHash);
                    // proceed to set session
                    SessionManager::startSecureSession();
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    if (isset($user['user_type']) && !empty($user['user_type'])) {
                        $ut = $user['user_type'];
                        if (stripos($ut, 'branch') !== false) {
                            $_SESSION['user_type'] = 'branch';
                        } elseif (stripos($ut, 'admin') !== false) {
                            $_SESSION['user_type'] = 'admin';
                        } elseif (stripos($ut, 'operation') !== false || stripos($ut, 'op') !== false) {
                            $_SESSION['user_type'] = 'staff';
                        } else {
                            $_SESSION['user_type'] = $ut;
                        }
                    } else {
                        try {
                            $roleSlugs = $userModel->getRoleSlugs($user['id']);
                            if (in_array('branch', $roleSlugs) || in_array('um_branch', $roleSlugs)) {
                                $_SESSION['user_type'] = 'branch';
                            } elseif (in_array('superadmin', $roleSlugs) || in_array('sysadmin', $roleSlugs) || in_array('administrator', $roleSlugs)) {
                                $_SESSION['user_type'] = 'admin';
                            } elseif (in_array('operation', $roleSlugs) || in_array('um_operation', $roleSlugs)) {
                                $_SESSION['user_type'] = 'staff';
                            }
                        } catch (Exception $e) {}
                    }
                    if (isset($user['user_type'])) {
                        $_SESSION['user_type'] = $user['user_type'];
                    }

                    session_write_close();
                    Security::redirect(BASE_URL . '/index.php?action=dashboard');
                }
            }

            if (!$legacyMatch) {
                $_SESSION['error'] = "Invalid username or password.";
                Security::redirect(BASE_URL . '/index.php?action=login'); // Use action
            }
        }
    }

    public function logout() {
        SessionManager::destroySession();
        Security::redirect(BASE_URL . '/index.php?action=login'); // Use action
    }
}