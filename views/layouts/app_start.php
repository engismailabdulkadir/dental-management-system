<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$basePath = '/dental-management-system/public';
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '';
$currentPath = str_replace($basePath, '', $currentPath);
if ($currentPath === '') {
    $currentPath = '/';
}
if ($currentPath === '/') {
    $currentPath = '/dashboard';
}

$pageTitle = $pageTitle ?? 'Dashboard';
$pageSubtitle = $pageSubtitle ?? '';
$pageActions = $pageActions ?? '';
$showSearch = $showSearch ?? true;

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$displayName = $_SESSION['user']['full_name'] ?? 'User';
$roleId = $_SESSION['user']['role_id'] ?? 0;
$roleName = $roleId == 1 ? 'Admin' : ($roleId == 2 ? 'Doctor' : 'Staff');

function navClass($path, $currentPath)
{
    return $currentPath === $path
        ? 'bg-white/15 text-white'
        : 'text-white/85 hover:bg-white/10';
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="number"],
        input[type="date"],
        input[type="time"],
        select,
        textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            border-radius: 0.75rem;
            border: 1px solid #e2e8f0;
            background-color: #ffffff;
        }
        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

    </style>
</head>
<body class="bg-gradient-to-b from-slate-50 via-slate-50 to-slate-100 text-slate-800">

