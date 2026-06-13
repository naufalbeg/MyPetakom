<?php
// createatd.php
require_once '../Module_1/session_config.php';
requireLogin();
include '../Databased/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // sanitize & gather
    $attendanceName  = trim($_POST['attendance_name']);
    $eventName       = trim($_POST['event_name']);
    $locationName    = trim($_POST['location_name']);
    $latitude        = floatval($_POST['latitude']);
    $longitude       = floatval($_POST['longitude']);

    // ── NEW: Date + Time pickers ──
    $attendanceDate  = $_POST['attendance_date'];
    $startTime       = $_POST['start_time'];
    $endTime         = $_POST['end_time'];

    // Get event_id from event name
    $event_query = "SELECT event_id FROM events WHERE title = ?";
    $event_stmt = $conn->prepare($event_query);
    $event_stmt->bind_param("s", $eventName);
    $event_stmt->execute();
    $event_result = $event_stmt->get_result();
    
    if ($event_result->num_rows > 0) {
        $event_data = $event_result->fetch_assoc();
        $event_id = $event_data['event_id'];
        
        // Get user_id from attendance name (username)
        $user_query = "SELECT user_id FROM users WHERE username = ?";
        $user_stmt = $conn->prepare($user_query);
        $user_stmt->bind_param("s", $attendanceName);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        
        if ($user_result->num_rows > 0) {
            $user_data = $user_result->fetch_assoc();
            $user_id = $user_data['user_id'];
            
            // Create QR code data
            $qr_data = "event_id:" . $event_id . "|location:" . $locationName . "|date:" . $attendanceDate;
            
            // Insert into attendance table with correct structure
            $stmt = $conn->prepare(
              "INSERT INTO attendance 
                (event_id, user_id, attendance_qr, status_attd, timestamp, location_verified)
               VALUES (?, ?, ?, 'present', NOW(), 1)"
            );
            $stmt->bind_param(
              "iis",
              $event_id,
              $user_id,
              $qr_data
            );

    if ($stmt->execute()) {
        echo "<script>
                alert('Attendance “{$attendanceName}” created successfully.');
                window.location.href = 'manageatd.php';
              </script>";
    } else {
        echo "<script>alert('DB Error: {$stmt->error}');</script>";
    }

    $stmt->close();
        } else {
            echo "<script>alert('User not found: {$attendanceName}');</script>";
        }
        $event_stmt->close();
    } else {
        echo "<script>alert('Event not found: {$eventName}');</script>";
    }
    $conn->close();
    exit;
}

include '../HADER_SIDER_FOOTER/HST.PHP';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Create Attendance | MyPetakom</title>

  <!-- your existing styles -->
  <link rel="stylesheet" href="createatd.css">

  <!-- Leaflet CSS -->
  <link
    rel="stylesheet"
    href="https://unpkg.com/leaflet/dist/leaflet.css"
  />
  <style>
    /* map container sizing */
    #map { 
      width: 100%; 
      height: 300px; 
      border: 1px solid #aaa; 
      margin-bottom: 15px;
      border-radius: 4px;
    }
    .geo-group { display: flex; gap: 20px; }
    .geo-group > div { flex: 1; }
  </style>
</head>
<body>
    <!-- FORM + MAP -->
    <section class="content">
      <div class="form-container">
        <h2>Create Attendance Form</h2>
        <form method="post" action="createatd.php">          <!-- Basic fields -->
          <label for="attendance_name">Attendance Name:</label>
          <select id="attendance_name" name="attendance_name" required>
            <option value="">-- Select User --</option>
            <?php
            // Get all users with student role
            $user_query = "SELECT u.username, s.student_name FROM users u 
                          JOIN student s ON u.user_id = s.user_id 
                          WHERE u.user_role = 'student'";
            $user_result = $conn->query($user_query);
            while ($user = $user_result->fetch_assoc()) {
                echo "<option value='" . htmlspecialchars($user['username']) . "'>" . 
                     htmlspecialchars($user['username']) . " - " . 
                     htmlspecialchars($user['student_name']) . "</option>";
            }
            ?>
          </select>

          <label for="event_name">Event Name:</label>
          <select id="event_name" name="event_name" required>
            <option value="">-- Select Event --</option>
            <?php
            // Get all events
            $event_query = "SELECT title FROM events ORDER BY start_date DESC";
            $event_result = $conn->query($event_query);
            while ($event = $event_result->fetch_assoc()) {
                echo "<option value='" . htmlspecialchars($event['title']) . "'>" . 
                     htmlspecialchars($event['title']) . "</option>";
            }
            ?>
          </select>

          <!-- Location name (optional manual override) -->
          <label for="location_name">Location Name:</label>
          <input
            type="text"
            id="location_name"
            name="location_name"
            placeholder="e.g. Lecture Hall A"
            required
          >

          <!-- MAP -->
          <div id="map"></div>

          <!-- Auto‐filled by map clicks/drags -->
          <div class="geo-group">
            <div>
              <label for="latitude">Latitude:</label>
              <input
                type="number"
                id="latitude"
                name="latitude"
                step="any"
                placeholder="e.g. 3.0765"
                required
              >
            </div>
            <div>
              <label for="longitude">Longitude:</label>
              <input
                type="number"
                id="longitude"
                name="longitude"
                step="any"
                placeholder="e.g. 101.5183"
                required
              >
            </div>
          </div>

          <!-- ── NEW: Date + Time pickers ── -->
          <label for="attendance_date">Date:</label>
          <input type="date" id="attendance_date" name="attendance_date" required>

          <label for="start_time">Start Time:</label>
          <input type="time" id="start_time" name="start_time" required>

          <label for="end_time">End Time:</label>
          <input type="time" id="end_time" name="end_time" required>

          <!-- Buttons -->
          <div class="buttons">
            <button
              type="button"
              class="cancel-btn"
              onclick="location.href='manageatd.php'"
            >Cancel</button>
            <button type="submit" class="submit-btn">Submit</button>
          </div>
        </form>
      </div>
    </section>
  </div>

  <!-- Leaflet JS -->
  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
  <script>
    // Initialize map
    var map = L.map('map').setView([3.0765, 101.5183], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // Draggable marker
    var marker = L.marker([3.0765, 101.5183], { draggable: true }).addTo(map);

    // Utility to update inputs
    function updateLatLng(lat, lng) {
      document.getElementById('latitude').value = lat.toFixed(6);
      document.getElementById('longitude').value = lng.toFixed(6);
    }

    // On map click → move marker + update
    map.on('click', function(e) {
      marker.setLatLng(e.latlng);
      updateLatLng(e.latlng.lat, e.latlng.lng);
    });

    // On marker drag end → update
    marker.on('dragend', function(e) {
      var pos = marker.getLatLng();
      updateLatLng(pos.lat, pos.lng);
    });

    // Try browser geolocation to center map
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(function(pos) {
        var lat = pos.coords.latitude,
            lng = pos.coords.longitude;
        map.setView([lat, lng], 15);
        marker.setLatLng([lat, lng]);
        updateLatLng(lat, lng);
      });
    }
  </script>
</body>
</html>

