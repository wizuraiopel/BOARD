<?php
/**
 * Settings Dashboard View
 * Manages inventory items, categories, and batches with tabbed interface
 */
?>

<div id="inventra-combined-settings-dashboard" class="settings-dashboard-container">
    <div class="settings-header">
        <h1>Inventra Settings</h1>
        <p>Manage inventory items, categories, and batches</p>
    </div>

    <!-- Main Navigation Tabs -->
    <div class="settings-tabs" role="tablist">
        <button class="tab-button active" data-tab="inventory-tab" role="tab" aria-selected="true">
            <i class="fas fa-boxes"></i> Inventory
        </button>
        <button class="tab-button" data-tab="batches-tab" role="tab" aria-selected="false">
            <i class="fas fa-layer-group"></i> Batches
        </button>
    </div>

    <!-- Tab Content Container -->
    <div class="settings-content-container">

        <!-- INVENTORY TAB -->
        <div class="tab-content active" id="inventory-tab-content" role="tabpanel">
            <div class="sub-tabs">
                <button class="sub-tab-button active" data-subtab="items-subtab">Items</button>
                <button class="sub-tab-button" data-subtab="categories-subtab">Categories</button>
            </div>

            <!-- Items Sub-Tab -->
            <div class="sub-tab-content active" id="items-subtab">
                <div class="settings-section">
                    <div class="section-header">
                        <h2>Inventory Items</h2>
                        <button class="btn btn-primary toggle-form" data-target="add-item-form">
                            + Add Item
                        </button>
                    </div>

                    <!-- Add Item Form -->
                    <div id="add-item-form" class="form-container" style="display: none;">
                        <h3>Add New Item</h3>
                        <form class="settings-form" data-action="add_item">
                            <div class="form-grid">
                                <div class="form-group full-width">
                                    <label for="item-name">Item Name *</label>
                                    <input type="text" id="item-name" name="item_name" class="form-control" required>
                                </div>
                            </div>
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                            <div class="form-actions">
                                <button type="submit" class="btn btn-success">Add Item</button>
                                <button type="button" class="btn btn-secondary toggle-form" data-target="add-item-form">Cancel</button>
                            </div>
                        </form>
                    </div>

                    <!-- Edit Item Form -->
                    <div id="edit-item-form" class="form-container" style="display: none;">
                        <h3>Edit Item</h3>
                        <form class="settings-form" data-action="edit_item">
                            <div class="form-grid">
                                <div class="form-group full-width">
                                    <label for="edit-item-name">Item Name *</label>
                                    <input type="text" id="edit-item-name" name="item_name" class="form-control" required>
                                </div>
                            </div>
                            <input type="hidden" id="edit-item-id" name="item_id" value="">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                            <div class="form-actions">
                                <button type="submit" class="btn btn-success">Update Item</button>
                                <button type="button" class="btn btn-secondary toggle-form" data-target="edit-item-form">Cancel</button>
                            </div>
                        </form>
                    </div>

                    <!-- Items Table -->
                    <div class="table-container">
                        <table class="wp-list-table widefat striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['id']); ?></td>
                                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-warning edit-item-btn" data-id="<?php echo $item['id']; ?>">Edit</button>
                                            <button class="btn btn-sm btn-danger delete-item-btn" data-id="<?php echo $item['id']; ?>">Delete</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php if (empty($items)): ?>
                            <p class="no-data">No items found. <a href="#" class="toggle-form" data-target="add-item-form">Add one now</a>.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Categories Sub-Tab -->
            <div class="sub-tab-content" id="categories-subtab">
                <div class="settings-section">
                    <div class="section-header">
                        <h2>Item Categories</h2>
                        <button class="btn btn-primary toggle-form" data-target="add-category-form">
                            + Add Category
                        </button>
                    </div>

                    <!-- Add Category Form -->
                    <div id="add-category-form" class="form-container" style="display: none;">
                        <h3>Add New Category</h3>
                        <form class="settings-form" data-action="add_category">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="category-name">Category Name *</label>
                                    <input type="text" id="category-name" name="category_name" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="category-key">Category Key *</label>
                                    <input type="text" id="category-key" name="category_key" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="category-icon">Icon (Emoji)</label>
                                    <input type="text" id="category-icon" name="category_icon" class="form-control" value="📦" maxlength="2">
                                </div>
                            </div>
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                            <div class="form-actions">
                                <button type="submit" class="btn btn-success">Add Category</button>
                                <button type="button" class="btn btn-secondary toggle-form" data-target="add-category-form">Cancel</button>
                            </div>
                        </form>
                    </div>

                    <!-- Categories Grid -->
                    <div class="categories-grid">
                        <?php foreach ($categories as $key => $cat): ?>
                            <div class="category-card" data-key="<?php echo htmlspecialchars($key); ?>">
                                <div class="category-icon"><?php echo htmlspecialchars($cat['icon']); ?></div>
                                <div class="category-info">
                                    <div class="category-name"><?php echo htmlspecialchars($cat['name']); ?></div>
                                    <div class="category-key"><?php echo htmlspecialchars($key); ?></div>
                                </div>
                                <div class="category-actions">
                                    <button class="btn btn-sm btn-warning edit-category-btn" data-key="<?php echo htmlspecialchars($key); ?>">Edit</button>
                                    <button class="btn btn-sm btn-danger delete-category-btn" data-key="<?php echo htmlspecialchars($key); ?>">Delete</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- BATCHES TAB -->
        <div class="tab-content" id="batches-tab-content" role="tabpanel">
            <div class="settings-section">
                <div class="section-header">
                    <h2>Inventory Batches</h2>
                    <button class="btn btn-primary toggle-form" data-target="add-batch-form">
                        + Add Batch
                    </button>
                </div>

                <!-- Add Batch Form -->
                <div id="add-batch-form" class="form-container" style="display: none;">
                    <h3>Add New Batch</h3>
                    <form class="settings-form" data-action="add_batch">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="batch-name">Batch Name *</label>
                                <input type="text" id="batch-name" name="batch_name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="batch-mm-yyyy">Batch MM/YYYY</label>
                                <input type="text" id="batch-mm-yyyy" name="batch_mm_yyyy" class="form-control" placeholder="MM/YYYY">
                            </div>
                            <div class="form-group">
                                <label for="batch-status">Status</label>
                                <select id="batch-status" name="batch_status" class="form-control">
                                    <option value="planning">Planning</option>
                                    <option value="distributing">Distributing</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="batch-supplier">Supplier</label>
                                <input type="text" id="batch-supplier" name="batch_supplier" class="form-control">
                            </div>
                            <div class="form-group full-width">
                                <label for="batch-notes">Notes</label>
                                <textarea id="batch-notes" name="batch_notes" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                        <div class="form-actions">
                            <button type="submit" class="btn btn-success">Add Batch</button>
                            <button type="button" class="btn btn-secondary toggle-form" data-target="add-batch-form">Cancel</button>
                        </div>
                    </form>
                </div>

                <!-- Batches Table -->
                <div class="table-container">
                    <table class="wp-list-table widefat striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>MM/YYYY</th>
                                <th>Status</th>
                                <th>Supplier</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($batches as $batch): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($batch['id']); ?></td>
                                    <td><?php echo htmlspecialchars($batch['name']); ?></td>
                                    <td><?php echo htmlspecialchars($batch['batch_mm_yyyy'] ?? 'N/A'); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo htmlspecialchars($batch['status']); ?>">
                                            <?php echo ucfirst(htmlspecialchars($batch['status'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($batch['supplier'] ?? 'N/A'); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-warning edit-batch-btn" data-id="<?php echo $batch['id']; ?>">Edit</button>
                                        <button class="btn btn-sm btn-danger delete-batch-btn" data-id="<?php echo $batch['id']; ?>">Delete</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if (empty($batches)): ?>
                        <p class="no-data">No batches found. <a href="#" class="toggle-form" data-target="add-batch-form">Add one now</a>.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
/* ========== SETTINGS DASHBOARD STYLES ========== */

.settings-dashboard-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.settings-header {
    margin-bottom: 25px;
}

.settings-header h1 {
    color: #0073aa;
    margin-top: 0;
    font-size: 1.8rem;
    font-weight: 600;
}

.settings-header p {
    color: #646970;
    font-size: 1rem;
    margin-bottom: 0;
}

/* Main Tabs */
.settings-tabs {
    display: flex;
    border-bottom: 2px solid #e2e4e7;
    margin-bottom: 20px;
    gap: 0;
}

.tab-button {
    flex: 1;
    background: none;
    border: none;
    padding: 12px 16px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    color: #4a5568;
    font-size: 0.95rem;
    font-weight: 500;
    border-bottom: 3px solid transparent;
    transition: all 0.2s ease;
    margin-bottom: -2px;
}

.tab-button:hover {
    background: #f0f0f1;
    color: #1a202c;
}

.tab-button.active {
    border-bottom-color: #0073aa;
    color: #0073aa;
    font-weight: 600;
}

/* Sub-Tabs */
.sub-tabs {
    display: flex;
    gap: 8px;
    margin-bottom: 20px;
    background: #f8fafc;
    border-radius: 8px;
    padding: 8px;
}

.sub-tab-button {
    flex: 1;
    background: none;
    border: none;
    padding: 10px 14px;
    cursor: pointer;
    color: #4a5568;
    font-size: 0.9rem;
    font-weight: 500;
    border-radius: 6px;
    transition: all 0.2s ease;
}

.sub-tab-button:hover {
    background: #e2e8f0;
    color: #1a202c;
}

.sub-tab-button.active {
    background: #fff;
    color: #0073aa;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    font-weight: 600;
}

/* Tab Content */
.settings-content-container {
    padding-top: 10px;
}

.tab-content,
.sub-tab-content {
    display: none;
}

.tab-content.active,
.sub-tab-content.active {
    display: block;
    animation: fadeIn 0.3s ease;
}

/* Settings Section */
.settings-section {
    background: #f9f9f9;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    margin-bottom: 20px;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid #e2e8f0;
}

.section-header h2 {
    margin: 0;
    font-size: 1.2rem;
    color: #2d3748;
    font-weight: 600;
}

/* Forms */
.form-container {
    background: #fff;
    padding: 20px;
    margin-bottom: 20px;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.07);
}

.form-container h3 {
    margin-top: 0;
    color: #2d3748;
    font-size: 1.1rem;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 15px;
}

.form-group {
    margin-bottom: 0;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
    font-size: 0.85rem;
    color: #4a5568;
}

.form-control {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #cbd5e0;
    border-radius: 4px;
    font-size: 0.85rem;
    font-family: inherit;
}

.form-control:focus {
    outline: none;
    border-color: #0073aa;
    box-shadow: 0 0 0 3px rgba(0, 115, 170, 0.1);
}

.form-actions {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

/* Tables */
.table-container {
    overflow-x: auto;
    margin-bottom: 20px;
}

.wp-list-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem;
}

.wp-list-table th,
.wp-list-table td {
    padding: 12px;
    text-align: left;
    vertical-align: top;
    border-bottom: 1px solid #e2e8f0;
}

.wp-list-table th {
    background: #0073aa;
    color: #fff;
    font-weight: 600;
}

.wp-list-table tbody tr:hover {
    background: #f8f9fa;
}

/* Buttons */
.btn {
    padding: 8px 12px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 500;
    text-decoration: none;
    display: inline-block;
    text-align: center;
    font-size: 0.85rem;
    transition: all 0.2s ease;
    font-family: inherit;
}

.btn-sm {
    padding: 6px 10px;
    font-size: 0.75rem;
}

.btn-primary {
    background: #0073aa;
    color: white;
}

.btn-primary:hover {
    background: #005a87;
}

.btn-success {
    background: #30b15f;
    color: white;
}

.btn-success:hover {
    background: #1e7e34;
}

.btn-warning {
    background: #ffc107;
    color: #000;
}

.btn-warning:hover {
    background: #e0a800;
}

.btn-danger {
    background: #d63638;
    color: white;
}

.btn-danger:hover {
    background: #c82333;
}

.btn-secondary {
    background: #7e8993;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
}

/* Categories Grid */
.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 15px;
    margin-top: 20px;
}

.category-card {
    background: #fff;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    padding: 15px;
    text-align: center;
    transition: all 0.2s ease;
}

.category-card:hover {
    border-color: #0073aa;
    box-shadow: 0 4px 12px rgba(0, 115, 170, 0.15);
    transform: translateY(-2px);
}

.category-icon {
    font-size: 2.5rem;
    margin-bottom: 10px;
}

.category-info {
    margin-bottom: 10px;
}

.category-name {
    font-weight: 600;
    color: #2d3748;
    font-size: 0.95rem;
    margin-bottom: 4px;
}

.category-key {
    font-size: 0.75rem;
    color: #718096;
}

.category-actions {
    display: flex;
    gap: 4px;
    justify-content: center;
}

/* Status Badges */
.status-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
}

