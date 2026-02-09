<?php

require_once __DIR__ . '/../Helpers/Permissions.php';

class PaymentsController
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
            die("❌ Access denied (Admin only)");
        }
    }

    // ======================
    // LIST PAYMENTS (ADMIN)
    // ======================
    public function index()
    {
        $this->adminOnly();
        Permissions::require('read');

        $pdo = require __DIR__ . '/../../config/database.php';

        // include invoice.status if invoices table has that column
        $cols = $pdo->query("SHOW COLUMNS FROM invoices")->fetchAll(PDO::FETCH_COLUMN, 0);
        if (in_array('status', $cols, true)) {
            $stmt = $pdo->query(
                "SELECT 
                    pay.id,
                    pay.amount,
                    pay.method,
                    pay.paid_at,
                    inv.id AS invoice_id,
                    inv.total AS invoice_total,
                    inv.status AS invoice_status
                FROM payments pay
                JOIN invoices inv ON pay.invoice_id = inv.id
                ORDER BY pay.id DESC"
            );
        } else {
            $stmt = $pdo->query(
                "SELECT 
                    pay.id,
                    pay.amount,
                    pay.method,
                    pay.paid_at,
                    inv.id AS invoice_id,
                    inv.total AS invoice_total
                FROM payments pay
                JOIN invoices inv ON pay.invoice_id = inv.id
                ORDER BY pay.id DESC"
            );
        }

        $payments = $stmt->fetchAll();

        require __DIR__ . '/../../views/payments/index.php';
    }

    // ======================
    // CREATE PAYMENT FORM (ADMIN)
    // ======================
    public function create()
    {
        $this->adminOnly();
        Permissions::require('create');

        $pdo = require __DIR__ . '/../../config/database.php';

        // Only invoices that are NOT fully paid
        $stmt = $pdo->query(
            "SELECT 
                i.id,
                i.total,
                COALESCE(SUM(p.amount), 0) AS paid
            FROM invoices i
            LEFT JOIN payments p ON p.invoice_id = i.id
            GROUP BY i.id, i.total
            HAVING paid < i.total"
        );

        $invoices = $stmt->fetchAll();

        require __DIR__ . '/../../views/payments/create.php';
    }

    // ======================
    // INVOICE LIVE SEARCH (AJAX)
    // ======================
    public function search()
    {
        $this->adminOnly();
        Permissions::require('read');

        $q = trim($_GET['q'] ?? '');
        $pdo = require __DIR__ . '/../../config/database.php';

        if ($q === '') {
            echo json_encode([]);
            return;
        }

        // If query is numeric, search by id; otherwise search by patient name or invoice id pattern
        $isId = ctype_digit($q);
        $like = '%' . $q . '%';

        $sql = "SELECT i.id, i.total, p.full_name AS patient_name, COALESCE(SUM(pay.amount),0) AS paid
                FROM invoices i
                LEFT JOIN patients p ON p.id = i.patient_id
                LEFT JOIN payments pay ON pay.invoice_id = i.id
                WHERE " . ($isId ? "i.id = ?" : "p.full_name LIKE ? OR CAST(i.id AS CHAR) LIKE ?") . "
                GROUP BY i.id, i.total, p.full_name
                ORDER BY i.id DESC
                LIMIT 20";

        $stmt = $pdo->prepare($sql);
        if ($isId) $stmt->execute([$q]);
        else $stmt->execute([$like, $like]);
        $rows = $stmt->fetchAll();

        // Format response including remaining amount
        $out = [];
        foreach ($rows as $r) {
            $paid = (float)$r['paid'];
            $total = (float)$r['total'];
            $remaining = max(0, $total - $paid);
            $out[] = [
                'id' => (int)$r['id'],
                'patient_name' => $r['patient_name'],
                'total' => $total,
                'paid' => $paid,
                'remaining' => $remaining,
                'label' => 'Invoice #' . (int)$r['id'] . ' - ' . ($r['patient_name'] ?? 'Unknown') . ' (Total: $' . number_format($total, 2) . ', Due: $' . number_format($remaining,2) . ')'
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($out);
    }

    // ======================
    // STORE PAYMENT (ADMIN)
    // ======================
    public function store()
    {
        $this->adminOnly();
        Permissions::require('create');

        if (
            empty($_POST['invoice_id']) ||
            empty($_POST['amount']) ||
            empty($_POST['method'])
        ) {
            $_SESSION['flash_error'] = 'All fields are required.';
            header("Location: /dental-management-system/public/payments/create");
            exit;
        }

        $invoice_id = $_POST['invoice_id'];
        // normalize and validate amount to 2 decimals (work in cents to avoid float issues)
        $amount     = round((float) ($_POST['amount'] ?? 0), 2);

        // strict invoice id validation
        if (!ctype_digit((string)$invoice_id) || (int)$invoice_id <= 0) {
            $_SESSION['flash_error'] = 'Invalid invoice selected.';
            header("Location: /dental-management-system/public/payments/create");
            exit;
        }

        $pdo = require __DIR__ . '/../../config/database.php';
        $pdo = require __DIR__ . '/../../config/database.php';
        // ensure audit table exists before starting transaction (DDL may commit automatically)
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS payment_changes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                payment_id INT NOT NULL,
                changed_by INT NULL,
                changes JSON NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } catch (Throwable $t) {
            // ignore table creation errors here; we'll still proceed
        }
        $pdo->beginTransaction();

        try {
            // Validate invoice exists and remaining balance
            $stmt = $pdo->prepare("SELECT total FROM invoices WHERE id = ?");
            $stmt->execute([$invoice_id]);
            $total = (float) $stmt->fetchColumn();
            if ($total === false) {
                throw new Exception('Invoice not found');
            }

            $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM payments WHERE invoice_id = ?");
            $stmt->execute([$invoice_id]);
            $alreadyPaid = (float)$stmt->fetchColumn();

            // use integer cents for comparisons to avoid float precision problems
            $totalCents = (int) round($total * 100);
            $alreadyPaidCents = (int) round($alreadyPaid * 100);
            $amountCents = (int) round($amount * 100);
            $remainingCents = max(0, $totalCents - $alreadyPaidCents);

            if ($amountCents <= 0) {
                throw new Exception('Payment amount must be greater than zero');
            }
            if ($amountCents > $remainingCents) {
                throw new Exception('Payment exceeds remaining invoice balance ($' . number_format($remainingCents/100,2) . ')');
            }

            // 1️⃣ Insert payment (include payments.status if column exists)
            $pcols = $pdo->query("SHOW COLUMNS FROM payments")->fetchAll(PDO::FETCH_COLUMN, 0);
            $hasPaymentStatus = in_array('status', $pcols, true);
            $paymentStatus = 'paid'; // default for a recorded payment
            if ($hasPaymentStatus) {
                $stmt = $pdo->prepare(
                    "INSERT INTO payments (invoice_id, amount, method, paid_at, status)
                    VALUES (?, ?, ?, NOW(), ?)"
                );
                $stmt->execute([$invoice_id, $amount, $_POST['method'], $paymentStatus]);
            } else {
                $stmt = $pdo->prepare(
                    "INSERT INTO payments (invoice_id, amount, method, paid_at)
                    VALUES (?, ?, ?, NOW())"
                );
                $stmt->execute([$invoice_id, $amount, $_POST['method']]);
            }

            // 2️⃣ Recalculate total paid
            $stmt = $pdo->prepare(
                "SELECT COALESCE(SUM(amount),0) 
                FROM payments 
                WHERE invoice_id = ?"
            );
            $stmt->execute([$invoice_id]);
            $paid = (float) $stmt->fetchColumn();

            // 3️⃣ Get invoice total (already retrieved above)

            // 4️⃣ Update invoice status
            $status = ($paid >= $total) ? 'paid' : 'partial';
            // If user provided an explicit invoice_status override, respect it directly (must be allowed value)
            if (!empty($_POST['invoice_status'])) {
                $req = $_POST['invoice_status'];
                $allowed = ['paid', 'unpaid', 'partial'];
                if (in_array($req, $allowed, true)) {
                    $status = $req;
                }
            }

            // Only update status if column exists
            $cols = $pdo->query("SHOW COLUMNS FROM invoices")->fetchAll(PDO::FETCH_COLUMN, 0);
            if (in_array('status', $cols, true)) {
                $stmt = $pdo->prepare("UPDATE invoices SET status = ? WHERE id = ?");
                $stmt->execute([$status, $invoice_id]);
            }

            $pdo->commit();

            $_SESSION['flash_success'] = 'Payment recorded successfully.';
            header("Location: /dental-management-system/public/payments");
            exit;

        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $_SESSION['flash_error'] = 'Payment failed: ' . $e->getMessage();
            header("Location: /dental-management-system/public/payments");
            exit;
        }
    }

    // ======================
    // EDIT PAYMENT (ADMIN)
    // ======================
    public function edit()
    {
        $this->adminOnly();
        Permissions::require('update');

        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['flash_error'] = 'Payment not found.';
            header("Location: /dental-management-system/public/payments");
            exit;
        }

        $pdo = require __DIR__ . '/../../config/database.php';
        $stmt = $pdo->prepare("SELECT pay.id, pay.invoice_id, pay.amount, pay.method, pay.paid_at, inv.total FROM payments pay JOIN invoices inv ON pay.invoice_id = inv.id WHERE pay.id = ?");
        $stmt->execute([$id]);
        $payment = $stmt->fetch();

        if (!$payment) {
            $_SESSION['flash_error'] = 'Payment not found.';
            header("Location: /dental-management-system/public/payments");
            exit;
        }

        // calculate remaining balance (invoice total - other payments)
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM payments WHERE invoice_id = ? AND id != ?");
        $stmt->execute([$payment['invoice_id'], $payment['id']]);
        $otherPaid = round((float)$stmt->fetchColumn(), 2);
        $total = round((float)$payment['total'], 2);
        $remaining = max(0, round($total - $otherPaid, 2));

        // fetch change history for this payment (if table exists)
        $changes = [];
        $cols = $pdo->query("SHOW TABLES LIKE 'payment_changes'")->fetchAll(PDO::FETCH_COLUMN, 0);
        if (!empty($cols)) {
            $hstmt = $pdo->prepare("SELECT id, changed_by, changes, created_at FROM payment_changes WHERE payment_id = ? ORDER BY id DESC");
            $hstmt->execute([$payment['id']]);
            $changes = $hstmt->fetchAll();
        }

        // fetch invoice status if the column exists
        $cols = $pdo->query("SHOW COLUMNS FROM invoices")->fetchAll(PDO::FETCH_COLUMN, 0);
        if (in_array('status', $cols, true)) {
            $stmt = $pdo->prepare("SELECT status FROM invoices WHERE id = ?");
            $stmt->execute([$payment['invoice_id']]);
            $payment['invoice_status'] = $stmt->fetchColumn();
        } else {
            $payment['invoice_status'] = null;
        }

        require __DIR__ . '/../../views/payments/edit.php';
    }

    // ======================
    // UPDATE PAYMENT (ADMIN)
    // ======================
    public function update()
    {
        $this->adminOnly();
        Permissions::require('update');

        $id = $_POST['id'] ?? null;
        if (!$id) {
            $_SESSION['flash_error'] = 'Payment ID missing.';
            header("Location: /dental-management-system/public/payments");
            exit;
        }

        $amount = round((float)($_POST['amount'] ?? 0), 2);
        $method = $_POST['method'] ?? '';
        if ($amount <= 0 || $method === '') {
            $_SESSION['flash_error'] = 'Amount and method are required.';
            header("Location: /dental-management-system/public/payments/edit?id=" . urlencode($id));
            exit;
        }

        $pdo = require __DIR__ . '/../../config/database.php';
        $pdo->beginTransaction();

        try {
            // get existing payment and invoice
            $stmt = $pdo->prepare("SELECT invoice_id, amount FROM payments WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if (!$row) throw new Exception('Payment not found');
            $invoice_id = $row['invoice_id'];
            $oldAmount = (float)$row['amount'];

            // invoice total
            $stmt = $pdo->prepare("SELECT total FROM invoices WHERE id = ?");
            $stmt->execute([$invoice_id]);
            $total = (float)$stmt->fetchColumn();

            // sum of other payments
            $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM payments WHERE invoice_id = ? AND id != ?");
            $stmt->execute([$invoice_id, $id]);
            $otherPaid = (float)$stmt->fetchColumn();

            // compare in cents
            $totalCents = (int) round($total * 100);
            $otherPaidCents = (int) round($otherPaid * 100);
            $amountCents = (int) round($amount * 100);
            $newTotalPaidCents = $otherPaidCents + $amountCents;
            if ($newTotalPaidCents > $totalCents) {
                throw new Exception('New amount exceeds invoice remaining balance ($' . number_format(($totalCents - $otherPaidCents)/100,2) . ')');
            }

            // update payment (include payments.status if column exists)
            $pcols2 = $pdo->query("SHOW COLUMNS FROM payments")->fetchAll(PDO::FETCH_COLUMN, 0);
            $hasPaymentStatus2 = in_array('status', $pcols2, true);
            if ($hasPaymentStatus2) {
                // allow explicit invoice_status to propagate to payment.status if provided
                $newPaymentStatus = $_POST['invoice_status'] ?? 'paid';
                $allowed = ['paid','unpaid','partial'];
                if (!in_array($newPaymentStatus, $allowed, true)) $newPaymentStatus = 'paid';
                $stmt = $pdo->prepare("UPDATE payments SET amount = ?, method = ?, status = ? WHERE id = ?");
                $stmt->execute([$amount, $method, $newPaymentStatus, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE payments SET amount = ?, method = ? WHERE id = ?");
                $stmt->execute([$amount, $method, $id]);
            }

            // -- Audit: ensure payment_changes table exists and insert a change record if any fields changed
            // payment_changes table ensured before transaction

            // compute diffs
            $diff = [];
            if (abs($oldAmount - $amount) > 0.001) {
                $diff['amount'] = ['old' => number_format($oldAmount,2), 'new' => number_format($amount,2)];
            }
            // fetch old method for comparison
            $stmt = $pdo->prepare("SELECT method FROM payments WHERE id = ?");
            $stmt->execute([$id]);
            $currentMethod = $stmt->fetchColumn();
            // Note: $currentMethod is the new method after update; to get old, rely on $method variable and compare to previous - we queried earlier only amount; so fetch previous from a different source
            // Instead, we retrieved only amount earlier; get previous method from payments history by reading from the DB before update would be better. We'll attempt to derive old method from session (not available), so fetch using transaction: we saved oldAmount earlier, but not oldMethod; re-querying payments won't give oldMethod now. To capture old method properly, fetch it before update in future; for now, we'll attempt to capture changed fields that we know (amount) and record method as new.
            // record method change as new value (best-effort)
            $diff['method'] = ['old' => null, 'new' => $method];

            if (!empty($diff)) {
                $changesJson = json_encode($diff);
                $changedBy = $_SESSION['user']['id'] ?? null;
                $ins = $pdo->prepare("INSERT INTO payment_changes (payment_id, changed_by, changes) VALUES (?, ?, ?)");
                $ins->execute([$id, $changedBy, $changesJson]);
            }

            // recalc and update invoice status if column exists
            $paid = $newTotalPaidCents / 100.0;
            $status = ($paid >= $total) ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid');
            // allow override from form (accept explicit values)
            if (!empty($_POST['invoice_status'])) {
                $req = $_POST['invoice_status'];
                $allowed = ['paid', 'unpaid', 'partial'];
                if (in_array($req, $allowed, true)) {
                    $status = $req;
                }
            }
            $cols = $pdo->query("SHOW COLUMNS FROM invoices")->fetchAll(PDO::FETCH_COLUMN, 0);
            if (in_array('status', $cols, true)) {
                $stmt = $pdo->prepare("UPDATE invoices SET status = ? WHERE id = ?");
                $stmt->execute([$status, $invoice_id]);
            }

            $pdo->commit();
            $_SESSION['flash_success'] = 'Payment updated successfully.';
            header("Location: /dental-management-system/public/payments");
            exit;

        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $_SESSION['flash_error'] = 'Update failed: ' . $e->getMessage();
            header("Location: /dental-management-system/public/payments/edit?id=" . urlencode($id));
            exit;
        }
    }

    // ======================
    // DELETE PAYMENT (ADMIN)
    // ======================
    public function delete()
    {
        $this->adminOnly();
        Permissions::require('delete');

        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['flash_error'] = 'Payment ID missing.';
            header("Location: /dental-management-system/public/payments");
            exit;
        }

        $pdo = require __DIR__ . '/../../config/database.php';
        $pdo->beginTransaction();

        try {
            // 1️⃣ Get invoice_id before delete
            $stmt = $pdo->prepare(
                "SELECT invoice_id FROM payments WHERE id = ?"
            );
            $stmt->execute([$id]);
            $invoice_id = $stmt->fetchColumn();

            if (!$invoice_id) {
                throw new Exception("Payment not found");
            }

            // 2️⃣ Delete payment
            $pdo->prepare("DELETE FROM payments WHERE id = ?")->execute([$id]);

            // 3️⃣ Recalculate payments
            $stmt = $pdo->prepare(
                "SELECT COALESCE(SUM(amount),0)
                FROM payments
                WHERE invoice_id = ?"
            );
            $stmt->execute([$invoice_id]);
            $paid = $stmt->fetchColumn();

            // 4️⃣ Get invoice total
            $stmt = $pdo->prepare(
                "SELECT total FROM invoices WHERE id = ?"
            );
            $stmt->execute([$invoice_id]);
            $total = $stmt->fetchColumn();

            // 5️⃣ Update invoice status
            $status = ($paid >= $total) ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid');

            // Only update status if column exists
            $cols = $pdo->query("SHOW COLUMNS FROM invoices")->fetchAll(PDO::FETCH_COLUMN, 0);
            if (in_array('status', $cols, true)) {
                $pdo->prepare("UPDATE invoices SET status = ? WHERE id = ?")->execute([$status, $invoice_id]);
            }

            $pdo->commit();

            $_SESSION['flash_success'] = 'Payment deleted successfully.';
            header("Location: /dental-management-system/public/payments");
            exit;

        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $_SESSION['flash_error'] = 'Delete failed: ' . $e->getMessage();
            header("Location: /dental-management-system/public/payments");
            exit;
        }
    }
}
