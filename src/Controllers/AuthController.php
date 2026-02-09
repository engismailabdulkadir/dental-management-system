<?php

class AuthController
{
    // ======================
    // SHOW LOGIN PAGE
    // ======================
    public function login()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // If already logged in, skip login page
        if (!empty($_SESSION['user'])) {
            $this->redirectByRole($_SESSION['user']);
        }

        require __DIR__ . '/../../views/auth/login.php';
    }

    // ======================
    // HANDLE LOGIN
    // ======================
    public function authenticate()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $pdo = require __DIR__ . '/../../config/database.php';

        $fullName = $_POST['full_name'] ?? '';
        $password = $_POST['password'] ?? '';

        // 1️⃣ Find user
        $stmt = $pdo->prepare("SELECT * FROM users WHERE full_name = ?");
        $stmt->execute([$fullName]);
        $user = $stmt->fetch();

        // 2️⃣ Verify password
        if (!$user || !password_verify($password, $user['password'])) {
            $error = "Invalid name or password";
            require __DIR__ . '/../../views/auth/login.php';
            return;
        }

        // 3️⃣ Save session
        $_SESSION['user'] = $user;
        $_SESSION['flash_success'] = 'Login successful. Welcome back!';

        // 4️⃣ Show success alert then redirect based on role
        $target = $this->getRedirectUrl($user);
        require __DIR__ . '/../../views/auth/login_success.php';
        return;
    }

    // ======================
    // LOGOUT
    // ======================
    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_destroy();
        header("Location: /dental-management-system/public/login");
        exit;
    }

    private function redirectByRole(array $user): void
    {
        if (($user['role_id'] ?? null) == 1) {
            // Admin
            header("Location: /dental-management-system/public/dashboard");
        } elseif (($user['role_id'] ?? null) == 3) {
            // Doctor
            header("Location: /dental-management-system/public/doctor-dashboard");
        } else {
            // Staff / Reception
            header("Location: /dental-management-system/public/appointments");
        }
        exit;
    }

    private function getRedirectUrl(array $user): string
    {
        if (($user['role_id'] ?? null) == 1) {
            return '/dental-management-system/public/dashboard';
        } elseif (($user['role_id'] ?? null) == 3) {
            return '/dental-management-system/public/doctor-dashboard';
        }
        return '/dental-management-system/public/appointments';
    }
}
