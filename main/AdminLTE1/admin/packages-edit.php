<?php
    include('includes/header.php');
    // include('includes/topbar.php');
    include('includes/slidebar.php');
    include('config/dbcon.php');
?>

<div class="content-wrapper">

<div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 text-dark">Edit Package</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Edit Package </li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
</div>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Edit Package</h3>
        <a href="packages.php" class="btn btn-danger float-sm-right">BACK</a>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
            <form action="code.php" method="POST" enctype="multipart/form-data">
                <div class="model-body">
                    <?php
                    if(isset($_GET['user_id']))
                    {
                        $user_id = $_GET['user_id'];
                        $query = "select * from packages where package_id='$user_id' LIMIT 1 ";
                        $query_run = mysqli_query($conn, $query);

                        if(mysqli_num_rows($query_run) > 0)
                        {
                            foreach($query_run as $row)
                            {
                                ?>
                                    <input type="hidden" name="user_id" value="<?php echo $row['package_id'] ?>">
                                    <label for="category">Category:</label><br>
                                    <select name="category" required style="width: 100%;">
                                        <option value="International">International</option>
                                        <option value="National">National</option>
                                        <option value="Adventure">Adventure</option>
                                    </select><br><br>

                                    <label>Package Name:</label><br>
                                    <input type="text" name="package_name" value="<?php echo $row['package_name'] ?>" required style="width: 100%;" placeholder="Package Name"><br>

                                    <label>Description:</label><br>
                                    <textarea name="description" style="width: 100%;" required style="width: 100%;" placeholder="Description"><?php echo $row['description'] ?></textarea><br>

                                    <label>Price (in INR):</label><br>
                                    <input type="number" name="price" step="0.01" value="<?php echo $row['price'] ?>" required style="width: 100%;" placeholder="Price"><br>

                                    <label>Duration:</label><br>
                                    <input type="text" name="duration" value="<?php echo $row['duration'] ?>" required style="width: 100%;" placeholder="Duration"><br>

                                    <label>Current Image:</label><br>
                                    <img src="uploads/<?= $row['image'] ?>" width="100"><br><br>
                                    <label>Change Image:</label>
                                    <input type="file" name="image" style="width: 100%;"><br>

                                    <div class="modal-footer">
                                        <!-- <a href="packages.php" name="updatePackage" class="btn btn-info">Update Packages</a> -->
                                    <button type="submit" name="updatePackage" class="btn btn-info">Update Package</button>
                                    </div>
                                <?php
                            }
                        }
                        else
                        {
                            echo "<h4>No Record Found</h4>";
                        }
                    }
                    
                    ?>                

                    
                </div>
            </form>
            </div>
        </div>
    </div>

</div>

<?php include('includes/footer.php'); ?>
<?php include('includes/script.php'); ?>