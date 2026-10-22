<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Categories - TripTrove</title>
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
    
    <section class="packages-hero">
        <div class="packages-hero-content">
            <h1>Explore Our Packages</h1>
            <p>Find the perfect travel experience tailored for you.</p>
        </div>
    </section>
    <h1 style="text-align:center;">Explore by Category</h1>

    <div class="category-container">
        <div class="category-box">
            <img src="images/international.jpg" alt="" height="200px" width="300px">
            <h2>International</h2>
            <a href="category.php?category=International">View More</a>
        </div>
        <div class="category-box">
            <img src="images/national.jpg" alt="" height="200px" width="300px">
            <h2>National</h2>
            <a href="category.php?category=National">View More</a>
        </div>
        <div class="category-box">
            <img src="images/adventure.jpg" alt="" height="200px" width="300px">
            <h2>Adventure</h2>
            <a href="category.php?category=Adventure">View More</a>
        </div>
    </div>


        <?php
        if (isset($_GET['category'])) {
            $selected_category = $_GET['category'];
            echo "<h2 style='text-align:center;'>Showing $selected_category Packages</h2>";
        ?>
        <div class="category-container">
            <?php
                include 'db_connect.php';
                $sql = "SELECT * FROM packages WHERE category = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $selected_category);
                $stmt->execute();
                $result = $stmt->get_result();

                while ($row = $result->fetch_assoc()) {
                    echo "<div class='package'>";
                    echo "<img src='uploads/" . $row["image"] . "' alt='" . $row["package_name"] . "'>";
                    echo "<h3>" . $row["package_name"] . "</h3>";
                    echo "<p>" . $row["description"] . "</p>";
                    echo "<p><strong>Price:</strong> ₹" . $row["price"] . "</p>";
                    echo "<p><strong>Duration:</strong> " . $row["duration"] . "</p>";
                    echo "<a href='itinerary.php?package_name=" . urlencode($row["package_name"]) . "' class='btn' style='display:inline-block; margin-top:10px; background:#0056b3; color:white; padding:10px 20px; border-radius:6px; text-decoration:none; font-weight:600;'>View Itinerary</a>";

                    echo "</div>";
                }

                $stmt->close();
                $conn->close();
            }
            ?>
        </div>  
        
    <footer>
        <p>&copy; 2025 TripTrove. All rights reserved.</p>
    </footer>
    
</body>
</html>
