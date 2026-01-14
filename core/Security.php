<?php
// core/Security.php

class Security {

    /**
     * Generates a random CSRF token and stores it in the session.
     */
    public static function generateCsrfToken() {
        $token = bin2hex(random_bytes(32)); // Generate a secure random token
        $_SESSION[CSRF_TOKEN_NAME] = $token;
        return $token;
    }

    /**
     * Validates the submitted CSRF token against the one stored in the session.
     * @param string $submittedToken
     * @return bool
     */
    public static function validateCsrfToken($submittedToken) {
        $storedToken = $_SESSION[CSRF_TOKEN_NAME] ?? null; // Use null coalescing operator
        
        // Check if stored token exists and submitted token matches
        $isValid = $storedToken !== null && hash_equals($storedToken, $submittedToken);
        
        // Always clear the token after validation (whether valid or not) to prevent replay attacks
        unset($_SESSION[CSRF_TOKEN_NAME]);
        
        return $isValid;
    }

    /**
     * Hashes a password using PHP's password_hash function.
     * @param string $password
     * @return string
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Verifies a password against its hash.
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    /**
     * Sanitizes user input to prevent XSS.
     * @param string $input
     * @return string
     */
    public static function sanitizeInput($input) {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Checks if the user is logged in based on session variable.
     * @return bool
     */
    public static function isLoggedIn() {
        // Ensure session is active before checking
        // This might be implicitly handled by accessing $_SESSION, but good practice
        if (session_status() !== PHP_SESSION_ACTIVE) {
            SessionManager::startSecureSession();
        }
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Redirects the user to a specified URL.
     * @param string $url
     */
    public static function redirect($url) {
        header("Location: $url");
        exit();
    }
}