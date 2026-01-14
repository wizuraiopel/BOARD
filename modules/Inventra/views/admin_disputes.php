<h2>Pending Disputes</h2>
<?php if (!empty($pending_disputes)): ?>
<table class="wp-list-table widefat striped">
    <thead>
        <tr><th>Date</th><th>Branch</th><th>Item</th><th>Disputed Qty</th><th>Reason</th><th>Actions</th></tr>
    </thead>
    <tbody>
    <?php foreach ($pending_disputes as $d): ?>
        <tr>
            <td><?= htmlspecialchars(date('Y-m-d', strtotime($d['created_at']))) ?></td>
            <td><?= htmlspecialchars($d['branch_name']) ?></td>
            <td><?= htmlspecialchars($d['item_name']) ?></td>
            <td><?= (int)$d['disputed_qty'] ?></td>
            <td><?= htmlspecialchars($d['reason']) ?></td>
            <td>
                <button class="resolve-btn" data-id="<?= $d['id'] ?>">Resolve</button>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php else: ?>
<p>No pending disputes.</p>
<?php endif; ?>
