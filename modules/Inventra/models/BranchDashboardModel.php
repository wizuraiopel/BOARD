<?php
// models/BranchDashboardModel.php
require_once __DIR__ . '/../../../config/database.php';

class BranchDashboardModel {
    private $pdo;
    private $hasReceivedAt = false;
    private $hasAdjustmentDistribution = false;
    private $hasDisputeAllocation = false;
    private $hasDisputeDistribution = false;
    private $hasTransferApprovedAt = false;
    
    public function __construct() {
        $this->pdo = Database::getInstance();
        try {
            $check = $this->pdo->query("SHOW COLUMNS FROM inventra_distributions LIKE 'received_at'");
            $this->hasReceivedAt = (bool) ($check && $check->fetch());
        } catch (Exception $e) {
            $this->hasReceivedAt = false;
        }
        try {
            $check2 = $this->pdo->query("SHOW COLUMNS FROM inventra_adjustments LIKE 'distribution_id'");
            $this->hasAdjustmentDistribution = (bool) ($check2 && $check2->fetch());
        } catch (Exception $e) {
            $this->hasAdjustmentDistribution = false;
        }
        try {
            $c1 = $this->pdo->query("SHOW COLUMNS FROM inventra_disputes LIKE 'allocation_id'");
            $this->hasDisputeAllocation = (bool) ($c1 && $c1->fetch());
        } catch (Exception $e) {
            $this->hasDisputeAllocation = false;
        }
        try {
            $c2 = $this->pdo->query("SHOW COLUMNS FROM inventra_disputes LIKE 'distribution_id'");
            $this->hasDisputeDistribution = (bool) ($c2 && $c2->fetch());
        } catch (Exception $e) {
            $this->hasDisputeDistribution = false;
        }
        try {
            $c3 = $this->pdo->query("SHOW COLUMNS FROM inventra_branch_transfers LIKE 'approved_at'");
            $this->hasTransferApprovedAt = (bool) ($c3 && $c3->fetch());
        } catch (Exception $e) {
            $this->hasTransferApprovedAt = false;
        }
    }
    
    /**
     * Get all dashboard data for a branch
     */
    public function getDashboardData($branchId) {
        return [
            'overview_cards' => $this->getOverviewCards($branchId),
            'pending_allocations' => $this->getPendingAllocations($branchId),
            'received_items' => $this->getReceivedItems($branchId),
            'received_count_31days' => $this->getReceivedCount31Days($branchId),
            'adjustments' => $this->getAdjustments($branchId),
            'pending_adjustments' => $this->getPendingAdjustments($branchId),
            'adjustment_history' => $this->getAdjustmentHistory($branchId),
            'disputes' => $this->getDisputes($branchId),
            'pending_disputes' => $this->getPendingDisputes($branchId),
            'dispute_history' => $this->getDisputeHistory($branchId),
            'transfers' => $this->getTransfers($branchId),
            'outgoing_transfers' => $this->getOutgoingTransfers($branchId),
            'pending_outgoing_count' => $this->getPendingOutgoingCount($branchId),
            'incoming_transfers' => $this->getIncomingTransfers($branchId),
            'pending_incoming_count' => $this->getPendingIncomingCount($branchId),
            'inventory' => $this->getInventorySummary($branchId),
            'current_branch_inventory' => $this->getCurrentBranchInventory($branchId),
            'all_branches_for_transfer' => $this->getAllBranchesForTransfer($branchId)
        ];
    }
    
