<?php
include dirname(__DIR__) . '/db.php';
$conn = getDBConnection();
session_start();
// Ensure Kebele is set
if (!isset($_SESSION['kebele'])) {
    $_SESSION['kebele'] = 'Kebele 1';
}
$user_kebele = $_SESSION['kebele'];

// Get total employees for this kebele
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM employees WHERE working_kebele = ?");
$stmt->bind_param("s", $user_kebele);
$stmt->execute();
$total_employees = $stmt->get_result()->fetch_assoc()['total'];

// Open positions (Job postings usually don't have kebele, but maybe we filter by woreda/zone or show all? 
// Let's assume global for now or Woreda level. Let's keep it simple and show all open for now, 
// or if we had a kebele column in job_postings we would use it.)
$result = $conn->query("SELECT COUNT(*) as total FROM job_postings WHERE status = 'open'");
$open_positions = $result->fetch_assoc()['total'];

// Get employees on leave
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM employees WHERE status = 'on-leave' AND working_kebele = ?");
$stmt->bind_param("s", $user_kebele);
$stmt->execute();
$on_leave = $stmt->get_result()->fetch_assoc()['total'];

// Get pending leave requests
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM leave_requests lr JOIN employees e ON lr.employee_id = e.employee_id WHERE lr.status = 'pending' AND e.working_kebele = ?");
$stmt->bind_param("s", $user_kebele);
$stmt->execute();
$pending_leave = $stmt->get_result()->fetch_assoc()['total'];

// Attendance rate
if ($total_employees > 0) {
    $attendance_rate = (($total_employees - $on_leave) / $total_employees) * 100;
} else {
    $attendance_rate = 0;
}
$attendance_rate = number_format($attendance_rate, 1);

echo json_encode([
    'success' => true,
    'stats' => [
        'total_employees' => $total_employees,
        'open_positions' => $open_positions,
        'on_leave' => $on_leave,
        'pending_leave' => $pending_leave,
        'attendance_rate' => $attendance_rate
    ]
]);

$conn->close();
?>