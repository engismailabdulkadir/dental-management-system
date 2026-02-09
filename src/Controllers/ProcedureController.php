<?php

require_once __DIR__ . '/../Helpers/Permissions.php';

class ProcedureController
{
    // ======================
    // ADMIN CHECK
    // ======================
    private function checkAdmin()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user']) || $_SESSION['user']['role_id'] != 1) {
            die("Access denied (Admin only)");
        }
    }

    // ======================
    // LIST PROCEDURES
    // ======================
    public function index()
    {
        $this->checkAdmin();
        Permissions::require('read');

        $pdo = require __DIR__ . '/../../config/database.php';
        $procedures = $pdo->query(
            "SELECT * FROM procedures ORDER BY id DESC"
        )->fetchAll();

        require __DIR__ . '/../../views/procedures/index.php';
    }

    // ======================
    // SHOW CREATE FORM
    // ======================
    public function create()
    {
        $this->checkAdmin();
        Permissions::require('create');
        require __DIR__ . '/../../views/procedures/create.php';
    }

    // ======================
    // STORE PROCEDURE
    // ======================
    public function store()
    {
        $this->checkAdmin();
        Permissions::require('create');

        if (empty($_POST['name']) || $_POST['price'] === '') {
            die("Name and price are required");
        }
        if ((float)$_POST['price'] < 0) {
            die("Price cannot be negative");
        }

        $pdo = require __DIR__ . '/../../config/database.php';

        $stmt = $pdo->prepare(
            "INSERT INTO procedures (name, price) VALUES (?, ?)"
        );
        $stmt->execute([
            $_POST['name'],
            $_POST['price']
        ]);

        $_SESSION['flash_success'] = 'Procedure created successfully.';
        header("Location: /dental-management-system/public/procedures");
        exit;
    }

    // ======================
    // SHOW EDIT FORM
    // ======================
    public function edit()
    {
        $this->checkAdmin();
        Permissions::require('update');

        $id = $_GET['id'] ?? null;
        if (!$id) {
            header("Location: /dental-management-system/public/procedures");
            exit;
        }

        $pdo = require __DIR__ . '/../../config/database.php';

        $stmt = $pdo->prepare(
            "SELECT * FROM procedures WHERE id = ?"
        );
        $stmt->execute([$id]);
        $procedure = $stmt->fetch();

        require __DIR__ . '/../../views/procedures/edit.php';
    }

    // ======================
    // UPDATE PROCEDURE
    // ======================
    public function update()
    {
        $this->checkAdmin();
        Permissions::require('update');

        if ((float)$_POST['price'] < 0) {
            die("Price cannot be negative");
        }

        $pdo = require __DIR__ . '/../../config/database.php';

        $stmt = $pdo->prepare(
            "UPDATE procedures SET name = ?, price = ? WHERE id = ?"
        );
        $stmt->execute([
            $_POST['name'],
            $_POST['price'],
            $_POST['id']
        ]);

        $_SESSION['flash_success'] = 'Procedure updated successfully.';
        header("Location: /dental-management-system/public/procedures");
        exit;
    }

    // ======================
    // DELETE PROCEDURE
    // ======================
    public function delete()
    {
        $this->checkAdmin();
        Permissions::require('delete');

        $pdo = require __DIR__ . '/../../config/database.php';
        $pdo->prepare(
            "DELETE FROM procedures WHERE id = ?"
        )->execute([$_GET['id']]);

        $_SESSION['flash_success'] = 'Procedure deleted successfully.';
        header("Location: /dental-management-system/public/procedures");
        exit;
    }
}
