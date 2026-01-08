<?php
session_start();
include dirname(__DIR__) . '/db.php';

$conn = getDBConnection();

// Assume kebele for demo
$kebele = isset($_SESSION['kebele']) ? $_SESSION['kebele'] : 'Kebele 01';

// Get date request or default to today
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// 1. Get total employees in kebele
$total_stmt = $conn->prepare("SELECT COUNT(*) FROM employees WHERE kebele = ?");
$total_stmt->bind_param("s", $kebele);
$total_stmt->execute();
$total_stmt->bind_result($total_employees);
$total_stmt->fetch();
$total_stmt->close();

// 2. Get Present and Late counts
$att_stmt = $conn->prepare("SELECT
    COUNT(CASE WHEN a.status = 'present' THEN 1 END) as present_today,
    COUNT(CASE WHEN a.status = 'late' THEN 1 END) as late_today
FROM attendance a
JOIN employees e ON a.employee_id = e.employee_id
WHERE DATE(a.attendance_date) = ? AND e.kebele = ?");
$att_stmt->bind_param("ss", $date, $kebele);
$att_stmt->execute();
$att_stmt->bind_result($present, $late);
$att_stmt->fetch();
$att_stmt->close();

// 3. Get On Leave count
$leave_stmt = $conn->prepare("SELECT COUNT(*) FROM leave_requests lr
    JOIN employees e ON lr.employee_id = e.employee_id
    WHERE ? BETWEEN lr.start_date AND lr.end_date
    AND lr.status = 'approved' AND e.kebele = ?");
$leave_stmt->bind_param("ss", $date, $kebele);
$leave_stmt->execute();
$leave_stmt->bind_result($on_leave);
$leave_stmt->fetch();
$leave_stmt->close();

// 4. Calculate Absent
// Note: If someone is marked 'absent' explicitly in attendance, they are counted there.
// But we want implicit absent too.
// However, the above query only counted present and late.
// If we have explicit 'absent' records, we should probably include them in 'absent_today'.
// Let's refine step 2 to include explicit absent if any.
// But simpler: Absent = Total - (Present + Late + On Leave).
// This assumes no overlap (e.g. someone on leave but marked present).
// If overlap, priority usually goes to Present. But let's keep it simple.

$absent = $total_employees - ($present + $late + $on_leave);
if ($absent < 0) $absent = 0; // Just in case data is weird

$stats = [
    'present_today' => $present,
    'late_today' => $late,
    'on_leave' => $on_leave,
    'absent_today' => $absent
];

$conn->close();

header('Content-Type: application/json');
echo json_encode($stats);
?>