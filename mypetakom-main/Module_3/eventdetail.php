<?php
include '../Databased/db_connect.php';

if (!isset($_GET['data'])) {
  http_response_code(400);
  echo json_encode(['error' => 'No QR data provided']);
  exit;
}

$data = $_GET['data'];

// Expecting QR string like "ID:<id>;..."
if (!preg_match('/ID:(\d+)/', $data, $m)) {
  http_response_code(400);
  echo json_encode(['error' => 'Invalid QR data']);
  exit;
}

$id = intval($m[1]);

$stmt = $conn->prepare("
  SELECT
    id, attendance_name, event_name, status,
    start_time, end_time, location_name,
    latitude, longitude
  FROM attendance
  WHERE id = ?
");
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();

if ($row = $res->fetch_assoc()) {
  echo json_encode($row);
} else {
  http_response_code(404);
  echo json_encode(['error' => 'Event not found']);
}
exit;
?>
