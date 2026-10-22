<!-- <?php
// index.php - Home Page

// Start the session
session_start();

// Include the database connection
// include '.php';

?> -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TripTrove - Home</title>
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
    <section class="hero">
        <div class="hero-content">
            <h1>Explore the World with TripTrove</h1>
            <p>Your journey begins here. Find the best travel packages tailored for you.</p>
            <a href="category.php" class="btn">View Packages</a>
        </div>
    </section>
    <section class="features">
        <div class="feature-box">
            <h2>Exclusive Destinations</h2>
            <p>Discover breathtaking locations around the world.</p>
        </div>
        <div class="feature-box">
            <h2>Affordable Prices</h2>
            <p>Best deals that fit within your budget.</p>
        </div>
        <div class="feature-box">
            <h2>24/7 Support</h2>
            <p>We are here to assist you anytime, anywhere.</p>
        </div>
    </section>
    <footer>
        <p>&copy; 2025 TripTrove. All rights reserved.</p>
    </footer>
</body>
</html>
