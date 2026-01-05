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

if (!$data || !isset($data['leave_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit();
}

$leave_id = $data['leave_id'];
$approved_by = $_SESSION['user_id'];

$sql = "UPDATE leave_requests SET status = 'rejected', approved_by = ?, approved_at = NOW() WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $approved_by, $leave_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}

$stmt->close();
$conn->close();
?>