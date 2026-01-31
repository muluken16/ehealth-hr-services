<?php
session_start();
header('Content-Type: application/json');

require_once dirname(__DIR__) . '/db.php';
$conn = getDBConnection();

// Set error reporting for JSON responses
error_reporting(0);
ini_set('display_errors', 0);

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['employee_id']) || !isset($data['leave_type'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data provided or empty request body']);
    exit;
}

$employee_id = $conn->real_escape_string($data['employee_id']);
$leave_type = $conn->real_escape_string($data['leave_type']);
$start_date = $conn->real_escape_string($data['start_date']);
$end_date = $conn->real_escape_string($data['end_date']);
$days_requested = intval($data['days_requested']);
$reason = $conn->real_escape_string($data['reason']);
$requested_by = $_SESSION['user_id'] ?? 0;

// Set status to pending (or could be auto-approved if HR does it, but usually workflow requires tracking)
$status = 'pending';

$sql = "INSERT INTO leave_requests (employee_id, leave_type, start_date, end_date, days_requested, reason, status, requested_by) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssissi", $employee_id, $leave_type, $start_date, $end_date, $days_requested, $reason, $status, $requested_by);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Leave request submitted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>