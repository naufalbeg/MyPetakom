<?php
// attendancelist.php
require_once '../Module_1/session_config.php';
requireLogin();
include '../Databased/db_connect.php';

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
  <style>
    .export-bar {
      display: flex;
      gap: 10px;
      margin: 12px 0 16px 0;
    }
    .btn-export {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 8px 16px;
      border: none;
      border-radius: 4px;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      color: #fff;
    }
    .btn-csv { background: #217346; }
    .btn-pdf { background: #c0392b; }
    .btn-export:hover { opacity: 0.88; }
  </style>
</head>
<body>

  <div class="content">
    <h2><?= htmlspecialchars($eventName) ?> – Attendance List</h2>

    <div class="export-bar" style="display:flex;gap:10px;margin:12px 0 16px 0;justify-content:flex-end;">
      <a class="btn-export btn-csv"
         href="exportatd.php?event_id=<?= $eventId ?>&format=csv">
        &#128196; Export CSV
      </a>
      <a class="btn-export btn-pdf"
         href="exportatd.php?event_id=<?= $eventId ?>&format=pdf"
         target="_blank">
        &#128438; Export PDF
      </a>
    </div>

    <div class="table-wrap">
      <table id="attd-table">
        <thead>
          <tr>
            <th>No</th>
            <th>Student ID</th>
            <th>Username</th>
            <th>Student Name</th>
            <th>Status</th>
            <th>Date</th>
            <th>Time</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $i = 1;
          while ($row = $result->fetch_assoc()):
          ?>
          <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($row['student_id_card']) ?></td>
            <td><?= htmlspecialchars($row['username']) ?></td>
            <td><?= htmlspecialchars($row['student_name']) ?></td>
            <td><?= htmlspecialchars($row['status_attd'] ?? 'N/A') ?></td>
            <td><?= date('M d, Y', strtotime($row['timestamp'])) ?></td>
            <td><?= date('h:i A', strtotime($row['timestamp'])) ?></td>
          </tr>
          <?php endwhile; ?>
          <?php if ($i === 1): ?>
            <tr><td colspan="7">No attendance records found.</td></tr>
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
