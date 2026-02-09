<?php

require_once __DIR__ . '/../Helpers/Permissions.php';

class TreatmentController
{
    // ======================
    // AUTH CHECK
    // ======================
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

    // ======================
    // LIST TREATMENTS
    // ======================
    public function index()
    {
        $this->auth();
        Permissions::require('read');

        $pdo = require __DIR__ . '/../../config/database.php';

        // 🧠 Doctor → kaliya treatments-kiisa
        if ($_SESSION['user']['role_id'] == 2) {

            $stmt = $pdo->prepare("
                SELECT 
                    t.id,
                    t.notes,
                    t.created_at,
                    a.appointment_date,
                    a.appointment_time,
                    p.full_name AS patient_name
                FROM treatments t
                JOIN appointments a ON t.appointment_id = a.id
                JOIN patients p ON a.patient_id = p.id
                WHERE t.doctor_id = ?
                ORDER BY t.id DESC
            ");
            $stmt->execute([$_SESSION['user']['doctor_id']]);

        } 
        // 👑 Admin → dhammaan treatments
        else if ($_SESSION['user']['role_id'] == 1) {

            $stmt = $pdo->query("
                SELECT 
                    t.id,
                    t.notes,
                    t.created_at,
                    a.appointment_date,
                    a.appointment_time,
                    p.full_name AS patient_name,
                    d.full_name AS doctor_name
                FROM treatments t
                JOIN appointments a ON t.appointment_id = a.id
                JOIN patients p ON a.patient_id = p.id
                JOIN doctors d ON t.doctor_id = d.id
                ORDER BY t.id DESC
            ");

        } 
        // ❌ Others (patients)
        else {
            die("❌ Access denied");
        }

        $treatments = $stmt->fetchAll();
        require __DIR__ . '/../../views/treatments/index.php';
    }

    // ======================
    // SHOW CREATE FORM (DOCTOR ONLY)
    // ======================
    public function create()
    {
        $this->auth();
        Permissions::require('create');

        $pdo = require __DIR__ . '/../../config/database.php';

        if ($_SESSION['user']['role_id'] == 2) {
            // ONLY booked appointments for THIS doctor, without treatment
            $stmt = $pdo->prepare("
                SELECT 
                    a.id,
                    p.full_name,
                    a.appointment_date,
                    a.appointment_time
                FROM appointments a
                JOIN patients p ON p.id = a.patient_id
                LEFT JOIN treatments t ON t.appointment_id = a.id
                WHERE a.status = 'booked'
                  AND t.id IS NULL
                  AND a.doctor_id = ?
            ");
            $stmt->execute([$_SESSION['user']['doctor_id']]);
            $appointments = $stmt->fetchAll();
            $doctors = [];
        } elseif ($_SESSION['user']['role_id'] == 1) {
            // Admin can create for any doctor
            $appointments = $pdo->query("
                SELECT 
                    a.id,
                    p.full_name,
                    a.appointment_date,
                    a.appointment_time
                FROM appointments a
                JOIN patients p ON p.id = a.patient_id
                LEFT JOIN treatments t ON t.appointment_id = a.id
                WHERE a.status = 'booked'
                  AND t.id IS NULL
                ORDER BY a.id DESC
            ")->fetchAll();
            $doctors = $pdo->query("SELECT id, full_name FROM doctors ORDER BY full_name")->fetchAll();
        } else {
            die("❌ Only doctors or admin can create treatments");
        }

        require __DIR__ . '/../../views/treatments/create.php';
    }

    // ======================
    // STORE TREATMENT (DOCTOR ONLY)
    // ======================
    public function store()
    {
        $this->auth();
        Permissions::require('create');

        if ($_SESSION['user']['role_id'] != 2 && $_SESSION['user']['role_id'] != 1) {
            die("❌ Only doctors or admin can store treatments");
        }

        if (empty($_POST['appointment_id'])) {
            die("❌ Appointment is required");
        }

        $pdo = require __DIR__ . '/../../config/database.php';

        // double check
        $check = $pdo->prepare("
            SELECT COUNT(*) FROM treatments WHERE appointment_id = ?
        ");
        $check->execute([$_POST['appointment_id']]);

        if ($check->fetchColumn() > 0) {
            $_SESSION['flash_error'] = 'This appointment already has a treatment.';
            header("Location: /dental-management-system/public/treatments/create");
            exit;
        }

        $doctorId = $_SESSION['user']['doctor_id'] ?? null;
        if ($_SESSION['user']['role_id'] == 1) {
            $doctorId = $_POST['doctor_id'] ?? null;
            if (!$doctorId) {
                $_SESSION['flash_error'] = 'Doctor is required.';
                header("Location: /dental-management-system/public/treatments/create");
                exit;
            }
        }

        $notes = trim((string)($_POST['notes'] ?? ''));
        if ($notes !== '' && !preg_match('/^[A-Za-z\\s]+$/', $notes)) {
            $_SESSION['flash_error'] = 'Notes must contain only letters.';
            header("Location: /dental-management-system/public/treatments/create");
            exit;
        }

        $stmt = $pdo->prepare("
            INSERT INTO treatments (appointment_id, doctor_id, notes)
            VALUES (?, ?, ?)
        ");

        $stmt->execute([
            $_POST['appointment_id'],
            $doctorId,
            $notes === '' ? null : $notes
        ]);

        $_SESSION['flash_success'] = 'Treatment created successfully.';
        header("Location: /dental-management-system/public/treatments");
        exit;
    }

    // ======================
    // EDIT (DOCTOR & ADMIN)
    // ======================
    public function edit()
    {
        $this->auth();
        Permissions::require('update');

        $id = $_GET['id'] ?? null;
        if (!$id) {
            header("Location: /dental-management-system/public/treatments");
            exit;
        }

        $pdo = require __DIR__ . '/../../config/database.php';

        $stmt = $pdo->prepare("
            SELECT t.*, p.full_name AS patient_name
            FROM treatments t
            JOIN appointments a ON t.appointment_id = a.id
            JOIN patients p ON a.patient_id = p.id
            WHERE t.id = ?
        ");
        $stmt->execute([$id]);
        $treatment = $stmt->fetch();

        require __DIR__ . '/../../views/treatments/edit.php';
    }

    // ======================
    // UPDATE (DOCTOR & ADMIN)
    // ======================
    public function update()
    {
        $this->auth();
        Permissions::require('update');

        $pdo = require __DIR__ . '/../../config/database.php';

        $notes = trim((string)($_POST['notes'] ?? ''));
        if ($notes !== '' && !preg_match('/^[A-Za-z\\s]+$/', $notes)) {
            $_SESSION['flash_error'] = 'Notes must contain only letters.';
            header("Location: /dental-management-system/public/treatments/edit?id=" . urlencode($_POST['id'] ?? ''));
            exit;
        }

        $stmt = $pdo->prepare("
            UPDATE treatments SET notes = ? WHERE id = ?
        ");

        $stmt->execute([
            $notes === '' ? null : $notes,
            $_POST['id']
        ]);

        $_SESSION['flash_success'] = 'Treatment updated successfully.';
        header("Location: /dental-management-system/public/treatments");
        exit;
    }

    // ======================
    // DELETE (ADMIN ONLY)
    // ======================
    public function delete()
    {
        $this->auth();
        Permissions::require('delete');

        if ($_SESSION['user']['role_id'] != 1) {
            die("❌ Only admin can delete treatments");
        }

        $pdo = require __DIR__ . '/../../config/database.php';

        $pdo->prepare("DELETE FROM treatments WHERE id = ?")
            ->execute([$_GET['id']]);

        $_SESSION['flash_success'] = 'Treatment deleted successfully.';
        header("Location: /dental-management-system/public/treatments");
        exit;
    }
}
