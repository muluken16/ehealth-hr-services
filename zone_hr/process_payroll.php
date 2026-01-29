<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'zone_hr') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

include '../db.php';
$conn = getDBConnection();

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit();
}

$processed_by = $_SESSION['user_id'];

$conn->begin_transaction();

try {
    foreach ($data as $payroll) {
        $sql = "INSERT INTO payroll (employee_id, period_start, period_end, basic_salary, allowances, deductions, net_salary, processed_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssdddi",
            $payroll['employee_id'],
            $payroll['period_start'],
            $payroll['period_end'],
            $payroll['basic_salary'],
            $payroll['allowances'],
            $payroll['deductions'],
            $payroll['net_salary'],
            $processed_by
        );
        $stmt->execute();
        $stmt->close();
    }

    $conn->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>