<?php

require_once __DIR__ . '/../Helpers/Permissions.php';

class DashboardController
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

        // ======================
        // COUNTS (Admin view)
        // ======================
        $patientsCount = (int)$pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn();
        $appointmentsCount = (int)$pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn();
        $treatmentsCount = (int)$pdo->query("SELECT COUNT(*) FROM treatments")->fetchColumn();
        $paymentsCount = (int)$pdo->query("SELECT COUNT(*) FROM payments")->fetchColumn();
        $paidInvoices = 0;
        $unpaidInvoices = 0;


        // Revenue = SUM(payments.amount)
        $revenue = (float)$pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments")->fetchColumn();

        // Invoice status (read directly from database status column)
        $invoiceCols = $pdo->query("SHOW COLUMNS FROM invoices")->fetchAll(PDO::FETCH_COLUMN, 0);
        if (in_array('status', $invoiceCols, true)) {
            $paidInvoices = (int)$pdo->query("SELECT COUNT(*) FROM invoices WHERE status = 'paid'")->fetchColumn();
            $unpaidInvoices = (int)$pdo->query("SELECT COUNT(*) FROM invoices WHERE status != 'paid'")->fetchColumn();
        } elseif (in_array('total', $invoiceCols, true)) {
            // Fallback if no status column exists
            $paidInvoices = (int)$pdo->query("
                SELECT COUNT(*)
                FROM invoices i
                JOIN (
                    SELECT invoice_id, COALESCE(SUM(amount), 0) AS paid_amount
                    FROM payments
                    GROUP BY invoice_id
                ) p ON p.invoice_id = i.id
                WHERE COALESCE(p.paid_amount, 0) >= COALESCE(i.total, 0)
            ")->fetchColumn();

            $unpaidInvoices = (int)$pdo->query("
                SELECT COUNT(*)
                FROM invoices i
                JOIN (
                    SELECT invoice_id, COALESCE(SUM(amount), 0) AS paid_amount
                    FROM payments
                    GROUP BY invoice_id
                ) p ON p.invoice_id = i.id
                WHERE COALESCE(p.paid_amount, 0) < COALESCE(i.total, 0)
            ")->fetchColumn();
        }

        // Today appointments list
        $todayAppointmentsStmt = $pdo->prepare("
            SELECT 
                a.id,
                a.appointment_date,
                a.appointment_time,
                a.status,
                p.full_name AS patient_name,
                d.full_name AS doctor_name
            FROM appointments a
            JOIN patients p ON p.id = a.patient_id
            JOIN doctors d ON d.id = a.doctor_id
            WHERE a.appointment_date = CURDATE()
            ORDER BY a.appointment_time ASC
            LIMIT 6
        ");
        $todayAppointmentsStmt->execute();
        $todayAppointments = $todayAppointmentsStmt->fetchAll();

        // Next patient details (closest upcoming today)
        $nextPatient = null;
        $nextStmt = $pdo->prepare("
            SELECT 
                p.id AS patient_id,
                p.full_name,
                p.gender,
                p.date_of_birth,
                p.phone,
                p.address,
                a.appointment_date,
                a.appointment_time,
                d.full_name AS doctor_name
            FROM appointments a
            JOIN patients p ON p.id = a.patient_id
            JOIN doctors d ON d.id = a.doctor_id
            WHERE a.appointment_date = CURDATE()
              AND a.appointment_time >= CURTIME()
            ORDER BY a.appointment_time ASC
            LIMIT 1
        ");
        $nextStmt->execute();
        $nextPatient = $nextStmt->fetch();

        // Recent payments
            // Recent payments (include invoice.status when available)
            if (in_array('status', $invoiceCols, true)) {
                $recentPaymentsStmt = $pdo->query(
                    "SELECT pm.id, pm.amount, pm.method, pm.paid_at, pm.invoice_id, i.total, i.status AS invoice_status
                     FROM payments pm
                     JOIN invoices i ON i.id = pm.invoice_id
                     ORDER BY pm.id DESC
                     LIMIT 5"
                );
            } else {
                $recentPaymentsStmt = $pdo->query(
                    "SELECT pm.id, pm.amount, pm.method, pm.paid_at, pm.invoice_id, i.total
                     FROM payments pm
                     JOIN invoices i ON i.id = pm.invoice_id
                     ORDER BY pm.id DESC
                     LIMIT 5"
                );
            }
        $recentPayments = $recentPaymentsStmt->fetchAll();

        // Role (optional filtering)
        $roleId = $_SESSION['user']['role_id'] ?? null;

        require __DIR__ . '/../../views/dashboard/index.php';
    }

    // ======================
    // DOCTOR DASHBOARD
    // ======================
    public function doctor()
    {
        $this->auth();
        Permissions::require('read');

        $roleId = $_SESSION['user']['role_id'] ?? 0;
        if (!in_array($roleId, [3], true)) {
            header("Location: /dental-management-system/public/dashboard");
            exit;
        }

        $doctorId = $_SESSION['user']['doctor_id'] ?? null;
        if (!$doctorId) {
            // Try to link by full name if doctor_id is missing
            $fullName = $_SESSION['user']['full_name'] ?? '';
            if ($fullName) {
                $pdo = require __DIR__ . '/../../config/database.php';
                $linkStmt = $pdo->prepare("SELECT id, full_name, specialization, photo FROM doctors WHERE full_name = ?");
                $linkStmt->execute([$fullName]);
                $foundDoctor = $linkStmt->fetch();
                if ($foundDoctor) {
                    // Update users table if column exists
                    $cols = $pdo->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN, 0);
                    if (in_array('doctor_id', $cols, true)) {
                        $pdo->prepare("UPDATE users SET doctor_id = ? WHERE id = ?")
                            ->execute([$foundDoctor['id'], $_SESSION['user']['id']]);
                    }
                    $_SESSION['user']['doctor_id'] = $foundDoctor['id'];
                    $doctorId = $foundDoctor['id'];
                }
            }
        }

        if (!$doctorId) {
            $_SESSION['flash_error'] = 'Doctor profile not linked to your user. Please link doctor_id in users table.';
            header("Location: /dental-management-system/public/dashboard");
            exit;
        }

        if (!isset($pdo)) {
            $pdo = require __DIR__ . '/../../config/database.php';
        }

        $doctor = null;
        $stmt = $pdo->prepare("SELECT full_name, specialization, photo FROM doctors WHERE id = ?");
        $stmt->execute([$doctorId]);
        $doctor = $stmt->fetch();

        $today = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE doctor_id = ? AND appointment_date = CURDATE()");
        $today->execute([$doctorId]);
        $todayAppointmentsCount = (int)$today->fetchColumn();

        $totalPatientsStmt = $pdo->prepare("
            SELECT COUNT(DISTINCT patient_id)
            FROM appointments
            WHERE doctor_id = ?
        ");
        $totalPatientsStmt->execute([$doctorId]);
        $totalPatients = (int)$totalPatientsStmt->fetchColumn();

        $totalTreatmentsStmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM treatments
            WHERE doctor_id = ?
        ");
        $totalTreatmentsStmt->execute([$doctorId]);
        $totalTreatments = (int)$totalTreatmentsStmt->fetchColumn();

        $upcomingStmt = $pdo->prepare("
            SELECT 
                a.id,
                a.appointment_date,
                a.appointment_time,
                a.status,
                p.full_name AS patient_name
            FROM appointments a
            JOIN patients p ON p.id = a.patient_id
            WHERE a.doctor_id = ?
              AND a.appointment_date >= CURDATE()
            ORDER BY a.appointment_date ASC, a.appointment_time ASC
            LIMIT 6
        ");
        $upcomingStmt->execute([$doctorId]);
        $upcomingAppointments = $upcomingStmt->fetchAll();

        $nextStmt = $pdo->prepare("
            SELECT 
                p.full_name,
                p.gender,
                p.date_of_birth,
                p.phone,
                p.address,
                a.appointment_date,
                a.appointment_time
            FROM appointments a
            JOIN patients p ON p.id = a.patient_id
            WHERE a.doctor_id = ?
              AND a.appointment_date = CURDATE()
              AND a.appointment_time >= CURTIME()
            ORDER BY a.appointment_time ASC
            LIMIT 1
        ");
        $nextStmt->execute([$doctorId]);
        $nextPatient = $nextStmt->fetch();

        require __DIR__ . '/../../views/dashboard/doctor.php';
    }
}
