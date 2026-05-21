<?php
        include '../../Databased/db_connect.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle approve/reject actions
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && isset($_POST['id'])) {
    $id = $_POST['id'];
    $status = $_POST['action'] === "approve" ? "approved" : "rejected";

    $update = "UPDATE membership SET status = ? WHERE membership_id = ?";
    $stmt = $conn->prepare($update);
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
    $stmt->close();
}

// Fetch all memberships with student info, ordered by status and student name
$sql = "
  SELECT m.membership_id, s.student_id, s.student_name, s.program, s.semester, s.student_id_card, m.status
  FROM membership m
  JOIN student s ON m.user_id = s.user_id
  ORDER BY FIELD(m.status, 'pending', 'approved', 'rejected'), s.student_name
";
$result = $conn->query($sql);
  include '../HADER_SIDER_FOOTER/HST.PHP';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Petakom Coordinator - Manage Memberships</title>
    <link rel="stylesheet" href="style.css" />
    <style>
      table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
      }
      th, td {
        padding: 10px;
        border: 1px solid #ccc;
        text-align: left;
      }
      .approve-btn {
        background-color: #4CAF50;
        color: white;
        border: none;
        padding: 5px 10px;
        cursor: pointer;
      }
      .reject-btn {
        background-color: #f44336;
        color: white;
        border: none;
        padding: 5px 10px;
        cursor: pointer;
      }
      em {
        color: #555;
        font-style: normal;
        font-size: 0.9em;
      }
    </style>
</head>
<body>


<div class="main-container">
    
    <div class="dashboard">
        <h2>Students Membership Approval</h2>

        <table>
            <tr>
                <th>Student ID</th>
                <th>Full Name</th>
                <th>Program</th>
                <th>Semester</th>
                <th>Student ID Card</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>

            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['student_id']) ?></td>
                        <td><?= htmlspecialchars($row['student_name']) ?></td>
                        <td><?= htmlspecialchars($row['program']) ?></td>
                        <td><?= htmlspecialchars($row['semester']) ?></td>
                        <td><a href="<?= htmlspecialchars($row['student_id_card']) ?>" target="_blank">View</a></td>
                        <td><?= ucfirst($row['status']) ?></td>
                        <td>
                            <?php if ($row['status'] === 'pending'): ?>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= $row['membership_id'] ?>">
                                    <button type="submit" name="action" value="approve" class="approve-btn">Approve</button>
                                </form>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= $row['membership_id'] ?>">
                                    <button type="submit" name="action" value="reject" class="reject-btn">Reject</button>
                                </form>
                            <?php else: ?>
                                <em>No actions available</em>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="7">No membership records found.</td></tr>
            <?php endif; ?>
        </table>
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
<?php $conn->close(); ?>
