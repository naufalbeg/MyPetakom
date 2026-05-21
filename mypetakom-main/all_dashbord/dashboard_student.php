  <?php
session_start();
  include '../../Databased/db_connect.php';
 // 1️ Check the existing session keys
if (
    !isset($_SESSION['username'], $_SESSION['userRole'])
    || $_SESSION['userRole'] !== 'student'
) {
    header("Location: ../Module_1/Login.php");
    exit;
}



// 2️ Look up the real user_id from the DB
$stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    // Something’s wrong—kick back to login
    header("Location: ../Module_1/Login.php");
    exit;
}

$row = $result->fetch_assoc();
$user_id = $row['user_id'];
  

  // Get total merits for the student
  $total_merits_sql = "SELECT SUM(m.points) as total_points
                      FROM merit m
                      JOIN attendance a ON a.event_id = m.event_id AND a.user_id = m.user_id
                      WHERE m.user_id = ? AND a.status_attd = 'present'";

  $stmt = $conn->prepare($total_merits_sql);
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $total_result = $stmt->get_result();
  $total_data = $total_result->fetch_assoc();
  $total_merits = $total_data['total_points'] ?? 0;

  // Get current year merits
  $current_year = date('Y');
  $current_year_sql = "SELECT SUM(m.points) as current_year_points
                      FROM merit m
                      JOIN attendance a ON a.event_id = m.event_id AND a.user_id = m.user_id
                      WHERE m.user_id = ? AND a.status_attd = 'present' 
                      AND m.academic_year = ?";

  $stmt2 = $conn->prepare($current_year_sql);
  $stmt2->bind_param("is", $user_id, $current_year);
  $stmt2->execute();
  $current_result = $stmt2->get_result();
  $current_data = $current_result->fetch_assoc();
  $current_year_merits = $current_data['current_year_points'] ?? 0;

  // Get student information
  $student_sql = "SELECT s.*, u.username, u.email 
                  FROM student s 
                  JOIN users u ON s.user_id = u.user_id 
                  WHERE s.user_id = ?";

  $stmt3 = $conn->prepare($student_sql);
  $stmt3->bind_param("i", $user_id);
  $stmt3->execute();
  $student_result = $stmt3->get_result();
  $student_info = $student_result->fetch_assoc();

  // Get recent awarded merits (last 5)
  $recent_merits_sql = "SELECT e.title AS event_name,
                              m.points,
                              e.start_date,
                              ec.role,
                              e.event_level
                        FROM merit m
                        JOIN events e ON m.event_id = e.event_id
                        JOIN attendance a ON a.event_id = e.event_id AND a.user_id = m.user_id
                        LEFT JOIN eventcommittee ec ON ec.event_id = e.event_id AND ec.user_id = m.user_id
                        WHERE m.user_id = ? AND a.status_attd = 'present'
                        ORDER BY e.start_date DESC
                        LIMIT 5";

  $stmt4 = $conn->prepare($recent_merits_sql);
  $stmt4->bind_param("i", $user_id);
  $stmt4->execute();
  $recent_result = $stmt4->get_result();

  // Get merit data for chart (monthly data for current year)
  $chart_data_sql = "SELECT 
                      MONTH(e.start_date) as month,
                      SUM(m.points) as monthly_points
                    FROM merit m
                    JOIN events e ON m.event_id = e.event_id
                    JOIN attendance a ON a.event_id = e.event_id AND a.user_id = m.user_id
                    WHERE m.user_id = ? AND a.status_attd = 'present' 
                    AND YEAR(e.start_date) = ?
                    GROUP BY MONTH(e.start_date)
                    ORDER BY MONTH(e.start_date)";

  $stmt5 = $conn->prepare($chart_data_sql);
  $stmt5->bind_param("ii", $user_id, $current_year);
  $stmt5->execute();
  $chart_result = $stmt5->get_result();
  $monthly_data = array_fill(1, 12, 0); // Initialize all months with 0
  while ($row = $chart_result->fetch_assoc()) {
      $monthly_data[$row['month']] = $row['monthly_points'];
  }

  // Get event participation by level for pie chart
  $event_level_sql = "SELECT 
                        e.event_level,
                        COUNT(*) as participation_count,
                        SUM(m.points) as total_points
                      FROM merit m
                      JOIN events e ON m.event_id = e.event_id
                      JOIN attendance a ON a.event_id = e.event_id AND a.user_id = m.user_id
                      WHERE m.user_id = ? AND a.status_attd = 'present'
                      GROUP BY e.event_level
                      ORDER BY participation_count DESC";

  $stmt_level = $conn->prepare($event_level_sql);
  $stmt_level->bind_param("i", $user_id);
  $stmt_level->execute();
  $level_result = $stmt_level->get_result();

  $level_data = [];
  $level_labels = [];
  $level_colors = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'];
  $color_index = 0;

  while ($row = $level_result->fetch_assoc()) {
      $level_labels[] = $row['event_level'] ?? 'Unknown';
      $level_data[] = $row['participation_count'];
      $color_index++;
  }

  // Get membership status
  $membership_sql = "SELECT * FROM membership WHERE user_id = ? ORDER BY join_date DESC LIMIT 1";
  $stmt6 = $conn->prepare($membership_sql);
  $stmt6->bind_param("i", $user_id);
  $stmt6->execute();
  $membership_result = $stmt6->get_result();
  $membership_info = $membership_result->fetch_assoc();

  // Set page title
  $page_title = "Student Dashboard";

  // Include header and sidebar
  include '../HADER_SIDER_FOOTER/HST.PHP';
  include '../MODULE_4/inser_merit.php';
  ?>



  <!DOCTYPE html>
  <html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard – Awarded Merits</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">    <link rel="stylesheet" href="../../mypetakom-1/CSS/dashbord.css">
  <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
  <style>
    .stats-row {
      display: flex;
      gap: 20px;
      flex-wrap: wrap;
    }
    
    .merits-table {
      flex: 1;
      min-width: 400px;
    }
    
    .charts-container {
      flex: 1;
      min-width: 400px;
      display: flex;
      flex-direction: column;
      gap: 20px;
    }
    
    .chart-card {
      background: white;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
    
    .chart-wrapper {
      height: 300px;
      margin: 15px 0;
    }
    
    @media (max-width: 1024px) {
      .stats-row {
        flex-direction: column;
      }
      
      .merits-table,
      .charts-container {
        min-width: 100%;
      }
    }
  </style>
  </head>
  <body>
  <!-- Main Content -->
  <div class="main-content">
    <div class="page-inner">
      <h2>Student Dashboard</h2>
      <p><b>Welcome back, <?= htmlspecialchars($student_info['student_name'] ?? 'Student') ?>!</b><br>Track Your Awarded Merits and Activities</p>
      

    </div>

    <div class="widget-row">
      <!-- Total Merit Widget -->
      <div class="card" style="background: #14519c">
        <div class="icon-box">
          <i class="fas fa-award"></i>
        </div >
        <p style="color: white;" >Total Merits Collected</p>
        <div class="card-title" style="color: white;" ><?= number_format($total_merits) ?></div>
      </div>

      <!-- Current Year Merits Widget -->
      <div class="card" style="background: #14519c">
        <div class="icon-box" style="background: linear-gradient(135deg, var(--success), #48c78e);">
          <i class="fas fa-calendar-alt"></i>
        </div>
        <p style="color: white;" >Current Semester Merits</p>
        <div class="card-title" style="color: white;" ><?= number_format($current_year_merits) ?></div>
      </div>

      <!-- QR Code Widget -->
<!-- QR Code Widget -->
<div class="card" style="background: #14519c">
  <div class="qr-box">
    <?php 
      $host = $_SERVER['HTTP_HOST'];
      $path = dirname($_SERVER['PHP_SELF']);
      $qrLink = "http://{$host}{$path}/student_info.php?student_id=" . $user_id;


    ?>
    <div id="studentQR" data-qrtext="<?= $qrLink ?>"></div>
  </div>
  <button onclick="downloadStudentQR()">⬇ Download QR</button>
  <button onclick="copyLink('<?= $qrLink ?>')">Copy Link</button>
</div>

    </div>    <div class="stats-row">
      <!-- Charts Container - Left Side -->
      <div class="charts-container">
        <!-- Merit Growth Chart -->
        <div class="chart-card">
          <h3>Merit Growth Over Time (<?= $current_year ?>)</h3>
          <div class="chart-wrapper">
            <canvas id="statisticsChart"></canvas>
          </div>
          <div class="chart-buttons">
            <button class="btn-export" onclick="exportChart()">
              <i class="fas fa-file-export"></i> Export
            </button>
            <button class="btn-print" onclick="window.print()">
              <i class="fas fa-print"></i> Print
            </button>
          </div>
        </div>

        <!-- Event Participation by Level Chart -->
        <div class="chart-card">
          <h3>Event Participation by Level</h3>
          <div class="chart-wrapper">
            <canvas id="participationChart"></canvas>
          </div>
          <div class="chart-buttons">
            <button class="btn-export" onclick="exportParticipationChart()">
              <i class="fas fa-file-export"></i> Export
            </button>
            <button class="btn-refresh" onclick="refreshParticipationChart()">
              <i class="fas fa-sync-alt"></i> Refresh
            </button>
          </div>
        </div>
      </div>

      <!-- Recent Awarded Merits Table - Right Side -->
      <div class="merits-table">
        <h3>Recent Events and Merits</h3>
        <table>
          <thead>
            <tr>
              <th>Event</th>
              <th>Role</th>
              <th>Points</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($recent_result->num_rows > 0): ?>
              <?php while ($row = $recent_result->fetch_assoc()): ?>
              <tr>
                <td><?= htmlspecialchars($row['event_name']) ?></td>
                <td>
                  <span class="badge badge-committee">
                    <?= htmlspecialchars($row['role'] ?? 'Participant') ?>
                  </span>
                </td>
                <td><?= $row['points'] ?></td>
                <td><?= date('M Y', strtotime($row['start_date'])) ?></td>
              </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="4" style="text-align: center; color: var(--text-light); padding: 20px;">
                  <i class="fas fa-info-circle"></i> No recent merits found. 
                  <br><small>Start participating in events to earn merits!</small>
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
        
        <?php if ($recent_result->num_rows > 0): ?>
          <div style="text-align: center; margin-top: 15px;">
            <a href="../MODULE_4/VIEW_AWARDED.PHP" class="btn-sm">
              <i class="fas fa-eye"></i> View All Merits
            </a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

      </div> <!-- End wrapper -->
    </div> <!-- End layout-container -->

    <footer class="footer">
      <p>&copy; 2025 MyPetakom System. All rights reserved. | UMP Student Dashboard</p>
    </footer>

  <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
  <script>
  // Chart.js implementation
  const ctx = document.getElementById('statisticsChart').getContext('2d');

  const monthlyData = <?= json_encode(array_values($monthly_data)) ?>;
  const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

 const statisticsChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: monthNames,
        datasets: [{
            label: 'Merit Points',
            data: monthlyData,
            borderColor: 'rgb(67, 97, 238)',
            backgroundColor: 'rgba(67, 97, 238, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: 'rgb(67, 97, 238)',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 6,
            pointHoverRadius: 8
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false, // This is crucial!
        plugins: {
            legend: {
                display: true,
                position: 'top',
            },
            tooltip: {
                mode: 'index',
                intersect: false,
                backgroundColor: 'rgba(0,0,0,0.8)',
                titleColor: '#fff',
                bodyColor: '#fff',
                borderColor: 'rgb(67, 97, 238)',
                borderWidth: 1
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(0,0,0,0.1)'
                },
                ticks: {
                    color: '#666'
                }
            },
            x: {
                grid: {
                    color: 'rgba(0,0,0,0.1)'
                },
                ticks: {
                    color: '#666'
                }
            }
        },
        interaction: {
            mode: 'nearest',
            axis: 'x',
            intersect: false
        }
    }
});

