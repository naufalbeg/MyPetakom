<?php
session_start();
include '../../Databased/db_connect.php';

$sql = "SELECT a.*, e.title as event_name, u.username
        FROM attendance a
        JOIN events e ON a.event_id = e.event_id
        JOIN users u ON a.user_id = u.user_id
        ORDER BY a.timestamp DESC";

$result = $conn->query($sql);

include '../HADER_SIDER_FOOTER/HST.PHP';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Attendance History</title>
  <link rel="stylesheet" href="historyatd.css">
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
      max-width: 90%;
    }
  </style>
</head>
<body>
  
    <div class="content">
      <h2>Attendance History</h2>
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
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php        $query = "
          SELECT 
            u.username as attendance_name,
            e.title as event_name, 
            e.location as location_name,
            e.start_date,
            e.end_date,
            e.start_date as attendance_date,
            a.status_attd as status
          FROM attendance a
          JOIN events e ON a.event_id = e.event_id
          JOIN users u ON a.user_id = u.user_id
          ORDER BY e.start_date DESC
        ";

        $result = $conn->query($query);
        $no = 1;

        if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {            echo "<tr>
              <td>{$no}</td>
              <td>{$row['attendance_name']}</td>
              <td>{$row['event_name']}</td>
              <td>{$row['status']}</td>
              <td>" . date('M d, Y', strtotime($row['start_date'])) . "</td>
              <td>" . date('M d, Y', strtotime($row['end_date'])) . "</td>
              <td>{$row['location_name']}</td>
              <td>" . date('M d, Y', strtotime($row['attendance_date'])) . "</td>
            </tr>";
            $no++;
          }
        } else {
          echo "<tr><td colspan='8'>No attendance history found.</td></tr>";
        }
        ?>
        </table>
      </div>
    </div>
  </div>
</body>
</html>
