<?php
session_start();
require '../Databased/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['userID'];  // userID input is actually the username
    $password = $_POST['password'];
    $userRole = $_POST['userType']; // userType from the form maps to user_role in DB

    // Query based on username (not user_id)
    $sql = "SELECT * FROM users WHERE username = ? AND user_role = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("ss", $username, $userRole);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['username'] = $user['username'];
            $_SESSION['userRole'] = $user['user_role'];

            // Redirect based on user_role (case-sensitive filename fix)
            switch ($user['user_role']) {
                case 'admin':
                    header("Location: ../all_dashbord/Admin-Dashboard.php");
                    exit;
                case 'advisor':
                    header("Location: ../all_dashbord/dashboard_advisor.php");
                    exit;
                case 'student':
                    header("Location: ../all_dashbord/dashboard_student.php");
                    exit;
                default:
                    echo "Unknown user role.";
                    exit;
            }
        } else {
            echo "<script>alert('Invalid password.'); window.location='login.php';</script>";
        }
    } else {
        echo "<script>alert('Invalid username or role.'); window.location='login.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login Page</title>
    <link rel="stylesheet" type="text/css" href="../CSS/MODULE_1_css/style.css">
    <style>
        .logo-container {
    display: flex;
    justify-content: center;
    margin-bottom: 20px;
}

.logo {
    width: 100px;
    height: 100px;
    margin: 0 15px;
}
    </style>
</head>
<body>
<div class="container">
    <div class="logo-container">
        <img src="../templet ( use this to match our overview)/image/logo-emblem__329x482.png" alt="Logo 1" class="logo">
        <img src="../templet ( use this to match our overview)/image/images.png" alt="Logo 2" class="logo">
    </div>
    <div class="login-box">
        <div class="tabs">
            <a href="signup.php" class="tab">Signup</a>
            <a href="login.php" class="tab active">Login</a>
        </div>
        <form action="login.php" method="POST" autocomplete="off" novalidate>
            <label for="userID">Username:</label>
            <input type="text" id="userID" name="userID" autocomplete="off" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" autocomplete="new-password" required>

            <label for="userType">User Type:</label>
            <select id="userType" name="userType" autocomplete="off" required>
                <option value="admin">Admin</option>
                <option value="advisor">Advisor</option>
                <option value="student">Student</option>
            </select>

            <button type="submit" class="login-btn">Login</button>
            <a href="forgot-password.php" class="forgot-password">Forgot password?</a>
        </form>
    </div>
</div>
</body>
</html>
