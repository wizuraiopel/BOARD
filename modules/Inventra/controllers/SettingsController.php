<?php
// controllers/SettingsController.php
/**
 * Settings Controller - Manages inventory items, categories, and batches
 * Handles both view rendering and AJAX requests for settings management
 */

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../../../core/Security.php';
require_once __DIR__ . '/../models/SettingsDashboardModel.php';
require_once __DIR__ . '/../models/User.php';

class SettingsController extends BaseController {

    private $settingsModel;

    public function __construct() {
        $this->settingsModel = new SettingsDashboardModel();
    }

    /**
     * Display settings dashboard
     */
    public function index() {
        // Permission check: Only admin users
        if (!Security::isLoggedIn()) {
            Security::redirect(BASE_URL . '/index.php?action=login');
        }

        $currentUserId = $_SESSION['user_id'];
        $currentUserModel = new User();
        $currentUser = $currentUserModel->findById($currentUserId);

        if (!$currentUser || $currentUser['user_type'] !== 'admin') {
            Security::redirect(BASE_URL . '/index.php?action=dashboard');
        }

        // Fetch initial data
        $data = [
            'items' => $this->settingsModel->getAllItems(),
            'categories' => $this->settingsModel->getAllCategories(),
            'batches' => $this->settingsModel->getAllBatches(),
            'csrf_token' => Security::generateCSRFToken(),
        ];

        $viewContent = $this->render('settings_dashboard', $data);
        $this->loadLayout($viewContent, 'Settings Dashboard');
    }

    /**
     * Handle AJAX requests for settings operations
     */
    public function handleAjax() {
        // Verify CSRF token
        if (!isset($_POST['_wpnonce']) || !Security::validateCsrfToken($_POST['_wpnonce'])) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Security check failed (CSRF).']);
            exit();
        }

