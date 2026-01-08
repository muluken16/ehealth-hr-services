<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'zone_hr') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

include '../db.php';
$conn = getDBConnection();

$zone = $_SESSION['zone'];

$sql = "SELECT status, COUNT(*) as count FROM employees WHERE zone = ? GROUP BY status";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $zone);
$stmt->execute();
$result = $stmt->get_result();

$stats = [];
while ($row = $result->fetch_assoc()) {
    $stats[] = $row;
}

echo json_encode($stats);

$stmt->close();
$conn->close();
?>