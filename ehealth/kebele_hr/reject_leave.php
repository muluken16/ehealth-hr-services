<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../db.php';
$conn = getDBConnection();

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['leave_id'])) {
    echo json_encode(['success' => false, 'message' => 'Leave ID required']);
    exit;
}

$leave_id = $data['leave_id'];
$reason = $data['reason'] ?? 'Rejected by HR';
$admin_id = $_SESSION['user_id'];

// Update leave request status
$stmt = $conn->prepare("UPDATE leave_requests SET status = 'rejected', rejected_reason = ?, approved_by = ?, approved_at = NOW() WHERE id = ?");
$stmt->bind_param("sii", $reason, $admin_id, $leave_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}

$conn->close();
?>