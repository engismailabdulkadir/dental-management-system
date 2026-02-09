<?php
session_start();

if (isset($_SESSION['user'])) {
    header("Location: /dental-management-system/public/dashboard");
    exit;
}

$pdo = require __DIR__ . '/../../config/database.php';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        empty($_POST['full_name']) ||
        empty($_POST['email']) ||
        empty($_POST['password'])
    ) {
        $error = "All fields are required";
    } else {
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$_POST['email']]);

        if ($check->fetch()) {
            $error = "Email already exists";
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO users (full_name, email, password, role_id)
                VALUES (?, ?, ?, 4)
            ");

            $stmt->execute([
                $_POST['full_name'],
                $_POST['email'],
                password_hash($_POST['password'], PASSWORD_DEFAULT)
            ]);

            header("Location: /dental-management-system/public/login");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Patient Registration</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-slate-100 flex items-center justify-center min-h-screen">

    <div class="bg-white w-full max-w-md p-8 rounded-2xl shadow-lg">
        <h2 class="text-2xl font-bold text-center text-blue-600 mb-6">
            Register as Patient
        </h2>

        <?php if ($error): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-4 text-sm">
                <?= htmlspecialchars($error) ?>
            </div>
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Registration Failed',
                    text: <?= json_encode($error) ?>
                });
            </script>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="text-sm text-slate-600">Full Name</label>
                <input type="text" name="full_name" required
                       class="mt-2 w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="text-sm text-slate-600">Email</label>
                <input type="email" name="email" required
                       class="mt-2 w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="text-sm text-slate-600">Password</label>
                <input type="password" name="password" required
                       class="mt-2 w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <button class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl font-semibold transition">
                Register
            </button>
        </form>

        <p class="mt-4 text-sm text-slate-600 text-center">
            Already have an account?
            <a class="text-blue-600 hover:underline" href="/dental-management-system/public/login">Login</a>
        </p>
    </div>

</body>
</html>
