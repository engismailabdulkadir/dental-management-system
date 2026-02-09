<?php
$pageTitle = 'Add Appointment';
$pageSubtitle = 'Create a new appointment';
require __DIR__ . '/../layouts/app_start.php';

$basePath = '/dental-management-system/public';
?>

<form method="POST" action="/dental-management-system/public/appointments/store" class="max-w-3xl">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="relative">
            <label class="text-sm text-slate-600">Patient</label>
            <input type="hidden" name="patient_id" id="patient_id" required>
            <input type="text" id="patient_search" placeholder="Type patient name..."
                   class="mt-2 w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
            <div id="patient_results"
                 class="absolute z-20 mt-2 w-full bg-white border border-slate-200 rounded-xl shadow-lg hidden max-h-48 overflow-auto">
            </div>
        </div>

        <div>
            <label class="text-sm text-slate-600">Doctor</label>
            <select name="doctor_id" required
                    class="mt-2 w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                <option value="">Select Doctor</option>
                <?php foreach ($doctors as $d): ?>
                    <option value="<?= (int)$d['id'] ?>"><?= htmlspecialchars($d['full_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="text-sm text-slate-600">Date</label>
            <input type="date" name="appointment_date" required
                   class="mt-2 w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
        </div>

        <div>
            <label class="text-sm text-slate-600">Time</label>
            <input type="time" name="appointment_time" required
                   class="mt-2 w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
        </div>

        <div class="md:col-span-2 text-sm text-slate-500">
            Status will be set to <span class="font-semibold text-slate-700">booked</span> when saved.
        </div>
    </div>

    <div class="mt-4 flex gap-2">
        <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
            Save Appointment
        </button>
        <a href="/dental-management-system/public/appointments"
           class="rounded-xl bg-slate-100 px-4 py-2 text-slate-700 hover:bg-slate-200">
            Back
        </a>
    </div>
</form>

<script>
const basePath = '<?= $basePath ?>';
const searchInput = document.getElementById('patient_search');
const resultsBox = document.getElementById('patient_results');
const patientIdInput = document.getElementById('patient_id');
let activeRequest = null;
let debounceTimer = null;

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

        fetch(basePath + '/patients/search?q=' + encodeURIComponent(q), {
            signal: controller.signal
        })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (!Array.isArray(data)) {
                    renderResults([]);
                    return;
                }
                renderResults(data);
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
</script>

<?php require __DIR__ . '/../layouts/app_end.php'; ?>
