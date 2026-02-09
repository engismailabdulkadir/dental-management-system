<?php
$pageTitle = 'Add User';
$pageSubtitle = 'Create Admin or Staff account';
require __DIR__ . '/../layouts/app_start.php';
?>

<form method="POST" action="/dental-management-system/public/users/store" class="max-w-3xl">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
            <label class="text-sm text-slate-600">Full Name</label>
            <input type="text" name="full_name" required
                   class="mt-2 w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
        </div>

        <div>
            <label class="text-sm text-slate-600">Email</label>
            <input type="email" name="email" required
                   class="mt-2 w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
        </div>

        <div>
            <label class="text-sm text-slate-600">Password</label>
            <input type="password" name="password" required
                   class="mt-2 w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
        </div>

        <div>
            <label class="text-sm text-slate-600">Role</label>
            <select name="role_id" required
                    id="user_role"
                    class="mt-2 w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                <option value="">Select Role</option>
                <?php foreach ($roles as $r): ?>
                    <option value="<?= (int)$r['id'] ?>"><?= htmlspecialchars($r['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div id="doctor_select_wrap" class="md:col-span-2 hidden">
            <label class="text-sm text-slate-600">Doctor Profile</label>
            <select name="doctor_id"
                    class="mt-2 w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                <option value="">Select Doctor</option>
                <?php foreach ($doctors as $d): ?>
                    <option value="<?= (int)$d['id'] ?>"><?= htmlspecialchars($d['full_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="mt-4 flex gap-2">
        <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
            Save User
        </button>
        <a href="/dental-management-system/public/users"
           class="rounded-xl bg-slate-100 px-4 py-2 text-slate-700 hover:bg-slate-200">
            Back
        </a>
    </div>
</form>

<script>
const roleSelect = document.getElementById('user_role');
const doctorWrap = document.getElementById('doctor_select_wrap');
roleSelect.addEventListener('change', function () {
    if (roleSelect.value === '3') {
        doctorWrap.classList.remove('hidden');
    } else {
        doctorWrap.classList.add('hidden');
    }
});
</script>

<?php require __DIR__ . '/../layouts/app_end.php'; ?>
