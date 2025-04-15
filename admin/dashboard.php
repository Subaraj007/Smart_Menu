<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

check_admin_access();

// Get all restaurants
$restaurants = $pdo->query("SELECT * FROM restaurants ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="container">
        <h1>SmartMenu Admin Dashboard</h1>
        
        <div class="header-actions">
            <a href="add_restaurant.php" class="btn btn-primary">Add New Restaurant</a>
            <a href="../includes/logout.php" class="btn btn-secondary">Logout</a>
        </div>
        
        <div class="restaurants-list">
            <h2>Your Restaurants</h2>
            
            <?php if (empty($restaurants)): ?>
            <p>No restaurants found. Add your first restaurant.</p>
            <?php else: ?>
            
            <div class="restaurant-cards">
                <?php foreach ($restaurants as $restaurant): ?>
                <div class="restaurant-card">
                    <div class="card-header">
                        <?php if ($restaurant['logo']): ?>
                        <img src="../<?= $restaurant['logo'] ?>" alt="<?= htmlspecialchars($restaurant['name']) ?>">
                        <?php endif; ?>
                        <h3><?= htmlspecialchars($restaurant['name']) ?></h3>
                    </div>
                    
                    <div class="card-body">
                        <p><strong>ID:</strong> <?= $restaurant['rest_id'] ?></p>
                        <p><strong>Added:</strong> <?= date('M d, Y', strtotime($restaurant['created_at'])) ?></p>
                    </div>
                    
                    <div class="card-actions">
                        <a href="manage_menu.php?rest_id=<?= $restaurant['rest_id'] ?>" class="btn btn-sm">Manage Menu</a>
                        <a href="../customer/view_menu.php?id=<?= $restaurant['rest_id'] ?>" target="_blank" class="btn btn-sm">View Menu</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <?php endif; ?>
        </div>
    </div>
</body>
</html>