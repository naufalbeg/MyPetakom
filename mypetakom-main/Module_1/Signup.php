<?php
include('../Databased/db_connect.php');

// Initialize variables
$success = "";
$error = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $createPassword = $_POST['createPassword'];
    $confirmPassword = $_POST['confirmPassword'];
    $user_role = $_POST['user_role'];

    // Basic validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif ($createPassword !== $confirmPassword) {
        $error = "Passwords do not match.";
    } else {
        // Check if username or email already exists
        $check_sql = "SELECT * FROM users WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Username or email already exists.";
        } else {
            // Hash password
            $hashedPassword = password_hash($createPassword, PASSWORD_DEFAULT);

            // Insert new user
            $sql = "INSERT INTO users (username, name, email, password, user_role) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $username, $name, $email, $hashedPassword, $user_role);

            if ($stmt->execute()) {
                // Get the newly created user_id
                $user_id = $conn->insert_id;
                
                // Insert into role-specific table based on user_role
                if ($user_role == 'student') {
                    $role_sql = "INSERT INTO student (user_id, student_name) VALUES (?, ?)";
                    $role_stmt = $conn->prepare($role_sql);
                    $role_stmt->bind_param("is", $user_id, $name);
                    $role_stmt->execute();
                    $role_stmt->close();
                } 
                elseif ($user_role == 'advisor') {
                    $role_sql = "INSERT INTO eventadvisor (user_id, advisor_name) VALUES (?, ?)";
                    $role_stmt = $conn->prepare($role_sql);
                    $role_stmt->bind_param("is", $user_id, $name);
                    $role_stmt->execute();
                    $role_stmt->close();
                } 
                elseif ($user_role == 'admin') {
                    $role_sql = "INSERT INTO petakomadmin (user_id, admin_name) VALUES (?, ?)";
                    $role_stmt = $conn->prepare($role_sql);
                    $role_stmt->bind_param("is", $user_id, $name);
                    $role_stmt->execute();
                    $role_stmt->close();
                }
                
                $success = "Account created successfully! You can now <a href='login.php' style='color: #0056b3; font-weight: bold;'>log in</a>.";
            } else {
                $error = "Database error: " . $stmt->error;
            }
        }
        $stmt->close();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup Page</title>
    <link href="https://fonts.googleapis.com/css2?family=Arial,sans-serif&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-color: #0d233a;
            --card-bg: #d1d1d1;
            --primary-blue: #0056b3;
            --success-green: #4caf50;
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

        .alert {
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            font-size: 13px;
            text-align: center;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
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
        input[type="email"],
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

        .signup-btn {
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
            text-align: center;
        }

        .signup-btn:hover {
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
                <a href="signup.php" class="tab tab-active">Signup</a>
                <a href="login.php" class="tab tab-inactive">Login</a>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php elseif (!empty($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form action="signup.php" method="POST">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>

                <div class="form-group">
                    <label for="name">Full Name:</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="createPassword">Create Password:</label>
                    <input type="password" id="createPassword" name="createPassword" required>
                </div>

                <div class="form-group">
                    <label for="confirmPassword">Confirm Password:</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" required>
                </div>

                <div class="form-group">
                    <label for="user_role">User Role:</label>
                    <select id="user_role" name="user_role" required>
                        <option value="student">Student</option>
                        <option value="advisor">Advisor</option>
                        <option value="admin">Petakom Coordinator-Administrator</option>
                    </select>
                </div>

                <button type="submit" class="signup-btn">Signup</button>
            </form>
        </div>
    </div>
</body>
</html>