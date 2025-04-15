<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

// Restrict access to admins only
check_admin_access();

$rest_id = $_GET['rest_id'] ?? '';
if (empty($rest_id)) {
    die("Restaurant ID is required");
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_item'])) {
        // Add new menu item
        $stmt = $pdo->prepare("INSERT INTO menu_items (category_id, name, description, price, spicy_level, is_vegetarian) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['category_id'],
            $_POST['name'],
            $_POST['description'],
            $_POST['price'],
            $_POST['spicy_level'] ?? 0,
            isset($_POST['is_vegetarian']) ? 1 : 0
        ]);
    } elseif (isset($_POST['update_item'])) {
        // Update existing item
        $stmt = $pdo->prepare("UPDATE menu_items SET category_id=?, name=?, description=?, price=?, spicy_level=?, is_vegetarian=? WHERE item_id=?");
        $stmt->execute([
            $_POST['category_id'],
            $_POST['name'],
            $_POST['description'],
            $_POST['price'],
            $_POST['spicy_level'] ?? 0,
            isset($_POST['is_vegetarian']) ? 1 : 0,
            $_POST['item_id']
        ]);
    } elseif (isset($_POST['delete_item'])) {
        // Delete item
        $stmt = $pdo->prepare("DELETE FROM menu_items WHERE item_id=?");
        $stmt->execute([$_POST['item_id']]);
    }
}

// Get restaurant details
$stmt = $pdo->prepare("SELECT * FROM restaurants WHERE rest_id=?");
$stmt->execute([$rest_id]);
$restaurant = $stmt->fetch();
if (!$restaurant) {
    die("Restaurant not found for ID: $rest_id");
}


// Get all categories for this restaurant
$stmt = $pdo->prepare("SELECT * FROM menu_categories WHERE rest_id=? ORDER BY display_order");
$stmt->execute([$rest_id]);
$categories = $stmt->fetchAll();
if ($categories === false) {
    die("Failed to fetch categories for restaurant ID: $rest_id");
}


// Get all menu items with their categories
$menuItems = [];
foreach ($categories as $category) {
    $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE category_id=? ORDER BY name");
    $stmt->execute([$category['category_id']]);
    $items = $stmt->fetchAll();

    if ($items) {
        $menuItems[] = [
            'category' => $category,
            'items' => $items
        ];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Menu - <?= htmlspecialchars($restaurant['name']) ?></title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="container">
        <h1>Manage Menu: <?= htmlspecialchars($restaurant['name']) ?></h1>
        
        <a href="dashboard.php" class="btn">Back to Dashboard</a>
        <a href="manage_categories.php?rest_id=<?= $rest_id ?>" class="btn">Manage Categories</a>
        
        <div class="menu-management">
            <!-- Add New Item Form -->
            <div class="add-item-form">
                <h2>Add New Menu Item</h2>
                <form method="POST">
                    <div class="form-group">
                        <label>Category:</label>
                        <select name="category_id" required>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['category_id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Item Name:</label>
                        <input type="text" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Description:</label>
                        <textarea name="description"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Price (Rs.):</label>
                        <input type="number" name="price" step="0.01" min="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Spicy Level:</label>
                        <select name="spicy_level">
                            <option value="0">Mild</option>
                            <option value="1">Low</option>
                            <option value="2">Medium</option>
                            <option value="3">Hot</option>
                            <option value="4">Very Hot</option>
                            <option value="5">Extreme</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_vegetarian"> Vegetarian
                        </label>
                    </div>
                    
                    <button type="submit" name="add_item" class="btn">Add Item</button>
                </form>
            </div>
            
            <!-- Current Menu Items -->
            <div class="current-menu">
                <h2>Current Menu Items</h2>
                
                <?php if (empty($menuItems)): ?>
                <p>No menu items found. Add some using the form above.</p>
                <?php else: ?>
                
                <?php foreach ($menuItems as $group): ?>
                <div class="category-group">
                    <h3><?= htmlspecialchars($group['category']['name']) ?></h3>
                    
                    <div class="items-list">
                        <?php foreach ($group['items'] as $item): ?>
                        <div class="menu-item">
                            <form method="POST" class="item-form">
                                <input type="hidden" name="item_id" value="<?= $item['item_id'] ?>">
                                
                                <div class="form-group">
                                    <select name="category_id">
                                        <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['category_id'] ?>" <?= $cat['category_id'] == $item['category_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['name']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <input type="text" name="name" value="<?= htmlspecialchars($item['name']) ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <textarea name="description"><?= htmlspecialchars($item['description']) ?></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <input type="number" name="price" step="0.01" min="0" value="<?= $item['price'] ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <select name="spicy_level">
                                        <?php for ($i=0; $i<=5; $i++): ?>
                                        <option value="<?= $i ?>" <?= $i == $item['spicy_level'] ? 'selected' : '' ?>>
                                            <?= $i == 0 ? 'Mild' : ($i == 5 ? 'Extreme' : $i) ?>
                                        </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label>
                                        <input type="checkbox" name="is_vegetarian" <?= $item['is_vegetarian'] ? 'checked' : '' ?>> Vegetarian
                                    </label>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit" name="update_item" class="btn btn-sm">Update</button>
                                    <button type="submit" name="delete_item" class="btn btn-sm btn-danger" onclick="return confirm('Delete this item?')">Delete</button>
                                </div>
                            </form>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/admin.js"></script>
</body>
</html>