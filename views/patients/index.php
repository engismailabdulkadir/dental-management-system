<?php
$pageTitle = 'Patients';
$pageSubtitle = 'Manage patient records';
$pageActions = '<a class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-white hover:bg-blue-700" href="/dental-management-system/public/patients/create">Add Patient</a>';
require __DIR__ . '/../layouts/app_start.php';
?>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="p-4 border-b border-slate-100">
        <div class="flex items-center justify-between">
            <div class="text-sm text-slate-500">Search patients by name</div>
            <div class="flex items-center gap-2">
                <input id="patient_search" type="text" placeholder="Search..." class="w-64 rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500 text-sm px-3 py-2">
                <button id="patient_clear" type="button" class="rounded-xl bg-slate-100 px-3 py-2 text-slate-700 hover:bg-slate-200 text-sm">Clear</button>
            </div>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-slate-500">
                <tr>
                    <th class="px-5 py-3 text-left">ID</th>
                    <th class="px-5 py-3 text-left">Full Name</th>
                    <th class="px-5 py-3 text-left">Gender</th>
                    <th class="px-5 py-3 text-left">Phone</th>
                    <th class="px-5 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (empty($patients)): ?>
                    <tr>
                        <td class="px-5 py-4 text-slate-500" colspan="5">No patients found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($patients as $patient): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-4 font-semibold text-slate-800"><?= (int)$patient['id'] ?></td>
                            <td class="px-5 py-4 text-slate-700"><?= htmlspecialchars($patient['full_name']) ?></td>
                            <td class="px-5 py-4 text-slate-700"><?= htmlspecialchars($patient['gender']) ?></td>
                            <td class="px-5 py-4 text-slate-700"><?= htmlspecialchars($patient['phone']) ?></td>
                            <td class="px-5 py-4">
                                <div class="flex gap-2">
                                    <button data-id="<?= (int)$patient['id'] ?>" class="patient-view px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100">View</button>
                                    <a class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200"
                                       href="/dental-management-system/public/patients/edit?id=<?= (int)$patient['id'] ?>">
                                        Edit
                                    </a>
                                    <a class="px-3 py-1.5 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100"
                                       href="/dental-management-system/public/patients/delete?id=<?= (int)$patient['id'] ?>"
                                       data-confirm="Delete this patient?">
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

<!-- Patient details modal -->
<div id="patientModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">
    <div class="bg-white rounded-2xl shadow-lg w-11/12 max-w-2xl p-6">
        <div class="flex justify-between items-start">
            <h3 class="text-lg font-semibold">Patient Details</h3>
            <button id="patientModalClose" class="text-slate-500 hover:text-slate-700">Close</button>
        </div>
        <div id="patientModalBody" class="mt-4 text-sm text-slate-700 space-y-2">
            <!-- filled by JS -->
        </div>
    </div>
</div>

<script>
    (function(){
        const basePath = '/dental-management-system/public';
        const input = document.getElementById('patient_search');
        const clearBtn = document.getElementById('patient_clear');
        const tbody = document.querySelector('tbody');
        const modal = document.getElementById('patientModal');
        const modalBody = document.getElementById('patientModalBody');
        const modalClose = document.getElementById('patientModalClose');
        let debounceTimer = null;

        function renderRows(items) {
            if (!tbody) return;
            tbody.innerHTML = '';
            if (!items || !items.length) {
                tbody.innerHTML = '<tr><td class="px-5 py-4 text-slate-500" colspan="5">No patients found.</td></tr>';
                return;
            }
            items.forEach(function (p) {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-slate-50';
                tr.innerHTML =
                    '<td class="px-5 py-4 font-semibold text-slate-800">' + (p.id || '') + '</td>' +
                    '<td class="px-5 py-4 text-slate-700">' + (p.name || '') + '</td>' +
                    '<td class="px-5 py-4 text-slate-700">' + (p.gender || '') + '</td>' +
                    '<td class="px-5 py-4 text-slate-700">' + (p.phone || '') + '</td>' +
                    '<td class="px-5 py-4">' +
                        '<div class="flex gap-2">' +
                            '<button data-id="' + (p.id || '') + '" class="patient-view px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100">View</button>' +
                            '<a class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200" href="' + basePath + '/patients/edit?id=' + encodeURIComponent(p.id || '') + '">Edit</a>' +
                            '<a class="px-3 py-1.5 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100" data-confirm="Delete this patient?" href="' + basePath + '/patients/delete?id=' + encodeURIComponent(p.id || '') + '">Delete</a>' +
                        '</div>' +
                    '</td>';
                tbody.appendChild(tr);
            });
            attachViewHandlers();
        }

        function attachViewHandlers() {
            document.querySelectorAll('.patient-view').forEach(function (btn) {
                btn.removeEventListener('click', onViewClick);
                btn.addEventListener('click', onViewClick);
            });
        }

        function onViewClick(ev) {
            const id = ev.currentTarget.getAttribute('data-id');
            if (!id) return;
            // fetch single patient details
            fetch(basePath + '/patients/search?q=' + encodeURIComponent(id))
                .then(function(res){ return res.json(); })
                .then(function(data){
                    if (!Array.isArray(data) || !data.length) return;
                    // try to find exact id match
                    let p = data.find(x=>String(x.id)===String(id)) || data[0];
                    modalBody.innerHTML = '';
                    const rows = [
                        ['ID', p.id || ''],
                        ['Full Name', p.name || ''],
                        ['Gender', p.gender || ''],
                        ['Date of Birth', p.date_of_birth || ''],
                        ['Phone', p.phone || ''],
                        ['Address', p.address || '']
                    ];
                    rows.forEach(function(r){
                        const div = document.createElement('div');
                        div.innerHTML = '<div class="text-slate-500 text-sm">'+r[0]+'</div><div class="font-semibold text-slate-800">'+(r[1]||'-')+'</div>';
                        modalBody.appendChild(div);
                    });
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                }).catch(function(){ });
        }

        if (!input) return;
        input.addEventListener('input', function () {
            const q = input.value.trim();
            if (debounceTimer) clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () {
                if (!q) { window.location.reload(); return; }
                fetch(basePath + '/patients/search?q=' + encodeURIComponent(q), {credentials: 'same-origin'})
                    .then(function (res) { return res.json(); })
                    .then(function (data) { renderRows(Array.isArray(data) ? data : []); })
                    .catch(function () { /* ignore */ });
            }, 250);
        });

        if (clearBtn) clearBtn.addEventListener('click', function () { input.value = ''; window.location.reload(); });
        if (modalClose) modalClose.addEventListener('click', function (){ modal.classList.add('hidden'); modal.classList.remove('flex'); });
        // close on background click
        modal.addEventListener('click', function (e){ if (e.target === modal) { modal.classList.add('hidden'); modal.classList.remove('flex'); } });

        // initial attach for existing buttons
        attachViewHandlers();
    })();
</script>

<?php require __DIR__ . '/../layouts/app_end.php'; ?>
