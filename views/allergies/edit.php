<?php
$pageTitle = 'Edit Allergy';
$pageSubtitle = 'Update allergy details';
require __DIR__ . '/../layouts/app_start.php';
?>

<form method="POST" action="/dental-management-system/public/allergies/update" class="max-w-3xl">
    <input type="hidden" name="id" value="<?= (int)$allergy['id'] ?>">

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 grid grid-cols-1 gap-5">
        <div>
            <label class="text-sm text-slate-600">Allergy Name</label>
            <input type="text" name="name" required
                   value="<?= htmlspecialchars($allergy['name']) ?>"
                   class="mt-2 w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
        </div>
    </div>

    <div class="mt-4 flex gap-2">
        <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
            Update Allergy
        </button>
        <a href="/dental-management-system/public/allergies"
           class="rounded-xl bg-slate-100 px-4 py-2 text-slate-700 hover:bg-slate-200">
            Back
        </a>
    </div>
</form>

<?php require __DIR__ . '/../layouts/app_end.php'; ?>
