<?php
require_once '../Module_1/session_config.php';
requireLogin();

include('../Databased/db_connect.php');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM users";
$result = $conn->query($sql);

// Pass page title context variable to HST layout header
$page_title = "Manage User Profiles";

include '../HADER_SIDER_FOOTER/HST.PHP';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
  /* Base Dashboard Layout overrides to match HST frame */
  .main-container {
    margin-left: 280px; /* Aligned perfectly with HST sidebar width */
    padding: 40px 60px; /* Increased side padding for extra wide containers */
    background-color: #f8f9fa;
    min-height: calc(100vh - 90px);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    box-sizing: border-box;
  }

  /* ULTRA WIDESCREEN ENLARGEMENT: Maximized horizontal management card footprint */
  .admin-table-card {
    background: #ffffff;
    border-radius: 16px;
    padding: 60px; /* Enhanced interior comfort padding */
    max-width: 1650px; /* Expanded heavily to take up maximum widescreen screen space */
    width: 100%;
    margin: 10px auto;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04), 0 1px 8px rgba(0, 0, 0, 0.02);
    border: 1px solid #e9ecef;
    box-sizing: border-box;
  }

  .admin-table-card h2 {
    font-size: 2.4rem;
    font-weight: 700;
    color: #1a1d20;
    margin: 0 0 14px 0;
  }

  .table-subtitle {
    color: #6c757d;
    font-size: 1.1rem;
    margin-bottom: 45px;
    border-bottom: 1px solid #e9ecef;
    padding-bottom: 25px;
  }

  /* Responsive Structural Table Wrapper */
  .table-responsive-wrapper {
    width: 100%;
    overflow-x: auto;
    border-radius: 8px;
    border: 1px solid #e9ecef;
  }

  /* Clean Modern Design Data Table expanded horizontally */
  .modern-data-table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
    font-size: 1.05rem;
  }

  .modern-data-table th {
    background-color: #f1f3f5;
    color: #495057;
    font-weight: 700;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.75px;
    padding: 18px 28px; /* Roomier horizontal padding for th */
    border-bottom: 2px solid #dee2e6;
  }

  .modern-data-table td {
    padding: 18px 28px; /* Roomier horizontal padding for td */
    border-bottom: 1px solid #e9ecef;
    color: #212529;
    vertical-align: middle;
  }

  .modern-data-table tr:last-child td {
    border-bottom: none;
  }

  .modern-data-table tr:hover td {
    background-color: #f8f9fa;
  }

  /* Badge styling for User Roles */
  .role-badge {
    display: inline-block;
    padding: 6px 14px;
    border-radius: 30px;
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: capitalize;
  }

  .role-badge.admin { background-color: #e7f5ff; color: #228be6; }
  .role-badge.advisor { background-color: #f3f0ff; color: #748ffc; }
  .role-badge.student { background-color: #e6fcf5; color: #0ca678; }

  /* Table Interactive Action Control Buttons */
  .action-buttons-container {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
  }

  .action-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 18px; /* Slightly wider click parameters */
    font-size: 0.9rem;
    font-weight: 600;
    border-radius: 6px;
    text-decoration: none;
    transition: background 0.2s ease, transform 0.1s ease;
  }

  .action-link:active {
    transform: scale(0.97);
  }

  .view-btn { background-color: #e7f5ff; color: #228be6; }
  .view-btn:hover { background-color: #d0ebff; }

  .edit-btn { background-color: #fff9db; color: #f59f00; }
  .edit-btn:hover { background-color: #fff3bf; }

  .delete-btn { background-color: #fff5f5; color: #fa5252; }
  .delete-btn:hover { background-color: #ffe3e3; }

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

  @keyframes slideInUp {
    from { transform: translateY(50px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
  }

  /* Responsive Breakpoint scaling */
  @media (max-width: 1200px) {
    .main-container {
      padding: 20px;
    }
    .admin-table-card {
      padding: 30px;
    }
  }
</style>

<?php
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'deleted') {
        echo "<script>alert('User deleted successfully.');</script>";
    } elseif ($_GET['msg'] == 'updated') {
        echo "<script>alert('User updated successfully.');</script>";
    }
}
?>

<div class="main-container">
  <div class="admin-table-card">
    <h2>Manage User Profiles</h2>
    <p class="table-subtitle">Review configuration logs, process modifications, and verify account roles registered inside the MyPetakom core application architecture.</p>

    <div class="table-responsive-wrapper">
      <table class="modern-data-table">
        <thead>
          <tr>
            <th>User ID</th>
            <th>Username</th>
            <th>Full Name</th>
            <th>Email Address</th>
            <th>Role Badge</th>
            <th>Actions Control</th>
          </tr>
        </thead>
        <tbody id="userTableBody">
          <?php
          if ($result->num_rows > 0) {
              while($row = $result->fetch_assoc()) {
                  // Determine proper clean css classes for roles dynamically
                  $roleClass = 'student';
                  if ($row['user_role'] === 'admin') $roleClass = 'admin';
                  if ($row['user_role'] === 'advisor') $roleClass = 'advisor';
                  
                  echo "<tr>
                        <td><strong>#{$row['user_id']}</strong></td>
                        <td>{$row['username']}</td>  
                        <td>{$row['name']}</td>
                        <td>{$row['email']}</td>
                        <td><span class='role-badge {$roleClass}'>{$row['user_role']}</span></td>
                        <td>
                          <div class='action-buttons-container'>
                            <a class='action-link view-btn' href='Admin-ViewUser.php?id={$row['user_id']}'><i class='fa-regular fa-eye'></i> View</a>
                            <a class='action-link edit-btn' href='Admin-EditUser.php?id={$row['user_id']}'><i class='fa-regular fa-pen-to-square'></i> Edit</a>
                            <a class='action-link delete-btn' href='Admin-DeleteUser.php?id={$row['user_id']}' onclick='return confirm(\"Are you sure you want to delete this user?\");'><i class='fa-regular fa-trash-can'></i> Delete</a>
                          </div>
                        </td>
                      </tr>";
              }
          } else {
              echo "<tr><td colspan='6' style='text-align: center; padding: 30px; color: #6c757d;'>No user account profiles located inside database registers.</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
  let warningShown = false;
  let popupElement = null;
  let remainingSeconds = <?php echo function_exists('getRemainingTime') ? getRemainingTime() : 120; ?>;
  
  function showWarningPopup() {
    if (popupElement) return;
    const minutes = Math.floor(remainingSeconds / 60);
    const seconds = remainingSeconds % 60;
    
    popupElement = document.createElement('div');
    popupElement.className = 'session-popup';
    popupElement.innerHTML = `
      <strong><i class="fas fa-exclamation-triangle"></i> Session Timeout Warning</strong>
      <div>Your administrative workspace session will expire in ${minutes}:${seconds.toString().padStart(2, '0')}.</div>
      <div class="popup-actions">
        <button class="stay-btn" onclick="stayLoggedIn()">Stay Logged In</button>
        <button class="logout-btn" onclick="logout()">Logout Now</button>
      </div>
    `;
    document.body.appendChild(popupElement);
  }
  
  function stayLoggedIn() {
    window.location.reload();
  }
  
  // Safe redirection to prevent routing failure loops
  function logout() {
    window.location.href = '../Module_1/logout.php';
  }
  
  // Countdown timer clock loop
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
  
  // Logout action handler
  const logoutBtn = document.getElementById('logoutButton');
  if (logoutBtn) {
    logoutBtn.addEventListener('click', function(event) {
      event.preventDefault();
      const confirmLogout = confirm("Are you sure you want to log out?");
      if (confirmLogout) {
        window.location.href = '../Module_1/logout.php';
      }
    });
  }
</script>

<?php
$conn->close();
?>