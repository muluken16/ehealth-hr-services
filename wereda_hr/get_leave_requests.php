<?php
session_start();
$woreda = $_SESSION['woreda'] ?? 'Woreda 1';

include dirname(__DIR__) . '/db.php';

$conn = getDBConnection();

$stmt = $conn->prepare("SELECT lr.id, lr.employee_id, lr.leave_type, lr.start_date, lr.end_date, lr.reason, lr.status, lr.created_at, e.first_name, e.last_name, e.department_assigned as department
        FROM leave_requests lr
        JOIN employees e ON lr.employee_id = e.employee_id
        WHERE lr.status = 'pending' AND e.woreda LIKE ?
        ORDER BY lr.created_at DESC");

$woreda_param = "%$woreda%";
$stmt->bind_param("s", $woreda_param);
$stmt->execute();
$result = $stmt->get_result();

$leave_requests = [];
while ($row = $result->fetch_assoc()) {
    $leave_requests[] = $row;
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($leave_requests);
?>