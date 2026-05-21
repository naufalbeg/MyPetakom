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
                $success = "Account created successfully! You can now <a href='login.php'>log in</a>.";
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
    <title>Signup Page</title>
    <link rel="stylesheet" type="text/css" href="../CSS/MODULE_1_css/style.css">
</head>
<body>
    <div class="container">
        <div class="logo-container">
            <img src="../templet ( use this to match our overview)/image/logo-emblem__329x482.png" alt="Logo 1" class="logo">
            <img src="../templet ( use this to match our overview)/image/images.png" alt="Logo 2" class="logo">
        </div>
        <div class="login-box">
            <div class="tabs">
                <a href="login.php" class="tab">Login</a>
                <a href="signup.php" class="tab active">Signup</a>
            </div>

            <?php
            if (!empty($error)) {
                echo "<p style='color:red;'>$error</p>";
            } elseif (!empty($success)) {
                echo "<p style='color:green;'>$success</p>";
            }
            ?>

            <form action="signup.php" method="POST">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>

                <label for="name">Full Name:</label>
                <input type="text" id="name" name="name" required>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>

                <label for="createPassword">Create Password:</label>
                <input type="password" id="createPassword" name="createPassword" required>

                <label for="confirmPassword">Confirm Password:</label>
                <input type="password" id="confirmPassword" name="confirmPassword" required>

                <label for="user_role">User Role:</label>
                <select id="user_role" name="user_role" required>
                    <option value="student">Student</option>
                    <option value="advisor">Advisor</option>
                    <option value="admin">Petakom Coordinator-Administrator</option>
                </select>

                <button type="submit" class="signup-btn">Signup</button>
            </form>
        </div>
    </div>
</body>
</html>
