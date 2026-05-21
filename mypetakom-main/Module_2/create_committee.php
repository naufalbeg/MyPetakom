<?php

include '../../Databased/db_connect.php';

if (isset($_POST['submit'])) {
    $username = $_POST['username'];  // Changed back to username
    $role = $_POST['role'];
    $event_title = $_POST['title'];

    // Get event_id from events table using title
    $event_lookup = mysqli_query($conn, "SELECT event_id FROM events WHERE title = '$event_title'");
    
    if ($event_lookup && mysqli_num_rows($event_lookup) > 0) {
        $event_data = mysqli_fetch_assoc($event_lookup);
        $event_id = $event_data['event_id'];        // Get user_id from users table using username
        $student_query = mysqli_query($conn, "SELECT user_id FROM users WHERE username = '$username'");
        
        if ($student_query && mysqli_num_rows($student_query) > 0) {
            $student = mysqli_fetch_assoc($student_query);
            $user_id = $student['user_id'];

            // Check if already assigned
            $check = mysqli_query($conn, "SELECT * FROM eventcommittee WHERE user_id = '$user_id' AND event_id = '$event_id'");
            if (mysqli_num_rows($check) > 0) {
                echo "<script>alert('This student is already assigned to this event.');</script>";
            } else {
                $insert = "INSERT INTO eventcommittee (event_id, user_id, role) VALUES ('$event_id', '$user_id', '$role')";
                if (mysqli_query($conn, $insert)) {
                    echo "<script>alert('Committee member added successfully.');</script>";
                } else {
                    echo "<script>alert('Insert failed: " . mysqli_error($conn) . "');</script>";
                }
            }        } else {
            // Better error message with debugging info
            echo "<script>alert('Student not found with username: $username. Please check the username.');</script>";
        }
    } else {
        echo "<script>alert('Event not found.');</script>";
    }
}
  include '../HADER_SIDER_FOOTER/HST.PHP';
?>





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Committee</title>
    <link rel="stylesheet" href="../CSS/MODULE_2_css/styleadvisor.css">
</head>
<body>
    <div class="container">

        <!-- Main Content -->
		<main class="main-content">


            <section class="dashboard-header">
                <h2>Commitee Registration Form</h2>
            </section>

            <form method="POST" enctype="multipart/form-data" style="background:#ccc; padding:20px; border-radius:10px;">
				<div style="display:flex; gap:20px;">
					<div style="flex:1;">
								<label for="student-id">Username:</label><br>
						<select id="student-id" name="username" required>
							<option value="">-- Select Student --</option>
							<?php
							$student_query = "SELECT u.username, s.student_name FROM users u JOIN student s ON u.user_id = s.user_id WHERE u.user_role = 'student'";
							$student_result = mysqli_query($conn, $student_query);
							while ($student = mysqli_fetch_assoc($student_result)) {
								echo "<option value='" . htmlspecialchars($student['username']) . "'>" . 
								     htmlspecialchars($student['username']) . " - " . 
								     htmlspecialchars($student['student_name']) . "</option>";
							}
							?>
						</select><br><br>

						<label for="student-name">Student Name:</label><br>
						<input type="text" id="student-name" name="student_name" required><br><br>

						
					</div>
				
					<div style="flex:1;">
				 
						<label for="assigned-position">Assigned Position:</label><br>
							<select id="role" name="role" required>
								<option value="">-- Select Role --</option>
								<option value="Main committee">Main committee</option>
								<option value="Committee">Committee</option>
								
							</select><br><br>
						
						<label for="event">Select Event:</label><br>
							<select id="event" name="title" required><br><br>
								<option value="">-- Select an Event --</option>
								<?php
								$event_query = "SELECT title FROM events";
								$event_result = mysqli_query($conn, $event_query);
								while ($event = mysqli_fetch_assoc($event_result)) {
									echo "<option value='" . htmlspecialchars($event['title']) . "'>" . htmlspecialchars($event['title']) . "</option>";
								}
								?>
							</select>
					</div>
				</div>
				
						<div style="text-align:right;">
		
						<button type="submit" name="submit" class="logout" style="background-color:green;">Submit</button>
					</form>

                </div>
            </div>
        </div>
    </div>
</body>
</html>
