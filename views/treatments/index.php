<?php
$pageTitle = 'Treatments';
$pageSubtitle = 'Manage treatment records';
$pageActions = '';
if (in_array(($_SESSION['user']['role_id'] ?? 0), [1, 2], true)) {
    $pageActions = '<a class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-white hover:bg-blue-700" href="/dental-management-system/public/treatments/create">Add Treatment</a>';
}
require __DIR__ . '/../layouts/app_start.php';
?>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-slate-500">
                <tr>
                    <th class="px-5 py-3 text-left">ID</th>
                    <th class="px-5 py-3 text-left">Patient</th>
                    <?php if (($_SESSION['user']['role_id'] ?? 0) == 1): ?>
                        <th class="px-5 py-3 text-left">Doctor</th>
                    <?php endif; ?>
                    <th class="px-5 py-3 text-left">Appointment</th>
                    <th class="px-5 py-3 text-left">Notes</th>
                    <th class="px-5 py-3 text-left">Created</th>
                    <th class="px-5 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (empty($treatments)): ?>
                    <tr>
                        <td class="px-5 py-4 text-slate-500" colspan="7">No treatments found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($treatments as $t): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-4 font-semibold text-slate-800"><?= (int)$t['id'] ?></td>
                            <td class="px-5 py-4 text-slate-700"><?= htmlspecialchars($t['patient_name']) ?></td>
                            <?php if (($_SESSION['user']['role_id'] ?? 0) == 1): ?>
                                <td class="px-5 py-4 text-slate-700"><?= htmlspecialchars($t['doctor_name']) ?></td>
                            <?php endif; ?>
                            <td class="px-5 py-4 text-slate-700">
                                <?= htmlspecialchars($t['appointment_date']) ?> | <?= htmlspecialchars(substr($t['appointment_time'], 0, 5)) ?>
                            </td>
                            <td class="px-5 py-4 text-slate-700"><?= nl2br(htmlspecialchars($t['notes'])) ?></td>
                            <td class="px-5 py-4 text-slate-500"><?= htmlspecialchars($t['created_at']) ?></td>
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap gap-2">
                                    <a class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200"
                                       href="/dental-management-system/public/treatments/edit?id=<?= (int)$t['id'] ?>">
                                        Edit
                                    </a>
                                    <?php if (($_SESSION['user']['role_id'] ?? 0) == 2): ?>
                                        <a class="px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100"
                                           href="/dental-management-system/public/treatments/procedures?treatment_id=<?= (int)$t['id'] ?>">
                                            Add Procedures
                                        </a>
                                    <?php endif; ?>
                                    <?php if (($_SESSION['user']['role_id'] ?? 0) == 1): ?>
                                        <a class="px-3 py-1.5 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100"
                                           href="/dental-management-system/public/treatments/delete?id=<?= (int)$t['id'] ?>"
                                           data-confirm="Delete this treatment?">
                                            Delete
                                        </a>
                                    <?php endif; ?>
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
