<?php
// models/Audit.php
require_once __DIR__ . '/../../../config/database.php';

class Audit {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function logChange($user_id, $changed_by, $action, $details = '') {
        $stmt = $this->pdo->prepare("INSERT INTO role_change_audit (user_id, changed_by, action, details) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$user_id, $changed_by, $action, $details]);
    }

    public function listRecent($limit = 50) {
        $stmt = $this->pdo->prepare("SELECT * FROM role_change_audit ORDER BY created_at DESC LIMIT ?");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}
