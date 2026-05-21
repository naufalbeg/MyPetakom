<?php
session_start();
include '../../Databased/db_connect.php';

if (!isset($_GET['id'])) {
    echo "<script>alert('No advisor selected.'); window.location.href = 'manage_profile_advisor.php';</script>";
    exit();
}

$user_id = intval($_GET['id']);

// Fetch existing user + advisor data
$sql = "SELECT users.name, users.username, users.email,
               eventadvisor.admin_phone_number, eventadvisor.position_advisor
        FROM users
        LEFT JOIN eventadvisor ON users.user_id = eventadvisor.user_id
        WHERE users.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    echo "<script>alert('User not found.'); window.location.href = 'manage_profile_advisor.php';</script>";
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $phone = $_POST['admin_phone_number'];
    $position = $_POST['position_advisor'];

    // Update users table
    $stmt = $conn->prepare("UPDATE users SET name=?, username=?, email=? WHERE user_id=?");
    $stmt->bind_param("sssi", $name, $username, $email, $user_id);
    $stmt->execute();

    // Update eventadvisor table
$stmt2 = $conn->prepare("UPDATE eventadvisor SET admin_phone_number=?, position_advisor=? WHERE user_id=?");
$stmt2->bind_param("ssi", $phone, $position, $user_id);

    $stmt2->execute();

    echo "<script>alert('Profile updated successfully!'); window.location.href = 'manage_profile_advisor.php';</script>";
    exit();
}


?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit User Profile</title>
    <style>
        body {
            background-color: grey;
            font-family: Arial, sans-serif;
        }
        form {
            max-width: 600px;
            margin: 30px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            color: black;
        }
        .header {
            color: white;
            text-align: center;
            margin-top: 20px;
        }
        label {
            font-weight: bold;
            display: block;
            margin-top: 15px;
        }
        input {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        .update, .cancel {
            padding: 10px 15px;
            border-radius: 5px;
            border: none;
            margin-top: 20px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .update {
            background-color: green;
            color: white;
        }
        .cancel {
            background-color: grey;
            color: white;
            margin-left: 10px;
        }
    </style>
</head>
<body>

<section class="header">
    <h2>Edit User Profile</h2>
</section>

<form method="POST">
    <label>Full Name:</label>
    <input type="text" name="name" value="<?php echo htmlspecialchars($row['name']); ?>" required>

    <label>Username:</label>
    <input type="text" name="username" value="<?php echo htmlspecialchars($row['username']); ?>" required>

    <label>Email:</label>
    <input type="email" name="email" value="<?php echo htmlspecialchars($row['email']); ?>" required>

    <label>Phone Number:</label>
    <input type="text" name="admin_phone_number" value="<?php echo htmlspecialchars($row['admin_phone_number']); ?>" required>

    <label for="position_advisor">Advisor Position:</label><br>
		<select id="position_advisor" name="position_advisor" required>
			<option value="">-- Select Position --</option>
			<option value="Event Advisor">Event Advisor</option>
			<option value="Senior Event Advisor">Senior Event Advisor</option>
			<option value="Assistant Event Advisor">Assistant Event Advisor</option>
			<option value="Faculty Advisor">Faculty Advisor</option>
			<option value="Event Coordinator">Event Coordinator</option>
			<option value="Program Coordinator">Program Coordinator</option>
			<option value="Club/Society Coordinator">Club/Society Coordinator</option>
			<option value="Student Affairs Officer">Student Affairs Officer</option>
			<option value="University Liaison Officer">University Liaison Officer</option>
			<option value="Administrative Officer">Administrative Officer</option>
			<option value="Academic Mentor">Academic Mentor</option>
		</select><br><br>


    <button type="submit" class="update">Update Profile</button>
    <a href="manage_profile_advisor.php" class="cancel">Cancel</a>
</form>

</body>
</html>
