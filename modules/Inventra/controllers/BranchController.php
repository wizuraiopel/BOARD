<?php
// controllers/BranchController.php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../../../core/Security.php';
require_once __DIR__ . '/../models/BranchDashboardModel.php';
require_once __DIR__ . '/../models/User.php';


class BranchController extends BaseController {

    public function index() {
        // Permission check: Only branch users
        if (!Security::isLoggedIn()) {
            Security::redirect(BASE_URL . '/index.php?action=login');
        }

        $currentUserId = $_SESSION['user_id'];
        $currentUserModel = new User();
        $currentUser = $currentUserModel->findById($currentUserId);
        $roleSlugs = $currentUserModel->getRoleSlugs($currentUserId);

        if (!$currentUser || (!in_array('um_branch', $roleSlugs) && !in_array($currentUser['user_type'], ['branch', 'branch_user']))) {
            Security::redirect(BASE_URL . '/index.php?action=dashboard'); // Redirect non-branch users
        }

        // Prefer the modern dashboard view for branch users
        Security::redirect(BASE_URL . '/index.php?action=branch_dashboard_modern');
    }

    // Branch allocations page (GET)
    public function allocations() {
        if (!Security::isLoggedIn()) {
            Security::redirect(BASE_URL . '/index.php?action=login');
        }
        $currentUserId = $_SESSION['user_id'];
        $currentUserModel = new User();
        $currentUser = $currentUserModel->findById($currentUserId);
        $roleSlugs = $currentUserModel->getRoleSlugs($currentUserId);
        if (!$currentUser || (!in_array('um_branch', $roleSlugs) && !in_array($currentUser['user_type'], ['branch', 'branch_user']))) {
            Security::redirect(BASE_URL . '/index.php?action=dashboard');
        }
        $model = new BranchDashboardModel();
        $data = [
            'pending_allocations' => $model->getPendingAllocations($currentUserId)
        ];
        $viewContent = $this->render('branch/allocations', $data);
        $this->loadLayout($viewContent, 'Pending Allocations');
    }

    // Branch adjustments page (GET)
    public function adjustments() {
        if (!Security::isLoggedIn()) {
            Security::redirect(BASE_URL . '/index.php?action=login');
        }
        $currentUserId = $_SESSION['user_id'];
        $currentUserModel = new User();
        $currentUser = $currentUserModel->findById($currentUserId);
        $roleSlugs = $currentUserModel->getRoleSlugs($currentUserId);
        if (!$currentUser || (!in_array('um_branch', $roleSlugs) && !in_array($currentUser['user_type'], ['branch', 'branch_user']))) {
            Security::redirect(BASE_URL . '/index.php?action=dashboard');
        }
        $model = new BranchDashboardModel();
        $data = [
            'pending_adjustments' => $model->getPendingAdjustments($currentUserId),
            'adjustment_history' => $model->getAdjustmentHistory($currentUserId)
        ];
        $viewContent = $this->render('branch/adjustments', $data);
        $this->loadLayout($viewContent, 'Adjustments');
    }

    // Branch transfers (incoming)
    public function transfers() {
        if (!Security::isLoggedIn()) {
            Security::redirect(BASE_URL . '/index.php?action=login');
        }
        $currentUserId = $_SESSION['user_id'];
        $currentUserModel = new User();
        $currentUser = $currentUserModel->findById($currentUserId);
        $roleSlugs = $currentUserModel->getRoleSlugs($currentUserId);
        if (!$currentUser || (!in_array('um_branch', $roleSlugs) && !in_array($currentUser['user_type'], ['branch', 'branch_user']))) {
            Security::redirect(BASE_URL . '/index.php?action=dashboard');
        }
        $model = new BranchDashboardModel();
        $data = [
            'incoming_transfers' => $model->getIncomingTransfers($currentUserId),
            'outgoing_transfers' => $model->getOutgoingTransfers($currentUserId)
        ];
        $viewContent = $this->render('branch/transfers', $data);
        $this->loadLayout($viewContent, 'Transfers');
    }

