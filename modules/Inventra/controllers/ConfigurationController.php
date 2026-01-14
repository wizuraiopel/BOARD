<?php
// controllers/ConfigurationController.php
/**
 * Configuration Controller - Manage Roles & Permissions (basic RBAC UI + AJAX)
 */

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../../../core/Security.php';
require_once __DIR__ . '/../models/Role.php';
require_once __DIR__ . '/../models/Permission.php';
require_once __DIR__ . '/../models/User.php';

class ConfigurationController extends BaseController {
    private $roleModel;
    private $permModel;

    public function __construct() {
        $this->roleModel = new Role();
        $this->permModel = new Permission();
    }

    public function index() {
        if (!Security::isLoggedIn()) {
            Security::redirect(BASE_URL . '/index.php?action=login');
        }

        $currentUserId = $_SESSION['user_id'];
        $currentUserModel = new User();
        $currentUser = $currentUserModel->findById($currentUserId);

        // Access control: require specific roles (only 'superadmin' or 'sysadmin') to access Configuration
        if (!$currentUser) {
            Security::redirect(BASE_URL . '/index.php?action=login');
        }

        $roleSlugs = $currentUserModel->getRoleSlugs($currentUserId);
        $allowedRoles = ['superadmin', 'sysadmin'];
        // Allow explicit admin user_type as a fallback for backward compatibility
        $hasConfigAccess = ($currentUser['user_type'] === 'admin') || (bool) array_intersect($roleSlugs, $allowedRoles);

        if (!$hasConfigAccess) {
            Security::redirect(BASE_URL . '/index.php?action=dashboard');
        }

        $data = [
            'roles' => $this->roleModel->all(),
            'modules' => $this->permModel->getModulesAndFeatures(),
            'csrf_token' => Security::generateCsrfToken(),
        ];

        $viewContent = $this->render('configuration_dashboard', $data);
        $this->loadLayout($viewContent, 'Configuration');
    }

    /**
     * Manage users page
     */
    public function manageUsers() {
        if (!Security::isLoggedIn()) {
            Security::redirect(BASE_URL . '/index.php?action=login');
        }

        $currentUserId = $_SESSION['user_id'];
        $currentUserModel = new User();
        $currentUser = $currentUserModel->findById($currentUserId);

        // Only superadmin/sysadmin may access
        $roleSlugs = $currentUserModel->getRoleSlugs($currentUserId);
        $allowedRoles = ['superadmin', 'sysadmin'];
        $hasConfigAccess = ($currentUser['user_type'] === 'admin') || (bool) array_intersect($roleSlugs, $allowedRoles);
        if (!array_intersect($roleSlugs, $allowedRoles)) {
            if (!$hasConfigAccess) {
                Security::redirect(BASE_URL . '/index.php?action=dashboard');
            }
        }

        $data = [
            'csrf_token' => Security::generateCsrfToken(),
        ];
        $viewContent = $this->render('manage_users', $data);
        $this->loadLayout($viewContent, 'Manage Users');
    }

    public function handleAjax() {
        // Verify CSRF token
        if (!isset($_POST['_wpnonce']) || !Security::validateCsrfToken($_POST['_wpnonce'])) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Security check failed (CSRF).']);
            exit();
        }

