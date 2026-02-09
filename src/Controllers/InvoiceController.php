<?php

require_once __DIR__ . '/../Helpers/Permissions.php';

class InvoiceController
{
    // ======================
    // ADMIN ONLY CHECK
    // ======================
    private function adminOnly()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user']) || $_SESSION['user']['role_id'] != 1) {
            die("Access denied (Admin only)");
        }
    }

    private function invoiceMode($pdo)
    {
        $cols = $pdo->query("SHOW COLUMNS FROM invoices")->fetchAll(PDO::FETCH_COLUMN, 0);
        if (in_array('treatment_id', $cols, true)) {
            return 'treatment';
        }
        if (in_array('appointment_id', $cols, true)) {
            return 'appointment';
        }
        if (in_array('patient_id', $cols, true)) {
            return 'patient';
        }
        return 'unknown';
    }

    private function invoiceColumns($pdo)
    {
        return $pdo->query("SHOW COLUMNS FROM invoices")->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    // ======================
    // LIST ALL INVOICES
    // ======================
    public function index()
    {
        $this->adminOnly();
        Permissions::require('read');

        $pdo = require __DIR__ . '/../../config/database.php';
        $mode = $this->invoiceMode($pdo);

        if ($mode === 'treatment') {
            $stmt = $pdo->query("
                SELECT 
                    i.id,
                    i.total,
                    i.created_at,
                    p.full_name AS patient_name
                FROM invoices i
                JOIN treatments t ON i.treatment_id = t.id
                JOIN appointments a ON t.appointment_id = a.id
                JOIN patients p ON a.patient_id = p.id
                ORDER BY i.id DESC
            ");
            $invoices = $stmt->fetchAll();
        } elseif ($mode === 'appointment') {
            $stmt = $pdo->query("
                SELECT 
                    i.id,
                    i.total,
                    i.created_at,
                    p.full_name AS patient_name
                FROM invoices i
                JOIN appointments a ON i.appointment_id = a.id
                JOIN patients p ON a.patient_id = p.id
                ORDER BY i.id DESC
            ");
            $invoices = $stmt->fetchAll();
        } elseif ($mode === 'patient') {
            $stmt = $pdo->query("
                SELECT 
                    i.id,
                    i.total,
                    i.created_at,
                    p.full_name AS patient_name
                FROM invoices i
                JOIN patients p ON i.patient_id = p.id
                ORDER BY i.id DESC
            ");
            $invoices = $stmt->fetchAll();
        } else {
            $invoices = [];
        }

        require __DIR__ . '/../../views/invoices/index.php';
    }

    // ======================
    // CREATE INVOICE
    // ======================
    public function create()
    {
        $this->adminOnly();
        Permissions::require('create');

        $pdo = require __DIR__ . '/../../config/database.php';
        $mode = $this->invoiceMode($pdo);

        if ($mode === 'treatment') {
            $treatment_id = $_GET['treatment_id'] ?? null;
            if (!$treatment_id) {
                die("Treatment not specified");
            }

            $check = $pdo->prepare("
                SELECT COUNT(*) FROM invoices WHERE treatment_id = ?
            ");
            $check->execute([$treatment_id]);
            if ($check->fetchColumn() > 0) {
                die("Invoice already exists for this treatment");
            }

            $stmt = $pdo->prepare("
                SELECT 
                    p.name,
                    tp.qty,
                    tp.price,
                    (tp.qty * tp.price) AS subtotal
                FROM treatment_procedures tp
                JOIN procedures p ON tp.procedure_id = p.id
                WHERE tp.treatment_id = ?
            ");
            $stmt->execute([$treatment_id]);
            $items = $stmt->fetchAll();

            if (empty($items)) {
                die("No procedures found for this treatment");
            }

            $total = array_sum(array_column($items, 'subtotal'));
        } elseif ($mode === 'appointment') {
            $appointments = $pdo->query("
                SELECT 
                    a.id,
                    a.appointment_date,
                    a.appointment_time,
                    p.full_name AS patient_name
                FROM appointments a
                JOIN patients p ON a.patient_id = p.id
                ORDER BY a.id DESC
            ")->fetchAll();
            $procedures = $pdo->query("
                SELECT id, name, price FROM procedures ORDER BY name
            ")->fetchAll();
        } elseif ($mode === 'patient') {
            $patients = $pdo->query("
                SELECT id, full_name FROM patients ORDER BY full_name
            ")->fetchAll();
            $procedures = $pdo->query("
                SELECT id, name, price FROM procedures ORDER BY name
            ")->fetchAll();
        } else {
            die("Invoice schema not supported");
        }

        require __DIR__ . '/../../views/invoices/create.php';
    }

    // ======================
    // STORE INVOICE
    // ======================
    public function store()
    {
        $this->adminOnly();
        Permissions::require('create');

        $pdo = require __DIR__ . '/../../config/database.php';
        $mode = $this->invoiceMode($pdo);
        $pdo->beginTransaction();

        try {
            if ($mode === 'treatment') {
                if (empty($_POST['treatment_id'])) {
                    throw new Exception("Treatment is required");
                }

                $treatment_id = $_POST['treatment_id'];

                $stmt = $pdo->prepare("
                    SELECT procedure_id, qty, price
                    FROM treatment_procedures
                    WHERE treatment_id = ?
                ");
                $stmt->execute([$treatment_id]);
                $procedures = $stmt->fetchAll();

                if (empty($procedures)) {
                    throw new Exception("No procedures found");
                }

                $stmt = $pdo->prepare("
                    INSERT INTO invoices (treatment_id, total)
                    VALUES (?, 0)
                ");
                $stmt->execute([$treatment_id]);
            } else {
                if (empty($_POST['procedure_id']) || empty($_POST['qty'])) {
                    throw new Exception("Procedure and quantity are required");
                }

                $procedure_id = (int)$_POST['procedure_id'];
                $qty = (int)$_POST['qty'];
                if ($qty <= 0) {
                    throw new Exception("Quantity must be at least 1");
                }

                if ($mode === 'patient') {
                    if (empty($_POST['patient_id'])) {
                        throw new Exception("Patient is required");
                    }
                    $stmt = $pdo->prepare("
                        INSERT INTO invoices (patient_id, total)
                        VALUES (?, 0)
                    ");
                    $stmt->execute([$_POST['patient_id']]);
                } elseif ($mode === 'appointment') {
                    if (empty($_POST['appointment_id'])) {
                        throw new Exception("Appointment is required");
                    }
                    $stmt = $pdo->prepare("
                        INSERT INTO invoices (appointment_id, total)
                        VALUES (?, 0)
                    ");
                    $stmt->execute([$_POST['appointment_id']]);
                } else {
                    throw new Exception("Invoice schema not supported");
                }

                $procedures = [[
                    'procedure_id' => $procedure_id,
                    'qty' => $qty
                ]];
            }

            $invoice_id = $pdo->lastInsertId();

            $total = 0;
            $itemStmt = $pdo->prepare("
                INSERT INTO invoice_items
                (invoice_id, procedure_id, qty, price, subtotal)
                VALUES (?, ?, ?, ?, ?)
            ");

            foreach ($procedures as $p) {
                $priceStmt = $pdo->prepare("SELECT price FROM procedures WHERE id = ?");
                $priceStmt->execute([$p['procedure_id']]);
                $price = (float)$priceStmt->fetchColumn();
                $subtotal = $p['qty'] * $price;
                $total += $subtotal;

                $itemStmt->execute([
                    $invoice_id,
                    $p['procedure_id'],
                    $p['qty'],
                    $price,
                    $subtotal
                ]);
            }

            $pdo->prepare("
                UPDATE invoices SET total = ? WHERE id = ?
            ")->execute([$total, $invoice_id]);

            $pdo->commit();

            $_SESSION['flash_success'] = 'Invoice created successfully.';
            header("Location: /dental-management-system/public/invoices/show?id=" . $invoice_id);
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['flash_error'] = 'Invoice failed: ' . $e->getMessage();
            header("Location: /dental-management-system/public/invoices");
            exit;
        }
    }

    // ======================
    // SHOW INVOICE DETAILS
    // ======================
    public function show()
    {
        $this->adminOnly();
        Permissions::require('read');

        $id = $_GET['id'] ?? null;
        if (!$id) {
            die("Invoice not found");
        }

        $pdo = require __DIR__ . '/../../config/database.php';
        $mode = $this->invoiceMode($pdo);

        if ($mode === 'treatment') {
            $stmt = $pdo->prepare("
                SELECT 
                    i.id,
                    i.total,
                    i.created_at,
                    p.full_name AS patient_name
                FROM invoices i
                JOIN treatments t ON i.treatment_id = t.id
                JOIN appointments a ON t.appointment_id = a.id
                JOIN patients p ON a.patient_id = p.id
                WHERE i.id = ?
            ");
            $stmt->execute([$id]);
            $invoice = $stmt->fetch();
        } elseif ($mode === 'appointment') {
            $stmt = $pdo->prepare("
                SELECT 
                    i.id,
                    i.total,
                    i.created_at,
                    p.full_name AS patient_name
                FROM invoices i
                JOIN appointments a ON i.appointment_id = a.id
                JOIN patients p ON a.patient_id = p.id
                WHERE i.id = ?
            ");
            $stmt->execute([$id]);
            $invoice = $stmt->fetch();
        } elseif ($mode === 'patient') {
            $stmt = $pdo->prepare("
                SELECT 
                    i.id,
                    i.total,
                    i.created_at,
                    p.full_name AS patient_name
                FROM invoices i
                JOIN patients p ON i.patient_id = p.id
                WHERE i.id = ?
            ");
            $stmt->execute([$id]);
            $invoice = $stmt->fetch();
        } else {
            $invoice = null;
        }

        if (!$invoice) {
            die("Invoice not found");
        }

        $stmt = $pdo->prepare("
            SELECT 
                pr.name,
                ii.qty,
                ii.price,
                ii.subtotal
            FROM invoice_items ii
            JOIN procedures pr ON ii.procedure_id = pr.id
            WHERE ii.invoice_id = ?
        ");
        $stmt->execute([$id]);
        $items = $stmt->fetchAll();

        require __DIR__ . '/../../views/invoices/show.php';
    }

    // ======================
    // EDIT INVOICE
    // ======================
    public function edit()
    {
        $this->adminOnly();
        Permissions::require('update');

        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['flash_error'] = 'Invoice not found.';
            header("Location: /dental-management-system/public/invoices");
            exit;
        }

        $pdo = require __DIR__ . '/../../config/database.php';
        $mode = $this->invoiceMode($pdo);
        $cols = $this->invoiceColumns($pdo);
        $hasStatus = in_array('status', $cols, true);

        // Build SELECT list depending on whether `status` column exists
        $statusSelect = $hasStatus ? "i.status," : "";

        if ($mode === 'treatment') {
            $sql = "SELECT i.id, i.total, i.created_at, " . $statusSelect . " p.full_name AS patient_name
                FROM invoices i
                JOIN treatments t ON i.treatment_id = t.id
                JOIN appointments a ON t.appointment_id = a.id
                JOIN patients p ON a.patient_id = p.id
                WHERE i.id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
            $invoice = $stmt->fetch();
        } elseif ($mode === 'appointment') {
            $sql = "SELECT i.id, i.total, i.created_at, " . $statusSelect . " p.full_name AS patient_name
                FROM invoices i
                JOIN appointments a ON i.appointment_id = a.id
                JOIN patients p ON a.patient_id = p.id
                WHERE i.id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
            $invoice = $stmt->fetch();
        } elseif ($mode === 'patient') {
            $sql = "SELECT i.id, i.total, i.created_at, " . $statusSelect . " p.full_name AS patient_name
                FROM invoices i
                JOIN patients p ON i.patient_id = p.id
                WHERE i.id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
            $invoice = $stmt->fetch();
        } else {
            $invoice = null;
        }

        if (!$invoice) {
            $_SESSION['flash_error'] = 'Invoice not found.';
            header("Location: /dental-management-system/public/invoices");
            exit;
        }

        require __DIR__ . '/../../views/invoices/edit.php';
    }

    // ======================
    // UPDATE INVOICE
    // ======================
    public function update()
    {
        $this->adminOnly();
        Permissions::require('update');

        $id = $_POST['id'] ?? null;
        if (!$id) {
            $_SESSION['flash_error'] = 'Invoice ID missing.';
            header("Location: /dental-management-system/public/invoices");
            exit;
        }

        $pdo = require __DIR__ . '/../../config/database.php';
        $cols = $this->invoiceColumns($pdo);

        $fields = [];
        $params = [];

        if (isset($_POST['total'])) {
            $total = (float)$_POST['total'];
            if ($total < 0) {
                $_SESSION['flash_error'] = 'Total cannot be negative.';
                header("Location: /dental-management-system/public/invoices/edit?id=" . urlencode($id));
                exit;
            }
            $fields[] = "total = ?";
            $params[] = $total;
        }

        if (in_array('status', $cols, true) && isset($_POST['status'])) {
            $fields[] = "status = ?";
            $params[] = $_POST['status'];
        }

        if (empty($fields)) {
            $_SESSION['flash_error'] = 'No changes to save.';
            header("Location: /dental-management-system/public/invoices/edit?id=" . urlencode($id));
            exit;
        }

        $params[] = $id;
        $sql = "UPDATE invoices SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $_SESSION['flash_success'] = 'Invoice updated successfully.';
        header("Location: /dental-management-system/public/invoices");
        exit;
    }

    // ======================
    // LIVE SEARCH INVOICES (JSON)
    // ======================
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
        $mode = $this->invoiceMode($pdo);

        $isId = ctype_digit($q);
        $like = '%' . $q . '%';

        if ($mode === 'treatment') {
            $sql = "
                SELECT 
                    i.id,
                    i.total,
                    i.created_at,
                    p.full_name AS patient_name
                FROM invoices i
                JOIN treatments t ON i.treatment_id = t.id
                JOIN appointments a ON t.appointment_id = a.id
                JOIN patients p ON a.patient_id = p.id
                WHERE " . ($isId ? "i.id = ?" : "p.full_name LIKE ?") . "
                ORDER BY i.id DESC
                LIMIT 10
            ";
        } elseif ($mode === 'appointment') {
            $sql = "
                SELECT 
                    i.id,
                    i.total,
                    i.created_at,
                    p.full_name AS patient_name
                FROM invoices i
                JOIN appointments a ON i.appointment_id = a.id
                JOIN patients p ON a.patient_id = p.id
                WHERE " . ($isId ? "i.id = ?" : "p.full_name LIKE ?") . "
                ORDER BY i.id DESC
                LIMIT 10
            ";
        } else {
            $sql = "
                SELECT 
                    i.id,
                    i.total,
                    i.created_at,
                    p.full_name AS patient_name
                FROM invoices i
                JOIN patients p ON i.patient_id = p.id
                WHERE " . ($isId ? "i.id = ?" : "p.full_name LIKE ?") . "
                ORDER BY i.id DESC
                LIMIT 10
            ";
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$isId ? $q : $like]);
        $rows = $stmt->fetchAll();

        $results = [];
        foreach ($rows as $row) {
            $results[] = [
                'id' => (int)$row['id'],
                'patient_name' => $row['patient_name'],
                'total' => (float)$row['total'],
                'created_at' => $row['created_at']
            ];
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($results);
    }

    // ======================
    // DELETE INVOICE
    // ======================
    public function delete()
    {
        $this->adminOnly();
        Permissions::require('delete');

        $id = $_GET['id'] ?? null;
        if (!$id) {
            die("Invoice ID missing");
        }

        $pdo = require __DIR__ . '/../../config/database.php';
        $pdo->prepare("DELETE FROM invoices WHERE id = ?")->execute([$id]);

        $_SESSION['flash_success'] = 'Invoice deleted successfully.';
        header("Location: /dental-management-system/public/invoices");
        exit;
    }
}
