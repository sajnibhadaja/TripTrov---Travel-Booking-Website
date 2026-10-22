<?php
    session_start();
    include('includes/header.php');
    // include('includes/topbar.php');
    include('includes/slidebar.php');
    include('config/dbcon.php');

    if (isset($_POST['delete_itineraries'])) {
      $id = $_POST['delete_id'];
  
      $query = "DELETE FROM itineraries WHERE itineray_id='$id'";
      $query_run = mysqli_query($conn, $query);
  
      if ($query_run) {
          $_SESSION['status'] = "Itineray Deleted Successfully";
      } else {
          $_SESSION['status'] = "Itineray Deletion Failed";
      }
    }
?>

<div class="content-wrapper">


<!-- Modal -->
<div class="modal fade" id="addusermodel" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Add Itineraries</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      <section class="login-container">        
        <div class="login-box" id="register">
            <form action="code.php" method="POST" enctype="multipart/form-data">
        
              <label>Package</label>
              <select name="package_name" class="form-control" required>
                  <option value="">Select Package</option>
                  <?php
                  $package_query = "SELECT * FROM packages";
                  $package_run = mysqli_query($conn, $package_query);
                  while ($package = mysqli_fetch_assoc($package_run)) {
                      echo "<option value='".$package['package_name']."'>".$package['package_name']."</option>";
                  }
                  ?>
              </select>

              <label>Duration:</label><br>
              <input type="text" name="duration" required><br>

              <label>Description:</label><br>
              <textarea name="description" required></textarea><br>

              <label>Departure Location:</label><br>
              <input type="text" name="departure_location" required><br>

              <label>Destination:</label><br>
              <input type="text" name="destination" required><br>

              <label>Budget:</label><br>
              <input type="number" name="budget" required><br>

              <label>Transport:</label><br>
              <input type="text" name="transport" required><br>

              <label>Image:</label><br>
              <input type="file" name="image" required><br>

              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" name="itinerary" class="btn btn-primary">Save Itineraries</button>
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
            <h1 class="m-0 text-dark">Package Itineraries</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Itineraries </li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <div class="card">
    <div class="card-header">
        <h3 class="card-title">DataTable for Packages</h3>
        <a href="" data-toggle="modal" data-target="#addusermodel" class="btn btn-primary float-sm-right">Add Itineraries</a>
    </div>
    <!-- /.card-header -->

    
    <div class="card-body">
        <table id="example1" class="table table-bordered table-striped">
            <thead>
                <tr>
                  <th>ID</th>
                  <th>Package Name</th>
                  <th>Describtion</th>
                  <th>Duration</th>
                  <th>Budget</th>
                  <th>Transport</th>
                  <th>Image</th>
                  <th>Departure Location</th>
                  <th>Destination</th>
                  <th>Action</th>
                </tr>
            </thead>
            <?php
              $query = "select * from itineraries";
              $query_run = mysqli_query($conn,$query);

              if(mysqli_num_rows($query_run) > 0)
              {
                foreach($query_run as $row)
                {
                  ?>
                    <tr>
                      <td><?php echo $row['itineray_id']; ?></td>
                      <td><?php echo $row['package_name']; ?></td>
                      <td><?php echo $row['description']; ?></td>
                      <td><?php echo $row['duration']; ?></td>
                      <td><?php echo $row['budget']; ?></td>
                      <td><?php echo $row['transport']; ?></td>                      
                      <td><?php echo $row['image']; ?></td>
                      <td><?php echo $row['departure_location']; ?></td>
                      <td><?php echo $row['destination']; ?></td>
                      <td>
                      <a href="itineraries-edit.php?user_id=<?php echo $row['itineray_id']; ?>" class="btn btn-info">Edit</a>
                      <form action="" method="POST" style="display:inline-block;">
                          <input type="hidden" name="delete_id" value="<?= $row['itineray_id'] ?>">
                          <button type="submit" name="delete_itineraries" class="btn btn-danger">Delete</button>
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
<?php include('includes/script.php'); ?>