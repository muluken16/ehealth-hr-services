<?php
header('Content-Type: application/json');
$host = "localhost";
$user = "root";
$pass = "";

$conn = @new mysqli($host, $user, $pass);

if ($conn->connect_error) {
    echo json_encode([
        'success' => false,
        'status' => 'offline',
        'error' => $conn->connect_error,
        'check_time' => date('Y-m-d H:i:s')
    ]);
} else {
    echo json_encode([
        'success' => true,
        'status' => 'online',
        'version' => $conn->server_info,
        'check_time' => date('Y-m-d H:i:s')
    ]);
    $conn->close();
}
?>
