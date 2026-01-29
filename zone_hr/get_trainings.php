<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'zone_hr') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

include '../db.php';
$conn = getDBConnection();

// For zone HR, get all training sessions
$sql = "SELECT * FROM training_sessions ORDER BY session_date DESC";
$result = $conn->query($sql);

$trainings = [];
while ($row = $result->fetch_assoc()) {
    $trainings[] = $row;
}

echo json_encode($trainings);

$conn->close();
?>