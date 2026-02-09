<?php
$pageTitle = 'Add Procedures';
$pageSubtitle = 'Patient: ' . ($treatment['patient_name'] ?? '');
require __DIR__ . '/../layouts/app_start.php';
?>

<form method="post" action="/dental-management-system/public/treatments/procedures/store" class="max-w-4xl">
    <input type="hidden" name="treatment_id" value="<?= (int)$treatment['id'] ?>">

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="px-4 py-3 text-left">Select</th>
                        <th class="px-4 py-3 text-left">Procedure</th>
                        <th class="px-4 py-3 text-left">Price</th>
                        <th class="px-4 py-3 text-left">Qty</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($procedures)): ?>
                        <tr>
                            <td class="px-4 py-4 text-slate-500" colspan="4">No procedures found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($procedures as $p): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3">
                                    <input type="checkbox"
                                           name="procedures[<?= (int)$p['id'] ?>][checked]"
                                           value="1"
                                           class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                </td>
                                <td class="px-4 py-3 text-slate-700"><?= htmlspecialchars($p['name']) ?></td>
                                <td class="px-4 py-3 text-slate-700">$<?= number_format((float)$p['price'], 2) ?></td>
                                <td class="px-4 py-3">
                                    <input type="number"
                                           name="procedures[<?= (int)$p['id'] ?>][qty]"
                                           value="1"
                                           min="1"
                                           class="w-24 rounded-lg border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                                    <input type="hidden"
                                           name="procedures[<?= (int)$p['id'] ?>][price]"
                                           value="<?= htmlspecialchars($p['price']) ?>">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4 flex gap-2">
        <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
            Save Procedures
        </button>
        <a href="/dental-management-system/public/treatments"
           class="rounded-xl bg-slate-100 px-4 py-2 text-slate-700 hover:bg-slate-200">
            Back
        </a>
    </div>
</form>

<?php require __DIR__ . '/../layouts/app_end.php'; ?>
