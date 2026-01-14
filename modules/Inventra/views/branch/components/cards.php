<?php
// views/branch/components/cards.php
?>

<!-- Pending Allocations -->
<div id="PendingAllocationsCard-branch-card" class="collapsible-table-wrapper-branch-card collapsible-table-wrapper-standalone" style="display:none;">
    <h2>Pending Allocations</h2>
    <p><i>⚠️ Staff: Please ensure that the total items received are equal to the amount in the system. If there's a discrepancy, receive the items with a dispute.</i></p>
    <div class="table-container-branch-card">
        <table id="table-pending-allocations-branch-card" class="wp-list-table-branch-card wp-list-table-standalone widefat striped" style="width:100%">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Item</th>
                    <th>Batch</th>
                    <th>Quantity</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($pending_allocations)) : ?>
                    <?php foreach ($pending_allocations as $pa): ?>
                    <tr>
                        <td><?php echo date('Y-m-d H:i', strtotime($pa['created_at'])); ?></td>
                        <td>
                            <?php if ($pa['image_url']): ?>
                                <img src="<?php echo $pa['image_url']; ?>" alt="<?php echo $pa['item_name']; ?>" class="item-thumb-branch-card item-thumb-standalone">
                            <?php endif; ?>
                            <?php echo $pa['item_name']; ?>
                        </td>
                        <td><?php echo $pa['batch_name']; ?></td>
                        <td><?php echo $pa['distributed']; ?></td>
                        <td>
                            <button class="btn btn-success mark-received-btn-branch-card"
                                    data-dist-id="<?php echo $pa['id']; ?>"
                                    data-distributed="<?php echo $pa['distributed']; ?>"
                                    data-item-name="<?php echo $pa['item_name']; ?>"
                                    data-batch-name="<?php echo $pa['batch_name']; ?>">
                                Mark Received
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5">No pending allocations.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Received Items -->
<div id="ReceivedItemsCard-branch-card" class="collapsible-table-wrapper-branch-card collapsible-table-wrapper-standalone" style="display:none;">
    <h2>Received Items (All Time)</h2>
    <div class="table-container-branch-card">
        <table id="table-received-items-branch-card" class="wp-list-table-branch-card wp-list-table-standalone widefat striped" style="width:100%">
            <thead>
                <tr>
                    <th>Date Received</th>
                    <th>Item</th>
                    <th>Batch</th>
                    <th>Quantity</th>
                    <th>Staff</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($received_items)) : ?>
                    <?php foreach ($received_items as $ri): ?>
                    <tr>
                        <td><?php echo $ri['received_at'] ? date('Y-m-d H:i', strtotime($ri['received_at'])) : '-'; ?></td>
                        <td>
                            <?php if ($ri['image_url']): ?>
                                <img src="<?php echo $ri['image_url']; ?>" alt="<?php echo $ri['item_name']; ?>" class="item-thumb-branch-card item-thumb-standalone">
                            <?php endif; ?>
                            <?php echo $ri['item_name']; ?>
                        </td>
                        <td><?php echo $ri['batch_name']; ?></td>
                        <td><?php echo $ri['received'] ?? 'N/A'; ?></td>
                        <td><?php echo $ri['staff_name'] ?: 'N/A'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5">No received items.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Adjustments (Pending) -->
