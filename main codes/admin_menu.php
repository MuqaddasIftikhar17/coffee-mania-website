<?php
require_once 'config.php';

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Add new category
        if (isset($_POST['add_category'])) {
            $name = $_POST['category_name'];
            $icon = $_POST['category_icon'];
            
            $stmt = $conn->prepare("INSERT INTO categories (name, icon) VALUES (:name, :icon)");
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':icon', $icon);
            $stmt->execute();
            $message = "Category added successfully!";
        }

        // Add new menu item
        if (isset($_POST['add_item'])) {
            $category_id = $_POST['item_category'];
            $name = $_POST['item_name'];
            $description = $_POST['item_description'];
            $price = $_POST['item_price'];
            $image_path = $_POST['item_image_path'];
            
            $stmt = $conn->prepare("INSERT INTO menu_items (category_id, name, description, price, image_path) 
                                   VALUES (:category_id, :name, :description, :price, :image_path)");
            $stmt->bindParam(':category_id', $category_id);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':price', $price);
            $stmt->bindParam(':image_path', $image_path);
            $stmt->execute();
            $message = "Menu item added successfully!";
        }

        // Delete item
        if (isset($_POST['delete_item'])) {
            $item_id = $_POST['item_id'];
            
            $stmt = $conn->prepare("DELETE FROM menu_items WHERE id = :id");
            $stmt->bindParam(':id', $item_id);
            $stmt->execute();
            $message = "Item deleted successfully!";
        }

        // Delete category
        if (isset($_POST['delete_category'])) {
            $category_id = $_POST['category_id'];
            
            $stmt = $conn->prepare("DELETE FROM categories WHERE id = :id");
            $stmt->bindParam(':id', $category_id);
            $stmt->execute();
            $message = "Category and all its items deleted successfully!";
        }

    } catch(PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
    $conn = null;
}

// Get current data
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get categories
    $category_stmt = $conn->prepare("SELECT * FROM categories ORDER BY id");
    $category_stmt->execute();
    $categories = $category_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get all menu items
    $item_stmt = $conn->prepare("SELECT m.*, c.name as category_name FROM menu_items m 
                                JOIN categories c ON m.category_id = c.id 
                                ORDER BY m.category_id, m.id");
    $item_stmt->execute();
    $menu_items = $item_stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
$conn = null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Panel - Coffee Mania</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="shortcut icon" href="images\favicon.png" type="image/x-icon" />
  <style>
    body { font-family: 'Inter', sans-serif; margin: 0; padding: 20px; }
    .container { max-width: 1200px; margin: 0 auto; }
    .section { margin-bottom: 40px; border: 1px solid #ddd; padding: 20px; border-radius: 5px; }
    .form-group { margin-bottom: 15px; }
    label { display: block; margin-bottom: 5px; font-weight: 600; }
    input, textarea, select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
    button { background: #4CAF50; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer; }
    button.delete { background: #f44336; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    .message { padding: 10px; margin-bottom: 20px; border-radius: 4px; }
    .success { background-color: #dff0d8; color: #3c763d; }
    .error { background-color: #f2dede; color: #a94442; }
    .back-link { display: inline-block; margin-bottom: 20px; }
  </style>
</head>
<body>
  <div class="container">
    <a href="menu.php" class="back-link">← Back to Menu</a>
    <h1>Admin Panel</h1>

    <?php if (!empty($message)): ?>
      <div class="message success"><?php echo $message; ?></div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
      <div class="message error"><?php echo $error; ?></div>
    <?php endif; ?>

    <!-- Add Category Form -->
    <div class="section">
      <h2>Add New Category</h2>
      <form method="POST">
        <div class="form-group">
          <label for="category_name">Category Name:</label>
          <input type="text" id="category_name" name="category_name" required>
        </div>
        <div class="form-group">
          <label for="category_icon">Icon (Emoji):</label>
          <input type="text" id="category_icon" name="category_icon" placeholder="e.g., ☕">
        </div>
        <button type="submit" name="add_category">Add Category</button>
      </form>
    </div>

    <!-- Add Menu Item Form -->
    <div class="section">
      <h2>Add New Menu Item</h2>
      <form method="POST">
        <div class="form-group">
          <label for="item_category">Category:</label>
          <select id="item_category" name="item_category" required>
            <?php foreach ($categories as $category): ?>
              <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="item_name">Item Name:</label>
          <input type="text" id="item_name" name="item_name" required>
        </div>
        <div class="form-group">
          <label for="item_description">Description:</label>
          <textarea id="item_description" name="item_description" rows="3" required></textarea>
        </div>
        <div class="form-group">
          <label for="item_price">Price:</label>
          <input type="number" id="item_price" name="item_price" step="0.01" min="0" required>
        </div>
        <div class="form-group">
          <label for="item_image_path">Image Path:</label>
          <input type="text" id="item_image_path" name="item_image_path" placeholder="images/filename.jpg">
        </div>
        <button type="submit" name="add_item">Add Menu Item</button>
      </form>
    </div>

    <!-- Current Categories -->
    <div class="section">
      <h2>Current Categories</h2>
      <?php if (!empty($categories)): ?>
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Icon</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($categories as $category): ?>
              <tr>
                <td><?php echo $category['id']; ?></td>
                <td><?php echo htmlspecialchars($category['name']); ?></td>
                <td><?php echo htmlspecialchars($category['icon']); ?></td>
                <td>
                  <form method="POST" style="display: inline;">
                    <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                    <button type="submit" name="delete_category" class="delete" onclick="return confirm('Are you sure? This will delete all items in this category!')">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p>No categories found.</p>
      <?php endif; ?>
    </div>

    <!-- Current Menu Items -->
    <div class="section">
      <h2>Current Menu Items</h2>
      <?php if (!empty($menu_items)): ?>
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Category</th>
              <th>Name</th>
              <th>Price</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($menu_items as $item): ?>
              <tr>
                <td><?php echo $item['id']; ?></td>
                <td><?php echo htmlspecialchars($item['category_name']); ?></td>
                <td><?php echo htmlspecialchars($item['name']); ?></td>
                <td>$<?php echo number_format($item['price'], 2); ?></td>
                <td>
                  <form method="POST" style="display: inline;">
                    <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                    <button type="submit" name="delete_item" class="delete" onclick="return confirm('Are you sure?')">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p>No menu items found.</p>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>