<?php
// VIEW_AWARDED.PHP
// (put this at the very top of your file)

session_start();
include '../../Databased/db_connect.php';

// 1) Auth + look up real user_id
if (
    !isset($_SESSION['username'], $_SESSION['userRole']) ||
    $_SESSION['userRole'] !== 'student'
) {
    header("Location: ../Module_1/Login.php");
    exit;
}
$stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows !== 1) {
    header("Location: ../Module_1/Login.php");
    exit;
}
$user_id = $res->fetch_assoc()['user_id'];
$stmt->close();

// 2) Include the inser_merit script — this must populate:
//    - $events_data  : an *unfiltered* array of all attended events,
//    - $total_points : sum of all of them,
//    - $current_year_points,
//    - $current_year,
// and also insert new merit rows into `merit` with IGNORE so you don’t double‐insert.
include 'inser_merit.php';

// 3) Snapshot the full, unfiltered list
$full_events = $events_data;

// 4) Build dropdown lists from full data
$all_years        = array_unique(array_column($full_events, 'academic_year'));
rsort($all_years);
$all_event_levels = array_unique(array_column($full_events, 'event_level'));
sort($all_event_levels);

// 5) Read the GET filters
$selected_year        = $_GET['academic_year']    ?? '';
$selected_semester    = $_GET['semester']         ?? '';
$min_points           = $_GET['min_points']       ?? '';
$selected_event_level = $_GET['event_level']      ?? '';
$search               = trim($_GET['search']      ?? '');

// 6) Apply filters
$events_data          = [];
$total_points         = 0;
$current_year_points  = 0;
foreach ($full_events as $e) {
    if ($selected_year        !== '' && $e['academic_year'] != $selected_year)        continue;
    if ($selected_semester    !== '' && $e['semester']      != $selected_semester)     continue;
    if ($min_points           !== '' && $e['points']        <  (int)$min_points)      continue;
    if ($selected_event_level !== '' && $e['event_level']   != $selected_event_level) continue;
    if ($search               !== '' && stripos($e['event_name'], $search) === false) continue;

    // passes all filters
    $events_data[]       = $e;
    $total_points       += $e['points'];
    if ($e['academic_year'] === (int)$current_year) {
        $current_year_points += $e['points'];
    }
}

