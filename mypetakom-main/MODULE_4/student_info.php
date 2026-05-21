<?php
include '../../Databased/db_connect.php';

if (!isset($_GET['student_id'])) {
    echo "Student ID is missing.";
    exit;
}

$student_id = (int) $_GET['student_id'];

// Fetch student info
$info_sql = "SELECT s.student_name, s.program, s.faculty, u.email
             FROM student s 
             JOIN users u ON s.user_id = u.user_id 
             WHERE s.user_id = ?";
$stmt = $conn->prepare($info_sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Student not found.";
    exit;
}

$student = $result->fetch_assoc();

// Fetch total merits for current academic year
$current_year = date('Y');
$merit_sql = "SELECT SUM(m.points) AS total_merits 
              FROM merit m
              JOIN attendance a ON m.event_id = a.event_id AND m.user_id = a.user_id
              WHERE m.user_id = ? AND m.academic_year = ? AND a.status_attd = 'present'";
$stmt2 = $conn->prepare($merit_sql);
$stmt2->bind_param("is", $student_id, $current_year);
$stmt2->execute();
$merit_result = $stmt2->get_result();
$merit = $merit_result->fetch_assoc();
$total_merits = $merit['total_merits'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Student Merit Info</title>
  <style>
    body { font-family: Arial; padding: 20px; background: #f4f4f4; }
    .card { background: white; padding: 20px; border-radius: 8px; max-width: 500px; margin: auto; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    h2 { color: #14519c; }
  </style>
</head>
<body>

<div class="card">
  <h2><?= htmlspecialchars($student['student_name']) ?></h2>
  <p><strong>Program:</strong> <?= htmlspecialchars($student['program']) ?></p>
  <p><strong>Faculty:</strong> <?= htmlspecialchars($student['faculty']) ?></p>
  <p><strong>Email:</strong> <?= htmlspecialchars($student['email']) ?></p>
  <hr>
  <p><strong>Total Merits (<?= $current_year ?>):</strong> <?= $total_merits ?></p>
</div>

</body>
</html>
