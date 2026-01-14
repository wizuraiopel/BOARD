<?php
// controllers/AdminController.php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../../../core/Security.php';
require_once __DIR__ . '/../models/AdminDashboardModel.php';
require_once __DIR__ . '/../models/User.php'; // Assuming you have a User model

class AdminController extends BaseController {

    public function index() {
        // Permission check: Only admin users
        if (!Security::isLoggedIn()) {
            Security::redirect(BASE_URL . '/index.php?action=login');
        }

        $currentUserId = $_SESSION['user_id'];
        $currentUserModel = new User();
        $currentUser = $currentUserModel->findById($currentUserId);

        if (!$currentUser || $currentUser['user_type'] !== 'admin') {
            Security::redirect(BASE_URL . '/index.php?action=dashboard'); // Redirect non-admins
        }

        $model = new AdminDashboardModel();

        $data = [
            'overview_counts' => $model->getOverviewCounts(),
            'pending_allocations' => $model->getPendingAllocations(),
            'resolved_allocations' => $model->getResolvedAllocations(),
            'pending_adjustments' => $model->getPendingAdjustments(),
            'adjustment_history' => $model->getAdjustmentHistory(),
            'pending_disputes' => $model->getPendingDisputes(),
            'dispute_history' => $model->getDisputeHistory(),
            'pending_branch_transfers' => $model->getPendingBranchTransfers(),
            'transfer_history' => $model->getTransferHistory(),
            'received' => $model->getReceivedHistory(),
            'branches' => $model->getBranches(),
            'items' => $model->getItems(),
            'batches' => $model->getBatches(),
            'inventory_pivot' => $model->getFullInventoryPivot(),
            'received_history_count' => count($model->getReceivedHistory()), // Calculate count for display
        ];

        // Prefer modern admin dashboard by default
        Security::redirect(BASE_URL . '/index.php?action=admin_dashboard_modern');
    }

    /**
     * Modern admin dashboard (KPIs, charts, tables)
     */
    public function modernDashboard() {
        if (!Security::isLoggedIn()) {
            Security::redirect(BASE_URL . '/index.php?action=login');
        }

        $currentUserId = $_SESSION['user_id'];
        $userModel = new User();
        $roleSlugs = $userModel->getRoleSlugs($currentUserId);
        $isAdmin = ($userModel->findById($currentUserId)['user_type'] === 'admin') || in_array('admin', $roleSlugs) || in_array('um_admin', $roleSlugs);
        if (!$isAdmin) {
            Security::redirect(BASE_URL . '/index.php?action=dashboard');
        }

        $model = new AdminDashboardModel();
        $data = $model->getDashboardMetrics(31);
        // Add pending lists so the modern UI can render approve/decline actions
        $data['pending_allocations'] = $model->getPendingAllocations();
        $data['pending_adjustments'] = $model->getPendingAdjustments();
        $data['pending_disputes'] = $model->getPendingDisputes();
        $data['pending_transfers'] = $model->getPendingBranchTransfers();
        // Reference lists for the allocation modal
        $data['items'] = $model->getItems();
        $data['branches'] = $model->getBranches();
        $data['batches'] = $model->getBatches();
        $viewContent = $this->render('admin_modern_dashboard', $data);
        $this->loadLayout($viewContent, 'Admin Dashboard (Modern)');
    }

    /**
     * Admin dashboard stats endpoint (JSON)
     */
    public function dashboardStats() {
        if (!Security::isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Not authenticated.']);
            exit();
        }

        $currentUserId = $_SESSION['user_id'];
        $userModel = new User();
        $roleSlugs = $userModel->getRoleSlugs($currentUserId);
        $isAdmin = ($userModel->findById($currentUserId)['user_type'] === 'admin') || in_array('admin', $roleSlugs) || in_array('um_admin', $roleSlugs);
        if (!$isAdmin) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Insufficient permissions.']);
            exit();
        }

        $days = intval($_GET['days'] ?? 31);
        $days = max(1, min(365, $days));

