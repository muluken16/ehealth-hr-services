<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
header('Content-Type: application/json');

if ($_SESSION['role'] != 'wereda_hr' && $_SESSION['role'] != 'wereda_health_officer') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once '../db.php';

try {
    $conn = getDBConnection();
    $employee_id = $_GET['employee_id'] ?? '';

    if (empty($employee_id)) {
        echo json_encode(['success' => false, 'message' => 'Employee ID required']);
        exit();
    }

    // Verify employee belongs to this woreda  
    $woreda_wildcard = "%" . ($_SESSION['woreda'] ?? 'Woreda 1') . "%";

    $stmt = $conn->prepare("SELECT * FROM employees WHERE employee_id = ? AND woreda LIKE ? LIMIT 1");
    $stmt->bind_param('ss', $employee_id, $woreda_wildcard);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($employee = $result->fetch_assoc()) {
        echo json_encode(['success' => true, 'employee' => $employee]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Employee not found']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>