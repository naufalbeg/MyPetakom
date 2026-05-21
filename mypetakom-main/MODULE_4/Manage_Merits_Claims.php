<?php
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

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'submit_claim':
                handleClaimSubmission($conn, $user_id);
                break;
            case 'update_claim':
                handleClaimUpdate($conn, $user_id);
                break;
            case 'delete_claim':
                handleClaimDeletion($conn, $user_id);
                break;
        }
    }
}

function handleClaimSubmission($conn, $user_id) {
    try {
        $event_id = $_POST['event_id'];
        $supporting_document = '';

        // File upload
        if (isset($_FILES['supporting_document']) && $_FILES['supporting_document']['error'] === 0) {
            $upload_dir = '../../uploads/merit_claims/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $allowed_types = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
            $file_extension = strtolower(pathinfo($_FILES['supporting_document']['name'], PATHINFO_EXTENSION));
            if (!in_array($file_extension, $allowed_types)) {
                throw new Exception("Invalid file type. Only PDF, DOC, DOCX, JPG, PNG allowed.");
            }

            if ($_FILES['supporting_document']['size'] > 5 * 1024 * 1024) {
                throw new Exception("File too large. Max 5MB allowed.");
            }

            $file_name = 'claim_' . $user_id . '_' . $event_id . '_' . time() . '.' . $file_extension;
            $file_path = $upload_dir . $file_name;
            if (move_uploaded_file($_FILES['supporting_document']['tmp_name'], $file_path)) {
                $supporting_document = $file_name;
            } else {
                throw new Exception("Upload failed.");
            }
        } else {
            throw new Exception("Supporting document is required.");
        }
///////
///////

///

        // // Prevent duplicate
        // $check_sql = "SELECT application_id FROM meritapplication WHERE event_id = ? AND user_id = ?";
        // $check_stmt = $conn->prepare($check_sql);
        // $check_stmt->bind_param("ii", $event_id, $user_id);
        // $check_stmt->execute();
        // $check_result = $check_stmt->get_result();
        // if ($check_result->num_rows > 0) {
        //     throw new Exception("You already submitted a claim for this event.");
        // }

        ///
        ///
        ////
        ////

        // Insert new claim
        $sql = "INSERT INTO meritapplication (event_id, user_id, claim_status, submission_date, supporting_document) 
                VALUES (?, ?, 'pending', CURDATE(), ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $event_id, $user_id, $supporting_document);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Claim submitted successfully!";
        } else {
            throw new Exception("Database insert failed.");
        }

    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        if (isset($file_path) && file_exists($file_path)) {
            unlink($file_path);
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

function handleClaimUpdate($conn, $user_id) {
    try {
        $application_id = $_POST['application_id'];
        $supporting_document = $_POST['current_document'];

        $verify_sql = "SELECT supporting_document FROM meritapplication 
                       WHERE application_id = ? AND user_id = ? AND claim_status = 'pending'";
        $verify_stmt = $conn->prepare($verify_sql);
        $verify_stmt->bind_param("ii", $application_id, $user_id);
        $verify_stmt->execute();
        $verify_result = $verify_stmt->get_result();
        if ($verify_result->num_rows === 0) {
            throw new Exception("Claim not editable.");
        }

        $current_claim = $verify_result->fetch_assoc();
        if (isset($_FILES['supporting_document']) && $_FILES['supporting_document']['error'] === 0) {
            $upload_dir = '../../uploads/merit_claims/';
            if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);

            $file_extension = strtolower(pathinfo($_FILES['supporting_document']['name'], PATHINFO_EXTENSION));
            $allowed_types = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
            if (!in_array($file_extension, $allowed_types)) {
                throw new Exception("Invalid file type.");
            }

            if ($_FILES['supporting_document']['size'] > 5 * 1024 * 1024) {
                throw new Exception("File too large.");
            }

            $file_name = 'claim_' . $user_id . '_' . $application_id . '_' . time() . '.' . $file_extension;
            $file_path = $upload_dir . $file_name;
            if (move_uploaded_file($_FILES['supporting_document']['tmp_name'], $file_path)) {
                if (!empty($current_claim['supporting_document'])) {
                    $old_file = $upload_dir . $current_claim['supporting_document'];
                    if (file_exists($old_file)) unlink($old_file);
                }
                $supporting_document = $file_name;
            } else {
                throw new Exception("Upload failed.");
            }
        }

        $sql = "UPDATE meritapplication SET supporting_document = ?, submission_date = CURDATE() 
                WHERE application_id = ? AND user_id = ? AND claim_status = 'pending'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sii", $supporting_document, $application_id, $user_id);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Claim updated!";
        } else {
            throw new Exception("Update failed.");
        }

    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        if (isset($file_path) && file_exists($file_path)) unlink($file_path);
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

function handleClaimDeletion($conn, $user_id) {
    try {
        $application_id = $_POST['application_id'];
        
        // Get document name and verify ownership before deletion
        $doc_sql = "SELECT supporting_document FROM meritapplication 
                    WHERE application_id = ? AND user_id = ? AND claim_status = 'pending'";
        $doc_stmt = $conn->prepare($doc_sql);
        $doc_stmt->bind_param("ii", $application_id, $user_id);
        $doc_stmt->execute();
        $doc_result = $doc_stmt->get_result();
        
        if ($doc_result->num_rows === 0) {
            throw new Exception("Claim not found or not deletable.");
        }
        
        $doc_row = $doc_result->fetch_assoc();
        
        // Delete merit application
        $sql = "DELETE FROM meritapplication WHERE application_id = ? AND user_id = ? AND claim_status = 'pending'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $application_id, $user_id);
        
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            // Delete associated file
            if (!empty($doc_row['supporting_document'])) {
                $file_path = '../../uploads/merit_claims/' . $doc_row['supporting_document'];
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }
            $_SESSION['success'] = "Merit claim deleted successfully!";
        } else {
            throw new Exception("Failed to delete merit claim.");
        }
        
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Get existing merit claims with better error handling
try {
    $claims_sql = "SELECT ma.*, e.title as event_name, e.start_date, e.end_date, 
                          e.geographic_location, e.event_level
                   FROM meritapplication ma
                   JOIN events e ON ma.event_id = e.event_id
                   WHERE ma.user_id = ?
                   ORDER BY ma.submission_date DESC";

    $claims_stmt = $conn->prepare($claims_sql);
    $claims_stmt->bind_param("i", $user_id);
    $claims_stmt->execute();
    $claims_result = $claims_stmt->get_result();
    $existing_claims = [];
    while ($row = $claims_result->fetch_assoc()) {
        $existing_claims[] = $row;
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Error loading claims: " . $e->getMessage();
    $existing_claims = [];
}

// Get available events with better filtering
try {
$available_events_sql = "SELECT e.event_id, e.title, e.start_date, e.end_date, 
                                e.geographic_location, e.event_level
                         FROM events e
                         WHERE e.event_id NOT IN (
                             SELECT event_id FROM meritapplication WHERE user_id = ?
                         )
                         ORDER BY e.start_date DESC";

$available_stmt = $conn->prepare($available_events_sql);
$available_stmt->bind_param("i", $user_id);

    $available_stmt->execute();
    $available_result = $available_stmt->get_result();
    $available_events = [];
    while ($row = $available_result->fetch_assoc()) {
        $available_events[] = $row;
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Error loading available events: " . $e->getMessage();
    $available_events = [];
}

// Get statistics with error handling
try {
    $stats_sql = "SELECT 
                    COUNT(CASE WHEN claim_status = 'pending' THEN 1 END) as pending_claims,
                    COUNT(CASE WHEN claim_status = 'approved' THEN 1 END) as approved_claims,
                    COUNT(CASE WHEN claim_status = 'rejected' THEN 1 END) as rejected_claims,
                    COUNT(*) as total_claims
                  FROM meritapplication WHERE user_id = ?";

    $stats_stmt = $conn->prepare($stats_sql);
    $stats_stmt->bind_param("i", $user_id);
    $stats_stmt->execute();
    $stats_result = $stats_stmt->get_result();
    $stats = $stats_result->fetch_assoc();
} catch (Exception $e) {
    $stats = ['pending_claims' => 0, 'approved_claims' => 0, 'rejected_claims' => 0, 'total_claims' => 0];
}

// Set page title for header
$page_title = "Manage Merit Claims";

// Include header and sidebar
include '../HADER_SIDER_FOOTER/HST.PHP';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Merit Claims – Student Dashboard</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../CSS/MODULE_4_css/Manage_Merits.css">

</head>
<body>
    <!-- Main Content -->
    <div class="main-content">
        <div class="page-inner">
            <h2><i class="fas fa-file-alt"></i> Manage Merit Claims</h2>
            <p><b>Submit and manage your merit claim applications</b><br>Claim missing merits for events you attended</p>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
        </div>



        <!-- Statistics Cards -->
        <div class="summary-stats">
            <div class="stat-card" style="background-color: #14519c;">
                <div class="stat-number"><?= $stats['total_claims'] ?></div>
                <div class="stat-label">Total Claims</div>
            </div>
            <div class="stat-card" style="background: #14519c;">
                <div class="stat-number"><?= $stats['pending_claims'] ?></div>
                <div class="stat-label">Pending Claims</div>
            </div>
            <div class="stat-card" style="background:  #14519c;">
                <div class="stat-number"><?= $stats['approved_claims'] ?></div>
                <div class="stat-label">Approved Claims</div>
            </div>
            <div class="stat-card" style="background:  #14519c;">
                <div class="stat-number"><?= $stats['rejected_claims'] ?></div>
                <div class="stat-label">Rejected Claims</div>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="tab-container">
            <div class="tab-nav">
                <button class="tab-btn active" data-tab="available">
                    <i class="fas fa-plus-circle"></i> Available to Claim (<?= count($available_events) ?>)
                </button>
                <button class="tab-btn" data-tab="submitted">
                    <i class="fas fa-list"></i> My Claims (<?= count($existing_claims) ?>)
                </button>
                
            </div>

            <!-- Available Events Tab -->
            <div class="tab-content active" id="available">
                <?php if (count($available_events) > 0): ?>
                    <div class="events-grid">
                        <?php foreach ($available_events as $event): ?>
                            <div class="event-card claimable">
                                <div class="event-header">
                                    <div class="event-title"><?= htmlspecialchars($event['title']) ?></div>
                                    <div class="event-level"><?= htmlspecialchars($event['event_level']) ?> Level</div>
                                </div>
                                
                                <div class="event-details">
                                    <?php if (!empty($event['geographic_location'])): ?>
                                        <div><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($event['geographic_location']) ?></div>
                                    <?php endif; ?>
                                    <div><i class="fas fa-calendar-alt"></i> 
                                        <?= date('M d, Y', strtotime($event['start_date'])) ?>
                                        <?php if ($event['start_date'] != $event['end_date']): ?>
                                            - <?= date('M d, Y', strtotime($event['end_date'])) ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="event-actions">
                                    <button class="btn-claim" onclick="openClaimModal(<?= $event['event_id'] ?>, '<?= htmlspecialchars($event['title'], ENT_QUOTES) ?>')">
                                        <i class="fas fa-plus"></i> Submit Claim
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-check-circle"></i>
                        <h3>No Events Available for Claims</h3>
                        <p>You don't have any events that you can claim merits for.<br>
                           Either you've already received merits or submitted claims for all attended events.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Submitted Claims Tab -->
            <div class="tab-content" id="submitted">
                <?php if (count($existing_claims) > 0): ?>
                    <div class="claims-grid">
                        <?php foreach ($existing_claims as $claim): ?>
                            <div class="claim-card <?= $claim['claim_status'] ?>">
                                <div class="claim-header">
                                    <div>
                                        <div class="claim-title"><?= htmlspecialchars($claim['event_name']) ?></div>
                                        <div class="claim-meta">
                                            <span class="claim-date">
                                                <i class="fas fa-calendar-alt"></i>
                                                Submitted: <?= date('M d, Y', strtotime($claim['submission_date'])) ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="claim-status-badge status-<?= $claim['claim_status'] ?>">
                                        <?php
                                        $status_icons = [
                                            'pending' => 'fas fa-clock',
                                            'approved' => 'fas fa-check-circle',
                                            'rejected' => 'fas fa-times-circle'
                                        ];
                                        ?>
                                        <i class="<?= $status_icons[$claim['claim_status']] ?>"></i>
                                        <?= ucfirst($claim['claim_status']) ?>
                                    </div>
                                </div>
                                
                                <div class="claim-details">
                                    <?php if (!empty($claim['geographic_location'])): ?>
                                        <div><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($claim['geographic_location']) ?></div>
                                    <?php endif; ?>
                                    <div><i class="fas fa-level-up-alt"></i> <?= htmlspecialchars($claim['event_level']) ?> Level</div>
                                    <?php if (!empty($claim['supporting_document'])): ?>
                                        <div>
                                            <i class="fas fa-paperclip"></i> 
                                            <a href="../../uploads/merit_claims/<?= htmlspecialchars($claim['supporting_document']) ?>" target="_blank">
                                                View Document
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if ($claim['claim_status'] === 'pending'): ?>
                                    <div class="claim-actions">
                                        <button class="btn-edit" onclick="openEditModal(<?= $claim['application_id'] ?>, '<?= htmlspecialchars($claim['event_name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($claim['supporting_document']) ?>')">
                                            <i class="fas fa-edit"></i> Update
                                        </button>
                                        <button class="btn-delete" onclick="confirmDelete(<?= $claim['application_id'] ?>, '<?= htmlspecialchars($claim['event_name'], ENT_QUOTES) ?>')">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-file-alt"></i>
                        <h3>No Claims Submitted Yet</h3>
                        <p>You haven't submitted any merit claims yet.<br>
                           Check the "Available to Claim" tab to submit your first claim.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Claim Submission Modal -->
    <div id="claimModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-plus-circle"></i> Submit Merit Claim</h3>
                <span class="close">&times;</span>
            </div>
            <form method="POST" enctype="multipart/form-data" id="claimForm">
                <input type="hidden" name="action" value="submit_claim">
                <input type="hidden" name="event_id" id="claim_event_id">
                
                <div class="form-group">
                    <label>Event Name</label>
                    <input type="text" id="claim_event_name" readonly>
                </div>
                
                <div class="form-group">
                    <label for="supporting_document">Supporting Document *</label>
                    <input type="file" name="supporting_document" id="supporting_document" 
                           accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                    <small>Upload official letter for event participation (PDF, DOC, DOCX, JPG, PNG - Max 5MB)</small>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="closeModal('claimModal')">Cancel</button>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-paper-plane"></i> Submit Claim
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Claim Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-edit"></i> Update Merit Claim</h3>
                <span class="close">&times;</span>
            </div>
            <form method="POST" enctype="multipart/form-data" id="editForm">
                <input type="hidden" name="action" value="update_claim">
                <input type="hidden" name="application_id" id="edit_application_id">
                <input type="hidden" name="current_document" id="edit_current_document">
                
                <div class="form-group">
                    <label>Event Name</label>
                    <input type="text" id="edit_event_name" readonly>
                </div>
                
                <div class="form-group">
                    <label for="edit_supporting_document">Supporting Document</label>
                    <input type="file" name="supporting_document" id="edit_supporting_document" 
                           accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                    <small>Upload new document to replace current one (PDF, DOC, DOCX, JPG, PNG - Max 5MB)</small>
                    <div id="current_document_info"></div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="closeModal('editModal')">Cancel</button>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i> Update Claim
                    </button>
                </div>
            </form>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2025 MyPetakom System. All rights reserved. | UMP Student Dashboard</p>
    </footer>

    <script >
        // Merit Claims JavaScript Functionality

document.addEventListener('DOMContentLoaded', function() {
    initializeTabs();
    initializeModals();
    initializeFileInputs();
});

// Tab functionality
function initializeTabs() {
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');
            
            // Remove active class from all tabs and contents
            tabBtns.forEach(b => b.classList.remove('active'));
            tabContents.forEach(c => c.classList.remove('active'));
            
            // Add active class to clicked tab and corresponding content
            this.classList.add('active');
            document.getElementById(targetTab).classList.add('active');
        });
    });
}

// Modal functionality
function initializeModals() {
    const modals = document.querySelectorAll('.modal');
    const closeButtons = document.querySelectorAll('.close');
    
    // Close modal when clicking the X button
    closeButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const modal = this.closest('.modal');
            closeModal(modal.id);
        });
    });
    
    // Close modal when clicking outside
    modals.forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal(this.id);
            }
        });
    });
    
    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            modals.forEach(modal => {
                if (modal.style.display === 'block') {
                    closeModal(modal.id);
                }
            });
        }
    });
}

