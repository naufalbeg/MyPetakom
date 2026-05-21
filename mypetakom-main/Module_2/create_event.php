<?php
include '../../Databased/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $event_name = $_POST['event_name'];
    $description = $_POST['description'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $geolocation = $_POST['geolocation'];
    $location = $_POST['location'];
    $merit = $_POST['merit'];
    $merit_application = ($merit === 'Apply Merit') ? 'applied' : 'not applied';

    $approval_letter = '';
    if (isset($_FILES['approval_letter']) && $_FILES['approval_letter']['error'] == 0) {
        $upload_dir = "uploads/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_name = basename($_FILES["approval_letter"]["name"]);
        $target_file = $upload_dir . time() . "_" . $file_name;

        if (move_uploaded_file($_FILES["approval_letter"]["tmp_name"], $target_file)) {
            $approval_letter = $target_file;
        }
    }

   session_start(); 
	if (!isset($_SESSION['username'])) {
		echo "<script>alert('You must be logged in to access this page.'); window.location.href = 'Login.php';</script>";
		exit();
	}

	$username = $_SESSION['username'];

	// Fetch user_id from username
	$stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
	$stmt->bind_param("s", $username);
	$stmt->execute();
	$result = $stmt->get_result();

	if ($result->num_rows !== 1) {
		echo "<script>alert('User not found.'); window.location.href = 'Login.php';</script>";
		exit();
	}

	$row = $result->fetch_assoc();
	$created_by = $row['user_id'];
	
    $event_status = isset($_POST['save_draft']) ? 'Draft' : 'Pending Approval';
    $event_level = 'Faculty';
    $qrcode_event = '';

    $sql = "INSERT INTO events 
        (created_by, qrcode_event, title, description, start_date, end_date, event_status, geographic_location, location, approval_letter, event_level, merit_application)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("isssssssssss", 
        $created_by, 
        $qrcode_event, 
        $event_name, 
        $description, 
        $start_date, 
        $end_date, 
        $event_status, 
        $geolocation, 
        $location, 
        $approval_letter, 
        $event_level,
        $merit_application
    );

    if ($stmt->execute()) {
        $event_id = $conn->insert_id;

        if ($merit === "Apply Merit") {
            $user_id = $created_by;
            $claim_status = 'pending';

            $sql_merit = "INSERT INTO meritapplication (user_id, event_id, claim_status) VALUES (?, ?, ?)";
            $stmt_merit = $conn->prepare($sql_merit);

            if (!$stmt_merit) {
                die("SQL Error (meritapplication): " . $conn->error);
            }

            $stmt_merit->bind_param("iis", $user_id, $event_id, $claim_status);

            if ($stmt_merit->execute()) {
                echo "<script>alert('Event and Merit Application submitted successfully!'); window.location='create_event.php';</script>";
            } else {
                echo "Merit application error: " . $stmt_merit->error;
            }

            $stmt_merit->close();
        } else {
           $message = ($event_status === 'Draft') 
		  ? 'Event saved as draft.' 
		  : 'Event created successfully without merit.';
		echo "<script>alert('$message'); window.location='create_event.php';</script>";

        }
    } else {
        echo "Event creation error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

    
}

  include '../HADER_SIDER_FOOTER/HST.PHP';
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Create New Event</title>
<link rel="stylesheet" href="../CSS/MODULE_2_css/styleadvisor.css">
</head>
<body>
  <div class="container">


    <main class="main-content">
     

      <section class="dashboard-header">
        <h2>Create New Event Form</h2>
      </section>

      <form method="POST" enctype="multipart/form-data" style="background:#ccc; padding:20px; border-radius:10px;">
        <div style="display:flex; gap:20px;">
          <div style="flex:1;">
            <label>Event Name:</label><br>
            <input type="text" name="event_name" required><br><br>

            <label>Description:</label><br>
            <input type="text" name="description" required><br><br>

            <label>Start Date:</label><br>
            <input type="date" name="start_date" required><br><br>

            <label>End Date:</label><br>
            <input type="date" name="end_date" required><br><br>
          </div>

          <div style="flex:1;">
            <label>Location:</label><br>
            <input type="text" name="location" required><br><br>

            <label>Geolocation:</label><br>
            <input type="text" name="geolocation" placeholder="eg. 3.1234,101.6789"><br><br>

            <label>Approval Letter (PDF):</label><br>
            <input type="file" name="approval_letter" accept="application/pdf"><br><br>

            <label>Merit Application for Event:</label><br>
            <input type="radio" name="merit" value="Apply Merit"> Apply Merit
            <input type="radio" name="merit" value="No" checked> No<br><br>
          </div>
        </div>

        <div style="text-align:right;">
          <button type="submit" name="save_draft" class="logout" style="background-color:orange;">Save as Draft</button>
          <button type="submit" name="submit" class="logout" style="background-color:green;">Submit</button>
          <button type="reset" class="logout" style="background-color:gray;">Cancel</button>
        </div>
      </form>
    </main>
  </div>
</body>
</html>
