<?php

class Permissions
{
    private static function ensureSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function has($permissionName)
    {
        self::ensureSession();

        if (!isset($_SESSION['user'])) {
            return false;
        }

        $pdo = require __DIR__ . '/../../config/database.php';

        $stmt = $pdo->prepare("SELECT id FROM permissions WHERE name = ?");
        $stmt->execute([$permissionName]);
        $permissionId = $stmt->fetchColumn();
        if (!$permissionId) {
            return false;
        }

        $userId = $_SESSION['user']['id'];
        $roleId = $_SESSION['user']['role_id'];

        // User-specific override
        $stmt = $pdo->prepare("
            SELECT allow
            FROM user_permissions
            WHERE user_id = ? AND permission_id = ?
        ");
        $stmt->execute([$userId, $permissionId]);
        $override = $stmt->fetchColumn();
        if ($override !== false) {
            return (int)$override === 1;
        }

        // Role-based permission
        $stmt = $pdo->prepare("
            SELECT 1
            FROM role_permissions
            WHERE role_id = ? AND permission_id = ?
        ");
        $stmt->execute([$roleId, $permissionId]);
        return (bool)$stmt->fetchColumn();
    }

    public static function require($permissionName)
    {
        if (!self::has($permissionName)) {
            self::ensureSession();
            $_SESSION['flash_error'] = 'Access denied. Permission required: ' . $permissionName;
            header("Location: /dental-management-system/public/dashboard");
            exit;
        }
    }
}
