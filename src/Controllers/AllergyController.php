<?php

require_once __DIR__ . '/../Helpers/Permissions.php';

class AllergyController
{
    private function auth()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user'])) {
            header("Location: /dental-management-system/public/login");
            exit;
        }
    }

    public function index()
    {
        $this->auth();
        Permissions::require('read');

        $pdo = require __DIR__ . '/../../config/database.php';
        $allergies = $pdo->query("SELECT * FROM allergies ORDER BY id DESC")->fetchAll();

        require __DIR__ . '/../../views/allergies/index.php';
    }

    public function create()
    {
        $this->auth();
        Permissions::require('create');
        require __DIR__ . '/../../views/allergies/create.php';
    }

    public function store()
    {
        $this->auth();
        Permissions::require('create');

        if (empty($_POST['name'])) {
            $_SESSION['flash_error'] = 'Allergy name is required.';
            header("Location: /dental-management-system/public/allergies/create");
            exit;
        }

        $pdo = require __DIR__ . '/../../config/database.php';
        $stmt = $pdo->prepare("INSERT INTO allergies (name) VALUES (?)");
        $stmt->execute([$_POST['name']]);

        $_SESSION['flash_success'] = 'Allergy created successfully.';
        header("Location: /dental-management-system/public/allergies");
        exit;
    }

    public function edit()
    {
        $this->auth();
        Permissions::require('update');

        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['flash_error'] = 'Allergy not found.';
            header("Location: /dental-management-system/public/allergies");
            exit;
        }

        $pdo = require __DIR__ . '/../../config/database.php';
        $stmt = $pdo->prepare("SELECT * FROM allergies WHERE id = ?");
        $stmt->execute([$id]);
        $allergy = $stmt->fetch();

        if (!$allergy) {
            $_SESSION['flash_error'] = 'Allergy not found.';
            header("Location: /dental-management-system/public/allergies");
            exit;
        }

        require __DIR__ . '/../../views/allergies/edit.php';
    }

    public function update()
    {
        $this->auth();
        Permissions::require('update');

        if (empty($_POST['id']) || empty($_POST['name'])) {
            $_SESSION['flash_error'] = 'Allergy name is required.';
            header("Location: /dental-management-system/public/allergies");
            exit;
        }

        $pdo = require __DIR__ . '/../../config/database.php';
        $stmt = $pdo->prepare("UPDATE allergies SET name = ? WHERE id = ?");
        $stmt->execute([$_POST['name'], $_POST['id']]);

        $_SESSION['flash_success'] = 'Allergy updated successfully.';
        header("Location: /dental-management-system/public/allergies");
        exit;
    }

    public function delete()
    {
        $this->auth();
        Permissions::require('delete');

        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['flash_error'] = 'Allergy ID missing.';
            header("Location: /dental-management-system/public/allergies");
            exit;
        }

        $pdo = require __DIR__ . '/../../config/database.php';
        $stmt = $pdo->prepare("DELETE FROM allergies WHERE id = ?");
        $stmt->execute([$id]);

        $_SESSION['flash_success'] = 'Allergy deleted successfully.';
        header("Location: /dental-management-system/public/allergies");
        exit;
    }

    public function search()
    {
        $this->auth();
        Permissions::require('read');

        $q = trim($_GET['q'] ?? '');
        if ($q === '') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([]);
            return;
        }

        $pdo = require __DIR__ . '/../../config/database.php';
        $isId = ctype_digit($q);
        $stmt = $pdo->prepare(
            "SELECT id, name FROM allergies WHERE " . ($isId ? "id = ?" : "name LIKE ?") . " ORDER BY id DESC LIMIT 10"
        );
        $stmt->execute([$isId ? $q : ('%' . $q . '%')]);
        $rows = $stmt->fetchAll();

        $results = [];
        foreach ($rows as $row) {
            $results[] = [
                'id' => (int)$row['id'],
                'name' => $row['name']
            ];
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($results);
    }
}
