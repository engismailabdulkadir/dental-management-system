<?php
$pageTitle = 'Edit Payment';
$pageSubtitle = 'Update payment details';
require __DIR__ . '/../layouts/app_start.php';

// $payment is provided by controller
?>

<form method="POST" action="/dental-management-system/public/payments/update" class="max-w-3xl">
    <input type="hidden" name="id" value="<?= (int)$payment['id'] ?>">

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="md:col-span-2">
            <label class="text-sm text-slate-600">Invoice</label>
            <div class="mt-2 w-full rounded-xl border-slate-200 p-3 bg-slate-50 flex items-center justify-between">
                <div>Invoice #<?= (int)$payment['invoice_id'] ?> (Total: $<?= number_format((float)$payment['total'],2) ?>)</div>
                <div>
                    <?php
                    $invStatus = $payment['invoice_status'] ?? null;
                    if ($invStatus === null) $invStatus = 'unknown';
                    $s = strtolower($invStatus);
                    $badgeClass = 'bg-slate-100 text-slate-700';
                    if ($s === 'paid') $badgeClass = 'bg-emerald-100 text-emerald-800';
                    if ($s === 'partial') $badgeClass = 'bg-amber-100 text-amber-800';
                    if ($s === 'unpaid') $badgeClass = 'bg-rose-100 text-rose-800';
                    ?>
                    <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm <?= $badgeClass ?>"><?= htmlspecialchars(ucfirst($s)) ?></span>
                </div>
            </div>
        </div>

        <div>
            <label class="text-sm text-slate-600">Amount</label>
            <input id="amountInput" type="number" name="amount" step="0.01" min="0" required value="<?= number_format((float)$payment['amount'],2) ?>"
                   max="<?= number_format($remaining,2) ?>"
                   class="mt-2 w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
            <div id="remainingInfo" class="mt-2 text-sm text-slate-600">Remaining available for this invoice: $<?= number_format($remaining,2) ?></div>
        </div>

        <div>
            <label class="text-sm text-slate-600">Payment Method</label>
            <select name="method" required class="mt-2 w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                <option value="">Select Method</option>
                <option value="cash" <?= $payment['method']==='cash' ? 'selected' : '' ?>>Cash</option>
                <option value="card" <?= $payment['method']==='card' ? 'selected' : '' ?>>Card</option>
                <option value="mobile" <?= $payment['method']==='mobile' ? 'selected' : '' ?>>Mobile Money</option>
            </select>
        </div>
        
        <div>
            <label class="text-sm text-slate-600">Invoice Status (Paid / Unpaid)</label>
            <select name="invoice_status" id="invoiceStatusEdit" required class="mt-2 w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                <option value="paid" <?= (isset($payment['invoice_status']) && $payment['invoice_status']==='paid') ? 'selected' : '' ?>>Paid</option>
                <option value="unpaid" <?= (isset($payment['invoice_status']) && $payment['invoice_status']==='unpaid') ? 'selected' : '' ?>>Unpaid</option>
            </select>
        </div>
    </div>

    <div class="mt-4 flex gap-2">
        <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">Update Payment</button>
        <a href="/dental-management-system/public/payments" class="rounded-xl bg-slate-100 px-4 py-2 text-slate-700 hover:bg-slate-200">Back</a>
    </div>
</form>

<?php require __DIR__ . '/../layouts/app_end.php'; ?>
<script>
    (function(){
        const amountInput = document.getElementById('amountInput');
        const remaining = parseFloat('<?= number_format($remaining,2) ?>');
        if (amountInput) {
            amountInput.addEventListener('input', function(){
                const val = parseFloat(this.value) || 0;
                if (val > remaining) {
                    this.value = remaining.toFixed(2);
                }
            });
        }
    })();
</script>

<?php if (!empty($changes)): ?>
    <div class="max-w-3xl mt-6">
        <h3 class="text-lg font-semibold">Change History</h3>
        <div class="mt-3 space-y-3">
            <?php foreach ($changes as $c): ?>
                <?php $ch = json_decode($c['changes'], true); ?>
                <div class="border rounded-xl p-3 bg-white">
                    <div class="text-sm text-slate-500">On <?= htmlspecialchars($c['created_at']) ?> by <?= htmlspecialchars($c['changed_by'] ?? 'System') ?></div>
                    <div class="mt-2 text-sm">
                        <?php foreach ($ch as $field => $vals): ?>
                            <div><strong><?= htmlspecialchars($field) ?>:</strong>
                                <span class="text-slate-700"><?= htmlspecialchars($vals['old'] ?? '-') ?></span>
                                →
                                <span class="text-slate-800 font-semibold"><?= htmlspecialchars($vals['new'] ?? '-') ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>
