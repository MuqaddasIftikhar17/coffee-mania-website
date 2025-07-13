<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Coffee Mania</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="images\favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="css\login.css">
</head>
<body>
    <!-- MAIN SECTION -->
    <main>
        <h1>Welcome Back!</h1>
        <p>Don't have an account? <a href="signup.php">Register here</a>.</p>

        <?php
        session_start();
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                // Database connection
                $servername = "sql211.infinityfree.com"; // Replace with your InfinityFree details
                $username = "if0_39273958"; // Replace with your username
                $password = "FjY82gYbIn2u9yZ"; // Replace with your password
                $dbname = "if0_39273958_coffeemania"; // Replace with your database name
                
                $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Get user input
                $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
                $password = $_POST['password'];
                
                // Check if user exists
                $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
                $stmt->bindParam(':email', $email);
                $stmt->execute();
                
                if ($stmt->rowCount() > 0) {
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    // Verify password
                    if (password_verify($password, $user['password'])) {
                        // Record login attempt
                        $login_stmt = $conn->prepare("INSERT INTO login_logs (user_id, email, login_time, ip_address, user_agent) 
                                                    VALUES (:user_id, :email, NOW(), :ip, :ua)");
                        $login_stmt->bindParam(':user_id', $user['id']);
                        $login_stmt->bindParam(':email', $email);
                        $login_stmt->bindParam(':ip', $_SERVER['REMOTE_ADDR']);
                        $login_stmt->bindParam(':ua', $_SERVER['HTTP_USER_AGENT']);
                        $login_stmt->execute();
                        
                        // Set session variables
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['email'] = $user['email'];
                        $_SESSION['fname'] = $user['fname'];
                        $_SESSION['lname'] = $user['lname'];
                        
                        // Redirect to dashboard or home page
                        header("Location: index.html");
                        exit();
                    } else {
                        throw new Exception("Invalid email or password!");
                    }
                } else {
                    throw new Exception("Invalid email or password!");
                }
            } catch(PDOException $e) {
                echo "<p style='color:red;'>Database error: " . $e->getMessage() . "</p>";
            } catch(Exception $e) {
                echo "<p style='color:red;'>" . $e->getMessage() . "</p>";
            }
            
            $conn = null;
        }
        ?>

        <form action="login.php" method="post">
            <label for="email">Email:</label><br>
            <input type="email" name="email" id="email" required><br><br>

            <label for="password">Password:</label><br>
            <input type="password" name="password" id="password" required><br><br>

            <a href="forgot_password.php" target="_blank">Forgot Password?</a><br><br>

            <button type="submit">Login</button>

            <p>OR login with</p>

            <a href="email-login.php" class="social-btn">Email</a>
            <a href="facebook-login.php" class="social-btn">Facebook</a>
        </form>
    </main>
</body>
</html>