// File input enhancements
function initializeFileInputs() {
    const fileInputs = document.querySelectorAll('input[type="file"]');
    
    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                // Validate file size (max 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('File size must be less than 5MB');
                    this.value = '';
                    return;
                }
                
                // Validate file type
                const allowedTypes = ['application/pdf', 'application/msword', 
                                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                    'image/jpeg', 'image/jpg', 'image/png'];
                
                if (!allowedTypes.includes(file.type)) {
                    alert('Please upload a valid file (PDF, DOC, DOCX, JPG, PNG)');
                    this.value = '';
                    return;
                }
                
                // Show file name
                const fileName = file.name;
                const fileInfo = this.parentNode.querySelector('.file-info');
                if (fileInfo) {
                    fileInfo.textContent = `Selected: ${fileName}`;
                }
            }
        });
    });
}

// Open claim submission modal
function openClaimModal(eventId, eventName) {
    document.getElementById('claim_event_id').value = eventId;
    document.getElementById('claim_event_name').value = eventName;
    document.getElementById('supporting_document').value = '';
    
    const modal = document.getElementById('claimModal');
    modal.style.display = 'block';
    
    // Focus on file input
    setTimeout(() => {
        document.getElementById('supporting_document').focus();
    }, 100);
}

// Open edit claim modal
function openEditModal(applicationId, eventName, currentDocument) {
    document.getElementById('edit_application_id').value = applicationId;
    document.getElementById('edit_event_name').value = eventName;
    document.getElementById('edit_current_document').value = currentDocument;
    document.getElementById('edit_supporting_document').value = '';
    
    // Show current document info
    const currentDocInfo = document.getElementById('current_document_info');
    if (currentDocument) {
        currentDocInfo.innerHTML = `
            <div style="display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-paperclip" style="color: var(--primary);"></i>
                <span>Current: ${currentDocument}</span>
                <a href="../../uploads/merit_claims/${currentDocument}" target="_blank" 
                   style="color: var(--primary); text-decoration: none; font-size: 0.8rem;">
                    <i class="fas fa-external-link-alt"></i> View
                </a>
            </div>
        `;
    } else {
        currentDocInfo.innerHTML = '<span style="color: var(--text-light);">No document uploaded</span>';
    }
    
    const modal = document.getElementById('editModal');
    modal.style.display = 'block';
}

