<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'zone_hr') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

include '../db.php';
$conn = getDBConnection();

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit();
}

$created_by = $_SESSION['user_id'];

$sql = "INSERT INTO training_sessions (title, description, trainer, session_date, start_time, end_time, venue, max_participants, created_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssssii",
    $data['title'],
    $data['description'],
    $data['trainer'],
    $data['session_date'],
    $data['start_time'],
    $data['end_time'],
    $data['venue'],
    $data['max_participants'],
    $created_by
);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}

$stmt->close();
$conn->close();
?>