    /**
     * Get overview cards data
     */
    private function getOverviewCards($branchId) {
        return [
            [
                'icon' => '📦',
                'count' => count($this->getPendingAllocations($branchId)),
                'subtitle' => 'Pending Allocations',
                'class' => 'allocation-group-branch-card',
                'target' => 'PendingAllocationsCard-branch-card'
            ],
            [
                'icon' => '📥',
                'count' => $this->getReceivedCount31Days($branchId),
                'subtitle' => 'Received (31 Days)',
                'class' => 'allocation-group-branch-card received-branch-card',
                'target' => 'ReceivedItemsCard-branch-card'
            ],
            [
                'icon' => '🔧',
                'count' => count($this->getPendingAdjustments($branchId)),
                'subtitle' => 'Pending Adjustments',
                'class' => 'adjustment-group-branch-card',
                'target' => 'AdjustmentsCard-branch-card'
            ],
            [
                'icon' => '📜',
                'count' => count($this->getAdjustmentHistory($branchId)),
                'subtitle' => 'Resolved Adjustments',
                'class' => 'adjustment-group-branch-card',
                'target' => 'AdjustmentsHistoryCard-branch-card'
            ],
            [
                'icon' => '⚠️',
                'count' => count($this->getPendingDisputes($branchId)),
                'subtitle' => 'Pending Disputes',
                'class' => 'dispute-group-branch-card',
                'target' => 'DisputesCard-branch-card'
            ],
            [
                'icon' => '📜',
                'count' => count($this->getDisputeHistory($branchId)),
                'subtitle' => 'Resolved Disputes',
                'class' => 'dispute-group-branch-card',
                'target' => 'DisputesHistoryCard-branch-card'
            ],
            [
                'icon' => '⇄',
                'count' => $this->getPendingOutgoingCount($branchId) + $this->getPendingIncomingCount($branchId),
                'subtitle' => 'Transfers (Pending)',
                'class' => 'transfer-group-branch-card',
                'target' => 'InternalTransferCard-branch-card'
            ],
            [
                'icon' => '📂',
                'count' => count($this->getInventorySummary($branchId)),
                'subtitle' => 'Inventory',
                'class' => 'inventory-group-branch-card',
                'target' => 'InventoryCard-branch-card'
            ]
        ];
    }
    