        // Verify user is admin
        if (!Security::isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'User not authenticated.']);
            exit();
        }

        $currentUserId = $_SESSION['user_id'];
        $userModel = new User();
        $currentUser = $userModel->findById($currentUserId);

        if (!$currentUser || $currentUser['user_type'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Insufficient permissions.']);
            exit();
        }

        $ajaxAction = sanitize($_POST['ajax_action'] ?? '');
        $response = ['success' => false, 'message' => 'Invalid AJAX action.'];

        try {
            switch ($ajaxAction) {
                case 'add_item':
                    $response = $this->handleAddItem();
                    break;
                case 'edit_item':
                    $response = $this->handleEditItem();
                    break;
                case 'delete_item':
                    $response = $this->handleDeleteItem();
                    break;
                case 'add_category':
                    $response = $this->handleAddCategory();
                    break;
                case 'edit_category':
                    $response = $this->handleEditCategory();
                    break;
                case 'delete_category':
                    $response = $this->handleDeleteCategory();
                    break;
                case 'add_batch':
                    $response = $this->handleAddBatch();
                    break;
                case 'edit_batch':
                    $response = $this->handleEditBatch();
                    break;
                case 'delete_batch':
                    $response = $this->handleDeleteBatch();
                    break;
                default:
                    $response = ['success' => false, 'message' => 'Unknown AJAX action.'];
            }
        } catch (Exception $e) {
            $response = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    /**
     * Add inventory item
     */
    private function handleAddItem() {
        $name = sanitize($_POST['item_name'] ?? '');

        if (!$name) {
            return ['success' => false, 'message' => 'Item name is required.'];
        }

        $result = $this->settingsModel->addItem([
            'name' => $name
        ]);

        if ($result) {
            return ['success' => true, 'message' => 'Item added successfully.'];
        }
        return ['success' => false, 'message' => 'Failed to add item.'];
    }

    /**
     * Edit inventory item
     */
    private function handleEditItem() {
        $id = intval($_POST['item_id'] ?? 0);
        $name = sanitize($_POST['item_name'] ?? '');

        if (!$id || !$name) {
            return ['success' => false, 'message' => 'Item ID and name are required.'];
        }

        $result = $this->settingsModel->updateItem($id, [
            'name' => $name
        ]);

        if ($result) {
            return ['success' => true, 'message' => 'Item updated successfully.'];
        }
        return ['success' => false, 'message' => 'Failed to update item.'];
    }

    /**
     * Delete inventory item
     */
    private function handleDeleteItem() {
        $id = intval($_POST['item_id'] ?? 0);

        if (!$id) {
            return ['success' => false, 'message' => 'Item ID is required.'];
        }

        $result = $this->settingsModel->deleteItem($id);

        if ($result) {
            return ['success' => true, 'message' => 'Item deleted successfully.'];
        }
        return ['success' => false, 'message' => 'Failed to delete item.'];
    }

    /**
     * Add category
     */
    private function handleAddCategory() {
        $name = sanitize($_POST['category_name'] ?? '');
        $key = sanitize_key($_POST['category_key'] ?? '');
        $icon = sanitize($_POST['category_icon'] ?? '📦');

        if (!$name || !$key) {
            return ['success' => false, 'message' => 'Category name and key are required.'];
        }

        $result = $this->settingsModel->addCategory([
            'key' => $key,
            'name' => $name,
            'icon' => $icon
        ]);

        if ($result) {
            return ['success' => true, 'message' => 'Category added successfully.'];
        }
        return ['success' => false, 'message' => 'Failed to add category.'];
    }

    /**
     * Edit category
     */
    private function handleEditCategory() {
        $key = sanitize_key($_POST['category_key'] ?? '');
        $name = sanitize($_POST['category_name'] ?? '');
        $icon = sanitize($_POST['category_icon'] ?? '📦');

        if (!$key || !$name) {
            return ['success' => false, 'message' => 'Category key and name are required.'];
        }

        $result = $this->settingsModel->updateCategory($key, [
            'name' => $name,
            'icon' => $icon
        ]);

        if ($result) {
            return ['success' => true, 'message' => 'Category updated successfully.'];
        }
        return ['success' => false, 'message' => 'Failed to update category.'];
    }

    /**
     * Delete category
     */
    private function handleDeleteCategory() {
        $key = sanitize_key($_POST['category_key'] ?? '');

        if (!$key) {
            return ['success' => false, 'message' => 'Category key is required.'];
        }

        $result = $this->settingsModel->deleteCategory($key);

        if ($result) {
            return ['success' => true, 'message' => 'Category deleted successfully.'];
        }
        return ['success' => false, 'message' => 'Failed to delete category.'];
    }

    /**
     * Add batch
     */
    private function handleAddBatch() {
        $name = sanitize($_POST['batch_name'] ?? '');
        $batchMmYyyy = sanitize($_POST['batch_mm_yyyy'] ?? '');
        $status = sanitize($_POST['batch_status'] ?? 'planning');
        $supplier = sanitize($_POST['batch_supplier'] ?? '');
        $notes = sanitize($_POST['batch_notes'] ?? '');

        $allowedStatuses = ['planning', 'distributing', 'completed', 'cancelled'];
        if (!in_array($status, $allowedStatuses)) {
            return ['success' => false, 'message' => 'Invalid batch status.'];
        }

        if (!$name) {
            return ['success' => false, 'message' => 'Batch name is required.'];
        }

        $result = $this->settingsModel->addBatch([
            'name' => $name,
            'batch_mm_yyyy' => $batchMmYyyy,
            'status' => $status,
            'supplier' => $supplier,
            'notes' => $notes
        ]);

        if ($result) {
            return ['success' => true, 'message' => 'Batch added successfully.'];
        }
        return ['success' => false, 'message' => 'Failed to add batch.'];
    }

    /**
     * Edit batch
     */
    private function handleEditBatch() {
        $id = intval($_POST['batch_id'] ?? 0);
        $name = sanitize($_POST['batch_name'] ?? '');
        $batchMmYyyy = sanitize($_POST['batch_mm_yyyy'] ?? '');
        $status = sanitize($_POST['batch_status'] ?? 'planning');
        $supplier = sanitize($_POST['batch_supplier'] ?? '');
        $notes = sanitize($_POST['batch_notes'] ?? '');

        $allowedStatuses = ['planning', 'distributing', 'completed', 'cancelled'];
        if (!in_array($status, $allowedStatuses)) {
            return ['success' => false, 'message' => 'Invalid batch status.'];
        }

        if (!$id || !$name) {
            return ['success' => false, 'message' => 'Batch ID and name are required.'];
        }

        $result = $this->settingsModel->updateBatch($id, [
            'name' => $name,
            'batch_mm_yyyy' => $batchMmYyyy,
            'status' => $status,
            'supplier' => $supplier,
            'notes' => $notes
        ]);

        if ($result) {
            return ['success' => true, 'message' => 'Batch updated successfully.'];
        }
        return ['success' => false, 'message' => 'Failed to update batch.'];
    }

    /**
     * Delete batch
     */
    private function handleDeleteBatch() {
        $id = intval($_POST['batch_id'] ?? 0);

        if (!$id) {
            return ['success' => false, 'message' => 'Batch ID is required.'];
        }

        $result = $this->settingsModel->deleteBatch($id);

        if ($result) {
            return ['success' => true, 'message' => 'Batch deleted successfully.'];
        }
        return ['success' => false, 'message' => 'Failed to delete batch.'];
    }
}

/**
 * Utility function: Sanitize input
 */
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Utility function: Sanitize key
 */
function sanitize_key($input) {
    return preg_replace('/[^a-z0-9_-]/i', '', trim($input));
}
