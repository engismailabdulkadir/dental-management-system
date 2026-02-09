<?php
$pageTitle = 'Edit Patient';
$pageSubtitle = 'Update patient details';
require __DIR__ . '/../layouts/app_start.php';
?>

<form method="POST" action="/dental-management-system/public/patients/update" class="max-w-3xl">
    <input type="hidden" name="id" value="<?= (int)$patient['id'] ?>">

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
            <label class="text-sm text-slate-600">Full Name</label>
            <input type="text" name="full_name" required
                   value="<?= htmlspecialchars($patient['full_name']) ?>"
                   class="mt-2 w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
        </div>

        <div>
            <label class="text-sm text-slate-600">Gender</label>
                <select name="gender" required
                    class="mt-2 w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                <option value="male" <?= $patient['gender'] == 'male' ? 'selected' : '' ?>>Male</option>
                <option value="female" <?= $patient['gender'] == 'female' ? 'selected' : '' ?>>Female</option>
            </select>
        </div>

        <div>
            <label class="text-sm text-slate-600">Date of Birth</label>
                 <input type="date" name="date_of_birth" required
                     value="<?= htmlspecialchars($patient['date_of_birth']) ?>"
                     class="mt-2 w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
        </div>

        <div>
            <label class="text-sm text-slate-600">Phone</label>
                 <input type="text" name="phone" required
                     value="<?= htmlspecialchars($patient['phone']) ?>"
                     class="mt-2 w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
        </div>

        <div class="md:col-span-2">
            <label class="text-sm text-slate-600">Address</label>
            <textarea name="address" rows="3" required
                      class="mt-2 w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500"><?= htmlspecialchars($patient['address']) ?></textarea>
        </div>
    </div>

    <div class="mt-4 flex gap-2">
        <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
            Update Patient
        </button>
        <a href="/dental-management-system/public/patients"
           class="rounded-xl bg-slate-100 px-4 py-2 text-slate-700 hover:bg-slate-200">
            Back
        </a>
    </div>
</form>

<script>
(function () {
    const form = document.querySelector('form');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        const fullName = (form.querySelector('input[name="full_name"]')?.value || '').trim();
        const phoneRaw = (form.querySelector('input[name="phone"]')?.value || '').trim();
        const phone = phoneRaw.replace(/\D+/g, '');
        const nameParts = fullName.split(/\s+/).filter(Boolean);

        if (nameParts.length !== 3) {
            e.preventDefault();
            if (window.Swal) {
                Swal.fire({ icon: 'error', title: 'Invalid Name', text: 'Full Name must be exactly 3 words.' });
            } else {
                alert('Full Name must be exactly 3 words.');
            }
            return;
        }

        if (phone.length !== 10) {
            e.preventDefault();
            if (window.Swal) {
                Swal.fire({ icon: 'error', title: 'Invalid Phone', text: 'Phone must be exactly 10 digits.' });
            } else {
                alert('Phone must be exactly 10 digits.');
            }
            return;
        }
    }, true);
})();
</script>

<?php require __DIR__ . '/../layouts/app_end.php'; ?>
