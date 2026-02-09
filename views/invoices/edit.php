<?php
$pageTitle = 'Edit Invoice';
$pageSubtitle = 'Invoice #' . (int)$invoice['id'];
require __DIR__ . '/../layouts/app_start.php';
?>

<form method="POST" action="/dental-management-system/public/invoices/update" class="max-w-3xl">
    <input type="hidden" name="id" value="<?= (int)$invoice['id'] ?>">

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="md:col-span-2">
            <label class="text-sm text-slate-600">Patient</label>
            <input type="text" value="<?= htmlspecialchars($invoice['patient_name']) ?>" readonly
                   class="mt-2 w-full rounded-xl border-slate-200 bg-slate-50 text-slate-600">
        </div>

        <div>
            <label class="text-sm text-slate-600">Total</label>
            <input type="number" step="0.01" min="0" name="total" required
                   value="<?= htmlspecialchars($invoice['total']) ?>"
                   class="mt-2 w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
        </div>

        <?php if (!empty($hasStatus)): ?>
            <div>
                <label class="text-sm text-slate-600">Status</label>
                <select name="status"
                        class="mt-2 w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                    <option value="unpaid" <?= ($invoice['status'] ?? '') === 'unpaid' ? 'selected' : '' ?>>Unpaid</option>
                    <option value="partial" <?= ($invoice['status'] ?? '') === 'partial' ? 'selected' : '' ?>>Partial</option>
                    <option value="paid" <?= ($invoice['status'] ?? '') === 'paid' ? 'selected' : '' ?>>Paid</option>
                </select>
            </div>
        <?php endif; ?>
    </div>

    <div class="mt-4 flex gap-2">
        <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
            Update Invoice
        </button>
        <a href="/dental-management-system/public/invoices"
           class="rounded-xl bg-slate-100 px-4 py-2 text-slate-700 hover:bg-slate-200">
            Back
        </a>
    </div>
</form>

<?php require __DIR__ . '/../layouts/app_end.php'; ?>
