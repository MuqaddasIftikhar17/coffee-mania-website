<?php
session_start();
require_once 'config.php';

if (!isset($_GET['order_number'])) {
    header("Location: menu.php");
    exit();
}

$order_number = $_GET['order_number'];

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get order details
    $stmt = $conn->prepare("SELECT * FROM orders WHERE order_number = :order_number");
    $stmt->bindParam(':order_number', $order_number);
    $stmt->execute();
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        header("Location: menu.php");
        exit();
    }
    
    // Get order items
    $item_stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = :order_id");
    $item_stmt->bindParam(':order_id', $order['id']);
    $item_stmt->execute();
    $items = $item_stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
$conn = null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Coffee Mania</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; margin: 0; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; text-align: center; }
        .confirmation-box { background: #f9f9f9; padding: 30px; border-radius: 5px; margin-bottom: 20px; }
        .order-details { text-align: left; margin-top: 20px; }
        .order-item { display: flex; justify-content: space-between; margin-bottom: 10px; }
        .total { font-weight: bold; font-size: 1.2em; margin-top: 20px; }
        .btn { background: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="confirmation-box">
            <h1>Order Confirmed!</h1>
            <p>Thank you for your order. Here are your order details:</p>
            
            <div class="order-details">
                <h2>Order #<?php echo $order['order_number']; ?></h2>
                <p><strong>Customer:</strong> <?php echo $order['customer_name']; ?></p>
                <p><strong>Email:</strong> <?php echo $order['customer_email']; ?></p>
                <p><strong>Phone:</strong> <?php echo $order['customer_phone']; ?></p>
                <p><strong>Address:</strong> <?php echo nl2br($order['customer_address']); ?></p>
                <p><strong>Delivery:</strong> <?php echo ucfirst($order['delivery_type']); ?> (+$<?php echo number_format($order['delivery_fee'], 2); ?>)</p>
                
                <h3>Order Items:</h3>
                <?php foreach ($items as $item): ?>
                    <div class="order-item">
                        <span><?php echo $item['item_name']; ?> (x<?php echo $item['quantity']; ?>)</span>
                        <span>$<?php echo number_format($item['item_price'] * $item['quantity'], 2); ?></span>
                    </div>
                <?php endforeach; ?>
                
                <div class="total">
                    <span>Total Amount:</span>
                    <span>$<?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
            </div>
        </div>
        
        <a href="menu.php" class="btn">Back to Menu</a>
    </div>
</body>
</html>