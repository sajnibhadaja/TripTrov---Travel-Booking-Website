<?php

session_start();
include ('config/dbcon.php');


if(isset($_POST['addpackage']))
{
    $category = $_POST['category'];
    $package_name = $_POST['package_name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $duration = $_POST['duration'];
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $imageTmp = $_FILES['image']['tmp_name'];
        $imageName = basename($_FILES['image']['name']); // Secure file name
        $destination = "uploads/" . $imageName; // No '../' needed

        if (move_uploaded_file($imageTmp, $destination)) {
            echo "Image uploaded successfully!";
        } else {
            echo "Error uploading image!";
            exit(); // Stop execution if image upload fails
        }
    } else {
        $imageName = ""; // Set empty if no image is uploaded
    }

    // Fix SQL Query - Wrap $imageName in quotes
    $user_query = "INSERT INTO packages (category, package_name, description, price, duration, image) 
                   VALUES ('$category', '$package_name', '$description', '$price', '$duration', '$imageName')";

    $user_query_run = mysqli_query($conn, $user_query);

    if($user_query_run) {
        $_SESSION['status'] = "Package Added Successfully";
        header("Location: packages.php");
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

if(isset($_POST['itinerary'])) {
    $package_name = mysqli_real_escape_string($conn, $_POST['package_name']);
    $duration1 = mysqli_real_escape_string($conn, $_POST['duration']);
    $description1 = mysqli_real_escape_string($conn, $_POST['description']);
    $departure_location = mysqli_real_escape_string($conn, $_POST['departure_location']);
    $destination = mysqli_real_escape_string($conn, $_POST['destination']);
    $budget = mysqli_real_escape_string($conn, $_POST['budget']);
    $transport = mysqli_real_escape_string($conn, $_POST['transport']);

    
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $imageTmp = $_FILES['image']['tmp_name'];
        $imageName = basename($_FILES['image']['name']);
        $destinationPath = "uploads/" . $imageName;

        if (!move_uploaded_file($imageTmp, $destinationPath)) {
            echo "Error uploading image!";
            exit();
        }
    } else {
        $imageName = "";
    }

    // ✅ Include package_name in your insert query
    $user_query = "INSERT INTO itineraries 
        (package_name, duration, description, image, departure_location, destination, budget, transport) 
        VALUES 
        ('$package_name', '$duration1', '$description1', '$imageName', '$departure_location', '$destination', '$budget', '$transport')";

    $user_query_run = mysqli_query($conn, $user_query);

    if($user_query_run) {
        $_SESSION['status'] = "Itinerary Added Successfully";
        header("Location: itineraries.php");
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}



if (isset($_POST['updatePackage'])) {
    $id = $_POST['user_id'];
    $category = $_POST['category'];
    $package_name = $_POST['package_name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $duration = $_POST['duration'];
    
    // Handle image
    $new_image = $_FILES['image']['name'];
    $old_image_query = "SELECT image FROM packages WHERE package_id='$id'";
    $result = mysqli_query($conn, $old_image_query);
    $old_image = mysqli_fetch_assoc($result)['image'];

    if ($new_image != '') {
        $image = $new_image;
        move_uploaded_file($_FILES['image']['tmp_name'], "uploads/" . $new_image);

        // Optionally delete old image file
        if (file_exists("uploads/" . $old_image)) {
            unlink("uploads/" . $old_image);
        }
    } else {
        $image = $old_image;
    }

    $update_query = "UPDATE packages SET 
        category='$category', 
        package_name='$package_name', 
        description='$description', 
        price='$price', 
        duration='$duration', 
        image='$image' 
        WHERE package_id='$id'";

    $update_run = mysqli_query($conn, $update_query);

    if ($update_run) {
        $_SESSION['status'] = "Package Updated Successfully!";
        header("Location: packages.php");
        exit();
    } else {
        $_SESSION['status'] = "Failed to Update Package.";
        header("Location: packages.php");
        exit();
    }
}

if (isset($_POST['updateitinerary'])) {
    $id = ($_POST['itineray_id']);
    $package_name = mysqli_real_escape_string($conn, $_POST['package_name']);
    $duration1 = mysqli_real_escape_string($conn, $_POST['duration']);
    $description1 = mysqli_real_escape_string($conn, $_POST['description']);
    $departure_location = mysqli_real_escape_string($conn, $_POST['departure_location']);
    $destination = mysqli_real_escape_string($conn, $_POST['destination']);
    $budget = $_POST['budget'];
    $transport = mysqli_real_escape_string($conn, $_POST['transport']);
    
    // Handle image
    $new_image = $_FILES['image']['name'];
    $old_image_query = "SELECT image FROM itineraries WHERE itineray_id='$id'";
    $result = mysqli_query($conn, $old_image_query);
    $old_image = mysqli_fetch_assoc($result)['image'];

    if ($new_image != '') {
        $image = $new_image;
        move_uploaded_file($_FILES['image']['tmp_name'], "uploads/" . $new_image);

        // Optionally delete old image file
        if (file_exists("uploads/" . $old_image)) {
            unlink("uploads/" . $old_image);
        }
    } else {
        $image = $old_image;
    }

    $update_query = "UPDATE itineraries SET 
        duration='$duration1', 
        package_name='$package_name', 
        description='$description1', 
        departure_location='$departure_location', 
        destination='$destination', 
        budget=$budget, 
        transport='$transport', 
        image='$image' 
        WHERE itineray_id='$id'";

    $update_run = mysqli_query($conn, $update_query);

    if ($update_run) {
        $_SESSION['status'] = "Package Updated Successfully!";
        header("Location: itineraries.php");
        exit();
    } else {
        $_SESSION['status'] = "Failed to Update Package.";
        header("Location: itineraries.php");
        exit();
    }
}


?>