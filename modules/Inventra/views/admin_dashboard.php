<div id="inventra-admin-dashboard-standalone">
    <h1>Admin Dashboard</h1>
    <p>Welcome, <?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?>.</p>

    <!-- Top menu buttons (modern) -->
    <div class="inventra-top-menu" role="toolbar" aria-label="Admin quick menu">
        <button class="top-menu-btn active" data-target="AllocationCard-standalone">Allocations <span class="tm-count"><?= (int)count($pending_allocations) ?></span></button>
        <button class="top-menu-btn" data-target="AdjustmentsCard-standalone">Adjustments <span class="tm-count"><?= (int)count($pending_adjustments) ?></span></button>
        <button class="top-menu-btn" data-target="DisputesCard-standalone">Disputes <span class="tm-count"><?= (int)count($pending_disputes) ?></span></button>
        <button class="top-menu-btn" data-target="BranchTransfersCard-standalone">Transfers <span class="tm-count"><?= (int)count($pending_branch_transfers) ?></span></button>
        <button class="top-menu-btn" data-target="TotalInventoryCard-standalone">All Inventory</button>
        <button id="open-allocate-modal-btn-standalone" class="top-menu-btn btn-allocate-standalone" title="Create Allocation"><span class="icon">+</span> Create Allocation</button>
    </div>
    <p>Select a menu item from the left sidebar or top menu to view related data tables.</p>
