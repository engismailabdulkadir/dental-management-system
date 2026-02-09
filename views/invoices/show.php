<?php
$pageTitle = 'Invoice Details';
$pageSubtitle = 'Invoice #' . (int)$invoice['id'];
require __DIR__ . '/../layouts/app_start.php';
?>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
    <div class="flex flex-col gap-1">
        <div class="text-sm text-slate-500">Patient</div>
        <div class="text-lg font-semibold text-slate-800"><?= htmlspecialchars($invoice['patient_name']) ?></div>
        <div class="text-sm text-slate-500">Date: <?= htmlspecialchars($invoice['created_at']) ?></div>
    </div>

    <div class="mt-6 overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-slate-500">
                <tr>
                    <th class="px-4 py-3 text-left">Procedure</th>
                    <th class="px-4 py-3 text-left">Qty</th>
                    <th class="px-4 py-3 text-left">Price</th>
                    <th class="px-4 py-3 text-left">Subtotal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($items as $it): ?>
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-slate-700"><?= htmlspecialchars($it['name']) ?></td>
                        <td class="px-4 py-3 text-slate-700"><?= (int)$it['qty'] ?></td>
                        <td class="px-4 py-3 text-slate-700">$<?= number_format((float)$it['price'], 2) ?></td>
                        <td class="px-4 py-3 text-slate-700">$<?= number_format((float)$it['subtotal'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-6 flex items-center justify-between">
        <div class="text-lg font-semibold text-slate-800">Total: $<?= number_format((float)$invoice['total'], 2) ?></div>
        <a href="/dental-management-system/public/invoices"
           class="rounded-xl bg-slate-100 px-4 py-2 text-slate-700 hover:bg-slate-200">
            Back to Invoices
        </a>
    </div>
</div>

<?php require __DIR__ . '/../layouts/app_end.php'; ?>
