<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-slate-100 flex items-center justify-center min-h-screen">

  <div class="bg-white w-full max-w-md p-8 rounded-2xl shadow-lg">
    <h2 class="text-2xl font-bold text-center text-blue-600 mb-6">
      Sign In
    </h2>

    <?php if (!empty($error)): ?>
      <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-4 text-sm">
        <?= htmlspecialchars($error) ?>
      </div>
      <script>
        Swal.fire({
          icon: 'error',
          title: 'Login Failed',
          text: <?= json_encode($error) ?>
        });
      </script>
    <?php endif; ?>

    <form method="POST" action="/dental-management-system/public/login/store" class="space-y-4">
      <div>
        <label class="text-sm text-slate-600">Full Name</label>
        <input
          type="text"
          name="full_name"
          required
          class="mt-2 w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
      </div>

      <div>
        <label class="text-sm text-slate-600">Password</label>
        <input
          type="password"
          name="password"
          required
          class="mt-2 w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
      </div>

      <button class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl font-semibold transition">
        Sign In
      </button>
    </form>
  </div>

</body>
</html>
