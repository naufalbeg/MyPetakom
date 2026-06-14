<?php
// HST.PHP
// Shared header + sidebar for Student, Advisor, and Admin roles.

session_start();

// 1) Fetch profile_info if not already
if (!isset($profile_info)) {
    if (!isset($conn)) {
        include '../Databased/db_connect.php';
    }

    // Determine the role (either set earlier or from “userRole”)
    $role = $_SESSION['user_role']
          ?? $_SESSION['userRole']
          ?? '';

    switch ($role) {
        case 'student':
            $sql = "
                SELECT 
                  s.student_name   AS name,
                  s.student_id_card AS id_card,
                  s.program,
                  s.semester,
                  s.faculty,
                  u.email
                FROM student s
                JOIN users u ON s.user_id = u.user_id
                WHERE s.user_id = ?
            ";
            break;

        case 'advisor':
            $sql = "
                SELECT 
                  ea.advisor_name AS name,
                  u.email
                FROM eventadvisor ea
                JOIN users u ON ea.user_id = u.user_id
                WHERE ea.user_id = ?
            ";
            break;

        case 'admin':
            $sql = "
                SELECT 
                  pa.admin_name AS name,
                  u.email
                FROM petakomadmin pa
                JOIN users u ON pa.user_id = u.user_id
                WHERE pa.user_id = ?
            ";
            break;

        default:
            $profile_info = ['name' => 'Guest', 'email' => ''];
            break;
    }

    if (isset($sql)) {
        // We need the current user’s numeric ID. 
        // We assume the session had stored that under one of two keys:
        $uid = $_SESSION['user_id']    // if you stored it as “user_id”
             ?? $_SESSION['userId']   // or if you called it “userId”
             ?? 0;

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $uid);
        $stmt->execute();
        $profile_info = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
}

