<?php
$pageTitle = 'Edit Medical History';
$pageSubtitle = 'Update record';
require __DIR__ . '/../layouts/app_start.php';
?>

<form method="POST" action="/dental-management-system/public/medical-histories/update" class="max-w-3xl">
    <input type="hidden" name="id" value="<?= (int)$history['id'] ?>">

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 grid grid-cols-1 gap-5">
        <div class="relative">
            <label class="text-sm text-slate-600">Patient</label>
            <input type="hidden" name="patient_id" id="mh_patient_id" value="<?= (int)$history['patient_id'] ?>" required>
            <input type="text" id="mh_patient_search" value="<?= htmlspecialchars($history['patient_name']) ?>" placeholder="Type patient name..."
                   class="mt-2 w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
            <div id="mh_patient_results"
                 class="absolute z-20 mt-2 w-full bg-white border border-slate-200 rounded-xl shadow-lg hidden max-h-48 overflow-auto">
            </div>
        </div>

        <div>
            <label class="text-sm text-slate-600">Description</label>
            <textarea name="description" rows="4" required
                      class="mt-2 w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500"><?= htmlspecialchars($history['description']) ?></textarea>
        </div>
    </div>

    <div class="mt-4 flex gap-2">
        <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
            Update Record
        </button>
        <a href="/dental-management-system/public/medical-histories"
           class="rounded-xl bg-slate-100 px-4 py-2 text-slate-700 hover:bg-slate-200">
            Back
        </a>
    </div>
</form>

<script>
const basePath = '/dental-management-system/public';
const searchInput = document.getElementById('mh_patient_search');
const resultsBox = document.getElementById('mh_patient_results');
const patientIdInput = document.getElementById('mh_patient_id');
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
}
</script>

<?php require __DIR__ . '/../layouts/app_end.php'; ?>
