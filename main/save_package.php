<?php 
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $package_name = $_POST["package_name"];
    $description = $_POST["description"];
    $price = $_POST["price"];
    $duration = $_POST["duration"];
    $category = $_POST["category"]; // New category field

    // Handle Image Upload
    $target_dir = "uploads/"; // Folder to store images
    $target_file = $target_dir . basename($_FILES["image"]["name"]);

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        echo "Image uploaded successfully!<br>";
    } else {
        echo "Error uploading image!<br>";
    }

    $image = basename($_FILES["image"]["name"]); // Save only filename in DB

    // Updated SQL to include category
    $sql = "INSERT INTO packages (package_name, description, price, duration, category, image) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdsss", $package_name, $description, $price, $duration, $category, $image);

    if ($stmt->execute()) {
        echo "Package added successfully!";
    } else {
        echo "Error: " . $conn->error;
    }

    $stmt->close();
}
?>
