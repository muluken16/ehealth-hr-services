<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Set default user for demo
$_SESSION['user_name'] = 'Wereda Health Officer';
$_SESSION['user_id'] = 4; // wereda_ho user ID
$_SESSION['role'] = 'wereda_health_officer';

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
    $action = $input['action'] ?? null; // 'approve' or 'reject'
    $comments = $input['comments'] ?? '';
    
    if (!$leave_id || !$action) {
        echo json_encode(['success' => false, 'message' => 'Leave ID and action are required']);
        exit();
    }
    
    try {
        $conn->begin_transaction();
        
        // Get leave request details
        $stmt = $conn->prepare("SELECT lr.*, e.first_name, e.last_name, e.woreda FROM leave_requests lr JOIN employees e ON lr.employee_id = e.employee_id WHERE lr.id = ? AND lr.status = 'pending'");
        $stmt->bind_param("i", $leave_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Leave request not found or already processed");
        }
        
        $leave_request = $result->fetch_assoc();
        $stmt->close();
        
        // Verify this is for the correct woreda (in real system, check user's woreda)
        // For demo, we'll approve any request
        
        $user_id = $_SESSION['user_id'];
        $new_status = ($action === 'approve') ? 'approved' : 'rejected';
        
        // Update leave request status
        $update_stmt = $conn->prepare("UPDATE leave_requests SET status = ?, approved_by = ?, approved_at = CURRENT_TIMESTAMP WHERE id = ?");
        $update_stmt->bind_param("sii", $new_status, $user_id, $leave_id);
        
        if (!$update_stmt->execute()) {
            throw new Exception("Failed to update leave request");
        }
        $update_stmt->close();
        
        // If approved, update leave entitlements
        if ($action === 'approve') {
            $current_year = date('Y');
            $leave_type = $leave_request['leave_type'];
            $days_requested = $leave_request['days_requested'];
            
            $used_field_map = [
                'annual' => 'used_annual_leave',
                'sick' => 'used_sick_leave',
                'maternity' => 'used_maternity_leave',
                'paternity' => 'used_paternity_leave',
                'emergency' => 'used_emergency_leave'
            ];
            
            if (isset($used_field_map[$leave_type])) {
                $used_field = $used_field_map[$leave_type];
                $update_entitlement = $conn->prepare("UPDATE leave_entitlements SET {$used_field} = {$used_field} + ? WHERE employee_id = ? AND year = ?");
                $update_entitlement->bind_param("isi", $days_requested, $leave_request['employee_id'], $current_year);
                $update_entitlement->execute();
                $update_entitlement->close();
            }
            
            // Update employee status to on-leave if leave starts today or in the past
            $today = date('Y-m-d');
            if ($leave_request['start_date'] <= $today) {
                $emp_update = $conn->prepare("UPDATE employees SET status = 'on-leave' WHERE employee_id = ?");
                $emp_update->bind_param("s", $leave_request['employee_id']);
                $emp_update->execute();
                $emp_update->close();
            }
        }
        
        // Insert approval record
        $approval_stmt = $conn->prepare("INSERT INTO leave_approvals (leave_request_id, approver_level, approver_id, status, comments, approved_at) VALUES (?, 'ho', ?, ?, ?, CURRENT_TIMESTAMP)");
        $approval_stmt->bind_param("iiss", $leave_id, $user_id, $new_status, $comments);
        $approval_stmt->execute();
        $approval_stmt->close();
        
        $conn->commit();
        
        $action_text = ($action === 'approve') ? 'approved' : 'rejected';
        echo json_encode([
            'success' => true, 
            'message' => "Leave request {$action_text} successfully",
            'employee_name' => $leave_request['first_name'] . ' ' . $leave_request['last_name'],
            'leave_type' => $leave_request['leave_type'],
            'days' => $leave_request['days_requested'],
            'action' => $action_text
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