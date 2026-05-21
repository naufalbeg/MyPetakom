<?php
       
session_start();
 include '../../Databased/db_connect.php';
// Use logged-in user_id from session
$stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$result = $stmt->get_result();

$row = $result->fetch_assoc();
$user_id = $row['user_id'];

if ($user_id == 0) {
    // Not logged in - redirect to login page or show error
    header("Location: login.php");
    exit();
}

// Fetch latest membership and student info for this user
$sql = "SELECT s.student_id, s.student_name, s.program, s.semester, s.faculty, s.student_id_card, m.join_date, m.status
        FROM student s 
        JOIN membership m ON s.user_id = m.user_id 
        WHERE s.user_id = ? 
        ORDER BY m.join_date DESC
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
  include '../HADER_SIDER_FOOTER/HST.PHP';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Petakom Membership Status</title>
  <link rel="stylesheet" href="../CSS/MODULE_1_css/style.css" />
  <style>
    .preview-img {
      max-height: 200px;
      margin-top: 15px;
      border: 1px solid #ccc;
      border-radius: 8px;
    }

    .student-details {

      padding: 20px;
      border-radius: 10px;
      margin-top: 20px;
      width: 80%;         /* Wider box */
      max-width: 900px;   /* Prevent too wide on large screens */
      margin-left: auto;
      margin-right: auto;
    }

    .info-item {
      margin-bottom: 10px;
      font-size: 16px;
    }

    .info-item strong {
      width: 180px;
      display: inline-block;
    }

body {
  background-color: #f0f0f0; 
}


  </style>
</head>
<body>
  
  <div class="main-container">
   

    <div class="dashboard">
      <div class="container">
        <h2>Petakom Membership Status</h2>

        <?php if ($student): ?>
          <div class="student-details">
            <h3>Student Details</h3>
            <div class="info-item"><strong>Student ID:</strong> <?= htmlspecialchars($student['student_id']) ?></div>
            <div class="info-item"><strong>Name:</strong> <?= htmlspecialchars($student['student_name']) ?></div>
            <div class="info-item"><strong>Program:</strong> <?= htmlspecialchars($student['program']) ?></div>
            <div class="info-item"><strong>Semester:</strong> <?= htmlspecialchars($student['semester']) ?></div>
            <div class="info-item"><strong>Faculty:</strong> <?= htmlspecialchars($student['faculty']) ?></div>
            <div class="info-item">
              <strong>Student Card:</strong>
              <?php if ($student['student_id_card']): ?>
                <a href="<?= htmlspecialchars($student['student_id_card']) ?>" target="_blank">View Uploaded Card</a>
              <?php else: ?>
                No card uploaded.
              <?php endif; ?>
            </div>

            <h3 style="margin-top: 30px;">Membership Information</h3>
            <div class="info-item"><strong>Join Date:</strong> <?= htmlspecialchars($student['join_date']) ?></div>
            <div class="info-item"><strong>Status:</strong> <?= htmlspecialchars(ucfirst($student['status'])) ?></div>
          </div>
        <?php else: ?>
          <p>No membership record found.</p>
        <?php endif; ?>
      </div>
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
