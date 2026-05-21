<?php 
include('../Databased/db_connect.php');
  include '../HADER_SIDER_FOOTER/HST.PHP';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Petakom Coordinator (Administrator)</title>
  <link rel="stylesheet" href="../CSS/MODULE_1_css/style.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>


  <div class="main-container">

    <!-- Dashboard -->
    <div class="dashboard">
      <div class="stats">
        <!-- Students Card -->
        <div class="card">
          <p>Number of Computer Science Students</p>
          <h2>15,000</h2>
        </div>

        <!-- Lecturers Card -->
        <div class="card">
          <p>Number of Computer Science Lecturers</p>
          <h2>3,000</h2>
        </div>

        <!-- Line Chart -->
        <div class="card chart">
          <p>Student per Year</p>
          <canvas id="studentLineChart"></canvas>
        </div>

        <!-- Bar Chart -->
        <div class="card chart">
          <p>Attendance Rate (%)</p>
          <canvas id="attendanceBarChart"></canvas>
        </div>
      </div>
    </div>
  </div>

  <!-- Chart JS -->
  <script>
    // Line Chart: Student per Year
    const ctx1 = document.getElementById('studentLineChart').getContext('2d');
    new Chart(ctx1, {
      type: 'line',
      data: {
        labels: ['2021', '2022', '2023', '2024', '2025'],
        datasets: [{
          label: 'Number of Students',
          data: [2500, 3000, 3200, 4000, 4300],
          borderColor: 'rgba(75, 192, 192, 1)',
          backgroundColor: 'rgba(75, 192, 192, 0.2)',
          fill: true,
          tension: 0.4
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: true }
        },
        scales: {
          y: { beginAtZero: true }
        }
      }
    });

    // Bar Chart: Attendance Rate
    const ctx2 = document.getElementById('attendanceBarChart').getContext('2d');
    new Chart(ctx2, {
      type: 'bar',
      data: {
        labels: ['January', 'February', 'March', 'April', 'May'],
        datasets: [{
          label: 'Attendance Rate (%)',
          data: [85, 90, 88, 92, 87],
          backgroundColor: 'rgba(153, 102, 255, 0.6)',
          borderColor: 'rgba(153, 102, 255, 1)',
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: true }
        },
        scales: {
          y: {
            beginAtZero: true,
            max: 100
          }
        }
      }
    });

    // Logout Button Function
    document.getElementById('logoutButton').addEventListener('click', function(event) {
      event.preventDefault();
      const confirmLogout = confirm("Are you sure you want to log out?");
      if (confirmLogout) {
        sessionStorage.setItem('logoutSuccess', 'true');
        window.location.href = 'login.php'; // Replace with your login page
      }
    });
  </script>
</body>
</html>
