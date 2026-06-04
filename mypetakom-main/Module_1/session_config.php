<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//Session timeout: 20 minutes (1200 seconds)
define('SESSION_TIMEOUT', 1200);

//define('SESSION_TIMEOUT', 60);  // 60 seconds for testing


// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) || isset($_SESSION['username']);
}

// Function to check session timeout
function checkSessionTimeout() {
    if (isset($_SESSION['last_activity'])) {
        $inactive_time = time() - $_SESSION['last_activity'];
        if ($inactive_time > SESSION_TIMEOUT) {
            session_unset();
            session_destroy();
            header("Location: ../Module_1/login.php?error=session_expired");
            exit();
        }
    }
    $_SESSION['last_activity'] = time();
}

// Function to protect pages
function requireLogin() {
    if (!isset($_SESSION['username'])) {
        header("Location: ../Module_1/login.php");
        exit();
    }
    checkSessionTimeout();
}

// Function to get remaining time (for JavaScript)
function getRemainingTime() {
    if (isset($_SESSION['last_activity'])) {
        $elapsed = time() - $_SESSION['last_activity'];
        $remaining = SESSION_TIMEOUT - $elapsed;
        return $remaining > 0 ? $remaining : 0;
    }
    return SESSION_TIMEOUT;
}
?>