<?php

require_once __DIR__ . '/../Helpers/Permissions.php';

class DoctorController
{
    // ======================
    // LIST DOCTORS
    // ======================
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user'])) {
            header("Location: /dental-management-system/public/login");
            exit;
        }
        Permissions::require('read');

        $pdo = require __DIR__ . '/../../config/database.php';

        $stmt = $pdo->query("SELECT * FROM doctors ORDER BY id DESC");
        $doctors = $stmt->fetchAll();

        require __DIR__ . '/../../views/doctors/index.php';
    }

    // ======================
    // SHOW CREATE FORM
    // ======================
    public function create()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user'])) {
            header("Location: /dental-management-system/public/login");
            exit;
        }
        Permissions::require('create');

        require __DIR__ . '/../../views/doctors/create.php';
    }

    // ======================
    // STORE DOCTOR
    // ======================
    public function store()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user'])) {
            header("Location: /dental-management-system/public/login");
            exit;
        }
        Permissions::require('create');

        $pdo = require __DIR__ . '/../../config/database.php';

        $phone = preg_replace('/\\D+/', '', (string)($_POST['phone'] ?? ''));
        $phone = $phone === '' ? null : $phone;

        if ($phone !== null) {
            // Ensure phone is unique across the system (doctors + patients)
            $stmt = $pdo->prepare("SELECT id FROM doctors WHERE phone = ? LIMIT 1");
            $stmt->execute([$phone]);
            if ($stmt->fetch()) {
                $_SESSION['flash_error'] = 'Phone number already exists in the system.';
                header("Location: /dental-management-system/public/doctors/create");
                exit;
            }
            $stmt = $pdo->prepare("SELECT id FROM patients WHERE phone = ? LIMIT 1");
            $stmt->execute([$phone]);
            if ($stmt->fetch()) {
                $_SESSION['flash_error'] = 'Phone number already exists in the system.';
                header("Location: /dental-management-system/public/doctors/create");
                exit;
            }
        }

        $photoPath = null;
        if (!empty($_FILES['photo']['name'])) {
            $uploadDir = __DIR__ . '/../../public/uploads/doctors';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $fileName = 'doctor_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
            $dest = $uploadDir . '/' . $fileName;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $dest)) {
                $photoPath = 'uploads/doctors/' . $fileName;
            }
        }

        $stmt = $pdo->prepare("
            INSERT INTO doctors (full_name, specialization, phone, photo)
            VALUES (?, ?, ?, ?)
        ");

        $stmt->execute([
            $_POST['full_name'],
            $_POST['specialization'],
            $phone,
            $photoPath
        ]);

        $_SESSION['flash_success'] = 'Doctor created successfully.';
        header("Location: /dental-management-system/public/doctors");
        exit;
    }

    // ======================
    // SHOW EDIT FORM
    // ======================
    public function edit()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user'])) {
            header("Location: /dental-management-system/public/login");
            exit;
        }
        Permissions::require('update');

        $pdo = require __DIR__ . '/../../config/database.php';

        $id = $_GET['id'] ?? null;
        if (!$id) {
            header("Location: /dental-management-system/public/doctors");
            exit;
        }

        $stmt = $pdo->prepare("SELECT * FROM doctors WHERE id = ?");
        $stmt->execute([$id]);
        $doctor = $stmt->fetch();

        require __DIR__ . '/../../views/doctors/edit.php';
    }

    // ======================
    // UPDATE DOCTOR
    // ======================
    public function update()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user'])) {
            header("Location: /dental-management-system/public/login");
            exit;
        }
        Permissions::require('update');

        $pdo = require __DIR__ . '/../../config/database.php';

        $phone = preg_replace('/\\D+/', '', (string)($_POST['phone'] ?? ''));
        $phone = $phone === '' ? null : $phone;

        if ($phone !== null) {
            // Ensure phone is unique across the system (doctors + patients)
            $stmt = $pdo->prepare("SELECT id FROM doctors WHERE phone = ? AND id <> ? LIMIT 1");
            $stmt->execute([$phone, $_POST['id']]);
            if ($stmt->fetch()) {
                $_SESSION['flash_error'] = 'Phone number already exists in the system.';
                header("Location: /dental-management-system/public/doctors/edit?id=" . urlencode($_POST['id'] ?? ''));
                exit;
            }
            $stmt = $pdo->prepare("SELECT id FROM patients WHERE phone = ? LIMIT 1");
            $stmt->execute([$phone]);
            if ($stmt->fetch()) {
                $_SESSION['flash_error'] = 'Phone number already exists in the system.';
                header("Location: /dental-management-system/public/doctors/edit?id=" . urlencode($_POST['id'] ?? ''));
                exit;
            }
        }

        $photoPath = null;
        if (!empty($_FILES['photo']['name'])) {
            $uploadDir = __DIR__ . '/../../public/uploads/doctors';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $fileName = 'doctor_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
            $dest = $uploadDir . '/' . $fileName;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $dest)) {
                $photoPath = 'uploads/doctors/' . $fileName;
            }
        }

        if ($photoPath) {
            $stmt = $pdo->prepare("
                UPDATE doctors
                SET full_name = ?, specialization = ?, phone = ?, photo = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $_POST['full_name'],
                $_POST['specialization'],
                $phone,
                $photoPath,
                $_POST['id']
            ]);
        } else {
            $stmt = $pdo->prepare("
                UPDATE doctors
                SET full_name = ?, specialization = ?, phone = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $_POST['full_name'],
                $_POST['specialization'],
                $phone,
                $_POST['id']
            ]);
        }

        $_SESSION['flash_success'] = 'Doctor updated successfully.';
        header("Location: /dental-management-system/public/doctors");
        exit;
    }

    // ======================
    // DELETE DOCTOR
    // ======================
    public function delete()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user'])) {
            header("Location: /dental-management-system/public/login");
            exit;
        }
        Permissions::require('delete');

        $pdo = require __DIR__ . '/../../config/database.php';

        $doctorId = $_GET['id'] ?? null;
        if (!$doctorId) {
            $_SESSION['flash_error'] = 'Invalid doctor id.';
            header("Location: /dental-management-system/public/doctors");
            exit;
        }

        $pdo->beginTransaction();
        try {
            // Remove dependent appointments first to satisfy FK constraints
            $stmt = $pdo->prepare("DELETE FROM appointments WHERE doctor_id = ?");
            $stmt->execute([$doctorId]);

            $stmt = $pdo->prepare("DELETE FROM doctors WHERE id = ?");
            $stmt->execute([$doctorId]);

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        $_SESSION['flash_success'] = 'Doctor deleted successfully.';
        header("Location: /dental-management-system/public/doctors");
        exit;
    }

    // ======================
    // LIVE SEARCH DOCTORS (JSON)
    // ======================
    public function search()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user'])) {
            header("Location: /dental-management-system/public/login");
            exit;
        }
        Permissions::require('read');

        $q = trim($_GET['q'] ?? '');
        header('Content-Type: application/json; charset=utf-8');
        if ($q === '') {
            echo json_encode([]);
            return;
        }

        $pdo = require __DIR__ . '/../../config/database.php';
        $like = '%' . $q . '%';
        $stmt = $pdo->prepare("
            SELECT id, full_name, specialization, phone, photo
            FROM doctors
            WHERE full_name LIKE ?
               OR specialization LIKE ?
               OR phone LIKE ?
               OR CAST(id AS CHAR) LIKE ?
            ORDER BY full_name
            LIMIT 20
        ");
        $stmt->execute([$like, $like, $like, $like]);
        $rows = $stmt->fetchAll();

        $results = [];
        foreach ($rows as $row) {
            $results[] = [
                'id' => (int)$row['id'],
                'name' => $row['full_name'],
                'specialization' => $row['specialization'],
                'phone' => $row['phone'],
                'photo' => $row['photo']
            ];
        }

        echo json_encode($results);
    }
}