<div id="AdjustmentsCard-branch-card" class="collapsible-table-wrapper-branch-card" style="display:none;">
    <h2>Pending Adjustments</h2>
    <div class="table-container-branch-card">
        <table id="table-adjustments-branch-card" class="wp-list-table-branch-card wp-list-table-standalone widefat striped" style="width:100%">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Item</th>
                    <th>Batch</th>
                    <th>Type</th>
                    <th>Qty</th>
                    <th>Reason</th>
                    <th>Staff</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($pending_adjustments)) : ?>
                    <?php foreach ($pending_adjustments as $a): ?>
                    <tr>
                        <td><?php echo date('Y-m-d H:i', strtotime($a['created_at'])); ?></td>
                        <td>
                            <?php if ($a['image_url']): ?>
                                <img src="<?php echo $a['image_url']; ?>" alt="<?php echo $a['item_name']; ?>" class="item-thumb-branch-card">
                            <?php endif; ?>
                            <?php echo $a['item_name']; ?>
                        </td>
                        <td><?php echo $a['batch_name']; ?></td>
                        <td>
                            <?php
                                $display_type = $a['adj_type'];
                                if ($display_type === 'other (add)') {
                                    $display_type = 'Other-Add';
                                } elseif ($display_type === 'other (deduct)') {
                                    $display_type = 'Other-Deduct';
                                } else {
                                    $display_type = ucfirst($display_type);
                                }
                                echo $display_type;
                            ?>
                        </td>
                        <td><?php echo $a['quantity']; ?></td>
                        <td><?php echo $a['reason']; ?></td>
                        <td><?php echo $a['staff_name'] ?: 'N/A'; ?></td>
                        <td>
                            <span class="status-badge-branch-card status-<?php echo $a['status']; ?>-branch-card status-badge-standalone status-<?php echo $a['status']; ?>-standalone">
                                <?php echo ucfirst($a['status']); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="8">No adjustments.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Adjustment History -->
<div id="AdjustmentsHistoryCard-branch-card" class="collapsible-table-wrapper-branch-card" style="display:none;">
    <h2>Adjustment History</h2>
    <div class="table-container-branch-card">
        <table id="table-adjustments-history-branch-card" class="wp-list-table-branch-card wp-list-table-standalone widefat striped" style="width:100%">
            <thead>
                <tr>
                    <th>Date Resolved</th>
                    <th>Status</th>
                    <th>Item</th>
                    <th>Type</th>
                    <th>Qty</th>
                    <th>Reason</th>
                    <th>Staff</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($adjustment_history)) : ?>
                    <?php foreach ($adjustment_history as $a): ?>
                    <tr>
                        <td><?php echo $a['resolved_at'] ? date('Y-m-d H:i', strtotime($a['resolved_at'])) : '-'; ?></td>
                        <td>
                            <?php if ($a['status'] === 'accepted'): ?>
                                <span style="color: green; font-weight: bold;">Accepted</span>
                            <?php elseif ($a['status'] === 'declined'): ?>
                                <span style="color: red; font-weight: bold;">Declined</span>
                            <?php else: ?>
                                <?php echo ucfirst($a['status']); ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($a['image_url']): ?>
                                <img src="<?php echo $a['image_url']; ?>" alt="<?php echo $a['item_name']; ?>" class="item-thumb-branch-card">
                            <?php endif; ?>
                            <?php echo $a['item_name']; ?>
                        </td>
                        <td>
                            <?php
                                $display_type = $a['adj_type'];
                                if ($display_type === 'other (add)') {
                                    $display_type = 'Other-Add';
                                } elseif ($display_type === 'other (deduct)') {
                                    $display_type = 'Other-Deduct';
                                } else {
                                    $display_type = ucfirst($display_type);
                                }
                                echo $display_type;
                            ?>
                        </td>
                        <td><?php echo $a['quantity']; ?></td>
                        <td><?php echo $a['reason']; ?></td>
                        <td><?php echo $a['staff_name'] ?: 'N/A'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7">No adjustment history.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Internal Transfer -->
