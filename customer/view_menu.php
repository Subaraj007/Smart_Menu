<?php
require_once '../includes/db.php';

$rest_id = $_GET['id'] ?? '';
if (empty($rest_id)) {
    die("Restaurant ID is required");
}

// Get restaurant details
$restaurantStmt = $pdo->prepare("SELECT * FROM restaurants WHERE rest_id = ?");
if ($restaurantStmt->execute([$rest_id])) {
    $restaurant = $restaurantStmt->fetch();
    if (!$restaurant) {
        die("Restaurant not found");
    }
} else {
    die("Error fetching restaurant details");
}

// Get menu categories and items
$categoryStmt = $pdo->prepare("
    SELECT c.*, m.item_id, m.name AS item_name, m.description, m.price, m.spicy_level, m.is_vegetarian, m.image_path
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
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
            background: #f7f7f7;
        }
        .top-bar {
            background: white;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #ddd;
        }
        .top-bar .info {
            line-height: 1.4;
        }
        .top-bar .icons i {
            font-size: 20px;
            margin-left: 15px;
            color: #333;
        }
        .category-tabs {
            overflow-x: auto;
            white-space: nowrap;
            padding: 10px;
            background: #fff;
        }
        .category-tabs button {
            background: #eee;
            border: none;
            padding: 8px 16px;
            border-radius: 20px;
            margin-right: 8px;
            cursor: pointer;
            font-weight: bold;
        }
        .category-tabs button.active {
            background: #ff7043;
            color: white;
        }
        .category-tabs button:hover {
            background: #ff7043;
            color: white;
        }
        .menu-category {
            padding: 10px 15px;
            display: none; /* Hide all categories by default */
        }
        .menu-category.active {
            display: block; /* Show active category */
        }
        .menu-card {
            background: white;
            margin-bottom: 15px;
            border-radius: 12px;
            display: flex;
            padding: 10px;
            align-items: flex-start;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .menu-card img {
            width: 80px;
            height: 80px;
            border-radius: 10px;
            object-fit: cover;
            margin-right: 10px;
        }
        .menu-details {
            flex: 1;
        }
        .menu-details h3 {
            margin: 0 0 5px 0;
            font-size: 16px;
        }
        .menu-details p {
            margin: 0;
            color: #777;
            font-size: 13px;
        }
        .price {
            color: #e65100;
            font-weight: bold;
            margin-top: 6px;
        }
        .heart {
            color: #ccc;
            font-size: 20px;
            margin-left: 8px;
        }
        .footer {
            text-align: center;
            font-size: 13px;
            color: #999;
            padding: 20px 10px;
        }
    </style>
</head>
<body>

    <div class="top-bar">
        <div class="info">
            <strong><?= htmlspecialchars($restaurant['name']) ?></strong><br>
            <?= htmlspecialchars($restaurant['address']) ?><br>
            <?= htmlspecialchars($restaurant['contact']) ?>
        </div>
        <div class="icons">
            <i class="fas fa-map-marker-alt"></i>
            <i class="fas fa-phone"></i>
        </div>
    </div>

    <div class="category-tabs">
        <?php 
        $first = true;
        foreach ($menuByCategory as $catId => $cat): ?>
            <button class="<?= $first ? 'active' : '' ?>" 
                    data-category="<?= $catId ?>">
                <?= htmlspecialchars($cat['category_name']) ?>
            </button>
            <?php $first = false; ?>
        <?php endforeach; ?>
    </div>

    <?php 
    $first = true;
    foreach ($menuByCategory as $catId => $category): ?>
    <div class="menu-category <?= $first ? 'active' : '' ?>" 
         id="category-<?= $catId ?>">
        <h2><?= htmlspecialchars($category['category_name']) ?></h2>
        <?php if (empty($category['items'])): ?>
            <p>No items in this category yet</p>
        <?php else: ?>
            <?php foreach ($category['items'] as $item): ?>
            <div class="menu-card">
                <img src="../<?= $item['image_path'] ?? 'uploads/images/placeholder.png' ?>" alt="<?= htmlspecialchars($item['item_name']) ?>">
                <div class="menu-details">
                    <h3><?= htmlspecialchars($item['item_name']) ?> <span class="heart">&#9825;</span></h3>
                    <p><?= htmlspecialchars($item['description']) ?></p>
                    <div class="tags">
                        <?php if ($item['is_vegetarian']): ?>
                            <span style="color:green;">Vegetarian</span>
                        <?php endif; ?>
                        <?php if ($item['spicy_level'] > 0): ?>
                            <span style="color:red;">Spicy Level: <?= $item['spicy_level'] ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="price">Rs. <?= number_format($item['price'], 2) ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php 
    $first = false;
    endforeach; ?>

    <div class="footer" style="background-color: black; color: white; padding: 10px; text-align: center; margin-top: 15px;">
        Scan QR code to view this menu anytime.
        <div style="background-color: black; color: white; padding: 10px; text-align: center; margin-top: 15px;">
            Copyright © 2025 DigitalQAM Website. All Rights Reserved.
        </div>
    </div>


    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.category-tabs button');
            
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    // Remove active class from all tabs and categories
                    document.querySelectorAll('.category-tabs button').forEach(t => {
                        t.classList.remove('active');
                    });
                    document.querySelectorAll('.menu-category').forEach(c => {
                        c.classList.remove('active');
                    });
                    
                    // Add active class to clicked tab and corresponding category
                    this.classList.add('active');
                    const categoryId = this.getAttribute('data-category');
                    document.getElementById(`category-${categoryId}`).classList.add('active');
                });
            });
        });
    </script>
</body>
</html>
