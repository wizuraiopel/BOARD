<?php
// views/branch/components/modals.php
?>

<!-- Modal for Mark Received (Allocations) -->
<div id="mark-received-modal-branch-card" class="modal-branch-card" style="display:none;">
    <div class="modal-content-branch-card">
        <div class="modal-header-branch-card">
            <h2>Mark Allocation as Received</h2>
            <button class="close-modal-btn-branch-card">&times;</button>
        </div>
        <div class="modal-body-branch-card">
            <p><strong>Item:</strong> <span id="modal-item-name-branch-card"></span></p>
            <p><strong>Batch:</strong> <span id="modal-batch-name-branch-card"></span></p>
            <p><strong>Expected Quantity:</strong> <span id="modal-distributed-branch-card"></span></p>
            
            <label for="received-qty-branch-card">Received Quantity:</label>
            <input type="number" id="received-qty-branch-card" class="form-control-branch-card" min="0" required>
            
            <label for="dispute-reason-branch-card">Dispute Reason (if any):</label>
            <select id="dispute-reason-branch-card" class="form-control-branch-card">
                <option value="">No Dispute</option>
                <option value="damaged">Damaged Items</option>
                <option value="shortage">Shortage</option>
                <option value="expired">Expired Items</option>
                <option value="other">Other</option>
            </select>
            
            <label for="staff-name-branch-card">Receiving Staff Name:</label>
            <input type="text" id="staff-name-branch-card" class="form-control-branch-card" required>
        </div>
        <div class="modal-footer-branch-card">
            <button id="confirm-received-btn-branch-card" class="btn btn-success">Confirm Received</button>
            <button class="btn btn-secondary close-modal-btn-branch-card">Cancel</button>
        </div>
    </div>
</div>

<!-- Modal for Resolve Dispute -->
<div id="resolve-dispute-modal-branch-card" class="modal-branch-card" style="display:none;">
    <div class="modal-content-branch-card">
        <div class="modal-header-branch-card">
            <h2>Resolve Dispute</h2>
            <button class="close-modal-btn-branch-card">&times;</button>
        </div>
        <div class="modal-body-branch-card">
            <p><strong>Item:</strong> <span id="dispute-modal-item-name-branch-card"></span></p>
            <p><strong>Dispute Reason:</strong> <span id="dispute-modal-reason-branch-card"></span></p>
            
            <label for="resolution-branch-card">Resolution:</label>
            <select id="resolution-branch-card" class="form-control-branch-card" required>
                <option value="">Select Resolution</option>
                <option value="accepted">Accept Items</option>
                <option value="rejected">Reject Items</option>
                <option value="partial">Partial Acceptance</option>
            </select>
        </div>
        <div class="modal-footer-branch-card">
            <button id="confirm-resolve-dispute-btn-branch-card" class="btn btn-success">Resolve</button>
            <button class="btn btn-secondary close-modal-btn-branch-card">Cancel</button>
        </div>
    </div>
</div>

<!-- Modal for Initiate Transfer -->
<div id="initiate-transfer-modal-branch-card" class="modal-branch-card" style="display:none;">
    <div class="modal-content-branch-card">
        <div class="modal-header-branch-card">
            <h2>Initiate Internal Transfer</h2>
            <button class="close-modal-btn-branch-card">&times;</button>
        </div>
        <div class="modal-body-branch-card">
            <label for="transfer-item-branch-card">Item:</label>
            <select id="transfer-item-branch-card" class="form-control-branch-card" required>
                <option value="">Select Item</option>
            </select>
            
            <label for="transfer-batch-branch-card">Batch:</label>
            <select id="transfer-batch-branch-card" class="form-control-branch-card" required>
                <option value="">Select Batch</option>
            </select>
            
            <label for="transfer-to-branch-branch-card">Transfer To Branch:</label>
            <select id="transfer-to-branch-branch-card" class="form-control-branch-card" required>
                <option value="">Select Branch</option>
            </select>
            
            <label for="transfer-quantity-branch-card">Quantity:</label>
            <input type="number" id="transfer-quantity-branch-card" class="form-control-branch-card" min="1" required>
            
            <label for="transfer-reason-branch-card">Reason for Transfer:</label>
            <input type="text" id="transfer-reason-branch-card" class="form-control-branch-card" required>
            
            <label for="transfer-staff-name-branch-card">Transferring Staff Name:</label>
            <input type="text" id="transfer-staff-name-branch-card" class="form-control-branch-card" required>
        </div>
        <div class="modal-footer-branch-card">
            <button id="confirm-transfer-btn-branch-card" class="btn btn-success">Initiate Transfer</button>
            <button class="btn btn-secondary close-modal-btn-branch-card">Cancel</button>
        </div>
    </div>
