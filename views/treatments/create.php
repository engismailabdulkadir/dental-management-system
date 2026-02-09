<?php
$pageTitle = 'Add Treatment';
$pageSubtitle = 'Create a new treatment';
require __DIR__ . '/../layouts/app_start.php';
?>

<form method="POST" action="/dental-management-system/public/treatments/store" class="max-w-3xl">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="md:col-span-2">
            <label class="text-sm text-slate-600">Appointment</label>
            <select name="appointment_id" required
                    class="mt-2 w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                <option value="">Select Appointment</option>
                <?php foreach ($appointments as $a): ?>
                    <option value="<?= (int)$a['id'] ?>">
                        #<?= (int)$a['id'] ?> | <?= htmlspecialchars($a['full_name']) ?> | <?= htmlspecialchars($a['appointment_date']) ?> <?= htmlspecialchars(substr($a['appointment_time'], 0, 5)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <?php if (($_SESSION['user']['role_id'] ?? 0) == 1): ?>
            <div class="md:col-span-2">
                <label class="text-sm text-slate-600">Doctor</label>
                <select name="doctor_id" required
                        class="mt-2 w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Select Doctor</option>
                    <?php foreach ($doctors as $d): ?>
                        <option value="<?= (int)$d['id'] ?>"><?= htmlspecialchars($d['full_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>

        <div class="md:col-span-2">
            <label class="text-sm text-slate-600">Notes</label>
            <textarea name="notes" rows="4"
                      class="mt-2 w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500"></textarea>
        </div>
    </div>

    <div class="mt-4 flex gap-2">
        <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
            Save Treatment
        </button>
        <a href="/dental-management-system/public/treatments"
           class="rounded-xl bg-slate-100 px-4 py-2 text-slate-700 hover:bg-slate-200">
            Back
        </a>
    </div>
</form>

<?php require __DIR__ . '/../layouts/app_end.php'; ?>
