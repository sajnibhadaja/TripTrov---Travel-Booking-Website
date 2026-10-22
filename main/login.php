<?php
session_start();
$error = "";

include 'db_connect.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") 
{
    // Sanitize input
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    // Prepare SQL query to get hashed password and user info
    $sql = "SELECT user_id, role FROM users WHERE email = ? and password = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param('ss', $email, $password);
        $stmt->execute();
        $stmt->store_result();

        // Check if user exists
        if ($stmt->num_rows > 0) 
        {
            $stmt->bind_result($id, $role);
            $stmt->fetch();

            session_start();
            $_SESSION["user_id"] = $id;

            if ($role == 1) {
                header("Location: AdminLTE1/admin");
            } else {
                header("Location: index.php");
            }
            exit(); // Always exit after redirect
        } 
        else 
        {
            $error = "Invalid email or password.";
        }
        
        $stmt->close();

    } 
    else 
    {
        echo "Error in preparing statement: " . $conn->error;
    }
    $conn->close();
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TripTrove</title>
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
            <h2>Login</h2>
                        <form method="post" action="">
                <label for="email">Email</label>
                <input type="email" name="email" required><br>

                <label for="password">Password</label>
                <input type="password" name="password" required><br><br>

                <button type="submit" class="btn">Login</button>
            </form>
            <!-- Show error if set -->
            <?php if (!empty($error)) : ?>
                <p style="color:red;"><?php echo $error; ?></p>
            <?php endif; ?>
            <p><a href="resetpassword.php">Reset Password</a></p>
            <p>Don't have an account? <a href="register.php">Sign Up</a></p>
        </div>
    </section>
    
    <footer>
        <p>&copy; 2025 TripTrove. All rights reserved.</p>
    </footer>

</body>
</html>
