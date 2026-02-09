<?php

require_once __DIR__ . '/../Helpers/Permissions.php';

class TreatmentProcedureController
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

    // ======================
    // SHOW ADD PROCEDURES FORM
    // ======================
    public function create()
    {
        $this->auth();
        Permissions::require('read');

        $treatment_id = $_GET['treatment_id'] ?? null;
        if (!$treatment_id) {
            die("Treatment not specified");
        }

        $pdo = require __DIR__ . '/../../config/database.php';

        // treatment info
        $stmt = $pdo->prepare("
            SELECT t.id, p.full_name AS patient_name
            FROM treatments t
            JOIN appointments a ON t.appointment_id = a.id
            JOIN patients p ON a.patient_id = p.id
            WHERE t.id = ?
        ");
        $stmt->execute([$treatment_id]);
        $treatment = $stmt->fetch();

        // procedures list
        $procedures = $pdo->query("SELECT * FROM procedures")->fetchAll();

        require __DIR__ . '/../../views/treatments/add_procedures.php';
    }

    // ======================
    // STORE PROCEDURES
    // ======================
    public function store()
    {
        $this->auth();
        Permissions::require('create');

        $pdo = require __DIR__ . '/../../config/database.php';

        $treatment_id = $_POST['treatment_id'];

        foreach ($_POST['procedures'] as $procedure_id => $row) {
            if (empty($row['checked'])) {
                continue;
            }

            $qty = (int)($row['qty'] ?? 0);
            $price = (float)($row['price'] ?? 0);
            if ($qty <= 0) {
                $_SESSION['flash_error'] = 'Quantity must be at least 1.';
                header("Location: /dental-management-system/public/treatments/procedures/create?treatment_id=" . urlencode($treatment_id));
                exit;
            }
            if ($price < 0) {
                $_SESSION['flash_error'] = 'Price cannot be negative.';
                header("Location: /dental-management-system/public/treatments/procedures/create?treatment_id=" . urlencode($treatment_id));
                exit;
            }

            $stmt = $pdo->prepare("
                INSERT INTO treatment_procedures
                (treatment_id, procedure_id, qty, price)
                VALUES (?, ?, ?, ?)
            ");

            $stmt->execute([
                $treatment_id,
                $procedure_id,
                $qty,
                $price
            ]);
        }

        $_SESSION['flash_success'] = 'Procedures added successfully.';
        header(
            "Location: /dental-management-system/public/treatments"
        );
        exit;
    }
}
