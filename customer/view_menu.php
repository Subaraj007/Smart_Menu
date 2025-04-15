<?php
require_once '../includes/db.php';

$rest_id = $_GET['id'] ?? '';
if (empty($rest_id)) {
    die("Restaurant ID is required");
}

// Get restaurant details safely
$restaurantStmt = $pdo->prepare("SELECT * FROM restaurants WHERE rest_id = ?");
if ($restaurantStmt->execute([$rest_id])) {
    $restaurant = $restaurantStmt->fetch();
    if (!$restaurant) {
        die("Restaurant not found");
    }
} else {
    die("Error fetching restaurant details");
}

// Get menu categories and items safely
$categoryStmt = $pdo->prepare("
    SELECT c.*, m.item_id, m.name AS item_name, m.description, m.price, m.spicy_level, m.is_vegetarian
    FROM menu_categories c
    LEFT JOIN menu_items m ON c.category_id = m.category_id
    WHERE c.rest_id = ?
    ORDER BY c.display_order, m.name
");

if ($categoryStmt->execute([$rest_id])) {
    $categories = $categoryStmt->fetchAll();
} else {
    die("Error fetching categories");
}

// Organize by category
$menuByCategory = [];
foreach ($categories as $item) {
    if (!isset($menuByCategory[$item['category_id']])) {
        $menuByCategory[$item['category_id']] = [
            'category_name' => $item['name'],
            'items' => []
        ];
    }
    if ($item['item_id']) {
        $menuByCategory[$item['category_id']]['items'][] = $item;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($restaurant['name']) ?> - Menu</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/menu.css">
</head>
<body>
    <div class="restaurant-header">
        <?php if ($restaurant['logo']): ?>
        <img src="../<?= $restaurant['logo'] ?>" alt="<?= htmlspecialchars($restaurant['name']) ?>" class="logo">
        <?php endif; ?>
        <h1><?= htmlspecialchars($restaurant['name']) ?></h1>
        <p class="description"><?= htmlspecialchars($restaurant['description']) ?></p>
    </div>
    
    <div class="menu-container">
        <?php foreach ($menuByCategory as $category): ?>
        <div class="menu-category">
            <h2><?= htmlspecialchars($category['category_name']) ?></h2>
            
            <?php if (empty($category['items'])): ?>
            <p class="empty-category">No items in this category yet</p>
            <?php else: ?>
            
            <div class="menu-items">
                <?php foreach ($category['items'] as $item): ?>
                <div class="menu-item">
                    <div class="item-info">
                        <h3><?= htmlspecialchars($item['item_name']) ?></h3>
                        <p class="description"><?= htmlspecialchars($item['description']) ?></p>
                        <div class="tags">
                            <?php if ($item['is_vegetarian']): ?>
                            <span class="tag vegetarian">Vegetarian</span>
                            <?php endif; ?>
                            <?php if ($item['spicy_level'] > 0): ?>
                            <span class="tag spicy">Spicy Level: <?= $item['spicy_level'] ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="item-price">
                        Rs. <?= number_format($item['price'], 2) ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    
    <div class="restaurant-footer">
        <p><strong>Contact:</strong> <?= htmlspecialchars($restaurant['contact']) ?></p>
        <p><strong>Address:</strong> <?= htmlspecialchars($restaurant['address']) ?></p>
        <p class="qr-credit">Scan QR code to view this menu anytime</p>
    </div>
    
    <script src="../assets/js/menu.js"></script>
</body>
</html>
