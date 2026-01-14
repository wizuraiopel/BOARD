<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
    <h2 style="margin:0;">Pending Allocations</h2>
    <div>
        <a href="<?= BASE_URL ?>/index.php?action=admin_dashboard_modern" class="btn btn-primary" style="margin-right:8px;">Back to Dashboard</a>
        <a href="<?= BASE_URL ?>/index.php?action=admin_allocations" class="btn btn-primary">+ Create Allocation</a>
    </div>
</div>
<?php if (!empty($pending_allocations)): ?>
<table class="wp-list-table widefat striped">
    <thead>
        <tr><th>Date</th><th>Branch</th><th>Item</th><th>Batch</th><th>Quantity</th><th>Status</th><th>Actions</th></tr>
    </thead>
    <tbody>
    <?php foreach ($pending_allocations as $alloc): ?>
        <tr>
            <td><?= htmlspecialchars(date('Y-m-d', strtotime($alloc['created_at']))) ?></td>
            <td><?= htmlspecialchars($alloc['branch_name']) ?></td>
            <td><?= htmlspecialchars($alloc['item_name']) ?></td>
            <td><?= htmlspecialchars($alloc['batch_name']) ?></td>
            <td><?= (int)$alloc['quantity'] ?></td>
            <td><?= htmlspecialchars($alloc['status']) ?></td>
            <td>
                <button class="approve-btn" data-id="<?= $alloc['id'] ?>">Approve</button>
                <button class="decline-btn" data-id="<?= $alloc['id'] ?>">Decline</button>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php else: ?>
<p>No pending allocations.</p>
<?php endif; ?>
