<?php

require_once '../Module_1/session_config.php';
requireLogin();

include('../Databased/db_connect.php');

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $fullname = $_POST['fullname'];
  $email = $_POST['email'];
  $username = $_POST['username'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash password
  $role = $_POST['role'];

  // Insert into database including full name
  $sql = "INSERT INTO users (name, username, email, password, user_role) VALUES (?, ?, ?, ?, ?)";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("sssss", $fullname, $username, $email, $password, $role);

  if ($stmt->execute()) {
    echo '<script type="text/javascript">
      alert("User account created successfully.");
      window.location.href = "Admin-CreateUserAccount.php";
    </script>';
  } else {
    echo '<script type="text/javascript">
      alert("Error: ' . addslashes($stmt->error) . '");
    </script>';
  }
  $stmt->close();
}

// Pass page title context variable to HST layout header
$page_title = "Create User Account";

include '../HADER_SIDER_FOOTER/HST.PHP';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
  /* Base Dashboard Layout overrides to match HST frame */
  .main-container {
    margin-left: 280px; /* Aligned perfectly with HST sidebar width */
    padding: 50px;
    background-color: #f8f9fa;
    min-height: calc(100vh - 90px);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    box-sizing: border-box;
  }

  /* HORIZONTALLY ENLARGED: Massive widescreen card configuration */
  .admin-form-card {
    background: #ffffff;
    border-radius: 16px;
    padding: 50px;
    max-width: 1350px; /* Expanded for maximum horizontal space */
    width: 100%;
    margin: 20px auto;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04), 0 1px 8px rgba(0, 0, 0, 0.02);
    border: 1px solid #e9ecef;
    box-sizing: border-box;
  }

  .admin-form-card h1 {
    font-size: 2.2rem;
    font-weight: 700;
    color: #1a1d20;
    margin: 0 0 12px 0;
  }

  .form-subtitle {
    color: #6c757d;
    font-size: 1.05rem;
    margin-bottom: 40px;
    border-bottom: 1px solid #e9ecef;
    padding-bottom: 25px;
  }

  /* Two-column responsive grid layout stretched out horizontally */
  .form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 35px 45px; /* Generous spacing between grid elements */
  }

  .form-group.full-width {
    grid-column: span 2;
  }

  .form-group label {
    display: block;
    font-size: 0.95rem;
    font-weight: 700;
    color: #343a40;
    margin-bottom: 12px;
    text-transform: uppercase;
    letter-spacing: 0.75px;
  }

  /* Large input and select design fields */
  .form-group input,
  .form-group select {
    width: 100%;
    padding: 16px 18px; /* Taller fields for structural impact */
    font-size: 1.05rem; /* Readability text boost */
    border: 1px solid #ced4da;
    border-radius: 8px;
    background-color: #fff;
    color: #212529;
    box-sizing: border-box;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
  }

  .form-group input:focus,
  .form-group select:focus {
    border-color: #228be6;
    outline: 0;
    box-shadow: 0 0 0 4px rgba(34, 139, 230, 0.18);
  }

  /* Password Container Style for Layout Containment */
  .password-wrapper {
    position: relative;
    width: 100%;
  }

  .password-wrapper input {
    padding-right: 50px; /* Give space so text doesn't hide behind icon */
  }

  .password-toggle-icon {
    position: absolute;
    right: 18px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: #6c757d;
    font-size: 1.2rem;
    transition: color 0.2s ease;
  }

  .password-toggle-icon:hover {
    color: #228be6;
  }

  /* Form Action Control Buttons UI Layout */
  .form-buttons {
    display: flex;
    justify-content: flex-end;
    gap: 15px;
    margin-top: 45px;
    border-top: 1px solid #e9ecef;
    padding-top: 30px;
  }

  .btn {
    padding: 16px 35px; /* Larger, click-friendly interactive targets */
    font-size: 1.05rem;
    font-weight: 600;
    border-radius: 8px;
    cursor: pointer;
    border: none;
    transition: background 0.2s ease, transform 0.1s ease;
  }

  .btn:active {
    transform: scale(0.99);
  }

  .btn-primary {
    background-color: #228be6;
    color: white;
    box-shadow: 0 4px 12px rgba(34, 139, 230, 0.2);
  }

  .btn-primary:hover {
    background-color: #1c7ed6;
    box-shadow: 0 6px 16px rgba(34, 139, 230, 0.3);
  }

  .btn-secondary {
    background-color: #f1f3f5;
    color: #495057;
  }

  .btn-secondary:hover {
    background-color: #e9ecef;
  }

  /* Session Warning Notification Design Update */
  .session-popup {
    position: fixed;
    bottom: 30px;
    right: 30px;
    background: #ffffff;
    color: #212529;
    padding: 20px 25px;
    border-radius: 12px;
    box-shadow: 0 15px 35px rgba(0,0,0,0.15);
    z-index: 10000;
    font-family: inherit;
    width: 320px;
    border-left: 5px solid #fd7e14;
    animation: slideInUp 0.4s cubic-bezier(0.16, 1, 0.3, 1);
  }

  .session-popup strong {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #fd7e14;
    font-size: 1.05rem;
    margin-bottom: 8px;
  }

  .session-popup .popup-actions {
    margin-top: 15px;
    display: flex;
    gap: 10px;
    justify-content: flex-end;
  }

  .session-popup button {
    padding: 8px 14px;
    border: none;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s ease;
  }

  .session-popup .stay-btn { background: #4caf50; color: white; }
  .session-popup .stay-btn:hover { background: #439a46; }
  .session-popup .logout-btn { background: #f44336; color: white; }
  .session-popup .logout-btn:hover { background: #d32f2f; }

  /* Adjust grid layout back to single column layout on smaller windows */
  @media (max-width: 1200px) {
    .form-grid {
      grid-template-columns: 1fr;
      gap: 20px;
    }
    .form-group.full-width {
      grid-column: span 1;
    }
    .main-container {
      padding: 20px;
    }
    .admin-form-card {
      padding: 30px;
    }
  }
</style>

<div class="main-container">
  
  <div class="admin-form-card">
    <h1>Create New User Account</h1>
    <p class="form-subtitle">Register credentials and system access permission clearance levels into the MyPetakom platform database core.</p>
    
    <form class="user-form" method="post" autocomplete="off">
      <div class="form-grid">
        
        <div class="form-group full-width">
          <label for="fullname">Full Name</label>
          <input type="text" id="fullname" name="fullname" placeholder="e.g. Professor John Doe" required>
        </div>
        
        <div class="form-group">
          <label for="username">Username</label>
          <input type="text" id="username" name="username" placeholder="johndoe123" required>
        </div>
        
        <div class="form-group">
          <label for="email">Email Address</label>
          <input type="email" id="email" name="email" placeholder="example@ump.edu.my" required>
        </div>
        
        <div class="form-group">
          <label for="password">Password</label>
          <div class="password-wrapper">
            <input type="password" id="password" name="password" autocomplete="new-password" placeholder="••••••••••••" required>
            <i class="fa-solid fa-eye password-toggle-icon" id="togglePassword"></i>
          </div>
        </div>
        
        <div class="form-group">
          <label for="role">User Role Base</label>
          <select id="role" name="role" required>
            <option value="">-- Select Role Clearance --</option>
            <option value="admin">Petakom Coordinator (Administrator)</option>
            <option value="advisor">Event Advisor</option>
            <option value="student">Student</option>
          </select>
        </div>

      </div>

      <div class="form-buttons">
        <button type="reset" class="btn btn-secondary">Clear Form</button>
        <button type="submit" class="btn btn-primary">Create Account Profile</button>
      </div>
    </form>
  </div>

</div>

<script>
  // Password Visibility Toggle Logic
  const togglePassword = document.querySelector('#togglePassword');
  const passwordInput = document.querySelector('#password');

  togglePassword.addEventListener('click', function () {
    // Toggle the type attribute field parameter
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);
    
    // Toggle the icons cleanly between eye and slashed eye
    this.classList.toggle('fa-eye');
    this.classList.toggle('fa-eye-slash');
  });

  let warningShown = false;
  let popupElement = null;
  
  // Safe Fallback calculation for session countdown
  let remainingSeconds = <?php echo function_exists('getRemainingTime') ? getRemainingTime() : 120; ?>;
  
  function showWarningPopup() {
    if (popupElement) return;
    
    const minutes = Math.floor(remainingSeconds / 60);
    const seconds = remainingSeconds % 60;
    
    popupElement = document.createElement('div');
    popupElement.className = 'session-popup';
    popupElement.innerHTML = `
      <strong><i class="fas fa-exclamation-triangle"></i> Session Timeout Warning</strong>
      <div>Your administrative creation session workspace will expire in ${minutes}:${seconds.toString().padStart(2, '0')}.</div>
      <div class="popup-actions">
        <button class="stay-btn" onclick="stayLoggedIn()">Stay Logged In</button>
        <button class="logout-btn" onclick="logout()">Logout</button>
      </div>
    `;
    document.body.appendChild(popupElement);
  }
  
  function stayLoggedIn() {
    window.location.reload();
  }
  
  function logout() {
    window.location.href = '../Module_1/logout.php';
  }
  
  // High-performance countdown clock process loop
  const countdown = setInterval(function() {
    if (remainingSeconds <= 60 && remainingSeconds > 0 && !warningShown) {
      warningShown = true;
      showWarningPopup();
    }
    
    if (remainingSeconds <= 0) {
      clearInterval(countdown);
      window.location.href = '../Module_1/login.php?error=session_expired';
    }
    
    remainingSeconds--;
  }, 1000);
</script>