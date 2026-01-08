<?php
include dirname(__DIR__) . '/db.php';

$conn = getDBConnection();

// Assume kebele for demo
$kebele = 'Kebele 1';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['kebele'])) {
    $kebele = $_SESSION['kebele'];
}

// Get payroll statistics for employees in this kebele
$sql = "SELECT
            SUM(p.net_salary) as total_payroll,
            COUNT(DISTINCT p.employee_id) as processed_employees
        FROM payroll p
        JOIN employees e ON p.employee_id = e.employee_id
        WHERE e.kebele = ? AND p.status = 'processed'
        AND MONTH(p.period_end) = MONTH(CURRENT_DATE())
        AND YEAR(p.period_end) = YEAR(CURRENT_DATE())";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $kebele);
$stmt->execute();
$result = $stmt->get_result();

$stats = $result->fetch_assoc();

$conn->close();

header('Content-Type: application/json');
echo json_encode($stats);
?>