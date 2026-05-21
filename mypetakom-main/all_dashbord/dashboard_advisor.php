<?php
include '../../Databased/db_connect.php';

// Fetch only upcoming events
$sql = "SELECT * FROM events WHERE start_date >= CURDATE() ORDER BY start_date ASC";
$result = $conn->query($sql);

// Fetch recent committees and join with events to show event name
$sql_committees = "SELECT ec.committee_id, ec.role, e.title AS event_title, u.name AS student_name
                   FROM eventcommittee ec
                   JOIN events e ON ec.event_id = e.event_id
                   JOIN users u ON ec.user_id = u.user_id
                   ORDER BY ec.committee_id DESC 
                   LIMIT 5";


$result_committees = $conn->query($sql_committees);

// Total events
$sql_total_events = "SELECT COUNT(*) AS total FROM events";
$total_events = $conn->query($sql_total_events)->fetch_assoc()['total'];

// Active events
$sql_active_events = "SELECT COUNT(*) AS total FROM events WHERE event_status = 'Active'";
$active_events = $conn->query($sql_active_events)->fetch_assoc()['total'];

// Postponed/Cancelled events
$sql_postponed_events = "SELECT COUNT(*) AS total FROM events WHERE event_status IN ('Postponed', 'Cancelled')";
$postponed_events = $conn->query($sql_postponed_events)->fetch_assoc()['total'];

// Total committees
$sql_total_committees = "SELECT COUNT(*) AS total FROM eventcommittee";
$total_committees = $conn->query($sql_total_committees)->fetch_assoc()['total'];

// Merit Applications
$sql_merit_applications = "SELECT COUNT(*) AS total FROM meritapplication";
$merit_applications = $conn->query($sql_merit_applications)->fetch_assoc()['total'];

// Event status for the chart
$chart_sql = "SELECT event_status, COUNT(*) as total FROM events GROUP BY event_status";
$chart_result = $conn->query($chart_sql);

$chart_labels = [];
$chart_data = [];

while ($row = $chart_result->fetch_assoc()) {
    $chart_labels[] = $row['event_status'];
    $chart_data[] = $row['total'];
}

// Events per month
$month_sql = "SELECT MONTH(start_date) AS month_number, DATE_FORMAT(start_date, '%M') AS month, COUNT(*) AS total
              FROM events
              WHERE YEAR(start_date) = YEAR(CURDATE())
              GROUP BY MONTH(start_date)
              ORDER BY MONTH(start_date)";


$month_result = $conn->query($month_sql);

$month_labels = [];
$month_data = [];

if ($month_result) {
    while ($row = $month_result->fetch_assoc()) {
        $month_labels[] = $row['month'];
        $month_data[] = $row['total'];
    }
} else {
    echo "Error in month SQL: " . $conn->error;
}


// Merit application status
$merit_sql = "SELECT claim_status, COUNT(*) AS total FROM meritapplication GROUP BY claim_status";
$merit_result = $conn->query($merit_sql);

$merit_labels = [];
$merit_data = [];

if ($merit_result) {
    while ($row = $merit_result->fetch_assoc()) {
        $merit_labels[] = $row['claim_status'];
        $merit_data[] = $row['total'];
    }
} else {
    echo "Error in merit SQL: " . $conn->error;
}
  include '../HADER_SIDER_FOOTER/HST.PHP';
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Event Advisor Dashboard</title>
  <link rel="stylesheet" href="../CSS/MODULE_2_css/styleadvisor.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
	<style>
    body {
        height: 55rem !important;
  overflow-y: auto !important;
  
    }
  </style>
</head>

