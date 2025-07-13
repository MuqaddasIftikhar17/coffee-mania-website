<?php
session_start();
require_once 'config.php';

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add to cart functionality
if (isset($_POST['add_to_cart'])) {
    $item_id = $_POST['item_id'];
    $item_name = $_POST['item_name'];
    $item_price = $_POST['item_price'];
    
    // Check if item already in cart
    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] == $item_id) {
            $item['quantity']++;
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        $_SESSION['cart'][] = [
            'id' => $item_id,
            'name' => $item_name,
            'price' => $item_price,
            'quantity' => 1
        ];
    }
    
    // Set success message
    $_SESSION['cart_message'] = "$item_name added to cart!";
    header("Location: menu.php");
    exit();
}

// Remove from cart functionality
if (isset($_GET['remove_from_cart'])) {
    $item_id = $_GET['remove_from_cart'];
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['id'] == $item_id) {
            unset($_SESSION['cart'][$key]);
            break;
        }
    }
    header("Location: menu.php");
    exit();
}

// Get menu data
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get categories
    $category_stmt = $conn->prepare("SELECT * FROM categories ORDER BY id");
    $category_stmt->execute();
    $categories = $category_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get all menu items grouped by category
    $menu_items = [];
    foreach ($categories as $category) {
        $item_stmt = $conn->prepare("SELECT * FROM menu_items WHERE category_id = :category_id ORDER BY id");
        $item_stmt->bindParam(':category_id', $category['id']);
        $item_stmt->execute();
        $menu_items[$category['id']] = $item_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
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
  <title>Menu - Coffee Mania</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="shortcut icon" href="images\favicon.png" type="image/x-icon" />
  <link rel="stylesheet" href="css\menu.css">
  <style>
    /* Cart Styles */
    .cart-icon {
      position: fixed;
      bottom: 20px;
      right: 20px;
      background: #333;
      color: white;
      padding: 15px;
      border-radius: 50%;
      cursor: pointer;
      z-index: 1000;
      box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    }

    .cart-count {
      position: absolute;
      top: -5px;
      right: -5px;
      background: #d4a762;
      color: white;
      border-radius: 50%;
      padding: 2px 6px;
      font-size: 12px;
    }

    .cart-panel {
      position: fixed;
      top: 0;
      right: -400px;
      width: 350px;
      height: 100%;
      background: white;
      box-shadow: -2px 0 10px rgba(0,0,0,0.1);
      transition: right 0.3s ease;
      z-index: 999;
      padding: 20px;
      overflow-y: auto;
      padding-top: 80px; /* Added to account for header */
    }

    .cart-panel.open {
      right: 0;
    }

    .cart-item {
      display: flex;
      justify-content: space-between;
      margin-bottom: 15px;
      padding-bottom: 15px;
      border-bottom: 1px solid #eee;
    }

    .cart-total {
      font-weight: bold;
      margin-top: 20px;
      text-align: right;
    }

    /* Added to prevent body scrolling when cart is open */
    body.cart-open {
      overflow: hidden;
    }
  </style>
</head>
<body>

  <!-- Header -->
  <header class="header">
    <div class="logo">
      <strong>Coffee Mania</strong>
    </div>
    <nav class="nav">
      <a href="index.html">Home</a>
      <a href="about.html">About Us</a>
      <a href="menu.php">Menu</a>
      <a href="contact.php">Contact</a>
      <a href="signup.php">Signup</a>
      <a href="login.php">Login</a>
    </nav>
  </header>

  <!-- Main Content -->
  <main>
    <section class="menu-section">
      <h1>Our Menu</h1>

      <?php if (!empty($error)): ?>
        <div style="color: #e74c3c; background: #ffebee; padding: 1rem; border-radius: 8px; margin-bottom: 2rem;">
          <?php echo $error; ?>
        </div>
      <?php endif; ?>

      <?php foreach ($categories as $category): ?>
        <h2><?php echo htmlspecialchars($category['icon'] . ' ' . $category['name']); ?></h2>
        <div class="item-grid">
          <?php if (!empty($menu_items[$category['id']])): ?>
            <?php foreach ($menu_items[$category['id']] as $item): ?>
              <div class="menu-item">
                <?php if (!empty($item['image_path'])): ?>
                  <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" width="100">
                <?php else: ?>
                  <img src="images/coffee-placeholder.jpg" alt="Coffee placeholder" width="100">
                <?php endif; ?>
                <h3><?php echo htmlspecialchars($item['name']); ?> - $<?php echo number_format($item['price'], 2); ?></h3>
                <p><?php echo htmlspecialchars($item['description']); ?></p>
                <form method="post">
                  <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                  <input type="hidden" name="item_name" value="<?php echo htmlspecialchars($item['name']); ?>">
                  <input type="hidden" name="item_price" value="<?php echo $item['price']; ?>">
                  <button type="submit" name="add_to_cart">Add to Cart</button>
                </form>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p>No items in this category yet.</p>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </section>
  </main>

  <!-- Footer -->
  <footer class="footer-container">
    <p>&copy; 2025 Coffee Mania. All Rights Reserved.</p>
  </footer>

  <!-- Cart Icon -->
  <div class="cart-icon" id="cartIcon">
    🛒
    <span class="cart-count" id="cartCount"><?php echo array_sum(array_column($_SESSION['cart'], 'quantity')); ?></span>
  </div>

  <!-- Cart Panel -->
  <div class="cart-panel" id="cartPanel">
    <h2>Your Order</h2>
    <div id="cartItems">
      <?php if (!empty($_SESSION['cart'])): ?>
        <?php foreach ($_SESSION['cart'] as $item): ?>
          <div class="cart-item">
            <div>
              <h4><?php echo htmlspecialchars($item['name']); ?></h4>
              <p>$<?php echo number_format($item['price'], 2); ?> x <?php echo $item['quantity']; ?></p>
            </div>
            <div>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
            <a href="menu.php?remove_from_cart=<?php echo $item['id']; ?>">×</a>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p>Your cart is empty</p>
      <?php endif; ?>
    </div>
    <div class="cart-total">
      Total: $<?php 
        $total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        echo number_format($total, 2); 
      ?>
    </div>
    <button id="checkoutBtn" style="width:100%; padding:10px; margin-top:20px; background:#d4a762; color:white; border:none;">
      Checkout
    </button>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Cart functionality
      const cartIcon = document.getElementById('cartIcon');
      const cartPanel = document.getElementById('cartPanel');
      const body = document.body;
      
      // Toggle cart panel
      cartIcon.addEventListener('click', () => {
        cartPanel.classList.toggle('open');
        body.classList.toggle('cart-open');
      });

      // Checkout button
      document.getElementById('checkoutBtn').addEventListener('click', () => {
        window.location.href = 'checkout.php';
      });

      // Show cart message if exists
      <?php if (isset($_SESSION['cart_message'])): ?>
        alert("<?php echo $_SESSION['cart_message']; ?>");
        <?php unset($_SESSION['cart_message']); ?>
      <?php endif; ?>
    });
  </script>
</body>
</html>