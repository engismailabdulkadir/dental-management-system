<?php
$pageTitle = 'Dashboard';
$pageSubtitle = 'Overview of your dental management system';
$pageActions = '<a class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-white hover:bg-blue-700" href="/dental-management-system/public/appointments/create">Make Appointment</a>'
    . '<a class="inline-flex items-center gap-2 rounded-xl bg-rose-500 px-4 py-2 text-white hover:bg-rose-600" href="/dental-management-system/public/patients/create">Add Patient</a>';
require __DIR__ . '/../layouts/app_start.php';
?>

<div class="bg-gradient-to-r from-blue-600 via-blue-500 to-indigo-500 text-white rounded-2xl p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
    <div>
        <div class="text-sm text-white/80">Dental Clinic</div>
        <div class="text-2xl font-semibold">Welcome back, <?= htmlspecialchars($_SESSION['user']['full_name'] ?? 'User') ?></div>
        <div class="text-white/90">Track appointments, patients, and revenue in one place.</div>
    </div>
    <div class="flex items-center gap-3">
        <div class="h-20 w-20 rounded-2xl bg-white/15 flex items-center justify-center">
            <svg class="h-12 w-12 text-white" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M12 2a6 6 0 0 1 6 6v2a6 6 0 0 1-12 0V8a6 6 0 0 1 6-6Zm-7 18a7 7 0 0 1 14 0v1H5Z"/>
            </svg>
        </div>
        <div class="flex items-center gap-2">
            <button id="fullscreenBtn" title="Toggle fullscreen"
                    class="rounded-xl bg-white/10 text-white px-3 py-2 hover:bg-white/20 flex items-center gap-2">
                <svg id="fsIcon" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M7 14H5v4h4v-2H7v-2zM17 6h2V2h-4v2h2v2zM7 6h4V4H5v4h2V6zM17 18v-4h2v4h-4v-2h2z"/>
                </svg>
                <span class="hidden sm:inline">Fullscreen</span>
            </button>

            <a class="rounded-xl bg-white text-blue-600 px-4 py-2 font-semibold hover:bg-blue-50"
               href="/dental-management-system/public/appointments">
                View Appointments
            </a>
        </div>
    </div>
</div>

