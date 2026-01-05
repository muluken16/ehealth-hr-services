<?php
include dirname(__DIR__) . '/db.php';

$conn = getDBConnection();

// Assume kebele for demo
$kebele = 'Kebele 01';

// Get leave requests by month for employees in this kebele
$result = $conn->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count FROM leave_requests lr JOIN employees e ON lr.employee_id = e.employee_id WHERE e.kebele = '$kebele' GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY month");

$stats = [];
while ($row = $result->fetch_assoc()) {
    $stats[] = $row;
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($stats);
?>