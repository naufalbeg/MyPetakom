<?php
session_start();
require '../Databased/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['userID'];
    $password = $_POST['password'];
    $userRole = $_POST['userType'];

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
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['userRole'] = $user['user_role'];
            $_SESSION['last_activity'] = time();

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    
    <style>
        :root {
            --bg-color: #0d233a;
            --card-bg: #d1d1d1;
            --primary-blue: #0056b3;
            --success-green: #4caf50;
            --danger-red: #cc2424;
            --text-dark: #333333;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: var(--bg-color);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 440px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
        }

        .logo-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
            background: transparent;
            padding: 5px;
        }

        .logo {
            max-height: 75px;
            object-fit: contain;
        }

        .logo:last-child {
            background: #ffffff;
            padding: 3px;
            border-radius: 4px;
        }

        .login-box {
            background-color: var(--card-bg);
            width: 100%;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            text-align: center;
        }

        .tabs {
            display: flex;
            margin-bottom: 20px;
            border-radius: 4px;
            overflow: hidden;
        }

        .tab {
            flex: 1;
            text-align: center;
            padding: 10px 0;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
        }

        /* BLUE = Active (current page) */
        .tab-active {
            background-color: var(--primary-blue) !important;
            color: #ffffff !important;
        }

        /* GRAY = Inactive (other page) */
        .tab-inactive {
            background-color: #e9ecef !important;
            color: #6c757d !important;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        label {
            font-size: 13px;
            color: #444444;
            text-align: left;
        }

        input[type="text"],
        input[type="password"],
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ffffff;
            border-radius: 4px;
            font-size: 14px;
            background-color: #ffffff;
            color: #000000;
            outline: none;
        }

        select {
            cursor: pointer;
        }

        .login-btn {
            background-color: var(--success-green);
            color: #ffffff;
            border: none;
            padding: 12px;
            font-size: 14px;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 8px;
            width: 100%;
        }

        .login-btn:hover {
            opacity: 0.9;
        }

        .forgot-password {
            display: inline-block;
            background-color: var(--danger-red);
            color: #ffffff;
            text-decoration: none;
            padding: 8px 16px;
            font-size: 12px;
            border-radius: 4px;
            margin-top: 14px;
            align-self: center;
            transition: opacity 0.2s ease;
        }

        .forgot-password:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo-container">
            <img src="../templet ( use this to match our overview)/image/logo-emblem__329x482.png" alt="UMPSA Logo" class="logo">
            <img src="../templet ( use this to match our overview)/image/images.png" alt="PETAKOM Logo" class="logo">
        </div>
        
        <div class="login-box">
            <div class="tabs">
                <a href="signup.php" class="tab tab-inactive">Signup</a>
                <a href="login.php" class="tab tab-active">Login</a>
            </div>

            <form action="login.php" method="POST" autocomplete="off" novalidate>
                <div class="form-group">
                    <label for="userID">Username:</label>
                    <input type="text" id="userID" name="userID" autocomplete="off" required>
                </div>

                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" autocomplete="new-password" required>
                </div>

                <div class="form-group">
                    <label for="userType">User Type:</label>
                    <select id="userType" name="userType" autocomplete="off" required>
                        <option value="admin">Admin</option>
                        <option value="advisor">Advisor</option>
                        <option value="student">Student</option>
                    </select>
                </div>

                <button type="submit" class="login-btn">Login</button>
                <a href="forgot-password.php" class="forgot-password">Forgot password?</a>
            </form>
        </div>
    </div>
</body>
</html>