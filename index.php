<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Redirect to appropriate page
if (is_admin_logged_in()) {
    header("Location: admin/dashboard.php");
} else {
    header("Location: customer/view_menu.php");
}
exit;
?>