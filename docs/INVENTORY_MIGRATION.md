# Inventory Migration: Sync branch inventory

This migration helps migrate old `inventra_inventory` rows into the modern `inventra_branch_inventory` table and provides an overview of pending allocations (useful for calculating "allocated" counts).

File: `db/migrations/20260113_sync_branch_inventory.sql`

How to run:

  mysql -u <user> -p <database> < db/migrations/20260113_sync_branch_inventory.sql

What it does:
- Creates `inventra_branch_inventory` if it does not exist.
- Populates `current_stock` and `total_received` by aggregating `inventra_inventory` grouped by `branch_id`, `item_id`, `batch_id`.
- Creates a temporary allocations summary (`tmp_allocations_summary`) for convenience (in-memory), which you can query after migration to see pending allocations per branch/item.

Notes & options:
- The migration does not store `total_allocated` as a persistent column; allocated counts are computed dynamically from `inventra_allocations`.
- If you prefer to store `total_allocated` in the table, I can add an `quantity_allocated` column and a follow-up migration to populate and maintain it on updates.
- Always back up production data before running migrations.
