<?php
$pageTitle = 'Procedures';
$pageSubtitle = 'Manage dental procedures';
$pageActions = '<a class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-white hover:bg-blue-700" href="/dental-management-system/public/procedures/create">Add Procedure</a>';
require __DIR__ . '/../layouts/app_start.php';
?>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-slate-500">
                <tr>
                    <th class="px-5 py-3 text-left">ID</th>
                    <th class="px-5 py-3 text-left">Name</th>
                    <th class="px-5 py-3 text-left">Price</th>
                    <th class="px-5 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (empty($procedures)): ?>
                    <tr>
                        <td class="px-5 py-4 text-slate-500" colspan="4">No procedures found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($procedures as $p): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-4 font-semibold text-slate-800"><?= (int)$p['id'] ?></td>
                            <td class="px-5 py-4 text-slate-700"><?= htmlspecialchars($p['name']) ?></td>
                            <td class="px-5 py-4 text-slate-700">$<?= number_format(max(0, (float)$p['price']), 2) ?></td>
                            <td class="px-5 py-4">
                                <div class="flex gap-2">
                                    <a class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200"
                                       href="/dental-management-system/public/procedures/edit?id=<?= (int)$p['id'] ?>">
                                        Edit
                                    </a>
                                    <a class="px-3 py-1.5 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100"
                                       href="/dental-management-system/public/procedures/delete?id=<?= (int)$p['id'] ?>"
                                       data-confirm="Delete this procedure?">
                                        Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../layouts/app_end.php'; ?>
