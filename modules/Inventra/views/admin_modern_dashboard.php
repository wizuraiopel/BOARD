<?php
// views/admin_modern_dashboard.php
$metrics = $metrics ?? [];
$overview = $metrics['overview'] ?? [];
$received_series = $metrics['received_series'] ?? [];
$adjustments_series = $metrics['adjustments_series'] ?? [];
$transfers_series = $metrics['transfers_series'] ?? [];
$recent_received = $metrics['recent_received'] ?? [];

// Reference lists for actions/modals
$items = $items ?? [];
$branches = $branches ?? [];
$batches = $batches ?? [];
$pending_allocations = $pending_allocations ?? []; 
?>

<div class="admin-modern-dashboard">
    <h1>Admin Dashboard — Modern</h1>
    <p class="muted">KPIs, 31-day trends, and recent activity — interactive and responsive.</p>

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;gap:12px;">
        <div style="flex:1">
            <div style="margin-bottom:8px;display:flex;gap:8px;align-items:center">
                <button id="open-create-allocation" class="btn btn-primary">Create Allocation</button>
                <a href="<?= BASE_URL ?>/index.php?action=admin_adjustments" class="btn btn-outline">View Adjustments</a>
            </div>
            <div class="kpi-strip">
                <div class="kpi" data-key="allocations">
                    <div class="kpi-value"><?php echo (int)($overview['allocations'] ?? 0); ?></div>
                    <div class="kpi-label">Pending Allocations</div>
                </div>
                <div class="kpi" data-key="adjustments">
                    <div class="kpi-value"><?php echo (int)($overview['adjustments'] ?? 0); ?></div>
                    <div class="kpi-label">Pending Adjustments</div>
                </div>
                <div class="kpi" data-key="disputes">
                    <div class="kpi-value"><?php echo (int)($overview['disputes'] ?? 0); ?></div>
                    <div class="kpi-label">Pending Disputes</div>
                </div>
                <div class="kpi" data-key="transfers">
                    <div class="kpi-value"><?php echo (int)($overview['transfers'] ?? 0); ?></div>
                    <div class="kpi-label">Pending Transfers</div>
                </div>
            </div>
        </div>
        <div style="flex:0 0 auto; display:flex; gap:8px; align-items:center;">
            <label for="admin-dashboard-range" style="font-size:13px;color:#6b7280;margin-right:6px">Range</label>
            <select id="admin-dashboard-range" style="padding:6px;border-radius:6px;border:1px solid #e2e8f0">
                <option value="7">7d</option>
                <option value="14">14d</option>
                <option value="31" selected>31d</option>
                <option value="90">90d</option>
            </select>
            <button id="admin-dashboard-refresh" class="btn btn-primary">Refresh</button>
            <span id="admin-dashboard-loading" style="display:none;color:#6b7280;margin-left:6px">Loading…</span>
        </div>
    </div>

    <div class="modern-grid">
        <div class="panel">
            <h2>Received Items (range)</h2>
            <canvas id="adminReceivedChart" width="600" height="250"></canvas>
        </div>
        <div class="panel">
            <h2>Adjustments (range)</h2>
            <canvas id="adminAdjustmentsChart" width="600" height="250"></canvas>
        </div>
    </div>

    <div class="panel">
        <h2>Pending Allocations</h2>
        <table id="table-admin-pending-allocations" class="display" style="width:100%">
            <thead>
                <tr><th>Requested</th><th>Branch</th><th>Item</th><th>Batch</th><th>Qty</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach($pending_allocations as $pa): ?>
                    <tr data-id="<?= $pa['id'] ?>">
                        <td><?= htmlspecialchars($pa['created_at'] ?? '') ?></td>
                        <td><?= htmlspecialchars($pa['branch_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($pa['item_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($pa['batch_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($pa['distributed'] ?? '') ?></td>
                        <td>
                            <button class="btn btn-success admin-action-approve" data-id="<?= $pa['id'] ?>">Approve</button>
                            <button class="btn btn-danger admin-action-decline" data-id="<?= $pa['id'] ?>">Decline</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Create Allocation Modal -->
    <div id="create-allocation-modal" class="modal" style="display:none;">
        <div class="modal-content" style="max-width:700px;margin:40px auto;background:#fff;padding:18px;border-radius:8px;">
            <h3>Create Allocation</h3>
            <form id="create-allocation-form">
                <div style="display:flex;gap:8px;margin-bottom:8px">
                    <select name="item" id="alloc-item" required>
                        <option value="">Select item</option>
                        <?php foreach($items as $it): ?>
                            <option value="<?= $it['id'] ?>"><?= htmlspecialchars($it['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="batch" id="alloc-batch" required>
                        <option value="">Select batch</option>
                        <?php foreach($batches as $bt): ?>
                            <option value="<?= $bt['id'] ?>"><?= htmlspecialchars($bt['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" name="quantity" id="alloc-quantity" min="1" placeholder="Quantity" required />
                </div>
                <div style="margin-bottom:8px">
                    <label>Branches</label>
                    <div style="max-height:180px;overflow:auto;border:1px solid #e5e7eb;padding:8px;border-radius:6px;">
                        <?php foreach($branches as $br): ?>
                            <label style="display:block;margin-bottom:6px"><input type="checkbox" name="branches[]" value="<?= $br->id ?>" /> <?= htmlspecialchars($br->username) ?></label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div style="display:flex;gap:8px;justify-content:flex-end">
                    <button type="button" class="btn btn-outline" id="alloc-cancel">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create</button>
                </div>
            </form>
        </div>
    </div>

    <div class="panel">
        <h2>Recent Received Items</h2>
        <table id="table-admin-received" class="display" style="width:100%">
            <thead>
                <tr><th>Date</th><th>Branch</th><th>Item</th><th>Batch</th><th>Qty</th></tr>
            </thead>
            <tbody>
                <!-- Filled by JS -->
            </tbody>
        </table>
    </div> 
</div>

<link rel="stylesheet" href="<?= BASE_URL ?>/public/css/branch.css">
<style>
.admin-modern-dashboard { max-width:1200px; margin:0 auto; padding:20px; }
.kpi-strip { display:flex; gap:16px; margin-bottom:18px; }
.kpi { background:#fff; padding:14px 18px; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.04); flex:1; text-align:center; }
.kpi-value { font-size:28px; font-weight:700; }
.kpi-label { color:#6b7280; margin-top:6px; }
.modern-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px; }
.panel { background:#fff; padding:16px; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.04); }
.muted { color:#6b7280; }
@media(max-width:900px){ .modern-grid{ grid-template-columns:1fr; } .kpi-strip{ flex-direction:column; } }
</style>

<!-- Chart.js + DataTables (CDNs) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="<?= BASE_URL ?>/public/js/admin_modern.js"></script>

<script>
window.__admin_initial_metrics = <?php echo json_encode($metrics); ?> || {};
</script>