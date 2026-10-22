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
            <form id="register-form" action="code.php" method="POST">
                <label for="first-name">First Name</label>
                <input type="text" id="first-name" name="firstName" required><br>
                
                <label for="last-name">Last Name</label>
                <input type="text" id="last-name" name="lastName" required><br>
                
                <label for="register-email">Email</label>
                <input type="email" id="register-email" name="email" required><br>
                
                <label for="register-password">Password</label>
                <input type="password" id="register-password" name="password" required>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" name="adduser" class="btn btn-primary">Save</button>
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
            <h1 class="m-0 text-dark">User Registered</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">User Registered </li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <div class="card">
    <div class="card-header">
        <h3 class="card-title">DataTable for User Registered</h3>
    </div>
    <!-- /.card-header -->

    
    <div class="card-body">
        <table id="example1" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>FirstName</th>
                    <th>LastName</th>
                    <th>Email</th>
                    <th>Password</th>
                </tr>
            </thead>
            <?php
              $query = "select * from users";
              $query_run = mysqli_query($conn,$query);

              if(mysqli_num_rows($query_run) > 0)
              {
                foreach($query_run as $row)
                {
                  ?>
                    <tr>
                      <td><?php echo $row['user_id']; ?></td>
                      <td><?php echo $row['firstName']; ?></td>
                      <td><?php echo $row['lastName']; ?></td>
                      <td><?php echo $row['email']; ?></td>
                      <td><?php echo $row['password']; ?></td>
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