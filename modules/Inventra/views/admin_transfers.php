<h2>Pending Transfers</h2>
<?php if (!empty($pending_branch_transfers)): ?>
<table class="wp-list-table widefat striped">
    <thead>
        <tr><th>Date</th><th>From</th><th>To</th><th>Item</th><th>Batch</th><th>Qty</th><th>Actions</th></tr>
    </thead>
    <tbody>
    <?php foreach ($pending_branch_transfers as $t): ?>
        <tr>
            <td><?= htmlspecialchars(date('Y-m-d', strtotime($t['created_at']))) ?></td>
            <td><?= htmlspecialchars($t['from_branch_name']) ?></td>
            <td><?= htmlspecialchars($t['to_branch_name']) ?></td>
            <td><?= htmlspecialchars($t['item_name']) ?></td>
            <td><?= htmlspecialchars($t['batch_name']) ?></td>
            <td><?= (int)$t['quantity'] ?></td>
            <td>
                <button class="approve-btn" data-id="<?= $t['id'] ?>">Approve</button>
                <button class="decline-btn" data-id="<?= $t['id'] ?>">Decline</button>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php else: ?>
<p>No pending transfers.</p>
<?php endif; ?>
