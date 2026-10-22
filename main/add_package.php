<?php
include 'db_connect.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Package - TripTrove</title>
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
    <h2>Add New Package</h2>
    <form action="save_package.php" method="POST" enctype="multipart/form-data">
        
        <label for="category">Category:</label>
        <select name="category" required>
            <option value="International">International</option>
            <option value="National">National</option>
            <option value="Adventure">Adventure</option>
        </select><br><br>

        <label>Package Name:</label>
        <input type="text" name="package_name" required>

        <label>Description:</label>
        <textarea name="description" required></textarea>

        <label>Price (in INR):</label>
        <input type="number" name="price" step="0.01" required>

        <label>Duration:</label>
        <input type="text" name="duration" required>

        <label>Upload Image:</label>
        <input type="file" name="image" required>

        <button type="submit">Add Package</button>
    </form>
</body>
</html>
