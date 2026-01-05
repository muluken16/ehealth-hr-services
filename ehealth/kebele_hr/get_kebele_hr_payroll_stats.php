<?php
include dirname(__DIR__) . '/db.php';

$conn = getDBConnection();

// Assume kebele for demo
$kebele = 'Kebele 01';

// Get payroll statistics for employees in this kebele
$result = $conn->query("SELECT
    COUNT(*) as total_employees,
    SUM(COALESCE(salary, 0)) as total_payroll,
    SUM(COALESCE(salary, 0) * 0.1) as total_allowances,
    SUM(COALESCE(salary, 0) * 0.05) as total_deductions
FROM employees WHERE kebele = '$kebele'");

$stats = $result->fetch_assoc();

$conn->close();

header('Content-Type: application/json');
echo json_encode($stats);
?>