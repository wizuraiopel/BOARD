<?php
// scripts/smoke/admin_flows.php
// Quick smoke tests for admin allocation create -> approve -> decline flows

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../modules/Inventra/models/AdminDashboardModel.php';
require_once __DIR__ . '/../../modules/Inventra/models/User.php';

$db = Database::getInstance();
$model = new AdminDashboardModel();
$userModel = new User();

function ensureUser($userModel, $username, $user_type = 'admin') {
    $u = $userModel->findByUsername($username);
    if ($u) return $u['id'];
    $email = $username . '@example.local';
    $pass = password_hash('smoketest', PASSWORD_DEFAULT);
    $stmt = $userModel->pdo->prepare("INSERT INTO users (username, email, password_hash, user_type, created_at) VALUES (?, ?, ?, ?, ?)" );
    $now = date('Y-m-d H:i:s');
    $ok = $stmt->execute([$username, $email, $pass, $user_type, $now]);
    if (!$ok) throw new Exception('Failed to create user: ' . implode(' ', $stmt->errorInfo()));
    return $db->lastInsertId();
}

try {
    echo "Setting up test users/items...\n";
    // Ensure admin and branch test users
    $adminId = ensureUser($userModel, 'smoke_admin_user', 'admin');
    $branchId = ensureUser($userModel, 'smoke_branch_user', 'branch');

    // Insert or find an item
    $itemStmt = $db->prepare("SELECT id FROM inventra_inventory_items WHERE name = ? LIMIT 1");
    $itemStmt->execute(['Smoke Test Item']);
    $item = $itemStmt->fetch();
    if (!$item) {
        $db->prepare("INSERT INTO inventra_inventory_items (name, image_url, created_at) VALUES (?,?,?)")->execute(['Smoke Test Item', '', date('Y-m-d H:i:s')]);
        $itemId = $db->lastInsertId();
    } else {
        $itemId = $item['id'];
    }

    // Insert or find a batch
    $batchStmt = $db->prepare("SELECT id FROM inventra_batches WHERE name = ? LIMIT 1");
    $batchStmt->execute(['Smoke Test Batch']);
    $batch = $batchStmt->fetch();
    if (!$batch) {
        $db->prepare("INSERT INTO inventra_batches (name, created_at) VALUES (?,?)")->execute(['Smoke Test Batch', date('Y-m-d H:i:s')]);
        $batchId = $db->lastInsertId();
    } else {
        $batchId = $batch['id'];
    }

    echo "Creating allocations to branch...\n";
    $res = $model->createAllocations($itemId, $batchId, [$branchId], 5, $adminId);
    if (empty($res['success'])) throw new Exception('createAllocations failed: ' . json_encode($res));
    echo "createAllocations result: " . json_encode($res) . "\n";

    // Find the newly created distribution(s)
    $distStmt = $db->prepare("SELECT * FROM inventra_distributions WHERE created_by = ? AND item_id = ? AND batch_id = ? ORDER BY id DESC LIMIT 5");
    $distStmt->execute([$adminId, $itemId, $batchId]);
    $dists = $distStmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($dists)) throw new Exception('No distributions found after createAllocations');

    $first = $dists[0];
    $second = isset($dists[1]) ? $dists[1] : null;

    echo "Approving allocation id {$first['id']}...\n";
    $appr = $model->approveAllocation($first['id'], $adminId);
    echo json_encode($appr) . "\n";
    // Verify in DB
    $chk = $db->prepare("SELECT status, resolved_by FROM inventra_distributions WHERE id = ?"); $chk->execute([$first['id']]); $row = $chk->fetch(PDO::FETCH_ASSOC);
    if ($row['status'] !== 'approved') throw new Exception('Allocation not approved in DB');
    if ($row['resolved_by'] != $adminId) throw new Exception('Resolved_by not set correctly');

    if ($second) {
        echo "Declining allocation id {$second['id']}...\n";
        $decl = $model->declineAllocation($second['id'], $adminId, 'Smoke test decline');
        echo json_encode($decl) . "\n";
        $chk = $db->prepare("SELECT status, resolved_reason FROM inventra_distributions WHERE id = ?"); $chk->execute([$second['id']]); $row2 = $chk->fetch(PDO::FETCH_ASSOC);
        if ($row2['status'] !== 'declined') throw new Exception('Allocation not declined in DB');
        if (stripos($row2['resolved_reason'], 'Smoke test') === false) throw new Exception('Resolved reason missing');
    }

    // Cleanup: remove the test distributions we created in this run
    $idsToRemove = array_map(function($x){ return intval($x['id']); }, $dists);
    if (!empty($idsToRemove)) {
        $in = implode(',', $idsToRemove);
        $db->exec("DELETE FROM inventra_distributions WHERE id IN ({$in})");
        echo "Cleaned up test distributions: " . implode(',', $idsToRemove) . "\n";
    }

    echo "SMOKE TESTS PASSED\n";
    exit(0);
} catch (Exception $e) {
    echo "SMOKE TEST FAILED: " . $e->getMessage() . "\n";
    exit(2);
}