<body>
  <div class="container">


    <main class="main-content">

      <section class="dashboard-header">
        <h2>Event Advisor Dashboard</h2>
        <button class="download-btn">Download Report</button>
      </section>

      <section class="stats">
		  <div class="card">Total Events<br><span><?php echo $total_events; ?></span></div>
		  <div class="card">Active Events<br><span><?php echo $active_events; ?></span></div>
		  <div class="card">Postponed/Cancelled<br><span><?php echo $postponed_events; ?></span></div>
		  <div class="card">Total Committees<br><span><?php echo $total_committees; ?></span></div>
		  <div class="card">Merit Application<br><span><?php echo $merit_applications; ?></span></div>
	  </section>


      <section class="content">
        <div class="charts">
          <h3>Charts/Graphs</h3>
          <div class="graph-box">
			  <canvas id="eventStatusChart" width="200" height="100"></canvas>
		  </div>
          <div class="graph-box">
			  <canvas id="meritStatusChart" width="200" height="100"></canvas>
		  </div>

          <div class="graph-box">
			  <canvas id="eventsPerMonthChart" width="200" height="100"></canvas>
		  </div>

        </div>

        <div class="short-list">
          <h3>Short List</h3>
          <div class="table-wrapper">
            <!-- Upcoming Events Section -->
			<h4>Upcoming Events</h4>
				<table>
					<thead>
						<tr>
							<th>No</th>
							<th>Event Name</th>
							<th>Status</th>
							<th>Start Date</th>
							<th>Location</th>
							
						</tr>
					</thead>
					<tbody>
						<?php
						if ($result && $result->num_rows > 0) {
							$no = 1;
							while ($row = $result->fetch_assoc()) {
								echo "<tr>";
								echo "<td>" . $no++ . "</td>";
								echo "<td>" . htmlspecialchars($row['title']) . "</td>";
								echo "<td class='" . strtolower($row['event_status']) . "-status'>" . htmlspecialchars($row['event_status']) . "</td>";
								echo "<td>" . htmlspecialchars($row['start_date']) . "</td>";
								echo "<td>" . htmlspecialchars($row['location']) . "</td>";
								
								echo "</tr>";
							}
						} else {
							echo "<tr><td colspan='8'>No upcoming events found.</td></tr>";
						}
						?>
					</tbody>
				</table>

			<!-- Committee List Section -->
			<div class="table-wrapper">
			<h4>Committee List</h4>
			<table>
				<thead>
					<tr>
						<th>No</th>
						<th>Name</th>
						<th>Role</th>
						<th>Event</th>
					</tr>
				</thead>
				<tbody>
					<?php
					if ($result_committees && $result_committees->num_rows > 0) {
						$no = 1;
						while ($row = $result_committees->fetch_assoc()) {
							echo "<tr>";
							echo "<td>" . $no++ . "</td>";
							echo "<td>" . htmlspecialchars($row['student_name']) . "</td>";
							echo "<td>" . htmlspecialchars($row['role']) . "</td>";
							echo "<td>" . htmlspecialchars($row['event_title']) . "</td>";
							echo "</tr>";
						}
					} else {
						echo "<tr><td colspan='4'>No committee data found.</td></tr>";
					}
					?>
				</tbody>
			</table>


          </div>
        </div>
      </section>
    </main>
  </div>
  
<script>
  // Event Status Bar Chart
  const ctx = document.getElementById('eventStatusChart').getContext('2d');
  const eventStatusChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: <?php echo json_encode($chart_labels); ?>,
      datasets: [{
        label: 'Number of Events',
        data: <?php echo json_encode($chart_data); ?>,
        backgroundColor: ['#4CAF50', '#FFC107', '#F44336', '#03A9F4', '#9E9E9E'],
        
		borderColor: '#ffffff',
        borderWidth: 3,
        borderRadius: 3,
        hoverBackgroundColor: ['#66BB6A', '#FFD54F', '#EF5350', '#29B6F6', '#BDBDBD'],
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false },
        tooltip: {
          mode: 'index',
          intersect: false,
          backgroundColor: '#333',
          titleColor: '#fff',
          bodyColor: '#fff',
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: { precision: 0 }
        }
      }
    }
  });

  // Events Per Month line Chart
  const ctxMonth = document.getElementById('eventsPerMonthChart').getContext('2d');
  const eventsPerMonthChart = new Chart(ctxMonth, {
    type: 'line',
    data: {
      labels: <?php echo json_encode($month_labels); ?>,
      datasets: [{
        label: 'Events Per Month',
        data: <?php echo json_encode($month_data); ?>,
        backgroundColor: 'rgba(33, 150, 243, 0.2)',
        borderColor: '#2196F3',
        borderWidth: 3,
        pointBackgroundColor: '#fff',
        pointBorderColor: '#2196F3',
        fill: true,
        tension: 0.,
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: true },
        tooltip: {
          backgroundColor: '#333',
          titleColor: '#fff',
          bodyColor: '#fff',
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: { precision: 0 }
        }
      }
    }
  });

  // Merit Status Pie Chart
  const ctxMerit = document.getElementById('meritStatusChart').getContext('2d');
  const meritStatusChart = new Chart(ctxMerit, {
    type: 'doughnut',
    data: {
      labels: <?php echo json_encode($merit_labels); ?>,
      datasets: [{
        label: 'Merit Applications',
        data: <?php echo json_encode($merit_data); ?>,
        backgroundColor: ['#8E24AA', '#43A047', '#FB8C00', '#E53935', '#3949AB'],
        borderColor: '#fff',
        borderWidth: 2,
        hoverOffset: 8
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: 'center',
          labels: {
            color: '#000',
            font: {
              size: 12,
              weight: 'bold'
            }
          }
        },
        tooltip: {
          backgroundColor: '#222',
          titleColor: '#fff',
          bodyColor: '#fff',
        }
      }
    }
  });
</script>


</body>
</html>
