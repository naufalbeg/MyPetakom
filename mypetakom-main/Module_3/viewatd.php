<?php
session_start();
include '../../Databased/db_connect.php';

// Get all attendance events
$sql = "SELECT a.*, e.title as event_name, u.username, e.start_date 
        FROM attendance a 
        JOIN events e ON a.event_id = e.event_id 
        JOIN users u ON a.user_id = u.user_id 
        ORDER BY a.timestamp DESC";
$result = $conn->query($sql);
include '../HADER_SIDER_FOOTER/HST.PHP';
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>View Attendance</title>
  <link rel="stylesheet" href="viewatd.css">
  <style>
    .content {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      width: 100%;
      padding: 20px;
    }
    
    .table-wrap {
      display: flex;
      justify-content: center;
      width: 100%;
      margin: 20px 0;
    }
    
    #attd-table {
      margin: 0 auto;
      max-width: 95%;
    }
  </style>
</head>
<body>

    <div class="content">
      <h2>View Attendance</h2>
      <div class="table-wrap">
        <table id="attd-table">
          <thead>
            <tr>
              <th>No</th>
              <th>Attendance Name</th>
              <th>Event Name</th>
              <th>Status</th>
              <th>Start Time</th>
              <th>End Time</th>
              <th>Location</th>
              <th>View List</th>
            </tr>
          </thead>
          <tbody>            <?php $i=1; while($row = $result->fetch_assoc()): ?>
              <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= htmlspecialchars($row['event_name']) ?></td>
                <td style="color:green; font-weight:bold;"><?= htmlspecialchars($row['status_attd']) ?></td>
                <td><?= date('M d, Y', strtotime($row['start_date'])) ?></td>
                <td><?= date('M d, Y h:i A', strtotime($row['timestamp'])) ?></td>
                <td><?= htmlspecialchars($row['location_verified']) ?></td>
                <td><a href="attendancelist.php?event_id=<?= $row['event_id'] ?>" class="delete-btn">View</a></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</body>
</html>
