<?php
require_once __DIR__ . '/../vendor/autoload.php';

if (!function_exists('generateRestID')) {
    function generateRestID($pdo) {
        $stmt = $pdo->query("SELECT rest_id FROM restaurants ORDER BY rest_id DESC LIMIT 1");
        $lastID = $stmt->fetchColumn();
        
        if ($lastID) {
            $num = (int) substr($lastID, 1);  // Extract "0100" from "R0100"
            $nextNum = $num + 1;
            return 'R' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
        }
        return 'R0100';
    }

}


if (!function_exists('generateQRCode')) {
    function generateQRCode($data, $filename) {
        $qrCode = new Endroid\QrCode\QrCode($data);
        $qrCode->setSize(300);
        $qrCode->setMargin(10);
        $qrCode->writeFile($filename);
    }
}


if (!function_exists('sanitizeInput')) {
    function sanitizeInput($data) {
        return htmlspecialchars(strip_tags(trim($data)));
    }
}


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

?>