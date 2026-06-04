<?php
require_once 'session_config.php';
requireLogin();

include '../../Databased/db_connect.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle approve/reject actions
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && isset($_POST['id'])) {
    $id = $_POST['id'];
    $status = $_POST['action'] === "approve" ? "approved" : "rejected";

    $update = "UPDATE membership SET status = ? WHERE membership_id = ?";
    $stmt = $conn->prepare($update);
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
    $stmt->close();
}

// Fetch all memberships with student info, ordered by status and student name
$sql = "
  SELECT m.membership_id, s.student_id, s.student_name, s.program, s.semester, s.student_id_card, m.status
  FROM membership m
  JOIN student s ON m.user_id = s.user_id
  ORDER BY FIELD(m.status, 'pending', 'approved', 'rejected'), s.student_name
";
$result = $conn->query($sql);

// Pass page title context variable to HST layout header
$page_title = "Manage Membership Approvals";

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
    padding: 18px 28px;
    border-bottom: 2px solid #dee2e6;
  }

  .modern-data-table td {
    padding: 18px 28px;
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

  /* Badge styling for Status Variables */
  .status-badge {
    display: inline-block;
    padding: 6px 14px;
    border-radius: 30px;
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: capitalize;
  }
  .status-badge.pending { background-color: #fff9db; color: #f59f00; }
  .status-badge.approved { background-color: #e6fcf5; color: #0ca678; }
  .status-badge.rejected { background-color: #fff5f5; color: #fa5252; }

  /* Interactive Action Control Elements */
  .action-buttons-container {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: center;
  }

  .action-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 18px;
    font-size: 0.9rem;
    font-weight: 600;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    transition: background 0.2s ease, transform 0.1s ease;
  }

  .action-btn:active {
    transform: scale(0.97);
  }

  .approve-btn { background-color: #e6fcf5; color: #0ca678; }
  .approve-btn:hover { background-color: #c3fae8; }

  .reject-btn { background-color: #fff5f5; color: #fa5252; }
  .reject-btn:hover { background-color: #ffe3e3; }

  .view-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #228be6;
    text-decoration: none;
    font-weight: 600;
  }
  .view-link:hover {
    text-decoration: underline;
  }

  em {
    color: #868e96;
    font-style: italic;
    font-size: 0.95rem;
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

<div class="main-container">
  <div class="admin-table-card">
    <h2>Students Membership Approval</h2>
    <p class="table-subtitle">Process incoming registration profiles, verify digital student identification registers, and authorize platform access clearance configurations.</p>

    <div class="table-responsive-wrapper">
      <table class="modern-data-table">
        <thead>
          <tr>
            <th>Student ID</th>
            <th>Full Name</th>
            <th>Program</th>
            <th>Semester</th>
            <th>Student ID Card</th>
            <th>Status</th>
            <th>Actions Control</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td><strong><?= htmlspecialchars($row['student_id']) ?></strong></td>
                <td><?= htmlspecialchars($row['student_name']) ?></td>
                <td><?= htmlspecialchars($row['program']) ?></td>
                <td>Semester <?= htmlspecialchars($row['semester']) ?></td>
                <td>
                  <a href="<?= htmlspecialchars($row['student_id_card']) ?>" target="_blank" class="view-link">
                    <i class="fa-regular fa-file-image"></i> View Card
                  </a>
                </td>
                <td>
                  <span class="status-badge <?= htmlspecialchars($row['status']) ?>">
                    <?= htmlspecialchars(ucfirst($row['status'])) ?>
                  </span>
                </td>
                <td>
                  <div class="action-buttons-container">
                    <?php if ($row['status'] === 'pending'): ?>
                      <form method="post" style="display:inline;" onsubmit="return confirm('Approve this student membership application?');">
                        <input type="hidden" name="id" value="<?= $row['membership_id'] ?>">
                        <button type="submit" name="action" value="approve" class="action-btn approve-btn">
                          <i class="fa-regular fa-circle-check"></i> Approve
                        </button>
                      </form>
                      <form method="post" style="display:inline;" onsubmit="return confirm('Reject this student membership application?');">
                        <input type="hidden" name="id" value="<?= $row['membership_id'] ?>">
                        <button type="submit" name="action" value="reject" class="action-btn reject-btn">
                          <i class="fa-regular fa-circle-xmark"></i> Reject
                        </button>
                      </form>
                    <?php else: ?>
                      <em>No actions available</em>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" style="text-align: center; padding: 30px; color: #6c757d;">No membership verification records located inside database files.</td>
            </tr>
          <?php endif; ?>
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
    
    function logout() {
      window.location.href = 'logout.php';
    }
    
    // Countdown timer clock cycle
    const countdown = setInterval(function() {
      if (remainingSeconds <= 60 && remainingSeconds > 0 && !warningShown) {
        warningShown = true;
        showWarningPopup();
      }
      
      if (remainingSeconds <= 0) {
        clearInterval(countdown);
        window.location.href = 'login.php?error=session_expired';
      }
      
      remainingSeconds--;
    }, 1000);
    
    // Logout handling framework initialization
    const logoutBtn = document.getElementById('logoutButton');
    if (logoutBtn) {
      logoutBtn.addEventListener('click', function(event) {
        event.preventDefault();
        const confirmLogout = confirm("Are you sure you want to log out?");
        if (confirmLogout) {
          window.location.href = 'logout.php';
        }
      });
    }
</script>

<?php $conn->close(); ?>