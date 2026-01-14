-- Sample Seed Data for Dev B.O.A.R.D (updated)
-- Run this on a development database. Back up your data before running in production.

-- NOTE: This script assumes schema from CONFIGURATION.php has been applied (tables: users, roles, role_user, modules, module_features, role_permissions, inventra_* tables)

-- 1) Roles
INSERT IGNORE INTO roles (name, slug, description) VALUES
('Super Admin','superadmin','Full system access'),
('Sys Admin','sysadmin','System administration'),
('Operation','operation','Operation users'),
('Compliance','compliance','Compliance team'),
('Manager','manager','Managers'),
('Monitor','monitor','Monitoring/Read-only'),
('Branch','branch','Branch user with limited access');

-- 2) Modules & Features (ensure modules exist)
INSERT IGNORE INTO modules (name, module_key, description) VALUES
('Configuration','configuration','System configuration and RBAC'),
('Inventra','inventra','Inventory module'),
('CashOps','cashops','Cash operations'),
('KPI','kpi','Key performance indicators');

-- Inventra features
INSERT IGNORE INTO module_features (module_id, feature_key, name)
SELECT m.id, v.feature_key, v.name FROM (
    SELECT 'allocations' AS feature_key, 'Allocations' AS name UNION ALL
    SELECT 'adjustments','Adjustments' UNION ALL
    SELECT 'disputes','Disputes' UNION ALL
    SELECT 'transfers','Transfers' UNION ALL
    SELECT 'settings','Settings'
) v JOIN modules m ON m.module_key = 'inventra';

-- CashOps/KPI features
INSERT IGNORE INTO module_features (module_id, feature_key, name)
SELECT m.id, v.feature_key, v.name FROM (SELECT 'cashflows' AS feature_key, 'Cashflows' AS name) v JOIN modules m ON m.module_key = 'cashops';
INSERT IGNORE INTO module_features (module_id, feature_key, name)
SELECT m.id, v.feature_key, v.name FROM (SELECT 'reports' AS feature_key, 'Reports' AS name) v JOIN modules m ON m.module_key = 'kpi';

-- Configuration features (roles, permissions, users)
INSERT IGNORE INTO module_features (module_id, feature_key, name)
SELECT m.id, v.feature_key, v.name FROM (
    SELECT 'roles' AS feature_key, 'Roles' AS name UNION ALL
    SELECT 'permissions','Permissions' UNION ALL
    SELECT 'users','Users'
) v JOIN modules m ON m.module_key = 'configuration';

-- 3) Sample Users
-- Passwords use a sample bcrypt hash for 'password' (dev only)
-- Hash used (can be replaced): $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi

INSERT IGNORE INTO users (username, email, password_hash, user_type) VALUES
('superadmin','superadmin@example.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','admin'),
('sysadmin','sysadmin@example.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','admin'),
('manager1','manager1@example.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','staff'),
('op1','op1@example.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','staff'),
('compliance1','compliance1@example.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','staff'),
('monitor1','monitor1@example.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','staff'),
('branch1','branch1@example.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','branch');

-- 4) Assign Roles to Users
INSERT IGNORE INTO role_user (role_id, user_id)
VALUES
((SELECT id FROM roles WHERE slug='superadmin'), (SELECT id FROM users WHERE username='superadmin')),
((SELECT id FROM roles WHERE slug='sysadmin'), (SELECT id FROM users WHERE username='sysadmin')),
((SELECT id FROM roles WHERE slug='manager'), (SELECT id FROM users WHERE username='manager1')),
((SELECT id FROM roles WHERE slug='operation'), (SELECT id FROM users WHERE username='op1')),
((SELECT id FROM roles WHERE slug='compliance'), (SELECT id FROM users WHERE username='compliance1')),
((SELECT id FROM roles WHERE slug='monitor'), (SELECT id FROM users WHERE username='monitor1')),
((SELECT id FROM roles WHERE slug='branch'), (SELECT id FROM users WHERE username='branch1'));

-- 5) Role Permissions (examples)
-- Superadmin: full access across modules
INSERT IGNORE INTO role_permissions (role_id, module_key, feature_key, can_create, can_read, can_update, can_delete)
SELECT r.id, m.module_key, f.feature_key, 1,1,1,1
FROM roles r JOIN modules m JOIN module_features f ON f.module_id = m.id
WHERE r.slug = 'superadmin';

