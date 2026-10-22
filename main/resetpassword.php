<?php
include 'db_connect.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $new_password = $_POST["new_password"];
    $confirm_password = $_POST["confirm_password"];

    if ($new_password !== $confirm_password) {
        $message = "Passwords do not match.";
    } else {
        // Optional: hash the password for security
        // $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $hashed_password = $new_password; // plain text for now

        // Check if email exists
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->close();

            // Update the password
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->bind_param("ss", $hashed_password, $email);
            if ($stmt->execute()) {
                $message = "Password successfully reset!";
            } else {
                $message = "Error updating password.";
            }
        } else {
            $message = "Email not found.";
        }

        $stmt->close();
        $conn->close();
    }
}
?>

<!-- HTML Form -->
<!DOCTYPE html>
<html>
<head>
    <title>Reset Password - TripTrove</title>
    <link rel="icon" type="image/png" href="images/logoTT.png">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <div class="navbar">
            <div class="logo">
                <img src="images/logoTT.png" alt="TripTrove Logo">
                <span>TripTrove</span>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="about.php">About</a></li>
                    <li><a href="category.php">Packages</a></li>
                    <li><a href="login.php">Login</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <section class="login-container">
        <div class="login-box">
            <div style="margin-bottom: 10px;">
                <img src="images/logoTT.png" alt="TripTrove Logo" style="width: 55px; height: 55px; border-radius: 50%; box-shadow: 0 4px 10px rgba(0,86,179,0.2);">
            </div>
            <h2>Reset Password</h2>
            <?php if (!empty($message)): ?>
                <p style="color:red;"><?php echo $message; ?></p>
            <?php endif; ?>

            <form method="post" action="">
                <label for="email">Email</label>
                <input type="email" name="email" required><br><br>

                <label for="new_password">New Password</label>
                <input type="password" name="new_password" required><br><br>

                <label for="confirm_password">Confirm New Password</label>
                <input type="password" name="confirm_password" required><br><br>

                <button type="submit" class="btn">Reset Password</button>
            </form>

            <p><a href="login.php">Back to Login</a></p>
        </div>
    </section>
</body>
</html>
