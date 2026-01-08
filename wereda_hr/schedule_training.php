<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'wereda_hr') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

include '../db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit();
}

$conn = getDBConnection();

$title = $data['title'];
$description = $data['description'];
$trainer = $data['trainer'];
$session_date = $data['session_date'];
$start_time = $data['start_time'];
$end_time = $data['end_time'];
$venue = $data['venue'];
$max_participants = $data['max_participants'] ?: 0;
$created_by = $_SESSION['user_id'];

$sql = "INSERT INTO training_sessions (title, description, trainer, session_date, start_time, end_time, venue, max_participants, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssssds", $title, $description, $trainer, $session_date, $start_time, $end_time, $venue, $max_participants, $created_by);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Training scheduled successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error scheduling training: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>