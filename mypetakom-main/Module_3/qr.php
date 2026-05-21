<?php
// qr.php
if (!isset($_GET['data'])) {
    http_response_code(400);
    exit('Missing data');
}

$data = $_GET['data'];
$size = isset($_GET['size']) ? preg_replace('/[^0-9x]/', '', $_GET['size']) : '100x100';

// Fetch from QRServer
$apiUrl = "https://api.qrserver.com/v1/create-qr-code/?size={$size}&data=" . urlencode($data);
$img = @file_get_contents($apiUrl);
if ($img === false) {
    http_response_code(502);
    exit('Could not retrieve QR');
}

header('Content-Type: image/png');
if (isset($_GET['download'])) {
    // Force download
    header('Content-Disposition: attachment; filename="qr.png"');
}
echo $img;
