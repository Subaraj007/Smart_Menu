<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

check_admin_access();

$rest_id = $_GET['rest_id'] ?? '';
if (empty($rest_id)) {
    die("Restaurant ID is required");
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_category'])) {
        $stmt = $pdo->prepare("INSERT INTO menu_categories (rest_id, name, display_order) VALUES (?, ?, ?)");
        $stmt->execute([$rest_id, $_POST['name'], $_POST['display_order']]);
    } elseif (isset($_POST['update_category'])) {
        $stmt = $pdo->prepare("UPDATE menu_categories SET name=?, display_order=? WHERE category_id=?");
        $stmt->execute([$_POST['name'], $_POST['display_order'], $_POST['category_id']]);
    } elseif (isset($_POST['delete_category'])) {
        // First check if category has items
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM menu_items WHERE category_id=?");
        $stmt->execute([$_POST['category_id']]);
        $hasItems = $stmt->fetchColumn();
        
        if ($hasItems > 0) {
            $error = "Cannot delete category with menu items. Please delete or move the items first.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM menu_categories WHERE category_id=?");
            $stmt->execute([$_POST['category_id']]);
        }
    }
}

// Get restaurant details
$stmt = $pdo->prepare("SELECT * FROM restaurants WHERE rest_id=?");
$stmt->execute([$rest_id]);
$restaurant = $stmt->fetch();

// Get all categories
$stmt = $pdo->prepare("SELECT * FROM menu_categories WHERE rest_id=? ORDER BY display_order, name");
$stmt->execute([$rest_id]);
$categories = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Categories - <?= htmlspecialchars($restaurant['name']) ?></title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="container">
        <h1>Manage Categories: <?= htmlspecialchars($restaurant['name']) ?></h1>
        
        <a href="manage_menu.php?rest_id=<?= $rest_id ?>" class="btn">Back to Menu</a>
        
        <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <div class="category-management">
            <!-- Add New Category Form -->
            <div class="add-category-form">
                <h2>Add New Category</h2>
                <form method="POST">
                    <div class="form-group">
                        <label>Category Name:</label>
                        <input type="text" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Display Order:</label>
                        <input type="number" name="display_order" value="0" min="0">
                    </div>
                    
                    <button type="submit" name="add_category" class="btn">Add Category</button>
                </form>
            </div>
            
            <!-- Current Categories -->
            <div class="current-categories">
                <h2>Current Categories</h2>
                
                <?php if (empty($categories)): ?>
                <p>No categories found. Add some using the form above.</p>
                <?php else: ?>
                
                <div class="categories-list">
                    <?php foreach ($categories as $category): ?>
                    <div class="category-item">
                        <form method="POST">
                            <input type="hidden" name="category_id" value="<?= $category['category_id'] ?>">
                            
                            <div class="form-group">
                                <label>Name:</label>
                                <input type="text" name="name" value="<?= htmlspecialchars($category['name']) ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Display Order:</label>
                                <input type="number" name="display_order" value="<?= $category['display_order'] ?>" min="0">
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" name="update_category" class="btn btn-sm">Update</button>
                                <button type="submit" name="delete_category" class="btn btn-sm btn-danger" onclick="return confirm('Delete this category?')">Delete</button>
                            </div>
                        </form>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
