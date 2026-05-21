<?php
include '../../Databased/db_connect.php';

if (isset($_GET['id'])) {
    $event_id = $_GET['id'];

    // Delete related merit applications first
    $sql1 = "DELETE FROM meritapplication WHERE event_id = ?";
    $stmt1 = $conn->prepare($sql1);
    $stmt1->bind_param("i", $event_id);
    $stmt1->execute();

    // Now delete the event
    $sql2 = "DELETE FROM events WHERE event_id = ?";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("i", $event_id);

    if ($stmt2->execute()) {
        echo "<script>
                alert('Event deleted successfully!');
                window.location.href = 'manage_event.php';
              </script>";
        exit();
    } else {
        echo "Error deleting event: " . $conn->error;
    }

    $stmt1->close();
    $stmt2->close();
}

$conn->close();
?>
