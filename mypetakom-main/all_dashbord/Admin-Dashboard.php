<?php 
require_once '../Module_1/session_config.php';
requireLogin();

include('../Databased/db_connect.php');

// Define specific configurations before loading HST
$page_title = "Petakom Coordinator Dashboard";

// Include header/sidebar framework
include '../HADER_SIDER_FOOTER/HST.PHP';
?>

<style>
  /* Base Dashboard Layout overrides to match HST frame */
  .main-container {
    margin-left: 280px; /* Aligned perfectly with HST sidebar width */
    padding: 40px;
    background-color: #f8f9fa;
    min-height: calc(100vh - 90px);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    box-sizing: border-box;
  }

  .dashboard-header-title {
    margin-bottom: 30px;
    color: #333;
  }

  .dashboard-header-title h1 {
    font-size: 1.8rem;
    font-weight: 600;
    margin: 0 0 5px 0;
  }

  .dashboard-header-title p {
    color: #6c757d;
    margin: 0;
    font-size: 0.95rem;
  }

  /* Grid configuration for analytical widgets */
  .stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 25px;
    margin-bottom: 30px;
  }

  /* Stat Metric Cards Styling */
  .metric-card {
    background: #ffffff;
    border-radius: 12px;
    padding: 25px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02), 0 1px 3px rgba(0, 0, 0, 0.05);
    border: 1px solid #e9ecef;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }

  .metric-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 15px rgba(0, 0, 0, 0.05);
  }

  .metric-info p {
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #868e96;
    margin: 0 0 8px 0;
    font-weight: 600;
  }

  .metric-info h2 {
    font-size: 2rem;
    color: #212529;
    margin: 0;
    font-weight: 700;
  }

  .metric-icon {
    width: 55px;
    height: 55px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
  }

  /* Unique Card Accents */
  .icon-students { background-color: #e7f5ff; color: #228be6; }
  .icon-lecturers { background-color: #f3f0ff; color: #7950f2; }

  /* Charts Panel Layout */
  .charts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
    gap: 25px;
  }

  .chart-card {
    background: #ffffff;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02), 0 1px 3px rgba(0, 0, 0, 0.05);
    border: 1px solid #e9ecef;
  }

  .chart-card p {
    font-size: 1rem;
    font-weight: 600;
    color: #495057;
    margin: 0 0 20px 0;
    border-bottom: 1px solid #f1f3f5;
    padding-bottom: 10px;
  }

  .chart-container {
    position: relative;
    width: 100%;
    max-height: 280px;
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
</style>

<div class="main-container">
  
  <div class="dashboard-header-title">
    <h1>Petakom Coordinator Panel</h1>
    <p>System Overview, Analytics, and Administrative Dashboard metrics.</p>
  </div>

  <div class="stats-grid">
    
    <div class="metric-card">
      <div class="metric-info">
        <p>Computer Science Students</p>
        <h2>15,000</h2>
      </div>
      <div class="metric-icon icon-students">
        <i class="fas fa-graduation-cap"></i>
      </div>
    </div>

    <div class="metric-card">
      <div class="metric-info">
        <p>Computer Science Lecturers</p>
        <h2>3,000</h2>
      </div>
      <div class="metric-icon icon-lecturers">
        <i class="fas fa-chalkboard-teacher"></i>
      </div>
    </div>

  </div>

  <div class="charts-grid">
    
    <div class="chart-card">
      <p><i class="fas fa-chart-line" style="color:#228be6; margin-right:8px;"></i> Student Growth per Year</p>
      <div class="chart-container">
        <canvas id="studentLineChart"></canvas>
      </div>
    </div>

    <div class="chart-card">
      <p><i class="fas fa-chart-bar" style="color:#7950f2; margin-right:8px;"></i> Event Attendance Rate (%)</p>
      <div class="chart-container">
        <canvas id="attendanceBarChart"></canvas>
      </div>
    </div>

  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
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
      <div>Your administrative workspace session will expire in ${minutes}:${seconds.toString().padStart(2, '0')}.</div>
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

  // Initialize Data Charts on View Layout Ready
  const ctx1 = document.getElementById('studentLineChart').getContext('2d');
  new Chart(ctx1, {
    type: 'line',
    data: {
      labels: ['2021', '2022', '2023', '2024', '2025'],
      datasets: [{
        label: 'Students Registered',
        data: [2500, 3000, 3200, 4000, 4300],
        borderColor: '#228be6',
        backgroundColor: 'rgba(34, 139, 230, 0.1)',
        fill: true,
        tension: 0.35,
        borderWidth: 2.5
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } }
    }
  });

  const ctx2 = document.getElementById('attendanceBarChart').getContext('2d');
  new Chart(ctx2, {
    type: 'bar',
    data: {
      labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May'],
      datasets: [{
        label: 'Attendance Rate (%)',
        data: [85, 90, 88, 92, 87],
        backgroundColor: 'rgba(121, 80, 242, 0.75)',
        hoverBackgroundColor: '#7950f2',
        borderRadius: 6
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: { 
        y: { beginAtZero: true, max: 100, grid: { color: '#f1f3f5' } },
        x: { grid: { display: false } }
      }
    }
  });
</script>