<?php
// models/AdminDashboardModel.php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/User.php'; // Assuming you have a User model

class AdminDashboardModel {
    private $pdo;
    private $hasTransferReceivedAt = false;
    private $hasTransferApprovedAt = false;
    private $hasDistributionReceivedAt = false;

    public function __construct() {
        $this->pdo = Database::getInstance();
        try {
            $c1 = $this->pdo->query("SHOW COLUMNS FROM inventra_branch_transfers LIKE 'received_at'");
            $this->hasTransferReceivedAt = (bool) ($c1 && $c1->fetch());
        } catch (Exception $e) {
            $this->hasTransferReceivedAt = false;
        }
        try {
            $c2 = $this->pdo->query("SHOW COLUMNS FROM inventra_branch_transfers LIKE 'approved_at'");
            $this->hasTransferApprovedAt = (bool) ($c2 && $c2->fetch());
        } catch (Exception $e) {
            $this->hasTransferApprovedAt = false;
        }
        try {
            $c3 = $this->pdo->query("SHOW COLUMNS FROM inventra_distributions LIKE 'received_at'");
            $this->hasDistributionReceivedAt = (bool) ($c3 && $c3->fetch());
        } catch (Exception $e) {
            $this->hasDistributionReceivedAt = false;
        }
    }

    /**
     * Fetches counts for the overview cards.
     */
    public function getOverviewCounts() {
        $alloc_sql = "SELECT COUNT(*) FROM inventra_distributions WHERE status = 'pending'";
        $adj_sql = "SELECT COUNT(*) FROM inventra_adjustments WHERE status = 'pending'";
        $disp_sql = "SELECT COUNT(*) FROM inventra_disputes WHERE status = 'pending'";
        $trans_sql = "SELECT COUNT(*) FROM inventra_branch_transfers WHERE status = 'pending'";

        $stmt = $this->pdo->prepare($alloc_sql);
        $stmt->execute();
        $alloc_count = $stmt->fetchColumn();

        $stmt = $this->pdo->prepare($adj_sql);
        $stmt->execute();
        $adj_count = $stmt->fetchColumn();

        $stmt = $this->pdo->prepare($disp_sql);
        $stmt->execute();
        $disp_count = $stmt->fetchColumn();

        $stmt = $this->pdo->prepare($trans_sql);
        $stmt->execute();
        $trans_count = $stmt->fetchColumn();

        return [
            'allocations' => (int)$alloc_count,
            'adjustments' => (int)$adj_count,
            'disputes' => (int)$disp_count,
            'transfers' => (int)$trans_count
        ];
    }