<!-- CARDS -->
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-5">
    <div class="bg-white rounded-2xl shadow-sm p-5 border border-slate-100">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-slate-500 text-sm">Patients</div>
                <div class="mt-2 text-3xl font-bold text-slate-800"><?= (int)$patientsCount ?></div>
            </div>
            <div class="h-10 w-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-3.33 0-6 1.34-6 3v2h12v-2c0-1.66-2.67-3-6-3Z"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-5 border border-slate-100">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-slate-500 text-sm">Appointments</div>
                <div class="mt-2 text-3xl font-bold text-slate-800"><?= (int)$appointmentsCount ?></div>
            </div>
            <div class="h-10 w-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M7 2a1 1 0 0 1 1 1v1h8V3a1 1 0 1 1 2 0v1h1a2 2 0 0 1 2 2v3H2V6a2 2 0 0 1 2-2h1V3a1 1 0 0 1 2 0v1h1V3a1 1 0 0 1 1-1Zm14 8v9a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-9h19Z"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-5 border border-slate-100">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-slate-500 text-sm">Treatments</div>
                <div class="mt-2 text-3xl font-bold text-slate-800"><?= (int)$treatmentsCount ?></div>
            </div>
            <div class="h-10 w-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M6 3h12a2 2 0 0 1 2 2v14l-4-3-4 3-4-3-4 3V5a2 2 0 0 1 2-2Z"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-5 border border-slate-100">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-slate-500 text-sm">Revenue</div>
                <div class="mt-2 text-3xl font-bold text-slate-800">$<?= number_format($revenue, 2) ?></div>
            </div>
            <div class="h-10 w-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M4 7h16a2 2 0 0 1 2 2v2H2V9a2 2 0 0 1 2-2Zm-2 6h20v2a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2Z"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-5 border border-slate-100">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-slate-500 text-sm">Payments</div>
                <div class="mt-2 text-3xl font-bold text-slate-800"><?= (int)$paymentsCount ?></div>
            </div>
            <div class="h-10 w-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M12 2a2 2 0 0 1 2 2v1.07a7 7 0 0 1 3.55 2.06l.76-.44a2 2 0 1 1 2 3.46l-.76.44a7 7 0 0 1 0 4.76l.76.44a2 2 0 1 1-2 3.46l-.76-.44A7 7 0 0 1 14 18.93V20a2 2 0 1 1-4 0v-1.07a7 7 0 0 1-3.55-2.06l-.76.44a2 2 0 1 1-2-3.46l.76-.44a7 7 0 0 1 0-4.76l-.76-.44a2 2 0 1 1 2-3.46l.76.44A7 7 0 0 1 10 5.07V4a2 2 0 0 1 2-2Z"/>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- LOWER GRID -->
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mt-6">

    <!-- TODAY APPOINTMENTS -->
    <section class="bg-white rounded-2xl shadow-sm p-6 xl:col-span-2 border border-slate-100">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-slate-800">Today Appointments</h2>
            <a class="text-sm text-blue-600 hover:underline" href="/dental-management-system/public/appointments">See all</a>
        </div>

        <div class="mt-4 space-y-3">
            <?php if (empty($todayAppointments)): ?>
                <div class="text-slate-500">No appointments today.</div>
            <?php else: ?>
                <?php foreach ($todayAppointments as $a): ?>
                    <div class="flex items-center justify-between border border-slate-100 rounded-xl p-4 hover:bg-slate-50">
                        <div>
                            <div class="font-semibold text-slate-800"><?= htmlspecialchars($a['patient_name']) ?></div>
                            <div class="text-sm text-slate-500">
                                Dr: <?= htmlspecialchars($a['doctor_name']) ?> - Status: <?= htmlspecialchars($a['status']) ?>
                            </div>
                        </div>
                        <div class="text-slate-700 font-semibold">
                            <?= htmlspecialchars(substr($a['appointment_time'], 0, 5)) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- NEXT PATIENT DETAILS + INVOICE STATUS -->
    <section class="bg-white rounded-2xl shadow-sm p-6 border border-slate-100">
        <h2 class="text-lg font-semibold text-slate-800">Next Patient Details</h2>

        <?php if (!$nextPatient): ?>
            <div class="mt-4 text-slate-500">No upcoming appointment today.</div>
        <?php else: ?>
            <div class="mt-4 border border-slate-100 rounded-2xl p-4">
                <div class="font-bold text-slate-800"><?= htmlspecialchars($nextPatient['full_name']) ?></div>
                <div class="text-sm text-slate-500">
                    <?= htmlspecialchars($nextPatient['address'] ?? '') ?>
                </div>

                    <?php if (!empty($nextPatient['invoice_status'])): ?>
                        <div class="mt-3 text-sm">
                            <span class="text-slate-500">Invoice Status:</span>
                            <span class="font-semibold text-slate-800"><?= htmlspecialchars(ucfirst($nextPatient['invoice_status'])) ?></span>
                        </div>
                    <?php endif; ?>

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
                        <div class="text-slate-500">Doctor</div>
                        <div class="font-semibold text-slate-800"><?= htmlspecialchars($nextPatient['doctor_name']) ?></div>
                    </div>
                </div>

                <div class="mt-4 flex gap-2">
                    <a href="/dental-management-system/public/patients"
                       class="flex-1 text-center bg-blue-600 text-white px-4 py-2 rounded-xl hover:bg-blue-700">
                        Patients
                    </a>
                    <a href="/dental-management-system/public/appointments"
                       class="flex-1 text-center bg-slate-100 text-slate-800 px-4 py-2 rounded-xl hover:bg-slate-200">
                        Schedule
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Invoice Status -->
        <div class="mt-6 grid grid-cols-2 gap-3">
            <div class="bg-emerald-50 rounded-2xl p-4 border border-emerald-100">
                <div class="text-sm text-emerald-700">Paid Invoices</div>
                <div class="text-2xl font-bold text-emerald-800"><?= (int)$paidInvoices ?></div>
            </div>
            <div class="bg-rose-50 rounded-2xl p-4 border border-rose-100">
                <div class="text-sm text-rose-700">Unpaid / Partial</div>
                <div class="text-2xl font-bold text-rose-800"><?= (int)$unpaidInvoices ?></div>
            </div>
        </div>
    </section>
</div>

