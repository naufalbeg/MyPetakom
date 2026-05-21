<?php
include('../Databased/db_connect.php');

if (isset($_GET['id'])) {
    $userId = (int)$_GET['id'];

    // Start a transaction so we can rollback on failure
    $conn->begin_transaction();
    try {
        // 1) Delete from attendance
        $stmt = $conn->prepare("DELETE FROM attendance WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();

        // 2) Delete from meritapplication
        $stmt = $conn->prepare("DELETE FROM meritapplication WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();

        // 3) Delete from merit
        $stmt = $conn->prepare("DELETE FROM merit WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();

        // 4) Delete from eventcommittee
        $stmt = $conn->prepare("DELETE FROM eventcommittee WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();

        // 5) Delete from membership
        $stmt = $conn->prepare("DELETE FROM membership WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();

        // 6) Delete from student
        $stmt = $conn->prepare("DELETE FROM student WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();

        // 7) Delete from eventadvisor
        $stmt = $conn->prepare("DELETE FROM eventadvisor WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();

        // 8) Delete from petakomadmin
        $stmt = $conn->prepare("DELETE FROM petakomadmin WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();

        // Finally, delete from users
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();

        // Commit all or rollback on any error
        $conn->commit();

        header("Location: ../all_dashbord/Admin-Dashboard.php?msg=deleted");
        exit;

    } catch (Exception $e) {
        // Something went wrong: roll back
        $conn->rollback();
        echo "Failed to delete user and related data: " . htmlspecialchars($e->getMessage());
    }
}

$conn->close();
?>