// 2) Helper to highlight “active” menu items
function isActive($page, $dir = '') {
    $current_page = basename($_SERVER['PHP_SELF']);
    $current_dir  = basename(dirname($_SERVER['PHP_SELF']));

    if ($dir && $page) {
        if ($current_dir === $dir && $current_page === $page) {
            return 'active';
        }
    }
    elseif ($dir && !$page) {
        if ($current_dir === $dir) {
            return 'active';
        }
    }
    elseif ($current_page === $page) {
        return 'active';
    }
    return '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>
    <?php
      // If the including page set $page_title, use it; otherwise choose a default.
      if (isset($page_title) && trim($page_title) !== '') {
          echo htmlspecialchars($page_title);
      } else {
          // Fallback based on $role
          switch ($role) {
            case 'advisor':
              echo 'Advisor Dashboard';
              break;
            case 'admin':
              echo 'Admin Dashboard';
              break;
            default:
              echo 'Student Dashboard';
              break;
          }
      }

      // Append the user’s name (from $profile_info['name'])
      echo ' – ' . htmlspecialchars($profile_info['name'] ?? 'Guest');
    ?>
  </title>

  <!-- Font Awesome for icons -->
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Your shared CSS for header/sidebar -->
  <link rel="stylesheet" href="../HADER_SIDER_FOOTER/HST_css">

  <style>
    /* Ensure main-content is pushed right of the fixed sidebar */
    .dashboard, .main-container {
      flex: 1;
      margin-left: 200px;       /* same width as .sidebar */
      padding: 30px;
  background-color: #f0f0f0;
      min-height: calc(100vh - 90px);
    }
  </style>
</head>
<body>

  <div class="layout-container">
    <!-- Top Navigation (fixed height: 90px) -->
    <div class="main-header">
      <div class="logo-header">
        <a href="#" class="logo">
          <img src="../templet ( use this to match our overview)/image/logo-emblem__329x482.png"
               alt="MyPetakom Logo" />
        </a>
        <a href="../templet ( use this to match our overview)/image/images.png" class="logo">
          <img src="../templet ( use this to match our overview)/image/images.png"
               alt="MyPetakom Logo" />
        </a>
      </div>
      <div class="nav-actions">
        <a href="../Module_1/logout.php" class="btn-logout">
          <i class="fas fa-sign-out-alt"></i> Logout
        </a>
      </div>
    </div>

    <!-- Page Wrapper (Sidebar + Content) -->
    <div class="wrapper">
      <!-- Sidebar (fixed width: 280px) -->
      <div class="sidebar">
        <div class="sidebar-wrapper">
          <div class="sidebar-content">

            <!-- 2a) Show “Profile” heading based on role -->
            <h4 class="section-title">
              <?php
                switch ($role) {
                  case 'advisor': echo 'Advisor Profile'; break;
                  case 'admin':   echo 'Admin Profile';   break;
                  default:        echo 'Student Profile'; break;
                }
              ?>
            </h4>

            <!-- 2b) Profile picture placeholder -->
            <div class="profile-image-container">
              <img src="../templet ( use this to match our overview)/image/user-icon.webp"
                   alt="Profile Image"
                   class="profile-image">
            </div>

            <!-- 2c) Name (bold) and email (lighter) -->
            <?php if (!empty($profile_info)): ?>
              <div style="text-align:center; margin-bottom:20px; color:rgba(255,255,255,0.9);">
                <div style="font-weight:600; margin-bottom:5px;">
                  <?= htmlspecialchars($profile_info['name']) ?>
                </div>
                <div style="font-size:0.85rem; opacity:0.8;">
                  <?= htmlspecialchars($profile_info['email']) ?>
                </div>
              </div>
            <?php endif; ?>

            <!-- 3) Sidebar Menu Items -->
            <ul class="sidebar-menu">
              <?php if ($role === 'student'): ?>
                <!-- STUDENT MENU -->
                <li class="menu-item <?= isActive('dashboard_student.php','all_dashbord') ?>">
                  <a href="../all_dashbord/dashboard_student.php">
                    <i class="fas fa-th-large"></i> Dashboard
                  </a>
                </li>
                <li class="menu-item <?= isActive('Manage_User_Profile.php','MODULE_4') ?>">
                  <a href="../MODULE_4/Manage_User_Profile.php">
                    <i class="fas fa-user-edit"></i>  Profile
                  </a>
                </li>

                <li class="menu-item <?= isActive('Student-MembershipApplication.php','Module_1') ?>">
                  <a href="../Module_1/Student-MembershipApplication.php">
                    <i class="fas fa-id-card"></i> Manage Membership
                  </a>
                </li>
                
                <li class="menu-item <?= isActive('Student-ViewMembershipApproval.php','Module_1') ?>">
                  <a href="../Module_1/Student-ViewMembershipApproval.php">
                    <i class="fas fa-eye"></i> View Membership
                  </a>
                </li>                <li class="menu-item <?= isActive('scanatd.php','Module_3') ?>">
                  <a href="../Module_3/scanatd.php">
                    <i class="fas fa-camera"></i> Scan Attendance
                  </a>
                </li>

                <li class="menu-item <?= isActive('historyatd.php','Module_3') ?>">
                  <a href="../Module_3/historyatd.php">
                    <i class="fas fa-history"></i> Attendance History
                  </a>
                </li>

                <li class="menu-item <?= isActive('viewatd.php','Module_3') ?>">
                  <a href="../Module_3/viewatd.php">
                    <i class="fas fa-eye"></i> View Attendance
                  </a>
                </li>
                <li class="menu-item <?= isActive('Manage_Merits_Claims.php','MODULE_4') ?>">
                  <a href="../MODULE_4/Manage_Merits_Claims.php">
                    <i class="fas fa-clipboard-check"></i> Manage Merit Claims
                  </a>
                </li>
                <li class="menu-item <?= isActive('VIEW_AWARDED.PHP','MODULE_4') ?>">
                  <a href="../MODULE_4/VIEW_AWARDED.PHP">
                    <i class="fas fa-medal"></i> View Awarded Merits
                  </a>
                </li>


                

              <?php elseif ($role === 'advisor'): ?>
                <!-- ADVISOR MENU -->
                <li class="menu-item <?= isActive('dashboard_advisor.php','all_dashbord') ?>">
                  <a href="../all_dashbord/dashboard_advisor.php">
                    <i class="fas fa-th-large"></i> Dashboard
                  </a>
                </li>
                <li class="menu-item <?= isActive('manage_profile_advisor.php','MODULE_4') ?>">
                  <a href="../Module_2/manage_profile_advisor.php">
                    <i class="fas fa-user-edit"></i> Manage User Profile
                  </a>
                </li>
                <li class="menu-item <?= isActive('create_event.php','Module_2') ?>">
                  <a href="../Module_2/create_event.php">
                    <i class="fas fa-calendar-plus"></i> Create New Event
                  </a>
                </li>
                <li class="menu-item <?= isActive('create_committee.php','Module_2') ?>">
                  <a href="../Module_2/create_committee.php">
                    <i class="fas fa-user-plus"></i> Register Committee
                  </a>
                </li>
                <li class="menu-item <?= isActive('manage_event.php','Module_2') ?>">
                  <a href="../Module_2/manage_event.php">
                    <i class="fas fa-tasks"></i> Manage Events
                  </a>
                </li>
                <li class="menu-item <?= isActive('manage_committee.php','Module_2') ?>">
                  <a href="../Module_2/manage_committee.php">
                    <i class="fas fa-users-cog"></i> Manage Committees
                  </a>
                </li>
                <li class="menu-item <?= isActive('merit_approval.php','MODULE_4') ?>">
                  <a href="../Module_4/manage_claim_advisor.php">
                    <i class="fas fa-check-double"></i> Merit Application Approval
                  </a>
                </li>                <li class="menu-item <?= isActive('event_qr.php','Module_2') ?>">
                  <a href="../Module_2/event_qr.php">
                    <i class="fas fa-qrcode"></i> Event QR Code
                  </a>
                </li>
                <li class="menu-item <?= isActive('manageatd.php','Module_3') ?>">
                  <a href="../Module_3/manageatd.php">
                    <i class="fas fa-clipboard-list"></i> Manage Attendance
                  </a>
                </li>
                <li class="menu-item <?= isActive('createatd.php','Module_3') ?>">
                  <a href="../Module_3/createatd.php">
                    <i class="fas fa-plus-circle"></i> Create Attendance
                  </a>
                </li>

              <?php elseif ($role === 'admin'): ?>
                <!-- ADMIN MENU -->
                <li class="menu-item <?= isActive('Admin-Dashboard.php','all_dashbord') ?>">
                  <a href="../all_dashbord/Admin-Dashboard.php">
                    <i class="fas fa-th-large"></i> Dashboard
                  </a>
                </li>
                <li class="menu-item <?= isActive('Admin-CreateUserAccount.php','Module_1') ?>">
                  <a href="../Module_1/Admin-CreateUserAccount.php">
                    <i class="fas fa-user-plus"></i> Create User Account
                  </a>
                </li>
                <li class="menu-item <?= isActive('Admin-ManageUserProfiles.php','Module_1') ?>">
                  <a href="../Module_1/Admin-ManageUserProfiles.php">
                    <i class="fas fa-users"></i> Manage User Profiles
                  </a>
                </li>
                <li class="menu-item <?= isActive('Admin-ManageMembership.php','Module_1') ?>">
                  <a href="../Module_1/Admin-ManageMembership.php">
                    <i class="fas fa-id-card"></i> Manage Membership
                  </a>
                </li>
              <?php endif; ?>
            </ul>
          </div>
        </div>
      </div>
  <footer class="footer">
    <p>&copy; 2025 MyPetakom System. All rights reserved. | UMP Student Dashboard</p>
  </footer>
<?php

