<?php
require '../Databased/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Check if the email exists in the database
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // Simulate sending reset link (in production, you'd send an actual email with token)
        echo "<script>alert('A password reset link has been sent to your email address.'); window.location='login.php';</script>";
    } else {
        echo "<script>alert('Email not found. Please try again.'); window.location='forgot-password.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <div class="container">
        <!-- Logo Container -->
        <div class="logo-container">
            <img src="logo1.png" alt="Logo 1" class="logo">
            <img src="logo2.png" alt="Logo 2" class="logo">
        </div>

        <!-- Forgot Password Box -->
        <div class="login-box">
            <h2>Forgot Password</h2>
            <form action="forgot-password.php" method="POST">
                <label for="email">Enter your email address:</label>
                <input type="email" id="email" name="email" required>
                <button type="submit" class="forgot-password-btn">Send Reset Link</button>
            </form>
        </div>
    </div>
</body>
</html>
