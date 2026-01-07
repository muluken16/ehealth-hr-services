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
$admin_id = $_SESSION['user_id'];

// Start transaction
$conn->begin_transaction();

try {
    // 1. Get leave details
    $stmt = $conn->prepare("SELECT employee_id, leave_type, days_requested FROM leave_requests WHERE id = ?");
    $stmt->bind_param("i", $leave_id);
    $stmt->execute();
    $leave = $stmt->get_result()->fetch_assoc();

    if (!$leave) {
        throw new Exception("Leave request not found");
    }

    $emp_id = $leave['employee_id'];
    $type = $leave['leave_type'];
    $days = $leave['days_requested'];

    // 2. Update leave request status
    $stmt = $conn->prepare("UPDATE leave_requests SET status = 'approved', approved_by = ?, approved_at = NOW() WHERE id = ?");
    $stmt->bind_param("ii", $admin_id, $leave_id);
    $stmt->execute();

    // 3. Update employee balance and status
    $balance_col = $type . "_used";
    $sql = "UPDATE employees SET $balance_col = $balance_col + ?, status = 'on-leave' WHERE employee_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $days, $emp_id);
    $stmt->execute();

    // Commit
    $conn->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>