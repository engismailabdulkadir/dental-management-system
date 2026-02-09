<?php
$pageTitle = 'Doctor Dashboard';
$pageSubtitle = 'Your daily overview';
require __DIR__ . '/../layouts/app_start.php';
?>

<div class="grid grid-cols-1 xl:grid-cols-5 gap-6 mb-6">
    <section class="xl:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <div class="flex flex-col items-center text-center">
            <?php if (!empty($doctor['photo'])): ?>
                <img src="/dental-management-system/public/<?= htmlspecialchars($doctor['photo']) ?>"
                     class="h-28 w-28 rounded-full object-cover border-4 border-blue-100" alt="Doctor">
            <?php else: ?>
                <div class="h-28 w-28 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-3xl font-bold">
                    <?= strtoupper(substr($doctor['full_name'] ?? 'D', 0, 1)) ?>
                </div>
            <?php endif; ?>
            <div class="mt-4 text-lg font-semibold text-slate-800"><?= htmlspecialchars($doctor['full_name'] ?? 'Doctor') ?></div>
            <div class="text-sm text-slate-500"><?= htmlspecialchars($doctor['specialization'] ?? 'Specialist') ?></div>
            <div class="mt-4 w-full border-t border-slate-100 pt-4">
                <div class="text-xs text-slate-500 uppercase tracking-wide">Today</div>
                <div class="mt-2 text-2xl font-bold text-slate-800"><?= (int)$todayAppointmentsCount ?></div>
                <div class="text-sm text-slate-500">Appointments</div>
            </div>
            <a class="mt-4 w-full rounded-xl bg-blue-600 text-white px-4 py-2 font-semibold hover:bg-blue-700"
               href="/dental-management-system/public/appointments">
                Make Appointment
            </a>
        </div>
    </section>

    <section class="xl:col-span-3 bg-gradient-to-r from-blue-600 via-blue-500 to-indigo-500 text-white rounded-2xl p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="text-sm text-white/80">Doctor Dashboard</div>
            <div class="text-2xl font-semibold">Welcome back, <?= htmlspecialchars($doctor['full_name'] ?? 'Doctor') ?></div>
            <div class="text-white/90">Track patients and appointments for today.</div>
        </div>
        <div class="flex items-center gap-3">
            <div class="h-14 w-14 rounded-2xl bg-white/15 flex items-center justify-center">
                <svg class="h-7 w-7 text-white" viewBox="0 0 24 24" fill="currentColor"><path d="M7 2a1 1 0 0 1 1 1v1h8V3a1 1 0 1 1 2 0v1h1a2 2 0 0 1 2 2v3H2V6a2 2 0 0 1 2-2h1V3a1 1 0 0 1 2 0v1h1V3a1 1 0 0 1 1-1Zm14 8v9a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-9h19Z"/></svg>
            </div>
            <a class="rounded-xl bg-white text-blue-600 px-4 py-2 font-semibold hover:bg-blue-50"
               href="/dental-management-system/public/treatments/create">
                Add Treatment
            </a>
        </div>
    </section>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-5">
    <div class="bg-white rounded-2xl shadow-sm p-5 border border-slate-100">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-slate-500 text-sm">Total Patients</div>
                <div class="mt-2 text-3xl font-bold text-slate-800"><?= (int)$totalPatients ?></div>
            </div>
            <div class="h-10 w-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-3.33 0-6 1.34-6 3v2h12v-2c0-1.66-2.67-3-6-3Z"/></svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-5 border border-slate-100">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-slate-500 text-sm">Today Appointments</div>
                <div class="mt-2 text-3xl font-bold text-slate-800"><?= (int)$todayAppointmentsCount ?></div>
            </div>
            <div class="h-10 w-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M7 2a1 1 0 0 1 1 1v1h8V3a1 1 0 1 1 2 0v1h1a2 2 0 0 1 2 2v3H2V6a2 2 0 0 1 2-2h1V3a1 1 0 0 1 2 0v1h1V3a1 1 0 0 1 1-1Zm14 8v9a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-9h19Z"/></svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-5 border border-slate-100">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-slate-500 text-sm">Total Treatments</div>
                <div class="mt-2 text-3xl font-bold text-slate-800"><?= (int)$totalTreatments ?></div>
            </div>
            <div class="h-10 w-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M6 3h12a2 2 0 0 1 2 2v14l-4-3-4 3-4-3-4 3V5a2 2 0 0 1 2-2Z"/></svg>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mt-6">
    <section class="bg-white rounded-2xl shadow-sm p-6 border border-slate-100">
        <h2 class="text-lg font-semibold text-slate-800">Patients Summary</h2>
        <div class="mt-4 flex items-center justify-center">
            <div class="h-40 w-40 rounded-full" style="background: conic-gradient(#2563eb 0% 55%, #93c5fd 55% 80%, #fbbf24 80% 100%);"></div>
        </div>
        <div class="mt-4 text-sm text-slate-500">New vs. returning patients (summary)</div>
    </section>

    <section class="bg-white rounded-2xl shadow-sm p-6 xl:col-span-2 border border-slate-100">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-slate-800">Upcoming Appointments</h2>
            <a class="text-sm text-blue-600 hover:underline" href="/dental-management-system/public/appointments">See all</a>
        </div>

        <div class="mt-4 space-y-3">
            <?php if (empty($upcomingAppointments)): ?>
                <div class="text-slate-500">No upcoming appointments.</div>
            <?php else: ?>
                <?php foreach ($upcomingAppointments as $a): ?>
                    <div class="flex items-center justify-between border border-slate-100 rounded-xl p-4 hover:bg-slate-50">
                        <div>
                            <div class="font-semibold text-slate-800"><?= htmlspecialchars($a['patient_name']) ?></div>
                            <div class="text-sm text-slate-500">
                                <?= htmlspecialchars($a['appointment_date']) ?> - <?= htmlspecialchars(substr($a['appointment_time'], 0, 5)) ?>
                            </div>
                        </div>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-700">
                            <?= htmlspecialchars($a['status']) ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <section class="bg-white rounded-2xl shadow-sm p-6 border border-slate-100">
        <h2 class="text-lg font-semibold text-slate-800">Next Patient</h2>

        <?php if (!$nextPatient): ?>
            <div class="mt-4 text-slate-500">No upcoming appointment today.</div>
        <?php else: ?>
            <div class="mt-4 border border-slate-100 rounded-2xl p-4">
                <div class="font-bold text-slate-800"><?= htmlspecialchars($nextPatient['full_name']) ?></div>
                <div class="text-sm text-slate-500"><?= htmlspecialchars($nextPatient['address'] ?? '') ?></div>

                <div class="grid grid-cols-2 gap-3 mt-4 text-sm">
                    <div class="bg-slate-50 rounded-xl p-3">
                        <div class="text-slate-500">D.O.B</div>
                        <div class="font-semibold text-slate-800"><?= htmlspecialchars($nextPatient['date_of_birth'] ?? '-') ?></div>
                    </div>
                    <div class="bg-slate-50 rounded-xl p-3">
                        <div class="text-slate-500">Gender</div>
                        <div class="font-semibold text-slate-800"><?= htmlspecialchars($nextPatient['gender'] ?? '-') ?></div>
                    </div>
                    <div class="bg-slate-50 rounded-xl p-3">
                        <div class="text-slate-500">Time</div>
                        <div class="font-semibold text-slate-800"><?= htmlspecialchars(substr($nextPatient['appointment_time'], 0, 5)) ?></div>
                    </div>
                    <div class="bg-slate-50 rounded-xl p-3">
                        <div class="text-slate-500">Phone</div>
                        <div class="font-semibold text-slate-800"><?= htmlspecialchars($nextPatient['phone'] ?? '-') ?></div>
                    </div>
                </div>

                <div class="mt-4">
                    <a href="/dental-management-system/public/patients"
                       class="block text-center bg-blue-600 text-white px-4 py-2 rounded-xl hover:bg-blue-700">
                        View Patients
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php require __DIR__ . '/../layouts/app_end.php'; ?>
