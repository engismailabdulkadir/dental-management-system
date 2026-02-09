<?php

class PermissionsController
{
    private function adminOnly()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user']) || $_SESSION['user']['role_id'] != 1) {
            die("Access denied (Admin only)");
        }
    }

    public function index()
    {
        $this->adminOnly();

        $pdo = require __DIR__ . '/../../config/database.php';
        $roles = $pdo->query("SELECT id, name FROM roles ORDER BY id")->fetchAll();
        $perms = $pdo->query("SELECT id, name FROM permissions ORDER BY id")->fetchAll();

        $map = [];
        $rows = $pdo->query("SELECT role_id, permission_id FROM role_permissions")->fetchAll();
        foreach ($rows as $r) {
            $map[$r['role_id']][$r['permission_id']] = true;
        }

        require __DIR__ . '/../../views/permissions/index.php';
    }

    public function update()
    {
        $this->adminOnly();

        $roleId = (int)($_POST['role_id'] ?? 0);
        if (!$roleId) {
            $_SESSION['flash_error'] = 'Role is required.';
            header("Location: /dental-management-system/public/permissions");
            exit;
        }

        $pdo = require __DIR__ . '/../../config/database.php';
        $pdo->beginTransaction();

        try {
            $pdo->prepare("DELETE FROM role_permissions WHERE role_id = ?")->execute([$roleId]);

            if (!empty($_POST['permissions']) && is_array($_POST['permissions'])) {
                $stmt = $pdo->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
                foreach ($_POST['permissions'] as $permId) {
                    $stmt->execute([$roleId, (int)$permId]);
                }
            }

            $pdo->commit();
            $_SESSION['flash_success'] = 'Permissions updated successfully.';
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['flash_error'] = 'Update failed: ' . $e->getMessage();
        }

        header("Location: /dental-management-system/public/permissions");
        exit;
    }
}