<div id="InternalTransferCard-branch-card" class="collapsible-table-wrapper-branch-card" style="display:none;">
    <h2>Internal Branch Transfer</h2>
    <button id="initiate-transfer-btn-branch-card" class="btn btn-primary" style="margin-bottom: 15px;">Initiate Transfer</button>

    <!-- Outgoing Transfers -->
    <h3 style="margin-top: 20px;">Outgoing Transfers</h3>
    <div class="table-container-branch-card">
        <table id="table-outgoing-transfer-branch-card" class="wp-list-table-branch-card wp-list-table-standalone widefat striped" style="width:100%">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Item</th>
                    <th>Batch</th>
                    <th>Quantity</th>
                    <th>To Branch</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Received By</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($outgoing_transfers)) : ?>
                    <?php foreach ($outgoing_transfers as $ot): ?>
                    <tr>
                        <td><?php echo date('Y-m-d H:i', strtotime($ot['created_at'])); ?></td>
                        <td>
                            <?php if ($ot['image_url']): ?>
                                <img src="<?php echo $ot['image_url']; ?>" alt="<?php echo $ot['item_name']; ?>" class="item-thumb-branch-card">
                            <?php endif; ?>
                            <?php echo $ot['item_name']; ?>
                        </td>
                        <td><?php echo $ot['batch_name']; ?></td>
                        <td><?php echo $ot['quantity']; ?></td>
                        <td><?php echo $ot['to_branch_name']; ?></td>
                        <td><?php echo $ot['reason'] ?: 'N/A'; ?></td>
                        <td>
                            <span class="status-badge-branch-card status-<?php echo $ot['status']; ?>-branch-card">
                                <?php echo ucfirst($ot['status']); ?>
                            </span>
                        </td>
                        <td><?php echo $ot['received_by'] ?: 'N/A'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="8">No outgoing transfers.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Incoming Transfers (Approved or Disputed) -->
    <h3 style="margin-top: 20px;">Incoming Transfers</h3>
    <div class="table-container-branch-card">
        <table id="table-incoming-transfer-branch-card" class="wp-list-table-branch-card wp-list-table-standalone widefat striped" style="width:100%">
            <thead>
                <tr>
                    <th>Date Approved</th>
                    <th>Item</th>
                    <th>Batch</th>
                    <th>Quantity</th>
                    <th>From Branch</th>
                    <th>Reason</th>
                    <th>Received By</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($incoming_transfers)) : ?>
                    <?php foreach ($incoming_transfers as $it): ?>
                    <tr>
                        <td><?php echo date('Y-m-d H:i', strtotime($it['approved_at'])); ?></td>
                        <td>
                            <?php if ($it['image_url']): ?>
                                <img src="<?php echo $it['image_url']; ?>" alt="<?php echo $it['item_name']; ?>" class="item-thumb-branch-card">
                            <?php endif; ?>
                            <?php echo $it['item_name']; ?>
                        </td>
                        <td><?php echo $it['batch_name']; ?></td>
                        <td><?php echo $it['quantity']; ?></td>
                        <td><?php echo $it['from_branch_name']; ?></td>
                        <td><?php echo $it['reason'] ?: 'N/A'; ?></td>
                        <td><?php echo $it['received_by'] ?: 'N/A'; ?></td>
                        <td>
                            <span class="status-badge-branch-card status-<?php echo $it['status']; ?>-branch-card">
                                <?php echo ucfirst($it['status']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($it['status'] === 'approved'): ?>
                                <button class="btn btn-success receive-transfer-btn-branch-card"
                                        data-transfer-id="<?php echo $it['id']; ?>"
                                        data-item-name="<?php echo $it['item_name']; ?>"
                                        data-batch-name="<?php echo $it['batch_name']; ?>"
                                        data-quantity="<?php echo $it['quantity']; ?>">
                                    Receive
                                </button>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="9">No incoming transfers.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Disputes (Pending) -->
<div id="DisputesCard-branch-card" class="collapsible-table-wrapper-branch-card" style="display:none;">
    <h2>Pending Disputes</h2>
    <div class="table-container-branch-card">
        <table id="table-disputes-branch-card" class="wp-list-table-branch-card wp-list-table-standalone widefat striped" style="width:100%">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Item</th>
                    <th>Batch</th>
                    <th>Qty Disputed</th>
                    <th>Reason</th>
                    <th>Staff</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($pending_disputes)) : ?>
                    <?php foreach ($pending_disputes as $d): ?>
                    <tr>
                        <td><?php echo date('Y-m-d H:i', strtotime($d['created_at'])); ?></td>
                        <td>
                            <?php if ($d['image_url']): ?>
                                <img src="<?php echo $d['image_url']; ?>" alt="<?php echo $d['item_name']; ?>" class="item-thumb-branch-card">
                            <?php endif; ?>
                            <?php echo $d['item_name']; ?>
                        </td>
                        <td><?php echo $d['batch_name']; ?></td>
                        <td><?php echo $d['disputed_qty'] ?? 'N/A'; ?></td>
                        <td><?php echo $d['reason']; ?></td>
                        <td><?php echo $d['staff_name'] ?: 'N/A'; ?></td>
                        <td>
                            <span class="status-badge-branch-card status-<?php echo $d['status']; ?>-branch-card">
                                <?php echo ucfirst($d['status']); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7">No disputes.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Dispute History -->
