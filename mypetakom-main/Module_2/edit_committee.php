<?php
include '../../Databased/db_connect.php';

if (isset($_GET['id'])) {
    $committee_id = intval($_GET['id']);

    // Fetch committee + student data
    $sql = "SELECT ec.committee_id, ec.role, s.student_name, s.student_id_card, s.user_id
            FROM eventcommittee ec
            JOIN student s ON ec.user_id = s.user_id
            WHERE ec.committee_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $committee_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $committee = $result->fetch_assoc();

    if (!$committee) {
        echo "<script>alert('Committee member not found.'); window.location.href = 'manage_committee.php';</script>";
        exit();
    }
} else {
    echo "<script>alert('No committee ID provided.'); window.location.href = 'manage_committee.php';</script>";
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_name = trim($_POST['student_name']);
    $student_id_card = trim($_POST['student_id_card']);
    $role = trim($_POST['role']);
    $user_id = $committee['user_id'];

    // Update student table
    $updateStudent = "UPDATE student SET student_name = ?, student_id_card = ? WHERE user_id = ?";
    $stmt1 = $conn->prepare($updateStudent);
    $stmt1->bind_param("ssi", $student_name, $student_id_card, $user_id);

    // Update eventcommittee table
    $updateCommittee = "UPDATE eventcommittee SET role = ? WHERE committee_id = ?";
    $stmt2 = $conn->prepare($updateCommittee);
    $stmt2->bind_param("si", $role, $committee_id);

    if ($stmt1->execute() && $stmt2->execute()) {
        echo "<script>alert('Committee member updated successfully!'); window.location.href = 'manage_committee.php';</script>";
        exit();
    } else {
        echo "Update failed: " . $stmt1->error . " / " . $stmt2->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Committee Member</title>
    <style>
        body {
            background-color: grey;
            font-family: Arial;
        }
        .header {
            color: white;
            text-align: center;
        }
        .body-edit {
            color: black;
            text-align: left;
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
            text-decoration: none;
        }
    </style>
</head>
<body>

<section class="header">
    <h2>Edit Committee Member</h2>
</section>

<section class="body-edit">
    <form method="POST">
        <label>Student Name:</label><br>
        <input type="text" name="student_name" value="<?php echo htmlspecialchars($committee['student_name']); ?>" required><br><br>

        <label>Student ID Card:</label><br>
        <input type="text" name="student_id_card" value="<?php echo htmlspecialchars($committee['student_id_card']); ?>" required><br><br>

        <label>Role / Position:</label><br>
        <input type="text" name="role" value="<?php echo htmlspecialchars($committee['role']); ?>" required><br><br>

        <button type="submit" class="update" style="background-color:green;">Update</button>
        <a href="manage_committee.php" class="update">Cancel</a>
    </form>
</section>

</body>
</html>
