<?php
session_start();
include '../../Databased/db_connect.php';

// Step 1: Ensure user is logged in by checking username
if (!isset($_SESSION['username'])) {
    echo "<script>alert('You must be logged in to access this page.'); window.location.href = 'Login.php';</script>";
    exit();
}

$username = $_SESSION['username'];

// Fetch user_id based on username
$stmt_user = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
$stmt_user->bind_param("s", $username);
$stmt_user->execute();
$result_user = $stmt_user->get_result();

if ($result_user->num_rows !== 1) {
    echo "<script>alert('User not found.'); window.location.href = 'Login.php';</script>";
    exit();
}

$user_data = $result_user->fetch_assoc();
$user_id = $user_data['user_id'];


// Step 2: Fetch advisor data (from users + eventadvisor)
$sql = "SELECT 
            users.user_id,
            users.name,
            users.username,
            users.email,
            eventadvisor.admin_phone_number,
            eventadvisor.position_advisor
        FROM users
        LEFT JOIN eventadvisor ON users.user_id = eventadvisor.user_id
        WHERE users.user_id = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

  include '../HADER_SIDER_FOOTER/HST.PHP';

?>






<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage User Profile</title>
<link rel="stylesheet" href="../CSS/MODULE_2_css/styleadvisor.css">
</head>
<body>
<div class="container">
   
    <!-- Main Content -->
    <div class="main-content">
       

        <h2>Manage User Profile</h2>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Full Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Phone Number</th>
                        <th>Position</th>
                        <th>Update</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result && $result->num_rows > 0) {
                        $no = 1;
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $no++ . "</td>";
                            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['admin_phone_number']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['position_advisor']) . "</td>";
                            echo "<td>
                                <a href='edit_profile.php?id=" . $row['user_id'] . "'><button>Edit</button></a>
                            </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='8'>No advisor profiles found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
