(function(){
    // Branch modern dashboard script
    const statsUrl = (window._base_url || window.baseUrl) + '/index.php?action=branch_dashboard_stats';

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

    function renderCharts(data) {
        const received = data.series.received || [];
        const adjustments = data.series.adjustments || [];
        const labels = received.map(s=>s.date);

        const ctx = document.getElementById('receivedChart');
        if (ctx) {
            new Chart(ctx, { type: 'line', data: { labels: labels, datasets: [ makeDataset(received, 'Received', '#3a86ff') ] }, options: { responsive:true, plugins:{legend:{display:false}} } });
        }

        const ctx2 = document.getElementById('adjustmentsChart');
        if (ctx2) {
            new Chart(ctx2, { type: 'line', data: { labels: adjustments.map(s=>s.date), datasets: [ makeDataset(adjustments, 'Adjustments', '#ff7b54') ] }, options: { responsive:true, plugins:{legend:{display:false}} } });
        }
    }

    function populateTable(rows) {
        const $tbl = $('#table-received-modern');
        if (!$tbl.length) return;
        const tbody = $tbl.find('tbody');
        tbody.empty();
        rows.forEach(r=>{
            const date = r.received_at || r.created_at || '';
            const tr = `<tr><td>${date}</td><td>${r.item_name||''}</td><td>${r.batch_name||''}</td><td>${r.received||r.distributed||''}</td><td>${r.staff_name||''}</td></tr>`;
            tbody.append(tr);
        });
        if ($.fn.DataTable && !$tbl.hasClass('dataTable')) {
            $tbl.DataTable({ pageLength: 25, responsive: true, order: [[0,'desc']] });
        }
    }

    function fetchMetrics(days){
        const url = statsUrl + (days ? ('&days=' + encodeURIComponent(days)) : '');
        document.getElementById('dashboard-loading') && (document.getElementById('dashboard-loading').style.display = 'inline');
        return fetch(url, { credentials: 'same-origin' }).then(r=>r.json()).then(js=>{
            document.getElementById('dashboard-loading') && (document.getElementById('dashboard-loading').style.display = 'none');
            if (js && js.success && js.data) return js.data; else throw new Error(js && js.message ? js.message : 'Invalid response');
        });
    }

    function init() {
        // Use seeded initial data if available to render quickly
        const initial = window.__inventra_initial_series || {};
        if (initial) {
            // Seed KPIs if counts exist
            if (initial.counts) {
                Object.entries(initial.counts).forEach(([k,v])=>{
                    const el = document.querySelector(`.kpi[data-key="${k}"] .kpi-value`);
                    if (el) el.textContent = v;
                });
            }
            renderCharts({ series: initial.series || initial });
            populateTable(initial.recent_received || []);
        }

        // Wire controls
        const range = document.getElementById('dashboard-range');
        const refreshBtn = document.getElementById('dashboard-refresh');
        function reload(){ const days = range ? parseInt(range.value||31) : 31; fetchMetrics(days).then(data=>{ if (data.counts){ Object.entries(data.counts).forEach(([k,v])=>{ const el = document.querySelector(`.kpi[data-key="${k}"] .kpi-value`); if (el) el.textContent = v; }); } renderCharts(data); populateTable(data.recent_received || []); }).catch(err=>{ console.error(err); alert('Unable to load metrics: '+err.message); }); }
        if (refreshBtn) refreshBtn.addEventListener('click', reload);

        // Initial live load
        reload();

        // Optional auto-refresh every 60s when enabled later
    }

    document.addEventListener('DOMContentLoaded', init);
})();