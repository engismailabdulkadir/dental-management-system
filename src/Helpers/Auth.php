<?php

class Auth
{
    public static function check()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user'])) {
            header("Location: /dental-management-system/public/login");
            exit;
        }
    }

    public static function admin()
    {
        self::check();
        if ($_SESSION['user']['role_id'] != 1) {
            die("Access denied");
        }
    }

    public static function doctor()
    {
        self::check();
        if ($_SESSION['user']['role_id'] != 2) {
            die("Access denied");
        }
    }

    public static function patient()
    {
        self::check();
        if ($_SESSION['user']['role_id'] != 3) {
            die("Access denied");
        }
    }
}
