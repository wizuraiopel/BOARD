<?php
// Temporary diagnostic file to check database tables and schemas

require_once 'config/database.php';

$pdo = Database::getInstance();

// Check for inventra_inventory_items table
echo "=== Checking inventra_inventory_items table ===\n";
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM inventra_inventory_items");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Columns in inventra_inventory_items:\n";
    foreach ($columns as $col) {
        echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Check for inventra_batches table
echo "\n=== Checking inventra_batches table ===\n";
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM inventra_batches");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Columns in inventra_batches:\n";
    foreach ($columns as $col) {
        echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Check for inventra_categories table (if exists)
echo "\n=== Checking inventra_categories table ===\n";
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM inventra_categories");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Columns in inventra_categories:\n";
    foreach ($columns as $col) {
        echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// List all tables in database
echo "\n=== All tables in database ===\n";
try {
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        echo "  - $table\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
