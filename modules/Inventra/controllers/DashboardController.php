<?php
// controllers/DashboardController.php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../../../core/Security.php';
require_once __DIR__ . '/../models/User.php';

class DashboardController extends BaseController {

    public function index() {
        if (!Security::isLoggedIn()) {
            Security::redirect(BASE_URL . '/index.php?action=login'); // Use action
        }

        $userId = $_SESSION['user_id'];
        $userModel = new User();
        $user = $userModel->findById($userId);

        if (!$user) {
             // If DB lookup fails, try to use session-stored type (set during login)
             $effectiveType = $_SESSION['user_type'] ?? null;
             if ($effectiveType === 'admin') {
                 Security::redirect(BASE_URL . '/index.php?action=admin_dashboard');
             } elseif ($effectiveType === 'branch') {
                 Security::redirect(BASE_URL . '/index.php?action=branch_dashboard_modern');
             }
             SessionManager::destroySession();
             Security::redirect(BASE_URL . '/index.php?action=login'); // Use action
        }

        // Determine effective user type: prefer DB field, but normalize variants, fallback to session or role slugs
        $effectiveType = $user['user_type'] ?? null;
        if (!empty($effectiveType)) {
            $ut = $effectiveType;
            if (stripos($ut, 'branch') !== false) $effectiveType = 'branch';
            elseif (stripos($ut, 'admin') !== false) $effectiveType = 'admin';
            elseif (stripos($ut, 'operation') !== false || stripos($ut, 'op') !== false) $effectiveType = 'staff';
        }
        if (empty($effectiveType)) {
            $effectiveType = $_SESSION['user_type'] ?? null;
        }
        if (empty($effectiveType)) {
            // Attempt to infer from role slugs (robust mapping)
            try {
                $roleSlugs = $userModel->getRoleSlugs($userId);
                if (in_array('branch', $roleSlugs) || in_array('um_branch', $roleSlugs)) {
                    $effectiveType = 'branch';
                } elseif (in_array('superadmin', $roleSlugs) || in_array('sysadmin', $roleSlugs) || in_array('administrator', $roleSlugs)) {
                    $effectiveType = 'admin';
                } elseif (in_array('operation', $roleSlugs) || in_array('um_operation', $roleSlugs)) {
                    $effectiveType = 'staff';
                }
            } catch (Exception $e) {
                // ignore
            }
        }

        // Redirect to appropriate dashboard based on effective user type
        if ($effectiveType === 'admin' || $effectiveType === 'staff') {
            Security::redirect(BASE_URL . '/index.php?action=admin_dashboard');
        } elseif ($effectiveType === 'branch') {
            Security::redirect(BASE_URL . '/index.php?action=branch_dashboard_modern');
        } else {
            // Unknown user type, redirect to login
            SessionManager::destroySession();
            Security::redirect(BASE_URL . '/index.php?action=login');
        }
    }
}