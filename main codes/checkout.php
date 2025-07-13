<?php
session_start();
require_once 'config.php';

// Redirect if cart is empty
if (empty($_SESSION['cart'])) {
    header("Location: menu.php");
    exit();
}

// Calculate subtotal
$subtotal = 0;
foreach ($_SESSION['cart'] as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

// Process checkout form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Generate order number
        $order_number = 'CM-' . strtoupper(uniqid());
        
        // Get form data
        $name = $_POST['name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];
        $delivery_type = $_POST['delivery_type'];
        $delivery_fee = ($delivery_type == 'express') ? 50 : 25;
        $total = $subtotal + $delivery_fee;
        $notes = isset($_POST['notes']) ? $_POST['notes'] : '';
        
        // Insert order
        $stmt = $conn->prepare("INSERT INTO orders (order_number, customer_name, customer_email, customer_phone, customer_address, delivery_type, delivery_fee, total_amount, notes) 
                               VALUES (:order_number, :name, :email, :phone, :address, :delivery_type, :delivery_fee, :total, :notes)");
        $stmt->bindParam(':order_number', $order_number);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':delivery_type', $delivery_type);
        $stmt->bindParam(':delivery_fee', $delivery_fee);
        $stmt->bindParam(':total', $total);
        $stmt->bindParam(':notes', $notes);
        $stmt->execute();
        $order_id = $conn->lastInsertId();
        
        // Insert order items
        foreach ($_SESSION['cart'] as $item) {
            $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, item_id, item_name, item_price, quantity) 
                                        VALUES (:order_id, :item_id, :item_name, :item_price, :quantity)");
            $item_stmt->bindParam(':order_id', $order_id);
            $item_stmt->bindParam(':item_id', $item['id']);
            $item_stmt->bindParam(':item_name', $item['name']);
            $item_stmt->bindParam(':item_price', $item['price']);
            $item_stmt->bindParam(':quantity', $item['quantity']);
            $item_stmt->execute();
        }
        
        // Clear cart
        unset($_SESSION['cart']);
        
        // Redirect to confirmation
        header("Location: order_confirmation.php?order_number=$order_number");
        exit();
        
    } catch(PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
    $conn = null;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Coffee Mania</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --coffee-dark: #4B3832;
            --coffee-medium: #6F4E37;
            --coffee-light: #C4A484;
            --coffee-cream: #FFF4E6;
            --coffee-accent: #E6B325;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--coffee-cream);
            color: var(--coffee-dark);
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
        }
        
        .checkout-form, .order-summary {
            flex: 1;
            min-width: 300px;
        }
        
        .section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }
        
        .section::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--coffee-medium), var(--coffee-accent));
        }
        
        h1, h2 {
            color: var(--coffee-medium);
            margin-top: 0;
        }
        
        h1 {
            width: 100%;
            text-align: center;
            margin-bottom: 30px;
            position: relative;
        }
        
        h1::after {
            content: "☕";
            position: absolute;
            right: 20px;
            animation: float 3s ease-in-out infinite;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--coffee-dark);
        }
        
        input, textarea, select {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 6px;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s;
        }
        
        input:focus, textarea:focus, select:focus {
            border-color: var(--coffee-medium);
            outline: none;
            box-shadow: 0 0 0 3px rgba(111, 78, 55, 0.2);
        }
        
        button {
            background: var(--coffee-medium);
            color: white;
            border: none;
            padding: 15px 25px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s;
        }
        
        button:hover {
            background: var(--coffee-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 10px rgba(0,0,0,0.1);
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px dashed var(--coffee-light);
            animation: fadeIn 0.5s ease-out;
        }
        
        .total-row {
            font-weight: bold;
            font-size: 1.1em;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 2px solid var(--coffee-medium);
            animation: fadeIn 0.7s ease-out;
        }
        
        .delivery-option {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding: 15px;
            border-radius: 6px;
            background: white;
            border: 2px solid #eee;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .delivery-option:hover {
            border-color: var(--coffee-light);
            transform: translateX(5px);
        }
        
        .delivery-option input {
            width: auto;
            margin-right: 15px;
        }
        
        .delivery-option label {
            margin-bottom: 0;
            flex: 1;
            cursor: pointer;
        }
        
        .delivery-price {
            font-weight: bold;
            color: var(--coffee-accent);
        }
        
        .coffee-bean {
            position: absolute;
            width: 30px;
            height: 30px;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><path fill="%234B3832" d="M85 40c0 20-15 35-35 35-5 0-10-1-15-3-5 15-20 25-35 25-5 0-5-5 0-5 10 0 20-5 25-15-20-5-35-25-35-45C10 20 25 5 45 5c5 0 10 5 15 10 15-10 30-5 30 10 0 5-5 10-10 10-5 0-10-5-10-10 0-15-15-15-25-5-15-10-25 0-25 15 0 10 5 20 15 25 5 2 10 3 15 3 15 0 30-10 30-25 0-5 5-10 10-10s10 5 10 10z"/></svg>');
            opacity: 0.1;
            animation: float 6s ease-in-out infinite;
            z-index: -1;
        }
        
        /* Animations */
        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-10px) rotate(5deg); }
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .pulse {
            animation: pulse 2s infinite;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            
            h1::after {
                position: static;
                display: block;
                margin-top: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Coffee bean decorations -->
    <div class="coffee-bean" style="top: 10%; left: 5%; animation-delay: 0s;"></div>
    <div class="coffee-bean" style="top: 30%; right: 8%; animation-delay: 1s;"></div>
    <div class="coffee-bean" style="bottom: 20%; left: 10%; animation-delay: 2s;"></div>
    <div class="coffee-bean" style="bottom: 40%; right: 15%; animation-delay: 3s;"></div>
    
    <div class="container">
        <h1>Checkout Your Order</h1>
        
        <?php if (!empty($error)): ?>
            <div style="color: #d32f2f; background: #ffebee; padding: 15px; border-radius: 6px; margin-bottom: 20px; width: 100%;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <div class="checkout-form">
            <div class="section">
                <h2>Customer Information</h2>
                <form method="post" id="checkoutForm">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" required>
                    </div>
                    <div class="form-group">
                        <label for="address">Delivery Address</label>
                        <textarea id="address" name="address" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="notes">Special Instructions (Optional)</label>
                        <textarea id="notes" name="notes" rows="2" placeholder="Any special requests or notes for delivery..."></textarea>
                    </div>
            </div>
            
            <div class="section">
                <h2>Delivery Options</h2>
                <div class="delivery-option">
                    <input type="radio" id="express" name="delivery_type" value="express" required>
                    <label for="express">Express Delivery <span class="delivery-price">(+$50)</span><br>
                    <small>Same day delivery - Get your coffee within 2 hours</small></label>
                </div>
                <div class="delivery-option">
                    <input type="radio" id="regular" name="delivery_type" value="regular" checked>
                    <label for="regular">Regular Delivery <span class="delivery-price">(+$25)</span><br>
                    <small>Next day delivery - Delivered fresh tomorrow morning</small></label>
                </div>
            </div>
        </div>
        
        <div class="order-summary">
            <div class="section pulse">
                <h2>Order Summary</h2>
                <?php foreach ($_SESSION['cart'] as $item): ?>
                    <div class="order-item" style="animation-delay: <?php echo rand(0, 3) * 0.1; ?>s">
                        <span><?php echo $item['name']; ?> × <?php echo $item['quantity']; ?></span>
                        <span>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                    </div>
                <?php endforeach; ?>
                
                <div class="order-item">
                    <strong>Subtotal</strong>
                    <strong>$<?php echo number_format($subtotal, 2); ?></strong>
                </div>
                
                <div class="order-item" id="deliveryFeeRow">
                    <span>Delivery Fee</span>
                    <span id="deliveryFee">$25.00</span>
                </div>
                
                <div class="order-item total-row">
                    <strong>Total Amount</strong>
                    <strong id="totalAmount">$<?php echo number_format($subtotal + 25, 2); ?></strong>
                </div>
                
                <button type="submit" form="checkoutForm" style="margin-top: 20px;">
                    Place Order & Pay
                </button>
                </form>
            </div>
            
            <div class="section" style="text-align: center; background: var(--coffee-cream);">
                <h3>Need Help?</h3>
                <p>Call us at <strong>1-800-COFFEE</strong><br>
                or email <strong>help@coffeemania.com</strong></p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Update delivery fee and total when selection changes
            const expressOption = document.getElementById('express');
            const regularOption = document.getElementById('regular');
            const deliveryFeeElement = document.getElementById('deliveryFee');
            const totalAmountElement = document.getElementById('totalAmount');
            const deliveryFeeRow = document.getElementById('deliveryFeeRow');
            const subtotal = <?php echo $subtotal; ?>;
            
            function updateTotals() {
                const isExpress = expressOption.checked;
                const deliveryFee = isExpress ? 50 : 25;
                const total = subtotal + deliveryFee;
                
                deliveryFeeElement.textContent = '$' + deliveryFee.toFixed(2);
                totalAmountElement.textContent = '$' + total.toFixed(2);
                
                // Add animation to highlight the change
                deliveryFeeRow.style.animation = 'none';
                void deliveryFeeRow.offsetWidth; // Trigger reflow
                deliveryFeeRow.style.animation = 'fadeIn 0.5s ease-out';
            }
            
            expressOption.addEventListener('change', updateTotals);
            regularOption.addEventListener('change', updateTotals);
            
            // Add floating animation to delivery options when hovered
            const deliveryOptions = document.querySelectorAll('.delivery-option');
            deliveryOptions.forEach(option => {
                option.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateX(5px)';
                });
                option.addEventListener('mouseleave', function() {
                    this.style.transform = '';
                });
            });
        });
    </script>
</body>
</html>