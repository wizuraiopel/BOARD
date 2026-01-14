<?php
// models/Role.php
require_once __DIR__ . '/../../../config/database.php';

class Role {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function all() {
        $stmt = $this->pdo->prepare("SELECT * FROM roles ORDER BY id ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM roles WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($name, $slug, $description = '') {
        $stmt = $this->pdo->prepare("INSERT INTO roles (name, slug, description) VALUES (?, ?, ?)");
        return $stmt->execute([$name, $slug, $description]);
    }

    public function assignToUser($role_id, $user_id) {
        $stmt = $this->pdo->prepare("INSERT IGNORE INTO role_user (role_id, user_id) VALUES (?, ?)");
        return $stmt->execute([$role_id, $user_id]);
    }

    public function removeFromUser($role_id, $user_id) {
        $stmt = $this->pdo->prepare("DELETE FROM role_user WHERE role_id = ? AND user_id = ?");
        return $stmt->execute([$role_id, $user_id]);
    }

    /**
     * Set roles for a given user (replaces existing assignments)
     */
    public function setRolesForUser($user_id, $roleIds) {
        try {
            $this->pdo->beginTransaction();
            $del = $this->pdo->prepare("DELETE FROM role_user WHERE user_id = ?");
            $del->execute([$user_id]);
            $ins = $this->pdo->prepare("INSERT INTO role_user (role_id, user_id) VALUES (?, ?)");
            foreach ($roleIds as $rid) {
                $ins->execute([intval($rid), $user_id]);
            }
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }
}
