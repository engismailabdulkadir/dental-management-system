<?php
$pageTitle = 'Add Patient';
$pageSubtitle = 'Create a new patient record';
require __DIR__ . '/../layouts/app_start.php';
?>

<form method="POST" action="/dental-management-system/public/patients/store" class="max-w-3xl">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
            <label class="text-sm text-slate-600">Full Name</label>
            <input type="text" name="full_name" required
                   class="mt-2 w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
        </div>

        <div>
            <label class="text-sm text-slate-600">Gender</label>
            <select name="gender" required
                    class="mt-2 w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                <option value="">Select</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
            </select>
        </div>

        <div>
            <label class="text-sm text-slate-600">Date of Birth</label>
                 <input type="date" name="date_of_birth" required
                     class="mt-2 w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
        </div>

        <div>
            <label class="text-sm text-slate-600">Phone</label>
                 <input type="text" name="phone" required
                     class="mt-2 w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
        </div>

        <div class="md:col-span-2">
            <label class="text-sm text-slate-600">Address</label>
            <textarea name="address" rows="3" required
                      class="mt-2 w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500"></textarea>
        </div>
    </div>

    <div class="mt-4 flex gap-2">
        <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
            Save Patient
        </button>
        <a href="/dental-management-system/public/patients"
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
        const address = (form.querySelector('textarea[name="address"]')?.value || '').trim();
        if (address.length <= 5 || !/[A-Za-z]/.test(address)) {
            e.preventDefault();
            if (window.Swal) {
                Swal.fire({ icon: 'error', title: 'Invalid Address', text: 'Address must be at least 6 characters and include at least one letter.' });
            } else {
                alert('Address must be at least 6 characters and include at least one letter.');
            }
        }
    }, true);
})();
</script>

<?php require __DIR__ . '/../layouts/app_end.php'; ?>
