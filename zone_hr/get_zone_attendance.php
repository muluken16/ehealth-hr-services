<?php
header('Content-Type: application/json');
session_start();
require_once '../db.php';
$conn = getDBConnection();
$date = $_GET['date'] ?? date('Y-m-d');
$zone = $_SESSION['zone'] ?? 'Zone 1';

$sql = "SELECT e.first_name, e.last_name, e.woreda, e.kebele, a.check_in, a.check_out, a.status 
        FROM employees e 
        LEFT JOIN attendance a ON e.employee_id = a.employee_id AND a.date = '$date'
        WHERE e.zone = '$zone'
        LIMIT 20";

$res = $conn->query($sql);
$records = [];
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $records[] = [
            'name' => $row['first_name'] . ' ' . $row['last_name'],
            'woreda' => $row['woreda'],
            'kebele' => $row['kebele'],
            'check_in' => $row['check_in'],
            'check_out' => $row['check_out'],
            'status' => $row['status'] ?? 'Absent'
        ];
    }
}

echo json_encode(['success' => true, 'records' => $records]);
