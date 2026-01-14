<?php
// views/branch/dashboard.php
?>

<div id="inventra-branch-dashboard-card-view">
    <h1>Branch Dashboard</h1>
    <hr>
    <!-- Top menu buttons (match admin UI) -->
    <div class="inventra-top-menu" role="toolbar" aria-label="Branch quick menu">
        <button class="top-menu-btn active" data-target="ReceivedItemsCard-branch-card">Received <span class="tm-count"><?= (int)($branch_counts['incoming'] ?? 0) ?></span></button>
        <button class="top-menu-btn" data-target="AdjustmentsCard-branch-card">Adjustment <span class="tm-count"><?= (int)($branch_counts['adjustments'] ?? 0) ?></span></button>
        <button class="top-menu-btn" data-target="DisputesCard-branch-card">Disputes</button>
        <button class="top-menu-btn" data-target="InternalTransferCard-branch-card">Transfer</button>
        <button class="top-menu-btn" data-target="InventoryCard-branch-card">Branch Inventory</button>
    </div>

    <!-- Overview Cards -->
    <div class="card-container-branch-card card-container-standalone">
        <?php foreach ($overview_cards as $card): ?>
        <div class="card-branch-card card-standalone <?php echo $card['class']; ?>" data-target="<?php echo $card['target']; ?>">
            <div class="card-icon-branch-card card-icon-standalone"><?php echo $card['icon']; ?></div>
            <div class="card-title-branch-card card-title-standalone"><?php echo $card['count']; ?></div>
            <div class="card-subtitle-branch-card card-subtitle-standalone"><?php echo $card['subtitle']; ?></div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Card Content Sections -->
    <div class="card-content-container-branch-card">
        <?php require __DIR__ . '/components/cards.php'; ?>
        <?php require __DIR__ . '/components/tables.php'; ?>
        <?php require __DIR__ . '/components/modals.php'; ?>
    </div>
</div>


<link rel="stylesheet" href="<?= BASE_URL ?>/public/css/branch.css">
<script>
// Define variables that branch.js needs
const baseUrl = '<?= BASE_URL ?>';
const csrfToken = '<?= $_SESSION['csrf_token'] ?? '' ?>';
</script>
<script src="<?= BASE_URL ?>/public/js/branch.js"></script>
<script>
    // Top menu toggles for branch dashboard
    (function(){
        document.addEventListener('click', function(e){
            var top = e.target.closest('.top-menu-btn');
            if (!top) return;
            var tgt = top.getAttribute('data-target');
            if (!tgt) return;
            document.querySelectorAll('.top-menu-btn').forEach(b=>b.classList.remove('active'));
            top.classList.add('active');
            // hide/show sections (cards and component areas can use IDs)
            var sections = ['ReceivedItemsCard-branch-card','AdjustmentsCard-branch-card','DisputesCard-branch-card','InternalTransferCard-branch-card','InventoryCard-branch-card'];
            sections.forEach(s=>{ var el = document.getElementById(s); if (el) el.style.display = (s===tgt)?'block':'none'; });
        });
    })();
</script>
