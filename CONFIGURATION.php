<?php
/**
 * SETTINGS DASHBOARD ROUTING CONFIGURATION
 * 
 * Add these routes to your Router.php to enable the Settings Dashboard
 */

// Example Router configuration in core/Router.php:
$routes = [
    // ... existing routes ...
    
    // Settings Dashboard Routes
    'settings' => 'SettingsController@index',           // Display settings page
    'settings_ajax' => 'SettingsController@handleAjax', // Handle AJAX requests

    // Configuration (Roles & Permissions)
    'configuration' => 'ConfigurationController@index',            // Configuration UI
    'configuration_ajax' => 'ConfigurationController@handleAjax',  // Configuration AJAX handler

    // Admin Dashboard Routes (Enhanced)
    'admin_dashboard' => 'AdminController@index',       // Admin dashboard view
    'admin_ajax' => 'AdminController@handleAjax',       // Admin AJAX handler
    
    // Branch Dashboard Routes
    'branch_dashboard' => 'BranchController@index',
    'branch_action' => 'BranchController@handleAjax',
    
    // Authentication Routes
    'login' => 'AuthController@showLoginForm',
    'logout' => 'AuthController@logout',
    
    // Default Dashboard (role-based routing)
    'dashboard' => 'DashboardController@index',
];

/**
 * QUICK START GUIDE
 * 
 * 1. Ensure database tables exist (see ENHANCEMENTS.md)
 * 2. Update config/config.php with database credentials
 * 3. Add routes above to your Router
 * 4. Access dashboard at: /index.php?action=settings
 * 
 * ROLE REQUIREMENTS:
 * - Settings Dashboard: Requires admin user role
 * - Branch Dashboard: Requires branch user role
 * - Admin Dashboard: Requires admin user role
 */

// ============================================================
// SUGGESTED DATABASE TABLE STRUCTURES
// ============================================================

