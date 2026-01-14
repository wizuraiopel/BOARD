<?php
// core/SessionManager.php

class SessionManager {

    public static function startSecureSession() {
        // Regenerate session ID to prevent session fixation
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        } else {
            session_regenerate_id(true);
        }

        // Set session cookie parameters for security
        $params = session_get_cookie_params();
        session_set_cookie_params(
            SESSION_LIFETIME,
            $params["path"],
            $params["domain"],
            true, // Secure (HTTPS only)
            true  // HttpOnly (Prevents JS access)
        );

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
                    Security::redirect(BASE_URL . '/login'); // Redirect to login if mismatch
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