// Close modal
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.style.display = 'none';
    
    // Reset forms
    const form = modal.querySelector('form');
    if (form) {
        form.reset();
    }
    
    // Clear any file info displays
    const fileInfos = modal.querySelectorAll('.file-info');
    fileInfos.forEach(info => info.textContent = '');
}

// Confirm delete action
function confirmDelete(applicationId, eventName) {
    const message = `Are you sure you want to delete the claim for "${eventName}"?\n\nThis action cannot be undone.`;
    
    if (confirm(message)) {
        // Create and submit delete form
        const form = document.createElement('form');
        form.method = 'POST';
        form.style.display = 'none';
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'delete_claim';
        
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'application_id';
        idInput.value = applicationId;
        
        form.appendChild(actionInput);
        form.appendChild(idInput);
        document.body.appendChild(form);
        
        form.submit();
    }
}

// Form validation
function validateClaimForm() {
    const form = document.getElementById('claimForm');
    const fileInput = document.getElementById('supporting_document');
    
    if (!fileInput.files.length) {
        alert('Please upload a supporting document');
        fileInput.focus();
        return false;
    }
    
    return true;
}

// Add form validation to claim form
document.getElementById('claimForm').addEventListener('submit', function(e) {
    if (!validateClaimForm()) {
        e.preventDefault();
    } else {
        // Show loading state
        const submitBtn = this.querySelector('.btn-submit');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
        submitBtn.disabled = true;
        
        // Re-enable after a delay (in case of errors)
        setTimeout(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }, 5000);
    }
});

