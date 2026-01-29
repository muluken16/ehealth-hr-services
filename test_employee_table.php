<?php
// Test if employees table exists and can be accessed
require_once 'db.php';

header('Content-Type: application/json');

try {
    $conn = getDBConnection();

    // Check if table exists
    $result = $conn->query("SHOW TABLES LIKE 'employees'");
    if ($result->num_rows == 0) {
        echo json_encode(['success' => false, 'message' => 'Employees table does not exist']);
        exit();
    }

    // Try to select from table
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM employees");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    echo json_encode([
        'success' => true,
        'message' => 'Employees table exists',
        'count' => $row['count']
    ]);

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>