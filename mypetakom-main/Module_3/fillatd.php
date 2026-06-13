<?php
require_once '../Module_1/session_config.php';
requireLogin();
$eventId = $_GET['event_id'] ?? 0;

include '../HADER_SIDER_FOOTER/HST.PHP';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>MyPetakom – Fill Attendance</title>
  <link rel="stylesheet" href="fillatd.css">
</head>
<body>


  <!-- MAIN CONTENT -->
  <main class="main-content">
  <?php if (!empty($_SESSION['msg'])): ?>
    <div class="flash"><?= $_SESSION['msg']; unset($_SESSION['msg']); ?></div>
  <?php endif; ?>

    <form id="attendanceForm"
          action="saveatd.php"
          method="post"
          onsubmit="return validateAndSyncLocation()">
      <h2>Attendance Form</h2>

      <input type="hidden" name="event_id" value="<?= htmlspecialchars($eventId) ?>">

      <div class="field">
      <label for="student_id">Student ID Card (e.g. CB23063):</label>
        <input type="text" id="student_id" name="student_id" required>
      </div>

      <div class="field">
        <label for="matric_id">Matric ID:</label>
        <input type="text" id="matric_id" name="matric_id" required>
      </div>

      <div class="field">
        <label for="committee">Are you a Committee Member?</label>
        <select id="committee" name="committee" required>
            <option value="">-- Select --</option>
            <option value="Yes">Yes</option>
            <option value="No">No</option>
        </select>
      </div>

      <div class="field">
        <label for="status">Attendance Status:</label>
        <select id="status" name="status" required>
          <option value="">-- Select Status --</option>
          <option value="present">Present</option>
          <option value="absent">Absent</option>
        </select>
      </div>

      <div class="field">
        <label for="study_year">Year of Study:</label>
        <select id="study_year" name="study_year" required>
            <option value="">-- Select Year --</option>
            <option value="1">1st Year</option>
            <option value="2">2nd Year</option>
            <option value="3">3rd Year</option>
            <option value="4">4th Year</option>
        </select>
      </div>

      <div class="field">
        <label for="date">Date:</label>
        <input type="date" id="date" name="date" value="<?= date('Y-m-d') ?>" readonly>
      </div>

      <div class="field">
        <label for="time">Time:</label>
        <input type="time" id="time" name="time" value="<?= date('H:i') ?>" required>
      </div>

      <div class="buttons">
        <button type="button" class="btn cancel" onclick="window.history.back()">Cancel</button>
        <button type="submit" class="btn submit">Submit</button>
      </div>
    </form>
  </main>

</body>
</html>