    // Branch outgoing transfers (GET)
    public function transfersOut() {
        if (!Security::isLoggedIn()) {
            Security::redirect(BASE_URL . '/index.php?action=login');
        }
        $currentUserId = $_SESSION['user_id'];
        $currentUserModel = new User();
        $currentUser = $currentUserModel->findById($currentUserId);
        $roleSlugs = $currentUserModel->getRoleSlugs($currentUserId);
        if (!$currentUser || (!in_array('um_branch', $roleSlugs) && !in_array($currentUser['user_type'], ['branch', 'branch_user']))) {
            Security::redirect(BASE_URL . '/index.php?action=dashboard');
        }
        $model = new BranchDashboardModel();
        $data = [
            'outgoing_transfers' => $model->getOutgoingTransfers($currentUserId)
        ];
        $viewContent = $this->render('branch/transfers_out', $data);
        $this->loadLayout($viewContent, 'Outgoing Transfers');
    }

    // Modern branch dashboard prototype (GET)
    public function modernDashboard() {
        if (!Security::isLoggedIn()) {
            Security::redirect(BASE_URL . '/index.php?action=login');
        }
        $currentUserId = $_SESSION['user_id'];
        $currentUserModel = new User();
        $currentUser = $currentUserModel->findById($currentUserId);
        $roleSlugs = $currentUserModel->getRoleSlugs($currentUserId);
        if (!$currentUser || (!in_array('um_branch', $roleSlugs) && !in_array($currentUser['user_type'], ['branch', 'branch_user']))) {
            Security::redirect(BASE_URL . '/index.php?action=dashboard');
        }

        $model = new BranchDashboardModel();
        $data = $model->getDashboardMetrics($currentUserId);

        $viewContent = $this->render('branch/modern_dashboard', $data);
        $this->loadLayout($viewContent, 'Branch Dashboard (Modern)');
    }

    // API: return dashboard metrics / time series as JSON
    public function dashboardStats() {
        if (!Security::isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }

        $currentUserId = $_SESSION['user_id'];
        $currentUserModel = new User();
        $currentUser = $currentUserModel->findById($currentUserId);
        $roleSlugs = $currentUserModel->getRoleSlugs($currentUserId);
        if (!$currentUser || (!in_array('um_branch', $roleSlugs) && !in_array($currentUser['user_type'], ['branch', 'branch_user']))) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Forbidden']);
            exit();
        }

