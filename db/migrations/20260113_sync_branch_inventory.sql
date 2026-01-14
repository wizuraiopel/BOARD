-- Migration: Sync inventra_branch_inventory from inventra_inventory and compute totals
-- Run on development DB (backup first):
-- mysql -u <user> -p <database> < db/migrations/20260113_sync_branch_inventory.sql

-- Create table if missing
CREATE TABLE IF NOT EXISTS inventra_branch_inventory (
    id INT PRIMARY KEY AUTO_INCREMENT,
    branch_id INT NOT NULL,
    item_id INT NOT NULL,
    batch_id INT DEFAULT NULL,
    current_stock INT NOT NULL DEFAULT 0,
    total_received INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_branch_item_batch (branch_id, item_id, batch_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Populate/update rows from inventra_inventory (grouped by branch/item/batch)
INSERT INTO inventra_branch_inventory (branch_id, item_id, batch_id, current_stock, total_received)
SELECT branch_id, item_id, batch_id, SUM(quantity) as current_stock, SUM(quantity) as total_received
FROM inventra_inventory
GROUP BY branch_id, item_id, batch_id
ON DUPLICATE KEY UPDATE
    current_stock = VALUES(current_stock),
    total_received = VALUES(total_received),
    updated_at = NOW();

-- Optionally, compute allocations summary for quick lookups (not stored by default)
-- This example creates a temporary table showing pending allocations per branch/item
DROP TEMPORARY TABLE IF EXISTS tmp_allocations_summary;
CREATE TEMPORARY TABLE tmp_allocations_summary ENGINE=MEMORY
SELECT branch_id, item_id, SUM(quantity) AS total_allocated
FROM inventra_allocations
WHERE status = 'pending'
GROUP BY branch_id, item_id;

SELECT 'Migration completed' AS info;
