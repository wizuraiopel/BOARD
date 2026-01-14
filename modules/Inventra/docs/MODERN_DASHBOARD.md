Modern Branch Dashboard Prototype

Overview
- Prototype route: /index.php?action=branch_dashboard_modern (Branch users only)
- API endpoint for metrics: /index.php?action=branch_dashboard_stats (JSON, authenticated)

What it includes
- KPI strip (counts)
- 31-day Received and Adjustments charts (Chart.js)
- Recent Received table (DataTables)

Verification steps
1. Start your web server and open the app.
2. Log in as a branch user.
3. Visit: /index.php?action=branch_dashboard_modern
4. Confirm the KPIs display counts, charts render, and the Recent Received table shows rows.
5. Check browser console for errors and network tab for the call to branch_dashboard_stats.

Next steps / Enhancements
- Add more metrics/visuals (inventory trends, allocation heatmap).
- Add server-side DataTables paging for large datasets.
- Replace inline styling with a theme or Tailwind for consistent modern UI.