        $model = new AdminDashboardModel();
        $metrics = $model->getDashboardMetrics($days);
        // Add pending lists for dynamic UI updates
        $metrics['pending_allocations'] = $model->getPendingAllocations();
        $metrics['pending_adjustments'] = $model->getPendingAdjustments();
        $metrics['pending_disputes'] = $model->getPendingDisputes();
        $metrics['pending_transfers'] = $model->getPendingBranchTransfers();

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'metrics' => $metrics]);
        exit();
    }

    // Admin allocations page
    public function allocations() {
        if (!Security::isLoggedIn()) {
            Security::redirect(BASE_URL . '/index.php?action=login');
        }
        $currentUserId = $_SESSION['user_id'];
        $currentUserModel = new User();
        $currentUser = $currentUserModel->findById($currentUserId);
        if (!$currentUser || $currentUser['user_type'] !== 'admin') {
            Security::redirect(BASE_URL . '/index.php?action=dashboard');
        }
        $model = new AdminDashboardModel();
        $data = [
            'pending_allocations' => $model->getPendingAllocations(),
            'resolved_allocations' => $model->getResolvedAllocations(),
            'branches' => $model->getBranches(),
            'items' => $model->getItems(),
            'batches' => $model->getBatches(),
        ];
        $viewContent = $this->render('admin_allocations', $data);
        $this->loadLayout($viewContent, 'Allocations');
    }

    // Admin adjustments page
    public function adjustments() {
        if (!Security::isLoggedIn()) {
            Security::redirect(BASE_URL . '/index.php?action=login');
        }
        $currentUserId = $_SESSION['user_id'];
        $currentUserModel = new User();
        $currentUser = $currentUserModel->findById($currentUserId);
        if (!$currentUser || $currentUser['user_type'] !== 'admin') {
            Security::redirect(BASE_URL . '/index.php?action=dashboard');
        }
        $model = new AdminDashboardModel();
        $data = [
            'pending_adjustments' => $model->getPendingAdjustments(),
            'adjustment_history' => $model->getAdjustmentHistory(),
            'branches' => $model->getBranches()
        ];
        $viewContent = $this->render('admin_adjustments', $data);
        $this->loadLayout($viewContent, 'Adjustments');
    }

    // Admin disputes page
    public function disputes() {
        if (!Security::isLoggedIn()) {
            Security::redirect(BASE_URL . '/index.php?action=login');
        }
        $currentUserId = $_SESSION['user_id'];
        $currentUserModel = new User();
        $currentUser = $currentUserModel->findById($currentUserId);
        if (!$currentUser || $currentUser['user_type'] !== 'admin') {
            Security::redirect(BASE_URL . '/index.php?action=dashboard');
        }
        $model = new AdminDashboardModel();
        $data = [
            'pending_disputes' => $model->getPendingDisputes(),
            'dispute_history' => $model->getDisputeHistory(),
            'branches' => $model->getBranches()
        ];
        $viewContent = $this->render('admin_disputes', $data);
        $this->loadLayout($viewContent, 'Disputes');
    }

    // Admin transfers page
    public function transfers() {
        if (!Security::isLoggedIn()) {
            Security::redirect(BASE_URL . '/index.php?action=login');
        }
        $currentUserId = $_SESSION['user_id'];
        $currentUserModel = new User();
        $currentUser = $currentUserModel->findById($currentUserId);
        if (!$currentUser || $currentUser['user_type'] !== 'admin') {
            Security::redirect(BASE_URL . '/index.php?action=dashboard');
        }
        $model = new AdminDashboardModel();
        $data = [
            'pending_branch_transfers' => $model->getPendingBranchTransfers(),
            'transfer_history' => $model->getTransferHistory(),
            'branches' => $model->getBranches()
        ];
        $viewContent = $this->render('admin_transfers', $data);
        $this->loadLayout($viewContent, 'Transfers');
    }

    // Inventory pivot view (Admin)
    public function inventory() {
        if (!Security::isLoggedIn()) {
            Security::redirect(BASE_URL . '/index.php?action=login');
        }
        $currentUserId = $_SESSION['user_id'];
        $currentUserModel = new User();
        $currentUser = $currentUserModel->findById($currentUserId);
        if (!$currentUser || $currentUser['user_type'] !== 'admin') {
            Security::redirect(BASE_URL . '/index.php?action=dashboard');
        }
        $model = new AdminDashboardModel();
        $data = [
            'inventory_pivot' => $model->getFullInventoryPivot(),
            'items' => $model->getItems(),
            'branches' => $model->getBranches()
        ];
        $viewContent = $this->render('admin_inventory', $data);
        $this->loadLayout($viewContent, 'Inventory');
    }

    /**
     * Handle AJAX requests for admin actions (approvals, declines, etc.)
     */
    public function handleAjax() {
        // Verify user is authenticated
        if (!Security::isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'User not authenticated.']);
            exit();
        }

        // Verify user is admin
        $currentUserId = $_SESSION['user_id'];
        $userModel = new User();
        $currentUser = $userModel->findById($currentUserId);

        if (!$currentUser || $currentUser['user_type'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Insufficient permissions.']);
            exit();
        }

        // Verify CSRF token (uses Security::validateCsrfToken)
        $submitted = $_POST['_wpnonce'] ?? '';
        if (empty($submitted) || !Security::validateCsrfToken($submitted)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Security check failed (CSRF).']);
            exit();
        }

        $action = $_POST['action'] ?? $_POST['action_type'] ?? '';
        $response = ['success' => false, 'message' => 'Invalid AJAX action.'];

        try {
            switch ($action) {
                case 'approve_allocation':
                    $response = $this->handleApproveAllocation();
                    break;
                case 'decline_allocation':
                    $response = $this->handleDeclineAllocation();
                    break;
                case 'approve_adjustment':
                    $response = $this->handleApproveAdjustment();
                    break;
                case 'decline_adjustment':
                    $response = $this->handleDeclineAdjustment();
                    break;
                case 'approve_dispute':
                    $response = $this->handleApproveDispute();
                    break;
                case 'decline_dispute':
                    $response = $this->handleDeclineDispute();
                    break;
                case 'approve_transfer':
                    $response = $this->handleApproveTransfer();
                    break;
                case 'decline_transfer':
                    $response = $this->handleDeclineTransfer();
                    break;
                case 'create_allocation':
                    $response = $this->handleCreateAllocation();
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
     * Approve allocation
     */
    private function handleApproveAllocation() {
        $allocationId = intval($_POST['allocation_id'] ?? 0);

        if (!$allocationId) {
            return ['success' => false, 'message' => 'Allocation ID is required.'];
        }

        $adminId = $_SESSION['user_id'];
        $model = new AdminDashboardModel();
        return $model->approveAllocation($allocationId, $adminId);
    }

    /**
     * Decline allocation
     */
    private function handleDeclineAllocation() {
        $allocationId = intval($_POST['allocation_id'] ?? 0);
        $reason = Security::sanitizeInput($_POST['reason'] ?? '');

        if (!$allocationId) {
            return ['success' => false, 'message' => 'Allocation ID is required.'];
        }
        $adminId = $_SESSION['user_id'];
        $model = new AdminDashboardModel();
        return $model->declineAllocation($allocationId, $adminId, $reason);
    }

    /**
     * Approve adjustment
     */
    private function handleApproveAdjustment() {
        $adjustmentId = intval($_POST['adjustment_id'] ?? 0);

        if (!$adjustmentId) {
            return ['success' => false, 'message' => 'Adjustment ID is required.'];
        }
        $adminId = $_SESSION['user_id'];
        $model = new AdminDashboardModel();
        return $model->approveAdjustment($adjustmentId, $adminId);
    }

    /**
     * Decline adjustment
     */
    private function handleDeclineAdjustment() {
        $adjustmentId = intval($_POST['adjustment_id'] ?? 0);
        $reason = Security::sanitizeInput($_POST['reason'] ?? '');

        if (!$adjustmentId) {
            return ['success' => false, 'message' => 'Adjustment ID is required.'];
        }
        $adminId = $_SESSION['user_id'];
        $model = new AdminDashboardModel();
        return $model->declineAdjustment($adjustmentId, $adminId, $reason);
    }

    /**
     * Approve dispute
     */
    private function handleApproveDispute() {
        $disputeId = intval($_POST['dispute_id'] ?? 0);

        if (!$disputeId) {
            return ['success' => false, 'message' => 'Dispute ID is required.'];
        }
        $adminId = $_SESSION['user_id'];
        $model = new AdminDashboardModel();
        return $model->approveDispute($disputeId, $adminId);
    }

    /**
     * Decline dispute
     */
    private function handleDeclineDispute() {
        $disputeId = intval($_POST['dispute_id'] ?? 0);
        $reason = Security::sanitizeInput($_POST['reason'] ?? '');

        if (!$disputeId) {
            return ['success' => false, 'message' => 'Dispute ID is required.'];
        }
        $adminId = $_SESSION['user_id'];
        $model = new AdminDashboardModel();
        return $model->declineDispute($disputeId, $adminId, $reason);
    }

    /**
     * Approve transfer
     */
    private function handleApproveTransfer() {
        $transferId = intval($_POST['transfer_id'] ?? 0);

        if (!$transferId) {
            return ['success' => false, 'message' => 'Transfer ID is required.'];
        }
        $adminId = $_SESSION['user_id'];
        $model = new AdminDashboardModel();
        return $model->approveTransfer($transferId, $adminId);
    }

    /**
     * Handle create allocation (admin submitting allocation to branches)
     */
    private function handleCreateAllocation() {
        $itemId = intval($_POST['item'] ?? 0);
        $batchId = intval($_POST['batch'] ?? 0);
        $quantity = intval($_POST['quantity'] ?? 0);

        // branches may come as branches[] or branches
        $branches = [];
        if (isset($_POST['branches']) && is_array($_POST['branches'])) {
            $branches = array_map('intval', $_POST['branches']);
        } elseif (isset($_POST['branches[]']) && is_array($_POST['branches[]'])) {
            $branches = array_map('intval', $_POST['branches[]']);
        } elseif (isset($_POST['branches']) && is_string($_POST['branches'])) {
            // single CSV string
            $branches = array_filter(array_map('intval', explode(',', $_POST['branches'])));
        }

        if (!$itemId || !$batchId || $quantity <= 0 || empty($branches)) {
            return ['success' => false, 'message' => 'All fields are required (item, batch, branches, quantity).'];
        }

        $adminId = $_SESSION['user_id'];
        $model = new AdminDashboardModel();
        return $model->createAllocations($itemId, $batchId, $branches, $quantity, $adminId);
    }

    /**
     * Decline transfer
     */
    private function handleDeclineTransfer() {
        $transferId = intval($_POST['transfer_id'] ?? 0);
        $reason = Security::sanitizeInput($_POST['reason'] ?? '');

        if (!$transferId) {
            return ['success' => false, 'message' => 'Transfer ID is required.'];
        }
        $adminId = $_SESSION['user_id'];
        $model = new AdminDashboardModel();
        return $model->declineTransfer($transferId, $adminId, $reason);
    }
}