</div>

    <!-- Pending Allocations Content -->
    <div id="AllocationCard-standalone" class="collapsible-table-wrapper-standalone">
        <h2 style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
            <span>Pending Allocations</span>
            <button class="open-allocate-modal-standalone btn-allocate-standalone" style="margin-left:auto;">+ Create Allocation</button>
        </h2>
        <label>Filter by Branch: </label>
        <select class="branch-filter-standalone" data-table="table-pending-allocations-standalone">
            <option value="">All Branches</option>
            <?php foreach ($branches as $b): ?>
                <option value="<?= htmlspecialchars($b->user_login) ?>"><?= htmlspecialchars($b->user_login) ?></option>
            <?php endforeach; ?>
        </select>
        <?php if (!empty($pending_allocations)): ?>
            <table id="table-pending-allocations-standalone" class="wp-list-table-standalone widefat striped" style="width:100%">
                <thead>
                    <tr>
                        <th>Date Created</th>
                        <th>Branch</th>
                        <th>Item</th>
                        <th>Batch</th>
                        <th>Quantity</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending_allocations as $alloc): ?>
                        <tr>
                            <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($alloc['created_at']))) ?></td>
                            <td><?= htmlspecialchars($alloc['branch_name']) ?></td>
                            <td>
                                <?php if ($alloc['image_url']): ?>
                                    <img src="<?= htmlspecialchars($alloc['image_url']) ?>" class="item-thumb-standalone" alt="" style="width:35px;height:35px;object-fit:contain;">
                                <?php endif; ?>
                                <?= htmlspecialchars($alloc['item_name']) ?>
                            </td>
                            <td><?= htmlspecialchars($alloc['batch_name']) ?></td>
                            <td><?= (int)$alloc['quantity'] ?></td>
                            <td><?= htmlspecialchars($alloc['status']) ?></td>
                            <td>
                                <button class="approve-btn-standalone" data-id="<?= $alloc['id'] ?>" data-type="allocation">Approve</button>
                                <button class="decline-btn-standalone" data-id="<?= $alloc['id'] ?>" data-type="allocation">Decline</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No pending allocations.</p>
        <?php endif; ?>
    </div>

    <!-- Resolved Allocations Content -->
    <div id="ResolvedAllocationCard-standalone" class="collapsible-table-wrapper-standalone" style="display:none;">
        <h2>Resolved Allocations (History)</h2>
        <label>Filter by Branch: </label>
        <select class="branch-filter-standalone" data-table="table-resolved-allocations-standalone">
            <option value="">All Branches</option>
            <?php foreach ($branches as $b): ?>
                <option value="<?= htmlspecialchars($b->user_login) ?>"><?= htmlspecialchars($b->user_login) ?></option>
            <?php endforeach; ?>
        </select>
        <?php if (!empty($resolved_allocations)): ?>
            <table id="table-resolved-allocations-standalone" class="wp-list-table-standalone widefat striped" style="width:100%">
                <thead>
                    <tr>
                        <th>Date Resolved</th>
                        <th>Branch</th>
                        <th>Item</th>
                        <th>Batch</th>
                        <th>Quantity</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($resolved_allocations as $alloc): ?>
                        <tr>
                            <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($alloc['resolved_at']))) ?></td>
                            <td><?= htmlspecialchars($alloc['branch_name']) ?></td>
                            <td>
                                <?php if ($alloc['image_url']): ?>
                                    <img src="<?= htmlspecialchars($alloc['image_url']) ?>" class="item-thumb-standalone" alt="" style="width:35px;height:35px;object-fit:contain;">
                                <?php endif; ?>
                                <?= htmlspecialchars($alloc['item_name']) ?>
                            </td>
                            <td><?= htmlspecialchars($alloc['batch_name']) ?></td>
                            <td><?= (int)$alloc['quantity'] ?></td>
                            <td><?= htmlspecialchars($alloc['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No resolved allocations.</p>
        <?php endif; ?>
    </div>

    <!-- Pending Adjustments Content -->
    <div id="AdjustmentsCard-standalone" class="collapsible-table-wrapper-standalone" style="display:none;">
        <h2>Pending Adjustments</h2>
        <label>Filter by Branch: </label>
        <select class="branch-filter-standalone" data-table="table-pending-adjustments-standalone">
            <option value="">All Branches</option>
            <?php foreach ($branches as $b): ?>
                <option value="<?= htmlspecialchars($b->user_login) ?>"><?= htmlspecialchars($b->user_login) ?></option>
            <?php endforeach; ?>
        </select>
        <?php if (!empty($pending_adjustments)): ?>
            <table id="table-pending-adjustments-standalone" class="wp-list-table-standalone widefat striped" style="width:100%">
                <thead>
                    <tr>
                        <th>Date Created</th>
                        <th>Branch</th>
                        <th>Item</th>
                        <th>Batch</th>
                        <th>Type</th>
                        <th>Quantity</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending_adjustments as $adj): ?>
                        <tr>
                            <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($adj['created_at']))) ?></td>
                            <td><?= htmlspecialchars($adj['branch_name']) ?></td>
                            <td>
                                <?php if ($adj['image_url']): ?>
                                    <img src="<?= htmlspecialchars($adj['image_url']) ?>" class="item-thumb-standalone" alt="" style="width:35px;height:35px;object-fit:contain;">
                                <?php endif; ?>
                                <?= htmlspecialchars($adj['item_name']) ?>
                            </td>
                            <td><?= htmlspecialchars($adj['batch_name']) ?></td>
                            <td><?= htmlspecialchars($adj['adjustment_type']) ?></td>
                            <td><?= (int)$adj['quantity'] ?></td>
                            <td><?= htmlspecialchars($adj['reason']) ?></td>
                            <td><?= htmlspecialchars($adj['status']) ?></td>
                            <td>
                                <button class="approve-btn-standalone" data-id="<?= $adj['id'] ?>" data-type="adjustment">Approve</button>
                                <button class="decline-btn-standalone" data-id="<?= $adj['id'] ?>" data-type="adjustment">Decline</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No pending adjustments.</p>
        <?php endif; ?>
    </div>

    <!-- Adjustment History Content -->
    <div id="AdjustmentsHistoryCard-standalone" class="collapsible-table-wrapper-standalone" style="display:none;">
        <h2>Adjustment History</h2>
        <label>Filter by Branch: </label>
        <select class="branch-filter-standalone" data-table="table-adjustments-history-standalone">
            <option value="">All Branches</option>
            <?php foreach ($branches as $b): ?>
                <option value="<?= htmlspecialchars($b->user_login) ?>"><?= htmlspecialchars($b->user_login) ?></option>
            <?php endforeach; ?>
        </select>
        <?php if (!empty($adjustment_history)): ?>
            <table id="table-adjustments-history-standalone" class="wp-list-table-standalone widefat striped" style="width:100%">
                <thead>
                    <tr>
                        <th>Date Resolved</th>
                        <th>Status</th>
                        <th>Branch</th>
                        <th>Item</th>
                        <th>Type</th>
                        <th>Qty</th>
                        <th>Reason</th>
                        <th>Staff</th>
                        <th>Batch</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($adjustment_history as $a): ?>
                        <tr>
                            <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($a['resolved_at']))) ?></td>
                            <td>
                                <?php if ($a['status'] === 'accepted'): ?>
                                    <span style="color: green; font-weight: bold;">Accepted</span>
                                <?php elseif ($a['status'] === 'declined'): ?>
                                    <span style="color: red; font-weight: bold;">Declined</span>
                                <?php else: ?>
                                    <?= htmlspecialchars(ucfirst($a['status'])) ?>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($a['branch_name']) ?></td>
                            <td>
                                <?php if ($a['image_url']): ?>
                                    <img src="<?= htmlspecialchars($a['image_url']) ?>" class="item-thumb-standalone" alt="" style="width:35px;height:35px;object-fit:contain;">
                                <?php endif; ?>
                                <?= htmlspecialchars($a['item_name']) ?>
                            </td>
                            <td><?= htmlspecialchars($a['adjustment_type']) ?></td>
                            <td><?= (int)$a['quantity'] ?></td>
                            <td><?= htmlspecialchars($a['reason']) ?></td>
                            <td><?= htmlspecialchars($a['staff_name'] ?: 'N/A') ?></td>
                            <td><?= htmlspecialchars($a['batch_name']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No adjustment history found.</p>
        <?php endif; ?>
    </div>

    <!-- Pending Disputes Content -->
    <div id="DisputesCard-standalone" class="collapsible-table-wrapper-standalone" style="display:none;">
        <h2>Pending Disputes</h2>
        <label>Filter by Branch: </label>
        <select class="branch-filter-standalone" data-table="table-pending-disputes-standalone">
            <option value="">All Branches</option>
            <?php foreach ($branches as $b): ?>
                <option value="<?= htmlspecialchars($b->user_login) ?>"><?= htmlspecialchars($b->user_login) ?></option>
            <?php endforeach; ?>
        </select>
        <?php if (!empty($pending_disputes)): ?>
            <table id="table-pending-disputes-standalone" class="wp-list-table-standalone widefat striped" style="width:100%">
                <thead>
                    <tr>
                        <th>Date Created</th>
                        <th>Branch</th>
                        <th>Item</th>
                        <th>Disputed Qty</th>
                        <th>Reason</th>
                        <th>Staff</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending_disputes as $d): ?>
                        <tr>
                            <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($d['created_at']))) ?></td>
                            <td><?= htmlspecialchars($d['branch_name']) ?></td>
                            <td>
                                <?php if ($d['image_url']): ?>
                                    <img src="<?= htmlspecialchars($d['image_url']) ?>" class="item-thumb-standalone" alt="" style="width:35px;height:35px;object-fit:contain;">
                                <?php endif; ?>
                                <?= htmlspecialchars($d['item_name']) ?>
                            </td>
                            <td><?= (int)$d['disputed_qty'] ?></td>
                            <td><?= htmlspecialchars($d['reason']) ?></td>
                            <td><?= htmlspecialchars($d['staff_name'] ?: '—') ?></td>
                            <td>
                                <button class="resolve-dispute-btn-standalone" data-id="<?= $d['id'] ?>">Resolve</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No pending disputes.</p>
        <?php endif; ?>
    </div>

    <!-- Dispute History Content -->
    <div id="DisputesHistoryCard-standalone" class="collapsible-table-wrapper-standalone" style="display:none;">
        <h2>Dispute History</h2>
        <label>Filter by Branch: </label>
        <select class="branch-filter-standalone" data-table="table-disputes-history-standalone">
            <option value="">All Branches</option>
            <?php foreach ($branches as $b): ?>
                <option value="<?= htmlspecialchars($b->user_login) ?>"><?= htmlspecialchars($b->user_login) ?></option>
            <?php endforeach; ?>
        </select>
        <?php if (!empty($dispute_history)): ?>
            <table id="table-disputes-history-standalone" class="wp-list-table-standalone widefat striped" style="width:100%">
                <thead>
                    <tr>
                        <th>Date Resolved</th>
                        <th>Branch</th>
                        <th>Item</th>
                        <th>Disputed Qty</th>
                        <th>Reason</th>
                        <th>Staff</th>
                        <th>Resolved By</th>
                        <th>Resolution</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dispute_history as $d): ?>
                        <tr>
                            <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($d['resolved_at']))) ?></td>
                            <td><?= htmlspecialchars($d['branch_name']) ?></td>
                            <td>
                                <?php if ($d['image_url']): ?>
                                    <img src="<?= htmlspecialchars($d['image_url']) ?>" class="item-thumb-standalone" alt="" style="width:35px;height:35px;object-fit:contain;">
                                <?php endif; ?>
                                <?= htmlspecialchars($d['item_name']) ?>
                            </td>
                            <td><?= (int)$d['disputed_qty'] ?></td>
                            <td><?= htmlspecialchars($d['reason']) ?></td>
                            <td><?= htmlspecialchars($d['staff_name'] ?: '—') ?></td>
                            <td><?= htmlspecialchars($d['resolved_by_staff_name'] ?: 'System') ?></td>
                            <td><?= htmlspecialchars($d['resolution']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No dispute history found.</p>
        <?php endif; ?>
    </div>

    <!-- Branch Transfers Content -->
    <div id="BranchTransfersCard-standalone" class="collapsible-table-wrapper-standalone" style="display:none;">
        <!-- Pending Transfers -->
        <h2>Pending Branch Transfers</h2>
        <label>Filter by Branch (From/To): </label>
        <select class="branch-filter-standalone" data-table="table-pending-transfers-standalone">
            <option value="">All</option>
            <?php foreach ($branches as $b): ?>
                <option value="<?= htmlspecialchars($b->user_login) ?>"><?= htmlspecialchars($b->user_login) ?></option>
            <?php endforeach; ?>
        </select>
        <?php if (!empty($pending_branch_transfers)): ?>
            <table id="table-pending-transfers-standalone" class="wp-list-table-standalone widefat striped" style="width:100%">
                <thead>
                    <tr>
                        <th>Date Created</th>
                        <th>From Branch</th>
                        <th>To Branch</th>
                        <th>Item</th>
                        <th>Batch</th>
                        <th>Quantity</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending_branch_transfers as $t): ?>
                        <tr>
                            <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($t['created_at']))) ?></td>
                            <td><?= htmlspecialchars($t['from_branch_name']) ?></td>
                            <td><?= htmlspecialchars($t['to_branch_name']) ?></td>
                            <td>
                                <?php if ($t['image_url']): ?>
                                    <img src="<?= htmlspecialchars($t['image_url']) ?>" class="item-thumb-standalone" alt="" style="width:35px;height:35px;object-fit:contain;">
                                <?php endif; ?>
                                <?= htmlspecialchars($t['item_name']) ?>
                            </td>
                            <td><?= htmlspecialchars($t['batch_name']) ?></td>
                            <td><?= (int)$t['quantity'] ?></td>
                            <td><?= htmlspecialchars($t['reason']) ?></td>
                            <td><?= htmlspecialchars($t['status']) ?></td>
                            <td>
                                <button class="approve-btn-standalone" data-id="<?= $t['id'] ?>" data-type="transfer">Approve</button>
                                <button class="decline-btn-standalone" data-id="<?= $t['id'] ?>" data-type="transfer">Decline</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No pending branch transfers.</p>
        <?php endif; ?>

        <!-- Transfer History -->
        <h2>Transfer History</h2>
        <label>Filter by Branch (From/To): </label>
        <select class="branch-filter-standalone" data-table="table-transfer-history-standalone">
            <option value="">All</option>
            <?php foreach ($branches as $b): ?>
                <option value="<?= htmlspecialchars($b->user_login) ?>"><?= htmlspecialchars($b->user_login) ?></option>
            <?php endforeach; ?>
        </select>
        <?php if (!empty($transfer_history)): ?>
            <table id="table-transfer-history-standalone" class="wp-list-table-standalone widefat striped" style="width:100%">
                <thead>
                    <tr>
                        <th>Date Resolved/Received</th>
                        <th>Status</th>
                        <th>From Branch</th>
                        <th>To Branch</th>
                        <th>Item</th>
                        <th>Batch</th>
                        <th>Quantity</th>
                        <th>Received By</th>
                        <th>Reason</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transfer_history as $t): ?>
                        <tr>
                            <td><?= $t['received_at'] ? htmlspecialchars(date('Y-m-d H:i', strtotime($t['received_at']))) : htmlspecialchars(date('Y-m-d H:i', strtotime($t['resolved_at']))) ?></td>
                            <td><?= htmlspecialchars($t['status']) ?></td>
                            <td><?= htmlspecialchars($t['from_branch_name']) ?></td>
                            <td><?= htmlspecialchars($t['to_branch_name']) ?></td>
                            <td>
                                <?php if ($t['image_url']): ?>
                                    <img src="<?= htmlspecialchars($t['image_url']) ?>" class="item-thumb-standalone" alt="" style="width:35px;height:35px;object-fit:contain;">
                                <?php endif; ?>
                                <?= htmlspecialchars($t['item_name']) ?>
                            </td>
                            <td><?= htmlspecialchars($t['batch_name']) ?></td>
                            <td><?= (int)$t['quantity'] ?></td>
                            <td><?= htmlspecialchars($t['received_by_staff_name'] ?: 'N/A') ?></td>
                            <td><?= htmlspecialchars($t['reason'] ?: 'N/A') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No transfer history found.</p>
        <?php endif; ?>

        <!-- Received History -->
        <h2>Received Items (History)</h2>
        <label>Filter by Branch: </label>
        <select class="branch-filter-standalone" data-table="table-received-standalone">
            <option value="">All Branches</option>
            <?php foreach ($branches as $b): ?>
                <option value="<?= htmlspecialchars($b->user_login) ?>"><?= htmlspecialchars($b->user_login) ?></option>
            <?php endforeach; ?>
        </select>
        <?php if (!empty($received)): ?>
            <table id="table-received-standalone" class="wp-list-table-standalone widefat striped" style="width:100%">
                <thead>
                    <tr>
                        <th>Date (Created)</th>
                        <th>Branch</th>
                        <th>Item</th>
                        <th>Allocated</th>
                        <th>Batch</th>
                        <th>Received</th>
                        <th>Staff</th>
                        <th>Received At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($received as $r): ?>
                        <tr>
                            <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($r['created_at']))) ?></td>
                            <td><?= htmlspecialchars($r['branch_name']) ?></td>
                            <td>
                                <?php if ($r['image_url']): ?>
                                    <img src="<?= htmlspecialchars($r['image_url']) ?>" class="item-thumb-standalone" alt="" style="width:35px;height:35px;object-fit:contain;">
                                <?php endif; ?>
                                <?= htmlspecialchars($r['item_name']) ?>
                            </td>
                            <td><?= (int)$r['allocated'] ?></td>
                            <td><?= htmlspecialchars($r['batch_name'] ?: 'N/A') ?></td>
                            <td><?= (int)$r['received'] ?></td>
                            <td><?= htmlspecialchars($r['staff_name'] ?: '—') ?></td>
                            <td><?= $r['received_at'] ? htmlspecialchars(date('Y-m-d H:i', strtotime($r['received_at']))) : '-' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No items received yet.</p>
        <?php endif; ?>
    </div>

    <!-- Total Inventory Card -->
    <div id="TotalInventoryCard-standalone" class="collapsible-table-wrapper-standalone" style="display:none;">
        <h2>Complete Branch Inventory (All Branches & Items)</h2>
        <label for="branch-filter-total">Filter by Branch:</label>
        <select id="branch-filter-total" class="branch-filter-standalone" data-table="table-inventory-pivot-standalone">
            <option value="">All Branches</option>
            <?php foreach ($branches as $b): ?>
                <option value="<?= htmlspecialchars($b->user_login) ?>"><?= htmlspecialchars($b->user_login) ?></option>
            <?php endforeach; ?>
        </select>
        <?php if (empty($inventory_pivot['data'])): ?>
            <p>No branches or items found.</p>
        <?php else: ?>
            <div style="overflow-x: auto; -webkit-overflow-scrolling: touch; width: 100%;">
                <table id="table-inventory-pivot-standalone" class="wp-list-table-standalone widefat striped" style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr>
                            <th style="min-width:180px;">Branch</th>
                            <?php foreach ($inventory_pivot['items'] as $item): ?>
                                <th style="white-space:nowrap; text-align:center;">
                                    <?php if ($item['image_url']): ?>
                                        <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" style="width:28px; height:28px; object-fit:contain; vertical-align:middle; margin-right:4px;">
                                    <?php endif; ?>
                                    <span style="font-size:0.9em;"><?= htmlspecialchars($item['name']) ?></span>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inventory_pivot['data'] as $row): ?>
                            <tr>
                                <td style="font-weight:600;"><?= htmlspecialchars($row['branch_name']) ?></td>
                                <?php foreach ($inventory_pivot['items'] as $item): ?>
                                    <td style="text-align: center; vertical-align: middle; border: 1px solid #ddd;">
                                        Allocated: <?= $row[$item['id']]['allocated'] ?? 0 ?><br>
                                        Received: <?= $row[$item['id']]['received'] ?? 0 ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Allocation Modal -->
    <div id="allocate-modal-standalone" style="display:none; position:fixed; top:10%; left:50%; transform:translateX(-50%); width:90%; max-width:900px; background:#fff; padding:20px; box-shadow:0 0 15px rgba(0,0,0,0.3); border-radius:8px; z-index:9999;">
        <h2>Allocate Items to Branches</h2>
        <form id="form-allocate-standalone">
            <div style="display:flex; flex-wrap:wrap; gap:20px; margin-bottom:20px;">
                <div style="flex:1; min-width:200px;">
                    <label><strong>Item:</strong></label>
                    <select name="item" required style="width:100%; padding:6px; border-radius:4px; border:1px solid #ccc;">
                        <option value="">Select Item</option>
                        <?php foreach ($items as $item): ?>
                            <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="flex:1; min-width:200px;">
                    <label><strong>Batch:</strong></label>
                    <select name="batch" required style="width:100%; padding:6px; border-radius:4px; border:1px solid #ccc;">
                        <option value="">Select Batch</option>
                        <?php foreach ($batches as $batch): ?>
                            <option value="<?= $batch['id'] ?>"><?= htmlspecialchars($batch['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="flex:1; border:1px solid #ddd; padding:10px; border-radius:6px; max-height:300px; overflow-y:auto;">
                    <label><strong>Select Branches:</strong></label>
                    <label><input type="checkbox" id="select-all-branches-standalone"> <strong>Select All Branches</strong></label>
                    <hr>
                    <?php foreach ($branches as $b): ?>
                        <label style="display:block; margin-bottom:6px;">
                            <input type="checkbox" name="branches[]" value="<?= $b->id ?>"> <?= htmlspecialchars($b->username) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <div style="margin-top:20px; max-width:160px;">
                <label><strong>Quantity:</strong></label>
                <input type="number" name="quantity" min="1" value="1" required style="width:140px; padding:6px; border-radius:4px; border:1px solid #ccc;">
            </div>
            <div style="margin-top:20px;">
                <button type="submit" class="btn btn-primary">Submit Allocation</button>
                <button type="button" id="close-allocate-modal-btn-standalone" class="btn btn-secondary">Cancel</button>
            </div>
        </form>
    </div>

</div>

<!-- Embedded CSS -->
<style>
.card-container-standalone {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}
/* Top menu styles */
.inventra-top-menu{display:flex;flex-wrap:wrap;gap:10px;margin:10px 0 18px 0}
.top-menu-btn{background:#fff;border:1px solid #e2e8f0;padding:8px 12px;border-radius:8px;cursor:pointer;color:#0f1724;font-weight:600;display:inline-flex;align-items:center;gap:8px;box-shadow:0 1px 2px rgba(2,6,23,0.04)}
.top-menu-btn .tm-count{background:#0b84ff;color:#fff;padding:2px 8px;border-radius:999px;font-size:12px;margin-left:6px}
.top-menu-btn.active{background:linear-gradient(90deg,#0b84ff10,#0b84ff05);border-color:#0b84ff;color:#05284d}
.top-menu-btn:focus{outline:3px solid rgba(11,132,255,0.14)}
.card-standalone {
    background-color: #f9f9f9;
    padding: 20px;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.3s;
    border: 1px solid #ddd;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
}
.card-standalone.active {
    background-color: #e6f3ff;
    border-color: #007cba;
}
.card-icon-standalone {
    font-size: 2em;
    margin-bottom: 10px;
}
.card-title-standalone {
    font-weight: bold;
    margin: 0;
    font-size: 1.1em;
}
.card-subtitle-standalone {
    margin: 5px 0 0 0;
    color: #666;
    font-size: 0.9em;
}
.collapsible-table-wrapper-standalone {
    margin-top: 20px;
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 6px;
    background-color: #fafafa;
}
.wp-list-table-standalone {
    width: 100%;
    border-collapse: collapse;
}
.wp-list-table-standalone th,
.wp-list-table-standalone td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
}
.item-thumb-standalone {
    max-width: 50px;
    max-height: 50px;
}
.btn-allocate-standalone {
    margin-top: 10px;
    padding: 8px 12px;
    background-color: #007cba;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}
.btn-allocate-standalone:hover {
    background-color: #005a87;
}
#allocate-modal-standalone {
    display: none; /* Initially hidden */
}
</style>

<!-- Embedded JavaScript -->
<script>
(function(){
    function adminInit(){
        if (window._inventra_admin_initialized) return;
        window._inventra_admin_initialized = true;

        const token = window._inventra_csrf || '';

        function postAdminAction(payload, cb){
            payload._wpnonce = payload._wpnonce || token;
            const body = new URLSearchParams(payload).toString();
            fetch('<?= BASE_URL ?>/index.php?action=admin_action', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body: body, credentials: 'same-origin' })
                .then(r => r.json()).then(cb).catch(e=>{ console.error(e); cb({ success:false, message: String(e) }); });
        }

        function confirmAction(title, text, confirmLabel, cb){
            if (typeof Swal !== 'undefined'){
                Swal.fire({ title: title, text: text, showCancelButton: true, confirmButtonText: confirmLabel }).then(function(res){ if (res.isConfirmed) cb(); });
            } else {
                if (confirm(text)) cb();
            }
        }

        // Delegated click handler
        document.addEventListener('click', function(e){
                // Top menu button clicks
                const topBtn = e.target.closest('.top-menu-btn');
                if (topBtn){
                    const targetId = topBtn.getAttribute('data-target');
                    if (!targetId) return;
                    document.querySelectorAll('.top-menu-btn').forEach(b=>b.classList.remove('active'));
                    topBtn.classList.add('active');
                    document.querySelectorAll('.collapsible-table-wrapper-standalone').forEach(s => s.style.display = 'none');
                    const section = document.getElementById(targetId);
                    if (section) section.style.display = 'block';
                    // scroll into view for large pages
                    if (section && section.scrollIntoView) section.scrollIntoView({behavior:'smooth', block:'start'});
                    return;
                }
            const card = e.target.closest('.card-standalone');
            if (card){
                if (card.classList.contains('allocate-group')) return;
                const targetId = card.getAttribute('data-target');
                if (!targetId) return;
                // toggle active classes
                document.querySelectorAll('.card-standalone').forEach(c => c.classList.remove('active'));
                document.querySelectorAll('.collapsible-table-wrapper-standalone').forEach(s => s.style.display = 'none');
                card.classList.add('active');
                const targetSection = document.getElementById(targetId);
                if (targetSection) targetSection.style.display = 'block';
                return;
            }

            const openBtn = e.target.closest('#open-allocate-modal-btn-standalone, .open-allocate-modal-standalone');
            if (openBtn){ const modal = document.getElementById('allocate-modal-standalone'); if (modal) modal.style.display = 'block'; return; }

            const closeBtn = e.target.closest('#close-allocate-modal-btn-standalone');
            if (closeBtn){ const modal = document.getElementById('allocate-modal-standalone'); if (modal) modal.style.display = 'none'; return; }

            const approve = e.target.closest('.approve-btn-standalone');
            if (approve){
                const id = approve.getAttribute('data-id');
                const type = approve.getAttribute('data-type');
                confirmAction('Confirm Approval', 'Approve this '+type+' (ID: '+id+')?', 'Approve', function(){
                    var payload = {};
                    if (type === 'allocation') payload = { action_type: 'approve_allocation', allocation_id: id };
                    if (type === 'adjustment') payload = { action_type: 'approve_adjustment', adjustment_id: id };
                    if (type === 'dispute') payload = { action_type: 'approve_dispute', dispute_id: id };
                    if (type === 'transfer') payload = { action_type: 'approve_transfer', transfer_id: id };
                    postAdminAction(payload, function(res){ if (res.success) location.reload(); else alert(res.message || 'Error'); });
                });
                return;
            }

            const decline = e.target.closest('.decline-btn-standalone');
            if (decline){
                const id = decline.getAttribute('data-id');
                const type = decline.getAttribute('data-type');
                if (typeof Swal !== 'undefined'){
                    Swal.fire({title:'Decline '+type, input:'textarea', inputPlaceholder:'Reason for declining (optional)'}).then(function(result){ if (result.isConfirmed){ sendDecline(result.value || ''); } });
                } else {
                    var reason = prompt('Reason for declining (optional):'); if (reason !== null) sendDecline(reason);
                }
                function sendDecline(reason){
                    var payload = {};
                    if (type === 'allocation') payload = { action_type: 'decline_allocation', allocation_id: id, reason: reason };
                    if (type === 'adjustment') payload = { action_type: 'decline_adjustment', adjustment_id: id, reason: reason };
                    if (type === 'dispute') payload = { action_type: 'decline_dispute', dispute_id: id, reason: reason };
                    if (type === 'transfer') payload = { action_type: 'decline_transfer', transfer_id: id, reason: reason };
                    postAdminAction(payload, function(res){ if (res.success) location.reload(); else alert(res.message || 'Error'); });
                }
                return;
            }

            const resolveBtn = e.target.closest('.resolve-dispute-btn-standalone');
            if (resolveBtn){ const id = resolveBtn.getAttribute('data-id'); confirmAction('Resolve Dispute', 'Accept this dispute? (Accept will reconcile inventory)', 'Accept', function(){ postAdminAction({ action_type: 'approve_dispute', dispute_id: id }, function(res){ if (res.success) location.reload(); else alert(res.message || 'Error'); }); }); return; }
        });

        // Delegated change handler
        document.addEventListener('change', function(e){
            const selectAll = e.target.closest('#select-all-branches-standalone');
            if (selectAll){ document.querySelectorAll('#allocate-modal-standalone input[name="branches[]"]').forEach(cb=>cb.checked = selectAll.checked); return; }

            if (e.target.matches('.branch-filter-standalone')){
                const tableId = e.target.getAttribute('data-table');
                const tableName = e.target.value.toLowerCase();
                const table = document.getElementById(tableId);
                if (!table) return;
                const rows = table.querySelectorAll('tbody tr');
                rows.forEach(row => {
                    const branchCell = row.cells[1];
                    if (!branchCell) return;
                    const cellText = branchCell.textContent.toLowerCase();
                    row.style.display = (tableName === '' || cellText.includes(tableName)) ? '' : 'none';
                });
            }
        });

        // Delegated submit handler for allocation form
        document.addEventListener('submit', function(e){
            const form = e.target.closest('#form-allocate-standalone');
            if (!form) return;
            e.preventDefault();
            const item = form.querySelector('select[name="item"]').value;
            const batch = form.querySelector('select[name="batch"]').value;
            const quantity = form.querySelector('input[name="quantity"]').value;
            const branchEls = form.querySelectorAll('input[name="branches[]"]:checked');
            const branches = Array.from(branchEls).map(cb=>cb.value);
            if (!item || !batch || !quantity || branches.length === 0){ alert('Please select item, batch, branches and quantity.'); return; }
            const payload = { action_type: 'create_allocation', item: item, batch: batch, quantity: quantity };
            branches.forEach(b => { if (!payload['branches[]']) payload['branches[]'] = []; payload['branches[]'].push(b); });
            postAdminAction(payload, function(data){ if (data.success) { alert(data.message||'Allocations created'); location.reload(); } else { alert(data.message||'Error creating allocations'); } var modal = document.getElementById('allocate-modal-standalone'); if (modal) modal.style.display = 'none'; });
        });
    }

    // Initialize on initial load and after AJAX page loads
    document.addEventListener('DOMContentLoaded', adminInit);
    document.addEventListener('inventra:pageLoaded', adminInit);
})();
</script>