    /**
     * Get pending allocations
     */
    public function getPendingAllocations($branchId) {
        $sql = "
        SELECT d.*, i.name AS item_name, i.image_url, b.name AS batch_name
        FROM inventra_distributions d
        JOIN inventra_inventory_items i ON d.item_id = i.id
        JOIN inventra_batches b ON d.batch_id = b.id
        WHERE d.branch_id = ? AND d.status = 'pending'
        ORDER BY d.created_at DESC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$branchId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get received items
     */
    public function getReceivedItems($branchId) {
        $orderCol = $this->hasReceivedAt ? 'd.received_at' : 'd.created_at';
        $sql = "
        SELECT d.*, i.name AS item_name, i.image_url, b.name AS batch_name
        FROM inventra_distributions d
        JOIN inventra_inventory_items i ON d.item_id = i.id
        JOIN inventra_batches b ON d.batch_id = b.id
        WHERE d.branch_id = ? AND d.status = 'received'
        ORDER BY " . $orderCol . " DESC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$branchId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get received count (last 31 days)
     */
    public function getReceivedCount31Days($branchId) {
        $date_31_days_ago = date('Y-m-d H:i:s', strtotime('-31 days'));
        if ($this->hasReceivedAt) {
            $sql = "SELECT COUNT(*) FROM inventra_distributions WHERE branch_id = ? AND status = 'received' AND received_at >= ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$branchId, $date_31_days_ago]);
        } else {
            // Fallback: use created_at as an approximation when received_at column is missing
            $sql = "SELECT COUNT(*) FROM inventra_distributions WHERE branch_id = ? AND status = 'received' AND created_at >= ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$branchId, $date_31_days_ago]);
        }
        return (int)$stmt->fetchColumn();
    }
    
    /**
     * Get all adjustments
     */
    public function getAdjustments($branchId) {
        if ($this->hasAdjustmentDistribution) {
            $sql = "
            SELECT aj.*, i.name as item_name, i.image_url, b.name as batch_name, 
                   aj.created_at, aj.resolved_at
            FROM inventra_adjustments aj
            JOIN inventra_distributions d ON aj.distribution_id = d.id
            JOIN inventra_inventory_items i ON d.item_id = i.id
            JOIN inventra_batches b ON d.batch_id = b.id
            WHERE d.branch_id = ?
            ORDER BY aj.created_at DESC
            ";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$branchId]);
        } else {
            // Fallback when distribution_id column doesn't exist: use adjustment's branch/item fields
            $sql = "
            SELECT aj.*, i.name as item_name, i.image_url, NULL as batch_name, 
                   aj.created_at, aj.resolved_at
            FROM inventra_adjustments aj
            LEFT JOIN inventra_inventory_items i ON aj.item_id = i.id
            WHERE aj.branch_id = ?
            ORDER BY aj.created_at DESC
            ";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$branchId]);
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get pending adjustments
     */
    public function getPendingAdjustments($branchId) {
        $adjustments = $this->getAdjustments($branchId);
        return array_filter($adjustments, function($adj) {
            return $adj['status'] === 'pending';
        });
    }
    
    /**
     * Get adjustment history
     */
    public function getAdjustmentHistory($branchId) {
        $adjustments = $this->getAdjustments($branchId);
        return array_filter($adjustments, function($adj) {
            return in_array($adj['status'], ['approved', 'declined']);
        });
    }
    
    /**
     * Get all disputes
     */
    public function getDisputes($branchId) {
        // Build SQL depending on which foreign key exists on inventra_disputes
        if ($this->hasDisputeAllocation) {
            $sql = "
            SELECT ds.*, i.name as item_name, i.image_url, b.name as batch_name,
                   ds.created_at, ds.resolved_at
            FROM inventra_disputes ds
            LEFT JOIN inventra_allocations a ON ds.allocation_id = a.id
            LEFT JOIN inventra_inventory_items i ON a.item_id = i.id
            LEFT JOIN inventra_batches b ON a.batch_id = b.id
            WHERE COALESCE(a.branch_id, ds.branch_id) = ?
            ORDER BY ds.created_at DESC
            ";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$branchId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        if ($this->hasDisputeDistribution) {
            $sql = "
            SELECT ds.*, i.name as item_name, i.image_url, b.name as batch_name,
                   ds.created_at, ds.resolved_at
            FROM inventra_disputes ds
            LEFT JOIN inventra_distributions d ON ds.distribution_id = d.id
            LEFT JOIN inventra_inventory_items i ON d.item_id = i.id
            LEFT JOIN inventra_batches b ON d.batch_id = b.id
            WHERE COALESCE(d.branch_id, ds.branch_id) = ?
            ORDER BY ds.created_at DESC
            ";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$branchId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // Fallback: no FK columns detected, return disputes for branch without item/batch resolution
        $sql = "SELECT ds.*, NULL as item_name, NULL as image_url, NULL as batch_name, ds.created_at, ds.resolved_at FROM inventra_disputes ds WHERE ds.branch_id = ? ORDER BY ds.created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$branchId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get pending disputes
     */
    public function getPendingDisputes($branchId) {
        $disputes = $this->getDisputes($branchId);
        return array_filter($disputes, function($dispute) {
            return $dispute['status'] === 'pending';
        });
    }
    
    /**
     * Get dispute history
     */
    public function getDisputeHistory($branchId) {
        $disputes = $this->getDisputes($branchId);
        return array_filter($disputes, function($dispute) {
            return in_array($dispute['status'], ['approved', 'declined']);
        });
    }
    
    /**
     * Get transfers
     */
    public function getTransfers($branchId) {
        $sql = "
        SELECT bt.*, i.name AS item_name, i.image_url, b.name AS batch_name,
               u_to.username AS to_branch_name, u_from.username AS from_branch_name
        FROM inventra_branch_transfers bt
        JOIN inventra_inventory_items i ON bt.item_id = i.id
        JOIN inventra_batches b ON bt.batch_id = b.id
        JOIN users u_to ON bt.to_branch_id = u_to.id
        JOIN users u_from ON bt.from_branch_id = u_from.id
        WHERE bt.from_branch_id = ? OR bt.to_branch_id = ?
        ORDER BY bt.created_at DESC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$branchId, $branchId]);
        $all_transfers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'outgoing' => array_filter($all_transfers, function($t) use ($branchId) {
                return $t['from_branch_id'] == $branchId;
            }),
            'incoming' => array_filter($all_transfers, function($t) use ($branchId) {
                return $t['to_branch_id'] == $branchId;
            })
        ];
    }
    
    /**
     * Get outgoing transfers
     */
    public function getOutgoingTransfers($branchId) {
        $sql = "
        SELECT bt.*, i.name AS item_name, i.image_url, 
               u_to.username AS to_branch_name, b.name AS batch_name
        FROM inventra_branch_transfers bt
        JOIN inventra_inventory_items i ON bt.item_id = i.id
        JOIN inventra_batches b ON bt.batch_id = b.id
        JOIN users u_to ON bt.to_branch_id = u_to.id
        WHERE bt.from_branch_id = ?
        ORDER BY bt.created_at DESC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$branchId]);
        $outgoing = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return array_filter($outgoing, function($t) {
            return $t['status'] === 'pending';
        });
    }
    
    /**
     * Get pending outgoing count
     */
    public function getPendingOutgoingCount($branchId) {
        $outgoing = $this->getOutgoingTransfers($branchId);
        return count($outgoing);
    }
    
    /**
     * Get incoming transfers
     */
    public function getIncomingTransfers($branchId) {
         $orderCol = $this->hasTransferApprovedAt ? 'bt.approved_at' : 'bt.created_at';
         $sql = "
         SELECT bt.*, i.name AS item_name, i.image_url, 
             u_from.username AS from_branch_name, b.name AS batch_name
         FROM inventra_branch_transfers bt
         JOIN inventra_inventory_items i ON bt.item_id = i.id
         JOIN inventra_batches b ON bt.batch_id = b.id
         JOIN users u_from ON bt.from_branch_id = u_from.id
         WHERE bt.to_branch_id = ? AND bt.status IN ('approved', 'disputed')
         ORDER BY " . $orderCol . " DESC
         ";
         $stmt = $this->pdo->prepare($sql);
         $stmt->execute([$branchId]);
        $incoming = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return array_filter($incoming, function($t) {
            return $t['status'] === 'approved';
        });
    }
    
    /**
     * Get pending incoming count
     */
    public function getPendingIncomingCount($branchId) {
        $incoming = $this->getIncomingTransfers($branchId);
        return count($incoming);
    }
    
    /**
     * Get inventory summary
     */
    public function getInventorySummary($branchId) {
        // If the branch-level inventory table exists, use it; otherwise fall back to aggregating inventra_inventory
        $tableCheck = $this->pdo->query("SHOW TABLES LIKE 'inventra_branch_inventory'")->fetch();
        if ($tableCheck) {
            $sql = "
            SELECT
                i.id AS item_id,
                i.name AS item_name,
                i.image_url,
                b.id AS batch_id,
                b.name AS batch_name,
                COALESCE(bi.current_stock, 0) AS current_stock,
                COALESCE(bi.total_received, 0) AS total_received,
                COALESCE(adj_sub.total, 0) AS adjustments_subtracted,
                COALESCE(adj_add.total, 0) AS adjustments_added
            FROM inventra_branch_inventory bi
            JOIN inventra_inventory_items i ON bi.item_id = i.id
            JOIN inventra_batches b ON bi.batch_id = b.id
                    LEFT JOIN (
                            SELECT
                                    aj.item_id,
                                    SUM(aj.quantity) AS total
                            FROM inventra_adjustments aj
                            WHERE aj.adjustment_type IN ('redeem', 'damaged', 'stolen', 'missing', 'other (deduct)')
                                AND aj.status = 'approved'
                                AND aj.branch_id = ?
                            GROUP BY aj.item_id
                    ) adj_sub ON bi.item_id = adj_sub.item_id
                    LEFT JOIN (
                            SELECT
                                    aj.item_id,
                                    SUM(aj.quantity) AS total
                            FROM inventra_adjustments aj
                            WHERE aj.adjustment_type IN ('found', 'other (add)')
                                AND aj.status = 'approved'
                                AND aj.branch_id = ?
                            GROUP BY aj.item_id
                    ) adj_add ON bi.item_id = adj_add.item_id
            WHERE bi.branch_id = ?
            ORDER BY i.name, b.name
            ";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$branchId, $branchId, $branchId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            // Fallback: aggregate from inventra_inventory
            $sql = "
            SELECT
                i.id AS item_id,
                i.name AS item_name,
                i.image_url,
                inv.batch_id AS batch_id,
                b.name AS batch_name,
                COALESCE(SUM(inv.quantity), 0) AS current_stock,
                COALESCE(SUM(inv.quantity), 0) AS total_received,
                COALESCE(adj_sub.total, 0) AS adjustments_subtracted,
                COALESCE(adj_add.total, 0) AS adjustments_added
            FROM inventra_inventory inv
            JOIN inventra_inventory_items i ON inv.item_id = i.id
            LEFT JOIN inventra_batches b ON inv.batch_id = b.id
                    LEFT JOIN (
                            SELECT
                                    aj.item_id,
                                    SUM(aj.quantity) AS total
                            FROM inventra_adjustments aj
                            WHERE aj.adjustment_type IN ('redeem', 'damaged', 'stolen', 'missing', 'other (deduct)')
                                AND aj.status = 'approved'
                                AND aj.branch_id = ?
                            GROUP BY aj.item_id
                    ) adj_sub ON inv.item_id = adj_sub.item_id
                    LEFT JOIN (
                            SELECT
                                    aj.item_id,
                                    SUM(aj.quantity) AS total
                            FROM inventra_adjustments aj
                            WHERE aj.adjustment_type IN ('found', 'other (add)')
                                AND aj.status = 'approved'
                                AND aj.branch_id = ?
                            GROUP BY aj.item_id
                    ) adj_add ON inv.item_id = adj_add.item_id
            WHERE inv.branch_id = ?
            GROUP BY inv.item_id, inv.batch_id
            ORDER BY i.name, b.name
            ";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$branchId, $branchId, $branchId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    /**
     * Get current branch inventory for transfers
     */
    public function getCurrentBranchInventory($branchId) {
        $tableCheck = $this->pdo->query("SHOW TABLES LIKE 'inventra_branch_inventory'")->fetch();
        if ($tableCheck) {
            $sql = "
            SELECT
                i.id AS item_id,
                i.name AS item_name,
                b.id AS batch_id,
                b.name AS batch_name,
                bi.current_stock
            FROM inventra_branch_inventory bi
            JOIN inventra_inventory_items i ON bi.item_id = i.id
            JOIN inventra_batches b ON bi.batch_id = b.id
            WHERE bi.branch_id = ? AND bi.current_stock > 0
            ORDER BY i.name, b.name
            ";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$branchId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            // Fallback: aggregate from inventra_inventory and return positive quantities
            $sql = "
            SELECT
                i.id AS item_id,
                i.name AS item_name,
                inv.batch_id AS batch_id,
                b.name AS batch_name,
                SUM(inv.quantity) AS current_stock
            FROM inventra_inventory inv
            JOIN inventra_inventory_items i ON inv.item_id = i.id
            LEFT JOIN inventra_batches b ON inv.batch_id = b.id
            WHERE inv.branch_id = ?
            GROUP BY inv.item_id, inv.batch_id
            HAVING current_stock > 0
            ORDER BY i.name, b.name
            ";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$branchId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    /**
     * Get dashboard metrics and time series for branch (prototype)
     */
    public function getDashboardMetrics($branchId, $days = 31) {
        // Basic counts (re-using existing methods)
        $pendingAllocations = count($this->getPendingAllocations($branchId));
        $pendingAdjustments = count($this->getPendingAdjustments($branchId));
        $pendingDisputes = count($this->getPendingDisputes($branchId));
        $pendingIncoming = $this->getPendingIncomingCount($branchId);
        $pendingOutgoing = $this->getPendingOutgoingCount($branchId);

        $receivedSeries = $this->getReceivedTimeSeries($branchId, $days); // last $days days
        $adjustmentsSeries = $this->getAdjustmentsTimeSeries($branchId, $days);

        // recent received rows for table (last 50)
        $orderCol = $this->hasReceivedAt ? 'COALESCE(d.received_at, d.created_at)' : 'd.created_at';
        $sql = "SELECT d.*, i.name as item_name, b.name as batch_name FROM inventra_distributions d JOIN inventra_inventory_items i ON d.item_id = i.id LEFT JOIN inventra_batches b ON d.batch_id = b.id WHERE d.branch_id = ? AND d.status = 'received' ORDER BY " . $orderCol . " DESC LIMIT 50";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$branchId]);
        $recentReceived = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'counts' => [
                'pending_allocations' => $pendingAllocations,
                'pending_adjustments' => $pendingAdjustments,
                'pending_disputes' => $pendingDisputes,
                'pending_incoming' => $pendingIncoming,
                'pending_outgoing' => $pendingOutgoing
            ],
            'series' => [
                'received' => $receivedSeries,
                'adjustments' => $adjustmentsSeries
            ],
            'recent_received' => $recentReceived
        ];
    }

    /**
     * Get received items count per day for the last N days
     */
    private function getReceivedTimeSeries($branchId, $days = 31) {
        $col = $this->hasReceivedAt ? 'received_at' : 'created_at';
        $sql = "SELECT DATE($col) as dt, COUNT(*) as cnt FROM inventra_distributions WHERE branch_id = ? AND status = 'received' AND $col >= DATE_SUB(CURDATE(), INTERVAL ? DAY) GROUP BY DATE($col) ORDER BY DATE($col) ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$branchId, $days]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Build full day series for last $days days
        $series = [];
        $start = new DateTime();
        $start->modify("-" . ($days - 1) . " days");
        $map = [];
        foreach ($rows as $r) { $map[$r['dt']] = (int)$r['cnt']; }
        for ($i = 0; $i < $days; $i++) {
            $d = $start->format('Y-m-d');
            $series[] = ['date' => $d, 'count' => $map[$d] ?? 0];
            $start->modify('+1 day');
        }
        return $series;
    }

    /**
     * Get adjustments count per day for the last N days (approved + pending)
     */
    private function getAdjustmentsTimeSeries($branchId, $days = 31) {
        $sql = "SELECT DATE(created_at) as dt, COUNT(*) as cnt FROM inventra_adjustments WHERE branch_id = ? AND created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY) GROUP BY DATE(created_at) ORDER BY DATE(created_at) ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$branchId, $days]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $series = [];
        $start = new DateTime();
        $start->modify("-" . ($days - 1) . " days");
        $map = [];
        foreach ($rows as $r) { $map[$r['dt']] = (int)$r['cnt']; }
        for ($i = 0; $i < $days; $i++) {
            $d = $start->format('Y-m-d');
            $series[] = ['date' => $d, 'count' => $map[$d] ?? 0];
            $start->modify('+1 day');
        }
        return $series;
    }

    /**
     * Get all branches for transfer dropdown
     */
    public function getAllBranchesForTransfer($branchId) {
        $sql = "
        SELECT u.id, u.username
        FROM users u
        LEFT JOIN role_user ru ON ru.user_id = u.id
        LEFT JOIN roles r ON r.id = ru.role_id
        WHERE u.id != ? AND (u.user_type IN ('branch','branch_user') OR r.slug = 'um_branch')
        GROUP BY u.id
        ORDER BY u.username ASC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$branchId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
