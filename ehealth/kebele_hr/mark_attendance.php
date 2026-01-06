<?php
session_start();
include dirname(__DIR__) . '/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$conn = getDBConnection();
$data = json_decode(file_get_contents('php://input'), true);
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Default Kebele for demo (should come from session in production)
$kebele = isset($_SESSION['kebele']) ? $_SESSION['kebele'] : 'Kebele 01';
$today = isset($data['date']) ? $data['date'] : date('Y-m-d');

if ($action === 'mark_all_present') {
    // Get all employees in this kebele
    $sql = "SELECT employee_id FROM employees WHERE kebele = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $kebele);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $count = 0;
    while ($row = $result->fetch_assoc()) {
        $emp_id = $row['employee_id'];
        
        // Check if attendance exists
        $checkSql = "SELECT id FROM attendance WHERE employee_id = ? AND attendance_date = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("ss", $emp_id, $today);
        $checkStmt->execute();
        
        if ($checkStmt->get_result()->num_rows == 0) {
            // Insert present record with default times (e.g., 9-5)
            $insertSql = "INSERT INTO attendance (employee_id, attendance_date, status, check_in, check_out, working_hours, kebele) VALUES (?, ?, 'present', '09:00:00', '17:00:00', 8.0, ?)";
            $insertStmt = $conn->prepare($insertSql);
            $insertStmt->bind_param("sss", $emp_id, $today, $kebele);
            if ($insertStmt->execute()) {
                $count++;
            }
        }
    }
    
    echo json_encode(['success' => true, 'message' => "$count employees marked as present"]);

} elseif ($action === 'update_single') {
    $emp_id = $data['employee_id'];
    $status = $data['status'];
    $check_in = $data['check_in'] ?: null;
    $check_out = $data['check_out'] ?: null;
    
    // Calculate working hours if both times are present
    $working_hours = 0;
    if ($check_in && $check_out) {
        $t1 = strtotime($check_in);
        $t2 = strtotime($check_out);
        $working_hours = round(($t2 - $t1) / 3600, 2);
    }
    
    // Verify employee belongs to this kebele
    $empCheckSql = "SELECT id FROM employees WHERE employee_id = ? AND kebele = ?";
    $empCheckStmt = $conn->prepare($empCheckSql);
    $empCheckStmt->bind_param("ss", $emp_id, $kebele);
    $empCheckStmt->execute();
    if ($empCheckStmt->get_result()->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Employee not found in this Kebele']);
        exit;
    }

    // Check if exists
    $checkSql = "SELECT id FROM attendance WHERE employee_id = ? AND attendance_date = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("ss", $emp_id, $today);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update
        $updateSql = "UPDATE attendance SET status = ?, check_in = ?, check_out = ?, working_hours = ? WHERE employee_id = ? AND attendance_date = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("ssssss", $status, $check_in, $check_out, $working_hours, $emp_id, $today);
        $success = $updateStmt->execute();
    } else {
        // Insert
        $insertSql = "INSERT INTO attendance (employee_id, attendance_date, status, check_in, check_out, working_hours, kebele) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param("sssssss", $emp_id, $today, $status, $check_in, $check_out, $working_hours, $kebele);
        $success = $insertStmt->execute();
    }
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Attendance updated']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update attendance']);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

$conn->close();
?>
