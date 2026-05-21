<?php
include '../../Databased/db_connect.php';


if (isset($_GET['id'])) {
    $event_id = $_GET['id'];

    // Fetch event data
    $sql = "SELECT * FROM events WHERE event_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $event = $result->fetch_assoc();
} else {
    echo "<script>alert('No event ID provided.'); window.location.href = 'manage_event.php';</script>";
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
	$description = $_POST['description'];
    $status = $_POST['event_status'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $location = $_POST['location'];
    $geo = $_POST['geographic_location'];

    $update = "UPDATE events SET title=?, description=?, event_status=?, start_date=?, end_date=?, location=?, geographic_location=? WHERE event_id=?";
    $stmt = $conn->prepare($update);
	$stmt->bind_param("sssssssi", $title, $description, $status, $start_date, $end_date, $location, $geo, $event_id);

    if ($stmt->execute()) {
        
		echo "<script>alert('Event updated successfully!'); window.location.href = 'manage_event.php';</script>";
        exit();
    } else {
        echo "Update failed: " . $stmt->error;
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Event</title>
    <style>
    
		.header {
			color :white;
			text-align: center;

		}
		.body-edit {
			color: black;
			text-align: left;
		}
		
		body {
            background-color: grey;
            font-family: Arial;
        }
        form {
            max-width: 600px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        input, select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
		.update {
			 background-color: grey;
			 color: white;
			 border: none;
			 padding: 10px 15px;
			 border-radius: 5px;
		
		}
    </style>

<body>
    <section class="header">
	<h2>Edit Event Form</h2>
    <section class="body-edit">
	<form method="POST">
        <label>Event Name:</label><br>
        <input type="text" name="title" value="<?php echo htmlspecialchars($event['title']); ?>" required><br><br>
		
		<label>Description:</label><br>
		<input type="text" name="description" value="<?php echo htmlspecialchars($event['description']); ?>" required><br><br>

        <label>Status:</label><br>
        <select name="event_status" required>
            <option value="Active" <?php if ($event['event_status'] == 'Active') echo 'selected'; ?>>Active</option>
            <option value="Postponed" <?php if ($event['event_status'] == 'Postponed') echo 'selected'; ?>>Postponed</option>
            <option value="Cancelled" <?php if ($event['event_status'] == 'Cancelled') echo 'selected'; ?>>Cancelled</option>
			<option value="Completed" <?php if ($event['event_status'] == 'Completed') echo 'selected'; ?>>Completed</option>
        </select><br><br>

        <label>Start Date:</label><br>
        <input type="date" name="start_date" value="<?php echo $event['start_date']; ?>" required><br><br>

        <label>End Date:</label><br>
        <input type="date" name="end_date" value="<?php echo $event['end_date']; ?>" required><br><br>

        <label>Location:</label><br>
        <input type="text" name="location" value="<?php echo htmlspecialchars($event['location']); ?>" required><br><br>

        <label>Geolocation:</label><br>
        <input type="text" name="geographic_location" value="<?php echo htmlspecialchars($event['geographic_location']); ?>" required><br><br>

        
		<button type="submit" class="update" style="background-color:green;">Update Event</button>
		
        <a href="manage_event.php" class="update" >Cancel</a>
    </form>
</body>
</html>
