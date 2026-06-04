<?php
session_start(); 
include '../../Databased/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $studentName = $_POST['studentName'] ?? '';
    $studentID = $_POST['studentID'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $program = $_POST['program'] ?? '';
    $year = $_POST['year'] ?? '';

    if (!$studentName || !$studentID || !$email || !$phone || !$program || !$year) {
        echo "<script>alert('Please fill all fields'); window.history.back();</script>";
        exit;
    }

    // File upload handling
    $uploadDir = "uploads/studentCards/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $studentCardPath = '';
    if (isset($_FILES['studentCard']) && $_FILES['studentCard']['error'] == 0) {
        $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
        $fileType = $_FILES['studentCard']['type'];
        $fileSize = $_FILES['studentCard']['size'];
        $fileName = basename($_FILES['studentCard']['name']);
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
        $newName = $studentID . "_" . time() . "." . $ext;

        if (!in_array($fileType, $allowedTypes)) {
            echo "<script>alert('Invalid file type. Only PDF, JPG, PNG allowed.'); window.history.back();</script>";
            exit;
        }

        if ($fileSize > 5 * 1024 * 1024) {
            echo "<script>alert('File too large. Max 5MB.'); window.history.back();</script>";
            exit;
        }

        $studentCardPath = $uploadDir . $newName;
        move_uploaded_file($_FILES['studentCard']['tmp_name'], $studentCardPath);
    } else {
        echo "<script>alert('Please upload your student card.'); window.history.back();</script>";
        exit;
    }

    // Get user_id from session
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
    $stmt->bind_param("s", $_SESSION['username']);
    $stmt->execute();
    $result = $stmt->get_result();

    $row = $result->fetch_assoc();
    $user_id = $row['user_id'];
    $faculty = "Faculty of Computing";
    $student_qr = "";

    // Check if student already exists (update or insert)
    $checkStudent = $conn->prepare("SELECT * FROM student WHERE user_id = ?");
    $checkStudent->bind_param("i", $user_id);
    $checkStudent->execute();
    $studentExists = $checkStudent->get_result();

    if ($studentExists->num_rows > 0) {
        // Update existing student record
        $stmt1 = $conn->prepare("UPDATE student SET student_name = ?, student_id_card = ?, program = ?, semester = ?, faculty = ?, student_qr = ? WHERE user_id = ?");
        $stmt1->bind_param("ssssssi", $studentName, $studentCardPath, $program, $year, $faculty, $student_qr, $user_id);
        $stmt1->execute();
        $stmt1->close();
    } else {
        // Insert into student table
        $stmt1 = $conn->prepare("INSERT INTO student (user_id, student_name, student_id_card, program, semester, faculty, student_qr) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt1->bind_param("issssss", $user_id, $studentName, $studentCardPath, $program, $year, $faculty, $student_qr);
        $stmt1->execute();
        $stmt1->close();
    }

    // Check if membership already exists
    $checkMembership = $conn->prepare("SELECT * FROM membership WHERE user_id = ?");
    $checkMembership->bind_param("i", $user_id);
    $checkMembership->execute();
    $membershipExists = $checkMembership->get_result();

    if ($membershipExists->num_rows > 0) {
        $existing = $membershipExists->fetch_assoc();
        if ($existing['status'] == 'pending') {
            echo "<script>alert('You already have a pending membership application. Please wait for admin approval.'); window.location='Student-MembershipApplication.php';</script>";
        } elseif ($existing['status'] == 'approved') {
            echo "<script>alert('Your membership is already approved!'); window.location='Student-MembershipApplication.php';</script>";
        } else {
            echo "<script>alert('Your membership application was rejected. Please contact admin.'); window.location='Student-MembershipApplication.php';</script>";
        }
        exit;
    }

    // Insert into membership table with ALL required columns
    $join_date = date("Y-m-d");
    $expiry_date = date("Y-m-d", strtotime('+1 year'));
    $status = "pending";
    $membershipType = "student";

    $stmt2 = $conn->prepare("INSERT INTO membership (user_id, membershipType, join_date, expiry_date, status) VALUES (?, ?, ?, ?, ?)");
    $stmt2->bind_param("issss", $user_id, $membershipType, $join_date, $expiry_date, $status);
    $stmt2->execute();
    $stmt2->close();

    echo "<script>alert('Membership application submitted successfully! Waiting for admin approval.'); window.location='Student-MembershipApplication.php';</script>";
    exit;
}

// Pass page title context variable to HST layout header
$page_title = "Petakom Membership Registration";

include '../HADER_SIDER_FOOTER/HST.PHP';
?>

<!-- Font Awesome Required for Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
  /* Base Dashboard Layout overrides to match HST frame */
  .main-container {
    margin-left: 280px; /* Aligned perfectly with HST sidebar width */
    padding: 40px 60px; /* Roomy side padding */
    background-color: #f8f9fa;
    min-height: calc(100vh - 90px);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    box-sizing: border-box;
  }

  /* Form Container Panel Design */
  .registration-card {
    background: #ffffff;
    border-radius: 16px;
    padding: 50px 60px;
    max-width: 1200px; /* Clean optimal layout width for inputs */
    width: 100%;
    margin: 10px auto;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04), 0 1px 8px rgba(0, 0, 0, 0.02);
    border: 1px solid #e9ecef;
    box-sizing: border-box;
  }

  .registration-card h1 {
    font-size: 2.2rem;
    font-weight: 700;
    color: #1a1d20;
    margin: 0 0 12px 0;
  }

  .form-subtitle {
    color: #6c757d;
    font-size: 1.1rem;
    margin-bottom: 40px;
    border-bottom: 1px solid #e9ecef;
    padding-bottom: 25px;
  }

  /* Structural Grid Layout for Inputs */
  .form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 24px 35px;
    margin-bottom: 35px;
  }

  .form-group {
    display: flex;
    flex-direction: column;
  }

  .form-group.full-width {
    grid-column: span 2;
  }

  .form-group label {
    font-size: 0.9rem;
    font-weight: 600;
    color: #495057;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 6px;
  }

  /* Input Fields Styling Rules */
  .form-group input[type="text"],
  .form-group input[type="email"],
  .form-group select {
    padding: 12px 16px;
    font-size: 1rem;
    border: 1px solid #ced4da;
    border-radius: 8px;
    background-color: #ffffff;
    color: #212529;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
    width: 100%;
    box-sizing: border-box;
    height: 48px;
  }

  .form-group input:focus,
  .form-group select:focus {
    outline: none;
    border-color: #228be6;
    box-shadow: 0 0 0 4px rgba(34, 139, 230, 0.12);
  }

  /* File Input Custom Box Design */
  .file-drop-zone {
    border: 2px dashed #ced4da;
    padding: 30px;
    border-radius: 8px;
    text-align: center;
    background-color: #f8f9fa;
    cursor: pointer;
    transition: background 0.2s ease, border-color 0.2s ease;
  }

  .file-drop-zone:hover {
    background-color: #e9ecef;
    border-color: #228be6;
  }

  .file-drop-zone i {
    font-size: 2rem;
    color: #adb5bd;
    margin-bottom: 10px;
  }

  /* Hidden native input, triggered via parent label click pointer */
  .file-drop-zone input[type="file"] {
    display: none;
  }

  /* Live Preview Element Design */
  .preview-container {
    margin-top: 15px;
    display: flex;
    flex-direction: column;
    align-items: center;
  }

  .preview-img {
    max-height: 180px;
    max-width: 100%;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  }

  .preview-text {
    font-size: 0.9rem;
    color: #495057;
    margin-top: 8px;
    font-style: italic;
  }

  /* Action Tray Layout Controls */
  .form-actions {
    border-top: 1px solid #e9ecef;
    padding-top: 30px;
    display: flex;
    gap: 15px;
    justify-content: flex-start;
  }

  .btn {
    padding: 14px 28px;
    font-size: 1rem;
    font-weight: 600;
    border-radius: 8px;
    cursor: pointer;
    border: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: background 0.2s ease, transform 0.1s ease;
  }

  .btn:active {
    transform: scale(0.98);
  }

  .btn-submit {
    background-color: #228be6;
    color: #ffffff;
  }

  .btn-submit:hover {
    background-color: #1c7ed6;
  }

  .btn-cancel {
    background-color: #f1f3f5;
    color: #495057;
  }

  .btn-cancel:hover {
    background-color: #e9ecef;
  }

  /* Responsive Fallbacks */
  @media (max-width: 992px) {
    .form-grid {
      grid-template-columns: 1fr;
      gap: 20px;
    }
    .form-group.full-width {
      grid-column: span 1;
    }
    .main-container {
      padding: 20px;
    }
    .registration-card {
      padding: 30px;
    }
  }
