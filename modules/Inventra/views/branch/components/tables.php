<?php
// views/branch/components/tables.php
?>

<!-- Transfer Modal -->
<div id="transfer-modal-branch-card" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%); background:#fff; border-radius:8px; box-shadow:0 5px 15px rgba(0,0,0,0.3); padding:20px; z-index:10001; width:90%; max-width:500px;">
    <h2 style="margin-top:0; color:#0073aa;">Initiate Internal Transfer</h2>
    <form id="transfer-form-branch-card">
        <div class="inventra-swal-form-group">
            <label class="inventra-swal-label">Item <span style="color:red;">*</span></label>
            <select id="transfer-item-id-branch-card" name="item_id" class="inventra-swal-select" required>
                <option value="">-- Choose an Item --</option>
                <?php foreach ($current_branch_inventory as $inv_item): ?>
                    <?php if ($inv_item['current_stock'] > 0 && isset($inv_item['batch_id'])): ?>
                        <option value="<?php echo $inv_item['item_id']; ?>"
                                data-max-qty="<?php echo $inv_item['current_stock']; ?>"
                                data-batch-id="<?php echo $inv_item['batch_id']; ?>">
                            <?php echo $inv_item['item_name']; ?> (Batch: <?php echo $inv_item['batch_name']; ?>, Stock: <?php echo $inv_item['current_stock']; ?>)
                        </option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="inventra-swal-form-group">
            <label class="inventra-swal-label">Batch <span style="color:red;">*</span></label>
            <input type="text" id="transfer-batch-name-branch-card" class="inventra-swal-input" disabled>
            <input type="hidden" id="transfer-batch-id-branch-card" name="batch_id" required>
        </div>
        <div class="inventra-swal-form-group">
            <label class="inventra-swal-label">Available Quantity: <span id="transfer-available-qty-branch-card">0</span></label>
        </div>
        <div class="inventra-swal-form-group">
            <label class="inventra-swal-label">Quantity to Transfer <span style="color:red;">*</span></label>
            <input type="number" name="quantity" id="transfer-quantity-branch-card" min="1" required style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
        </div>
        <div class="inventra-swal-form-group">
            <label class="inventra-swal-label">To Branch <span style="color:red;">*</span></label>
            <select name="to_branch_id" class="inventra-swal-select" required>
                <option value="">-- Choose a Branch --</option>
                <?php foreach ($all_branches_for_transfer as $b): ?>
                    <option value="<?php echo $b['ID']; ?>"><?php echo $b['user_login']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="inventra-swal-form-group">
            <label class="inventra-swal-label">Reason (Optional):</label>
            <textarea name="reason" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;"></textarea>
        </div>
        <div class="inventra-swal-form-group">
            <label class="inventra-swal-label">Staff Name <span style="color:red;">*</span></label>
            <input type="text" name="staff_name" class="inventra-swal-input" required>
        </div>
        <div style="margin-top:15px;">
            <button type="submit" class="button button-primary">Initiate Transfer</button>
            <button type="button" id="btn-cancel-transfer-branch-card" class="button">Cancel</button>
        </div>
    </form>
</div>
<div id="transfer-modal-backdrop-branch-card" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:10000;"></div>