// 7) Render header/sidebar
$page_title = "View Awarded Merits";
include '../HADER_SIDER_FOOTER/HST.PHP';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View Awarded Merits – Student Dashboard</title>
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../CSS/MODULE_4_css/VIEW_AWARDED.css">
</head>
<body>
  <div class="main-content">
    <div class="page-inner">
      <h2><i class="fas fa-medal"></i> View Awarded Merits</h2>
      <p><b>Track your earned merits and achievements</b><br>
         Review all merits awarded through your event participation</p>

    </div>

    <!-- SUMMARY STATS -->
    <div class="summary-stats">
      <div class="stat-card" style="background: #14519c">
        <div class="stat-number"><?= $total_points ?></div>
        <div class="stat-label">Total Merits Earned</div>
      </div>
      <div class="stat-card" style="background: #14519c">
        <div class="stat-number"><?= $current_year_points ?></div>
        <div class="stat-label">Current Year Points</div>
      </div>
      <div class="stat-card" style="background: #14519c">
        <div class="stat-number"><?= count($events_data) ?></div>
        <div class="stat-label">Events Attended</div>
      </div>
    </div>

    <!-- FILTERS -->
    <div class="filter-section">
      <h4><i class="fas fa-filter"></i> Filter Merits</h4>
      <form method="GET">
        <div class="filter-row">
          <!-- Academic Year -->
          <div class="filter-group">
            <label for="academic_year">Academic Year</label>
            <select name="academic_year" id="academic_year">
              <option value="">All Years</option>
              <?php foreach ($all_years as $yr): ?>
              <option value="<?= $yr ?>" <?= $selected_year==$yr?'selected':'' ?>><?= $yr ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <!-- Semester -->
          <div class="filter-group">
            <label for="semester">Semester</label>
            <select name="semester" id="semester">
              <option value="">All Semesters</option>
              <option value="1" <?= $selected_semester==='1'?'selected':'' ?>>Semester 1</option>
              <option value="2" <?= $selected_semester==='2'?'selected':'' ?>>Semester 2</option>
            </select>
          </div>
          <!-- Event Level -->
          <div class="filter-group">
            <label for="event_level">Event Level</label>
            <select name="event_level" id="event_level">
              <option value="">All Levels</option>
              <?php foreach ($all_event_levels as $lvl): ?>
              <option value="<?= htmlspecialchars($lvl) ?>" <?= $selected_event_level===$lvl?'selected':'' ?>>
                <?= htmlspecialchars($lvl) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <!-- Minimum Points -->
          <div class="filter-group">
            <label for="min_points">Minimum Points</label>
            <input type="number" name="min_points" id="min_points" placeholder="0"
                   value="<?= htmlspecialchars($min_points) ?>">
          </div>
          <!-- Search Box -->
          <div class="filter-group" style="flex:1;min-width:200px;">
            <label for="search">Search Events</label>
            <input type="text" name="search" id="search" placeholder="e.g. Hackathon"
                   value="<?= htmlspecialchars($search) ?>">
          </div>
          <!-- Apply -->
          <div class="filter-group" style="justify-content:flex-end">
            <button type="submit" class="btn-filter">
              <i class="fas fa-search"></i> Apply Filters
            </button>
          </div>
        </div>
      </form>
    </div>

    <!-- MERITS GRID -->
    <?php if (count($events_data) > 0): ?>
      <div class="merits-grid">
        <?php foreach ($events_data as $e): ?>
          <div class="merit-card" data-merit='<?= htmlspecialchars(json_encode($e)) ?>'>
            <div class="merit-header">
              <div class="merit-title"><?= htmlspecialchars($e['event_name']) ?></div>
              <div class="merit-points">+<?= $e['points'] ?> pts</div>
            </div>
            <div class="merit-meta">
              <div class="merit-semester"><?= $e['academic_year'] ?> – Sem <?= $e['semester'] ?></div>
              <div class="merit-date">
                <i class="fas fa-calendar-alt"></i>
                <?= date('M d, Y',strtotime($e['start_date'])) ?>
                <?= $e['end_date']!==$e['start_date'] ? '– '.date('M d, Y',strtotime($e['end_date'])) : '' ?>
              </div>
              <div class="merit-location">
                <i class="fas fa-map-marker-alt"></i>
                <?= htmlspecialchars($e['geographic_location']) ?>
              </div>
            </div>
            <div class="merit-actions">
              <a href="merit_details.php?event_id=<?= $e['event_id'] ?>" class="btn-details">
                <i class="fas fa-info-circle"></i> View Details
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="empty-state">
        <i class="fas fa-search"></i>
        <h3>No merits found</h3>
        <p>
          <?= ($selected_year||$selected_semester||$min_points||$selected_event_level||$search)
             ? 'Try adjusting your filter or search terms.'
             : "You haven't earned any merits yet—participate in events to start earning!" ?>
        </p>
      </div>
    <?php endif; ?>
  </div>

  <footer class="footer">
    <p>&copy; 2025 MyPetakom System. All rights reserved. | UMP Student Dashboard</p>
  </footer>

  <script>
    function exportToCSV(){
      const rows = [
        ['Event','Role','Points','Year','Sem','Location','Start','End'],
        ...window.meritData.map(e=>[
          e.event_name,e.role,e.points,
          e.academic_year,e.semester,
          e.geographic_location,
          e.start_date,e.end_date
        ])
      ];
      const csv = rows.map(r=>r.map(f=>`"${f}"`).join(',')).join('\n');
      const blob= new Blob([csv],{type:'text/csv'});
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `awarded_merits_${new Date().toISOString().slice(0,10)}.csv`;
      document.body.appendChild(a);
      a.click();
      a.remove();
      URL.revokeObjectURL(url);
    }
    // expose for console/download
    window.meritData        = <?= json_encode($events_data) ?>;
    window.totalMerits      = <?= $total_points ?>;
    window.currentYearPoints= <?= $current_year_points ?>;
  </script>
</body>
</html>
<?php
$conn->close();
