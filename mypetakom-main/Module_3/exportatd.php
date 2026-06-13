<?php
require_once '../Module_1/session_config.php';
requireLogin();
include '../Databased/db_connect.php';

$eventId = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;
$allEvents = ($eventId === 0);
$format  = isset($_GET['format'])   ? strtolower(trim($_GET['format'])) : 'csv';

// Fetch event name
$eventName = "Event";
$eventStmt = $conn->prepare("SELECT title FROM events WHERE event_id = ?");
$eventStmt->bind_param("i", $eventId);
$eventStmt->execute();
$eventStmt->bind_result($eventName);
$eventStmt->fetch();
$eventStmt->close();

// Fetch attendance records
if ($allEvents) {
    $stmt = $conn->prepare(
        "SELECT s.student_id_card,
                s.student_name,
                u.username,
                e.title as event_name,
                a.status_attd,
                a.timestamp
         FROM   attendance a
         JOIN   users   u ON a.user_id = u.user_id
         JOIN   student s ON u.user_id = s.user_id
         JOIN   events  e ON a.event_id = e.event_id
         ORDER  BY a.timestamp ASC"
    );
    $stmt->execute();
} else {
    $stmt = $conn->prepare(
        "SELECT s.student_id_card,
                s.student_name,
                u.username,
                e.title as event_name,
                a.status_attd,
                a.timestamp
         FROM   attendance a
         JOIN   users   u ON a.user_id = u.user_id
         JOIN   student s ON u.user_id = s.user_id
         JOIN   events  e ON a.event_id = e.event_id
         WHERE  a.event_id = ?
         ORDER  BY a.timestamp ASC"
    );
    $stmt->bind_param("i", $eventId);
    $stmt->execute();
}
$result = $stmt->get_result();

$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}
$stmt->close();
$conn->close();

$safeEventName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $eventName);

// ── CSV EXPORT ────────────────────────────────────────────────────────────
if ($format === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header("Content-Disposition: attachment; filename=\"attendance_{$safeEventName}.csv\"");

    $out = fopen('php://output', 'w');
    fputs($out, "\xEF\xBB\xBF"); // BOM for Excel UTF-8

    fputcsv($out, ['No', 'Student ID', 'Username', 'Student Name', 'Event', 'Status', 'Date', 'Time'], ',', '"', '\\');

    $i = 1;
    foreach ($rows as $row) {
        fputcsv($out, [
            $i++,
            $row['student_id_card'],
            $row['username'],
            $row['student_name'],
            $row['event_name'],
            $row['status_attd'] ?? 'N/A',
            date('Y-m-d', strtotime($row['timestamp'])),
            date('H:i:s', strtotime($row['timestamp'])),
        ], ',', '"', '\\');
    }
    fclose($out);
    exit;
}

// ── PDF EXPORT ────────────────────────────────────────────────────────────
if ($format === 'pdf') {
    $tableRows = '';
    $i = 1;
    foreach ($rows as $row) {
        $no       = $i++;
        $sid      = htmlspecialchars($row['student_id_card']);
        $username = htmlspecialchars($row['username']);
        $name     = htmlspecialchars($row['student_name']);
        $event    = htmlspecialchars($row['event_name'] ?? '-');
        $status   = htmlspecialchars($row['status_attd'] ?? 'N/A');
        $date     = date('d M Y', strtotime($row['timestamp']));
        $time     = date('h:i A', strtotime($row['timestamp']));

        $tableRows .= "
        <tr>
            <td>{$no}</td>
            <td>{$sid}</td>
            <td>{$username}</td>
            <td>{$name}</td>
            <td>{$event}</td>
            <td>{$status}</td>
            <td>{$date}</td>
            <td>{$time}</td>
        </tr>";
    }

    $generatedAt  = date('d M Y, h:i A');
    $safeTitle    = htmlspecialchars($eventName);
    $totalRecords = count($rows);

    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Attendance Report – {$safeTitle}</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: Arial, sans-serif; font-size: 12px; color: #222; padding: 20px; }
    .report-header { text-align: center; margin-bottom: 18px; border-bottom: 2px solid #002240; padding-bottom: 12px; }
    .report-header h1 { font-size: 18px; color: #002240; margin-bottom: 4px; }
    .report-header p  { font-size: 11px; color: #555; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th { background: #002240; color: #fff; padding: 6px 8px; text-align: left; font-size: 11px; }
    td { padding: 5px 8px; border-bottom: 1px solid #ddd; font-size: 11px; }
    tr:nth-child(even) td { background: #f2f6fb; }
    .report-footer { margin-top: 14px; font-size: 10px; color: #777; text-align: right; }
    @media print { button { display: none !important; } }
  </style>
</head>
<body>
  <div class="report-header">
    <h1>MyPetakom – Attendance Report</h1>
    <p><strong>Event:</strong> {$safeTitle} &nbsp;|&nbsp; <strong>Total Records:</strong> {$totalRecords} &nbsp;|&nbsp; <strong>Generated:</strong> {$generatedAt}</p>
  </div>
  <table>
    <thead>
    <tr>
        <th>No</th><th>Student ID</th><th>Username</th>
        <th>Student Name</th><th>Event</th><th>Status</th><th>Date</th><th>Time</th>
      </tr>
    </thead>
    <tbody>{$tableRows}</tbody>
  </table>
  <div class="report-footer">MyPetakom System &copy; UMPSA Faculty of Computing</div>
  <br>
  <div style="text-align:center;">
    <button onclick="window.print()" style="padding:8px 20px;background:#002240;color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:13px;">
      &#128438; Save as PDF (Print)
    </button>
    <button onclick="window.close()" style="padding:8px 20px;background:#888;color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:13px;margin-left:8px;">
      Close
    </button>
  </div>
  <script>setTimeout(() => window.print(), 600);</script>
</body>
</html>
HTML;
    exit;
}

http_response_code(400);
echo "Invalid format. Use ?format=csv or ?format=pdf";
