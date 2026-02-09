<?php

require_once __DIR__ . '/../Helpers/Permissions.php';

class PatientController
{
    private function requireAuth()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user'])) {
            header("Location: /dental-management-system/public/login");
            exit;
        }
    }

    // ======================
    // LIST PATIENTS
    // ======================
    public function index()
    {
        $this->requireAuth();
        Permissions::require('read');

        $pdo = require __DIR__ . '/../../config/database.php';
        $stmt = $pdo->query("SELECT * FROM patients ORDER BY id DESC");
        $patients = $stmt->fetchAll();

        require __DIR__ . '/../../views/patients/index.php';
    }

    // ======================
    // SHOW CREATE FORM
    // ======================
    public function create()
    {
        $this->requireAuth();
        Permissions::require('create');
        require __DIR__ . '/../../views/patients/create.php';
    }

    // ======================
    // STORE PATIENT
    // ======================
    public function store()
    {
        $this->requireAuth();
        Permissions::require('create');

        // Require all patient fields to be provided
        $required = ['full_name', 'gender', 'date_of_birth', 'phone', 'address'];
        $missing = [];
        foreach ($required as $f) {
            if (!isset($_POST[$f]) || trim($_POST[$f]) === '') $missing[] = $f;
        }
        if (!empty($missing)) {
            $_SESSION['flash_error'] = 'Please fill all required patient fields.';
            header("Location: /dental-management-system/public/patients/create");
            exit;
        }

        $fullName = trim((string)($_POST['full_name'] ?? ''));
        $address = trim((string)($_POST['address'] ?? ''));
        $phone = preg_replace('/\D+/', '', (string)($_POST['phone'] ?? ''));

        // full name must be exactly 3 words
        $nameParts = preg_split('/\s+/', $fullName, -1, PREG_SPLIT_NO_EMPTY);
        if (count($nameParts) !== 3) {
            $_SESSION['flash_error'] = 'Full Name must be exactly 3 words.';
            header("Location: /dental-management-system/public/patients/create");
            exit;
        }

        // address must be longer than 5 chars and include at least one letter
        if (strlen($address) <= 5 || !preg_match('/[A-Za-z]/', $address)) {
            $_SESSION['flash_error'] = 'Address must be at least 6 characters and include at least one letter.';
            header("Location: /dental-management-system/public/patients/create");
            exit;
        }

        // phone must be exactly 10 digits
        if (strlen($phone) !== 10) {
            $_SESSION['flash_error'] = 'Phone must be exactly 10 digits.';
            header("Location: /dental-management-system/public/patients/create");
            exit;
        }

        $pdo = require __DIR__ . '/../../config/database.php';
        // Ensure phone is unique across the system (patients + doctors)
        $stmt = $pdo->prepare("SELECT id FROM patients WHERE phone = ? LIMIT 1");
        $stmt->execute([$phone]);
        if ($stmt->fetch()) {
            $_SESSION['flash_error'] = 'Phone number already exists in the system.';
            header("Location: /dental-management-system/public/patients/create");
            exit;
        }
        $stmt = $pdo->prepare("SELECT id FROM doctors WHERE phone = ? LIMIT 1");
        $stmt->execute([$phone]);
        if ($stmt->fetch()) {
            $_SESSION['flash_error'] = 'Phone number already exists in the system.';
            header("Location: /dental-management-system/public/patients/create");
            exit;
        }

        $stmt = $pdo->prepare("
            INSERT INTO patients (full_name, gender, date_of_birth, phone, address)
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $fullName,
            $_POST['gender'],
            $_POST['date_of_birth'] ?? null,
            $phone,
            $address
        ]);

        $_SESSION['flash_success'] = 'Patient created successfully.';
        header("Location: /dental-management-system/public/patients");
        exit;
    }

    // ======================
    // SHOW EDIT FORM
    // ======================
    public function edit()
    {
        $this->requireAuth();
        Permissions::require('update');

        $id = $_GET['id'] ?? null;
        if (!$id) {
            header("Location: /dental-management-system/public/patients");
            exit;
        }

        $pdo = require __DIR__ . '/../../config/database.php';
        $stmt = $pdo->prepare("SELECT * FROM patients WHERE id = ?");
        $stmt->execute([$id]);
        $patient = $stmt->fetch();

        if (!$patient) {
            header("Location: /dental-management-system/public/patients");
            exit;
        }

        require __DIR__ . '/../../views/patients/edit.php';
    }

    // ======================
    // UPDATE PATIENT
    // ======================
    public function update()
    {
        $this->requireAuth();
        Permissions::require('update');

        $required = ['id', 'full_name', 'gender', 'date_of_birth', 'phone', 'address'];
        $missing = [];
        foreach ($required as $f) {
            if (!isset($_POST[$f]) || trim($_POST[$f]) === '') $missing[] = $f;
        }
        if (!empty($missing)) {
            $_SESSION['flash_error'] = 'Please fill all required patient fields.';
            header("Location: /dental-management-system/public/patients/edit?id=" . urlencode($_POST['id'] ?? ''));
            exit;
        }

        $fullName = trim((string)($_POST['full_name'] ?? ''));
        $address = trim((string)($_POST['address'] ?? ''));
        $phone = preg_replace('/\D+/', '', (string)($_POST['phone'] ?? ''));
        $nameParts = preg_split('/\s+/', $fullName, -1, PREG_SPLIT_NO_EMPTY);
        if (count($nameParts) !== 3) {
            $_SESSION['flash_error'] = 'Full Name must be exactly 3 words.';
            header("Location: /dental-management-system/public/patients/edit?id=" . urlencode($_POST['id'] ?? ''));
            exit;
        }
        if (strlen($address) <= 5 || !preg_match('/[A-Za-z]/', $address)) {
            $_SESSION['flash_error'] = 'Address must be at least 6 characters and include at least one letter.';
            header("Location: /dental-management-system/public/patients/edit?id=" . urlencode($_POST['id'] ?? ''));
            exit;
        }
        if (strlen($phone) !== 10) {
            $_SESSION['flash_error'] = 'Phone must be exactly 10 digits.';
            header("Location: /dental-management-system/public/patients/edit?id=" . urlencode($_POST['id'] ?? ''));
            exit;
        }

        $pdo = require __DIR__ . '/../../config/database.php';
        // Ensure phone is unique across the system (patients + doctors)
        $stmt = $pdo->prepare("SELECT id FROM patients WHERE phone = ? AND id <> ? LIMIT 1");
        $stmt->execute([$phone, $_POST['id']]);
        if ($stmt->fetch()) {
            $_SESSION['flash_error'] = 'Phone number already exists in the system.';
            header("Location: /dental-management-system/public/patients/edit?id=" . urlencode($_POST['id'] ?? ''));
            exit;
        }
        $stmt = $pdo->prepare("SELECT id FROM doctors WHERE phone = ? LIMIT 1");
        $stmt->execute([$phone]);
        if ($stmt->fetch()) {
            $_SESSION['flash_error'] = 'Phone number already exists in the system.';
            header("Location: /dental-management-system/public/patients/edit?id=" . urlencode($_POST['id'] ?? ''));
            exit;
        }

        $stmt = $pdo->prepare("
            UPDATE patients
            SET full_name = ?, gender = ?, date_of_birth = ?, phone = ?, address = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $fullName,
            $_POST['gender'],
            $_POST['date_of_birth'] ?? null,
            $phone,
            $address,
            $_POST['id']
        ]);

        $_SESSION['flash_success'] = 'Patient updated successfully.';
        header("Location: /dental-management-system/public/patients");
        exit;
    }

    // ======================
    // DELETE PATIENT
    // ======================
    public function delete()
    {
        $this->requireAuth();
        Permissions::require('delete');

        $id = $_GET['id'] ?? null;
        if (!$id) {
            header("Location: /dental-management-system/public/patients");
            exit;
        }

        $pdo = require __DIR__ . '/../../config/database.php';
        try {
            $stmt = $pdo->prepare("DELETE FROM patients WHERE id = ?");
            $stmt->execute([$id]);

            $_SESSION['flash_success'] = 'Patient deleted successfully.';
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = 'Cannot delete patient. This patient has related appointments or invoices.';
        }

        header("Location: /dental-management-system/public/patients");
        exit;
    }

    // ======================
    // LIVE SEARCH PATIENTS (JSON)
    // ======================
    public function search()
    {
        $this->requireAuth();

        $q = trim($_GET['q'] ?? '');
        if ($q === '') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([]);
            return;
        }

        $pdo = require __DIR__ . '/../../config/database.php';
            $stmt = $pdo->prepare("
                SELECT id, full_name, gender, phone, date_of_birth, address
                FROM patients
                WHERE full_name LIKE ?
                ORDER BY full_name
                LIMIT 12
            ");
        $stmt->execute(['%' . $q . '%']);
        $rows = $stmt->fetchAll();

        $results = [];
        foreach ($rows as $row) {
                $results[] = [
                    'id' => (int)$row['id'],
                    'name' => $row['full_name'],
                    'gender' => $row['gender'],
                    'phone' => $row['phone'],
                    'date_of_birth' => $row['date_of_birth'],
                    'address' => $row['address']
                ];
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($results);
    }
}
