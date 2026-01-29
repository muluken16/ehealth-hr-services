<?php
include dirname(__DIR__) . '/db.php';

$conn = getDBConnection();

// Assume kebele for demo
$kebele = 'Kebele 01';

// Get payroll data for employees in this kebele
$result = $conn->query("SELECT
    e.employee_id,
    e.first_name,
    e.last_name,
    e.salary as basic_salary,
    (e.salary * 0.1) as allowances,
    (e.salary * 0.05) as deductions,
    (e.salary * 1.05) as net_salary,
    'processed' as payroll_status
FROM employees e
WHERE e.kebele = '$kebele'
ORDER BY e.first_name, e.last_name");

$payroll_data = [];
while ($row = $result->fetch_assoc()) {
    $payroll_data[] = $row;
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($payroll_data);
?>