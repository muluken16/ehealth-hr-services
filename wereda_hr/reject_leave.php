<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'wereda_hr') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once '../db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['leave_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit();
}

try {
    $conn = getDBConnection();
    $leave_id = $data['leave_id'];
    $approved_by = $_SESSION['user_id'] ?? $_SESSION['user_name'];
    $rejection_reason = $data['reason'] ?? 'Manual rejection by HR';

    // Update leave request status
    $stmt = $conn->prepare("UPDATE leave_requests SET status = 'rejected', approved_by = ?, approved_at = NOW(), rejection_reason = ? WHERE id = ?");
    $stmt->bind_param("ssi", $approved_by, $rejection_reason, $leave_id);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => '❌ Leave request rejected',
            'reason' => $rejection_reason
        ]);
    } else {
        throw new Exception('Error rejecting leave: ' . $stmt->error);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'System error: ' . $e->getMessage()
    ]);
}

$conn->close();