<?php
session_start();

if (!function_exists('is_admin_logged_in')) {
    function is_admin_logged_in() {
        return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
    }
}

if (!function_exists('check_admin_access')) {
    function check_admin_access() {
        if (!is_admin_logged_in()) {
            header("Location: ../index.php");
            exit;
        }
    }
}

// Simple login function (in real app, use proper password hashing)
if (!function_exists('admin_login')) {
    function admin_login($username, $password) {
        // Hardcoded for demo - replace with database check
        $valid_username = 'admin';
        $valid_password = 'password123'; // In production, use password_hash()
        
        if ($username === $valid_username && $password === $valid_password) {
            $_SESSION['admin_logged_in'] = true;
            return true;
        }
        return false;
    }
}
?>
