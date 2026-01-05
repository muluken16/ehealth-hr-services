<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'wereda_hr') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

include '../db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit();
}

$conn = getDBConnection();
$processed = 0;
$errors = [];

foreach ($data as $payroll) {
    $employee_id = $payroll['employee_id'];
    $period_start = $payroll['period_start'];
    $period_end = $payroll['period_end'];
    $basic_salary = $payroll['basic_salary'];
    $allowances = $payroll['allowances'];
    $deductions = $payroll['deductions'];
    $net_salary = $payroll['net_salary'];
    $processed_by = $_SESSION['user_id'];

    // Check if payroll already exists for this period
    $check_sql = "SELECT id FROM payroll WHERE employee_id = ? AND period_start = ? AND period_end = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("sss", $employee_id, $period_start, $period_end);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $errors[] = "Payroll already exists for employee $employee_id in this period";
        continue;
    }

    $sql = "INSERT INTO payroll (employee_id, period_start, period_end, basic_salary, allowances, deductions, net_salary, processed_by, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'processed')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssdddds", $employee_id, $period_start, $period_end, $basic_salary, $allowances, $deductions, $net_salary, $processed_by);

    if ($stmt->execute()) {
        $processed++;
    } else {
        $errors[] = "Error processing payroll for employee $employee_id: " . $stmt->error;
    }
    $stmt->close();
}

$conn->close();

if ($processed > 0) {
    echo json_encode(['success' => true, 'message' => "Payroll processed for $processed employees"]);
} else {
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
}
?>