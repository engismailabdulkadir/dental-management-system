<?php
$pageTitle = 'Edit Doctor';
$pageSubtitle = 'Update doctor details';
require __DIR__ . '/../layouts/app_start.php';
?>

<form method="POST" action="/dental-management-system/public/doctors/update" class="max-w-3xl" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= (int)$doctor['id'] ?>">

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
            <label class="text-sm text-slate-600">Full Name</label>
            <input type="text" name="full_name" required
                   value="<?= htmlspecialchars($doctor['full_name']) ?>"
                   class="mt-2 w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
        </div>

        <div>
            <label class="text-sm text-slate-600">Specialization</label>
            <input type="text" name="specialization" required
                   value="<?= htmlspecialchars($doctor['specialization']) ?>"
                   class="mt-2 w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
        </div>

        <div class="md:col-span-2">
            <label class="text-sm text-slate-600">Phone</label>
            <input type="text" name="phone"
                   value="<?= htmlspecialchars($doctor['phone']) ?>"
                   class="mt-2 w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
        </div>

        <div class="md:col-span-2">
            <label class="text-sm text-slate-600">Doctor Photo</label>
            <input type="file" name="photo" accept="image/*"
                   class="mt-2 w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
            <?php if (!empty($doctor['photo'])): ?>
                <div class="mt-3">
                    <img src="/dental-management-system/public/<?= htmlspecialchars($doctor['photo']) ?>"
                         alt="Doctor Photo" class="h-16 w-16 rounded-full object-cover border border-slate-200">
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="mt-4 flex gap-2">
        <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
            Update Doctor
        </button>
        <a href="/dental-management-system/public/doctors"
           class="rounded-xl bg-slate-100 px-4 py-2 text-slate-700 hover:bg-slate-200">
            Back
        </a>
    </div>
</form>

<?php require __DIR__ . '/../layouts/app_end.php'; ?>
