<?php
$pageTitle = 'Invoices';
$pageSubtitle = 'Billing and payment tracking';
$pageActions = '<a class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-white hover:bg-blue-700" href="/dental-management-system/public/invoices/create">Create Invoice</a>';
require __DIR__ . '/../layouts/app_start.php';
?>

<div class="mb-4">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div class="text-sm text-slate-500">Search by Invoice ID or Patient name</div>
        <div class="flex items-center gap-2">
            <input id="invoice_search" type="text" placeholder="Search..."
                   class="w-64 rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
            <button id="invoice_clear" type="button"
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
                    <th class="px-5 py-3 text-left">Patient</th>
                    <th class="px-5 py-3 text-left">Total</th>
                    <th class="px-5 py-3 text-left">Date</th>
                    <th class="px-5 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (empty($invoices)): ?>
                    <tr id="invoice_empty">
                        <td class="px-5 py-4 text-slate-500" colspan="5">No invoices found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($invoices as $inv): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-4 font-semibold text-slate-800">#<?= (int)$inv['id'] ?></td>
                            <td class="px-5 py-4 text-slate-700"><?= htmlspecialchars($inv['patient_name']) ?></td>
                            <td class="px-5 py-4 text-slate-700">$<?= number_format((float)$inv['total'], 2) ?></td>
                            <td class="px-5 py-4 text-slate-500"><?= htmlspecialchars($inv['created_at']) ?></td>
                            <td class="px-5 py-4">
                                <div class="flex gap-2">
                                    <a class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200"
                                       href="/dental-management-system/public/invoices/show?id=<?= (int)$inv['id'] ?>">
                                        View
                                    </a>
                                    <a class="px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100"
                                       href="/dental-management-system/public/invoices/edit?id=<?= (int)$inv['id'] ?>">
                                        Edit
                                    </a>
                                    <a class="px-3 py-1.5 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100"
                                       href="/dental-management-system/public/invoices/delete?id=<?= (int)$inv['id'] ?>"
                                       data-confirm="Delete this invoice?">
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
const searchInput = document.getElementById('invoice_search');
const clearBtn = document.getElementById('invoice_clear');
const tbody = document.querySelector('tbody');
let debounceTimer = null;

function renderRows(items) {
    tbody.innerHTML = '';
    if (!items.length) {
        tbody.innerHTML = '<tr><td class="px-5 py-4 text-slate-500" colspan="5">No invoices found.</td></tr>';
        return;
    }
    items.forEach(function (inv) {
        const tr = document.createElement('tr');
        tr.className = 'hover:bg-slate-50';
        tr.innerHTML =
            '<td class="px-5 py-4 font-semibold text-slate-800">#' + inv.id + '</td>' +
            '<td class="px-5 py-4 text-slate-700">' + inv.patient_name + '</td>' +
            '<td class="px-5 py-4 text-slate-700">$' + Number(inv.total).toFixed(2) + '</td>' +
            '<td class="px-5 py-4 text-slate-500">' + inv.created_at + '</td>' +
            '<td class="px-5 py-4">' +
                '<div class="flex gap-2">' +
                    '<a class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200" href="' + basePath + '/invoices/show?id=' + inv.id + '">View</a>' +
                    '<a class="px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100" href="' + basePath + '/invoices/edit?id=' + inv.id + '">Edit</a>' +
                    '<a class="px-3 py-1.5 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100" data-confirm="Delete this invoice?" href="' + basePath + '/invoices/delete?id=' + inv.id + '">Delete</a>' +
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
        fetch(basePath + '/invoices/search?q=' + encodeURIComponent(q))
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
