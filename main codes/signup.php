<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SignUp - Coffee Mania</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="C:\Users\Home\Documents\University\Semester VI\Web Technologies\coffeemania\images\favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="Ccss\about.css">
</head>
<body>
    <!-- MAIN SECTION -->
    <h1>SignUp - Coffee Mania</h1>
    <h2>Create an Account!</h2>
    
    <?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Database connection
        $servername = "sql211.infinityfree.com"; // Replace with your InfinityFree details
        $username = "if0_39273958"; // Replace with your username
        $password = "FjY82gYbIn2u9yZ"; // Replace with your password
        $dbname = "if0_39273958_coffeemania"; // Replace with your database name
        
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Prepare and bind
            $stmt = $conn->prepare("INSERT INTO users (fname, lname, email, password) VALUES (:fname, :lname, :email, :password)");
            
            // Sanitize and validate input
            $fname = filter_input(INPUT_POST, 'fname', FILTER_SANITIZE_STRING);
            $lname = filter_input(INPUT_POST, 'lname', FILTER_SANITIZE_STRING);
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            
            // Check if passwords match
            if ($_POST['password'] !== $_POST['confirm_password']) {
                throw new Exception("Passwords do not match!");
            }
            
            // Bind parameters
            $stmt->bindParam(':fname', $fname);
            $stmt->bindParam(':lname', $lname);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $password);
            
            // Execute query
            $stmt->execute();
            
            echo "<p style='color:green;'>Registration successful! You can now login.</p>";
        } catch(PDOException $e) {
            echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
        } catch(Exception $e) {
            echo "<p style='color:red;'>" . $e->getMessage() . "</p>";
        }
        
        $conn = null;
    }
    ?>
    
    <form action="signup.php" method="post">
        <label for="fname">First Name</label>
        <input type="text" name="fname" id="fname"> <br><br>

        <label for="lname">Last Name</label>
        <input type="text" name="lname" id="lname"> <br><br>

        <label for="email">Email</label>
        <input type="email" name="email" id="email" required> <br><br>

        <label for="password">Password</label>
        <input type="password" name="password" id="password" required> <br><br>

        <label for="confirm_password">Confirm Password</label>
        <input type="password" name="confirm_password" id="confirm_password" required> <br><br>

        <input type="submit" name="signup" value="SignUp">

        <p> OR </p>

        <a href="login.php" class="social-btn">Continue with Email</a>
        <a href="facebook-login.php" class="social-btn">Continue with Facebook</a>
    </form>

</body>
</html>