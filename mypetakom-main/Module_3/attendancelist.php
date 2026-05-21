<?php
// attendancelist.php
session_start();
include '../../Databased/db_connect.php';

// Get the event ID from the GET parameter
$eventId = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;

// Fetch event name
$eventName = "Event";
$eventStmt = $conn->prepare("SELECT title FROM events WHERE event_id = ?");
$eventStmt->bind_param("i", $eventId);
$eventStmt->execute();
$eventStmt->bind_result($eventName);
$eventStmt->fetch();
$eventStmt->close();

// Fetch attendance data
$stmt = $conn->prepare("SELECT a.*, s.student_id_card, s.student_name, u.username FROM attendance a 
                       JOIN users u ON a.user_id = u.user_id 
                       JOIN student s ON u.user_id = s.user_id 
                       WHERE a.event_id = ?");
$stmt->bind_param("i", $eventId);
$stmt->execute();
$result = $stmt->get_result();

include '../HADER_SIDER_FOOTER/HST.PHP';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>MyPetakom System – Attendance List</title>
  <link rel="stylesheet" href="viewatd.css">
</head>
<body>

  <div class="content">
    <h2><?= htmlspecialchars($eventName) ?> – Attendance List</h2>

    <div class="table-wrap">
      <table id="attd-table">
        <thead>          <tr>
            <th>No</th>
            <th>Student ID</th>
            <th>Username</th>
            <th>Student Name</th>
            <th>Committee</th>
            <th>Status</th>
            <th>Date</th>
            <th>Time</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $i = 1;
          while ($row = $result->fetch_assoc()):
          ?>          <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($row['student_id_card']) ?></td>
            <td><?= htmlspecialchars($row['username']) ?></td>
            <td><?= htmlspecialchars($row['student_name']) ?></td>
            <td><?= htmlspecialchars($row['status_attd'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($row['status_attd']) ?></td>
            <td><?= date('M d, Y', strtotime($row['timestamp'])) ?></td>
            <td><?= date('h:i A', strtotime($row['timestamp'])) ?></td>
          </tr>
          <?php endwhile; ?>
          <?php if ($i === 1): ?>
            <tr><td colspan="8">No attendance records found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