    /**
     * Fetches pending allocation requests (distributions).
     */
    public function getPendingAllocations() {
        $sql = "
        SELECT d.*, i.name AS item_name, i.image_url, b.name AS batch_name, u.username AS branch_name, d.created_at
        FROM inventra_distributions d
        JOIN inventra_inventory_items i ON i.id = d.item_id
        JOIN inventra_batches b ON b.id = d.batch_id
        JOIN users u ON u.id = d.branch_id
        WHERE d.status = 'pending'
        ORDER BY d.created_at DESC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Fetches resolved allocation requests (history).
     */
    public function getResolvedAllocations() {
        $sql = "
        SELECT d.*, i.name AS item_name, i.image_url, b.name AS batch_name, u.username AS branch_name, d.created_at, d.resolved_at
        FROM inventra_distributions d
        JOIN inventra_inventory_items i ON i.id = d.item_id
        JOIN inventra_batches b ON b.id = d.batch_id
        JOIN users u ON u.id = d.branch_id
        WHERE d.status != 'pending'
        ORDER BY d.resolved_at DESC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Fetches pending adjustment requests.
     */
    public function getPendingAdjustments() {
        $sql = "
        SELECT a.*, i.name AS item_name, i.image_url, b.name AS batch_name, u.username AS branch_name, a.created_at
        FROM inventra_adjustments a
        JOIN inventra_inventory_items i ON i.id = a.item_id
        JOIN inventra_batches b ON b.id = a.batch_id
        JOIN users u ON u.id = a.branch_id
        WHERE a.status = 'pending'
        ORDER BY a.created_at DESC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Fetches adjustment history (resolved).
     */
    public function getAdjustmentHistory() {
        $sql = "
        SELECT a.*, i.name AS item_name, i.image_url, b.name AS batch_name, u.username AS branch_name, a.created_at, a.resolved_at
        FROM inventra_adjustments a
        JOIN inventra_inventory_items i ON i.id = a.item_id
        JOIN inventra_batches b ON b.id = a.batch_id
        JOIN users u ON u.id = a.branch_id
        WHERE a.status != 'pending'
        ORDER BY a.resolved_at DESC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Fetches pending dispute requests.
     */
    public function getPendingDisputes() {
        $sql = "
        SELECT d.*, i.name AS item_name, i.image_url, b.name AS batch_name, u.username AS branch_name, d.created_at, st.username AS staff_name
        FROM inventra_disputes d
        JOIN inventra_inventory_items i ON i.id = d.item_id
        JOIN inventra_batches b ON b.id = d.batch_id
        JOIN users u ON u.id = d.branch_id
        LEFT JOIN users st ON st.id = d.staff_id
        WHERE d.status = 'pending'
        ORDER BY d.created_at DESC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Fetches dispute history (resolved).
     */
    public function getDisputeHistory() {
        $sql = "
        SELECT d.*, i.name AS item_name, i.image_url, b.name AS batch_name, u.username AS branch_name, d.created_at, d.resolved_at, st.username AS staff_name
        FROM inventra_disputes d
        JOIN inventra_inventory_items i ON i.id = d.item_id
        JOIN inventra_batches b ON b.id = d.batch_id
        JOIN users u ON u.id = d.branch_id
        LEFT JOIN users st ON st.id = d.staff_id
        WHERE d.status != 'pending'
        ORDER BY d.resolved_at DESC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Fetches pending branch transfers.
     */
    public function getPendingBranchTransfers() {
        $sql = "
        SELECT t.*, i.name AS item_name, i.image_url, b.name AS batch_name, u_from.username AS from_branch_name, u_to.username AS to_branch_name, t.created_at
        FROM inventra_branch_transfers t
        JOIN inventra_inventory_items i ON i.id = t.item_id
        JOIN inventra_batches b ON b.id = t.batch_id
        JOIN users u_from ON u_from.id = t.from_branch_id
        JOIN users u_to ON u_to.id = t.to_branch_id
        WHERE t.status = 'pending'
        ORDER BY t.created_at DESC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Fetches transfer history (resolved/received).
     */
    public function getTransferHistory() {
        $sql = "
        SELECT t.*, i.name AS item_name, i.image_url, b.name AS batch_name, u_from.username AS from_branch_name, u_to.username AS to_branch_name, t.created_at, t.resolved_at, t.received_at, t.received_by_staff_name, t.reason
        FROM inventra_branch_transfers t
        JOIN inventra_inventory_items i ON i.id = t.item_id
        JOIN inventra_batches b ON b.id = t.batch_id
        JOIN users u_from ON u_from.id = t.from_branch_id
        JOIN users u_to ON u_to.id = t.to_branch_id
        WHERE t.status != 'pending'
        ORDER BY t.resolved_at DESC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Fetches received items history.
     */
    public function getReceivedHistory() {
        $orderCol = $this->hasTransferReceivedAt ? 't.received_at' : 't.created_at';
        $sql = "
        SELECT t.*, i.name AS item_name, i.image_url, b.name AS batch_name, u_to.username AS to_branch_name, t.created_at, t.received_at, t.received_by_staff_name
        FROM inventra_branch_transfers t
        JOIN inventra_inventory_items i ON i.id = t.item_id
        JOIN inventra_batches b ON b.id = t.batch_id
        JOIN users u_to ON u_to.id = t.to_branch_id
        WHERE t.status = 'received'
        ORDER BY " . $orderCol . " DESC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Returns dashboard metrics and time series for the admin modern dashboard
     */
    public function getDashboardMetrics($days = 31) {
        $days = max(1, intval($days));
        return [
            'overview' => $this->getOverviewCounts(),
            'received_series' => $this->getReceivedTimeSeries($days),
            'adjustments_series' => $this->getAdjustmentsTimeSeries($days),
            'transfers_series' => $this->getTransfersTimeSeries($days),
            'recent_received' => array_slice($this->getReceivedHistory(), 0, 10),
        ];
    }

    private function getReceivedTimeSeries($days = 31) {
        $days = max(1, intval($days));
        $dateExpr = $this->hasDistributionReceivedAt ? "DATE(COALESCE(received_at, created_at))" : "DATE(created_at)";
        $sql = "SELECT {$dateExpr} AS day, COUNT(*) AS cnt FROM inventra_distributions WHERE status = 'received' AND {$dateExpr} >= DATE_SUB(CURDATE(), INTERVAL {$days} DAY) GROUP BY day ORDER BY day ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Normalize to every day
        $series = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-{$i} days"));
            $series[$d] = 0;
        }
        foreach ($rows as $r) {
            $series[$r['day']] = (int)$r['cnt'];
        }
        $result = [];
        foreach ($series as $day => $cnt) {
            $result[] = ['date' => $day, 'count' => $cnt];
        }
        return $result;
    }

    private function getAdjustmentsTimeSeries($days = 31) {
        $days = max(1, intval($days));
        $sql = "SELECT DATE(resolved_at) AS day, COUNT(*) AS cnt FROM inventra_adjustments WHERE status != 'pending' AND resolved_at IS NOT NULL AND DATE(resolved_at) >= DATE_SUB(CURDATE(), INTERVAL {$days} DAY) GROUP BY day ORDER BY day ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $series = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-{$i} days"));
            $series[$d] = 0;
        }
        foreach ($rows as $r) {
            $series[$r['day']] = (int)$r['cnt'];
        }
        $result = [];
        foreach ($series as $day => $cnt) {
            $result[] = ['date' => $day, 'count' => $cnt];
        }
        return $result;
    }

    private function getTransfersTimeSeries($days = 31) {
        $days = max(1, intval($days));
        $dateExpr = $this->hasTransferReceivedAt ? "DATE(COALESCE(received_at, created_at))" : "DATE(created_at)";
        $sql = "SELECT {$dateExpr} AS day, COUNT(*) AS cnt FROM inventra_branch_transfers WHERE status = 'received' AND {$dateExpr} >= DATE_SUB(CURDATE(), INTERVAL {$days} DAY) GROUP BY day ORDER BY day ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $series = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-{$i} days"));
            $series[$d] = 0;
        }
        foreach ($rows as $r) {
            $series[$r['day']] = (int)$r['cnt'];
        }
        $result = [];
        foreach ($series as $day => $cnt) {
            $result[] = ['date' => $day, 'count' => $cnt];
        }
        return $result;
    }


    /**
     * Gets all branch users.
     */
    public function getBranches() {
        // Select branch users: support legacy user_type values and role membership 'um_branch'
        $sql = "SELECT u.id, u.username FROM users u LEFT JOIN role_user ru ON ru.user_id = u.id LEFT JOIN roles r ON r.id = ru.role_id WHERE u.user_type IN ('branch','branch_user') OR r.slug = 'um_branch' GROUP BY u.id ORDER BY u.username";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Gets all inventory items.
     */
    public function getItems() {
        $sql = "SELECT id, name, image_url FROM inventra_inventory_items ORDER BY name";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Gets all batches.
     */
    public function getBatches() {
        $sql = "SELECT id, name FROM inventra_batches ORDER BY name";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Gets the full inventory pivot matrix (All Branches x All Items).
     */
    public function getFullInventoryPivot() {
        $items = $this->getItems();
        if (empty($items)) {
            return ['data' => [], 'items' => []];
        }

        $branchUsers = $this->getBranches();
        if (empty($branchUsers)) {
            return ['data' => [], 'items' => $items];
        }

        $pivotData = [];
        $itemIdMap = array_column($items, 'id');
        $branchIdMap = array_column($branchUsers, 'id');

        // Fetch aggregated inventory data (sum of allocated, received)
        // Prefer branch-level inventory table where available; otherwise fall back to inventra_inventory
        $inventoryResults = [];

        $tableCheck = $this->pdo->query("SHOW TABLES LIKE 'inventra_branch_inventory'")->fetch();
        if ($tableCheck) {
            // Use current_stock from branch-level inventory
            $sql = "
            SELECT 
                bi.branch_id,
                bi.item_id,
                    COALESCE(alloc.total_allocated, 0) AS total_allocated,
                SUM(COALESCE(bi.current_stock,0)) AS total_received
            FROM inventra_branch_inventory bi
            LEFT JOIN (
                SELECT branch_id, item_id, SUM(quantity) AS total_allocated
                FROM inventra_allocations
                WHERE status = 'pending'
                GROUP BY branch_id, item_id
            ) alloc ON alloc.branch_id = bi.branch_id AND alloc.item_id = bi.item_id
            GROUP BY bi.branch_id, bi.item_id
            ";
        } else {
            // Fallback to the older inventra_inventory table (uses `quantity` column)
            $sql = "
            SELECT 
                inv.branch_id,
                inv.item_id,
                COALESCE(alloc.total_allocated, 0) AS total_allocated,
                SUM(inv.quantity) AS total_received
            FROM inventra_inventory inv
            LEFT JOIN (
                SELECT branch_id, item_id, SUM(quantity) AS total_allocated
                FROM inventra_allocations
                WHERE status = 'pending'
                GROUP BY branch_id, item_id
            ) alloc ON alloc.branch_id = inv.branch_id AND alloc.item_id = inv.item_id
            GROUP BY inv.branch_id, inv.item_id
            ";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $inventoryResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Build the matrix structure
        foreach ($branchUsers as $branch) {
            $rowData = ['branch_id' => $branch->id, 'branch_name' => $branch->username];
            foreach ($items as $item) {
                $rowData[$item['id']] = ['allocated' => 0, 'received' => 0]; // Default to 0
            }
            $pivotData[] = $rowData;
        }

        // Populate the matrix with actual data
        foreach ($inventoryResults as $invRow) {
            $branchIndex = array_search($invRow['branch_id'], $branchIdMap);
            $itemIndex = array_search($invRow['item_id'], $itemIdMap);

            if ($branchIndex !== false && $itemIndex !== false) {
                $pivotData[$branchIndex][$invRow['item_id']]['allocated'] = (int)$invRow['total_allocated'];
                $pivotData[$branchIndex][$invRow['item_id']]['received'] = (int)$invRow['total_received'];
            }
        }

        return ['data' => $pivotData, 'items' => $items];
    }

    /* -------------------------
     * Admin actions (approve/decline)
     * ------------------------- */

    public function approveAllocation($allocationId, $adminId) {
        $now = date('Y-m-d H:i:s');
        $sql = "SELECT * FROM inventra_distributions WHERE id = ? AND status = 'pending'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$allocationId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return ['success' => false, 'message' => 'Allocation not found or not pending.'];

        $upd = $this->pdo->prepare("UPDATE inventra_distributions SET status = 'approved', resolved_at = ?, resolved_by = ? WHERE id = ?");
        $upd->execute([$now, $adminId, $allocationId]);
        return ['success' => true, 'message' => 'Allocation approved.'];
    }

    public function declineAllocation($allocationId, $adminId, $reason = '') {
        $now = date('Y-m-d H:i:s');
        $sql = "SELECT * FROM inventra_distributions WHERE id = ? AND status = 'pending'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$allocationId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return ['success' => false, 'message' => 'Allocation not found or not pending.'];

        $upd = $this->pdo->prepare("UPDATE inventra_distributions SET status = 'declined', resolved_at = ?, resolved_by = ?, resolved_reason = ? WHERE id = ?");
        $upd->execute([$now, $adminId, $reason, $allocationId]);
        return ['success' => true, 'message' => 'Allocation declined.'];
    }

    public function approveAdjustment($adjustmentId, $adminId) {
        $now = date('Y-m-d H:i:s');
        $sql = "SELECT a.*, d.branch_id, d.item_id, d.batch_id FROM inventra_adjustments a JOIN inventra_distributions d ON a.distribution_id = d.id WHERE a.id = ? AND a.status = 'pending'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$adjustmentId]);
        $adj = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$adj) return ['success' => false, 'message' => 'Adjustment not found or not pending.'];

        // Update adjustment status
        $upd = $this->pdo->prepare("UPDATE inventra_adjustments SET status = 'accepted', resolved_at = ?, resolved_by = ? WHERE id = ?");
        $upd->execute([$now, $adminId, $adjustmentId]);

        // Apply inventory change depending on adj_type
        $branchId = $adj['branch_id'];
        $itemId = $adj['item_id'];
        $batchId = $adj['batch_id'];
        $qty = (int)$adj['quantity'];
        $type = $adj['adj_type'];

        if ($type !== 'update') {
            if (in_array($type, ['found','other (add)'])) {
                // add quantity
                $this->pdo->prepare("INSERT INTO inventra_branch_inventory (branch_id,item_id,batch_id,current_stock,total_received) VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE current_stock = current_stock + VALUES(current_stock), total_received = total_received + VALUES(total_received)")
                    ->execute([$branchId, $itemId, $batchId, $qty, $qty]);
            } else {
                // deduct quantity
                $this->pdo->prepare("UPDATE inventra_branch_inventory SET current_stock = GREATEST(current_stock - ?,0) WHERE branch_id = ? AND item_id = ? AND batch_id = ?")
                    ->execute([$qty, $branchId, $itemId, $batchId]);
            }
        }

        return ['success' => true, 'message' => 'Adjustment approved and inventory updated.'];
    }

    public function declineAdjustment($adjustmentId, $adminId, $reason = '') {
        $now = date('Y-m-d H:i:s');
        $sql = "SELECT * FROM inventra_adjustments WHERE id = ? AND status = 'pending'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$adjustmentId]);
        $adj = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$adj) return ['success' => false, 'message' => 'Adjustment not found or not pending.'];

        $upd = $this->pdo->prepare("UPDATE inventra_adjustments SET status = 'declined', resolved_at = ?, resolved_by = ?, resolved_reason = ? WHERE id = ?");
        $upd->execute([$now, $adminId, $reason, $adjustmentId]);
        return ['success' => true, 'message' => 'Adjustment declined.'];
    }

    public function approveDispute($disputeId, $adminId) {
        $now = date('Y-m-d H:i:s');
        $sql = "SELECT d.*, dist.branch_id, dist.item_id, dist.batch_id, dist.received FROM inventra_disputes d LEFT JOIN inventra_distributions dist ON d.distribution_id = dist.id WHERE d.id = ? AND d.status = 'pending'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$disputeId]);
        $dispute = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$dispute) return ['success' => false, 'message' => 'Dispute not found or not pending.'];

        // mark dispute accepted
        $this->pdo->prepare("UPDATE inventra_disputes SET status = 'accepted', resolved_at = ?, resolved_by = ? WHERE id = ?")->execute([$now, $adminId, $disputeId]);

        if (!empty($dispute['distribution_id'])) {
            // ensure distribution marked received and inventory updated
            $this->pdo->prepare("UPDATE inventra_distributions SET status = 'received', received_at = ? WHERE id = ?")->execute([$now, $dispute['distribution_id']]);
            $qtyToAdd = (int)$dispute['received'];
            if ($qtyToAdd > 0) {
                $this->pdo->prepare("INSERT INTO inventra_branch_inventory (branch_id,item_id,batch_id,current_stock,total_received) VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE current_stock = current_stock + VALUES(current_stock), total_received = total_received + VALUES(total_received)")
                    ->execute([$dispute['branch_id'], $dispute['item_id'], $dispute['batch_id'], $qtyToAdd, $qtyToAdd]);
            }
        }

        return ['success' => true, 'message' => 'Dispute accepted and inventory reconciled.'];
    }

    public function declineDispute($disputeId, $adminId, $reason = '') {
        $now = date('Y-m-d H:i:s');
        $sql = "SELECT d.* FROM inventra_disputes d WHERE d.id = ? AND d.status = 'pending'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$disputeId]);
        $dispute = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$dispute) return ['success' => false, 'message' => 'Dispute not found or not pending.'];

        $this->pdo->prepare("UPDATE inventra_disputes SET status = 'declined', resolved_at = ?, resolved_by = ?, resolved_reason = ? WHERE id = ?")->execute([$now, $adminId, $reason, $disputeId]);

        if (!empty($dispute['distribution_id'])) {
            // revert distribution back to pending
            $this->pdo->prepare("UPDATE inventra_distributions SET status = 'pending', received = 0, received_at = NULL WHERE id = ?")->execute([$dispute['distribution_id']]);
        }

        return ['success' => true, 'message' => 'Dispute declined and distribution reverted.'];
    }

    public function approveTransfer($transferId, $adminId) {
        $now = date('Y-m-d H:i:s');
        $sql = "SELECT * FROM inventra_branch_transfers WHERE id = ? AND status = 'pending'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$transferId]);
        $t = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$t) return ['success' => false, 'message' => 'Transfer not found or not pending.'];

        $this->pdo->prepare("UPDATE inventra_branch_transfers SET status = 'approved', approved_at = ?, approved_by = ? WHERE id = ?")->execute([$now, $adminId, $transferId]);
        return ['success' => true, 'message' => 'Transfer approved.'];
    }

    public function declineTransfer($transferId, $adminId, $reason = '') {
        $now = date('Y-m-d H:i:s');
        $sql = "SELECT * FROM inventra_branch_transfers WHERE id = ? AND status = 'pending'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$transferId]);
        $t = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$t) return ['success' => false, 'message' => 'Transfer not found or not pending.'];

        $this->pdo->prepare("UPDATE inventra_branch_transfers SET status = 'declined', approved_at = ?, approved_by = ?, reason = ? WHERE id = ?")->execute([$now, $adminId, $reason, $transferId]);
        return ['success' => true, 'message' => 'Transfer declined.'];
    }

    /**
     * Create allocation(s) for one item/batch to multiple branches.
     * Returns summary with counts and any failures.
     */
    public function createAllocations($itemId, $batchId, array $branchIds, $quantity, $adminId) {
        if (empty($itemId) || empty($batchId) || empty($branchIds) || $quantity <= 0) {
            return ['success' => false, 'message' => 'Invalid allocation data.'];
        }

        $now = date('Y-m-d H:i:s');
        $stmt = $this->pdo->prepare("INSERT INTO inventra_distributions (branch_id, item_id, batch_id, distributed, status, created_at, created_by) VALUES (?, ?, ?, ?, 'pending', ?, ?)");

        $failed = [];
        $created = 0;
        foreach ($branchIds as $branchId) {
            try {
                $stmt->execute([$branchId, $itemId, $batchId, $quantity, $now, $adminId]);
                $created++;
            } catch (Exception $e) {
                $failed[] = ['branch_id' => $branchId, 'error' => $e->getMessage()];
            }
        }

        if (!empty($failed)) {
            return ['success' => false, 'message' => 'Some allocations failed.', 'created' => $created, 'failed' => $failed];
        }

        return ['success' => true, 'message' => 'Allocations created: ' . $created, 'created' => $created];
    }
}
