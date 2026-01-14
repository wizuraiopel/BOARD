<?php
// views/branch/allocations.php
?>

<div class="branch-standalone-view">
    <h1>Pending Allocations</h1>
    <hr />
    <div class="card-content-container-branch-card">
        <?php require __DIR__ . '/components/cards.php'; ?>
    </div>
</div>

<script>
    // Show only the Pending Allocations section
    document.addEventListener('DOMContentLoaded', function(){
        document.querySelectorAll('.collapsible-table-wrapper-branch-card, .collapsible-table-wrapper-standalone').forEach(el=>el.style.display='none');
        var el = document.getElementById('PendingAllocationsCard-branch-card'); if (el) el.style.display = 'block';
    });
</script>