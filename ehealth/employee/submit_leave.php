<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['employee_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../db.php';
$conn = getDBConnection();

$emp_id = $_SESSION['employee_id'];
$type = $_POST['leave_type'];
$start = $_POST['start_date'];
$end = $_POST['end_date'];
$reason = $_POST['reason'] ?? '';

// 1. Validation: Dates in future
if(strtotime($start) < strtotime(date('Y-m-d'))) {
    echo json_encode(['success' => false, 'message' => 'Start date cannot be in the past']);
    exit;
}
if(strtotime($end) < strtotime($start)) {
    echo json_encode(['success' => false, 'message' => 'End date must be after start date']);
    exit;
}

// 2. Calculate requested days
$diff = strtotime($end) - strtotime($start);
$days = ceil($diff / (60 * 60 * 24)) + 1;

// 3. Check balance
$stmt = $conn->prepare("SELECT annual_entitlement, annual_used, sick_entitlement, sick_used, maternity_entitlement, maternity_used, paternity_entitlement, paternity_used, emergency_entitlement, emergency_used FROM employees WHERE employee_id = ?");
$stmt->bind_param("s", $emp_id);
$stmt->execute();
$emp = $stmt->get_result()->fetch_assoc();

$balance_col_ent = $type . "_entitlement";
$balance_col_used = $type . "_used";
$remaining = $emp[$balance_col_ent] - $emp[$balance_col_used];

if($days > $remaining) {
    echo json_encode(['success' => false, 'message' => "Insufficient $type leave balance. Requested $days, remaining $remaining."]);
    exit;
}

// 4. Check for conflicts
$stmt = $conn->prepare("SELECT id FROM leave_requests WHERE employee_id = ? AND status = 'pending' AND ((start_date <= ? AND end_date >= ?) OR (start_date <= ? AND end_date >= ?))");
$stmt->bind_param("sssss", $emp_id, $start, $start, $end, $end);
$stmt->execute();
if($stmt->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'You already have a pending leave request for these dates.']);
    exit;
}

// 5. Handle Attachment
$attachment_name = null;
if(isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0) {
    $upload_dir = '../uploads/leave_requests/';
    if(!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
    
    $ext = pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);
    $attachment_name = $emp_id . '_' . time() . '.' . $ext;
    move_uploaded_file($_FILES['attachment']['tmp_name'], $upload_dir . $attachment_name);
}

// 6. Insert Request
$stmt = $conn->prepare("INSERT INTO leave_requests (employee_id, leave_type, start_date, end_date, days_requested, reason, attachment, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
$stmt->bind_param("ssssiss", $emp_id, $type, $start, $end, $days, $reason, $attachment_name);

if($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
}
$conn->close();
?>
