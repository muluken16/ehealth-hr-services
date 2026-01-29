<?php
session_start();
include dirname(__DIR__) . '/db.php';

$conn = getDBConnection();

// Assume kebele for demo
$kebele = isset($_SESSION['kebele']) ? $_SESSION['kebele'] : 'Kebele 01';

// Get date from request or default to today
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Get attendance data for the specific date
$stmt = $conn->prepare("SELECT
    e.employee_id,
    e.first_name,
    e.last_name,
    a.check_in,
    a.check_out,
    a.working_hours,
    a.status
FROM employees e
LEFT JOIN attendance a ON e.employee_id = a.employee_id AND DATE(a.attendance_date) = ?
WHERE e.kebele = ?
ORDER BY e.first_name, e.last_name");

$stmt->bind_param("ss", $date, $kebele);
$stmt->execute();
$result = $stmt->get_result();

$attendance_data = [];
$leave_stmt = $conn->prepare("SELECT COUNT(*) FROM leave_requests WHERE employee_id = ? AND ? BETWEEN start_date AND end_date AND status = 'approved'");

while ($row = $result->fetch_assoc()) {
    // Check if on leave if no status
    if (!$row['status']) {
        // Check leave
        $emp_id = $row['employee_id'];
        $leave_stmt->bind_param("ss", $emp_id, $date);
        $leave_stmt->execute();
        $leave_stmt->bind_result($is_on_leave);
        $leave_stmt->fetch();
        
        if ($is_on_leave > 0) {
            $row['status'] = 'on-leave';
            $row['working_hours'] = '0.00';
        } else {
            $row['status'] = 'absent';
            $row['check_in'] = null;
            $row['check_out'] = null;
            $row['working_hours'] = '0.00';
        }
    }
    $attendance_data[] = $row;
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($attendance_data);
?>