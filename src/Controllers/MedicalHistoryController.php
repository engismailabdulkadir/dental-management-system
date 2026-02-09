<?php

require_once __DIR__ . '/../Helpers/Permissions.php';

class MedicalHistoryController
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
        $histories = $pdo->query("
            SELECT mh.id, mh.description, mh.created_at, p.full_name AS patient_name
            FROM medical_histories mh
            JOIN patients p ON mh.patient_id = p.id
            ORDER BY mh.id DESC
        ")->fetchAll();

        require __DIR__ . '/../../views/medical_histories/index.php';
    }

    public function create()
    {
        $this->auth();
        Permissions::require('create');

        $pdo = require __DIR__ . '/../../config/database.php';
        $patients = $pdo->query("SELECT id, full_name FROM patients ORDER BY full_name")->fetchAll();

        require __DIR__ . '/../../views/medical_histories/create.php';
    }

    public function store()
    {
        $this->auth();
        Permissions::require('create');

        if (empty($_POST['patient_id']) || empty($_POST['description'])) {
            $_SESSION['flash_error'] = 'Patient and description are required.';
            header("Location: /dental-management-system/public/medical-histories/create");
            exit;
        }

        $pdo = require __DIR__ . '/../../config/database.php';
        $stmt = $pdo->prepare("
            INSERT INTO medical_histories (patient_id, description, created_at)
            VALUES (?, ?, NOW())
        ");
        $stmt->execute([$_POST['patient_id'], $_POST['description']]);

        $_SESSION['flash_success'] = 'Medical history created successfully.';
        header("Location: /dental-management-system/public/medical-histories");
        exit;
    }

    public function edit()
    {
        $this->auth();
        Permissions::require('update');

        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['flash_error'] = 'Record not found.';
            header("Location: /dental-management-system/public/medical-histories");
            exit;
        }

        $pdo = require __DIR__ . '/../../config/database.php';
        $stmt = $pdo->prepare("
            SELECT mh.*, p.full_name AS patient_name
            FROM medical_histories mh
            JOIN patients p ON mh.patient_id = p.id
            WHERE mh.id = ?
        ");
        $stmt->execute([$id]);
        $history = $stmt->fetch();

        if (!$history) {
            $_SESSION['flash_error'] = 'Record not found.';
            header("Location: /dental-management-system/public/medical-histories");
            exit;
        }

        $patients = $pdo->query("SELECT id, full_name FROM patients ORDER BY full_name")->fetchAll();

        require __DIR__ . '/../../views/medical_histories/edit.php';
    }

    public function update()
    {
        $this->auth();
        Permissions::require('update');

        if (empty($_POST['id']) || empty($_POST['patient_id']) || empty($_POST['description'])) {
            $_SESSION['flash_error'] = 'Patient and description are required.';
            header("Location: /dental-management-system/public/medical-histories");
            exit;
        }

        $pdo = require __DIR__ . '/../../config/database.php';
        $stmt = $pdo->prepare("
            UPDATE medical_histories
            SET patient_id = ?, description = ?
            WHERE id = ?
        ");
        $stmt->execute([$_POST['patient_id'], $_POST['description'], $_POST['id']]);

        $_SESSION['flash_success'] = 'Medical history updated successfully.';
        header("Location: /dental-management-system/public/medical-histories");
        exit;
    }

    public function delete()
    {
        $this->auth();
        Permissions::require('delete');

        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['flash_error'] = 'Record ID missing.';
            header("Location: /dental-management-system/public/medical-histories");
            exit;
        }

        $pdo = require __DIR__ . '/../../config/database.php';
        $stmt = $pdo->prepare("DELETE FROM medical_histories WHERE id = ?");
        $stmt->execute([$id]);

        $_SESSION['flash_success'] = 'Medical history deleted successfully.';
        header("Location: /dental-management-system/public/medical-histories");
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
        $stmt = $pdo->prepare("
            SELECT mh.id, mh.description, mh.created_at, p.full_name AS patient_name
            FROM medical_histories mh
            JOIN patients p ON mh.patient_id = p.id
            WHERE " . ($isId ? "mh.id = ?" : "p.full_name LIKE ?") . "
            ORDER BY mh.id DESC
            LIMIT 10
        ");
        $stmt->execute([$isId ? $q : ('%' . $q . '%')]);
        $rows = $stmt->fetchAll();

        $results = [];
        foreach ($rows as $row) {
            $results[] = [
                'id' => (int)$row['id'],
                'patient_name' => $row['patient_name'],
                'description' => $row['description'],
                'created_at' => $row['created_at']
            ];
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($results);
    }
}
