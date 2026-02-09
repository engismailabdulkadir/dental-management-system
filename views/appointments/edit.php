<?php
$pageTitle = 'Edit Appointment';
$pageSubtitle = 'Update appointment details';
require __DIR__ . '/../layouts/app_start.php';
?>

<form method="POST" action="/dental-management-system/public/appointments/update" class="max-w-3xl">
    <input type="hidden" name="id" value="<?= (int)$appointment['id'] ?>">

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
            <label class="text-sm text-slate-600">Patient</label>
            <select name="patient_id" required
                    class="mt-2 w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                <?php foreach ($patients as $p): ?>
                    <option value="<?= (int)$p['id'] ?>" <?= $p['id'] == $appointment['patient_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['full_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="text-sm text-slate-600">Doctor</label>
            <select name="doctor_id" required
                    class="mt-2 w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                <?php foreach ($doctors as $d): ?>
                    <option value="<?= (int)$d['id'] ?>" <?= $d['id'] == $appointment['doctor_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($d['full_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="text-sm text-slate-600">Date</label>
            <input type="date" name="appointment_date"
                   value="<?= htmlspecialchars($appointment['appointment_date']) ?>" required
                   class="mt-2 w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
        </div>

        <div>
            <label class="text-sm text-slate-600">Time</label>
            <input type="time" name="appointment_time"
                   value="<?= htmlspecialchars(substr($appointment['appointment_time'], 0, 5)) ?>" required
                   class="mt-2 w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
        </div>

        <div class="md:col-span-2">
            <label class="text-sm text-slate-600">Status</label>
            <select name="status"
                    class="mt-2 w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                <option value="booked" <?= $appointment['status'] == 'booked' ? 'selected' : '' ?>>Booked</option>
                <option value="completed" <?= $appointment['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                <option value="cancelled" <?= $appointment['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>
        </div>
    </div>

    <div class="mt-4 flex gap-2">
        <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
            Update Appointment
        </button>
        <a href="/dental-management-system/public/appointments"
           class="rounded-xl bg-slate-100 px-4 py-2 text-slate-700 hover:bg-slate-200">
            Back
        </a>
    </div>
</form>

<?php require __DIR__ . '/../layouts/app_end.php'; ?>
