<?php
$pageTitle = 'Appointments';
$pageSubtitle = 'Schedule and manage appointments';
$pageActions = '<a class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-white hover:bg-blue-700" href="/dental-management-system/public/appointments/create">Add Appointment</a>';
require __DIR__ . '/../layouts/app_start.php';
?>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="p-4 border-b border-slate-100">
        <div class="flex items-center justify-between">
            <div class="text-sm text-slate-500">Search appointments by patient or doctor</div>
            <div class="flex items-center gap-2">
                <input id="appointment_search" type="text" placeholder="Search..." class="w-64 rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500 text-sm px-3 py-2">
                <button id="appointment_clear" type="button" class="rounded-xl bg-slate-100 px-3 py-2 text-slate-700 hover:bg-slate-200 text-sm">Clear</button>
            </div>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-slate-500">
                <tr>
                    <th class="px-5 py-3 text-left">ID</th>
                    <th class="px-5 py-3 text-left">Patient</th>
                    <th class="px-5 py-3 text-left">Doctor</th>
                    <th class="px-5 py-3 text-left">Date</th>
                    <th class="px-5 py-3 text-left">Time</th>
                    <th class="px-5 py-3 text-left">Status</th>
                    <th class="px-5 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (empty($appointments)): ?>
                    <tr>
                        <td class="px-5 py-4 text-slate-500" colspan="7">No appointments found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($appointments as $a): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-4 font-semibold text-slate-800"><?= (int)$a['id'] ?></td>
                            <td class="px-5 py-4 text-slate-700"><?= htmlspecialchars($a['patient_name']) ?></td>
                            <td class="px-5 py-4 text-slate-700"><?= htmlspecialchars($a['doctor_name']) ?></td>
                            <td class="px-5 py-4 text-slate-700"><?= htmlspecialchars($a['appointment_date']) ?></td>
                            <td class="px-5 py-4 text-slate-700"><?= htmlspecialchars(substr($a['appointment_time'], 0, 5)) ?></td>
                            <td class="px-5 py-4">
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-700">
                                    <?= htmlspecialchars($a['status']) ?>
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex gap-2">
                                    <button data-id="<?= (int)$a['id'] ?>" class="appointment-view px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100">View</button>
                                    <a class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200"
                                       href="/dental-management-system/public/appointments/edit?id=<?= (int)$a['id'] ?>">
                                        Edit
                                    </a>
                                    <a class="px-3 py-1.5 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100"
                                       href="/dental-management-system/public/appointments/delete?id=<?= (int)$a['id'] ?>"
                                       data-confirm="Delete this appointment?">
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

<!-- Appointment details modal -->
<div id="appointmentModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">
    <div class="bg-white rounded-2xl shadow-lg w-11/12 max-w-2xl p-6">
        <div class="flex justify-between items-start">
            <h3 class="text-lg font-semibold">Appointment Details</h3>
            <button id="appointmentModalClose" class="text-slate-500 hover:text-slate-700">Close</button>
        </div>
        <div id="appointmentModalBody" class="mt-4 text-sm text-slate-700 space-y-2">
            <!-- filled by JS -->
        </div>
    </div>
</div>

<script>
    (function(){
        const basePath = '/dental-management-system/public';
        const input = document.getElementById('appointment_search');
        const clearBtn = document.getElementById('appointment_clear');
        const tbody = document.querySelector('tbody');
        const modal = document.getElementById('appointmentModal');
        const modalBody = document.getElementById('appointmentModalBody');
        const modalClose = document.getElementById('appointmentModalClose');
        let debounceTimer = null;

        function renderRows(items) {
            if (!tbody) return;
            tbody.innerHTML = '';
            if (!items || !items.length) {
                tbody.innerHTML = '<tr><td class="px-5 py-4 text-slate-500" colspan="7">No appointments found.</td></tr>';
                return;
            }
            items.forEach(function (p) {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-slate-50';
                tr.innerHTML =
                    '<td class="px-5 py-4 font-semibold text-slate-800">' + (p.id || '') + '</td>' +
                    '<td class="px-5 py-4 text-slate-700">' + (p.patient_name || '') + '</td>' +
                    '<td class="px-5 py-4 text-slate-700">' + (p.doctor_name || '') + '</td>' +
                    '<td class="px-5 py-4 text-slate-700">' + (p.appointment_date || '') + '</td>' +
                    '<td class="px-5 py-4 text-slate-700">' + (p.appointment_time ? p.appointment_time.substring(0,5) : '') + '</td>' +
                    '<td class="px-5 py-4"><span class="rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-700">' + (p.status || '') + '</span></td>' +
                    '<td class="px-5 py-4">' +
                        '<div class="flex gap-2">' +
                            '<button data-id="' + (p.id || '') + '" class="appointment-view px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100">View</button>' +
                            '<a class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200" href="' + basePath + '/appointments/edit?id=' + encodeURIComponent(p.id || '') + '">Edit</a>' +
                            '<a class="px-3 py-1.5 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100" data-confirm="Delete this appointment?" href="' + basePath + '/appointments/delete?id=' + encodeURIComponent(p.id || '') + '">Delete</a>' +
                        '</div>' +
                    '</td>';
                tbody.appendChild(tr);
            });
            attachViewHandlers();
        }

        function attachViewHandlers() {
            document.querySelectorAll('.appointment-view').forEach(function (btn) {
                btn.removeEventListener('click', onViewClick);
                btn.addEventListener('click', onViewClick);
            });
        }

        function onViewClick(ev) {
            const id = ev.currentTarget.getAttribute('data-id');
            if (!id) return;
            fetch(basePath + '/appointments/search?q=' + encodeURIComponent(id))
                .then(function(res){ return res.json(); })
                .then(function(data){
                    if (!Array.isArray(data) || !data.length) return;
                    const a = data.find(x=>String(x.id)===String(id)) || data[0];
                    modalBody.innerHTML = '';
                    const rows = [
                        ['ID', a.id || ''],
                        ['Patient', a.patient_name || ''],
                        ['Doctor', a.doctor_name || ''],
                        ['Date', a.appointment_date || ''],
                        ['Time', a.appointment_time ? a.appointment_time.substring(0,5) : ''],
                        ['Status', a.status || '']
                    ];
                    rows.forEach(function(r){
                        const div = document.createElement('div');
                        div.innerHTML = '<div class="text-slate-500 text-sm">'+r[0]+'</div><div class="font-semibold text-slate-800">'+(r[1]||'-')+'</div>';
                        modalBody.appendChild(div);
                    });
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                }).catch(function(){});
        }

        if (!input) return;
        input.addEventListener('input', function () {
            const q = input.value.trim();
            if (debounceTimer) clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () {
                if (!q) { window.location.reload(); return; }
                fetch(basePath + '/appointments/search?q=' + encodeURIComponent(q), {credentials: 'same-origin'})
                    .then(function (res) { return res.json(); })
                    .then(function (data) { renderRows(Array.isArray(data) ? data : []); })
                    .catch(function () { /* ignore */ });
            }, 250);
        });

        if (clearBtn) clearBtn.addEventListener('click', function () { input.value = ''; window.location.reload(); });
        if (modalClose) modalClose.addEventListener('click', function (){ modal.classList.add('hidden'); modal.classList.remove('flex'); });
        modal.addEventListener('click', function (e){ if (e.target === modal) { modal.classList.add('hidden'); modal.classList.remove('flex'); } });

        attachViewHandlers();
    })();
</script>

<?php require __DIR__ . '/../layouts/app_end.php'; ?>