/*
-- Inventory Items
CREATE TABLE IF NOT EXISTS inventra_inventory_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(100) UNIQUE NOT NULL,
    category VARCHAR(100),
    unit_value DECIMAL(10, 2),
    image_url TEXT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Batches
CREATE TABLE IF NOT EXISTS inventra_batches (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    batch_mm_yyyy VARCHAR(7),
    status ENUM('planning', 'distributing', 'completed', 'cancelled') DEFAULT 'planning',
    supplier VARCHAR(255),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Allocations (referenced by Admin AJAX handlers)
CREATE TABLE IF NOT EXISTS inventra_allocations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    branch_id INT NOT NULL,
    item_id INT NOT NULL,
    batch_id INT NOT NULL,
    quantity INT NOT NULL,
    status ENUM('pending', 'approved', 'declined', 'received') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES inventra_inventory_items(id),
    FOREIGN KEY (batch_id) REFERENCES inventra_batches(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Adjustments (referenced by Admin AJAX handlers)
CREATE TABLE IF NOT EXISTS inventra_adjustments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    branch_id INT NOT NULL,
    item_id INT NOT NULL,
    adjustment_type ENUM('damage', 'loss', 'repair', 'update') DEFAULT 'update',
    quantity INT NOT NULL,
    reason TEXT,
    status ENUM('pending', 'approved', 'declined') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES inventra_inventory_items(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Disputes (referenced by Admin AJAX handlers)
CREATE TABLE IF NOT EXISTS inventra_disputes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    allocation_id INT NOT NULL,
    branch_id INT NOT NULL,
    reason TEXT,
    status ENUM('pending', 'approved', 'declined') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (allocation_id) REFERENCES inventra_allocations(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Branch Transfers (referenced by Admin AJAX handlers)
CREATE TABLE IF NOT EXISTS inventra_branch_transfers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    from_branch_id INT NOT NULL,
    to_branch_id INT NOT NULL,
    item_id INT NOT NULL,
    batch_id INT NOT NULL,
    quantity INT NOT NULL,
    status ENUM('pending', 'approved', 'declined', 'received') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES inventra_inventory_items(id),
    FOREIGN KEY (batch_id) REFERENCES inventra_batches(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inventory (Stock tracking)
CREATE TABLE IF NOT EXISTS inventra_inventory (
    id INT PRIMARY KEY AUTO_INCREMENT,
    branch_id INT NOT NULL,
    item_id INT NOT NULL,
    batch_id INT NOT NULL,
    quantity INT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_branch_item_batch (branch_id, item_id, batch_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- RBAC / ACCESS CONTROL (roles, role assignments, modules, features, permissions)
-- Run these statements on your DB to add role/permission tables and seed default roles/modules.

-- Users (if not already created)
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(200) DEFAULT NULL,
    password_hash VARCHAR(255) NOT NULL,
    user_type ENUM('admin','staff','branch') DEFAULT 'staff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Roles
CREATE TABLE IF NOT EXISTS roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Mapping users to roles (many-to-many)
CREATE TABLE IF NOT EXISTS role_user (
    id INT PRIMARY KEY AUTO_INCREMENT,
    role_id INT NOT NULL,
    user_id INT NOT NULL,
    UNIQUE KEY unique_role_user (role_id, user_id),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Modules
CREATE TABLE IF NOT EXISTS modules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(150) NOT NULL,
    module_key VARCHAR(150) NOT NULL UNIQUE,
    description TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Features within modules
CREATE TABLE IF NOT EXISTS module_features (
    id INT PRIMARY KEY AUTO_INCREMENT,
    module_id INT NOT NULL,
    feature_key VARCHAR(150) NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Role permissions per module/feature (CRUD flags)
CREATE TABLE IF NOT EXISTS role_permissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    role_id INT NOT NULL,
    module_key VARCHAR(150) NOT NULL,
    feature_key VARCHAR(150) NOT NULL,
    can_create TINYINT(1) DEFAULT 0,
    can_read TINYINT(1) DEFAULT 0,
    can_update TINYINT(1) DEFAULT 0,
    can_delete TINYINT(1) DEFAULT 0,
    UNIQUE KEY unique_role_module_feature (role_id, module_key, feature_key),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed roles (id will be auto-assigned, slugs are stable identifiers)
INSERT IGNORE INTO roles (name, slug, description) VALUES
('Super Admin','superadmin','Full system access'),
('Sys Admin','sysadmin','System administration'),
('Operation','operation','Operation users'),
('Compliance','compliance','Compliance team'),
('Manager','manager','Managers'),
('Monitor','monitor','Monitoring/Read-only'),
('Branch','branch','Branch user with limited access');

-- Seed example modules and features (used by the Configuration UI)
INSERT IGNORE INTO modules (name, module_key, description) VALUES
('Configuration','configuration','System configuration and RBAC'),
('Inventra','inventra','Inventory module'),
('CashOps','cashops','Cash operations'),
('KPI','kpi','Key performance indicators');

-- Inventra features
INSERT IGNORE INTO module_features (module_id, feature_key, name) 
SELECT m.id, v.feature_key, v.name FROM (SELECT 'allocations' AS feature_key, 'Allocations' AS name UNION ALL SELECT 'adjustments','Adjustments' UNION ALL SELECT 'disputes','Disputes' UNION ALL SELECT 'transfers','Transfers' UNION ALL SELECT 'settings','Settings') v
JOIN modules m ON m.module_key = 'inventra';

-- Minimal sample CashOps/KPI features
INSERT IGNORE INTO module_features (module_id, feature_key, name)
SELECT m.id, v.feature_key, v.name FROM (SELECT 'cashflows' AS feature_key, 'Cashflows' AS name) v JOIN modules m ON m.module_key = 'cashops';
INSERT IGNORE INTO module_features (module_id, feature_key, name)
SELECT m.id, v.feature_key, v.name FROM (SELECT 'reports' AS feature_key, 'Reports' AS name) v JOIN modules m ON m.module_key = 'kpi';

-- Configuration module features (roles, permissions, users)
INSERT IGNORE INTO module_features (module_id, feature_key, name)
SELECT m.id, v.feature_key, v.name FROM (
    SELECT 'roles' AS feature_key, 'Roles' AS name UNION ALL
    SELECT 'permissions','Permissions' UNION ALL
    SELECT 'users','Users'
) v JOIN modules m ON m.module_key = 'configuration';

-- After running the SQL above, you can assign permissions by inserting into role_permissions.

-- Audit log for role changes and other configuration actions
CREATE TABLE IF NOT EXISTS role_change_audit (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NULL, -- the affected user
    changed_by INT NULL, -- who made the change
    action VARCHAR(100) NOT NULL,
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

*/

// ============================================================
// ENVIRONMENT VARIABLES (.env or config.php)
// ============================================================