.status-planning {
    background: #cfe9f3;
    color: #003d82;
}

.status-distributing {
    background: #fff8e5;
    color: #856404;
}

.status-completed {
    background: #d4edda;
    color: #155724;
}

.status-cancelled {
    background: #f8d7da;
    color: #721c24;
}

/* Utility */
.no-data {
    text-align: center;
    padding: 40px 20px;
    color: #646970;
    font-style: italic;
}

.no-data a {
    color: #0073aa;
    text-decoration: none;
    font-weight: 500;
}

.no-data a:hover {
    text-decoration: underline;
}

/* Animations */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(5px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive */
@media (max-width: 768px) {
    .settings-tabs {
        flex-direction: column;
    }

    .tab-button {
        border-bottom: none;
        border-left: 3px solid transparent;
        padding-left: 16px;
    }

    .tab-button.active {
        border-left-color: #0073aa;
        border-bottom: none;
    }

    .form-grid {
        grid-template-columns: 1fr;
    }

    .sub-tabs {
        flex-direction: column;
    }

    .sub-tab-button {
        border-radius: 4px;
    }

    .categories-grid {
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    }

    .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    .form-actions {
        flex-direction: column;
    }

    .btn {
        width: 100%;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('input[name="csrf_token"]')?.value || '';

    // Tab switching
    document.querySelectorAll('.tab-button').forEach(btn => {
        btn.addEventListener('click', function() {
            const tabId = this.dataset.tab;
            
            // Deactivate all tabs
            document.querySelectorAll('.tab-button').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            
            // Activate selected tab
            this.classList.add('active');
            document.getElementById(tabId + '-content').classList.add('active');
        });
    });

    // Sub-tab switching
    document.querySelectorAll('.sub-tab-button').forEach(btn => {
        btn.addEventListener('click', function() {
            const subtabId = this.dataset.subtab;
            const parent = this.closest('.tab-content');
            
            // Deactivate all sub-tabs in parent
            parent.querySelectorAll('.sub-tab-button').forEach(b => b.classList.remove('active'));
            parent.querySelectorAll('.sub-tab-content').forEach(c => c.classList.remove('active'));
            
            // Activate selected sub-tab
            this.classList.add('active');
            parent.querySelector('#' + subtabId).classList.add('active');
        });
    });

    // Toggle form visibility
    document.querySelectorAll('.toggle-form').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.dataset.target;
            const form = document.getElementById(targetId);
            if (form) {
                form.style.display = form.style.display === 'none' ? 'block' : 'none';
            }
        });
    });

    // Form submission
    document.querySelectorAll('.settings-form').forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const action = this.dataset.action;
            const formData = new FormData(this);
            formData.append('ajax_action', action);
            formData.append('_wpnonce', csrfToken);

            try {
                const response = await fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            }
        });
    });

    // Edit/Delete item handlers
    document.querySelectorAll('.edit-item-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const itemId = this.dataset.id;
            const row = this.closest('tr');
            const itemName = row.cells[1].textContent.trim();
            
            // Populate edit form
            document.getElementById('edit-item-id').value = itemId;
            document.getElementById('edit-item-name').value = itemName;
            
            // Show edit form
            document.getElementById('edit-item-form').style.display = 'block';
            document.getElementById('add-item-form').style.display = 'none';
            
            // Scroll to form
            document.getElementById('edit-item-form').scrollIntoView({ behavior: 'smooth' });
        });
    });

    document.querySelectorAll('.delete-item-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this item?')) {
                const itemId = this.dataset.id;
                const formData = new FormData();
                formData.append('ajax_action', 'delete_item');
                formData.append('item_id', itemId);
                formData.append('_wpnonce', csrfToken);

                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                }).then(r => r.json()).then(d => {
                    if (d.success) {
                        alert(d.message);
                        location.reload();
                    } else {
                        alert('Error: ' + d.message);
                    }
                });
            }
        });
    });

    // Edit/Delete category handlers
    document.querySelectorAll('.edit-category-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const categoryKey = this.dataset.key;
            alert('Edit functionality to be implemented for category ' + categoryKey);
        });
    });

    document.querySelectorAll('.delete-category-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this category?')) {
                const categoryKey = this.dataset.key;
                const formData = new FormData();
                formData.append('ajax_action', 'delete_category');
                formData.append('category_key', categoryKey);
                formData.append('_wpnonce', csrfToken);

                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                }).then(r => r.json()).then(d => {
                    if (d.success) {
                        alert(d.message);
                        location.reload();
                    } else {
                        alert('Error: ' + d.message);
                    }
                });
            }
        });
    });

    // Edit/Delete batch handlers
    document.querySelectorAll('.edit-batch-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const batchId = this.dataset.id;
            alert('Edit functionality to be implemented for batch ' + batchId);
        });
    });

    document.querySelectorAll('.delete-batch-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this batch?')) {
                const batchId = this.dataset.id;
                const formData = new FormData();
                formData.append('ajax_action', 'delete_batch');
                formData.append('batch_id', batchId);
                formData.append('_wpnonce', csrfToken);

                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                }).then(r => r.json()).then(d => {
                    if (d.success) {
                        alert(d.message);
                        location.reload();
                    } else {
                        alert('Error: ' + d.message);
                    }
                });
            }
        });
    });
});
</script>
