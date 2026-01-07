<?php
session_start();
require_once dirname(__DIR__) . '/db.php';
$conn = getDBConnection();

$kebele = $_SESSION['kebele'] ?? 'Kebele 1';

// Get leave requests for employees in this kebele
$result = $conn->query("SELECT lr.*, e.first_name, e.last_name, e.department_assigned as department 
                       FROM leave_requests lr 
                       JOIN employees e ON lr.employee_id = e.employee_id 
                       WHERE e.working_kebele = '$kebele' AND lr.status = 'pending' 
                       ORDER BY lr.created_at DESC");

$leave_requests = [];
while ($row = $result->fetch_assoc()) {
    $leave_requests[] = $row;
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($leave_requests);
?>