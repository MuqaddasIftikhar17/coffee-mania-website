<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Coffee Mania</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="images\favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="css\forgot_password.css">
</head>
<body>

    <!-- MAIN SECTION -->
    <main>
        <h1>Reset Your Password</h1>
        
        <?php
        session_start();
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                // Database connection
                $servername = "sql211.infinityfree.com";
                $username = "if0_39273958";
                $password = "FjY82gYbIn2u9yZ";
                $dbname = "if0_39273958_coffeemania";
                
                $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Get user input
                $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
                $new_password = $_POST['password'];
                $confirm_password = $_POST['confirm_password'];
                
                // Validate passwords match
                if ($new_password !== $confirm_password) {
                    throw new Exception("Passwords do not match!");
                }
                
                // Check if email exists
                $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
                $stmt->bindParam(':email', $email);
                $stmt->execute();
                
                if ($stmt->rowCount() > 0) {
                    // Hash the new password
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    
                    // Update password in database
                    $update_stmt = $conn->prepare("UPDATE users SET password = :password WHERE email = :email");
                    $update_stmt->bindParam(':password', $hashed_password);
                    $update_stmt->bindParam(':email', $email);
                    $update_stmt->execute();
                    
                    echo "<p style='color:green;'>Password updated successfully! You can now <a href='login.php'>login</a> with your new password.</p>";
                } else {
                    throw new Exception("No account found with that email address!");
                }
            } catch(PDOException $e) {
                echo "<p style='color:red;'>Database error: " . $e->getMessage() . "</p>";
            } catch(Exception $e) {
                echo "<p style='color:red;'>" . $e->getMessage() . "</p>";
            }
            
            $conn = null;
        }
        
        // Get email from query string if available
        $email = isset($_GET['email']) ? htmlspecialchars($_GET['email']) : '';
        ?>
        
        <p>Enter a new password for your account.</p>

        <form action="forgot_password.php" method="post">
            <label for="email">Email:</label><br>
            <input type="email" name="email" id="email" value="<?php echo $email; ?>" required><br><br>
            
            <label for="password">New Password:</label><br>
            <input type="password" name="password" id="password" required minlength="6"><br><br>

            <label for="confirm_password">Confirm New Password:</label><br>
            <input type="password" name="confirm_password" id="confirm_password" required minlength="6"><br><br>

            <button type="submit">Reset Password</button>
        </form>
        
        <p>Remember your password? <a href="login.php">Login here</a></p>
    </main>

</body>
</html>