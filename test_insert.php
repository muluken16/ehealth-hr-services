<?php
// Test basic employee insert
require_once 'db.php';

header('Content-Type: application/json');

try {
    $conn = getDBConnection();

    // Simple test insert
    $stmt = $conn->prepare("INSERT INTO employees (
        employee_id, first_name, last_name, gender, date_of_birth,
        email, department_assigned, position, join_date, status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $employee_id = 'TEST-' . time();
    $first_name = 'Test';
    $last_name = 'User';
    $gender = 'male';
    $date_of_birth = '1990-01-01';
    $email = 'test@example.com';
    $department_assigned = 'medical';
    $position = 'Test Position';
    $join_date = date('Y-m-d');
    $status = 'active';

    $stmt->bind_param("ssssssssss",
        $employee_id, $first_name, $last_name, $gender, $date_of_birth,
        $email, $department_assigned, $position, $join_date, $status
    );

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Test insert successful', 'employee_id' => $employee_id]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Insert failed: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>