<?php

require_once __DIR__ . '/../Helpers/Permissions.php';

class UserController
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
        Permissions::require('read');

        $pdo = require __DIR__ . '/../../config/database.php';
        $users = $pdo->query("
            SELECT u.id, u.full_name, u.email, u.role_id, r.name AS role_name
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            ORDER BY u.id DESC
        ")->fetchAll();

        require __DIR__ . '/../../views/users/index.php';
    }

    public function create()
    {
        $this->adminOnly();
        Permissions::require('create');

        $pdo = require __DIR__ . '/../../config/database.php';
        $roles = $pdo->query("SELECT id, name FROM roles WHERE id IN (1,2,3,4) ORDER BY id")->fetchAll();
        $doctors = $pdo->query("SELECT id, full_name FROM doctors ORDER BY full_name")->fetchAll();

        require __DIR__ . '/../../views/users/create.php';
    }

    public function store()
    {
        $this->adminOnly();
        Permissions::require('create');

        if (empty($_POST['full_name']) || empty($_POST['email']) || empty($_POST['password']) || empty($_POST['role_id'])) {
            $_SESSION['flash_error'] = 'All fields are required.';
            header("Location: /dental-management-system/public/users/create");
            exit;
        }

        $roleId = (int)$_POST['role_id'];
        if (!in_array($roleId, [1, 2, 3, 4], true)) {
            $_SESSION['flash_error'] = 'Only Admin, Staff, Doctor, or Reception can be created here.';
            header("Location: /dental-management-system/public/users/create");
            exit;
        }

        $pdo = require __DIR__ . '/../../config/database.php';

        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$_POST['email']]);
        if ($check->fetch()) {
            $_SESSION['flash_error'] = 'Email already exists.';
            header("Location: /dental-management-system/public/users/create");
            exit;
        }

        $cols = $pdo->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN, 0);
        $doctorId = null;
        if ($roleId === 3) {
            if (!in_array('doctor_id', $cols, true)) {
                $_SESSION['flash_error'] = 'users.doctor_id column is missing. Add it in database.';
                header("Location: /dental-management-system/public/users/create");
                exit;
            }
            $doctorId = $_POST['doctor_id'] ?? null;
            if (!$doctorId) {
                $_SESSION['flash_error'] = 'Doctor is required for doctor role.';
                header("Location: /dental-management-system/public/users/create");
                exit;
            }
        }

        if (in_array('doctor_id', $cols, true)) {
            $stmt = $pdo->prepare("
                INSERT INTO users (full_name, email, password, role_id, doctor_id)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $_POST['full_name'],
                $_POST['email'],
                password_hash($_POST['password'], PASSWORD_DEFAULT),
                $roleId,
                $doctorId
            ]);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO users (full_name, email, password, role_id)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $_POST['full_name'],
                $_POST['email'],
                password_hash($_POST['password'], PASSWORD_DEFAULT),
                $roleId
            ]);
        }

        $_SESSION['flash_success'] = 'User created successfully.';
        header("Location: /dental-management-system/public/users");
        exit;
    }

    public function edit()
    {
        $this->adminOnly();
        Permissions::require('update');

        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['flash_error'] = 'User not found.';
            header("Location: /dental-management-system/public/users");
            exit;
        }

        $pdo = require __DIR__ . '/../../config/database.php';
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        if (!$user) {
            $_SESSION['flash_error'] = 'User not found.';
            header("Location: /dental-management-system/public/users");
            exit;
        }

        $roles = $pdo->query("SELECT id, name FROM roles WHERE id IN (1,2,3,4) ORDER BY id")->fetchAll();
        $doctors = $pdo->query("SELECT id, full_name FROM doctors ORDER BY full_name")->fetchAll();

        require __DIR__ . '/../../views/users/edit.php';
    }

    public function update()
    {
        $this->adminOnly();
        Permissions::require('update');

        if (empty($_POST['id']) || empty($_POST['full_name']) || empty($_POST['email']) || empty($_POST['role_id'])) {
            $_SESSION['flash_error'] = 'All fields are required.';
            header("Location: /dental-management-system/public/users");
            exit;
        }

        $roleId = (int)$_POST['role_id'];
        if (!in_array($roleId, [1, 2, 3, 4], true)) {
            $_SESSION['flash_error'] = 'Only Admin, Staff, Doctor, or Reception can be assigned.';
            header("Location: /dental-management-system/public/users/edit?id=" . urlencode($_POST['id']));
            exit;
        }

        $pdo = require __DIR__ . '/../../config/database.php';
        $fields = ["full_name = ?", "email = ?", "role_id = ?"];
        $params = [$_POST['full_name'], $_POST['email'], $roleId];

        $cols = $pdo->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN, 0);
        if ($roleId === 3 && in_array('doctor_id', $cols, true)) {
            $doctorId = $_POST['doctor_id'] ?? null;
            if (!$doctorId) {
                $_SESSION['flash_error'] = 'Doctor is required for doctor role.';
                header("Location: /dental-management-system/public/users/edit?id=" . urlencode($_POST['id']));
                exit;
            }
            $fields[] = "doctor_id = ?";
            $params[] = $doctorId;
        } elseif (in_array('doctor_id', $cols, true)) {
            $fields[] = "doctor_id = NULL";
        }

        if (!empty($_POST['password'])) {
            $fields[] = "password = ?";
            $params[] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }

        $params[] = $_POST['id'];
        $stmt = $pdo->prepare("UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?");
        $stmt->execute($params);

        $_SESSION['flash_success'] = 'User updated successfully.';
        header("Location: /dental-management-system/public/users");
        exit;
    }

    public function delete()
    {
        $this->adminOnly();
        Permissions::require('delete');

        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['flash_error'] = 'User ID missing.';
            header("Location: /dental-management-system/public/users");
            exit;
        }

        $pdo = require __DIR__ . '/../../config/database.php';
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);

        $_SESSION['flash_success'] = 'User deleted successfully.';
        header("Location: /dental-management-system/public/users");
        exit;
    }

    public function search()
    {
        $this->adminOnly();
        Permissions::require('read');

        $q = trim($_GET['q'] ?? '');
        if ($q === '') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([]);
            return;
        }

        $pdo = require __DIR__ . '/../../config/database.php';
        $isId = ctype_digit($q);
        $stmt = $pdo->prepare("
            SELECT u.id, u.full_name, u.email, r.name AS role_name
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            WHERE " . ($isId ? "u.id = ?" : "(u.full_name LIKE ? OR u.email LIKE ?)") . "
            ORDER BY u.id DESC
            LIMIT 10
        ");

        if ($isId) {
            $stmt->execute([$q]);
        } else {
            $like = '%' . $q . '%';
            $stmt->execute([$like, $like]);
        }

        $rows = $stmt->fetchAll();
        $results = [];
        foreach ($rows as $row) {
            $results[] = [
                'id' => (int)$row['id'],
                'full_name' => $row['full_name'],
                'email' => $row['email'],
                'role_name' => $row['role_name'] ?? ''
            ];
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($results);
    }
}
