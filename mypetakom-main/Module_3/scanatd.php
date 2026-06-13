<?php
require_once '../Module_1/session_config.php';
requireLogin();
include '../HADER_SIDER_FOOTER/HST.PHP';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>MyPetakom System – Scan Attendance</title>
  <link rel="stylesheet" href="scanatd.css">
</head>
<body>

  <!-- MAIN -->
  <main class="main-content">
    <h2>Scan Attendance</h2>

    <div class="qr-area">
      <img id="qrPreview" src="image/qr-frame.png" alt="QR Frame" class="qr-frame">
      <form id="qrForm" enctype="multipart/form-data">
        <input type="file" id="qrInput" name="qr_file" accept="image/*" required style="display:none;" onchange="previewQR()">
        <button type="button" onclick="document.getElementById('qrInput').click()">Upload</button>
        <button type="submit">Continue</button>
      </form>
    </div>
  </main>

  <script>
    function previewQR() {
      const file = document.getElementById('qrInput').files[0];
      const reader = new FileReader();
      reader.onload = function (e) {
        document.getElementById('qrPreview').src = e.target.result;
      }
      reader.readAsDataURL(file);
    }

    document.getElementById('qrForm').addEventListener('submit', function (e) {
  e.preventDefault();
  // Example event_id = 1, in real case this should come from the QR
  window.location.href = "fillatd.php?event_id=1";
});

  </script>

</body>
</html>
