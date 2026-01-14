<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/styles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/branch.css">
    <style>
        /* quick fallback if styles.css missing */
        .layout-shell{display:flex;min-height:100vh}
        .sidebar{width:260px;background:#1f2937;color:#fff;padding:20px}
        .sidebar a{display:flex;justify-content:space-between;color:#d1d5db;padding:10px;border-radius:4px;text-decoration:none;margin-bottom:6px}
        .sidebar a.active{background:#111827;color:#fff}
        .badge{background:#ef4444;color:#fff;padding:2px 8px;border-radius:12px;font-size:12px}
        .content-wrap{flex:1;padding:24px;background:#f3f4f6}
        header.topbar{display:flex;justify-content:space-between;align-items:center;padding:12px 24px;background:#fff;border-bottom:1px solid #e5e7eb}
    </style>
</head>
<body>
<?php
// Compute notification counts per menu depending on user role
if (Security::isLoggedIn()) {
    require_once __DIR__ . '/../../modules/Inventra/models/User.php';
    $userModel = new User();
    $currentUser = $userModel->findById($_SESSION['user_id']);

    $adminCounts = ['allocations' => 0, 'adjustments' => 0, 'disputes' => 0, 'transfers' => 0];
    $branchCounts = ['allocations' => 0, 'adjustments' => 0, 'incoming' => 0, 'outgoing' => 0];

    // Fetch role slugs once and use to detect legacy 'um_branch' role membership
    $roleSlugsNav = $userModel->getRoleSlugs($currentUser['id']);

    if ($currentUser && ($currentUser['user_type'] === 'admin' || $currentUser['user_type'] === 'staff')) {
        require_once __DIR__ . '/../../modules/Inventra/models/AdminDashboardModel.php';
        $adm = new AdminDashboardModel();
        $adminCounts = $adm->getOverviewCounts();
    } elseif ($currentUser && (in_array('um_branch', $roleSlugsNav) || in_array($currentUser['user_type'], ['branch', 'branch_user']))) {
        require_once __DIR__ . '/../../modules/Inventra/models/BranchDashboardModel.php';
        $bm = new BranchDashboardModel();
        $branchData = $bm->getDashboardData($currentUser['id']);
        $branchCounts['allocations'] = count($branchData['pending_allocations'] ?? []);
        $branchCounts['adjustments'] = count($branchData['pending_adjustments'] ?? []);
        $branchCounts['incoming'] = $branchData['pending_incoming_count'] ?? 0;
        $branchCounts['outgoing'] = $branchData['pending_outgoing_count'] ?? 0;
    }

    // Determine whether to show top-level Configuration link (only for superadmin/sysadmin or admin user_type)
    $canConfigAccess = false;
    if ($currentUser) {
        $roleSlugsNav = $userModel->getRoleSlugs($currentUser['id']);
        $allowedRolesNav = ['superadmin', 'sysadmin'];
        $canConfigAccess = ($currentUser['user_type'] === 'admin') || (bool) array_intersect($roleSlugsNav, $allowedRolesNav);
    }
}
?>

<?php $currentAction = $_GET['action'] ?? ''; ?>
<div class="layout-shell">
    <?php $currentAction = $_GET['action'] ?? ''; ?>
    <?php $showSidebar = Security::isLoggedIn() && $currentAction !== 'login'; ?>
    <?php if ($showSidebar): ?>
    <aside class="sidebar<?= $isBranch ? ' branch-compact' : '' ?>" id="app-sidebar">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 style="margin:0;">Dev B.O.A.R.D</h2>
            <button id="sidebar-toggle" aria-label="Toggle sidebar" title="Toggle sidebar" style="background:none;border:none;color:#d1d5db;cursor:pointer;font-size:18px">⇆</button>
        </div>
        <?php if (Security::isLoggedIn()): ?>
            <?php if ($currentUser['user_type'] === 'admin' || $currentUser['user_type'] === 'staff'): ?>
                <a class="<?= $currentAction === 'admin_dashboard_modern' ? 'active' : '' ?>" href="<?= BASE_URL ?>/index.php?action=admin_dashboard_modern"><span class="s-icon">🏠</span><span class="s-label"> Admin Dashboard <span class="badge"><?= $adminCounts['allocations'] + $adminCounts['adjustments'] + $adminCounts['disputes'] + $adminCounts['transfers'] ?></span></span></a>
                <a class="<?= $currentAction === 'admin_allocations' ? 'active' : '' ?>" href="<?= BASE_URL ?>/index.php?action=admin_allocations"><span class="s-icon">📦</span><span class="s-label"> Allocations <span class="badge"><?= $adminCounts['allocations'] ?></span></span></a>
                <a class="<?= $currentAction === 'admin_adjustments' ? 'active' : '' ?>" href="<?= BASE_URL ?>/index.php?action=admin_adjustments"><span class="s-icon">🔧</span><span class="s-label"> Adjustments <span class="badge"><?= $adminCounts['adjustments'] ?></span></span></a>
                <a class="<?= $currentAction === 'admin_disputes' ? 'active' : '' ?>" href="<?= BASE_URL ?>/index.php?action=admin_disputes"><span class="s-icon">⚠️</span><span class="s-label"> Disputes <span class="badge"><?= $adminCounts['disputes'] ?></span></span></a>
                <a class="<?= $currentAction === 'admin_transfers' ? 'active' : '' ?>" href="<?= BASE_URL ?>/index.php?action=admin_transfers"><span class="s-icon">🚚</span><span class="s-label"> Transfers <span class="badge"><?= $adminCounts['transfers'] ?></span></span></a>
                <a class="<?= $currentAction === 'admin_inventory' ? 'active' : '' ?>" href="<?= BASE_URL ?>/index.php?action=admin_inventory"><span class="s-icon">📂</span><span class="s-label"> Inventory</span></a>
                <a class="<?= $currentAction === 'settings' ? 'active' : '' ?>" href="<?= BASE_URL ?>/index.php?action=settings"><span class="s-icon">⚙️</span><span class="s-label"> Settings</span></a>
                <a href="<?= BASE_URL ?>/index.php?action=logout"><span class="s-icon">🔒</span><span class="s-label"> Logout</span></a>
            <?php elseif (in_array('um_branch', $roleSlugsNav) || in_array($currentUser['user_type'], ['branch', 'branch_user'])): ?>
                <a class="<?= in_array($currentAction, ['branch_dashboard','branch_dashboard_modern']) ? 'active' : '' ?>" href="<?= BASE_URL ?>/index.php?action=branch_dashboard_modern"><span class="s-icon">🏠</span><span class="s-label"> Branch Dashboard <span class="badge"><?= $branchCounts['allocations'] + $branchCounts['adjustments'] + $branchCounts['incoming'] + $branchCounts['outgoing'] ?></span></span></a>
                <a class="<?= $currentAction === 'branch_allocations' ? 'active' : '' ?>" href="<?= BASE_URL ?>/index.php?action=branch_allocations"><span class="s-icon">📦</span><span class="s-label"> Pending Allocations <span class="badge"><?= $branchCounts['allocations'] ?></span></span></a>
                <a class="<?= $currentAction === 'branch_adjustments' ? 'active' : '' ?>" href="<?= BASE_URL ?>/index.php?action=branch_adjustments"><span class="s-icon">🔧</span><span class="s-label"> Adjustments <span class="badge"><?= $branchCounts['adjustments'] ?></span></span></a>
                <a class="<?= $currentAction === 'branch_transfers' ? 'active' : '' ?>" href="<?= BASE_URL ?>/index.php?action=branch_transfers"><span class="s-icon">📥</span><span class="s-label"> Incoming <span class="badge"><?= $branchCounts['incoming'] ?></span></span></a>
                <a class="<?= $currentAction === 'branch_transfers_out' ? 'active' : '' ?>" href="<?= BASE_URL ?>/index.php?action=branch_transfers_out"><span class="s-icon">📤</span><span class="s-label"> Outgoing <span class="badge"><?= $branchCounts['outgoing'] ?></span></span></a>
                <a class="<?= $currentAction === 'settings' ? 'active' : '' ?>" href="<?= BASE_URL ?>/index.php?action=settings"><span class="s-icon">⚙️</span><span class="s-label"> Settings</span></a>
                <a href="<?= BASE_URL ?>/index.php?action=logout"><span class="s-icon">🔒</span><span class="s-label"> Logout</span></a>
            <?php else: ?>
                <a class="<?= $currentAction === 'dashboard' ? 'active' : '' ?>" href="<?= BASE_URL ?>/index.php?action=dashboard"><span class="s-icon">🏠</span><span class="s-label"> Dashboard</span></a>
                <a href="<?= BASE_URL ?>/index.php?action=logout"><span class="s-icon">🔒</span><span class="s-label"> Logout</span></a>
            <?php endif; ?>
        <?php else: ?>
            <a href="<?= BASE_URL ?>/index.php?action=login"><span class="s-icon">🔑</span><span class="s-label"> Login</span></a>
        <?php endif; ?>
    </aside>
    <?php endif; ?>

    <div class="content-wrap">
        <header class="topbar">
            <div style="display:flex;align-items:center;gap:18px">
                <div style="display:flex;align-items:center;gap:12px">
                    <a href="<?= BASE_URL ?>/index.php?action=admin_dashboard_modern"><img src="<?= BASE_URL ?>/public/images/logo.svg" alt="Logo" style="height:34px;object-fit:contain;margin-right:6px"></a>
                    <strong><?= htmlspecialchars($title) ?></strong>
                </div>
                <nav style="margin-left:18px">
                    <?php if (!empty($canConfigAccess)): ?>
                        <a href="<?= BASE_URL ?>/index.php?action=configuration" style="margin-right:10px;">⚙️ Configuration</a>
                    <?php endif; ?>
                    <a href="<?= BASE_URL ?>/index.php?action=admin_dashboard_modern" style="margin-right:10px;">Inventra</a>
                    <a href="<?= BASE_URL ?>/index.php?action=cashops_dashboard" style="margin-right:10px;">CashOps</a>
                    <a href="<?= BASE_URL ?>/index.php?action=kpi_dashboard">KPI</a>
                </nav>
            </div>
            <div>
                <?php if (Security::isLoggedIn()): ?>
                    Welcome, <?= htmlspecialchars($currentUser['username'] ?? 'User') ?>
                <?php endif; ?>
            </div>
        </header>

        <script>
            // Sidebar collapse/expand handler (persist in localStorage)
            (function(){
                var btn = document.getElementById('sidebar-toggle');
                var sidebar = document.getElementById('app-sidebar');
                if (!btn || !sidebar) return;
                var state = localStorage.getItem('sidebar_collapsed');
                // Default to collapsed for branch users unless they previously set a preference
                if (state === null && sidebar.classList.contains('branch-compact')) {
                    sidebar.classList.add('collapsed');
                } else if (state === '1') {
                    sidebar.classList.add('collapsed');
                }
                btn.addEventListener('click', function(){
                    var is = sidebar.classList.toggle('collapsed');
                    localStorage.setItem('sidebar_collapsed', is ? '1' : '0');
                });
            })();
        </script>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <?= $content ?>

        <footer style="margin-top:24px;font-size:13px;color:#6b7280">&copy; <?= date('Y') ?> Dev B.O.A.R.D. All rights reserved.</footer>
    </div>
</div>

    <script src="<?= BASE_URL ?>/public/js/branch.js"></script>
    <script src="<?= BASE_URL ?>/public/js/app.js"></script>
    <script>
        // Expose CSRF token and base URL to frontend scripts
        (function(){
            try{
                // Ensure session is active before reading token
                if (typeof SessionManager !== 'undefined') {
                    // no-op; SessionManager is available via PHP include below
                }
            }catch(e){}
        })();
    </script>
    <?php
    // Expose base URL and the existing CSRF token (do not overwrite session token)
    // Ensure session started so we can read the stored token
    require_once __DIR__ . '/../../core/SessionManager.php';
    SessionManager::startSecureSession();
    $exposedCsrf = $_SESSION[CSRF_TOKEN_NAME] ?? null;
    if (empty($exposedCsrf)) {
        // Only generate a token if none exists in session
        $exposedCsrf = Security::generateCsrfToken();
    }
    ?>
    <script>
        window._base_url = '<?= BASE_URL ?>';
        window._inventra_csrf = '<?= $exposedCsrf ?>';
    </script>
</body>
</html>