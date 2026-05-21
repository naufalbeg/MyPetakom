<?php
session_start();
include '../../Databased/db_connect.php';

// Authentication check
if (!isset($_SESSION['username'], $_SESSION['userRole']) || $_SESSION['userRole'] !== 'advisor') {
    header("Location: ../Module_1/Login.php");
    exit;
}

// Handle form submissions for approve/reject
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $application_id = $_POST['application_id'] ?? 0;
    $action = $_POST['action'] ?? '';
    
    if ($application_id && ($action === 'approve' || $action === 'reject')) {
        $status = ($action === 'approve') ? 'approved' : 'rejected';
        
        $stmt = $conn->prepare("UPDATE meritapplication SET claim_status = ? WHERE application_id = ?");
        $stmt->bind_param("si", $status, $application_id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Claim successfully " . $status;
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = "Failed to update claim status";
            $_SESSION['message_type'] = 'error';
        }
        
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Get filters from URL
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? 'pending';
$level = $_GET['level'] ?? '';

// Build WHERE clause for filters
$where_conditions = [];
$params = [];
$types = '';

if (!empty($search)) {
    $where_conditions[] = "(s.student_name LIKE ? OR e.title LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param]);
    $types .= 'ss';
}

if ($status !== 'all') {
    $where_conditions[] = "ma.claim_status = ?";
    $params[] = $status;
    $types .= 's';
}

if (!empty($level)) {
    $where_conditions[] = "e.event_level = ?";
    $params[] = $level;
    $types .= 's';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get claims with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Fetch claims
$claims_sql = "SELECT ma.*, e.title as event_name, e.event_level, 
                      s.student_name, s.student_id_card
               FROM meritapplication ma
               JOIN events e ON ma.event_id = e.event_id
               JOIN student s ON ma.user_id = s.user_id
               $where_clause
               ORDER BY ma.submission_date DESC
               LIMIT ? OFFSET ?";

$stmt = $conn->prepare($claims_sql);
$params[] = $per_page;
$params[] = $offset;
$types .= 'ii';

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$claims = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get total for pagination
$count_sql = "SELECT COUNT(*) as total
              FROM meritapplication ma
              JOIN events e ON ma.event_id = e.event_id
              JOIN student s ON ma.user_id = s.user_id
              $where_clause";

$count_stmt = $conn->prepare($count_sql);
if (!empty($params) && count($params) > 2) {
    $count_params = array_slice($params, 0, -2);
    $count_types = substr($types, 0, -2);
    $count_stmt->bind_param($count_types, ...$count_params);
}

$count_stmt->execute();
$total_claims = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_claims / $per_page);

// Get statistics
$stats = $conn->query("SELECT 
    COUNT(CASE WHEN claim_status = 'pending' THEN 1 END) as pending_claims,
    COUNT(CASE WHEN claim_status = 'approved' THEN 1 END) as approved_claims,
    COUNT(CASE WHEN claim_status = 'rejected' THEN 1 END) as rejected_claims,
    COUNT(*) as total_claims
FROM meritapplication")->fetch_assoc();

$page_title = "Manage Merit Claims";
include '../HADER_SIDER_FOOTER/HST.PHP';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Merit Claims - Advisor Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../CSS/MODULE_4_css/manage_claim_advisor.css">
</head>
<body>
    <div class="main-content">
        <div class="page-inner">
            <h2><i class="fas fa-tasks"></i> Manage Merit Claims</h2>
            <p><b>Review and manage student merit claims</b><br>
               Process pending claims and view claim history</p>
            
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?= $_SESSION['message_type'] ?>">
                    <?= $_SESSION['message'] ?>
                </div>
                <?php 
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
                ?>
            <?php endif; ?>
        </div>

        <!-- Statistics Cards -->

        <div class="summary-stats">
            <div class="stat-card" style="background: #14519c">
                <div class="stat-number"><?= $stats['total_claims'] ?></div>
                <div class="stat-label">Total Claims</div>
            </div>
            <div class="stat-card pending" style="background: #14519c">
                <div class="stat-number"><?= $stats['pending_claims'] ?></div>
                <div class="stat-label">Pending Review</div>
            </div>
            <div class="stat-card approved" style="background: #14519c">
                <div class="stat-number"><?= $stats['approved_claims'] ?></div>
                <div class="stat-label">Approved</div>
            </div>
            <div class="stat-card rejected" style="background: #14519c">
                <div class="stat-number"><?= $stats['rejected_claims'] ?></div>
                <div class="stat-label">Rejected</div>
            </div>
        </div>


        <!-- Filters -->
             <div class="filters-section">
                    <form method="GET" class="filter-form">
                        <input type="text" name="search" placeholder="Search claims..." 
                            value="<?= htmlspecialchars($search) ?>" class="search-input">
                        
                        <select name="status" class="status-select">
                            <option value="pending" <?= $status==='pending'?'selected':'' ?>>Pending</option>
                            <option value="approved" <?= $status==='approved'?'selected':'' ?>>Approved</option>
                            <option value="rejected" <?= $status==='rejected'?'selected':'' ?>>Rejected</option>
                            <option value="all" <?= $status==='all'?'selected':'' ?>>All Status</option>
                        </select>
                        
                        <select name="level" class="level-select">
                            <option value="">All Levels</option>
           
                            <option value="International" <?= $level==='International'?'selected':'' ?>>International</option>
                            <option value="National" <?= $level==='National'?'selected':'' ?>>National</option>
                            <option value="State" <?= $level==='State'?'selected':'' ?>>State</option>
                            <option value="District" <?= $level==='District'?'selected':'' ?>>District</option>
                            <option value="UMPSA" <?= $level==='UMPSA'?'selected':'' ?>>UMPSA</option>
                        </select>
                        
                        <button type="submit" class="apply-filters">Apply Filters</button>
                    </form>
                </div>
        <!-- Claims Table -->
        <div class="table-container">
            <?php if (count($claims) > 0): ?>
                <table class="claims-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Event</th>
                            <th>Level</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($claims as $claim): ?>
                            <tr>
                                <td>
                                    <div class="student-info">
                                        <div class="student-name">
                                            <?= htmlspecialchars($claim['student_name']) ?>
                                        </div>
                                        <div class="student-details">
                                            <?= htmlspecialchars($claim['student_id_card']) ?>
                                        </div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($claim['event_name']) ?></td>
                                <td>
                                    <span class="level-badge level-<?= strtolower($claim['event_level']) ?>">
                                        <?= htmlspecialchars($claim['event_level']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= $claim['claim_status'] ?>">
                                        <i class="fas fa-<?= $claim['claim_status'] === 'pending' ? 'clock' : 
                                                         ($claim['claim_status'] === 'approved' ? 'check-circle' : 'times-circle') ?>">
                                        </i>
                                        <?= ucfirst($claim['claim_status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($claim['claim_status'] === 'pending'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="application_id" value="<?= $claim['application_id'] ?>">
                                            
                                            <button type="submit" name="action" value="approve" class="btn-approve"
                                                    onclick="return confirm('Are you sure you want to approve this claim?')">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                            
                                            <button type="submit" name="action" value="reject" class="btn-reject"
                                                    onclick="return confirm('Are you sure you want to reject this claim?')">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <div class="pagination-info">
                            Showing <?= ($offset + 1) ?> to <?= min($offset + $per_page, $total_claims) ?> 
                            of <?= $total_claims ?> claims
                        </div>
                        <div class="pagination-buttons">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>&level=<?= urlencode($level) ?>" 
                                   class="btn-page <?= $i === $page ? 'active' : '' ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-clipboard-list"></i>
                    <h3>No Claims Found</h3>
                    <p>No merit claims match your current filters.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2025 MyPetakom System. All rights reserved. | Advisor Dashboard</p>
    </footer>
</body>
</html>
<?php
$conn->close();
?>