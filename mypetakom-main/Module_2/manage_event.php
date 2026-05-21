<?php
include '../../Databased/db_connect.php';
// Optional: use session to get current user's ID if needed
// session_start();
// $user_id = $_SESSION['user_id'];

// Example SQL: show all events. Add WHERE clause if needed
$sql = "SELECT * FROM events";
// Example filtered query: WHERE created_by = '$user_id'

$result = $conn->query($sql);

  include '../HADER_SIDER_FOOTER/HST.PHP';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Events</title>
<link rel="stylesheet" href="../CSS/MODULE_2_css/styleadvisor.css">
</head>
<body>

<div class="container">
	<!-- Sidebar -->
   
        <!-- Main Content -->
    <div class="main-content">
        
	
        <h2>Manage Events</h2>
		<div class="table-wrapper">
        <div class="event-list">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Event Name</th>
						<th>Description</th>
                        <th>Status</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Location</th>
                        <th>Geolocation</th>
                        <th>Update</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result && $result->num_rows > 0) {
                        $no = 1;
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $no++ . "</td>";
                            echo "<td>" . htmlspecialchars($row['title']) . "</td>";
							echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                            echo "<td class='" . strtolower($row['event_status']) . "-status'>" . htmlspecialchars($row['event_status']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['start_date']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['end_date']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['location']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['geographic_location']) . "</td>";
                            echo "<td>
								<a href='edit_event.php?id=" . $row['event_id'] . "'><button>Edit</button>
								<a href='delete_event.php?id=" . $row['event_id'] . "'><button>Delete</button>
							  </td>";


                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='9'>No events found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        
       
    </div>
</div>

</body>
</html>
