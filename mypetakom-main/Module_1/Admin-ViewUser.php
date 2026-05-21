<?php
include('../Databased/db_connect.php');

if (isset($_GET['id'])) {
    $userId = $_GET['id'];
    $sql = "SELECT * FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
}
  include '../HADER_SIDER_FOOTER/HST.PHP';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View User - Petakom Coordinator</title>
  <link rel="stylesheet" href="../CSS/MODULE_1_css/style.css">
</head>
<body>
  

  <div class="main-container">
   
    <!-- Main Content -->
	<div class="content" style="flex-grow: 1; padding: 20px; background-color: #ffffff; border-radius: 8px; margin: 20px;">
		<h2>User Details</h2>
		<?php if ($user): ?>
			<p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
			<p><strong>Full Name:</strong> <?= htmlspecialchars($user['name']) ?></p>
			<p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
			<p><strong>Role:</strong> <?= htmlspecialchars($user['user_role']) ?></p>
			<br>
			<a href="../Module_1/Admin-ManageUserProfiles.php" class="btn">Back to User List</a>
		<?php else: ?>
			<p>User not found.</p>
		<?php endif; ?>
	</div>
  </div>
</body>
</html>
