<?php
session_start();

// Check if user is logged in and has appropriate role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'wereda_hr') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

require_once '../db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid employee ID']);
    exit();
}

try {
    // Get database connection
    $conn = getDBConnection();

    // First, get employee details for file cleanup
    $stmt = $conn->prepare("SELECT scan_file, criminal_file, fin_scan, loan_file FROM employees WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Employee not found']);
        exit();
    }

    $employee = $result->fetch_assoc();

    // Delete the employee record
    $stmt = $conn->prepare("DELETE FROM employees WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // Clean up uploaded files if they exist
        $uploadDir = '../uploads/employees/';
        $filesToDelete = [
            $employee['scan_file'],
            $employee['criminal_file'],
            $employee['fin_scan'],
            $employee['loan_file']
        ];

        foreach ($filesToDelete as $file) {
            if ($file && file_exists($uploadDir . $file)) {
                unlink($uploadDir . $file);
            }
        }

        echo json_encode(['success' => true, 'message' => 'Employee deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete employee']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>