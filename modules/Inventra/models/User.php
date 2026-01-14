<?php
// models/User.php
require_once __DIR__ . '/../../../config/database.php'; // Fixed path using __DIR__

class User {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function findByUsername($username) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($username, $email, $password_hash) {
        $stmt = $this->pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
        $ok = $stmt->execute([$username, $email, $password_hash]);
        if ($ok) {
            return $this->pdo->lastInsertId();
        }
        return false;
    }

    /**
     * Return roles (as slug/name) assigned to a user
     */
    public function getRoles($user_id) {
        $stmt = $this->pdo->prepare("SELECT r.* FROM roles r JOIN role_user ru ON ru.role_id = r.id WHERE ru.user_id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }

    /**
     * Return role slugs for a user
     */
    public function getRoleSlugs($user_id) {
        $stmt = $this->pdo->prepare("SELECT r.slug FROM roles r JOIN role_user ru ON ru.role_id = r.id WHERE ru.user_id = ?");
        $stmt->execute([$user_id]);
        return array_column($stmt->fetchAll(), 'slug');
    }

    /**
     * List all users with basic role summary
     */
    public function listAllWithRoles($page = 1, $perPage = 20, $q = '') {
        $offset = max(0, ($page - 1) * $perPage);
        $qParam = '%' . trim($q) . '%';

        $stmt = $this->pdo->prepare("SELECT SQL_CALC_FOUND_ROWS u.id, u.username, u.email, u.user_type, GROUP_CONCAT(r.slug SEPARATOR ',') AS role_slugs
            FROM users u
            LEFT JOIN role_user ru ON ru.user_id = u.id
            LEFT JOIN roles r ON r.id = ru.role_id
            WHERE ? = '' OR (u.username LIKE ? OR u.email LIKE ?)
            GROUP BY u.id
            ORDER BY u.id ASC
            LIMIT ? OFFSET ?");
        $stmt->execute([$q, $qParam, $qParam, intval($perPage), intval($offset)]);
        $data = $stmt->fetchAll();

        $totalStmt = $this->pdo->query("SELECT FOUND_ROWS() as total");
        $total = (int)($totalStmt->fetch()['total'] ?? count($data));

        return ['data' => $data, 'total' => $total, 'page' => $page, 'per_page' => $perPage];
    }

    public function updatePassword($id, $newHash) {
        $stmt = $this->pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        return $stmt->execute([$newHash, $id]);
    }
}