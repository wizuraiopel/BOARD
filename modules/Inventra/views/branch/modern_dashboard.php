<?php
// views/branch/modern_dashboard.php
// $counts and $series are provided by controller
$counts = $counts ?? ($overview_counts ?? []);
$series = $series ?? ($series ?? []);
?>

<div class="branch-modern-dashboard">
    <h1>Branch Dashboard — Modern</h1>
    <p class="muted">A quick prototype: KPI strip, 31-day received chart, recent received table.</p>

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;gap:12px;">
        <div style="flex:1">
            <div class="kpi-strip">
                <div class="kpi" data-key="pending_allocations">
                    <div class="kpi-value"><?php echo (int)($counts['pending_allocations'] ?? 0); ?></div>
                    <div class="kpi-label">Pending Allocations</div>
                </div>
                <div class="kpi" data-key="pending_adjustments">
                    <div class="kpi-value"><?php echo (int)($counts['pending_adjustments'] ?? 0); ?></div>
                    <div class="kpi-label">Pending Adjustments</div>
                </div>
                <div class="kpi" data-key="pending_disputes">
                    <div class="kpi-value"><?php echo (int)($counts['pending_disputes'] ?? 0); ?></div>
                    <div class="kpi-label">Pending Disputes</div>
                </div>
                <div class="kpi" data-key="pending_incoming">
                    <div class="kpi-value"><?php echo (int)($counts['pending_incoming'] ?? 0); ?></div>
                    <div class="kpi-label">Pending Incoming</div>
                </div>
                <div class="kpi" data-key="pending_outgoing">
                    <div class="kpi-value"><?php echo (int)($counts['pending_outgoing'] ?? 0); ?></div>
                    <div class="kpi-label">Pending Outgoing</div>
                </div>
            </div>
        </div>
        <div style="flex:0 0 auto; display:flex; gap:8px; align-items:center;">
            <label for="dashboard-range" style="font-size:13px;color:#6b7280;margin-right:6px">Range</label>
            <select id="dashboard-range" style="padding:6px;border-radius:6px;border:1px solid #e2e8f0">
                <option value="7">7d</option>
                <option value="14">14d</option>
                <option value="31" selected>31d</option>
                <option value="90">90d</option>
            </select>
            <button id="dashboard-refresh" class="btn btn-primary">Refresh</button>
            <span id="dashboard-loading" style="display:none;color:#6b7280;margin-left:6px">Loading…</span>
        </div>
    </div>
        <div class="kpi" data-key="pending_adjustments">
            <div class="kpi-value"><?php echo (int)($counts['pending_adjustments'] ?? 0); ?></div>
            <div class="kpi-label">Pending Adjustments</div>
        </div>
        <div class="kpi" data-key="pending_disputes">
            <div class="kpi-value"><?php echo (int)($counts['pending_disputes'] ?? 0); ?></div>
            <div class="kpi-label">Pending Disputes</div>
        </div>
        <div class="kpi" data-key="pending_incoming">
            <div class="kpi-value"><?php echo (int)($counts['pending_incoming'] ?? 0); ?></div>
            <div class="kpi-label">Pending Incoming</div>
        </div>
        <div class="kpi" data-key="pending_outgoing">
            <div class="kpi-value"><?php echo (int)($counts['pending_outgoing'] ?? 0); ?></div>
            <div class="kpi-label">Pending Outgoing</div>
        </div>
    </div>

    <div class="modern-grid">
        <div class="panel">
            <h2>Received Items (31 days)</h2>
            <canvas id="receivedChart" width="600" height="250"></canvas>
        </div>
        <div class="panel">
            <h2>Adjustments (31 days)</h2>
            <canvas id="adjustmentsChart" width="600" height="250"></canvas>
        </div>
    </div>

    <div class="panel">
        <h2>Recent Received Items</h2>
        <table id="table-received-modern" class="display" style="width:100%">
            <thead>
                <tr><th>Date</th><th>Item</th><th>Batch</th><th>Qty</th><th>Staff</th></tr>
            </thead>
            <tbody>
                <!-- Filled by JS via AJAX -->
            </tbody>
        </table>
    </div>
</div>

<link rel="stylesheet" href="<?= BASE_URL ?>/public/css/branch.css">
<style>
.branch-modern-dashboard { max-width:1200px; margin:0 auto; padding:20px; }
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
<script src="<?= BASE_URL ?>/public/js/branch_modern.js"></script>

<script>
// Seed initial metrics (counts, series, recent_received) to render immediately
window.__inventra_initial_series = <?php echo json_encode(['counts'=>($counts ?? []),'series'=>($series ?? []),'recent_received'=>($recent_received ?? [])]); ?> || {};
</script>