<?php
// core/SessionManager.php

class SessionManager {

    public static function startSecureSession() {
        // Check if session is already active
        if (session_status() === PHP_SESSION_ACTIVE) {
            // If it's active, we can't set params. Log a warning or handle as needed.
            // For this scenario, if it's already active, just return.
            // This implies session was started elsewhere before this function was called.
            return; 
        }

        // Set session cookie parameters BEFORE calling session_start()
        $params = session_get_cookie_params();
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path' => $params["path"],
            'domain' => $params["domain"],
            'secure' => true, // HTTPS only
            'httponly' => true, // Prevents JS access
            'samesite' => 'Strict' // Add SameSite attribute for extra CSRF protection
        ]);

        // Now start the session
        session_start();

        // Store IP and User Agent to help detect session hijacking
        if (!isset($_SESSION['initiated'])) {
            $_SESSION['initiated'] = true;
            $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        } else {
            // Validate IP and User Agent (Basic check, can be bypassed easily)
            if ($_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR'] ||
                $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
                    // Consider logging this event and invalidating the session
                    session_destroy();
                    // Redirect to login
                    header("Location: " . BASE_URL . "/index.php?action=login");
                    exit();
            }
        }
    }

    public static function destroySession() {
        $_SESSION = array(); // Unset all session variables
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                true, // Secure
                true  // HttpOnly
            );
        }
        session_destroy();
    }
}