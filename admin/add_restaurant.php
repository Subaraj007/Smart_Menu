<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

check_admin_access();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name']);
    $description = sanitizeInput($_POST['description']);
    $contact = sanitizeInput($_POST['contact']);
    $address = sanitizeInput($_POST['address']);
    
    // Generate restaurant ID
    $rest_id = generateRestID($pdo);
    
    // Handle logo upload
    $logoPath = null;
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/logos/';
        $logoName = uniqid() . '_' . basename($_FILES['logo']['name']);
        $logoPath = 'uploads/logos/' . $logoName;
        
        if (!move_uploaded_file($_FILES['logo']['tmp_name'], $uploadDir . $logoName)) {
            $error = "Failed to upload logo";
        }
    }
    
    if (!isset($error)) {
        // Insert restaurant
        $stmt = $pdo->prepare("INSERT INTO restaurants (rest_id, name, description, contact, address, logo) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$rest_id, $name, $description, $contact, $address, $logoPath]);
        
        // Generate QR code
        $qrUrl = "http://" . $_SERVER['HTTP_HOST'] . "/customer/view_menu.php?id=" . $rest_id;
        $qrFilename = 'qr_codes/restaurant_' . $rest_id . '.png';
        generateQRCode($qrUrl, '../' . $qrFilename);
        
        // Update restaurant with QR code path
        $pdo->prepare("UPDATE restaurants SET qr_code = ? WHERE rest_id = ?")->execute([$qrFilename, $rest_id]);
        
        header("Location: dashboard.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add New Restaurant</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="container">
        <h1>Add New Restaurant</h1>
        
        <a href="dashboard.php" class="btn">Back to Dashboard</a>
        
        <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data" class="restaurant-form">
            <div class="form-group">
                <label>Restaurant Name *</label>
                <input type="text" name="name" required>
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="3"></textarea>
            </div>
            
            <div class="form-group">
                <label>Contact Number *</label>
                <input type="text" name="contact" required>
            </div>
            
            <div class="form-group">
                <label>Address *</label>
                <textarea name="address" rows="2" required></textarea>
            </div>
            
            <div class="form-group">
                <label>Logo</label>
                <input type="file" name="logo" accept="image/*">
                <small>Recommended size: 300x300px</small>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Restaurant</button>
            </div>
        </form>
    </div>
</body>
</html>