<div id="DisputesHistoryCard-branch-card" class="collapsible-table-wrapper-branch-card" style="display:none;">
    <h2>Dispute History</h2>
    <div class="table-container-branch-card">
        <table id="table-disputes-history-branch-card" class="wp-list-table-branch-card wp-list-table-standalone widefat striped" style="width:100%">
            <thead>
                <tr>
                    <th>Date Resolved</th>
                    <th>Status</th>
                    <th>Item</th>
                    <th>Qty Disputed</th>
                    <th>Reason</th>
                    <th>Staff</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($dispute_history)) : ?>
                    <?php foreach ($dispute_history as $d): ?>
                    <tr>
                        <td><?php echo $d['resolved_at'] ? date('Y-m-d H:i', strtotime($d['resolved_at'])) : '-'; ?></td>
                        <td>
                            <?php if ($d['status'] === 'accepted'): ?>
                                <span style="color: green; font-weight: bold;">Accepted</span>
                            <?php elseif ($d['status'] === 'declined'): ?>
                                <span style="color: red; font-weight: bold;">Declined</span>
                            <?php else: ?>
                                <?php echo ucfirst($d['status']); ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($d['image_url']): ?>
                                <img src="<?php echo $d['image_url']; ?>" alt="<?php echo $d['item_name']; ?>" class="item-thumb-branch-card">
                            <?php endif; ?>
                            <?php echo $d['item_name']; ?>
                        </td>
                        <td><?php echo $d['disputed_qty'] ?? 'N/A'; ?></td>
                        <td><?php echo $d['reason']; ?></td>
                        <td><?php echo $d['staff_name'] ?: 'N/A'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6">No dispute history.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Inventory Card Content -->
<div id="InventoryCard-branch-card" class="collapsible-table-wrapper-branch-card" style="display:none;">
    <h2>Branch Inventory</h2>
    <div class="table-container-branch-card">
        <table id="table-inventory-branch-card" class="wp-list-table-branch-card widefat striped" style="width:100%">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Batch</th>
                    <th>Current Stock</th>
                    <th>Total Received</th>
                    <th>Adjustments Subtracted</th>
                    <th>Adjustments Added</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($inventory)) : ?>
                    <?php foreach ($inventory as $item): ?>
                    <tr>
                        <td>
                            <?php if ($item['image_url']): ?>
                                <img src="<?php echo $item['image_url']; ?>" alt="<?php echo $item['item_name']; ?>" class="item-thumb-branch-card">
                            <?php endif; ?>
                            <?php echo $item['item_name']; ?>
                        </td>
                        <td><?php echo $item['batch_name']; ?></td>
                        <td><?php echo $item['current_stock']; ?></td>
                        <td><?php echo $item['total_received']; ?></td>
                        <td><?php echo $item['adjustments_subtracted']; ?></td>
                        <td><?php echo $item['adjustments_added']; ?></td>
                        <td>
                            <button class="btn btn-warning adjustment-btn-branch-card"
                                    data-item-id="<?php echo $item['item_id']; ?>"
                                    data-current-stock="<?php echo $item['current_stock']; ?>">
                                Request Adjustment
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7">No inventory items found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
