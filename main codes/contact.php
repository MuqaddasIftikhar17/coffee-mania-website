<?php
// Database config
$servername = "sql211.infinityfree.com";
$username = "if0_39273958";
$password = "FjY82gYbIn2u9yZ";
$dbname = "if0_39273958_coffeemania";

$name = $email = $message = '';
$success_message = $error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $message = htmlspecialchars(trim($_POST['message']));

    if (empty($name) || empty($email) || empty($message)) {
        $error_message = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    } else {
        try {
            $conn = new mysqli($servername, $username, $password, $dbname);
            if ($conn->connect_error) throw new Exception("Connection failed");

            $stmt = $conn->prepare("INSERT INTO contact_submissions (name, email, message) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $message);

            if ($stmt->execute()) {
                $success_message = 'Thank you for your message! We will get back to you soon.';
                $name = $email = $message = '';
            } else {
                throw new Exception("Execution failed");
            }

            $stmt->close();
            $conn->close();
        } catch (Exception $e) {
            $error_message = 'There was a problem submitting your form. Please try again later.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Contact - Coffee Mania</title>
  <link rel="shortcut icon" href="images\favicon.png" type="image/x-icon" />
  <link rel="stylesheet" href="css\contact.css" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet" />
</head>
<body>

  <!-- Header -->
  <header class="header">
    <div class="logo">Coffee Mania</div>
    <nav class="nav">
      <a href="index.html">Home</a>
      <a href="about.html">About Us</a>
      <a href="menu.php">Menu</a>
      <a href="contact.php" class="active">Contact</a>
      <a href="signup.php">Signup</a>
      <a href="login.php">Login</a>
    </nav>
  </header>

  <!-- Main Section -->
  <main class="section">
    <h1 style="text-align:center; margin-bottom: 40px;">📬 Contact Us</h1>
    
    <div class="contact-container">
      <!-- Contact Form -->
      <form class="contact-form" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
        <?php if (!empty($success_message)): ?>
          <div class="success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
          <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <label for="name">Your Name</label>
        <input type="text" id="name" name="name" placeholder="John Doe" value="<?php echo htmlspecialchars($name); ?>" required />

        <label for="email">Your Email</label>
        <input type="email" id="email" name="email" placeholder="you@example.com" value="<?php echo htmlspecialchars($email); ?>" required />

        <label for="message">Your Message</label>
        <textarea id="message" name="message" rows="5" placeholder="Type your message here..." required><?php echo htmlspecialchars($message); ?></textarea>

        <button type="submit" class="btn-signup">Submit</button>
      </form>

      <!-- Contact Info -->
      <div class="contact-info">
        <h2>📍 Get in Touch</h2>
        <p><strong>Address:</strong><br>4674 Sugar Camp Road,<br>Owatonna, Minnesota, 55060</p>
        <p><strong>Phone:</strong><br><a href="tel:5614562321">561-456-2321</a></p>
        <p><strong>Email:</strong><br><a href="mailto:coffee-mania@gmail.com">coffee-mania@gmail.com</a></p>
      </div>
    </div>
  </main>

  <!-- Footer -->
  <footer class="footer">
    &copy; 2025 Coffee Mania. All Rights Reserved.
  </footer>

</body>
</html>
