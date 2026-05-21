<?php
include('../Databased/db_connect.php');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM users";
$result = $conn->query($sql);

    include '../HADER_SIDER_FOOTER/HST.PHP';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Petakom Coordinator (Administrator) - Manage User Profiles</title>
  <link rel="stylesheet" href="../CSS/MODULE_1_css/style.css"/>
</head>
<body>
  <?php
  if (isset($_GET['msg'])) {
      if ($_GET['msg'] == 'deleted') {
          echo "<script>alert('User deleted successfully.');</script>";
      } elseif ($_GET['msg'] == 'updated') {
          echo "<script>alert('User updated successfully.');</script>";
      }
  }


  ?>
  

  <div class="main-container">
   

    <div class="dashboard">
      <div class="container">
        <h2>Manage User Profiles</h2>

        <div class="user-profile-table">
          <table>
            <thead>
              <tr>
                <th>User ID</th>
                <th>Username</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="userTableBody">
              <?php
              if ($result->num_rows > 0) {
                  while($row = $result->fetch_assoc()) {
                      echo "<tr>
                          <td>{$row['user_id']}</td>
                          <td>{$row['username']}</td>  
                          <td>{$row['name']}</td>
                          <td>{$row['email']}</td>
                          <td>{$row['user_role']}</td>
                          <td>
                            <a class='view-btn' href='Admin-ViewUser.php?id={$row['user_id']}'>View</a>
                            <a class='edit-btn' href='Admin-EditUser.php?id={$row['user_id']}'>Edit</a>
                            <a class='delete-btn' href='Admin-DeleteUser.php?id={$row['user_id']}' onclick='return confirm(\"Are you sure you want to delete this user?\");'>Delete</a>
                          </td>
                      </tr>";
                  }
              } else {
                  echo "<tr><td colspan='6'>No users found.</td></tr>";
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- JavaScript -->
  <script>
    document.getElementById('logoutButton').addEventListener('click', function(event) {
      event.preventDefault(); // Prevent immediate navigation
      const confirmLogout = confirm("Are you sure you want to log out?");
      if (confirmLogout) {
        window.location.href = 'Login.php'; // Redirect to logout handler
      }
    });
  </script>
</body>
</html>

<?php
$conn->close();
?>
