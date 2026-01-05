<?php
include dirname(__DIR__) . '/db.php';

$conn = getDBConnection();

// Assume kebele for demo
$kebele = 'Kebele 01';

// Get today's date
$today = date('Y-m-d');

// Get attendance statistics for today
$result = $conn->query("SELECT
    COUNT(CASE WHEN status = 'present' THEN 1 END) as present_today,
    COUNT(CASE WHEN status = 'absent' THEN 1 END) as absent_today,
    COUNT(CASE WHEN status = 'late' THEN 1 END) as late_today
FROM attendance WHERE DATE(attendance_date) = '$today' AND kebele = '$kebele'");

// Get employees on leave today
$leave_result = $conn->query("SELECT COUNT(*) as on_leave FROM leave_requests lr
    JOIN employees e ON lr.employee_id = e.employee_id
    WHERE '$today' BETWEEN lr.start_date AND lr.end_date
    AND lr.status = 'approved' AND e.kebele = '$kebele'");

$stats = $result->fetch_assoc();
$leave_stats = $leave_result->fetch_assoc();

$stats['on_leave'] = $leave_stats['on_leave'];

$conn->close();

header('Content-Type: application/json');
echo json_encode($stats);
?>