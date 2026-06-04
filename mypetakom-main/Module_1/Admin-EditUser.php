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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    $updateSql = "UPDATE users SET name = ?, email = ?, user_role = ? WHERE user_id = ?";
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param("sssi", $name, $email, $role, $userId);

    if ($stmt->execute()) {
        header("Location: Admin-ManageUserProfiles.php?msg=updated");
        exit();
    } else {
        echo "Error updating user: " . $conn->error;
    }
}

// Pass page title context variable to HST layout header
$page_title = "Modify User Credentials Workspace";

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

  /* ULTRA WIDESCREEN ENLARGEMENT: Match management and registration card layouts */
  .admin-form-card {
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

  .admin-form-card h2 {
    font-size: 2.4rem;
    font-weight: 700;
    color: #1a1d20;
    margin: 0 0 14px 0;
  }

  .form-subtitle {
    color: #6c757d;
    font-size: 1.1rem;
    margin-bottom: 45px;
    border-bottom: 1px solid #e9ecef;
    padding-bottom: 25px;
  }

  /* Split layout grid to wrap items clean horizontally */
  .form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 35px 60px;
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

  /* Premium inputs & dropdown select elements mapping */
  .form-group input[type="text"],
  .form-group input[type="email"],
  .form-group select {
    width: 100%;
    padding: 16px 20px;
    font-size: 1.05rem;
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

  /* Footer Action controls container buttons */
  .form-buttons {
    display: flex;
    justify-content: flex-end;
    gap: 20px;
    margin-top: 55px;
    border-top: 1px solid #e9ecef;
    padding-top: 35px;
  }

  .btn {
    padding: 16px 45px;
    font-size: 1.05rem;
    font-weight: 600;
    border-radius: 8px;
    cursor: pointer;
    border: none;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
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
    color: #212529;
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

  /* Smooth break back constraints for smaller system viewports */
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
    <h2>Edit User Profile</h2>
    <p class="form-subtitle">Modify profile configuration parameters, system access logs, and core privileges assigned to this system user runtime instance.</p>

    <?php if (isset($user) && $user): ?>
      <form method="post" autocomplete="off">
        <div class="form-grid">
          
          <div class="form-group full-width">
            <label for="name"><i class="fa-regular fa-id-card"></i> Full Name</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
          </div>

          <div class="form-group">
            <label for="email"><i class="fa-regular fa-envelope"></i> Email Address</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
          </div>

          <div class="form-group">
            <label for="role"><i class="fa-solid fa-shield-halved"></i> Security Clearance Role</label>
            <select id="role" name="role" required>
              <option value="admin" <?= $user['user_role'] === 'admin' ? 'selected' : '' ?>>Petakom Coordinator (Administrator)</option>
              <option value="advisor" <?= $user['user_role'] === 'advisor' ? 'selected' : '' ?>>Event Advisor</option>
              <option value="student" <?= $user['user_role'] === 'student' ? 'selected' : '' ?>>Student</option>
            </select>
          </div>

        </div>

        <div class="form-buttons">
          <a href="../Module_1/Admin-ManageUserProfiles.php" class="btn btn-secondary">
            <i class="fa-solid fa-xmark"></i> Cancel / Revert
          </a>
          <button type="submit" class="btn btn-primary">
            <i class="fa-regular fa-floppy-disk"></i> Update Profile Changes
          </button>
        </div>
      </form>
    <?php else: ?>
      <div class="error-box">
        <i class="fa-solid fa-triangle-exclamation"></i> Error: The targeted application configuration profile user could not be mapped inside the current architecture schema.
      </div>
      <div class="form-buttons" style="border-top:none; margin-top: 20px; padding-top: 0;">
        <a href="../Module_1/Admin-ManageUserProfiles.php" class="btn btn-secondary">
          <i class="fa-solid fa-arrow-left"></i> Return to Registry List
        </a>
      </div>
    <?php endif; ?>

  </div>
</div>