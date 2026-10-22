<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travel Packages - TripTrove</title>
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
    
    <section class="packages-hero">
        <div class="packages-hero-content">
            <h1>Explore Our Packages</h1>
            <p>Find the perfect travel experience tailored for you.</p>
        </div>
        <button></button>
    </section>
    
    <section class="package-list">
        
        <?php
            include 'db_connect.php';

            $sql = "SELECT * FROM packages";
            $result = $conn->query($sql);

            while ($row = $result->fetch_assoc()) {
                echo "<div class='package'>";
                echo "<img src='uploads/" . $row["image"] . "' alt='" . $row["package_name"] . "'>";
                echo "<h2>" . $row["package_name"] . "</h2>";
                echo "<p>" . $row["description"] . "</p>";
                echo "<p><strong>Price:</strong> ₹" . $row["price"] . "</p>";
                echo "<p><strong>Duration:</strong> " . $row["duration"] . "</p>";
                echo "<a href='itinerary.php?package_name=" . urlencode($row["package_name"]) . "' class='btn' style='display:inline-block; margin-top:10px; background:#0056b3; color:white; padding:10px 20px; border-radius:6px; text-decoration:none; font-weight:600;'>View Itinerary</a>";
                echo "</div>";
            }
        ?>
        
    </section>
    


    <footer>
        <p>&copy; 2025 TripTrove. All rights reserved.</p>
    </footer>
</body>
</html>
