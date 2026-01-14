<?php
// models/SettingsDashboardModel.php
/**
 * Settings Dashboard Model - Manages inventory items, categories, and batches
 */

require_once __DIR__ . '/../../../config/database.php';

class SettingsDashboardModel {
    private $pdo;
    private $itemsTable = 'inventra_inventory_items';
    private $batchesTable = 'inventra_batches';
    private $categoriesOption = 'inventra_categories';

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    // ==================== ITEMS ====================

    /**
     * Get all inventory items
     */
    public function getAllItems() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, name 
                FROM {$this->itemsTable} 
                ORDER BY name ASC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get item by ID
     */
    public function getItemById($id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, name 
                FROM {$this->itemsTable} 
                WHERE id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Add new item
     */
    public function addItem($data) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO {$this->itemsTable} (name) 
                VALUES (?)
            ");
            return $stmt->execute([
                $data['name']
            ]);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Update item
     */
    public function updateItem($id, $data) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE {$this->itemsTable} 
                SET name = ? 
                WHERE id = ?
            ");
            return $stmt->execute([
                $data['name'],
                $id
            ]);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Delete item
     */
    public function deleteItem($id) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM {$this->itemsTable} WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            return false;
        }
    }

    // ==================== CATEGORIES ====================

    /**
     * Get all categories
     */
    public function getAllCategories() {
        // Categories are stored as PHP options/settings
        // For now, return a default set - you can customize based on your storage method
        return [
            'tools' => ['name' => 'Tools & Equipment', 'icon' => '🔧'],
            'office' => ['name' => 'Office Supplies', 'icon' => '📎'],
            'other' => ['name' => 'Other', 'icon' => '📦']
        ];
    }

    /**
     * Get category by key
     */
    public function getCategoryByKey($key) {
        $categories = $this->getAllCategories();
        return $categories[$key] ?? null;
    }

    /**
     * Add new category
     */
    public function addCategory($data) {
        try {
            $categories = $this->getAllCategories();
            $categories[$data['key']] = [
                'name' => $data['name'],
                'icon' => $data['icon'] ?? '📦'
            ];
            // In a real application, you'd persist this to database
            // For now, returning true to indicate success
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Update category
     */
    public function updateCategory($key, $data) {
        try {
            $categories = $this->getAllCategories();
            if (isset($categories[$key])) {
                $categories[$key] = [
                    'name' => $data['name'],
                    'icon' => $data['icon'] ?? $categories[$key]['icon']
                ];
                return true;
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Delete category
     */
    public function deleteCategory($key) {
        try {
            $categories = $this->getAllCategories();
            if (isset($categories[$key])) {
                unset($categories[$key]);
                return true;
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    // ==================== BATCHES ====================

    /**
     * Get all batches
     */
    public function getAllBatches() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, name, batch_mm_yyyy, status, supplier, notes 
                FROM {$this->batchesTable} 
                ORDER BY id DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get batch by ID
     */
    public function getBatchById($id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, name, batch_mm_yyyy, status, supplier, notes 
                FROM {$this->batchesTable} 
                WHERE id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get batches by status
     */
    public function getBatchesByStatus($status) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, name, batch_mm_yyyy, status, supplier, notes 
                FROM {$this->batchesTable} 
                WHERE status = ? 
                ORDER BY id DESC
            ");
            $stmt->execute([$status]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Add new batch
     */
    public function addBatch($data) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO {$this->batchesTable} (name, batch_mm_yyyy, status, supplier, notes) 
                VALUES (?, ?, ?, ?, ?)
            ");
            return $stmt->execute([
                $data['name'],
                $data['batch_mm_yyyy'] ?? null,
                $data['status'] ?? 'planning',
                $data['supplier'] ?? null,
                $data['notes'] ?? null
            ]);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Update batch
     */
    public function updateBatch($id, $data) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE {$this->batchesTable} 
                SET name = ?, batch_mm_yyyy = ?, status = ?, supplier = ?, notes = ? 
                WHERE id = ?
            ");
            return $stmt->execute([
                $data['name'],
                $data['batch_mm_yyyy'] ?? null,
                $data['status'] ?? 'planning',
                $data['supplier'] ?? null,
                $data['notes'] ?? null,
                $id
            ]);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Delete batch
     */
    public function deleteBatch($id) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM {$this->batchesTable} WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Count items in batch
     */
    public function countItemsInBatch($batchId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as count 
                FROM inventra_batch_items 
                WHERE batch_id = ?
            ");
            $stmt->execute([$batchId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    // ==================== UTILITY METHODS ====================

    /**
     * Get item count (total items in inventory)
     */
    public function getItemCountByCategory($category) {
        // Since category column doesn't exist in database, return 0
        // This is a legacy method maintained for backward compatibility
        return 0;
    }

    /**
     * Get total item count
     */
    public function getTotalItemCount() {
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM {$this->itemsTable}");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Get total batch count
     */
    public function getTotalBatchCount() {
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM {$this->batchesTable}");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Search items
     */
    public function searchItems($query) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, name 
                FROM {$this->itemsTable} 
                WHERE name LIKE ? 
                ORDER BY name ASC
            ");
            $searchTerm = '%' . $query . '%';
            $stmt->execute([$searchTerm]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Search batches
     */
    public function searchBatches($query) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, name, batch_mm_yyyy, status, supplier, notes 
                FROM {$this->batchesTable} 
                WHERE name LIKE ? OR supplier LIKE ? 
                ORDER BY id DESC
            ");
            $searchTerm = '%' . $query . '%';
            $stmt->execute([$searchTerm, $searchTerm]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
}
