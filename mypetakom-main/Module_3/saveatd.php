<?php
require_once '../Module_1/session_config.php';
requireLogin();
include '../Databased/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_id          = intval($_POST['event_id']);
    $student_id        = trim($_POST['student_id']);
    $matric_id         = trim($_POST['matric_id']);
    $committee         = trim($_POST['committee']);
    $study_year        = intval($_POST['study_year']);
    $time              = trim($_POST['time']);
    $date              = trim($_POST['date']);
    $status            = trim($_POST['status']);
    $location_verified = 1;

    // Get user_id from student_id
    $user_query = "SELECT user_id FROM student WHERE student_id_card = ?";
    $user_stmt  = $conn->prepare($user_query);
    $user_stmt->bind_param("s", $student_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();

    if ($user_result->num_rows > 0) {
        $user_data = $user_result->fetch_assoc();
        $user_id   = $user_data['user_id'];

        $stmt = $conn->prepare("
            INSERT INTO attendance
            (event_id, user_id, status_attd, timestamp, location_verified)
            VALUES (?, ?, ?, NOW(), 1)
        ");
        $stmt->bind_param("iis",
            $event_id,
            $user_id,
            $status
        );

        if ($stmt->execute()) {
            $_SESSION['msg'] = "Attendance successfully submitted.";
        } else {
            $_SESSION['msg'] = "Database Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $_SESSION['msg'] = "Student ID not found. Please enter your matric number (e.g. CB23063).";
    }

    $conn->close();
    header("Location: fillatd.php?event_id=" . $_POST['event_id']);
    exit;
}
?>
