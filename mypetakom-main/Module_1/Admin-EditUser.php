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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    $updateSql = "UPDATE users SET name = ?, email = ?, user_role = ? WHERE user_id = ?";
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param("sssi", $name, $email, $role, $userId);

    if ($stmt->execute()) {
        header("Location: Admin-ManageUserProfiles.php?msg=updated");
        exit();
    } else {
        echo "Error updating user: " . $conn->error;
    }
}
  include '..//HADER_SIDER_FOOTER/HST.PHP';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Petakom Coordinator (Administrator)</title>
  <link rel="stylesheet" href="../CSS/MODULE_1_css/style.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
 

  <div class="main-container">
   

    <!-- Content Area -->
    <div class="content" style="
      background-color: #ffffff;
      padding: 40px;
      border-radius: 8px;
      margin: 20px auto;
      max-width: 1000px;
      max-height: 600px;
      overflow-y: auto;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      flex-grow: 1;
    ">
      <h2>Edit User</h2>
      <form method="post">
        <label><strong>Full Name:</strong></label><br>
        <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required><br><br>

        <label><strong>Email:</strong></label><br>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required><br><br>

        <label><strong>Role:</strong></label><br>
        <input type="text" name="role" value="<?= htmlspecialchars($user['user_role']) ?>" required><br><br>

        <input type="submit" value="Update">
        <a href="../Module_1/Admin-ManageUserProfiles.php" style="margin-left: 10px;">Cancel</a>
      </form>
    </div>
  </div>
</body>
</html>
