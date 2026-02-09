<?php
$pageTitle = 'Payments';
$pageSubtitle = 'Track payment history';
$pageActions = '<a class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-white hover:bg-blue-700" href="/dental-management-system/public/payments/create">Add Payment</a>';
require __DIR__ . '/../layouts/app_start.php';
?>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-slate-500">
                <tr>
                    <th class="px-5 py-3 text-left">ID</th>
                    <th class="px-5 py-3 text-left">Invoice</th>
                    <th class="px-5 py-3 text-left">Amount</th>
                    <th class="px-5 py-3 text-left">Method</th>
                    <th class="px-5 py-3 text-left">Status</th>
                    <th class="px-5 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (empty($payments)): ?>
                    <tr>
                        <td class="px-5 py-4 text-slate-500" colspan="6">No payments found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($payments as $p): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-4 font-semibold text-slate-800">#<?= (int)$p['id'] ?></td>
                            <td class="px-5 py-4 text-slate-700">#<?= (int)$p['invoice_id'] ?></td>
                            <td class="px-5 py-4 text-slate-700">$<?= number_format((float)$p['amount'], 2) ?></td>
                            <td class="px-5 py-4 text-slate-700"><?= htmlspecialchars($p['method']) ?></td>
                            <td class="px-5 py-4 text-slate-500">
                                <?php
                                $st = null;
                                if (isset($p['invoice_status']) && $p['invoice_status'] !== null && $p['invoice_status'] !== '') {
                                    $st = $p['invoice_status'];
                                } else {
                                    $st = !empty($p['paid_at']) ? 'paid' : 'unpaid';
                                }
                                $st = strtolower($st);
                                $badgeClass = 'bg-slate-100 text-slate-700';
                                if ($st === 'paid') $badgeClass = 'bg-emerald-100 text-emerald-800';
                                if ($st === 'partial') $badgeClass = 'bg-amber-100 text-amber-800';
                                if ($st === 'unpaid') $badgeClass = 'bg-rose-100 text-rose-800';
                                ?>
                                <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm <?= $badgeClass ?>"><?= htmlspecialchars(ucfirst($st)) ?></span>
                            </td>
                            <td class="px-5 py-4 flex items-center gap-2">
                                <a class="px-3 py-1.5 rounded-lg bg-amber-50 text-amber-700 hover:bg-amber-100"
                                   href="/dental-management-system/public/payments/edit?id=<?= (int)$p['id'] ?>">
                                    Edit
                                </a>
                                <a class="px-3 py-1.5 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100"
                                   href="/dental-management-system/public/payments/delete?id=<?= (int)$p['id'] ?>"
                                   data-confirm="Delete this payment?">
                                    Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../layouts/app_end.php'; ?>