</style>

<div class="main-container">
  <div class="registration-card">
    <h1>Petakom Membership Registration</h1>
    <p class="form-subtitle">Fill in your academic metadata information profile and upload valid physical credentials to complete access configuration requests.</p>

    <form method="POST" enctype="multipart/form-data">
      <div class="form-grid">
        
        <div class="form-group">
          <label><i class="fa-regular fa-user"></i> Full Name:</label>
          <input type="text" name="studentName" placeholder="Enter your full name as in IC" required>
        </div>

        <div class="form-group">
          <label><i class="fa-regular fa-id-card"></i> Student ID:</label>
          <input type="text" name="studentID" placeholder="e.g. CB21000" required>
        </div>

        <div class="form-group">
          <label><i class="fa-regular fa-envelope"></i> Email Address:</label>
          <input type="email" name="email" placeholder="student@ump.edu.my" required>
        </div>

        <div class="form-group">
          <label><i class="fa-solid fa-phone"></i> Contact Number:</label>
          <input type="text" name="phone" placeholder="e.g. 0123456789" required>
        </div>

        <div class="form-group">
          <label><i class="fa-solid fa-graduation-cap"></i> Program Course:</label>
          <select name="program" required>
            <option value="" disabled selected>-- Select Program --</option>
            <option value="BCS">BCS (Software Engineering)</option>
            <option value="BCG">BCG (Graphics & Multimedia)</option>
            <option value="BCN">BCN (Computer Systems & Networking)</option>
            <option value="BCY">BCY (Cyber Security)</option>
            <option value="DRC">DRC (Diploma in Computer Science)</option>
          </select>
        </div>

        <div class="form-group">
          <label><i class="fa-regular fa-calendar-days"></i> Academic Year:</label>
          <select name="year" required>
            <option value="" disabled selected>-- Select Current Year --</option>
            <option value="1">Year 1</option>
            <option value="2">Year 2</option>
            <option value="3">Year 3</option>
            <option value="4">Year 4</option>
          </select>
        </div>

        <div class="form-group full-width">
          <label><i class="fa-regular fa-file-image"></i> Upload Student Matric Card Identity Verification:</label>
          <label class="file-drop-zone">
            <i class="fa-solid fa-cloud-arrow-up"></i>
            <div><strong>Click to upload</strong> or drag and drop files here</div>
            <div style="font-size: 0.8rem; color:#868e96; margin-top: 4px;">PDF, JPG, or PNG structural images (Max size: 5MB)</div>
            <input type="file" name="studentCard" id="studentCard" accept=".pdf,.jpg,.jpeg,.png" required>
          </label>
          
          <div class="preview-container" id="previewContainer">
            <img id="previewImg" class="preview-img" style="display:none;" alt="Matric card asset preview register" />
            <p id="previewFile" class="preview-text"></p>
          </div>
        </div>

      </div>

      <div class="form-actions">
        <button type="submit" class="btn btn-submit">
          <i class="fa-regular fa-paper-plane"></i> Submit Application
        </button>
        <button type="button" class="btn btn-cancel" onclick="resetForm()">
          <i class="fa-solid fa-rotate-left"></i> Cancel / Clear
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  function resetForm() {
    document.querySelector('form').reset();
    document.getElementById('previewImg').style.display = 'none';
    document.getElementById('previewImg').src = '';
    document.getElementById('previewFile').textContent = '';
  }

  document.getElementById('studentCard').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const previewImg = document.getElementById('previewImg');
    const previewFile = document.getElementById('previewFile');

    if (!file) return;

    if (file.type.startsWith('image/')) {
      const reader = new FileReader();
      reader.onload = function(evt) {
        previewImg.src = evt.target.result;
        previewImg.style.display = 'block';
        previewFile.textContent = 'Selected Image: ' + file.name + ' (' + (file.size / (1024 * 1024)).toFixed(2) + ' MB)';
      };
      reader.readAsDataURL(file);
    } else {
      previewImg.style.display = 'none';
      previewImg.src = '';
      previewFile.textContent = '📄 Selected Document Payload: ' + file.name;
    }
  });
  
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
</body>
</html>