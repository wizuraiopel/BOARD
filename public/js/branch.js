/**
 * File: Branch Dashboard JavaScript
 * Description: Frontend functionality for branch dashboard
 */

jQuery(document).ready(function($) {
    const ajaxurl = (window._base_url || baseUrl) + '/index.php?action=branch_action';
    const token = window._inventra_csrf || window.csrfToken || '';

    function branchInit($scope) {
        $scope = $scope || $(document);

        // Card Toggle Logic (support branch + standalone cards)
        $('.card-branch-card, .card-standalone').off('click.inventra').on('click.inventra', function() {
        const targetId = $(this).data('target');
        const $activeContent = $('#' + targetId);
        
        // Toggle active class on cards
        $('.card-branch-card, .card-standalone').removeClass('active');
        $(this).addClass('active');
        
        // Hide all content sections (support both class names)
        $('.collapsible-table-wrapper-branch-card, .collapsible-table-wrapper-standalone').removeClass('active').hide();
        
        // Show the target content section
        $activeContent.addClass('active').show();
        
        // Initialize DataTables if not already done (support branch + admin standalone table classes)
        const $table = $activeContent.find('.wp-list-table-branch-card:not(.dataTable), .wp-list-table-standalone:not(.dataTable)');
        if ($table.length) {
            if ($.fn.DataTable && !$table.hasClass('dataTable')) {
                try {
                    $table.DataTable({
                        pageLength: 25,
                        responsive: true,
                        dom: 'Bfrtip',
                        buttons: ['copyHtml5', 'csvHtml5', 'excelHtml5', 'pdfHtml5', 'print', 'colVis'],
                        order: [[0, 'desc']],
                        columnDefs: [
                            { orderable: false, targets: '_all' }
                        ]
                    });
                    console.log('DataTable initialized for:', $table.attr('id'));
                } catch (e) {
                    console.error('DataTable initialization error for:', $table.attr('id'), e);
                }
            }
        }
    }

    // Run initial bind
    branchInit(jQuery);

    // Re-run init after AJAX page loads
    document.addEventListener('inventra:pageLoaded', function(){
        branchInit(jQuery);
    });
    
    // AJAX Request Handler
    function handleAjaxRequest(action, data) {
        // attach CSRF token as either _wpnonce or csrf_token for compatibility
        data._wpnonce = data._wpnonce || token;
        data.csrf_token = data.csrf_token || token;
        $.post(ajaxurl, data, function(response) {
            console.log('AJAX response:', response);
            
            if (response.success) {
                Swal.fire({
                    title: 'Success',
                    text: response.message,
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    title: 'Error',
                    text: response.message || 'An unknown error occurred.',
                    icon: 'error'
                });
            }
        }).fail(function(xhr, status, error) {
            console.error('AJAX error:', status, error);
            Swal.fire({
                title: 'Error',
                text: 'Network error or server issue.',
                icon: 'error'
            });
        });
    }
    
    // Mark Received Logic
    $(document).off('click.inventra', '.mark-received-btn-branch-card').on('click.inventra', '.mark-received-btn-branch-card', function() {
        const $btn = $(this);
        const distId = $btn.data('dist-id');
        const distributedQty = parseInt($btn.data('distributed'));
        const itemName = $btn.data('item-name');
        const batchName = $btn.data('batch-name');
        
        Swal.fire({
            title: `Mark <strong>${itemName} (${batchName})</strong> as Received`,
            html: `
                <div style="text-align: left; margin-bottom: 15px;">
                    <p><strong>Distributed Quantity:</strong> ${distributedQty}</p>
                </div>
                <div class="inventra-swal-form-group">
                    <label class="inventra-swal-label">Received Quantity <span style="color:red;">*</span></label>
                    <input id="swal-input-qty" type="number" class="inventra-swal-input" min="0" max="${distributedQty}" value="${distributedQty}" required>
                </div>
                <div id="swal-dispute-reason-group" class="inventra-swal-form-group" style="display:none;">
                    <label class="inventra-swal-label">Dispute Reason <span style="color:red;">*</span></label>
                    <textarea id="swal-input-reason" class="inventra-swal-textarea" placeholder="e.g., Damaged, Missing items" required></textarea>
                    <div style="font-size:0.85em; color:#666; margin-top:5px;">Required because received quantity is less than allocated.</div>
                </div>
                <div class="inventra-swal-form-group">
                    <label class="inventra-swal-label">Staff Name <span style="color:red;">*</span></label>
                    <input id="swal-input-staff" type="text" class="inventra-swal-input" placeholder="Enter your name" required>
                </div>
            `,
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: 'Confirm Receipt',
            cancelButtonText: 'Cancel',
            customClass: {
                popup: 'inventra-swal-popup',
                title: 'inventra-swal-title',
                content: 'inventra-swal-content',
                confirmButton: 'inventra-swal-confirm',
                cancelButton: 'inventra-swal-cancel',
                actions: 'inventra-swal-actions'
            },
            preConfirm: () => {
                const qty = parseInt($('#swal-input-qty').val());
                const reason = $('#swal-input-reason').val().trim();
                const staff = $('#swal-input-staff').val().trim();
                
                if (!staff) {
                    Swal.showValidationMessage('Staff name is required.');
                    return false;
                }
                if (isNaN(qty) || qty < 0 || qty > distributedQty) {
                    Swal.showValidationMessage(`Please enter a valid quantity (0-${distributedQty}).`);
                    return false;
                }
                if (qty < distributedQty && !reason) {
                    Swal.showValidationMessage('Dispute reason is required when quantity is less.');
                    return false;
                }
                
                return { qty, reason, staff };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const data = {
                    action_type: 'receive',
                    dist_id: distId,
                    received_qty: result.value.qty,
                    staff_name: result.value.staff,
                    dispute_reason: result.value.reason,
                    csrf_token: token
                };
                
                handleAjaxRequest('receive', data);
            }
        });
        
        // Handle quantity change to show/hide dispute reason
        $(document).on('input', '#swal-input-qty', function() {
            const qty = parseInt($(this).val()) || 0;
            if (qty < distributedQty) {
                $('#swal-dispute-reason-group').show();
            } else {
                $('#swal-dispute-reason-group').hide();
            }
        });
    });
    
    // Initiate Transfer Logic
    $('#initiate-transfer-btn-branch-card').off('click.inventra').on('click.inventra', function() {
        $('#transfer-form-branch-card')[0].reset();
        $('#transfer-batch-name-branch-card').val('');
        $('#transfer-batch-id-branch-card').val('');
        $('#transfer-available-qty-branch-card').text('0');
        $('#transfer-quantity-branch-card').prop('disabled', true).attr('max', 0);
        $('#transfer-modal-backdrop-branch-card, #transfer-modal-branch-card').fadeIn();
    });
    
    // Transfer Item Selection
    $('#transfer-item-id-branch-card').off('change.inventra').on('change.inventra', function() {
        const selectedOption = $(this).find('option:selected');
        const maxQty = parseInt(selectedOption.data('max-qty')) || 0;
        const batchId = selectedOption.data('batch-id') || '';
        const batchName = selectedOption.text().match(/Batch: ([^,]+)/)?.[1] || '';
        
        if (batchId && maxQty > 0) {
            $('#transfer-batch-name-branch-card').val(batchName);
            $('#transfer-batch-id-branch-card').val(batchId);
            $('#transfer-available-qty-branch-card').text(maxQty);
            $('#transfer-quantity-branch-card').prop('disabled', false).attr('max', maxQty).val('');
        } else {
            $('#transfer-batch-name-branch-card').val('');
            $('#transfer-batch-id-branch-card').val('');
            $('#transfer-available-qty-branch-card').text('0');
            $('#transfer-quantity-branch-card').prop('disabled', true).attr('max', 0);
        }
    });
    
    // Transfer Quantity Validation
    $('#transfer-quantity-branch-card').off('input.inventra').on('input.inventra', function() {
        const maxQty = parseInt($(this).attr('max')) || 0;
        const value = parseInt($(this).val()) || 0;
        
        if (value > maxQty) {
            $(this).val(maxQty);
            Swal.fire('Error', `Quantity cannot exceed available stock: ${maxQty}`, 'error');
        }
        if (value <= 0) {
            $(this).val('');
            Swal.fire('Error', 'Please enter a valid quantity.', 'error');
        }
    });
    
    // Transfer Form Submission
    $('#transfer-form-branch-card').off('submit.inventra').on('submit.inventra', function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        const data = formData + '&action_type=initiate_transfer&csrf_token=' + token;
        
        handleAjaxRequest('initiate_transfer', data);
    });
    
    // Cancel Transfer Modal
    $('#btn-cancel-transfer-branch-card, #transfer-modal-backdrop-branch-card').off('click.inventra').on('click.inventra', function() {
        $('#transfer-modal-backdrop-branch-card, #transfer-modal-branch-card').fadeOut();
    });
    
    // Receive Transfer Logic
    $(document).off('click.inventra', '.receive-transfer-btn-branch-card').on('click.inventra', '.receive-transfer-btn-branch-card', function() {
        const $btn = $(this);
        const transferId = $btn.data('transfer-id');
        const quantity = parseInt($btn.data('quantity'));
        const itemName = $btn.data('item-name');
        const batchName = $btn.data('batch-name');
        
        Swal.fire({
            title: `Mark <strong>${itemName} (${batchName})</strong> as Received`,
            html: `
                <div style="text-align: left; margin-bottom: 15px;">
                    <p><strong>Allocated Quantity:</strong> ${quantity}</p>
                </div>
                <div class="inventra-swal-form-group">
                    <label class="inventra-swal-label">Received Quantity <span style="color:red;">*</span></label>
                    <input id="swal-input-qty" type="number" class="inventra-swal-input" min="0" max="${quantity}" value="${quantity}" required>
                </div>
                <div id="swal-dispute-reason-group" class="inventra-swal-form-group" style="display:none;">
                    <label class="inventra-swal-label">Dispute Reason <span style="color:red;">*</span></label>
                    <textarea id="swal-input-reason" class="inventra-swal-textarea" placeholder="e.g., Damaged, Missing items" required></textarea>
                    <div style="font-size:0.85em; color:#666; margin-top:5px;">Required because received quantity is less than allocated.</div>
                </div>
                <div class="inventra-swal-form-group">
                    <label class="inventra-swal-label">Staff Name <span style="color:red;">*</span></label>
                    <input id="swal-input-staff" type="text" class="inventra-swal-input" placeholder="Enter your name" required>
                </div>
            `,
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: 'Confirm Receipt',
            cancelButtonText: 'Cancel',
            customClass: {
                popup: 'inventra-swal-popup',
                title: 'inventra-swal-title',
                content: 'inventra-swal-content',
                confirmButton: 'inventra-swal-confirm',
                cancelButton: 'inventra-swal-cancel',
                actions: 'inventra-swal-actions'
            },
            preConfirm: () => {
                const qty = parseInt($('#swal-input-qty').val());
                const reason = $('#swal-input-reason').val().trim();
                const staff = $('#swal-input-staff').val().trim();
                
                if (!staff) {
                    Swal.showValidationMessage('Staff name is required.');
                    return false;
                }
                if (isNaN(qty) || qty < 0 || qty > quantity) {
                    Swal.showValidationMessage(`Please enter a valid quantity (0-${quantity}).`);
                    return false;
                }
                if (qty < quantity && !reason) {
                    Swal.showValidationMessage('Dispute reason is required when quantity is less.');
                    return false;
                }
                
                return { qty, reason, staff };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const data = {
                    action_type: 'receive_transfer',
                    transfer_id: transferId,
                    received_qty: result.value.qty,
                    staff_name: result.value.staff,
                    dispute_reason: result.value.reason,
                    csrf_token: token
                };
                
                handleAjaxRequest('receive_transfer', data);
            }
        });
        
        // Handle quantity change to show/hide dispute reason
        $(document).on('input', '#swal-input-qty', function() {
            const qty = parseInt($(this).val()) || 0;
            if (qty < quantity) {
                $('#swal-dispute-reason-group').show();
            } else {
                $('#swal-dispute-reason-group').hide();
            }
        });
    });
    
    // Request Adjustment Logic
    $(document).off('click.inventra', '.adjustment-btn-branch-card').on('click.inventra', '.adjustment-btn-branch-card', function() {
        const $btn = $(this);
        const itemId = $btn.data('item-id');
        const currentStock = $btn.data('current-stock');
        
        Swal.fire({
            title: 'Request Inventory Adjustment',
            html: `<div style="text-align: left; margin-bottom: 15px;"><p><strong>Current Stock:</strong> ${currentStock}</p></div>` +
                  `<div class="inventra-swal-form-group">
                      <label class="inventra-swal-label">Adjustment Type <span style="color:red;">*</span></label>
                      <select id="swal-select-type" class="inventra-swal-select" required>
                          <option value="">-- Select Type --</option>
                          <option value="redeem">Redeem</option>
                          <option value="damaged">Damaged</option>
                          <option value="stolen">Stolen</option>
                          <option value="missing">Missing</option>
                          <option value="found">Found</option>
                          <option value="other (add)">Other (Add)</option>
                          <option value="other (deduct)">Other (Deduct)</option>
                          <option value="update">Update</option>
                      </select>
                  </div>` +
                  `<div id="swal-redeem-instruction-group" class="inventra-swal-form-group" style="display: none; text-align: left; color: #666; font-size: 0.9em; margin-top: -10px; margin-bottom: 10px;">
                       <p><strong>Reason Instruction:</strong> Enter the Member ID and the quantity redeemed (e.g., "M12345=2").</p>
                  </div>` +
                  `<div id="swal-update-instruction-group" class="inventra-swal-form-group" style="display: none; text-align: left; color: #666; font-size: 0.9em; margin-top: -10px; margin-bottom: 10px;">
                       <p><strong>Note:</strong> Monthly stock verification - confirms physical review with no quantity change.</p>
                  </div>` +
                  `<div class="inventra-swal-form-group">
                      <label class="inventra-swal-label">Quantity <span style="color:red;">*</span></label>
                      <input id="swal-input-adj-qty" type="number" class="inventra-swal-input" min="0" required>
                  </div>` +
                  `<div class="inventra-swal-form-group">
                      <label class="inventra-swal-label">Reason <span style="color:red;">*</span></label>
                      <textarea id="swal-input-adj-reason" class="inventra-swal-textarea" placeholder="Describe the reason for this adjustment..." required></textarea>
                  </div>` +
                  `<div class="inventra-swal-form-group">
                      <label class="inventra-swal-label">Staff Name <span style="color:red;">*</span></label>
                      <input id="swal-input-adj-staff" type="text" class="inventra-swal-input" placeholder="Enter your name" required>
                  </div>`,
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: 'Submit Request',
            cancelButtonText: 'Cancel',
            customClass: {
                popup: 'inventra-swal-popup',
                title: 'inventra-swal-title',
                content: 'inventra-swal-content',
                confirmButton: 'inventra-swal-confirm',
                cancelButton: 'inventra-swal-cancel',
                actions: 'inventra-swal-actions'
            },
            didOpen: () => {
                const $qtyInput = $('#swal-input-adj-qty');
                $('#swal-select-type').on('change', function () {
                    const selectedType = $(this).val();
                    
                    if (selectedType === 'redeem') {
                        $('#swal-redeem-instruction-group').show();
                    } else {
                        $('#swal-redeem-instruction-group').hide();
                    }
                    
                    if (selectedType === 'update') {
                        $('#swal-update-instruction-group').show();
                        $qtyInput.val(0).prop('disabled', true);
                    } else {
                        $('#swal-update-instruction-group').hide();
                        $qtyInput.prop('disabled', false).val('');
                    }
                });
                
                $qtyInput.prop('disabled', false);
                $('#swal-redeem-instruction-group, #swal-update-instruction-group').hide();
            },
            preConfirm: () => {
                const type = $('#swal-select-type').val();
                const qty = parseInt($('#swal-input-adj-qty').val());
                const reason = $('#swal-input-adj-reason').val().trim();
                const staff = $('#swal-input-adj-staff').val().trim();
                
                if (!type) {
                    Swal.showValidationMessage('Please select an adjustment type.');
                    return false;
                }
                if (!reason) {
                    Swal.showValidationMessage('Reason is required.');
                    return false;
                }
                if (!staff) {
                    Swal.showValidationMessage('Staff name is required.');
                    return false;
                }
                
                if (type === 'update') {
                    if (isNaN(qty) || qty !== 0) {
                        Swal.showValidationMessage('Quantity must be 0 for "Update" type.');
                        return false;
                    }
                } else {
                    if (isNaN(qty) || qty <= 0) {
                        Swal.showValidationMessage('Please enter a valid quantity (greater than 0).');
                        return false;
                    }
                }
                
                return { type, qty, reason, staff };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const data = {
                    action_type: 'request_adjustment',
                    item_id: itemId,
                    adj_type: result.value.type,
                    adj_qty: result.value.qty,
                    adj_reason: result.value.reason,
                    staff_name: result.value.staff,
                    csrf_token: token
                };
                
                handleAjaxRequest('request_adjustment', data);
            }
        });
    });
});
