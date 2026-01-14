<?php
// views/branch/transfers.php
?>

<div class="branch-standalone-view">
    <h1>Incoming Transfers</h1>
    <hr />
    <div class="card-content-container-branch-card">
        <?php require __DIR__ . '/components/cards.php'; ?>
    </div>
</div>

<script>
    // Show only incoming transfers
    document.addEventListener('DOMContentLoaded', function(){
        document.querySelectorAll('.collapsible-table-wrapper-branch-card, .collapsible-table-wrapper-standalone').forEach(el=>el.style.display='none');
        var el = document.getElementById('InternalTransferCard-branch-card'); if (el) el.style.display = 'block';
        // show incoming transfers area (within InternalTransferCard)
    });
</script>