/*
// Database Connection
define('DB_HOST', 'localhost');
define('DB_NAME', 'inventra_database');
define('DB_USER', 'inventra_user');
define('DB_PASS', 'secure_password');

// Application URLs
define('BASE_URL', 'http://dev.board.tmlhub.com');
define('ADMIN_PATH', '/modules/Inventra/');

// Security
define('CSRF_TOKEN_LENGTH', 32);
define('SESSION_TIMEOUT', 3600);
define('SALT', 'your_unique_salt_string');

// Pagination
define('ITEMS_PER_PAGE', 25);
define('BATCHES_PER_PAGE', 20);

// Features
define('ENABLE_BULK_IMPORT', true);
define('ENABLE_EXPORT', true);
define('ENABLE_NOTIFICATIONS', true);
*/

// ============================================================
// API ENDPOINT STRUCTURE
// ============================================================

/*
SETTINGS DASHBOARD ENDPOINTS:

1. Display Dashboard
   GET /index.php?action=settings
   Response: HTML with form and tables

2. Add Item
   POST /index.php?action=settings_ajax
   Body: {
     ajax_action: 'add_item',
     item_name: string,
     item_code: string,
     item_category: string,
     unit_value: number,
     item_image_url: string,
     item_description: string,
     _wpnonce: csrf_token
   }
   Response: {success: bool, message: string}

3. Edit Item
   POST /index.php?action=settings_ajax
   Body: {
     ajax_action: 'edit_item',
     item_id: int,
     item_name: string,
     ...
   }

4. Delete Item
   POST /index.php?action=settings_ajax
   Body: {
     ajax_action: 'delete_item',
     item_id: int,
     _wpnonce: csrf_token
   }

5. Add Category
   POST /index.php?action=settings_ajax
   Body: {
     ajax_action: 'add_category',
     category_name: string,
     category_key: string,
     category_icon: string,
     _wpnonce: csrf_token
   }

6. Edit Category
   POST /index.php?action=settings_ajax
   Body: {
     ajax_action: 'edit_category',
     category_key: string,
     category_name: string,
     category_icon: string,
     _wpnonce: csrf_token
   }

7. Delete Category
   POST /index.php?action=settings_ajax
   Body: {
     ajax_action: 'delete_category',
     category_key: string,
     _wpnonce: csrf_token
   }

8. Add Batch
   POST /index.php?action=settings_ajax
   Body: {
     ajax_action: 'add_batch',
     batch_name: string,
     batch_mm_yyyy: string,
     batch_status: enum,
     batch_supplier: string,
     batch_notes: string,
     _wpnonce: csrf_token
   }

9. Edit Batch
   POST /index.php?action=settings_ajax
   Body: {
     ajax_action: 'edit_batch',
     batch_id: int,
     batch_name: string,
     ...
   }

10. Delete Batch
    POST /index.php?action=settings_ajax
    Body: {
      ajax_action: 'delete_batch',
      batch_id: int,
      _wpnonce: csrf_token
    }

ADMIN DASHBOARD ENDPOINTS:

1. Approve Allocation
   POST /index.php?action=admin_ajax
   Body: {
     action: 'approve_allocation',
     allocation_id: int,
     _wpnonce: csrf_token
   }

2. Decline Allocation
   POST /index.php?action=admin_ajax
   Body: {
     action: 'decline_allocation',
     allocation_id: int,
     reason: string,
     _wpnonce: csrf_token
   }

[Similar pattern for approve/decline: adjustment, dispute, transfer]
*/

// ============================================================
// TESTING QUERIES
// ============================================================

/*
-- Test data for development

-- Insert test items
INSERT INTO inventra_inventory_items (name, code, category, unit_value, description)
VALUES 
  ('Emergency Kit', 'EMG-001', 'tools', 45.99, 'Complete emergency response kit'),
  ('First Aid Box', 'FAD-001', 'office', 32.50, 'Standard first aid supplies'),
  ('Protective Gear', 'PRG-001', 'tools', 125.00, 'Safety equipment set');

-- Insert test batches
INSERT INTO inventra_batches (name, batch_mm_yyyy, status, supplier, notes)
VALUES 
  ('Q1 2026 Supplies', '01/2026', 'planning', 'Global Suppliers Inc', 'Regular quarterly order'),
  ('Emergency Stock', '01/2026', 'distributing', 'Emergency Supplies Co', 'High priority');

-- Check all items
SELECT * FROM inventra_inventory_items;

-- Check all batches
SELECT * FROM inventra_batches;

-- Verify structure
DESCRIBE inventra_inventory_items;
DESCRIBE inventra_batches;
*/

?>
