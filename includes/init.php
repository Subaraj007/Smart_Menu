<?php
// Initialize application settings
session_start();
date_default_timezone_set('Asia/Colombo');

// Environment configuration
define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/smartmenu/');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('QR_DIR', __DIR__ . '/../qr_codes/');

// Create directories if they don't exist
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}
if (!file_exists(QR_DIR)) {
    mkdir(QR_DIR, 0755, true);
}

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

// Load dependencies
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';
?>