<!-- RECENT PAYMENTS -->
<section class="bg-white rounded-2xl shadow-sm p-6 mt-6 border border-slate-100">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold text-slate-800">Recent Payments</h2>
        <a class="text-sm text-blue-600 hover:underline" href="/dental-management-system/public/payments">View all</a>
    </div>

    <div class="mt-3 flex items-center gap-2">
        <input id="payments_search" type="text" placeholder="Search payments by invoice ID or patient..."
               class="w-80 rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500 text-sm px-3 py-2">
        <button id="payments_clear" type="button"
                class="rounded-xl bg-slate-100 px-3 py-2 text-slate-700 hover:bg-slate-200 text-sm">Clear</button>
    </div>

    <div class="mt-4 overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-slate-500 border-b border-slate-100">
                    <th class="py-2">Payment ID</th>
                    <th class="py-2">Invoice</th>
                    <th class="py-2">Amount</th>
                    <th class="py-2">Method</th>
                    <th class="py-2">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (empty($recentPayments)): ?>
                    <tr><td class="py-3 text-slate-500" colspan="5">No payments yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($recentPayments as $pm): ?>
                        <tr>
                            <td class="py-3 font-semibold text-slate-800">#<?= (int)$pm['id'] ?></td>
                            <td class="py-3 text-slate-700">Invoice #<?= (int)$pm['invoice_id'] ?> (Total: $<?= number_format((float)$pm['total'], 2) ?>)</td>
                            <td class="py-3 text-slate-800 font-semibold">$<?= number_format((float)$pm['amount'], 2) ?></td>
                            <td class="py-3 text-slate-700"><?= htmlspecialchars($pm['method']) ?></td>
                            <td class="py-3 text-slate-500">
                                <?php
                                $st = null;
                                if (isset($pm['invoice_status']) && $pm['invoice_status'] !== null && $pm['invoice_status'] !== '') {
                                    $st = $pm['invoice_status'];
                                } else {
                                    $st = !empty($pm['paid_at']) ? 'paid' : 'unpaid';
                                }
                                $st = strtolower($st);
                                $badgeClass = 'bg-slate-100 text-slate-700';
                                if ($st === 'paid') $badgeClass = 'bg-emerald-100 text-emerald-800';
                                if ($st === 'partial') $badgeClass = 'bg-amber-100 text-amber-800';
                                if ($st === 'unpaid') $badgeClass = 'bg-rose-100 text-rose-800';
                                ?>
                                <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm <?= $badgeClass ?>"><?= htmlspecialchars(ucfirst($st)) ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<script>
    (function(){
        const basePath = '/dental-management-system/public';
        const input = document.getElementById('payments_search');
        const clearBtn = document.getElementById('payments_clear');
        const tbody = document.querySelector('section[data-section="recent-payments"] tbody') || document.querySelector('section.bg-white table tbody');
        let debounceTimer = null;

        function renderRows(items) {
            if (!tbody) return;
            tbody.innerHTML = '';
            if (!items || !items.length) {
                tbody.innerHTML = '<tr><td class="py-3 text-slate-500" colspan="5">No payments found.</td></tr>';
                return;
            }
            items.forEach(function (pm) {
                const tr = document.createElement('tr');
                tr.innerHTML =
                    '<td class="py-3 font-semibold text-slate-800">#' + (pm.id || '') + '</td>' +
                    '<td class="py-3 text-slate-700">Invoice #' + (pm.invoice_id || '') + ' (Total: $' + (Number(pm.total || 0).toFixed(2)) + ')</td>' +
                    '<td class="py-3 text-slate-800 font-semibold">$' + (Number(pm.amount || 0).toFixed(2)) + '</td>' +
                    '<td class="py-3 text-slate-700">' + (pm.method ? pm.method : '') + '</td>' +
                    '<td class="py-3 text-slate-500">' +
                        (function(){
                            var st = null;
                            if (pm.invoice_status !== undefined && pm.invoice_status !== null && pm.invoice_status !== '') st = pm.invoice_status;
                            else st = pm.paid_at ? 'paid' : 'unpaid';
                            st = String(st).toLowerCase();
                            var badgeClass = 'bg-slate-100 text-slate-700';
                            if (st === 'paid') badgeClass = 'bg-emerald-100 text-emerald-800';
                            if (st === 'partial') badgeClass = 'bg-amber-100 text-amber-800';
                            if (st === 'unpaid') badgeClass = 'bg-rose-100 text-rose-800';
                            return '<span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm ' + badgeClass + '">' + (st ? (st.charAt(0).toUpperCase() + st.slice(1)) : '') + '</span>';
                        })() +
                    '</td>';
                tbody.appendChild(tr);
            });
        }

        if (!input) return;
        input.addEventListener('input', function () {
            const q = input.value.trim();
            if (debounceTimer) clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () {
                if (!q) {
                    // reload original page to restore server-rendered rows
                    window.location.reload();
                    return;
                }
                fetch(basePath + '/payments/search?q=' + encodeURIComponent(q), {credentials: 'same-origin'})
                    .then(function (res) { return res.json(); })
                    .then(function (data) { renderRows(Array.isArray(data) ? data : []); })
                    .catch(function () { /* ignore */ });
            }, 250);
        });

        if (clearBtn) clearBtn.addEventListener('click', function () { input.value = ''; window.location.reload(); });
    })();
