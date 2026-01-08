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
$user_kebele = $_SESSION['kebele'] ?? 'Kebele 1';

// Start transaction
$conn->begin_transaction();

try {
    // 1. Get leave details and verify kebele
    $sql = "SELECT lr.employee_id, lr.leave_type, lr.days_requested, e.kebele 
            FROM leave_requests lr
            JOIN employees e ON lr.employee_id = e.employee_id
            WHERE lr.id = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $leave_id);
    $stmt->execute();
    $leave = $stmt->get_result()->fetch_assoc();

    if (!$leave) {
        throw new Exception("Leave request not found");
    }

    // Strict Kebele Check
    if ($leave['kebele'] !== $user_kebele) {
        throw new Exception("Unauthorized: You can only approve leaves for your Kebele.");
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
    // Sanitize column name to be safe, though $type comes from enum/db
    $allowed_types = ['annual', 'sick', 'maternity', 'paternity', 'emergency'];
    if (!in_array($type, $allowed_types)) {
        // Fallback or skip balance update if type is weird, but technically shouldn't happen
    } else {
        $sql = "UPDATE employees SET $balance_col = $balance_col + ?, status = 'on-leave' WHERE employee_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $days, $emp_id);
        $stmt->execute();
    }

    // Commit
    $conn->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>