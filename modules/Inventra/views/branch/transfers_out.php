<?php
// views/branch/transfers_out.php
?>

<div class="branch-standalone-view">
    <h1>Outgoing Transfers</h1>
    <hr />
    <div class="card-content-container-branch-card">
        <?php require __DIR__ . '/components/cards.php'; ?>
    </div>
</div>

<script>
    // Show only outgoing transfers
    document.addEventListener('DOMContentLoaded', function(){
        document.querySelectorAll('.collapsible-table-wrapper-branch-card, .collapsible-table-wrapper-standalone').forEach(el=>el.style.display='none');
        var el = document.getElementById('InternalTransferCard-branch-card'); if (el) el.style.display = 'block';
        // show outgoing section by toggling or scrolling
        // There is only one InternalTransferCard with both incoming and outgoing tables; we keep both but visually scroll to outgoing
        var outTbl = document.getElementById('table-outgoing-transfer-branch-card'); if (outTbl) outTbl.scrollIntoView();
    });
</script>