</script>

<script>
    (function(){
        const btn = document.getElementById('fullscreenBtn');
        const icon = document.getElementById('fsIcon');
        const appWrap = document.querySelector('.min-h-screen.flex');

        if (!btn || !icon || !appWrap) return;

        // We'll apply inline styles when toggling UI fullscreen to ensure Chrome respects them.

        function setEnterIcon() {
            icon.innerHTML = '<path d="M7 14H5v4h4v-2H7v-2zM17 6h2V2h-4v2h2v2zM7 6h4V4H5v4h2V6zM17 18v-4h2v4h-4v-2h2z"/>';
            btn.title = 'Enter fullscreen';
        }

        function setExitIcon() {
            icon.innerHTML = '<path d="M9 7H5v4H3V5a2 2 0 0 1 2-2h4v2zm6 10h4v-4h2v6a2 2 0 0 1-2 2h-6v-2zM9 17H5v4h4v-2H7v-2zm6-10h4V3h-4v2z"/>';
            btn.title = 'Exit fullscreen';
        }

        function updateIcon() {
            if (appWrap.dataset.uiFullscreen === '1') setExitIcon();
            else setEnterIcon();
        }

        // Helper: request native fullscreen (vendor-prefixed fallback)
        async function requestEnterFullscreen(){
            const el = document.documentElement;
            if (el.requestFullscreen) return el.requestFullscreen();
            if (el.webkitRequestFullscreen) return el.webkitRequestFullscreen();
            if (el.mozRequestFullScreen) return el.mozRequestFullScreen();
            if (el.msRequestFullscreen) return el.msRequestFullscreen();
            throw new Error('Fullscreen API not supported');
        }

        // Toggle behavior: normal click -> UI-only toggle; Shift+click -> native fullscreen
        btn.addEventListener('click', async function (ev) {
            try {
                if (ev.shiftKey) {
                    // request native fullscreen (user gesture)
                    if (!document.fullscreenElement) {
                        await requestEnterFullscreen();
                        // mirror UI class for consistent layout (use inline styles)
                        enterUIFull();
                    } else {
                        if (document.exitFullscreen) await document.exitFullscreen();
                        else if (document.webkitExitFullscreen) await document.webkitExitFullscreen();
                        exitUIFull();
                    }
                } else {
                    // UI-only toggle (apply/remove inline styles)
                    if (appWrap.dataset.uiFullscreen === '1') exitUIFull();
                    else enterUIFull();
                }
            } catch (e) {
                // ignore or show small guidance
                console.warn('Fullscreen toggle error', e);
            }
            updateIcon();
        });

        // Keep UI class in sync with native fullscreen changes
        document.addEventListener('fullscreenchange', function () {
            if (document.fullscreenElement) {
                enterUIFull();
            } else {
                exitUIFull();
            }
            updateIcon();
        });

        // helper functions to apply/remove inline styles
        function enterUIFull(){
            try {
                appWrap.dataset.uiFullscreen = '1';
                // apply fixed full-viewport layout
                appWrap.style.position = 'fixed';
                appWrap.style.inset = '0';
                appWrap.style.zIndex = '9999';
                appWrap.style.background = '#fff';
                appWrap.style.display = 'flex';
                appWrap.style.flexDirection = 'row';
                appWrap.style.width = '100vw';
                appWrap.style.height = '100vh';
                // ensure aside and main layout
                const aside = appWrap.querySelector('aside');
                const main = appWrap.querySelector('main');
                if (aside) aside.style.flex = '0 0 18rem';
                if (main) main.style.flex = '1';
            } catch (e) {
                console.warn('enterUIFull error', e);
            }
        }

        function exitUIFull(){
            try {
                appWrap.dataset.uiFullscreen = '0';
                appWrap.style.position = '';
                appWrap.style.inset = '';
                appWrap.style.zIndex = '';
                appWrap.style.background = '';
                appWrap.style.display = '';
                appWrap.style.flexDirection = '';
                appWrap.style.width = '';
                appWrap.style.height = '';
                const aside = appWrap.querySelector('aside');
                const main = appWrap.querySelector('main');
                if (aside) aside.style.flex = '';
                if (main) main.style.flex = '';
            } catch (e) {
                console.warn('exitUIFull error', e);
            }
        }

        // initialize icon state
        updateIcon();
    })();
</script>

<?php require __DIR__ . '/../layouts/app_end.php'; ?>