        $model = new BranchDashboardModel();
        $days = isset($_GET['days']) ? max(7, intval($_GET['days'])) : 31; // allow caller to request day range, min 7
        // Call the base metrics method (backwards compatible) then adjust series if needed
        $metrics = $model->getDashboardMetrics($currentUserId);
        if ($days !== 31) {
            $metrics['series']['received'] = $model->getReceivedTimeSeries($currentUserId, $days);
            $metrics['series']['adjustments'] = $model->getAdjustmentsTimeSeries($currentUserId, $days);
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $metrics]);
        exit();
    }

    /**
     * Handle AJAX requests for branch dashboard
     */
    public function handleAjax() {
        // Verify CSRF token: accept either '_wpnonce' or 'csrf_token' and validate via Security
        $submitted = $_POST['_wpnonce'] ?? $_POST['csrf_token'] ?? '';
        if (empty($submitted) || !Security::validateCsrfToken($submitted)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
            exit();
        }

        // Verify user is logged in and is a branch user
        if (!Security::isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }

        $currentUserId = $_SESSION['user_id'];
        $currentUserModel = new User();
        $currentUser = $currentUserModel->findById($currentUserId);
        $roleSlugs = $currentUserModel->getRoleSlugs($currentUserId);

        if (!$currentUser || (!in_array('um_branch', $roleSlugs) && !in_array($currentUser['user_type'], ['branch', 'branch_user']))) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Forbidden']);
            exit();
        }

        $actionType = $_POST['action_type'] ?? $_POST['action'] ?? '';
        $model = new BranchDashboardModel();

        try {
            switch ($actionType) {
                case 'receive':
                    $result = $this->handleReceiveAllocation(
                        $_POST['dist_id'] ?? 0,
                        $_POST['received_qty'] ?? 0,
                        $_POST['dispute_reason'] ?? '',
                        $_POST['staff_name'] ?? '',
                        $currentUserId
                    );
                    break;

                case 'resolve_dispute':
                    $result = $this->handleResolveDispute(
                        $_POST['dispute_id'] ?? 0,
                        $_POST['resolution'] ?? '',
                        $currentUserId
                    );
                    break;

                case 'initiate_transfer':
                    $result = $this->handleInitiateTransfer(
                        $_POST['item_id'] ?? 0,
                        $_POST['batch_id'] ?? 0,
                        $_POST['to_branch_id'] ?? 0,
                        $_POST['quantity'] ?? 0,
                        $_POST['reason'] ?? '',
                        $_POST['staff_name'] ?? '',
                        $currentUserId
                    );
                    break;

                case 'receive_transfer':
                    $result = $this->handleReceiveTransfer(
                        $_POST['transfer_id'] ?? 0,
                        $_POST['received_qty'] ?? 0,
                        $_POST['dispute_reason'] ?? '',
                        $_POST['staff_name'] ?? '',
                        $currentUserId
                    );
                    break;

                case 'dispute_transfer':
                    $result = $this->handleDisputeTransfer(
                        $_POST['transfer_id'] ?? 0,
                        $_POST['disputed_qty'] ?? 0,
                        $_POST['dispute_reason'] ?? '',
                        $currentUserId
                    );
                    break;

                case 'request_adjustment':
                    $result = $this->handleRequestAdjustment(
                        Security::sanitizeInput($_POST['item_id'] ?? 0),
                        Security::sanitizeInput($_POST['adj_type'] ?? ''),
                        intval($_POST['adj_qty'] ?? 0),
                        Security::sanitizeInput($_POST['adj_reason'] ?? ''),
                        Security::sanitizeInput($_POST['staff_name'] ?? ''),
                        $currentUserId
                    );
                    break;

                default:
                    $result = ['success' => false, 'message' => 'Unknown action type.'];
            }
        } catch (Exception $e) {
            $result = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }

        echo json_encode($result);
        exit();
    }

    /**
     * Handle receive allocation
     */
    private function handleReceiveAllocation($distId, $receivedQty, $disputeReason, $staffName, $branchId) {
        $db = Database::getInstance();
        $prefix = 'inventra_';

        // Validate inputs
        if (!$distId || !$staffName) {
            return ['success' => false, 'message' => 'Invalid request. Missing required fields.'];
        }

        // Fetch distribution record
        $distribution = $db->get_row($db->prepare(
            "SELECT * FROM {$prefix}distributions WHERE id = %d AND branch_id = %d AND status = 'pending'",
            $distId, $branchId
        ));

        if (!$distribution) {
            return ['success' => false, 'message' => 'Distribution not found or not allocated to your branch.'];
        }

        // Validate received quantity
        if ($receivedQty < 0 || $receivedQty > $distribution->distributed) {
            return ['success' => false, 'message' => 'Invalid received quantity.'];
        }

        $receivedAt = date('Y-m-d H:i:s');

        // Start transaction
        $db->query('START TRANSACTION');

        try {
            if ($receivedQty < $distribution->distributed) {
                // Dispute case
                if (empty($disputeReason)) {
                    return ['success' => false, 'message' => 'Dispute reason is required when quantity is less.'];
                }

                // Update distribution to 'disputed'
                $db->update(
                    "{$prefix}distributions",
                    [
                        'status' => 'disputed',
                        'received_at' => $receivedAt,
                        'received' => $receivedQty,
                        'staff_name' => $staffName
                    ],
                    ['id' => $distId],
                    ['%s', '%s', '%d', '%s']
                );

                // Calculate disputed quantity
                $disputedQuantity = $distribution->distributed - $receivedQty;

                // Insert dispute record
                $db->insert(
                    "{$prefix}disputes",
                    [
                        'distribution_id' => $distId,
                        'disputed_qty' => $disputedQuantity,
                        'reason' => $disputeReason,
                        'status' => 'pending',
                        'staff_name' => $staffName,
                        'created_at' => $receivedAt
                    ],
                    ['%d', '%d', '%s', '%s', '%s', '%s']
                );

                $db->query('COMMIT');
                return ['success' => true, 'message' => 'Item received with dispute. Inventory update pending admin approval.'];
            } else {
                // Full receipt case
                $db->update(
                    "{$prefix}distributions",
                    [
                        'status' => 'received',
                        'received_at' => $receivedAt,
                        'received' => $receivedQty,
                        'staff_name' => $staffName
                    ],
                    ['id' => $distId],
                    ['%s', '%s', '%d', '%s']
                );

                // Update branch inventory
                $db->query($db->prepare(
                    "INSERT INTO {$prefix}branch_inventory (branch_id, item_id, batch_id, current_stock, total_received)
                     VALUES (%d, %d, %d, %d, %d)
                     ON DUPLICATE KEY UPDATE
                     current_stock = current_stock + %d,
                     total_received = total_received + %d",
                    $branchId, $distribution->item_id, $distribution->batch_id, $receivedQty, $receivedQty,
                    $receivedQty, $receivedQty
                ));

                $db->query('COMMIT');
                return ['success' => true, 'message' => 'Item received successfully.'];
            }
        } catch (Exception $e) {
            $db->query('ROLLBACK');
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Handle resolve dispute
     */
    private function handleResolveDispute($disputeId, $resolution, $branchId) {
        $db = Database::getInstance();
        $prefix = 'inventra_';

        // Validate inputs
        if (!$disputeId || !in_array($resolution, ['accept', 'decline'])) {
            return ['success' => false, 'message' => 'Invalid dispute ID or resolution type.'];
        }

        // Fetch dispute and associated distribution
        $dispute = $db->get_row($db->prepare(
            "SELECT d.*, dist.branch_id, dist.item_id, dist.batch_id, dist.distributed, dist.received 
             FROM {$prefix}disputes d 
             LEFT JOIN {$prefix}distributions dist ON d.distribution_id = dist.id 
             WHERE d.id = %d AND d.status = 'pending'",
            $disputeId
        ));

        if (!$dispute) {
            return ['success' => false, 'message' => 'Dispute not found or already resolved.'];
        }

        $db->query('START TRANSACTION');

        try {
            // Update dispute status
            $db->update(
                "{$prefix}disputes",
                [
                    'status' => $resolution === 'accept' ? 'accepted' : 'declined',
                    'resolved_at' => date('Y-m-d H:i:s')
                ],
                ['id' => $disputeId],
                ['%s', '%s']
            );

            if ($resolution === 'accept' && $dispute->distribution_id) {
                // Update distribution to 'received' with full quantity
                $db->update(
                    "{$prefix}distributions",
                    [
                        'status' => 'received',
                        'received_at' => date('Y-m-d H:i:s')
                    ],
                    ['id' => $dispute->distribution_id],
                    ['%s', '%s']
                );

                // Add received quantity to branch inventory
                $qtyToAdd = $dispute->received;

                $db->query($db->prepare(
                    "INSERT INTO {$prefix}branch_inventory (branch_id, item_id, batch_id, current_stock, total_received)
                         VALUES (%d, %d, %d, %d, %d)
                         ON DUPLICATE KEY UPDATE
                         current_stock = current_stock + %d,
                         total_received = total_received + %d",
                    $dispute->branch_id, $dispute->item_id, $dispute->batch_id, $qtyToAdd, $qtyToAdd
                ));

                $db->query('COMMIT');
                return ['success' => true, 'message' => 'Dispute accepted. Branch inventory updated.'];
            } elseif ($resolution === 'decline' && $dispute->distribution_id) {
                // Revert distribution to 'pending'
                $db->update(
                    "{$prefix}distributions",
                    [
                        'status' => 'pending',
                        'received' => 0,
                        'received_at' => null,
                        'staff_name' => null
                    ],
                    ['id' => $dispute->distribution_id],
                    ['%s', '%d', '%d', null, null],
                    ['%d']
                );

                $db->query('COMMIT');
                return ['success' => true, 'message' => 'Dispute declined. Distribution reverted to pending.'];
            }

            $db->query('COMMIT');
            return ['success' => true, 'message' => "Dispute {$resolution} successfully."];
        } catch (Exception $e) {
            $db->query('ROLLBACK');
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Handle initiate transfer
     */
    private function handleInitiateTransfer($itemId, $batchId, $toBranchId, $quantity, $reason, $staffName, $branchId) {
        $db = Database::getInstance();
        $prefix = 'inventra_';

        // Validate inputs
        if (!$itemId || !$batchId || !$toBranchId || !$quantity || !$staffName) {
            return ['success' => false, 'message' => 'All required fields must be provided.'];
        }

        if ($quantity <= 0) {
            return ['success' => false, 'message' => 'Quantity must be greater than zero.'];
        }

        // Verify target branch
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare(
            "SELECT u.id, u.username, u.user_type, GROUP_CONCAT(r.slug) AS role_slugs
             FROM users u
             LEFT JOIN role_user ru ON ru.user_id = u.id
             LEFT JOIN roles r ON r.id = ru.role_id
             WHERE u.id = ?
             GROUP BY u.id"
        );
        $stmt->execute([$toBranchId]);
        $toBranch = $stmt->fetch(PDO::FETCH_ASSOC);

        $toBranchRoles = array_filter(array_map('trim', explode(',', $toBranch['role_slugs'] ?? '')));
        if (!$toBranch || $toBranchId == $branchId || (!in_array('um_branch', $toBranchRoles) && !in_array($toBranch['user_type'], ['branch','branch_user']))) {
            return ['success' => false, 'message' => 'Invalid target branch.'];
        }

        // Check current stock
        $currentStock = $db->get_var($db->prepare(
            "SELECT current_stock FROM {$prefix}branch_inventory 
             WHERE branch_id = %d AND item_id = %d AND batch_id = %d",
            $branchId, $itemId, $batchId
        ));

        if ($currentStock === false || $currentStock < $quantity) {
            return ['success' => false, 'message' => 'Insufficient stock available.'];
        }

        // Insert transfer record
        $db->query('START TRANSACTION');

        try {
            $result = $db->insert(
                "{$prefix}branch_transfers",
                [
                    'from_branch_id' => $branchId,
                    'to_branch_id' => $toBranchId,
                    'item_id' => $itemId,
                    'batch_id' => $batchId,
                    'quantity' => $quantity,
                    'reason' => $reason,
                    'status' => 'pending',
                    'created_at' => date('Y-m-d H:i:s')
                ],
                ['%d', '%d', '%d', '%d', '%d', '%s', '%s']
            );

            if ($result === false) {
                throw new Exception('Failed to create transfer record');
            }

            $db->query('COMMIT');
            return ['success' => true, 'message' => 'Transfer request initiated. Awaiting admin approval.'];
        } catch (Exception $e) {
            $db->query('ROLLBACK');
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Handle receive transfer
     */
    private function handleReceiveTransfer($transferId, $receivedQty, $disputeReason, $staffName, $branchId) {
        $db = Database::getInstance();
        $prefix = 'inventra_';

        // Validate inputs
        if (!$transferId || !$staffName) {
            return ['success' => false, 'message' => 'Invalid request. Missing required fields.'];
        }

        // Fetch transfer record
        $transfer = $db->get_row($db->prepare(
            "SELECT * FROM {$prefix}branch_transfers WHERE id = %d AND to_branch_id = %d AND status = 'approved'",
            $transferId, $branchId
        ));

        if (!$transfer) {
            return ['success' => false, 'message' => 'Transfer not found or not approved for your branch.'];
        }

        $allocatedQty = $transfer->quantity;

        // Validate received quantity
        if ($receivedQty < 0 || $receivedQty > $allocatedQty) {
            return ['success' => false, 'message' => 'Invalid received quantity.'];
        }

        $receivedAt = date('Y-m-d H:i:s');

        $db->query('START TRANSACTION');

        try {
            if ($receivedQty < $allocatedQty) {
                // Dispute case
                if (empty($disputeReason)) {
                    return ['success' => false, 'message' => 'Dispute reason is required when quantity is less.'];
                }

                // Update transfer to 'disputed'
                $db->update(
                    "{$prefix}branch_transfers",
                    [
                        'status' => 'disputed',
                        'received_at' => $receivedAt,
                        'received_qty' => $receivedQty,
                        'disputed_qty' => $allocatedQty - $receivedQty,
                        'dispute_reason' => $disputeReason,
                        'received_by' => $staffName
                    ],
                    ['id' => $transferId],
                    ['%s', '%s', '%s', '%d', '%d', '%s', '%s'],
                    ['%d', '%d']
                );

                // Insert distribution record
                $distributionId = $db->insert(
                    "{$prefix}distributions",
                    [
                        'branch_id' => $branchId,
                        'item_id' => $transfer->item_id,
                        'batch_id' => $transfer->batch_id,
                        'distributed' => $allocatedQty,
                        'received' => $receivedQty,
                        'status' => 'disputed',
                        'created_at' => $receivedAt,
                        'received_at' => $receivedAt,
                        'staff_name' => $staffName
                    ],
                    ['%d', '%d', '%d', '%d', '%d', '%s', '%s', '%s']
                );

                // Insert dispute record
                $db->insert(
                    "{$prefix}disputes",
                    [
                        'distribution_id' => $distributionId,
                        'transfer_id' => $transferId,
                        'disputed_qty' => $allocatedQty - $receivedQty,
                        'reason' => $disputeReason,
                        'status' => 'pending',
                        'staff_name' => $staffName,
                        'created_at' => $receivedAt
                    ],
                    ['%d', '%d', '%d', '%d', '%s', '%s', '%s']
                );

                $db->query('COMMIT');
                return ['success' => true, 'message' => 'Transfer received with dispute. Inventory update pending admin approval.'];
            } else {
                // Full receipt case
                $db->update(
                    "{$prefix}branch_transfers",
                    [
                        'status' => 'received',
                        'received_at' => $receivedAt,
                        'received_qty' => $receivedQty,
                        'received_by' => $staffName
                    ],
                    ['id' => $transferId],
                    ['%s', '%s', '%d', '%s'],
                    ['%d', '%d']
                );

                // Insert distribution record
                $distributionId = $db->insert(
                    "{$prefix}distributions",
                    [
                        'branch_id' => $branchId,
                        'item_id' => $transfer->item_id,
                        'batch_id' => $transfer->batch_id,
                        'distributed' => $allocatedQty,
                        'received' => $receivedQty,
                        'status' => 'received',
                        'created_at' => $receivedAt,
                        'received_at' => $receivedAt,
                        'staff_name' => $staffName
                    ],
                    ['%d', '%d', '%d', '%d', '%s', '%s', '%s']
                );

                // Update branch inventory
                $db->query($db->prepare(
                    "INSERT INTO {$prefix}branch_inventory (branch_id, item_id, batch_id, current_stock, total_received)
                         VALUES (%d, %d, %d, %d, %d)
                         ON DUPLICATE KEY UPDATE
                         current_stock = current_stock + %d,
                         total_received = total_received + %d",
                    $branchId, $transfer->item_id, $transfer->batch_id, $receivedQty, $receivedQty
                ));

                $db->query('COMMIT');
                return ['success' => true, 'message' => 'Transfer received successfully.'];
            }
        } catch (Exception $e) {
            $db->query('ROLLBACK');
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Handle dispute transfer
     */
    private function handleDisputeTransfer($transferId, $disputedQty, $disputeReason, $branchId) {
        $db = Database::getInstance();
        $prefix = 'inventra_';

        // Validate inputs
        if (!$transferId || $disputedQty < 0 || empty($disputeReason)) {
            return ['success' => false, 'message' => 'Invalid or missing required fields.'];
        }

        // Fetch transfer record
        $transfer = $db->get_row($db->prepare(
            "SELECT * FROM {$prefix}branch_transfers WHERE id = %d AND to_branch_id = %d",
            $transferId, $branchId
        ));

        if (!$transfer || $transfer->status !== 'approved') {
            return ['success' => false, 'message' => 'Transfer not found or not in approved status.'];
        }

        $db->query('START TRANSACTION');

        try {
            // Update transfer to 'disputed'
            $db->update(
                "{$prefix}branch_transfers",
                [
                    'disputed_qty' => $disputedQty,
                    'dispute_reason' => $disputeReason,
                    'status' => 'disputed'
                ],
                ['id' => $transferId],
                ['%d', '%s', '%s'],
                ['%d']
            );

            $db->query('COMMIT');
            return ['success' => true, 'message' => 'Transfer dispute recorded successfully.'];
        } catch (Exception $e) {
            $db->query('ROLLBACK');
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Handle request adjustment
     */
    private function handleRequestAdjustment($itemId, $adjType, $adjQty, $adjReason, $staffName, $branchId) {
        $db = Database::getInstance();
        $prefix = 'inventra_';

        $validTypes = ['redeem', 'damaged', 'stolen', 'missing', 'found', 'other (add)', 'other (deduct)', 'update'];

        // Validate inputs
        if (!$itemId || !in_array($adjType, $validTypes) || empty($adjReason) || empty($staffName)) {
            return ['success' => false, 'message' => 'Invalid adjustment data.'];
        }

        // Quantity validation
        if ($adjType === 'update') {
            if ($adjQty !== 0) {
                return ['success' => false, 'message' => 'Quantity must be 0 for "Update" type.'];
            }
        } else {
            if ($adjQty <= 0) {
                return ['success' => false, 'message' => 'Quantity must be greater than 0.'];
            }
        }

        // Find relevant distribution ID
        $distribution = $db->get_row($db->prepare(
            "SELECT id FROM {$prefix}distributions WHERE branch_id = %d AND item_id = %d ORDER BY created_at DESC LIMIT 1",
            $branchId, $itemId
        ));

        if (!$distribution) {
            return ['success' => false, 'message' => 'No distribution record found for this item.'];
        }

        // Insert adjustment request
        $db->query('START TRANSACTION');

        try {
            $result = $db->insert(
                "{$prefix}adjustments",
                [
                    'distribution_id' => $distribution->id,
                    'adj_type' => $adjType,
                    'quantity' => $adjQty,
                    'reason' => $adjReason,
                    'staff_name' => $staffName,
                    'status' => 'pending',
                    'created_at' => date('Y-m-d H:i:s')
                ],
                ['%d', '%s', '%d', '%s', '%s', '%s', '%s']
            );

            if ($result === false) {
                throw new Exception('Failed to create adjustment record');
            }

            $db->query('COMMIT');
            return ['success' => true, 'message' => 'Adjustment request submitted. Awaiting admin approval.'];
        } catch (Exception $e) {
            $db->query('ROLLBACK');
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
}