// Event Participation Pie Chart
const participationCtx = document.getElementById('participationChart').getContext('2d');
const levelLabels = <?= json_encode($level_labels) ?>;
const levelData = <?= json_encode($level_data) ?>;

const participationChart = new Chart(participationCtx, {
    type: 'doughnut',
    data: {
        labels: levelLabels.length > 0 ? levelLabels : ['No Data'],
        datasets: [{
            label: 'Event Participation',
            data: levelData.length > 0 ? levelData : [1],
            backgroundColor: [
                '#FF6384',
                '#36A2EB', 
                '#FFCE56',
                '#4BC0C0',
                '#9966FF',
                '#FF9F40',
                '#FF6B6B',
                '#4ECDC4'
            ],
            borderColor: '#fff',
            borderWidth: 2,
            hoverOffset: 4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                position: 'bottom',
                labels: {
                    padding: 20,
                    usePointStyle: true,
                    font: {
                        size: 12
                    }
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = context.parsed;
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((value / total) * 100).toFixed(1);
                        return label + ': ' + value + ' events (' + percentage + '%)';
                    }
                }
            }
        }
    }
});

  function exportChart() {
      const link = document.createElement('a');
      link.download = 'merit-growth-chart.png';
      link.href = statisticsChart.toBase64Image();
      link.click();
  }

  function exportParticipationChart() {
      const link = document.createElement('a');
      link.download = 'participation-chart.png';
      link.href = participationChart.toBase64Image();
      link.click();
  }

  function refreshParticipationChart() {
      location.reload();
  }

window.onload = function () {
  const qrText = document.getElementById("studentQR").getAttribute("data-qrtext");
  new QRCode(document.getElementById("studentQR"), {
    text: qrText,
    width: 100,
    height: 100
  });
};

function downloadStudentQR() {
  const canvas = document.querySelector('#studentQR canvas');
  if (!canvas) return alert("QR code not ready yet.");
  const link = document.createElement('a');
  link.href = canvas.toDataURL("image/png");
  link.download = "student_qr.png";
  link.click();
}

function copyLink(link) {
  navigator.clipboard.writeText(link).then(() => {
    alert("Link copied to clipboard");
  });
}
  </script>

  </body>
  </html>