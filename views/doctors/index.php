<?php
$pageTitle = 'Doctors';
$pageSubtitle = 'Manage doctors and specializations';
$pageActions = '<a class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-white hover:bg-blue-700" href="/dental-management-system/public/doctors/create">Add Doctor</a>';
require __DIR__ . '/../layouts/app_start.php';
?>

<div class="mb-4">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div class="text-sm text-slate-500">Search doctors by name, specialization, phone, or ID</div>
        <div class="flex items-center gap-2">
            <input id="doctor_search" type="text" placeholder="Search..."
                   class="w-64 rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
            <button id="doctor_clear" type="button"
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
                    <th class="px-5 py-3 text-left">Doctor</th>
                    <th class="px-5 py-3 text-left">Specialization</th>
                    <th class="px-5 py-3 text-left">Phone</th>
                    <th class="px-5 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (empty($doctors)): ?>
                    <tr>
                        <td class="px-5 py-4 text-slate-500" colspan="5">No doctors found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($doctors as $doctor): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-4 font-semibold text-slate-800"><?= (int)$doctor['id'] ?></td>
                            <td class="px-5 py-4 text-slate-700">
                                <div class="flex items-center gap-3">
                                    <?php if (!empty($doctor['photo'])): ?>
                                        <img src="/dental-management-system/public/<?= htmlspecialchars($doctor['photo']) ?>"
                                             class="h-9 w-9 rounded-full object-cover border border-slate-200" alt="Doctor">
                                    <?php else: ?>
                                        <div class="h-9 w-9 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center text-sm font-semibold">
                                            <?= strtoupper(substr($doctor['full_name'], 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                    <span><?= htmlspecialchars($doctor['full_name']) ?></span>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-slate-700"><?= htmlspecialchars($doctor['specialization']) ?></td>
                            <td class="px-5 py-4 text-slate-700"><?= htmlspecialchars($doctor['phone']) ?></td>
                            <td class="px-5 py-4">
                                <div class="flex gap-2">
                                    <a class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200"
                                       href="/dental-management-system/public/doctors/edit?id=<?= (int)$doctor['id'] ?>">
                                        Edit
                                    </a>
                                    <a class="px-3 py-1.5 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100"
                                       href="/dental-management-system/public/doctors/delete?id=<?= (int)$doctor['id'] ?>"
                                       data-confirm="Delete this doctor?">
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
const searchInput = document.getElementById('doctor_search');
const clearBtn = document.getElementById('doctor_clear');
const tbody = document.querySelector('tbody');
let debounceTimer = null;

function escapeHtml(str) {
    return String(str ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function renderRows(items) {
    tbody.innerHTML = '';
    if (!items.length) {
        tbody.innerHTML = '<tr><td class="px-5 py-4 text-slate-500" colspan="5">No doctors found.</td></tr>';
        return;
    }
    items.forEach(function (d) {
        const initials = (d.name || 'D').trim().charAt(0).toUpperCase();
        const photoHtml = d.photo
            ? '<img src="' + basePath + '/' + escapeHtml(d.photo) + '" class="h-9 w-9 rounded-full object-cover border border-slate-200" alt="Doctor">'
            : '<div class="h-9 w-9 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center text-sm font-semibold">' + escapeHtml(initials) + '</div>';

        const tr = document.createElement('tr');
        tr.className = 'hover:bg-slate-50';
        tr.innerHTML =
            '<td class="px-5 py-4 font-semibold text-slate-800">' + (d.id || '') + '</td>' +
            '<td class="px-5 py-4 text-slate-700">' +
                '<div class="flex items-center gap-3">' +
                    photoHtml +
                    '<span>' + escapeHtml(d.name) + '</span>' +
                '</div>' +
            '</td>' +
            '<td class="px-5 py-4 text-slate-700">' + escapeHtml(d.specialization) + '</td>' +
            '<td class="px-5 py-4 text-slate-700">' + escapeHtml(d.phone) + '</td>' +
            '<td class="px-5 py-4">' +
                '<div class="flex gap-2">' +
                    '<a class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200" href="' + basePath + '/doctors/edit?id=' + encodeURIComponent(d.id || '') + '">Edit</a>' +
                    '<a class="px-3 py-1.5 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100" data-confirm="Delete this doctor?" href="' + basePath + '/doctors/delete?id=' + encodeURIComponent(d.id || '') + '">Delete</a>' +
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
        fetch(basePath + '/doctors/search?q=' + encodeURIComponent(q))
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
