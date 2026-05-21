<?php
include('../Databased/db_connect.php');


// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $fullname = $_POST['fullname'];
  $email = $_POST['email'];
  $username = $_POST['username'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash password
  $role = $_POST['role'];

  // Insert into database including full name
  $sql = "INSERT INTO users (name, username, email, password, user_role) VALUES (?, ?, ?, ?, ?)";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("sssss", $fullname, $username, $email, $password, $role);

  if ($stmt->execute()) {
	echo '<script type="text/javascript">
		alert("User account created successfully.");
		window.location.href = "Admin-CreateUserAccount.php";
  </script>';
	} else {
		echo '<script type="text/javascript">
		alert("Error: ' . addslashes($stmt->error) . '");
  </script>';
	}
  $stmt->close();
}

  include '../HADER_SIDER_FOOTER/HST.PHP';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Petakom Coordinator (Administrator)</title>
  <link rel="stylesheet" href="../CSS/MODULE_1_css/style.css">
</head>
<body>

  <div class="main-container">


    <div class="admin-container">
      <h1>Create User Account</h1>
      <form class="user-form" method="post" autocomplete="off">
        <div class="form-group">
          <label for="fullname">Full Name</label>
          <input type="text" id="fullname" name="fullname" required>
        </div>
        <div class="form-group">
          <label for="username">Username</label>
          <input type="text" id="username" name="username" required>
        </div>
        <div class="form-group">
          <label for="email">Email Address</label>
          <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" autocomplete="new-password" required>
        </div>
        <div class="form-group">
          <label for="role">User Role</label>
          <select id="role" name="role" required>
            <option value="">-- Select Role --</option>
            <option value="admin">Petakom Coordinator (Administrator)</option>
            <option value="advisor">Event Advisor</option>
            <option value="student">Student</option>
          </select>
        </div>
        <div class="form-buttons">
          <button type="submit" class="btn btn-primary">Create Account</button>
          <button type="reset" class="btn btn-secondary">Cancel</button>
        </div>
      </form>
    </div>
  </div>
  <script>
	  document.getElementById('logoutButton').addEventListener('click', function(event) {
		event.preventDefault(); // Prevent the default anchor behavior

		const confirmLogout = confirm("Are you sure you want to log out?");
		if (confirmLogout) {
		  // Redirect to login page
		  window.location.href = 'login.php'; // Replace with your actual login page
		}
	  });
  </script>

</body>
</html>