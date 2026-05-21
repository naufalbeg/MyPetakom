<?php
include('db_connect.php');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle approve/reject actions
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && isset($_POST['id'])) {
    $id = $_POST['id'];
    $status = $_POST['action'] === "approve" ? "approved" : "rejected";

    $update = "UPDATE meritapplication SET claim_status=? WHERE application_id=?";
    $stmt = $conn->prepare($update);
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
    $stmt->close();
}

// Fetch applications with event and user info
$sql = "
SELECT 
    ma.application_id,
    e.title AS event_name,
    u.username AS submitted_by,
    ma.submission_date,
    ma.claim_status
FROM 
    meritapplication ma
JOIN 
    events e ON ma.event_id = e.event_id
JOIN 
    users u ON ma.user_id = u.user_id
";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Petakom Coordinator - Manage Merits</title>
    <link rel="stylesheet" href="style.css" />
</head>
<body>
<div class="header">
    <div class="logo-section">
        <img src="Logo1.png" alt="UMP Logo" class="logo" />
        <img src="Logo2.png" alt="Petakom Logo" class="logo" />
    </div>
    <h1 class="white-text" style="color: white;">Petakom Coordinator (Administrator)</h1>
    <a href="logout.php" class="logout-button">Log Out</a>
</div>

<div class="main-container">
    <div class="sidebar">
        <div class="profile">
            <h3>Admin Profile</h3>
            <img src="profileIcon.png" alt="Admin Profile" class="profile-img" />
        </div>
        <hr>
        <ul class="menu">
            <li><a href="Admin-Dashboard.php">Dashboard</a></li>
            <hr>
            <li><a href="Admin-CreateUserAccount.php">Create User Account</a></li>
            <hr>
            <li><a href="Admin-ManageUserProfiles.php">Manage User Profiles</a></li>
            <hr>
            <li><a href="Admin-ManageMembership.php">Manage Memberships</a></li>
            <hr>
            <li class="active">Manage Merits</li>
            <hr>
        </ul>
    </div>

    <div class="dashboard">
        <div class="container">
            <h2>Approve Merit Applications</h2>
            <div class="membership-table">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Event Name</th>
                            <th>Submitted By</th>
                            <th>Submission Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        while ($row = $result->fetch_assoc()) { ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($row['event_name']) ?></td>
                                <td><?= htmlspecialchars($row['submitted_by']) ?></td>
                                <td><?= htmlspecialchars($row['submission_date']) ?></td>
                                <td><?= htmlspecialchars(ucfirst($row['claim_status'])) ?></td>
                                <td>
                                    <?php if ($row['claim_status'] === 'pending') { ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="id" value="<?= $row['application_id'] ?>">
                                            <button type="submit" name="action" value="approve" class="approve-btn">Approve</button>
                                        </form>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="id" value="<?= $row['application_id'] ?>">
                                            <button type="submit" name="action" value="reject" class="reject-btn">Reject</button>
                                        </form>
                                    <?php } else { ?>
                                        <span style="color: gray;">No actions</span>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                        <?php $conn->close(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