-- Sysadmin: nearly full, but no delete on KPI (example)
INSERT IGNORE INTO role_permissions (role_id, module_key, feature_key, can_create, can_read, can_update, can_delete)
SELECT r.id, m.module_key, f.feature_key, 1,1,1, CASE WHEN m.module_key='kpi' THEN 0 ELSE 1 END
FROM roles r JOIN modules m JOIN module_features f ON f.module_id = m.id
WHERE r.slug = 'sysadmin';

-- Manager: allow configuration:permissions (read/update) and configuration:users create/read
INSERT IGNORE INTO role_permissions (role_id, module_key, feature_key, can_create, can_read, can_update, can_delete)
VALUES
((SELECT id FROM roles WHERE slug='manager'), 'configuration', 'permissions', 0,1,1,0),
((SELECT id FROM roles WHERE slug='manager'), 'configuration', 'users', 1,1,1,0);

-- Operation: allow Inventra CRUD on allocations and transfers
INSERT IGNORE INTO role_permissions (role_id, module_key, feature_key, can_create, can_read, can_update, can_delete)
VALUES
((SELECT id FROM roles WHERE slug='operation'), 'inventra', 'allocations', 1,1,1,0),
((SELECT id FROM roles WHERE slug='operation'), 'inventra', 'transfers', 1,1,1,0);

-- Monitor: read-only across Inventra
INSERT IGNORE INTO role_permissions (role_id, module_key, feature_key, can_create, can_read, can_update, can_delete)
SELECT (SELECT id FROM roles WHERE slug='monitor'), m.module_key, f.feature_key, 0,1,0,0
FROM modules m JOIN module_features f ON f.module_id = m.id
WHERE m.module_key = 'inventra';

-- 6) Inventory demo data
INSERT IGNORE INTO inventra_inventory_items (name, code, category, unit_value, image_url, description)
VALUES
('Paracetamol 500mg', 'PARA500', 'Medicines', 0.12, NULL, 'Pain reliever/fever reducer'),
('Saline 500ml', 'SAL500', 'Supplies', 0.45, NULL, 'IV fluid'),
('Bandage Roll', 'BAND01', 'Supplies', 0.05, NULL, 'Generic bandage');

INSERT IGNORE INTO inventra_batches (name, batch_mm_yyyy, status, supplier, notes)
VALUES
('Batch A', '01/2026', 'distributing', 'Supplier A', 'Initial distribution'),
('Batch B', '12/2025', 'completed', 'Supplier B', 'Received prior month');

INSERT IGNORE INTO inventra_allocations (branch_id, item_id, batch_id, quantity, status)
VALUES
(1, (SELECT id FROM inventra_inventory_items WHERE code='PARA500'), (SELECT id FROM inventra_batches WHERE name='Batch A'), 1000, 'pending'),
(2, (SELECT id FROM inventra_inventory_items WHERE code='SAL500'), (SELECT id FROM inventra_batches WHERE name='Batch A'), 200, 'approved');

INSERT IGNORE INTO inventra_adjustments (branch_id, item_id, adjustment_type, quantity, reason, status)
VALUES
(1, (SELECT id FROM inventra_inventory_items WHERE code='BAND01'), 'update', 50, 'Stock correction', 'approved');

INSERT IGNORE INTO inventra_disputes (allocation_id, branch_id, reason, status)
VALUES
((SELECT id FROM inventra_allocations WHERE status='pending' LIMIT 1), 1, 'Quantity mismatch reported', 'pending');

INSERT IGNORE INTO inventra_branch_transfers (from_branch_id, to_branch_id, item_id, batch_id, quantity, status)
VALUES
(1, 2, (SELECT id FROM inventra_inventory_items WHERE code='PARA500'), (SELECT id FROM inventra_batches WHERE name='Batch A'), 100, 'pending');

INSERT IGNORE INTO inventra_inventory (branch_id, item_id, batch_id, quantity)
VALUES
(1, (SELECT id FROM inventra_inventory_items WHERE code='PARA500'), (SELECT id FROM inventra_batches WHERE name='Batch A'), 500),
(2, (SELECT id FROM inventra_inventory_items WHERE code='SAL500'), (SELECT id FROM inventra_batches WHERE name='Batch A'), 100);

-- Done
SELECT 'Sample seed completed' as info;
