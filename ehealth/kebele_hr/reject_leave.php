<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Set default user for demo
$_SESSION['user_name'] = 'Kebele HR Officer';
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'kebele_hr';

header('Content-Type: application/json');

// Create database connection
$conn = new mysqli("localhost", "root", "", "ehealth");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $leave_id = $input['leave_id'] ?? null;
    $rejection_reason = $input['reason'] ?? 'No reason provided';
    
    if (!$leave_id) {
        echo json_encode(['success' => false, 'message' => 'Leave ID is required']);
        exit();
    }
    
    try {
        $conn->begin_transaction();
        
        // Get leave request details
        $stmt = $conn->prepare("SELECT lr.*, e.first_name, e.last_name FROM leave_requests lr JOIN employees e ON lr.employee_id = e.employee_id WHERE lr.id = ? AND lr.status = 'pending'");
        $stmt->bind_param("i", $leave_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Leave request not found or already processed");
        }
        
        $leave_request = $result->fetch_assoc();
        $stmt->close();
        
        // Update leave request status
        $user_id = $_SESSION['user_id'];
        $update_stmt = $conn->prepare("UPDATE leave_requests SET status = 'rejected', approved_by = ?, approved_at = CURRENT_TIMESTAMP WHERE id = ?");
        $update_stmt->bind_param("ii", $user_id, $leave_id);
        
        if (!$update_stmt->execute()) {
            throw new Exception("Failed to reject leave request");
        }
        
        $update_stmt->close();
        $conn->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Leave request rejected successfully',
            'employee_name' => $leave_request['first_name'] . ' ' . $leave_request['last_name'],
            'leave_type' => $leave_request['leave_type'],
            'days' => $leave_request['days_requested']
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>