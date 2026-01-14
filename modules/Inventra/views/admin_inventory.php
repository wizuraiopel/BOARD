<?php
// views/admin_inventory.php
$inventory = $inventory_pivot['data'] ?? [];
$items = $inventory_pivot['items'] ?? [];
?>

<h1>Inventory Pivot</h1>
<p>Rows: Branches — Columns: Items</p>
<div style="overflow:auto; background:#fff;padding:12px;border-radius:8px;">
    <table class="wp-list-table widefat striped">
        <thead>
            <tr>
                <th>Branch</th>
                <?php foreach ($items as $it): ?>
                    <th><?= htmlspecialchars($it['name']) ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($inventory as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['branch_name']) ?></td>
                    <?php foreach ($items as $it): ?>
                        <?php $cell = $row[$it['id']] ?? ['allocated'=>0,'received'=>0]; ?>
                        <td><?= (int)$cell['received'] ?> <small style="color:#888">(A:<?= (int)$cell['allocated'] ?>)</small></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>