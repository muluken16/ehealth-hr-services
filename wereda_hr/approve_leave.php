<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'wereda_hr') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

include '../db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['leave_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit();
}

$conn = getDBConnection();

$leave_id = $data['leave_id'];
$approved_by = $_SESSION['user_id'];

$sql = "UPDATE leave_requests SET status = 'approved', approved_by = ?, approved_at = NOW() WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $approved_by, $leave_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Leave request approved']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error approving leave: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>