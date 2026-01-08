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
    echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

// Get employee ID from request
$employee_id = $_GET['id'] ?? $_POST['id'] ?? null;

if (!$employee_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Employee ID is required']);
    exit();
}

try {
    // Get current user
    $current_user = $_SESSION['user_name'] ?? 'Unknown';
    
    // First, check if the employee exists and belongs to current user
    $check_sql = "SELECT id, employee_id, first_name, last_name, documents, scan_file, criminal_file, fin_scan, loan_file, leave_document FROM employees WHERE (id = ? OR employee_id = ?) AND created_by = ?";
    $check_stmt = $conn->prepare($check_sql);
    
    if (!$check_stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $check_stmt->bind_param("sss", $employee_id, $employee_id, $current_user);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Employee not found or you do not have permission to delete this employee']);
        exit();
    }
    
    $employee = $result->fetch_assoc();
    $check_stmt->close();
    
    // Start transaction
    $conn->begin_transaction();
    
    // Delete employee record
    $delete_sql = "DELETE FROM employees WHERE (id = ? OR employee_id = ?) AND created_by = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    
    if (!$delete_stmt) {
        throw new Exception("Prepare delete failed: " . $conn->error);
    }
    
    $delete_stmt->bind_param("sss", $employee_id, $employee_id, $current_user);
    
    if (!$delete_stmt->execute()) {
        throw new Exception("Delete failed: " . $delete_stmt->error);
    }
    
    $affected_rows = $delete_stmt->affected_rows;
    $delete_stmt->close();
    
    if ($affected_rows === 0) {
        throw new Exception("No employee was deleted");
    }
    
    // Delete associated files
    $upload_dir = '../uploads/employees/';
    $files_to_delete = [];
    
    // Collect all file names
    if ($employee['documents']) {
        $documents = json_decode($employee['documents'], true);
        if (is_array($documents)) {
            $files_to_delete = array_merge($files_to_delete, $documents);
        }
    }
    
    // Add individual files
    $individual_files = ['scan_file', 'criminal_file', 'fin_scan', 'loan_file', 'leave_document'];
    foreach ($individual_files as $field) {
        if (!empty($employee[$field])) {
            $files_to_delete[] = $employee[$field];
        }
    }
    
    // Delete files from filesystem
    $deleted_files = 0;
    foreach ($files_to_delete as $filename) {
        if ($filename && file_exists($upload_dir . $filename)) {
            if (unlink($upload_dir . $filename)) {
                $deleted_files++;
            }
        }
    }
    
    // Commit transaction
    $conn->commit();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Employee deleted successfully',
        'employee_name' => $employee['first_name'] . ' ' . $employee['last_name'],
        'employee_id' => $employee['employee_id'],
        'files_deleted' => $deleted_files
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>