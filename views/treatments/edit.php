<?php
$pageTitle = 'Edit Treatment';
$pageSubtitle = 'Update treatment details';
require __DIR__ . '/../layouts/app_start.php';
?>

<form method="POST" action="/dental-management-system/public/treatments/update" class="max-w-3xl">
    <input type="hidden" name="id" value="<?= (int)$treatment['id'] ?>">

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="md:col-span-2">
            <label class="text-sm text-slate-600">Patient</label>
            <input type="text" value="<?= htmlspecialchars($treatment['patient_name']) ?>" readonly
                   class="mt-2 w-full rounded-xl border-slate-200 bg-slate-50 text-slate-600">
        </div>

        <div class="md:col-span-2">
            <label class="text-sm text-slate-600">Notes</label>
            <textarea name="notes" rows="4"
                      class="mt-2 w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500"><?= htmlspecialchars($treatment['notes']) ?></textarea>
        </div>
    </div>

    <div class="mt-4 flex gap-2">
        <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
            Update Treatment
        </button>
        <a href="/dental-management-system/public/treatments"
           class="rounded-xl bg-slate-100 px-4 py-2 text-slate-700 hover:bg-slate-200">
            Back
        </a>
    </div>
</form>

<script>
(function () {
    const form = document.querySelector('form');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        const notes = (form.querySelector('textarea[name="notes"]')?.value || '').trim();
        if (notes && !/^[A-Za-z\s]+$/.test(notes)) {
            e.preventDefault();
            if (window.Swal) {
                Swal.fire({ icon: 'error', title: 'Invalid Notes', text: 'Notes must contain only letters.' });
            } else {
                alert('Notes must contain only letters.');
            }
        }
    }, true);
})();
</script>

<?php require __DIR__ . '/../layouts/app_end.php'; ?>
