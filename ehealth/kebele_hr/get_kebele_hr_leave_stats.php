<?php
session_start();
require_once dirname(__DIR__) . '/db.php';
$conn = getDBConnection();

$kebele = $_SESSION['kebele'] ?? 'Kebele 1';

// Get leave requests by month for employees in this kebele
$result = $conn->query("SELECT DATE_FORMAT(lr.created_at, '%b %Y') as month, COUNT(*) as count 
                       FROM leave_requests lr 
                       JOIN employees e ON lr.employee_id = e.employee_id 
                       WHERE e.working_kebele = '$kebele' 
                       GROUP BY DATE_FORMAT(lr.created_at, '%Y-%m') 
                       ORDER BY lr.created_at ASC");

$stats = [];
while ($row = $result->fetch_assoc()) {
    $stats[] = $row;
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($stats);
?>