<?php
session_start();
header('Content-Type: application/json');

require_once '../db.php';
$conn = getDBConnection();

$id = $_GET['id'] ?? '';

if (empty($id)) {
    echo json_encode(['success' => false, 'message' => 'Employee ID is required']);
    exit;
}

try {
    if (is_numeric($id)) {
        $stmt = $conn->prepare("SELECT * FROM employees WHERE id = ?");
        $stmt->bind_param("i", $id);
    } else {
        $stmt = $conn->prepare("SELECT * FROM employees WHERE employee_id = ?");
        $stmt->bind_param("s", $id);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Employee not found']);
        exit;
    }

    $employee = $result->fetch_assoc();
    echo json_encode(['success' => true, 'employee' => $employee]);

    $stmt->close();
} catch (Exception $e) {
    error_log("Error in get_wereda_hr_employee_detail.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

$conn->close();
