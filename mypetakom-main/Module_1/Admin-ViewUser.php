<?php
include('../Databased/db_connect.php');

if (isset($_GET['id'])) {
    $userId = $_GET['id'];
    $sql = "SELECT * FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
}

// Pass page title context variable to HST layout header
$page_title = "View User Profile Workspace";

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

  /* ULTRA WIDESCREEN ENLARGEMENT: Match management card footprints precisely */
  .admin-view-card {
    background: #ffffff;
    border-radius: 16px;
    padding: 60px; /* Enhanced interior comfort padding */
    max-width: 1650px; /* Expanded heavily to take up maximum widescreen space */
    width: 100%;
    margin: 10px auto;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04), 0 1px 8px rgba(0, 0, 0, 0.02);
    border: 1px solid #e9ecef;
    box-sizing: border-box;
  }

  .admin-view-card h2 {
    font-size: 2.4rem;
    font-weight: 700;
    color: #1a1d20;
    margin: 0 0 14px 0;
  }

  .view-subtitle {
    color: #6c757d;
    font-size: 1.1rem;
    margin-bottom: 45px;
    border-bottom: 1px solid #e9ecef;
    padding-bottom: 25px;
  }

  /* Modern Presentation Grid for Profile Metadata */
  .profile-data-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 35px 60px;
    margin-bottom: 45px;
  }

  .data-item {
    background-color: #f8f9fa;
    padding: 20px 25px;
    border-radius: 8px;
    border-left: 4px solid #228be6; /* Subtle accent matching registration forms */
  }

  .data-item.full-width {
    grid-column: span 2;
  }

  .data-label {
    display: block;
    font-size: 0.85rem;
    font-weight: 700;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.75px;
    margin-bottom: 8px;
  }

  .data-value {
    font-size: 1.15rem;
    color: #212529;
    font-weight: 600;
  }

  /* Role Badge custom styling inside visualization pane */
  .role-tag {
    display: inline-block;
    padding: 6px 16px;
    border-radius: 30px;
    font-size: 0.9rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  .role-tag.admin { background-color: #e7f5ff; color: #228be6; }
  .role-tag.advisor { background-color: #f3f0ff; color: #748ffc; }
  .role-tag.student { background-color: #e6fcf5; color: #0ca678; }

  /* Navigation Layout Action Button Footer */
  .view-actions-footer {
    border-top: 1px solid #e9ecef;
    padding-top: 35px;
    display: flex;
    justify-content: flex-start;
  }

  .btn-back {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 16px 35px;
    font-size: 1.05rem;
    font-weight: 600;
    border-radius: 8px;
    text-decoration: none;
    cursor: pointer;
    background-color: #f1f3f5;
    color: #495057;
    transition: background 0.2s ease, transform 0.1s ease;
  }

  .btn-back:hover {
    background-color: #e9ecef;
    color: #212529;
  }

  .btn-back:active {
    transform: scale(0.99);
  }

  .error-box {
    padding: 30px;
    background-color: #fff5f5;
    color: #fa5252;
    border-radius: 8px;
    border: 1px solid #ffe3e3;
    font-size: 1.1rem;
    font-weight: 600;
  }

  /* Collapse back smoothly to single column on compact layout monitors */
  @media (max-width: 1200px) {
    .profile-data-grid {
      grid-template-columns: 1fr;
      gap: 20px;
    }
    .data-item.full-width {
      grid-column: span 1;
    }
    .main-container {
      padding: 20px;
    }
    .admin-view-card {
      padding: 30px;
    }
  }
</style>

<div class="main-container">
  <div class="admin-view-card">
    <h2>User Profile Details</h2>
    <p class="view-subtitle">Review static identity metadata, operational context parameters, and assignment classifications assigned to this platform user instance.</p>
    
    <?php if (isset($user) && $user): ?>
      <?php 
        // Determine dynamic tag styles for role strings cleanly
        $roleClass = 'student';
        if ($user['user_role'] === 'admin') $roleClass = 'admin';
        if ($user['user_role'] === 'advisor') $roleClass = 'advisor';
      ?>
      
      <div class="profile-data-grid">
        
        <div class="data-item full-width">
          <span class="data-label"><i class="fa-regular fa-id-card"></i> Full Name</span>
          <div class="data-value"><?= htmlspecialchars($user['name']) ?></div>
        </div>
        
        <div class="data-item">
          <span class="data-label"><i class="fa-regular fa-user"></i> Username Reference</span>
          <div class="data-value"><?= htmlspecialchars($user['username']) ?></div>
        </div>
        
        <div class="data-item">
          <span class="data-label"><i class="fa-regular fa-envelope"></i> Email Address Register</span>
          <div class="data-value"><?= htmlspecialchars($user['email']) ?></div>
        </div>
        
        <div class="data-item full-width">
          <span class="data-label"><i class="fa-solid fa-shield-halved"></i> Assigned Clearance Level</span>
          <div class="data-value">
            <span class="role-tag <?= $roleClass ?>"><?= htmlspecialchars($user['user_role']) ?></span>
          </div>
        </div>
        
      </div>
      
      <div class="view-actions-footer">
        <a href="../Module_1/Admin-ManageUserProfiles.php" class="btn-back">
          <i class="fa-solid fa-arrow-left"></i> Back to User Registry List
        </a>
      </div>
      
    <?php else: ?>
      <div class="error-box">
        <i class="fa-solid fa-triangle-exclamation"></i> Error: The requested target account profile could not be located inside database registers.
      </div>
      <div class="view-actions-footer">
        <a href="../Module_1/Admin-ManageUserProfiles.php" class="btn-back">
          <i class="fa-solid fa-arrow-left"></i> Return to Registry List
        </a>
      </div>
    <?php endif; ?>
    
  </div>
</div>