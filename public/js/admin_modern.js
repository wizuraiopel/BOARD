(function(){
    const statsUrl = (window._base_url || window.baseUrl) + '/index.php?action=admin_dashboard_stats';

    function makeDataset(series, label, color) {
        return {
            label: label,
            data: series.map(s=>s.count),
            backgroundColor: color + '33',
            borderColor: color,
            tension: 0.25,
            fill: true
        };
    }

    function renderCharts(metrics) {
        const received = metrics.received_series || [];
        const adjustments = metrics.adjustments_series || [];
        const transfers = metrics.transfers_series || [];
        const labels = received.map(s=>s.date);

        const ctx = document.getElementById('adminReceivedChart');
        if (ctx) {
            new Chart(ctx, { type: 'line', data: { labels: labels, datasets: [ makeDataset(received, 'Received', '#3a86ff') ] }, options: { responsive:true, plugins:{legend:{display:false}} } });
        }

        const ctx2 = document.getElementById('adminAdjustmentsChart');
        if (ctx2) {
            new Chart(ctx2, { type: 'line', data: { labels: adjustments.map(s=>s.date), datasets: [ makeDataset(adjustments, 'Adjustments', '#ff7b54') ] }, options: { responsive:true, plugins:{legend:{display:false}} } });
        }

        // If you want transfers as separate small chart, you can add it later
    }

    function populateTable(rows) {
        const $tbl = $('#table-admin-received');
        if (!$tbl.length) return;
        const tbody = $tbl.find('tbody');
        tbody.empty();
        rows.forEach(r=>{
            const date = r.received_at || r.created_at || '';
            const branch = r.to_branch_name || r.branch_name || '';
            const tr = `<tr><td>${date}</td><td>${branch}</td><td>${r.item_name||''}</td><td>${r.batch_name||''}</td><td>${r.received||r.distributed||''}</td></tr>`;
            tbody.append(tr);
        });
        if ($.fn.DataTable && !$tbl.hasClass('dataTable')) {
            $tbl.DataTable({ pageLength: 25, responsive: true, order: [[0,'desc']] });
        }
    }

    function fetchMetrics(days){
        const url = statsUrl + (days ? ('&days=' + encodeURIComponent(days)) : '');
        document.getElementById('admin-dashboard-loading') && (document.getElementById('admin-dashboard-loading').style.display = 'inline');
        return fetch(url, { credentials: 'same-origin' }).then(r=>r.json()).then(js=>{
            document.getElementById('admin-dashboard-loading') && (document.getElementById('admin-dashboard-loading').style.display = 'none');
            if (js && js.success && js.metrics) return js.metrics; else throw new Error(js && js.message ? js.message : 'Invalid response');
        });
    }

    function init() {
        const initial = window.__admin_initial_metrics || {};
        if (initial && Object.keys(initial).length) {
            if (initial.overview) {
                Object.entries(initial.overview).forEach(([k,v])=>{
                    const el = document.querySelector(`.kpi[data-key="${k}"] .kpi-value`);
                    if (el) el.textContent = v;
                });
            }
            renderCharts(initial);
            populateTable(initial.recent_received || []);
        }

        const range = document.getElementById('admin-dashboard-range');
        const refreshBtn = document.getElementById('admin-dashboard-refresh');
        function reload(){ const days = range ? parseInt(range.value||31) : 31; fetchMetrics(days).then(metrics=>{ if (metrics.overview){ Object.entries(metrics.overview).forEach(([k,v])=>{ const el = document.querySelector(`.kpi[data-key="${k}"] .kpi-value`); if (el) el.textContent = v; }); } renderCharts(metrics); populateTable(metrics.recent_received || []); }).catch(err=>{ console.error(err); alert('Unable to load metrics: '+err.message); }); }
        if (refreshBtn) refreshBtn.addEventListener('click', reload);

        // Approve/Decline actions via event delegation and modal open/cancel
        document.addEventListener('click', function(e){
            if (e.target && e.target.matches('.admin-action-approve')) {
                var id = e.target.getAttribute('data-id');
                if (confirm('Approve allocation #' + id + '?')) {
                    postAdminAction({action:'approve_allocation', allocation_id: id}).then(js=>{
                        if (js.success){ alert('Allocation approved.'); reload(); } else { alert(js.message || 'Failed to approve.'); }
                    }).catch(err=>{ console.error(err); alert('Error: '+err.message); });
                }
            }
            if (e.target && e.target.matches('.admin-action-decline')) {
                var id = e.target.getAttribute('data-id');
                var reason = prompt('Reason for decline (optional):');
                if (id) {
                    postAdminAction({action:'decline_allocation', allocation_id: id, reason: reason || ''}).then(js=>{
                        if (js.success){ alert('Allocation declined.'); reload(); } else { alert(js.message || 'Failed to decline.'); }
                    }).catch(err=>{ console.error(err); alert('Error: '+err.message); });
                }
            }
            if (e.target && e.target.id === 'open-create-allocation') {
                var modal = document.getElementById('create-allocation-modal');
                if (modal) modal.style.display = 'block';
            }
            if (e.target && e.target.id === 'alloc-cancel') {
                var modal = document.getElementById('create-allocation-modal');
                if (modal) modal.style.display = 'none';
            }
        });

        function postAdminAction(payload){
            var form = new FormData();
            form.append('_wpnonce', window._inventra_csrf || '');
            for (var k in payload) {
                var v = payload[k];
                if (Array.isArray(v)) {
                    v.forEach(function(x){ form.append(k + '[]', x); });
                } else {
                    form.append(k, v);
                }
            }
            return fetch((window._base_url || window.baseUrl) + '/index.php?action=admin_action', { method:'POST', credentials:'same-origin', body: form }).then(r=>r.json());
        }

        // Create allocation form submission
        var allocForm = document.getElementById('create-allocation-form');
        if (allocForm) {
            allocForm.addEventListener('submit', function(e){
                e.preventDefault();
                var formData = new FormData(allocForm);
                var payload = { action: 'create_allocation' };
                // get item, batch, quantity
                payload.item = formData.get('item');
                payload.batch = formData.get('batch');
                payload.quantity = formData.get('quantity');
                // gather branches[]
                var branches = [];
                allocForm.querySelectorAll('input[name="branches[]"]:checked').forEach(function(cb){ branches.push(cb.value); });
                payload.branches = branches;
                postAdminAction(payload).then(function(js){
                    if (js.success) {
                        alert('Allocations created: ' + (js.created || 'OK'));
                        var modal = document.getElementById('create-allocation-modal');
                        if (modal) modal.style.display = 'none';
                        reload();
                    } else {
                        alert(js.message || 'Failed to create allocation');
                    }
                }).catch(function(err){ console.error(err); alert('Error: '+err.message); });
            });
        }

        // Initial live load
        reload();
    }

    document.addEventListener('DOMContentLoaded', init);
})();