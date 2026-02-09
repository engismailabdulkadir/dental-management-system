<?php
$pageTitle = 'Create Invoice';
$pageSubtitle = 'Review and confirm invoice details';
require __DIR__ . '/../layouts/app_start.php';
?>

<?php if (isset($items, $treatment_id)): ?>
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="px-4 py-3 text-left">Procedure</th>
                        <th class="px-4 py-3 text-left">Qty</th>
                        <th class="px-4 py-3 text-left">Price</th>
                        <th class="px-4 py-3 text-left">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($items as $it): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-slate-700"><?= htmlspecialchars($it['name']) ?></td>
                            <td class="px-4 py-3 text-slate-700"><?= (int)$it['qty'] ?></td>
                            <td class="px-4 py-3 text-slate-700">$<?= number_format((float)$it['price'], 2) ?></td>
                            <td class="px-4 py-3 text-slate-700">$<?= number_format((float)$it['subtotal'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-6 flex items-center justify-between">
            <div class="text-lg font-semibold text-slate-800">Total: $<?= number_format((float)$total, 2) ?></div>
            <form method="post" action="/dental-management-system/public/invoices/store">
                <input type="hidden" name="treatment_id" value="<?= htmlspecialchars($treatment_id) ?>">
                <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                    Confirm Invoice
                </button>
            </form>
        </div>

        <div class="mt-4">
            <a href="/dental-management-system/public/treatments"
               class="rounded-xl bg-slate-100 px-4 py-2 text-slate-700 hover:bg-slate-200">
                Back to Treatments
            </a>
        </div>
    </div>
<?php else: ?>
    <form method="post" action="/dental-management-system/public/invoices/store" class="max-w-3xl">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
            <?php if (isset($appointments)): ?>
                <div class="md:col-span-2">
                    <label class="text-sm text-slate-600">Appointment</label>
                    <select name="appointment_id" required
                            class="mt-2 w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select Appointment</option>
                        <?php foreach ($appointments as $a): ?>
                            <option value="<?= (int)$a['id'] ?>">
                                #<?= (int)$a['id'] ?> | <?= htmlspecialchars($a['patient_name']) ?> | <?= htmlspecialchars($a['appointment_date']) ?> <?= htmlspecialchars(substr($a['appointment_time'], 0, 5)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php else: ?>
                <div class="md:col-span-2 relative">
                    <label class="text-sm text-slate-600">Patient</label>
                    <input type="hidden" name="patient_id" id="invoice_patient_id" required>
                    <input type="text" id="invoice_patient_search" placeholder="Type patient name..."
                           class="mt-2 w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                    <div id="invoice_patient_results"
                         class="absolute z-20 mt-2 w-full bg-white border border-slate-200 rounded-xl shadow-lg hidden max-h-48 overflow-auto">
                    </div>
                </div>
            <?php endif; ?>

            <div class="md:col-span-2">
                <label class="text-sm text-slate-600">Procedure</label>
                <select name="procedure_id" required
                        class="mt-2 w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Select Procedure</option>
                    <?php foreach ($procedures as $proc): ?>
                        <option value="<?= (int)$proc['id'] ?>">
                            <?= htmlspecialchars($proc['name']) ?> ($<?= number_format((float)$proc['price'], 2) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="text-sm text-slate-600">Quantity</label>
                <input type="number" name="qty" min="1" value="1" required
                       class="mt-2 w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
            </div>
        </div>

        <div class="mt-4 flex gap-2">
            <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                Create Invoice
            </button>
            <a href="/dental-management-system/public/invoices"
               class="rounded-xl bg-slate-100 px-4 py-2 text-slate-700 hover:bg-slate-200">
                Back
            </a>
        </div>
    </form>
<?php endif; ?>

<?php if (!isset($appointments)): ?>
<script>
const basePath = '/dental-management-system/public';
const searchInput = document.getElementById('invoice_patient_search');
const resultsBox = document.getElementById('invoice_patient_results');
const patientIdInput = document.getElementById('invoice_patient_id');
let debounceTimer = null;
let activeRequest = null;

function renderResults(list) {
    resultsBox.innerHTML = '';
    if (!list.length) {
        resultsBox.classList.add('hidden');
        return;
    }
    list.forEach(function (p) {
        const item = document.createElement('button');
        item.type = 'button';
        item.className = 'w-full text-left px-4 py-2 hover:bg-slate-50';
        item.textContent = p.name;
        item.addEventListener('click', function () {
            searchInput.value = p.name;
            patientIdInput.value = p.id;
            resultsBox.classList.add('hidden');
        });
        resultsBox.appendChild(item);
    });
    resultsBox.classList.remove('hidden');
}

if (searchInput) {
    searchInput.addEventListener('input', function () {
        const q = searchInput.value.trim();
        patientIdInput.value = '';
        if (!q) {
            resultsBox.classList.add('hidden');
            return;
        }
        if (debounceTimer) {
            clearTimeout(debounceTimer);
        }
        debounceTimer = setTimeout(function () {
            if (activeRequest && activeRequest.abort) {
                activeRequest.abort();
            }
            const controller = new AbortController();
            activeRequest = controller;

            fetch(basePath + '/patients/search?q=' + encodeURIComponent(q), { signal: controller.signal })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (Array.isArray(data)) {
                        renderResults(data);
                    } else {
                        renderResults([]);
                    }
                })
                .catch(function () {});
        }, 250);
    });

    document.addEventListener('click', function (e) {
        if (!resultsBox.contains(e.target) && e.target !== searchInput) {
            resultsBox.classList.add('hidden');
        }
    });

    document.querySelector('form').addEventListener('submit', function (e) {
        if (patientIdInput.value) {
            return;
        }
        e.preventDefault();
        if (window.Swal) {
            Swal.fire({
                icon: 'error',
                title: 'Patient not found',
                text: 'Please select a patient from the list.'
            });
        } else {
            alert('Please select a patient from the list.');
        }
    });
}
</script>
<?php endif; ?>

<?php require __DIR__ . '/../layouts/app_end.php'; ?>
