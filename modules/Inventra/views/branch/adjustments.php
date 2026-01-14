<?php
// views/branch/adjustments.php
?>

<div class="branch-standalone-view">
    <h1>Adjustments</h1>
    <hr />
    <div class="card-content-container-branch-card">
        <?php require __DIR__ . '/components/cards.php'; ?>
    </div>
</div>

<script>
    // Show only the Adjustments sections
    document.addEventListener('DOMContentLoaded', function(){
        document.querySelectorAll('.collapsible-table-wrapper-branch-card, .collapsible-table-wrapper-standalone').forEach(el=>el.style.display='none');
        var el1 = document.getElementById('AdjustmentsCard-branch-card'); if (el1) el1.style.display = 'block';
        var el2 = document.getElementById('AdjustmentsHistoryCard-branch-card'); if (el2) el2.style.display = 'block';
    });
</script>