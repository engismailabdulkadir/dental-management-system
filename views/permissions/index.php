<?php
$pageTitle = 'Permissions';
$pageSubtitle = 'Manage role permissions';
require __DIR__ . '/../layouts/app_start.php';
?>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
    <form method="POST" action="/dental-management-system/public/permissions/update" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="text-sm text-slate-600">Select Role</label>
                <select name="role_id" required
                        class="mt-2 w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Select Role</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= (int)$role['id'] ?>"><?= htmlspecialchars($role['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <?php foreach ($perms as $perm): ?>
                <label class="flex items-center gap-3 bg-slate-50 rounded-xl px-4 py-3 border border-slate-100">
                    <input type="checkbox" name="permissions[]" value="<?= (int)$perm['id'] ?>"
                           class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-slate-700"><?= htmlspecialchars($perm['name']) ?></span>
                </label>
            <?php endforeach; ?>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                Save Permissions
            </button>
        </div>
    </form>
</div>

<script>
const rolePerms = <?= json_encode($map ?? []) ?>;
const roleSelect = document.querySelector('select[name="role_id"]');
const checkboxes = Array.from(document.querySelectorAll('input[name="permissions[]"]'));

roleSelect.addEventListener('change', function () {
    const roleId = roleSelect.value;
    checkboxes.forEach(function (cb) {
        cb.checked = rolePerms[roleId] && rolePerms[roleId][cb.value] ? true : false;
    });
});
</script>

<?php require __DIR__ . '/../layouts/app_end.php'; ?>
