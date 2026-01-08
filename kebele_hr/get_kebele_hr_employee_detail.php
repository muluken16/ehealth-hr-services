<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
header('Content-Type: application/json');

// Set default user for demo (same as in add_employee.php)
$_SESSION['user_name'] = 'Kebele HR Officer';
$_SESSION['role'] = 'kebele_hr';

// Create database connection
$conn = new mysqli("localhost", "root", "", "ehealth");

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

// Get employee ID from request
$employee_id = $_GET['id'] ?? null;

if (!$employee_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Employee ID is required']);
    exit();
}

try {
    // Get current user and kebele
    $current_user = $_SESSION['user_name'] ?? 'Unknown';
    $user_kebele = $_SESSION['kebele'] ?? 'Kebele 1';
    
    // Get employee details - if assigned to user's kebele
    // Get employee details - if assigned to user's kebele
    // Get employee details - if assigned to user's kebele (working location)
    $sql = "SELECT * FROM employees WHERE (id = ? OR employee_id = ?) AND working_kebele = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("sss", $employee_id, $employee_id, $user_kebele);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Employee not found or you do not have permission to view this employee']);
        exit();
    }
    
    $employee = $result->fetch_assoc();
    
    // Parse documents JSON if it exists
    if ($employee['documents']) {
        $employee['documents_array'] = json_decode($employee['documents'], true);
    }
    
    // Return employee details
    echo json_encode($employee);
    
    $stmt->close();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>