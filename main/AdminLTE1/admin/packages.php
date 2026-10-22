<?php
    session_start();
    include('includes/header.php');
    // include('includes/topbar.php');
    include('includes/slidebar.php');
    include('config/dbcon.php');

    if (isset($_POST['delete_package'])) {
      $id = $_POST['delete_id'];
  
      $query = "DELETE FROM packages WHERE package_id='$id'";
      $query_run = mysqli_query($conn, $query);
  
      if ($query_run) {
          $_SESSION['status'] = "Package Deleted Successfully";
      } else {
          $_SESSION['status'] = "Package Deletion Failed";
      }
    }
  
?>

<div class="content-wrapper">


<!-- Modal -->
<div class="modal fade" id="addusermodel" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Add Package</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      <section class="login-container">        
        <div class="login-box" id="register">
            <form action="code.php" method="POST" enctype="multipart/form-data">
        
                <label for="category">Category:</label><br>
                <select name="category" required>
                    <option value="International">International</option>
                    <option value="National">National</option>
                    <option value="Adventure">Adventure</option>
                </select><br><br>

                <label>Package Name:</label><br>
                <input type="text" name="package_name" required placeholder="Package Name"><br>

                <label>Description:</label><br>
                <textarea name="description" required placeholder="Description"></textarea><br>

                <label>Price (in INR):</label><br>
                <input type="number" name="price" step="0.01" required placeholder="Price"><br>

                <label>Duration:</label><br>
                <input type="text" name="duration" required placeholder="Duration"><br>

                <label>Upload Image:</label><br>
                <input type="file" name="image" required placeholder="Upload Image"><br>

                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                  <button type="submit" name="addpackage" class="btn btn-primary">Save Package</button>
                </div>
            </form>
        </div>
    </section>
      </div>
    </div>
  </div>
</div>

<div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 text-dark">User Packages</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">User Package </li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <div class="card">
    <div class="card-header">
        <h3 class="card-title">DataTable for Packages</h3>
        <a href="" data-toggle="modal" data-target="#addusermodel" class="btn btn-primary float-sm-right">Add Package</a>
    </div>
    <!-- /.card-header -->

    
    <div class="card-body">
        <table id="example1" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Category</th>
                    <th>Package_Name</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Duration</th>
                    <th>Image</th>
                    <th>Action</th>
                </tr>
            </thead>
            <?php
              $query = "select * from packages";
              $query_run = mysqli_query($conn,$query);

              if(mysqli_num_rows($query_run) > 0)
              {
                foreach($query_run as $row)
                {
                  ?>
                    <tr>
                      <td><?php echo $row['package_id']; ?></td>
                      <td><?php echo $row['category']; ?></td>
                      <td><?php echo $row['package_name']; ?></td>
                      <td><?php echo $row['description']; ?></td>
                      <td><?php echo $row['price']; ?></td>
                      <td><?php echo $row['duration']; ?></td>
                      <td><?php echo $row['image']; ?></td>
                      <td>
                        <a href="packages-edit.php?user_id=<?php echo $row['package_id']; ?>" class="btn btn-info">Edit</a>
                        <form action="" method="POST" style="display:inline-block;">
                          <input type="hidden" name="delete_id" value="<?= $row['package_id'] ?>">
                          <button type="submit" name="delete_package" class="btn btn-danger">Delete</button>
                        </form>
                      </td>
                    </tr>
                  <?php
                }
              }
            ?>  
        </table>
    </div>
</div>
</div>



<?php include('includes/footer.php'); ?>

<script>
  $(document).ready(function () {
    $(deletebtn).click(function (e) {
      e.preventDefault();
    });
  });
</script>

<?php include('includes/script.php'); ?>