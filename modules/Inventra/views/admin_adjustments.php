<h2>Pending Adjustments</h2>
<?php if (!empty($pending_adjustments)): ?>
<table class="wp-list-table widefat striped">
    <thead>
        <tr><th>Date</th><th>Branch</th><th>Item</th><th>Type</th><th>Qty</th><th>Reason</th><th>Actions</th></tr>
    </thead>
    <tbody>
    <?php foreach ($pending_adjustments as $a): ?>
        <tr>
            <td><?= htmlspecialchars(date('Y-m-d', strtotime($a['created_at']))) ?></td>
            <td><?= htmlspecialchars($a['branch_name']) ?></td>
            <td><?= htmlspecialchars($a['item_name']) ?></td>
            <td><?= htmlspecialchars($a['adjustment_type']) ?></td>
            <td><?= (int)$a['quantity'] ?></td>
            <td><?= htmlspecialchars($a['reason']) ?></td>
            <td>
                <button class="approve-btn" data-id="<?= $a['id'] ?>">Approve</button>
                <button class="decline-btn" data-id="<?= $a['id'] ?>">Decline</button>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php else: ?>
<p>No pending adjustments.</p>
<?php endif; ?>
