<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = $_POST["firstName"];
    $lastName = $_POST["lastName"];
    $email = $_POST["email"];
    $password = $_POST["password"];

    $sql = "INSERT INTO users (firstName, lastName, email, password) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $firstName, $lastName, $email, $password);

    if ($stmt->execute()) {
        header("Location: login.php?success=registered");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - TripTrove</title>
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
                    <li><a href="packages.php">Packages</a></li>
                    <li><a href="login.php">Login</a></li>
                </ul>
            </nav>
        </div>
    </header>
    
    <section class="login-container">        
        <div class="login-box" id="register">
            <div style="margin-bottom: 10px;">
                <img src="images/logoTT.png" alt="TripTrove Logo" style="width: 55px; height: 55px; border-radius: 50%; box-shadow: 0 4px 10px rgba(0,86,179,0.2);">
            </div>
            <h2>Register</h2>

            <form method="post" id="register-form" action="#">
                <label for="firstname">First Name</label>
                <input type="text" id="firstname" name="firstName" required><br>

                <label for="lastname">Last Name</label>
                <input type="text" id="lastname" name="lastName" required><br>

                <label for="registeremail">Email</label>
                <input type="email" id="registeremail" name="email" required><br>

                <label for="registerpassword">Password</label>
                <input type="password" id="registerpassword" name="password" required><br><br>
                
                <button type="submit" class="btn">Register</a></button>
            </form>
        </div>
    </section>
    
    <footer>
        <p>&copy; 2025 TripTrove. All rights reserved.</p>
    </footer>
    
    <script src="script.js"></script>
</body>
</html>
