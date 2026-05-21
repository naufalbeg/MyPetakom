<?php
include '../../Databased/db_connect.php';

if (!isset($_GET['event_id'])) {
  echo "Invalid event ID.";
  exit;
}

$event_id = intval($_GET['event_id']);
$sql = "SELECT * FROM events WHERE event_id = $event_id";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
  echo "Event not found.";
  exit;
}

$event = mysqli_fetch_assoc($result);

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Event Details</title>
  <style>
    body { font-family: Arial, sans-serif; padding: 20px; }
    .event-details {
      max-width: 600px;
      margin: auto;
      border: 1px solid #ccc;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    h2 { text-align: center; }
  </style>
</head>
<body>
  <div class="event-details">
    <h2><?php echo htmlspecialchars($event['title']); ?></h2>
    <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
    <p><strong>Date:</strong> <?php echo date('d M Y', strtotime($event['start_date'])); ?></p>
    <p><strong>Status:</strong> <?php echo htmlspecialchars($event['event_status']); ?></p>
    <p><strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?></p>
    <p><strong>Level:</strong> <?php echo htmlspecialchars($event['event_level']); ?></p>
  </div>
</body>
</html>
