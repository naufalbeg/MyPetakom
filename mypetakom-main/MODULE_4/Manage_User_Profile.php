<?php
session_start();
include '../../Databased/db_connect.php';

if (
    !isset($_SESSION['username'], $_SESSION['userRole'])
    || $_SESSION['userRole'] !== 'student'
) {
    header("Location: ../Module_1/Login.php");
    exit;
}



// 2️ Look up the real user_id from the DB
$stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    // Something’s wrong—kick back to login
    header("Location: ../Module_1/Login.php");
    exit;
}

$row = $result->fetch_assoc();
$user_id = $row['user_id'];

// Load current values
$q = $conn->prepare(
    "SELECT u.username, u.email,
            s.student_name, s.student_id_card, s.program, s.semester, s.faculty
       FROM users u
  LEFT JOIN student s USING(user_id)
      WHERE u.user_id = ?"
);
$q->bind_param("i", $user_id);
$q->execute();
$row = $q->get_result()->fetch_assoc();
$q->close();

$username         = $row['username']        ?? '';
$email            = $row['email']           ?? '';
$student_name     = $row['student_name']    ?? '';
$student_id_card  = $row['student_id_card'] ?? '';
$program          = $row['program']         ?? '';
$semester         = $row['semester']        ?? '';
$faculty          = $row['faculty']         ?? '';

// Include header and sidebar
include '../HADER_SIDER_FOOTER/HST.PHP';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage User Profile – Student Dashboard</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../CSS/MODULE_4_css/manage_profile.css">
</head>
<body>

  <div class="form-container">
    <h2><i class="fas fa-user"></i> Manage Your Profile</h2>


  <div class="form-view">
      <div class="form-group">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required value="<?= htmlspecialchars($username) ?>" disabled>
      </div>
      <div class="form-group">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" required value="<?= htmlspecialchars($email) ?>" disabled>
      </div>

      <hr>

      <h3>Student Details</h3>
      <div class="form-group">
        <label for="student_name">Full Name</label>
        <input type="text" id="student_name" name="student_name" value="<?= htmlspecialchars($student_name) ?>" disabled>
      </div>
      <div class="form-group">
        <label for="student_id_card">Student ID Card</label>
        <input type="text" id="student_id_card" name="student_id_card" readonly value="<?= htmlspecialchars($student_id_card) ?>" disabled>
      </div>
      <div class="form-group">
        <label for="program">Program</label>
        <input type="text" id="program" name="program" readonly value="<?= htmlspecialchars($program) ?>" disabled>
      </div>
      <div class="form-group">
        <label for="semester">Semester</label>
        <input type="text" id="semester" name="semester" readonly value="<?= htmlspecialchars($semester) ?>" disabled>
      </div>
      <div class="form-group">
        <label for="faculty">Faculty</label>
        <input type="text" id="faculty" name="faculty" readonly value="<?= htmlspecialchars($faculty) ?>" disabled>
      </div>

    </div>
  </div>

</body>
</html>

<?php
$conn->close();
?>
