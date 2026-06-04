<?php
       
session_start();
include '../../Databased/db_connect.php';

// Use logged-in user_id from session
$stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$result = $stmt->get_result();

$row = $result->fetch_assoc();
$user_id = $row['user_id'];

if ($user_id == 0) {
    // Not logged in - redirect to login page or show error
    header("Location: login.php");
    exit();
}

// Fetch latest membership and student info for this user
$sql = "SELECT s.student_id, s.student_name, s.program, s.semester, s.faculty, s.student_id_card, m.join_date, m.status
        FROM student s 
        JOIN membership m ON s.user_id = m.user_id 
        WHERE s.user_id = ? 
        ORDER BY m.join_date DESC
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

// Pass page title context variable to HST layout header
$page_title = "Petakom Membership Verification Status";

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

  /* ULTRA WIDESCREEN ENLARGEMENT: Perfectly matches the 1650px card footprints */
  .status-display-card {
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

  .status-display-card h2 {
    font-size: 2.4rem;
    font-weight: 700;
    color: #1a1d20;
    margin: 0 0 14px 0;
  }

  .status-subtitle {
    color: #6c757d;
    font-size: 1.1rem;
    margin-bottom: 45px;
    border-bottom: 1px solid #e9ecef;
    padding-bottom: 25px;
  }

  .section-divider-title {
    font-size: 1.3rem;
    font-weight: 700;
    color: #495057;
    margin: 35px 0 20px 0;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .section-divider-title:first-of-type {
    margin-top: 0;
  }

  /* Presentation Grid for Student Profile Details */
  .info-data-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 24px 45px;
  }

  .info-card-item {
    background-color: #f8f9fa;
    padding: 18px 24px;
    border-radius: 8px;
    border-left: 4px solid #ced4da; /* Neutral accent baseline */
    display: flex;
    flex-direction: column;
    justify-content: center;
  }

  .info-card-item.status-active-accent {
    border-left-color: #228be6; /* Highlight border row */
  }

  .info-label {
    display: block;
    font-size: 0.85rem;
    font-weight: 700;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.75px;
    margin-bottom: 6px;
  }

  .info-value {
    font-size: 1.1rem;
    color: #212529;
    font-weight: 600;
  }

  /* Document link badge override styling */
  .document-fetch-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #228be6;
    text-decoration: none;
    font-weight: 600;
  }
  .document-fetch-link:hover {
    text-decoration: underline;
  }

  /* Dynamic Custom Badges for Approval States */
  .status-badge-inline {
    display: inline-block;
    padding: 6px 16px;
    border-radius: 30px;
    font-size: 0.9rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  .status-badge-inline.pending { background-color: #fff9db; color: #f59f00; }
  .status-badge-inline.approved { background-color: #e6fcf5; color: #0ca678; }
  .status-badge-inline.rejected { background-color: #fff5f5; color: #fa5252; }

  .empty-record-box {
    padding: 40px;
    background-color: #f1f3f5;
    color: #495057;
    border-radius: 8px;
    text-align: center;
    font-size: 1.1rem;
    font-weight: 600;
  }

  /* Responsive Fallbacks */
  @media (max-width: 1200px) {
    .info-data-grid {
      grid-template-columns: 1fr;
      gap: 20px;
    }
    .main-container {
      padding: 20px;
    }
    .status-display-card {
      padding: 30px;
    }
  }
</style>

<div class="main-container">
  <div class="status-display-card">
    <h2>Petakom Membership Status</h2>
    <p class="status-subtitle">Track the approval status of your enrollment request and verify your current academic clearance credentials within the system directory.</p>

    <?php if ($student): ?>
      
      <div class="section-divider-title">
        <i class="fa-regular fa-id-badge"></i> Academic Profile Details
      </div>
      
      <div class="info-data-grid">
        <div class="info-card-item">
          <span class="info-label">Student ID Reference</span>
          <div class="info-value"><?= htmlspecialchars($student['student_id']) ?></div>
        </div>

        <div class="info-card-item">
          <span class="info-label">Full Name Register</span>
          <div class="info-value"><?= htmlspecialchars($student['student_name']) ?></div>
        </div>

        <div class="info-card-item">
          <span class="info-label">Program Course</span>
          <div class="info-value"><?= htmlspecialchars($student['program']) ?></div>
        </div>

        <div class="info-card-item">
          <span class="info-label">Current Semester</span>
          <div class="info-value">Semester <?= htmlspecialchars($student['semester']) ?></div>
        </div>

        <div class="info-card-item">
          <span class="info-label">Faculty Affiliation</span>
          <div class="info-value"><?= htmlspecialchars($student['faculty']) ?></div>
        </div>

        <div class="info-card-item">
          <span class="info-label">Identification Card Attachment</span>
          <div class="info-value">
            <?php if ($student['student_id_card']): ?>
              <a href="<?= htmlspecialchars($student['student_id_card']) ?>" target="_blank" class="document-fetch-link">
                <i class="fa-regular fa-file-image"></i> View Uploaded Card
              </a>
            <?php else: ?>
              <span style="color: #868e96;"><i class="fa-solid fa-circle-minus"></i> No card payload located</span>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="section-divider-title">
        <i class="fa-solid fa-shield-halved"></i> Membership Verification Logs
      </div>

      <div class="info-data-grid">
        <div class="info-card-item">
          <span class="info-label">Application Entry Date</span>
          <div class="info-value"><?= htmlspecialchars($student['join_date']) ?></div>
        </div>

        <div class="info-card-item status-active-accent">
          <span class="info-label">Authorization Status</span>
          <div class="img-badge-wrapper" style="margin-top: 4px;">
            <span class="status-badge-inline <?= htmlspecialchars($student['status']) ?>">
              <?= htmlspecialchars(ucfirst($student['status'])) ?>
            </span>
          </div>
        </div>
      </div>

    <?php else: ?>
      <div class="empty-record-box">
        <i class="fa-solid fa-folder-open" style="font-size: 2rem; color: #adb5bd; display:block; margin-bottom:10px;"></i>
        No active structural registration or membership profile record found inside database parameters.
      </div>
    <?php endif; ?>

  </div>
</div>

<script>
  // Setup standard interactive confirmation bindings for framework layouts
  const logoutBtn = document.getElementById('logoutButton');
  if (logoutBtn) {
    logoutBtn.addEventListener('click', function(event) {
      event.preventDefault();
      const confirmLogout = confirm("Are you sure you want to log out?");
      if (confirmLogout) {
        window.location.href = 'login.php';
      }
    });
  }
</script>
</body>
</html>