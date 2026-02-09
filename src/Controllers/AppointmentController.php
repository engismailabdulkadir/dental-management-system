<?php

require_once __DIR__ . '/../Helpers/Permissions.php';

class AppointmentController
{
    // ======================
    // LIST APPOINTMENTS
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

        $stmt = $pdo->query("
            SELECT 
                appointments.id,
                patients.full_name AS patient_name,
                doctors.full_name AS doctor_name,
                appointments.appointment_date,
                appointments.appointment_time,
                appointments.status
            FROM appointments
            JOIN patients ON appointments.patient_id = patients.id
            JOIN doctors ON appointments.doctor_id = doctors.id
            ORDER BY appointments.id DESC
        ");

        $appointments = $stmt->fetchAll();

        require __DIR__ . '/../../views/appointments/index.php';
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

        $pdo = require __DIR__ . '/../../config/database.php';

        $patients = $pdo->query("SELECT id, full_name FROM patients")->fetchAll();
        $doctors  = $pdo->query("SELECT id, full_name FROM doctors")->fetchAll();

        require __DIR__ . '/../../views/appointments/create.php';
    }

    // ======================
    // STORE APPOINTMENT
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

        if (
            empty($_POST['patient_id']) ||
            empty($_POST['doctor_id']) ||
            empty($_POST['appointment_date']) ||
            empty($_POST['appointment_time'])
        ) {
            die("❌ All fields are required.");
        }

        $pdo = require __DIR__ . '/../../config/database.php';

        // 🔒 DOUBLE BOOKING CHECK (CREATE)
        $check = $pdo->prepare("
            SELECT COUNT(*)
            FROM appointments
            WHERE doctor_id = ?
              AND appointment_date = ?
              AND appointment_time = ?
              AND status = 'booked'
        ");

        $check->execute([
            $_POST['doctor_id'],
            $_POST['appointment_date'],
            $_POST['appointment_time']
        ]);

        if ($check->fetchColumn() > 0) {
            $_SESSION['flash_error'] = 'This doctor is already booked at this time.';
            header("Location: /dental-management-system/public/appointments/create");
            exit;
        }

        $stmt = $pdo->prepare("
            INSERT INTO appointments
            (patient_id, doctor_id, appointment_date, appointment_time, status)
            VALUES (?, ?, ?, ?, 'booked')
        ");

        $stmt->execute([
            $_POST['patient_id'],
            $_POST['doctor_id'],
            $_POST['appointment_date'],
            $_POST['appointment_time']
        ]);

        $_SESSION['flash_success'] = 'Appointment created successfully.';
        header("Location: /dental-management-system/public/appointments");
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
            header("Location: /dental-management-system/public/appointments");
            exit;
        }

        $stmt = $pdo->prepare("SELECT * FROM appointments WHERE id = ?");
        $stmt->execute([$id]);
        $appointment = $stmt->fetch();

        $patients = $pdo->query("SELECT id, full_name FROM patients")->fetchAll();
        $doctors  = $pdo->query("SELECT id, full_name FROM doctors")->fetchAll();

        require __DIR__ . '/../../views/appointments/edit.php';
    }

    // ======================
    // UPDATE APPOINTMENT
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

        if (
            empty($_POST['id']) ||
            empty($_POST['patient_id']) ||
            empty($_POST['doctor_id']) ||
            empty($_POST['appointment_date']) ||
            empty($_POST['appointment_time']) ||
            empty($_POST['status'])
        ) {
            die("❌ All fields are required.");
        }

        $pdo = require __DIR__ . '/../../config/database.php';

        // 🔒 DOUBLE BOOKING CHECK (UPDATE – exclude current row)
        $check = $pdo->prepare("
            SELECT COUNT(*)
            FROM appointments
            WHERE doctor_id = ?
              AND appointment_date = ?
              AND appointment_time = ?
              AND id != ?
              AND status = 'booked'
        ");

        $check->execute([
            $_POST['doctor_id'],
            $_POST['appointment_date'],
            $_POST['appointment_time'],
            $_POST['id']
        ]);

        if ($check->fetchColumn() > 0) {
            $_SESSION['flash_error'] = 'This doctor already has another appointment at this time.';
            header("Location: /dental-management-system/public/appointments/edit?id=" . urlencode($_POST['id']));
            exit;
        }

        $stmt = $pdo->prepare("
            UPDATE appointments
            SET patient_id = ?, doctor_id = ?, appointment_date = ?, appointment_time = ?, status = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $_POST['patient_id'],
            $_POST['doctor_id'],
            $_POST['appointment_date'],
            $_POST['appointment_time'],
            $_POST['status'],
            $_POST['id']
        ]);

        $_SESSION['flash_success'] = 'Appointment updated successfully.';
        header("Location: /dental-management-system/public/appointments");
        exit;
    }

    // ======================
    // DELETE APPOINTMENT
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

        $stmt = $pdo->prepare("DELETE FROM appointments WHERE id = ?");
        $stmt->execute([$_GET['id']]);

        $_SESSION['flash_success'] = 'Appointment deleted successfully.';
        header("Location: /dental-management-system/public/appointments");
        exit;
    }

    // ======================
    // LIVE SEARCH APPOINTMENTS (JSON)
    // ======================
    public function search()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user'])) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([]);
            return;
        }

        $q = trim($_GET['q'] ?? '');
        if ($q === '') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([]);
            return;
        }

        $pdo = require __DIR__ . '/../../config/database.php';
        $isId = ctype_digit($q);

        if ($isId) {
            $stmt = $pdo->prepare("SELECT a.id, p.full_name AS patient_name, d.full_name AS doctor_name, a.appointment_date, a.appointment_time, a.status FROM appointments a JOIN patients p ON a.patient_id = p.id JOIN doctors d ON a.doctor_id = d.id WHERE a.id = ? LIMIT 10");
            $stmt->execute([$q]);
        } else {
            $like = '%' . $q . '%';
            $stmt = $pdo->prepare("SELECT a.id, p.full_name AS patient_name, d.full_name AS doctor_name, a.appointment_date, a.appointment_time, a.status FROM appointments a JOIN patients p ON a.patient_id = p.id JOIN doctors d ON a.doctor_id = d.id WHERE p.full_name LIKE ? OR d.full_name LIKE ? ORDER BY a.appointment_date DESC, a.appointment_time DESC LIMIT 12");
            $stmt->execute([$like, $like]);
        }

        $rows = $stmt->fetchAll();
        $results = [];
        foreach ($rows as $r) {
            $results[] = [
                'id' => (int)$r['id'],
                'patient_name' => $r['patient_name'],
                'doctor_name' => $r['doctor_name'],
                'appointment_date' => $r['appointment_date'],
                'appointment_time' => $r['appointment_time'],
                'status' => $r['status']
            ];
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($results);
    }
}
