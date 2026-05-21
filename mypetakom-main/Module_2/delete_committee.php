<?php
include '../../Databased/db_connect.php';

if (isset($_GET['id'])) {
    $committee_id = intval($_GET['id']);

    // First, fetch the user_id related to this committee
    $stmt1 = $conn->prepare("SELECT user_id FROM eventcommittee WHERE committee_id = ?");
    $stmt1->bind_param("i", $committee_id);
    $stmt1->execute();
    $result = $stmt1->get_result();
    $row = $result->fetch_assoc();
    $user_id = $row['user_id'] ?? null;

    // Delete from eventcommittee
    $stmt2 = $conn->prepare("DELETE FROM eventcommittee WHERE committee_id = ?");
    $stmt2->bind_param("i", $committee_id);

    if ($stmt2->execute()) {
        echo "<script>
                alert('Committee member deleted successfully!');
                window.location.href = 'manage_committee.php';
              </script>";
        exit();
    } else {
        echo "Error deleting committee member: " . $conn->error;
    }

    $stmt1->close();
    $stmt2->close();
}

$conn->close();
?>