</div>

<!-- Modal for Receive Transfer -->
<div id="receive-transfer-modal-branch-card" class="modal-branch-card" style="display:none;">
    <div class="modal-content-branch-card">
        <div class="modal-header-branch-card">
            <h2>Receive Transfer</h2>
            <button class="close-modal-btn-branch-card">&times;</button>
        </div>
        <div class="modal-body-branch-card">
            <p><strong>From Branch:</strong> <span id="receive-transfer-from-branch-branch-card"></span></p>
            <p><strong>Item:</strong> <span id="receive-transfer-item-branch-card"></span></p>
            <p><strong>Quantity:</strong> <span id="receive-transfer-quantity-branch-card"></span></p>
            
            <label for="received-transfer-qty-branch-card">Received Quantity:</label>
            <input type="number" id="received-transfer-qty-branch-card" class="form-control-branch-card" min="0" required>
            
            <label for="transfer-dispute-reason-branch-card">Dispute Reason (if any):</label>
            <select id="transfer-dispute-reason-branch-card" class="form-control-branch-card">
                <option value="">No Dispute</option>
                <option value="damaged">Damaged Items</option>
                <option value="shortage">Shortage</option>
                <option value="expired">Expired Items</option>
                <option value="other">Other</option>
            </select>
        </div>
        <div class="modal-footer-branch-card">
            <button id="confirm-receive-transfer-btn-branch-card" class="btn btn-success">Confirm Receipt</button>
            <button class="btn btn-secondary close-modal-btn-branch-card">Cancel</button>
        </div>
    </div>
</div>

<!-- Modal for Request Adjustment -->
<div id="request-adjustment-modal-branch-card" class="modal-branch-card" style="display:none;">
    <div class="modal-content-branch-card">
        <div class="modal-header-branch-card">
            <h2>Request Adjustment</h2>
            <button class="close-modal-btn-branch-card">&times;</button>
        </div>
        <div class="modal-body-branch-card">
            <label for="adjustment-item-branch-card">Item:</label>
            <select id="adjustment-item-branch-card" class="form-control-branch-card" required>
                <option value="">Select Item</option>
            </select>
            
            <label for="adjustment-batch-branch-card">Batch:</label>
            <select id="adjustment-batch-branch-card" class="form-control-branch-card" required>
                <option value="">Select Batch</option>
            </select>
            
            <label for="adjustment-type-branch-card">Adjustment Type:</label>
            <select id="adjustment-type-branch-card" class="form-control-branch-card" required>
                <option value="">Select Type</option>
                <option value="damaged">Damaged Items</option>
                <option value="redeem">Redeemed Items</option>
                <option value="stolen">Stolen Items</option>
                <option value="missing">Missing Items</option>
                <option value="found">Found Items</option>
                <option value="other (add)">Other (Add)</option>
                <option value="other (deduct)">Other (Deduct)</option>
            </select>
            
            <label for="adjustment-quantity-branch-card">Quantity:</label>
            <input type="number" id="adjustment-quantity-branch-card" class="form-control-branch-card" min="1" required>
            
            <label for="adjustment-reason-branch-card">Reason:</label>
            <textarea id="adjustment-reason-branch-card" class="form-control-branch-card" rows="3" required></textarea>
            
            <label for="adjustment-staff-name-branch-card">Staff Name:</label>
            <input type="text" id="adjustment-staff-name-branch-card" class="form-control-branch-card" required>
        </div>
        <div class="modal-footer-branch-card">
            <button id="confirm-adjustment-btn-branch-card" class="btn btn-success">Submit Adjustment</button>
            <button class="btn btn-secondary close-modal-btn-branch-card">Cancel</button>
        </div>
    </div>
</div>
