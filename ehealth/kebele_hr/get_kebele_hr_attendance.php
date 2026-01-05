<?php
include dirname(__DIR__) . '/db.php';

$conn = getDBConnection();

// Assume kebele for demo
$kebele = 'Kebele 01';

// Get today's date
$today = date('Y-m-d');

// Get attendance data for today
$result = $conn->query("SELECT
    e.employee_id,
    e.first_name,
    e.last_name,
    a.check_in,
    a.check_out,
    a.working_hours,
    a.status
FROM employees e
LEFT JOIN attendance a ON e.employee_id = a.employee_id AND DATE(a.attendance_date) = '$today'
WHERE e.kebele = '$kebele'
ORDER BY e.first_name, e.last_name");

$attendance_data = [];
while ($row = $result->fetch_assoc()) {
    // Set default status if no attendance record
    if (!$row['status']) {
        $row['status'] = 'absent';
        $row['check_in'] = null;
        $row['check_out'] = null;
        $row['working_hours'] = '0.00';
    }
    $attendance_data[] = $row;
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($attendance_data);
?>