<?php
error_reporting(0);
ini_set('display_errors', 0);

// Clean any existing output
while (ob_get_level()) {
    ob_end_clean();
}

header('Content-Type: application/json');
session_start();

// For demo purposes, set default user
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['user_name'] = 'Kebele HR Officer';
    $_SESSION['role'] = 'kebele_hr';
    $_SESSION['kebele'] = 'Kebele 1';
}

require_once '../db.php';
$conn = getDBConnection();

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['leave_id']) || empty($data['leave_id'])) {
    echo json_encode(['success' => false, 'message' => 'Leave ID is required']);
    $conn->close();
    exit;
}

$leave_id = intval($data['leave_id']);
$reason = isset($data['reason']) ? $data['reason'] : 'Rejected by HR';
$admin_id = $_SESSION['user_id'];

if ($leave_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid leave ID']);
    $conn->close();
    exit;
}

try {
    // Update leave request status
    $stmt = $conn->prepare("UPDATE leave_requests SET status = 'rejected', rejection_reason = ?, approved_by = ?, approved_at = NOW() WHERE id = ?");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("sii", $reason, $admin_id, $leave_id);

    if ($stmt->execute()) {
        $stmt->close();
        echo json_encode(['success' => true, 'message' => 'Leave request rejected']);
    } else {
        $stmt->close();
        throw new Exception("Execute failed: " . $conn->error);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
exit;