<div class="min-h-screen flex">

    <!-- SIDEBAR -->
    <aside class="w-72 bg-blue-700 text-white flex flex-col p-6">
        <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-xl bg-white/20 flex items-center justify-center font-bold text-lg">
                DM
            </div>
            <div>
                <div class="text-lg font-semibold">Dental System</div>
                <div class="text-xs text-white/70">Clinic Dashboard</div>
            </div>
        </div>
        <div class="bg-blue-800/70 rounded-2xl p-5 flex items-center gap-4 mt-6">
            <div class="h-14 w-14 rounded-full bg-white/20 flex items-center justify-center text-xl font-bold">
                <?= strtoupper(substr($displayName, 0, 1)) ?>
            </div>
            <div>
                <div class="font-semibold"><?= htmlspecialchars($displayName) ?></div>
                <div class="text-sm text-white/80"><?= htmlspecialchars($roleName) ?></div>
            </div>
        </div>

        <nav class="mt-8 space-y-2">
            <?php if ($roleId == 3): ?>
                <a class="flex items-center gap-3 px-4 py-3 rounded-xl <?= navClass('/doctor-dashboard', $currentPath) ?>"
                   href="<?= $basePath ?>/doctor-dashboard">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12 2a5 5 0 0 1 5 5v2a5 5 0 0 1-10 0V7a5 5 0 0 1 5-5Zm-7 18a7 7 0 0 1 14 0v1H5Z"/>
                    </svg>
                    <span class="font-medium">Dashboard</span>
                </a>

                <a class="flex items-center gap-3 px-4 py-3 rounded-xl <?= navClass('/appointments', $currentPath) ?>"
                   href="<?= $basePath ?>/appointments">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M7 2a1 1 0 0 1 1 1v1h8V3a1 1 0 1 1 2 0v1h1a2 2 0 0 1 2 2v3H2V6a2 2 0 0 1 2-2h1V3a1 1 0 0 1 2 0v1h1V3a1 1 0 0 1 1-1Zm14 8v9a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-9h19Z"/>
                    </svg>
                    <span>Appointments</span>
                </a>

                <a class="flex items-center gap-3 px-4 py-3 rounded-xl <?= navClass('/treatments', $currentPath) ?>"
                   href="<?= $basePath ?>/treatments">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M6 3h12a2 2 0 0 1 2 2v14l-4-3-4 3-4-3-4 3V5a2 2 0 0 1 2-2Z"/>
                    </svg>
                    <span>Treatments</span>
                </a>

                <a class="flex items-center gap-3 px-4 py-3 rounded-xl <?= navClass('/patients', $currentPath) ?>"
                   href="<?= $basePath ?>/patients">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-3.33 0-6 1.34-6 3v2h12v-2c0-1.66-2.67-3-6-3Z"/>
                    </svg>
                    <span>Patients</span>
                </a>

                <a class="flex items-center gap-3 px-4 py-3 rounded-xl <?= navClass('/medical-histories', $currentPath) ?>"
                   href="<?= $basePath ?>/medical-histories">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M7 3h8l4 4v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Zm8 1.5V8h3.5"/>
                    </svg>
                    <span>Medical Histories</span>
                </a>
            <?php else: ?>
                <a class="flex items-center gap-3 px-4 py-3 rounded-xl <?= navClass('/dashboard', $currentPath) ?>"
                   href="<?= $basePath ?>/dashboard">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M4 10.5V19a1 1 0 0 0 1 1h5v-6h4v6h5a1 1 0 0 0 1-1v-8.5a1 1 0 0 0-.4-.8l-7-5.25a1 1 0 0 0-1.2 0l-7 5.25a1 1 0 0 0-.4.8Z"/>
                    </svg>
                    <span class="font-medium">Dashboard</span>
                </a>

                <a class="flex items-center gap-3 px-4 py-3 rounded-xl <?= navClass('/patients', $currentPath) ?>"
                   href="<?= $basePath ?>/patients">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-3.33 0-6 1.34-6 3v2h12v-2c0-1.66-2.67-3-6-3Z"/>
                    </svg>
                    <span>Patients</span>
                </a>

                <a class="flex items-center gap-3 px-4 py-3 rounded-xl <?= navClass('/appointments', $currentPath) ?>"
                   href="<?= $basePath ?>/appointments">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M7 2a1 1 0 0 1 1 1v1h8V3a1 1 0 1 1 2 0v1h1a2 2 0 0 1 2 2v3H2V6a2 2 0 0 1 2-2h1V3a1 1 0 0 1 2 0v1h1V3a1 1 0 0 1 1-1Zm14 8v9a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-9h19Z"/>
                    </svg>
                    <span>Appointments</span>
                </a>

                <a class="flex items-center gap-3 px-4 py-3 rounded-xl <?= navClass('/doctors', $currentPath) ?>"
                   href="<?= $basePath ?>/doctors">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12 2a5 5 0 0 1 5 5v2a5 5 0 0 1-10 0V7a5 5 0 0 1 5-5Zm-7 18a7 7 0 0 1 14 0v1H5Z"/>
                    </svg>
                    <span>Doctors</span>
                </a>

                <a class="flex items-center gap-3 px-4 py-3 rounded-xl <?= navClass('/treatments', $currentPath) ?>"
                   href="<?= $basePath ?>/treatments">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M6 3h12a2 2 0 0 1 2 2v14l-4-3-4 3-4-3-4 3V5a2 2 0 0 1 2-2Z"/>
                    </svg>
                    <span>Treatments</span>
                </a>

                <a class="flex items-center gap-3 px-4 py-3 rounded-xl <?= navClass('/medical-histories', $currentPath) ?>"
                   href="<?= $basePath ?>/medical-histories">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M7 3h8l4 4v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Zm8 1.5V8h3.5"/>
                    </svg>
                    <span>Medical Histories</span>
                </a>

                <a class="flex items-center gap-3 px-4 py-3 rounded-xl <?= navClass('/allergies', $currentPath) ?>"
                   href="<?= $basePath ?>/allergies">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12 2a2 2 0 0 1 2 2v1.07a7 7 0 0 1 3.55 2.06l.76-.44a2 2 0 1 1 2 3.46l-.76.44a7 7 0 0 1 0 4.76l.76.44a2 2 0 1 1-2 3.46l-.76-.44A7 7 0 0 1 14 18.93V20a2 2 0 1 1-4 0v-1.07a7 7 0 0 1-3.55-2.06l-.76.44a2 2 0 1 1-2-3.46l.76-.44a7 7 0 0 1 0-4.76l-.76-.44a2 2 0 1 1 2-3.46l.76.44A7 7 0 0 1 10 5.07V4a2 2 0 0 1 2-2Z"/>
                    </svg>
                    <span>Allergies</span>
                </a>

                <a class="flex items-center gap-3 px-4 py-3 rounded-xl <?= navClass('/procedures', $currentPath) ?>"
                   href="<?= $basePath ?>/procedures">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12 2a2 2 0 0 1 2 2v1.07a7 7 0 0 1 3.55 2.06l.76-.44a2 2 0 1 1 2 3.46l-.76.44a7 7 0 0 1 0 4.76l.76.44a2 2 0 1 1-2 3.46l-.76-.44A7 7 0 0 1 14 18.93V20a2 2 0 1 1-4 0v-1.07a7 7 0 0 1-3.55-2.06l-.76.44a2 2 0 1 1-2-3.46l.76-.44a7 7 0 0 1 0-4.76l-.76-.44a2 2 0 1 1 2-3.46l.76.44A7 7 0 0 1 10 5.07V4a2 2 0 0 1 2-2Z"/>
                    </svg>
                    <span>Procedures</span>
                </a>

            <?php if ($roleId == 1): ?>
                <a class="flex items-center gap-3 px-4 py-3 rounded-xl <?= navClass('/permissions', $currentPath) ?>"
                   href="<?= $basePath ?>/permissions">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12 2a2 2 0 0 1 2 2v1.07a7 7 0 0 1 3.55 2.06l.76-.44a2 2 0 1 1 2 3.46l-.76.44a7 7 0 0 1 0 4.76l.76.44a2 2 0 1 1-2 3.46l-.76-.44A7 7 0 0 1 14 18.93V20a2 2 0 1 1-4 0v-1.07a7 7 0 0 1-3.55-2.06l-.76.44a2 2 0 1 1-2-3.46l.76-.44a7 7 0 0 1 0-4.76l-.76-.44a2 2 0 1 1 2-3.46l.76.44A7 7 0 0 1 10 5.07V4a2 2 0 0 1 2-2Z"/>
                    </svg>
                    <span>Permissions</span>
                </a>
                <a class="flex items-center gap-3 px-4 py-3 rounded-xl <?= navClass('/users', $currentPath) ?>"
                   href="<?= $basePath ?>/users">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-3.33 0-6 1.34-6 3v2h12v-2c0-1.66-2.67-3-6-3Z"/>
                    </svg>
                    <span>Users</span>
                </a>
                <a class="flex items-center gap-3 px-4 py-3 rounded-xl <?= navClass('/invoices', $currentPath) ?>"
                   href="<?= $basePath ?>/invoices">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M6 2h9l5 5v13a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2Zm8 1.5V8h4.5"/>
                    </svg>
                    <span>Invoices</span>
                </a>
                <a class="flex items-center gap-3 px-4 py-3 rounded-xl <?= navClass('/payments', $currentPath) ?>"
                   href="<?= $basePath ?>/payments">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M4 7h16a2 2 0 0 1 2 2v2H2V9a2 2 0 0 1 2-2Zm-2 6h20v2a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2Z"/>
                    </svg>
                    <span>Payments</span>
                </a>
            <?php endif; ?>
            <?php endif; ?>
        </nav>

        <div class="mt-auto pt-6">
            <a class="block text-center bg-white/15 hover:bg-white/20 px-4 py-3 rounded-xl"
               href="<?= $basePath ?>/logout"
               data-confirm="Ma hubtaa inaad ka baxayso (logout)?">
                Logout
            </a>
        </div>
    </aside>

    <!-- MAIN -->
    <main class="flex-1 p-8">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-800"><?= htmlspecialchars($pageTitle) ?></h1>
                <?php if ($pageSubtitle): ?>
                    <p class="text-slate-500 text-sm"><?= htmlspecialchars($pageSubtitle) ?></p>
                <?php endif; ?>
            </div>

            <div class="flex items-center gap-3">
                <?php if ($showSearch): ?>
                    <div class="hidden md:flex items-center gap-2 bg-white border border-slate-200 rounded-xl px-3 py-2 shadow-sm">
                        <svg class="h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M10 2a8 8 0 1 1-5.29 14l-3.7 3.7a1 1 0 0 1-1.42-1.42l3.7-3.7A8 8 0 0 1 10 2Zm0 2a6 6 0 1 0 0 12 6 6 0 0 0 0-12Z"/>
                        </svg>
                        <input id="global_search" class="text-sm outline-none" type="text" placeholder="Search..." aria-label="Global search">
                    </div>
                <?php endif; ?>

                <?php if ($pageActions): ?>
                    <div class="flex items-center gap-2">
                        <?= $pageActions ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="mt-6">
