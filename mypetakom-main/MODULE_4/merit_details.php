<?php
// merit_details.php

session_start();
include '../../Databased/db_connect.php';

// 1) Ensure logged-in student
if (
    !isset($_SESSION['username'], $_SESSION['userRole']) ||
    $_SESSION['userRole'] !== 'student'
) {
    header("Location: ../Module_1/Login.php");
    exit;
}

// 2) Lookup real user_id
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

// 3) Grab & validate event_id
$event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;
if ($event_id < 1) {
    header("Location: VIEW_AWARDED.PHP");
    exit;
}

// 4) Fetch all details, only if attendance is “present”
$sql = "
  SELECT
    e.event_id,
    e.title,
    e.description,
    e.start_date,
    e.end_date,
    e.geographic_location,
    e.event_status,
    e.qrcode_event,
    e.approval_letter,
    e.event_level,
    u.username           AS organizer_name,
    m.points             AS merit_points,
    m.academic_year,
    m.semester,
    a.timestamp          AS attendance_time
  FROM events e
  JOIN merit      m ON m.event_id = e.event_id
                   AND m.user_id    = ?
  JOIN attendance a ON a.event_id = e.event_id
                   AND a.user_id    = ?
                   AND a.status_attd= 'present'
  JOIN users      u ON u.user_id    = e.created_by
  WHERE e.event_id = ?
  LIMIT 1
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $user_id, $user_id, $event_id);
$stmt->execute();
$detail = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$detail) {
    echo "<p>Details not found for that event.</p>";
    echo '<p><a href="VIEW_AWARDED.PHP">« Back to Merits</a></p>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Merit Details – <?= htmlspecialchars($detail['title']) ?></title>
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../CSS/MODULE_4_css/VIEW_AWARDED.css">
  <style>
    .details-container { max-width:600px; margin:40px auto; padding:20px; background:#fff; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.1); }
    .details-row { margin-bottom:20px; }
    .details-row h4 { margin:0 0 6px; color:var(--text-dark); }
    .details-row p { margin:0; line-height:1.4; }
    .back-link { display:inline-block; margin-top:30px; text-decoration:none; color:var(--primary); }
    .qr-code { max-width:200px; display:block; margin-top:10px; }
    .letter-link { display:inline-block; margin-top:5px; }
  </style>
</head>
<body>

  <div class="details-container">
    <h2><i class="fas fa-info-circle"></i> <?= htmlspecialchars($detail['title']) ?></h2>

    <div class="details-row">
      <h4>Description</h4>
      <p><?= nl2br(htmlspecialchars($detail['description'] ?: 'No description provided.')) ?></p>
    </div>

    <div class="details-row">
      <h4>Status & Level</h4>
      <p>
        Status: <?= htmlspecialchars(ucfirst($detail['event_status'])) ?><br>
        Level:  <?= htmlspecialchars($detail['event_level']) ?>
      </p>
    </div>

    <div class="details-row">
      <h4>Location</h4>
      <p><?= htmlspecialchars($detail['geographic_location']) ?></p>
    </div>

    <div class="details-row">
      <h4>Dates</h4>
      <p>
        <?= date('M d, Y', strtotime($detail['start_date'])) ?>
        <?php if ($detail['end_date'] && $detail['end_date'] !== $detail['start_date']): ?>
          – <?= date('M d, Y', strtotime($detail['end_date'])) ?>
        <?php endif; ?>
      </p>
    </div>

    <div class="details-row">
      <h4>Merit Points</h4>
      <p>+<?= htmlspecialchars($detail['merit_points']) ?> pts</p>
    </div>

    <div class="details-row">
      <h4>Academic Year</h4>
      <p><?= htmlspecialchars($detail['academic_year']) ?></p>
    </div>

    <div class="details-row">
      <h4>Semester</h4>
      <p><?= htmlspecialchars($detail['semester']) ?></p>
    </div>

    <div class="details-row">
      <h4>Attendance Verified On</h4>
      <p><?= date('M d, Y H:i', strtotime($detail['attendance_time'])) ?></p>
    </div>

    <div class="details-row">
      <h4>Organized By</h4>
      <p><?= htmlspecialchars($detail['organizer_name']) ?></p>
    </div>

    <?php if ($detail['qrcode_event']): ?>
    <div class="details-row">
      <h4>Event QR Code</h4>
      <img src="../../uploads/qrcodes/<?= htmlspecialchars($detail['qrcode_event']) ?>"
           alt="QR Code" class="qr-code">
    </div>
    <?php endif; ?>

    <?php if ($detail['approval_letter']): ?>
    <div class="details-row">
      <h4>Approval Letter</h4>
      <a href="../../uploads/letters/<?= htmlspecialchars($detail['approval_letter']) ?>"
         target="_blank" class="letter-link">
        <i class="fas fa-file-pdf"></i> Download Letter
      </a>
    </div>
    <?php endif; ?>

    <a href="VIEW_AWARDED.PHP" class="back-link">
      <i class="fas fa-chevron-left"></i> Back to Merits
    </a>
  </div>

</body>
</html>
<?php
$conn->close();
?>