// Add loading state to edit form
document.getElementById('editForm').addEventListener('submit', function(e) {
    const submitBtn = this.querySelector('.btn-submit');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
    submitBtn.disabled = true;
    
    // Re-enable after a delay (in case of errors)
    setTimeout(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }, 5000);
});

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {

            setTimeout(() => {
                if (alert.parentNode) {
                    alert.parentNode.removeChild(alert);
                }
            }, 300);
        }, 5000);
    });
});

// Add smooth scrolling to tab content
function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}


// File drag and drop functionality
function initializeDragDrop() {
    const fileInputs = document.querySelectorAll('input[type="file"]');
    
    fileInputs.forEach(input => {
        const container = input.parentNode;
        
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            container.addEventListener(eventName, preventDefaults, false);
        });
        
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        ['dragenter', 'dragover'].forEach(eventName => {
            container.addEventListener(eventName, highlight, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            container.addEventListener(eventName, unhighlight, false);
        });
        
        function highlight(e) {
            container.classList.add('drag-highlight');
        }
        
        function unhighlight(e) {
            container.classList.remove('drag-highlight');
        }
        
        container.addEventListener('drop', handleDrop, false);
        
        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            if (files.length > 0) {
                input.files = files;
                input.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }
    });
}

// Initialize drag and drop when page loads
document.addEventListener('DOMContentLoaded', initializeDragDrop);


document.head.appendChild(style);


    </script>


</body>
</html>

<?php
// Close prepared statements and connection
if (isset($claims_stmt)) $claims_stmt->close();
if (isset($available_stmt)) $available_stmt->close();
if (isset($stats_stmt)) $stats_stmt->close();
$conn->close();
?>