        if (!Security::isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'User not authenticated.']);
            exit();
        }

        $currentUserId = $_SESSION['user_id'];
        $userModel = new User();
        $currentUser = $userModel->findById($currentUserId);

        // Require specific roles (superadmin/sysadmin) for any configuration AJAX action
        $roleSlugs = $userModel->getRoleSlugs($currentUserId);
        $allowedRoles = ['superadmin', 'sysadmin'];
        // Allow admin user_type as fallback
        $hasConfigAccess = ($currentUser['user_type'] === 'admin') || (bool) array_intersect($roleSlugs, $allowedRoles);
        if (!$hasConfigAccess) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Insufficient permissions.']);
            exit();
        }

        $ajaxAction = sanitize($_POST['ajax_action'] ?? '');
        $response = ['success' => false, 'message' => 'Invalid AJAX action.'];

        switch ($ajaxAction) {
            case 'list_roles':
                $response = ['success' => true, 'roles' => $this->roleModel->all()];
                break;
            case 'list_users':
                $page = intval($_POST['page'] ?? 1);
                $per_page = intval($_POST['per_page'] ?? 20);
                $q = sanitize($_POST['q'] ?? '');
                $users = (new User())->listAllWithRoles($page, $per_page, $q);
                $roles = $this->roleModel->all();
                $response = ['success' => true, 'users' => $users['data'], 'roles' => $roles, 'total' => $users['total'], 'page' => $users['page'], 'per_page' => $users['per_page']];
                break;
            case 'list_audit':
                require_once __DIR__ . '/../models/Audit.php';
                $audit = new Audit();
                $response = ['success' => true, 'logs' => $audit->listRecent(50)];
                break;
            case 'update_user_roles':
                $user_id = intval($_POST['user_id'] ?? 0);
                $roles = $_POST['roles'] ?? [];
                if (!$user_id) {
                    $response = ['success' => false, 'message' => 'User id required.'];
                    break;
                }
                // fetch previous roles
                $userModel = new User();
                $prev = $userModel->getRoleSlugs($user_id);
                $ok = $this->roleModel->setRolesForUser($user_id, $roles);
                if ($ok) {
                    // log the change
                    require_once __DIR__ . '/../models/Audit.php';
                    $audit = new Audit();
                    $details = json_encode(['previous' => $prev, 'updated' => array_values($roles)]);
                    $audit->logChange($user_id, $currentUserId, 'update_roles', $details);
                }
                $response = ['success' => (bool) $ok];
                break;
            case 'create_role':
                // require permission to create roles
                if (!$this->permModel->userHasPermission($currentUserId, 'configuration', 'roles', 'create') && $currentUser['user_type'] !== 'admin') {
                    $response = ['success' => false, 'message' => 'Insufficient permissions to create roles.'];
                    break;
                }
                $name = sanitize($_POST['name'] ?? '');
                $slug = sanitize_key($_POST['slug'] ?? '');
                $desc = sanitize($_POST['description'] ?? '');
                if (!$name || !$slug) {
                    $response = ['success' => false, 'message' => 'Role name and slug are required.'];
                    break;
                }
                $ok = $this->roleModel->create($name, $slug, $desc);
                $response = ['success' => (bool) $ok];
                break;
            case 'get_permissions':
                $role_id = intval($_POST['role_id'] ?? 0);
                // require read permission
                if (!$this->permModel->userHasPermission($currentUserId, 'configuration', 'permissions', 'read') && $currentUser['user_type'] !== 'admin') {
                    $response = ['success' => false, 'message' => 'Insufficient permissions to view permissions.'];
                    break;
                }
                $response = ['success' => true, 'permissions' => $this->permModel->getByRole($role_id)];
                break;
            case 'save_permissions':
                $role_id = intval($_POST['role_id'] ?? 0);
                $permissions = $_POST['permissions'] ?? [];
                // require update permission on permissions
                if (!$this->permModel->userHasPermission($currentUserId, 'configuration', 'permissions', 'update') && $currentUser['user_type'] !== 'admin') {
                    $response = ['success' => false, 'message' => 'Insufficient permissions to save permissions.'];
                    break;
                }
                $ok = $this->permModel->saveForRole($role_id, $permissions);
                $response = ['success' => (bool) $ok];
                break;
            case 'add_user':
                // require create permission on users
                if (!$this->permModel->userHasPermission($currentUserId, 'configuration', 'users', 'create') && $currentUser['user_type'] !== 'admin') {
                    $response = ['success' => false, 'message' => 'Insufficient permissions to add users.'];
                    break;
                }
                $username = sanitize($_POST['username'] ?? '');
                $email = sanitize($_POST['email'] ?? '');
                $password = $_POST['password'] ?? '';
                $roles = $_POST['roles'] ?? [];

                if (!$username || !$password) {
                    $response = ['success' => false, 'message' => 'Username and password are required.'];
                    break;
                }

                $hash = password_hash($password, PASSWORD_BCRYPT);
                $newUserModel = new User();
                $userId = $newUserModel->create($username, $email, $hash);
                if (!$userId) {
                    $response = ['success' => false, 'message' => 'Failed to create user.'];
                    break;
                }

                // assign roles if provided (roles may be role ids)
                foreach ($roles as $r) {
                    $rid = intval($r);
                    if ($rid > 0) {
                        $this->roleModel->assignToUser($rid, $userId);
                    }
                }

                // log creation
                require_once __DIR__ . '/../models/Audit.php';
                $audit = new Audit();
                $details = json_encode(['created' => ['username' => $username, 'email' => $email, 'roles' => $roles]]);
                $audit->logChange($userId, $currentUserId, 'create_user', $details);

                $response = ['success' => true, 'user_id' => $userId];
                break;
            default:
                $response = ['success' => false, 'message' => 'Unknown AJAX action.'];
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }
}
