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
$admin_id = $_SESSION['user_id'];
$current_year = date('Y');

if ($leave_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid leave ID']);
    $conn->close();
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // 1. Get leave details
    $stmt = $conn->prepare("SELECT employee_id, leave_type, days_requested FROM leave_requests WHERE id = ?");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("i", $leave_id);
    $stmt->execute();
    $leave = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$leave) {
        throw new Exception("Leave request not found");
    }

    $emp_id = $leave['employee_id'];
    $type = $leave['leave_type'];
    $days = $leave['days_requested'];

    // 2. Update leave request status
    $stmt = $conn->prepare("UPDATE leave_requests SET status = 'approved', approved_by = ?, approved_at = NOW() WHERE id = ?");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("ii", $admin_id, $leave_id);
    $stmt->execute();
    $stmt->close();

    // 3. Update employee status to on-leave
    $stmt = $conn->prepare("UPDATE employees SET status = 'on-leave' WHERE employee_id = ?");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("s", $emp_id);
    $stmt->execute();
    $stmt->close();

    // 4. Update leave entitlement balance
    $valid_types = ['annual', 'sick', 'maternity', 'paternity', 'emergency'];
    if (!in_array($type, $valid_types)) {
        throw new Exception("Invalid leave type: " . $type);
    }
    // Map leave type to correct column name (e.g., 'sick' -> 'used_sick_leave')
    $column_map = [
        'annual' => 'used_annual_leave',
        'sick' => 'used_sick_leave',
        'maternity' => 'used_maternity_leave',
        'paternity' => 'used_paternity_leave',
        'emergency' => 'used_emergency_leave'
    ];
    $used_column = $column_map[$type];
    $stmt = $conn->prepare("UPDATE leave_entitlements SET $used_column = $used_column + ? WHERE employee_id = ? AND year = ?");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("isi", $days, $emp_id, $current_year);
    $stmt->execute();
    $stmt->close();

    // 5. Get employee details for the response
    $stmt = $conn->prepare("SELECT employee_id, first_name, last_name, status FROM employees WHERE employee_id = ?");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("s", $emp_id);
    $stmt->execute();
    $employee = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // 6. Get leave request details including end date
    $stmt = $conn->prepare("SELECT start_date, end_date, leave_type, days_requested FROM leave_requests WHERE id = ?");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("i", $leave_id);
    $stmt->execute();
    $leave_details = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Commit
    $conn->commit();
    echo json_encode([
        'success' => true,
        'message' => 'Leave request approved successfully',
        'employee' => [
            'employee_id' => $employee['employee_id'],
            'first_name' => $employee['first_name'],
            'last_name' => $employee['last_name'],
            'status' => $employee['status'],
            'leave_type' => $leave_details['leave_type'],
            'start_date' => $leave_details['start_date'],
            'end_date' => $leave_details['end_date'],
            'days_requested' => $leave_details['days_requested']
        ]
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
exit;
