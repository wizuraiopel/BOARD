<?php
// models/Permission.php
require_once __DIR__ . '/../../../config/database.php';

class Permission {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    /**
     * Return modules and their features as an array:
     * [ ['module_key'=>'inventra', 'module_name'=>'Inventra', 'features'=>[ ['feature_key'=>'allocations','name'=>'Allocations'], ... ] ], ... ]
     */
    public function getModulesAndFeatures() {
        $stmt = $this->pdo->prepare("SELECT m.id AS module_id, m.module_key, m.name AS module_name, f.id AS feature_id, f.feature_key, f.name AS feature_name
            FROM modules m
            LEFT JOIN module_features f ON f.module_id = m.id
            ORDER BY m.id, f.id");
        $stmt->execute();

        $results = $stmt->fetchAll();
        $out = [];
        foreach ($results as $row) {
            $mk = $row['module_key'];
            if (!isset($out[$mk])) {
                $out[$mk] = ['module_key'=>$mk,'module_name'=>$row['module_name'],'features'=>[]];
            }
            if (!empty($row['feature_key'])) {
                $out[$mk]['features'][] = ['feature_key'=>$row['feature_key'],'feature_name'=>$row['feature_name']];
            }
        }
        return array_values($out);
    }

    public function getByRole($role_id) {
        $stmt = $this->pdo->prepare("SELECT module_key, feature_key, can_create, can_read, can_update, can_delete FROM role_permissions WHERE role_id = ?");
        $stmt->execute([$role_id]);
        $rows = $stmt->fetchAll();
        $out = [];
        foreach ($rows as $r) {
            $out[$r['module_key']][$r['feature_key']] = [
                'c' => (int)$r['can_create'],
                'r' => (int)$r['can_read'],
                'u' => (int)$r['can_update'],
                'd' => (int)$r['can_delete'],
            ];
        }
        return $out;
    }

    public function saveForRole($role_id, $permissions) {
        // $permissions expected structure: [module_key => [feature_key => ['c'=>0/1,'r'=>0/1,'u'=>0/1,'d'=>0/1], ...], ...]
        try {
            $this->pdo->beginTransaction();
            $del = $this->pdo->prepare("DELETE FROM role_permissions WHERE role_id = ?");
            $del->execute([$role_id]);

            $ins = $this->pdo->prepare("INSERT INTO role_permissions (role_id, module_key, feature_key, can_create, can_read, can_update, can_delete) VALUES (?, ?, ?, ?, ?, ?, ?)");

            foreach ($permissions as $module_key => $features) {
                foreach ($features as $feature_key => $flags) {
                    $c = intval($flags['c'] ?? 0);
                    $r = intval($flags['r'] ?? 0);
                    $u = intval($flags['u'] ?? 0);
                    $d = intval($flags['d'] ?? 0);
                    $ins->execute([$role_id, $module_key, $feature_key, $c, $r, $u, $d]);
                }
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    /**
     * Returns true if the given user has ANY permission (c/r/u/d) for any feature in module
     */
    public function userHasAnyPermission($user_id, $module_key) {
        $stmt = $this->pdo->prepare("SELECT 1 FROM role_permissions rp
            JOIN role_user ru ON ru.role_id = rp.role_id
            WHERE ru.user_id = ? AND rp.module_key = ? AND (rp.can_create=1 OR rp.can_read=1 OR rp.can_update=1 OR rp.can_delete=1) LIMIT 1");
        $stmt->execute([$user_id, $module_key]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Returns true if the given user has a specific flag for module/feature, where $flag is one of 'create','read','update','delete'
     */
    public function userHasPermission($user_id, $module_key, $feature_key, $flag) {
        $col = null;
        switch ($flag) {
            case 'create': $col = 'can_create'; break;
            case 'read': $col = 'can_read'; break;
            case 'update': $col = 'can_update'; break;
            case 'delete': $col = 'can_delete'; break;
            default: return false;
        }
        $sql = "SELECT 1 FROM role_permissions rp
            JOIN role_user ru ON ru.role_id = rp.role_id
            WHERE ru.user_id = ? AND rp.module_key = ? AND rp.feature_key = ? AND rp.$col = 1 LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$user_id, $module_key, $feature_key]);
        return (bool) $stmt->fetchColumn();
    }
}

