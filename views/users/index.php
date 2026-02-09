<?php
$pageTitle = 'Users';
$pageSubtitle = 'Manage system users (Admin/Staff)';
$pageActions = '<a class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-white hover:bg-blue-700" href="/dental-management-system/public/users/create">Add User</a>';
require __DIR__ . '/../layouts/app_start.php';
?>

<div class="mb-4">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div class="text-sm text-slate-500">Search by ID, name, or email</div>
        <div class="flex items-center gap-2">
            <input id="user_search" type="text" placeholder="Search..."
                   class="w-64 rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
            <button id="user_clear" type="button"
                    class="rounded-xl bg-slate-100 px-3 py-2 text-slate-700 hover:bg-slate-200">
                Clear
            </button>
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-slate-500">
                <tr>
                    <th class="px-5 py-3 text-left">ID</th>
                    <th class="px-5 py-3 text-left">Full Name</th>
                    <th class="px-5 py-3 text-left">Email</th>
                    <th class="px-5 py-3 text-left">Role</th>
                    <th class="px-5 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr><td class="px-5 py-4 text-slate-500" colspan="5">No users found.</td></tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-4 font-semibold text-slate-800">#<?= (int)$u['id'] ?></td>
                            <td class="px-5 py-4 text-slate-700"><?= htmlspecialchars($u['full_name']) ?></td>
                            <td class="px-5 py-4 text-slate-700"><?= htmlspecialchars($u['email']) ?></td>
                            <td class="px-5 py-4 text-slate-700"><?= htmlspecialchars($u['role_name'] ?? '') ?></td>
                            <td class="px-5 py-4">
                                <div class="flex gap-2">
                                    <a class="px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100"
                                       href="/dental-management-system/public/users/edit?id=<?= (int)$u['id'] ?>">
                                        Edit
                                    </a>
                                    <a class="px-3 py-1.5 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100"
                                       href="/dental-management-system/public/users/delete?id=<?= (int)$u['id'] ?>"
                                       data-confirm="Delete this user?">
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

<script>
const basePath = '/dental-management-system/public';
const searchInput = document.getElementById('user_search');
const clearBtn = document.getElementById('user_clear');
const tbody = document.querySelector('tbody');
let debounceTimer = null;

function renderRows(items) {
    tbody.innerHTML = '';
    if (!items.length) {
        tbody.innerHTML = '<tr><td class="px-5 py-4 text-slate-500" colspan="5">No users found.</td></tr>';
        return;
    }
    items.forEach(function (u) {
        const tr = document.createElement('tr');
        tr.className = 'hover:bg-slate-50';
        tr.innerHTML =
            '<td class="px-5 py-4 font-semibold text-slate-800">#' + u.id + '</td>' +
            '<td class="px-5 py-4 text-slate-700">' + u.full_name + '</td>' +
            '<td class="px-5 py-4 text-slate-700">' + u.email + '</td>' +
            '<td class="px-5 py-4 text-slate-700">' + (u.role_name || '') + '</td>' +
            '<td class="px-5 py-4">' +
                '<div class="flex gap-2">' +
                    '<a class="px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100" href="' + basePath + '/users/edit?id=' + u.id + '">Edit</a>' +
                    '<a class="px-3 py-1.5 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100" data-confirm="Delete this user?" href="' + basePath + '/users/delete?id=' + u.id + '">Delete</a>' +
                '</div>' +
            '</td>';
        tbody.appendChild(tr);
    });
}

searchInput.addEventListener('input', function () {
    const q = searchInput.value.trim();
    if (debounceTimer) {
        clearTimeout(debounceTimer);
    }
    debounceTimer = setTimeout(function () {
        if (!q) {
            window.location.reload();
            return;
        }
        fetch(basePath + '/users/search?q=' + encodeURIComponent(q))
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (Array.isArray(data)) {
                    renderRows(data);
                }
            })
            .catch(function () {});
    }, 250);
});

clearBtn.addEventListener('click', function () {
    searchInput.value = '';
    window.location.reload();
});
</script>

<?php require __DIR__ . '/../layouts/app_end.php'; ?>
