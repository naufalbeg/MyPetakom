<?php

include '../../Databased/db_connect.php';

$student_id_card = isset($_GET['student_id_card']) ? $_GET['student_id_card'] : null;
$event_id = isset($_GET['event_id']) ? $_GET['event_id'] : null;
$no = 1;
$user_id = null;

// Get user_id using student_id_card
if ($student_id_card) {
    $student_query = mysqli_query($conn, "SELECT user_id FROM student WHERE student_id_card = '$student_id_card'");
    if ($student_query && mysqli_num_rows($student_query) > 0) {
        $student_row = mysqli_fetch_assoc($student_query);
        $user_id = $student_row['user_id'];
    }
}

  include '../HADER_SIDER_FOOTER/HST.PHP';
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Committees</title>
<link rel="stylesheet" href="../CSS/MODULE_2_css/styleadvisor.css">
</head>
<body>
		<div class="container">
		
		

        <!-- Main Content -->
        <div class="main-content">



                <!-- Committees List -->
                <h2>Committees List</h2>
				
				<form method="get" action="">
					<br>
					<h3><label for="event_id">Select Event:</label></h3>
					</br>
					<select name="event_id" id="event_id" onchange="this.form.submit()">
						<option value="">-- Select Event --</option>
						<?php
						$events = mysqli_query($conn, "SELECT event_id, title FROM events");
						while ($event = mysqli_fetch_assoc($events)) {
							$selected = (isset($_GET['event_id']) && $_GET['event_id'] == $event['event_id']) ? "selected" : "";
							echo "<option value='{$event['event_id']}' $selected>{$event['title']}</option>";
						}
						?>
					</select>
				</form>

					
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Student ID</th>
                                    <th>Student Name</th>
                                    <th>Position</th>
                                    <th>Update</th>
                                </tr>
                            </thead>
                        <tbody>
							<?php
							
							$event_id = isset($_GET['event_id']) ? $_GET['event_id'] : null;
							$no = 1;
							$result = mysqli_query($conn, "SELECT ec.committee_id, s.student_id_card, s.student_name, ec.role 
								FROM eventcommittee ec 
								JOIN student s ON ec.user_id = s.user_id 
								WHERE ec.event_id = '$event_id'");



								if (mysqli_num_rows($result) > 0) {
									while ($row = mysqli_fetch_assoc($result)) {
										echo "<tr>";
										echo "<td>" . $no++ . "</td>";
										echo "<td>" . $row['student_id_card'] . "</td>";
										echo "<td>" . $row['student_name'] . "</td>";
										echo "<td>" . $row['role'] . "</td>";
										echo "<td>
											<a href='edit_committee.php?id=" . $row['committee_id'] . "'><button>Edit</button>
											<a href='delete_committee.php?id=" . $row['committee_id'] . "'><button>Delete</button>
											</td>";
										echo "</tr>";
									}
								} else {
									echo "<tr><td colspan='5'>No committee members found for this event.</td></tr>";
								}
							?>
						</tbody>

                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
