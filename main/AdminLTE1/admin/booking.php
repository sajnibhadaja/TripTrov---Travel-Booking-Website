<?php
session_start();
    include('includes/header.php');
    // include('includes/topbar.php');
    include('includes/slidebar.php');
    include('config/dbcon.php');
?>

<div class="content-wrapper">
<div class="modal fade" id="addusermodel" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Add User</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      <section class="login-container">        
        <div class="login-box" id="register">
            <h2>Register</h2>
            
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
            <h1 class="m-0 text-dark">Booking</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Booking</li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <div class="card">
    <div class="card-header">
        <h3 class="card-title">DataTable for Booking Packages</h3>
    </div>
    <!-- /.card-header -->

    
    <div class="card-body">
        <table id="example1" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Destination</th>
                    <th>Duration</th>
                    <th>Price</th>
                    <th>Departure Date</th>
                    <th>Arrival Date</th>
                    <th>Payment ID</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <?php
              $query = "select * from booking1 order by id desc";
              $query_run = mysqli_query($conn,$query);

              if(mysqli_num_rows($query_run) > 0)
              {
                foreach($query_run as $row)
                {
                  $paymentId = !empty($row['payment_id']) ? $row['payment_id'] : 'N/A';
                  $status = !empty($row['payment_status']) ? $row['payment_status'] : 'Paid';
                  ?>
                    <tr>
                      <td><?php echo $row['id']; ?></td>
                      <td><?php echo $row['firstName']; ?></td>
                      <td><?php echo $row['lastName']; ?></td>
                      <td><?php echo $row['email']; ?></td>
                      <td><?php echo $row['destination']; ?></td>
                      <td><?php echo $row['duration']; ?></td>
                      <td>₹<?php echo number_format($row['price'], 2); ?></td>
                      <td><?php echo $row['departure_date']; ?></td>
                      <td><?php echo $row['arrival_date']; ?></td>
                      <td><span class="badge badge-info"><?php echo htmlspecialchars($paymentId); ?></span></td>
                      <td><span class="badge badge-success"><?php echo htmlspecialchars($status); ?></span></td>
                      <td>
                        <a href="../../receipt.php?booking_id=<?php echo $row['id']; ?>" target="_blank" class="btn btn-sm btn-primary">
                          <i class="fas fa-file-invoice"></i> Receipt
                        </a>
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